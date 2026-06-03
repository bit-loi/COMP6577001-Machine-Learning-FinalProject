<?php
$requestedType = (string) ($_GET['type'] ?? '500');
$errorTitles = ['404' => 'Page Not Found', '403' => 'Access Denied', '500' => 'System Error', '503' => 'Service Unavailable'];
$errorMessages = ['404' => 'The page you are looking for doesn\'t exist or has been moved.', '403' => 'You don\'t have permission to access this page.', '500' => 'Something went wrong on our end. We\'re working to fix it.', '503' => 'Our servers are temporarily unavailable. Please try again shortly.'];
$type = array_key_exists($requestedType, $errorTitles) ? $requestedType : '500';
$title = $errorTitles[$type] ?? 'Unknown Error';
$message = $errorMessages[$type] ?? 'Something went wrong.';

function error_page_escape($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo error_page_escape("$type - $title"); ?> | Shopmart</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f5f5f5; color: #333; margin: 0; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .error-card { background: #fff; border: 1px solid #eee; border-radius: 20px; padding: 60px; max-width: 500px; text-align: center; }
        .error-code { font-size: 6rem; font-weight: 800; color: #FF6B35; line-height: 1; margin-bottom: 12px; }
        .error-title { font-size: 1.3rem; font-weight: 700; color: #222; margin-bottom: 12px; }
        .error-msg { font-size: 0.9rem; color: #999; line-height: 1.7; margin-bottom: 32px; }
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; border-radius: 10px; font-size: 0.85rem; font-weight: 600; text-decoration: none; transition: all 0.2s; border: none; cursor: pointer; }
        .btn-primary { background: #FF6B35; color: white; }
        .btn-primary:hover { background: #EE4D2D; }
        .btn-secondary { background: #f5f5f5; color: #555; }
        .btn-secondary:hover { background: #eee; }
    </style>
</head>
<body>
    <div class="error-card">
        <div style="margin-bottom: 24px;">🛒 <span style="font-weight: 800; color: #FF6B35;">Shopmart</span></div>
        <div class="error-code"><?php echo error_page_escape($type); ?></div>
        <div class="error-title"><?php echo error_page_escape($title); ?></div>
        <div class="error-msg"><?php echo error_page_escape($message); ?></div>
        <div style="display: flex; gap: 12px; justify-content: center;">
            <a href="/shopmart/" class="btn btn-primary">← Back to Store</a>
            <button onclick="window.history.back()" class="btn btn-secondary">Go Back</button>
        </div>
    </div>
</body>
</html>
