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
<?php
$pageTitle = 'Customers Management';
$pageDescription = 'Manage your platform\'s users';
$topbarAction = '<a href="add.php" style="display:inline-flex; align-items:center; justify-content:center; gap:6px; background:#EE4D2D; color:#fff; font-size:0.8rem; font-weight:600; padding:8px 16px; border-radius:8px; text-decoration:none; transition:background 0.2s;" onmouseover="this.style.background=\'#D74226\'" onmouseout="this.style.background=\'#EE4D2D\'">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg> Add User
</a>';
require_once '../includes/header.php';
?>

        <!-- Table Box -->
        <!-- Table Box -->
        <div class="table-card mt-2">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <div style="font-size: 0.85rem; font-weight: 700; letter-spacing: 0.1em; color: #475569; text-transform: uppercase; display: flex; align-items: center; gap: 12px;">
                    <i data-lucide="users" style="width: 20px; height: 20px;"></i>
                    All Users
                </div>
                <div style="display: flex; gap: 16px;">
                    <input type="text" class="bg-gray-50 border border-gray-200 text-gray-800 rounded-lg text-xs px-3 py-2 w-64 focus:outline-none focus:border-[#EE4D2D] transition-colors" placeholder="Search by name or email...">
                    <select class="bg-gray-50 border border-gray-200 text-gray-800 rounded-lg text-xs px-3 py-2 outline-none focus:border-[#EE4D2D]">
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
                                <td><span class="mono" style="color: #94a3b8; font-size: 0.8rem;">#<?php echo str_pad($user->id, 4, '0', STR_PAD_LEFT); ?></span></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 16px;">
                                        <div style="width: 40px; height: 40px; border-radius: 50%; background: #FFF4ED; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #EE4D2D; font-size: 0.85rem; border: 1px solid #FFD9C6;" class="mono uppercase">
                                            <?php echo substr($user->username, 0, 2); ?>
                                        </div>
                                        <div>
                                            <div style="color: #0f172a; font-weight: 700; font-size: 0.95rem; letter-spacing: -0.01em;"><?php echo htmlspecialchars($user->username); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="mono" style="color: #64748b; font-size: 0.8rem;"><?php echo htmlspecialchars($user->email); ?></span></td>
                                <td>
                                    <?php if(isset($user->role) && $user->role === 'admin'): ?>
                                        <span class="badge badge-success">Admin</span>
                                    <?php else: ?>
                                        <span class="badge badge-info">Customer</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="mono" style="color: #94a3b8; font-size: 0.8rem;"><?php echo date('M d, Y', strtotime($user->created_at)); ?></span></td>
                                <td style="text-align: right;">
                                    <a href="#" class="action-icon" title="Edit">
                                        <i data-lucide="pencil" style="width: 16px; height: 16px;"></i>
                                    </a>
                                    <a href="#" class="action-icon btn-delete" style="color: #ef4444;" title="Suspend">
                                        <i data-lucide="user-x" style="width: 16px; height: 16px;"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: #94a3b8; padding: 80px 0;">
                                <i data-lucide="users" style="width:40px; height:40px; color: #cbd5e1; margin: 0 auto 16px; display: block;"></i>
                                <div style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.2em; font-weight: 700;">No users found</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

<?php require_once '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>.swal-border { border-radius: 16px !important; }</style>
<script>
    // Delete confirmation with SweetAlert2
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'SUSPEND USER',
                text: "This action cannot be undone.",
                icon: 'warning',
                showCancelButton: true,
                background: '#fff',
                color: '#333',
                confirmButtonColor: '#EE4D2D',
                cancelButtonColor: '#e2e8f0',
                confirmButtonText: '<span style="color:white; font-weight:bold; font-family:\'Inter\', sans-serif;">SUSPEND</span>',
                cancelButtonText: '<span style="color:#64748b; font-weight:bold; font-family:\'Inter\', sans-serif;">CANCEL</span>',
                customClass: { popup: 'swal-border' }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'SUSPENDED',
                        text: 'User has been suspended.',
                        icon: 'success',
                        background: '#fff',
                        color: '#333',
                        confirmButtonColor: '#EE4D2D',
                        confirmButtonText: '<span style="color:white; font-weight:bold; font-family:\'Inter\', sans-serif;">OK</span>',
                        customClass: { popup: 'swal-border' }
                    });
                }
            });
        });
    });
</script>
