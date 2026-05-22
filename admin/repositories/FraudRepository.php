<?php
/**
 * FraudRepository
 * 
 * Handles database operations for the fraud detection module.
 * Currently the fraud module is API-only (no local DB storage),
 * but this repository can be extended for logging and audit trails.
 */
class FraudRepository
{
    private PDO $conn;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    /**
     * Ensure the fraud_checks audit table exists.
     * Used for logging fraud check results for audit trails.
     */
    public function ensureTables(): void
    {
        $this->conn->exec("CREATE TABLE IF NOT EXISTS fraud_checks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            transaction_amount DECIMAL(12,2),
            anomaly_score DECIMAL(8,5),
            is_anomaly TINYINT DEFAULT 0,
            input_payload JSON,
            checked_by VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_anomaly (is_anomaly),
            INDEX idx_date (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    }

    /**
     * Log a fraud check result for audit trail.
     * @param float  $amount
     * @param float  $score
     * @param bool   $isAnomaly
     * @param array  $payload
     * @param string $checkedBy
     * @return int  Insert ID
     */
    public function logCheck(float $amount, float $score, bool $isAnomaly, array $payload, string $checkedBy): int
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO fraud_checks (transaction_amount, anomaly_score, is_anomaly, input_payload, checked_by)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $amount,
            $score,
            $isAnomaly ? 1 : 0,
            json_encode($payload),
            $checkedBy
        ]);

        return (int) $this->conn->lastInsertId();
    }

    /**
     * Get recent fraud check history.
     * @param int $limit
     * @return array
     */
    public function getRecentChecks(int $limit = 20): array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM fraud_checks ORDER BY created_at DESC LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
