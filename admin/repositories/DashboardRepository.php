<?php
/**
 * DashboardRepository
 * 
 * Handles all database queries for the admin dashboard overview page.
 * Provides clean methods for fetching statistics, recent orders, 
 * product data, and revenue analytics.
 */
class DashboardRepository
{
    private PDO $conn;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    /**
     * Get main dashboard statistics.
     * @return object { totalProducts, totalUsers, totalOrders, totalRevenue, pendingOrders, completedOrders, cancelledOrders, processingOrders }
     */
    public function getStatistics(): object
    {
        $stats = new stdClass();

        $stats->totalProducts   = (int) $this->conn->query("SELECT COUNT(*) as c FROM products")->fetch(PDO::FETCH_OBJ)->c;
        $stats->totalUsers      = (int) $this->conn->query("SELECT COUNT(*) as c FROM users WHERE role = 'customer' OR is_admin = 0")->fetch(PDO::FETCH_OBJ)->c;
        $stats->totalOrders     = (int) $this->conn->query("SELECT COUNT(*) as c FROM orders")->fetch(PDO::FETCH_OBJ)->c;
        $stats->totalRevenue    = (float) $this->conn->query("SELECT COALESCE(SUM(total_amount), 0) as r FROM orders WHERE status = 'completed'")->fetch(PDO::FETCH_OBJ)->r;
        $stats->pendingOrders   = (int) $this->conn->query("SELECT COUNT(*) as c FROM orders WHERE status = 'pending'")->fetch(PDO::FETCH_OBJ)->c;
        $stats->completedOrders = (int) $this->conn->query("SELECT COUNT(*) as c FROM orders WHERE status = 'completed'")->fetch(PDO::FETCH_OBJ)->c;
        $stats->cancelledOrders = (int) $this->conn->query("SELECT COUNT(*) as c FROM orders WHERE status = 'cancelled'")->fetch(PDO::FETCH_OBJ)->c;
        $stats->processingOrders= (int) $this->conn->query("SELECT COUNT(*) as c FROM orders WHERE status = 'processing'")->fetch(PDO::FETCH_OBJ)->c;

        return $stats;
    }

    /**
     * Get recent orders with customer usernames.
     * @param int $limit
     * @return array
     */
    public function getRecentOrders(int $limit = 8): array
    {
        return $this->conn->query(
            "SELECT o.*, u.username 
             FROM orders o 
             LEFT JOIN users u ON o.user_id = u.id 
             ORDER BY o.created_at DESC 
             LIMIT {$limit}"
        )->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get products with low stock.
     * @param int $threshold  Stock level below this is considered low.
     * @param int $limit
     * @return array
     */
    public function getLowStockProducts(int $threshold = 10, int $limit = 5): array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM products 
             WHERE stock < ? AND status = 1 
             ORDER BY stock ASC 
             LIMIT ?"
        );
        $stmt->execute([$threshold, $limit]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get top-selling products by total quantity sold.
     * @param int $limit
     * @return array
     */
    public function getTopSellingProducts(int $limit = 5): array
    {
        return $this->conn->query(
            "SELECT p.name, p.price, COALESCE(SUM(oi.quantity), 0) as total_sold 
             FROM products p 
             LEFT JOIN order_items oi ON p.id = oi.product_id 
             GROUP BY p.id 
             ORDER BY total_sold DESC 
             LIMIT {$limit}"
        )->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get monthly revenue data for the current year.
     * @return array  Indexed 0–11, each value is the revenue for that month.
     */
    public function getMonthlyRevenue(): array
    {
        $year = date('Y');
        $revenue = [];

        for ($m = 1; $m <= 12; $m++) {
            $stmt = $this->conn->prepare(
                "SELECT COALESCE(SUM(total_amount), 0) as rev 
                 FROM orders 
                 WHERE MONTH(created_at) = ? AND YEAR(created_at) = ? AND status = 'completed'"
            );
            $stmt->execute([$m, $year]);
            $revenue[] = (float) $stmt->fetch(PDO::FETCH_OBJ)->rev;
        }

        return $revenue;
    }
}
