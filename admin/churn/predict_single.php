<?php
/**
 * Single Churn Prediction Controller
 */
require_once '../includes/auth.php';
require_once '../../config/config.php';

$pageTitle = 'Customer Churn Check';
$pageDescription = 'Single Customer Risk Prediction';

require_once '../includes/header.php';
require_once '../views/churn/predict_single.view.php';
require_once '../includes/footer.php';
