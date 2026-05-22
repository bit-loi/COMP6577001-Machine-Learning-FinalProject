<?php
/**
 * Manual Fraud Check Controller
 */
require_once 'includes/auth.php';
require_once '../config/config.php';

$pageTitle = 'Fraud Detection';
$pageDescription = 'Manual Transaction Risk Check';

require_once 'includes/header.php';
require_once 'views/fraud/check.view.php';
require_once 'includes/footer.php';
