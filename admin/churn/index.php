<?php
/**
 * Customer Retention Intelligence (Churn) Dashboard Controller
 */
require_once '../includes/auth.php';
require_once '../../config/config.php';
require_once '../repositories/ChurnRepository.php';

$repo = new ChurnRepository($conn);

// Initialize DB schema if not exists
$repo->ensureTables();

// Handle clear all request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'clear_all') {
    $repo->clearAll();
    header('Location: index.php?msg=cleared');
    exit();
}

$riskFilter   = $_GET['risk']   ?? 'all';
$searchFilter = trim($_GET['search'] ?? '');

$latestSnap = $repo->getLatestSnapshotDate();
$counts     = $repo->getSummaryCounts();
$rows       = $repo->getFilteredScores($riskFilter, $searchFilter);
$distData   = $repo->getDistributionData();

$pageTitle = 'Customer Retention Intelligence';
$pageDescription = 'Batch Churn Prediction · Decision Support System';

require_once '../includes/header.php';
require_once '../views/churn/index.view.php';
require_once '../includes/footer.php';
