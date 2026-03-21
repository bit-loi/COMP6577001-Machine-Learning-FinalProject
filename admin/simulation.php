<?php
session_start();

// Check if user is admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ML Simulation — Admin Console</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,700;1,700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    
    <!-- Load Vite Dev Server / Build files for React Component -->
    <?php
    $vite_dev = true; // Set to false in production
    if ($vite_dev) {
        echo '<script type="module" src="http://localhost:5173/@vite/client"></script>';
        echo '<script type="module" src="http://localhost:5173/components/mount-app.tsx"></script>';
    }
    ?>

    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #050505; color: white; margin: 0; display: flex; min-height: 100vh; }
        .mono { font-family: 'JetBrains Mono', monospace; }
        .serif { font-family: 'Playfair Display', serif; }

        /* Sidebar Styles (matching index.php) */
        .sidebar { width: 240px; min-height: 100vh; background: #0a0a0a; border-right: 1px solid rgba(255,255,255,0.06); display: flex; flex-direction: column; position: fixed; top: 0; left: 0; z-index: 50; }
        .sidebar-logo { padding: 28px 24px; border-bottom: 1px solid rgba(255,255,255,0.06); }
        .sidebar-nav { flex: 1; padding: 16px 12px; }
        .nav-item { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 8px; color: rgba(255,255,255,0.4); font-size: 0.8rem; font-weight: 500; text-decoration: none; transition: all 0.15s; margin-bottom: 2px; cursor: pointer; }
        .nav-item:hover { background: rgba(255,255,255,0.05); color: rgba(255,255,255,0.8); }
        .nav-item.active { background: rgba(255,255,255,0.08); color: white; }
        .nav-item svg { width: 16px; height: 16px; flex-shrink: 0; }
        .nav-section { font-size: 0.65rem; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase; color: rgba(255,255,255,0.2); padding: 16px 12px 8px; }

        /* Main content Styles */
        .main { margin-left: 240px; flex: 1; min-height: 100vh; display: flex; flex-direction: column; }
        .topbar { height: 64px; border-bottom: 1px solid rgba(255,255,255,0.06); display: flex; align-items: center; justify-content: space-between; padding: 0 32px; background: rgba(5,5,5,0.8); backdrop-filter: blur(12px); position: sticky; top: 0; z-index: 40; }
        
        /* Our React element will take full height of content */
        .content { flex: 1; padding: 0; display: flex; flex-direction: column; }
        
        #react-ml-simulation { flex: 1; display: flex; flex-direction: column; }
        /* We want the dashboard component inside to span its own height correctly */
        #react-ml-simulation > div { height: 100%; min-height: calc(100vh - 64px); }
    </style>
</head>
<body>

<!-- Include Sidebar -->
<?php include 'includes/sidebar.php'; ?>

<!-- Main Content -->
<div class="main">
    
    <!-- Topbar -->
    <div class="topbar">
        <div>
            <div style="font-size: 1rem; font-weight: 600; color: white;">ML Intelligence Simulation</div>
            <div style="font-size: 0.75rem; color: rgba(255,255,255,0.3); margin-top: 1px;">Live Demonstration</div>
        </div>
        <div style="display: flex; align-items: center; gap: 16px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; color: white;">
                    <?php echo strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)); ?>
                </div>
                <div>
                    <div style="font-size: 0.8rem; font-weight: 600; color: white;"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></div>
                    <div style="font-size: 0.65rem; color: rgba(255,255,255,0.3);">Administrator</div>
                </div>
            </div>
        </div>
    </div>

    <!-- React Render Target -->
    <div class="content">
        <div id="react-ml-simulation"></div>
    </div>

</div>

<script>
    // Just simple manual fix for active class highlighting
    document.querySelectorAll('.nav-item').forEach(item => {
        if(item.textContent.includes('ML Simulation')) {
            item.classList.add('active');
        } else {
            item.classList.remove('active');
        }
    });
</script>
</body>
</html>
