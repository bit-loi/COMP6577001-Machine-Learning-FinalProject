<?php
/**
 * Admin Overview Dashboard Controller
 */
require_once 'includes/auth.php';
require_once '../config/config.php';
require_once 'repositories/DashboardRepository.php';

$repo = new DashboardRepository($conn);

try {
    $stats             = $repo->getStatistics();
    $recentOrders      = $repo->getRecentOrders(8);
    $lowStockProducts  = $repo->getLowStockProducts(10, 5);
    $topProducts       = $repo->getTopSellingProducts(5);
    $monthlyRevenue    = $repo->getMonthlyRevenue();
} catch (PDOException $e) {
    error_log("Dashboard Controller Error: " . $e->getMessage());
    $stats = (object)[
        'totalProducts' => 0, 'totalUsers' => 0, 'totalOrders' => 0, 'totalRevenue' => 0,
        'pendingOrders' => 0, 'completedOrders' => 0, 'cancelledOrders' => 0, 'processingOrders' => 0
    ];
    $recentOrders = $lowStockProducts = $topProducts = [];
    $monthlyRevenue = array_fill(0, 12, 0);
}

$pageTitle = 'Overview';
$pageDescription = date('l, F j, Y');

require_once 'includes/header.php';
require_once 'views/dashboard/index.view.php';
require_once 'includes/footer.php';
