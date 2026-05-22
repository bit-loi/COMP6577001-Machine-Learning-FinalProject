<?php
/**
 * ChurnRepository
 * 
 * Handles all database operations for the churn prediction module.
 * Manages churn_scores and retention_actions tables,
 * including schema creation, filtering, and aggregation queries.
 */
class ChurnRepository
{
    private PDO $conn;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    /**
     * Ensure the required tables exist.
     * Called once during page initialization.
     */
    public function ensureTables(): void
    {
        $this->conn->exec("CREATE TABLE IF NOT EXISTS churn_scores (
            id INT AUTO_INCREMENT PRIMARY KEY, customer_id VARCHAR(50) NOT NULL,
            snapshot_date DATE NOT NULL, country VARCHAR(100), orders_last_window INT,
            revenue_last_window DECIMAL(12,2), recency_days INT, customer_age_days INT,
            predicted_churn_probability DECIMAL(6,5), predicted_churn TINYINT,
            risk_level VARCHAR(20), recommended_action VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_customer_snapshot (customer_id, snapshot_date),
            INDEX idx_risk (risk_level)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $this->conn->exec("CREATE TABLE IF NOT EXISTS retention_actions (
            id INT AUTO_INCREMENT PRIMARY KEY, customer_id VARCHAR(50) NOT NULL,
            churn_score_id INT NULL, action_type VARCHAR(100),
            action_status VARCHAR(50) DEFAULT 'done', admin_note TEXT,
            actioned_by VARCHAR(100), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_customer (customer_id),
            FOREIGN KEY (churn_score_id) REFERENCES churn_scores(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    }

    /**
     * Clear all churn data (truncate both tables).
     */
    public function clearAll(): void
    {
        $this->conn->exec("SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE retention_actions; TRUNCATE TABLE churn_scores; SET FOREIGN_KEY_CHECKS = 1;");
    }

    /**
     * Get the latest snapshot date.
     * @return string|null
     */
    public function getLatestSnapshotDate(): ?string
    {
        return $this->conn->query("SELECT MAX(snapshot_date) as d FROM churn_scores")
            ->fetch(PDO::FETCH_OBJ)->d ?? null;
    }

    /**
     * Get summary counts grouped by risk level.
     * @return object { total, high_c, med_c, low_c }
     */
    public function getSummaryCounts(): object
    {
        return $this->conn->query("SELECT
            COUNT(*) as total,
            SUM(risk_level='Critical') as high_c,
            SUM(risk_level='At Risk') as med_c,
            SUM(risk_level='Loyal') as low_c
            FROM churn_scores")->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Get churn probability distribution buckets.
     * @return object { d1, d2, d3, d4 }
     */
    public function getDistributionData(): object
    {
        return $this->conn->query("SELECT
            SUM(predicted_churn_probability < 0.25) as d1,
            SUM(predicted_churn_probability >= 0.25 AND predicted_churn_probability < 0.50) as d2,
            SUM(predicted_churn_probability >= 0.50 AND predicted_churn_probability < 0.75) as d3,
            SUM(predicted_churn_probability >= 0.75) as d4
            FROM churn_scores")->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Get filtered churn scores with past actions.
     * @param string $riskFilter  'all' or specific risk level
     * @param string $searchFilter  Customer ID search term
     * @return array
     */
    public function getFilteredScores(string $riskFilter = 'all', string $searchFilter = ''): array
    {
        $where = "WHERE 1=1";
        $params = [];

        if ($riskFilter !== 'all') {
            $where .= " AND risk_level = ?";
            $params[] = $riskFilter;
        }

        if ($searchFilter) {
            $where .= " AND customer_id LIKE ?";
            $params[] = "%{$searchFilter}%";
        }

        $stmt = $this->conn->prepare("SELECT cs.*,
            (SELECT GROUP_CONCAT(action_type ORDER BY created_at DESC SEPARATOR ',') 
             FROM retention_actions ra 
             WHERE ra.customer_id = cs.customer_id LIMIT 1) as past_actions
            FROM churn_scores cs {$where} 
            ORDER BY predicted_churn_probability DESC");
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
