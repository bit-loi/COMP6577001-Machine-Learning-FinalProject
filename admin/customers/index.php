<?php
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../../auth/login.php");
    exit();
}

require_once '../../config/config.php';

// Fetch all users
try {
    $stmt = $conn->prepare("SELECT * FROM users ORDER BY created_at DESC");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_OBJ);
} catch(PDOException $e) {
    error_log("Error fetching users: " . $e->getMessage());
    $users = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers — Premeditatio Malorum</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,700;1,700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #020202; color: white; margin: 0; display: flex; min-height: 100vh; overflow-x: hidden; }
        .mono { font-family: 'JetBrains Mono', monospace; }
        .serif { font-family: 'Playfair Display', serif; }

        /* Sidebar Styles */
        .sidebar { width: 240px; min-height: 100vh; background: #050505; border-right: 1px solid rgba(255,255,255,0.04); display: flex; flex-direction: column; position: fixed; top: 0; left: 0; z-index: 50; }
        .sidebar-logo { padding: 32px 24px; border-bottom: 1px solid rgba(255,255,255,0.04); }
        .sidebar-nav { flex: 1; padding: 24px 16px; }
        .nav-item { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 10px; color: rgba(255,255,255,0.4); font-size: 0.85rem; font-weight: 600; text-decoration: none; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); margin-bottom: 4px; cursor: pointer; }
        .nav-item:hover { background: rgba(255,255,255,0.03); color: rgba(255,255,255,0.9); transform: translateX(4px); }
        .nav-item.active { background: rgba(255,255,255,0.1); color: white; border-left: 2px solid white; border-radius: 4px 10px 10px 4px; }
        .nav-item svg { width: 18px; height: 18px; flex-shrink: 0; transition: transform 0.3s; color: inherit; }
        .nav-section { font-size: 0.65rem; font-weight: 800; letter-spacing: 0.2em; text-transform: uppercase; color: rgba(255,255,255,0.2); padding: 16px 16px 12px; }

        /* Main content */
        .main { margin-left: 240px; flex: 1; min-height: 100vh; background: #050505; }
        .topbar { height: 80px; border-bottom: 1px solid rgba(255,255,255,0.04); display: flex; align-items: center; justify-content: space-between; padding: 0 48px; background: rgba(2,2,2,0.8); backdrop-filter: blur(24px); position: sticky; top: 0; z-index: 40; }
        .content { flex: 1; padding: 48px; display: flex; flex-direction: column; gap: 32px; max-width: 1600px; margin: 0 auto; width: 100%; }
        
        /* Table Box */
        .table-card { background: rgba(15,15,15,0.6); border: 1px solid rgba(255,255,255,0.06); border-radius: 16px; padding: 32px; width: 100%; overflow-x: auto; backdrop-filter: blur(20px); transition: transform 0.4s, box-shadow 0.4s, border-color 0.4s; }
        .table-card:hover { transform: translateY(-4px); box-shadow: 0 20px 40px rgba(0,0,0,0.5); border-color: rgba(255,255,255,0.12); }
        
        .data-table { width: 100%; border-collapse: collapse; min-width: 800px; }
        .data-table th { font-size: 0.75rem; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase; color: rgba(255,255,255,0.5); padding: 16px 20px; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.06); }
        .data-table td { padding: 18px 20px; font-size: 0.85rem; color: rgba(255,255,255,0.7); border-bottom: 1px solid rgba(255,255,255,0.04); vertical-align: middle; transition: background 0.3s; }
        .data-table tr:hover td { background: rgba(255,255,255,0.02); }
        .data-table tr:last-child td { border-bottom: none; }

        /* Badge */
        .badge { display: inline-flex; align-items: center; justify-content: center; gap: 4px; padding: 6px 14px; border-radius: 30px; font-size: 0.7rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; }
        .badge-admin { background: rgba(255,255,255,0.1); color: #fff; border: 1px solid rgba(255,255,255,0.3); box-shadow: 0 0 15px rgba(255,255,255,0.05); }
        .badge-customer { background: transparent; color: rgba(255,255,255,0.6); border: 1px solid rgba(255,255,255,0.15); }

        /* Actions */
        .action-icon { color: rgba(255,255,255,0.4); transition: color 0.3s, transform 0.3s; display: inline-block; margin: 0 4px; padding: 6px; }
        .action-icon:hover { color: white; transform: translateY(-2px); }
        
        /* Inputs */
        .input-dark { background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.1); color: white; padding: 12px 20px; border-radius: 8px; outline: none; transition: border-color 0.3s, box-shadow 0.3s; font-size: 0.85rem; font-family: 'Plus Jakarta Sans', sans-serif;}
        .input-dark:focus { border-color: rgba(255,255,255,0.4); box-shadow: 0 0 15px rgba(255,255,255,0.05); }

        .btn { border: none; outline: none; padding: 12px 28px; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.15em; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 12px; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
        .btn-primary { background: white; color: black; box-shadow: 0 4px 15px rgba(255,255,255,0.1); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(255,255,255,0.2); background: #f8f8f8; }

        ::-webkit-scrollbar { width: 10px; height: 10px; }
        ::-webkit-scrollbar-track { background: rgba(0,0,0,0.2); }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 5px; border: 2px solid rgba(0,0,0,1); }
        ::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }
    </style>
</head>
<body>

<!-- Include Sidebar -->
<?php include '../includes/sidebar.php'; ?>

<!-- Main Content -->
<div class="main">
    <!-- Topbar -->
    <div class="topbar">
        <div>
            <div style="font-size: 1.25rem; font-weight: 700; color: white; letter-spacing: -0.02em;">Customers Management</div>
            <div style="font-size: 0.85rem; color: rgba(255,255,255,0.4); margin-top: 4px; font-weight: 500;">Manage your platform's users</div>
        </div>
        <div style="display: flex; align-items: center; gap: 24px;">
            <button class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-4 h-4" style="width: 16px; height: 16px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> 
                Add User
            </button>
            <div style="display: flex; align-items: center; gap: 12px; padding: 8px 16px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); border-radius: 30px;">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: #333; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 800; color: white; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);">
                    <?php echo strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="content">

        <!-- Table Box -->
        <div class="table-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
                <div style="font-size: 0.85rem; font-weight: 700; letter-spacing: 0.15em; color: rgba(255,255,255,0.8); text-transform: uppercase; display: flex; align-items: center; gap: 12px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    All Users
                </div>
                <div style="display: flex; gap: 16px;">
                    <input type="text" class="input-dark" style="width: 300px;" placeholder="Search by name or email...">
                    <select class="input-dark" style="width: 180px; appearance: none; cursor: pointer;">
                        <option>All Roles</option>
                        <option>Admin</option>
                        <option>Customer</option>
                    </select>
                </div>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th>User Identity</th>
                        <th>Email Address</th>
                        <th>Access Level</th>
                        <th>Joined Date</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($users): ?>
                        <?php foreach($users as $user): ?>
                            <tr>
                                <td><span class="mono" style="color: rgba(255,255,255,0.4); font-size: 0.8rem;">#<?php echo str_pad($user->id, 4, '0', STR_PAD_LEFT); ?></span></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 16px;">
                                        <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; font-weight: 700; color: rgba(255,255,255,0.8); font-size: 0.85rem; border: 1px solid rgba(255,255,255,0.1);" class="mono uppercase">
                                            <?php echo substr($user->username, 0, 2); ?>
                                        </div>
                                        <div>
                                            <div style="color: white; font-weight: 700; font-size: 0.95rem; letter-spacing: -0.01em;"><?php echo htmlspecialchars($user->username); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="mono" style="color: rgba(255,255,255,0.6); font-size: 0.8rem;"><?php echo htmlspecialchars($user->email); ?></span></td>
                                <td>
                                    <?php if(isset($user->role) && $user->role === 'admin'): ?>
                                        <span class="badge badge-admin">Admin</span>
                                    <?php else: ?>
                                        <span class="badge badge-customer">Customer</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="mono" style="color: rgba(255,255,255,0.4); font-size: 0.8rem;"><?php echo date('M d, Y', strtotime($user->created_at)); ?></span></td>
                                <td style="text-align: right;">
                                    <a href="#" class="action-icon" title="Edit">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 18px; height: 18px;"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                    </a>
                                    <a href="#" class="action-icon btn-delete" style="color: rgba(255,255,255,0.2);" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.2)'" title="Suspend">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm0 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8z"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: rgba(255,255,255,0.3); padding: 80px 0;">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 16px; display: block; opacity: 0.5;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                <div style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.2em; font-weight: 700;">No users found</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>.swal-border { border: 1px solid rgba(255,255,255,0.1) !important; border-radius: 12px !important; }</style>
<script>
    // Navigation active state
    document.querySelectorAll('.nav-item').forEach(item => {
        if(item.textContent.includes('Customers')) {
            item.classList.add('active');
        } else {
            item.classList.remove('active');
        }
    });

    // Delete confirmation with SweetAlert2
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'SUSPEND USER',
                text: "This action cannot be undone.",
                icon: 'warning',
                showCancelButton: true,
                background: '#111',
                color: '#fff',
                confirmButtonColor: '#fff',
                cancelButtonColor: '#333',
                confirmButtonText: '<span style="color:black; font-weight:bold; font-family:\'Plus Jakarta Sans\', sans-serif;">SUSPEND</span>',
                cancelButtonText: '<span style="color:white; font-family:\'Plus Jakarta Sans\', sans-serif;">CANCEL</span>',
                customClass: { popup: 'swal-border' }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'SUSPENDED',
                        text: 'User has been suspended.',
                        icon: 'success',
                        background: '#111',
                        color: '#fff',
                        confirmButtonColor: '#fff',
                        confirmButtonText: '<span style="color:black; font-weight:bold; font-family:\'Plus Jakarta Sans\', sans-serif;">OK</span>',
                        customClass: { popup: 'swal-border' }
                    });
                }
            });
        });
    });
</script>
</body>
</html>
