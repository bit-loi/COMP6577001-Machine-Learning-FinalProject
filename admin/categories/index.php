<?php
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../../auth/login.php");
    exit();
}

require_once '../../config/config.php';

// Fetch all categories
try {
    $stmt = $conn->prepare("SELECT c.*, COUNT(p.id) as product_count 
                           FROM categories c 
                           LEFT JOIN products p ON c.id = p.category_id 
                           GROUP BY c.id 
                           ORDER BY c.created_at DESC");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_OBJ);
} catch(PDOException $e) {
    error_log("Error: " . $e->getMessage());
    $categories = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories — Premeditatio Malorum</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,700;1,700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
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
        
        /* Category Card */
        .category-card { background: rgba(15,15,15,0.6); border: 1px solid rgba(255,255,255,0.06); border-radius: 16px; padding: 32px; backdrop-filter: blur(20px); transition: transform 0.4s, box-shadow 0.4s, border-color 0.4s; position: relative; display: flex; flex-direction: column; height: 100%; }
        .category-card:hover { transform: translateY(-4px); box-shadow: 0 20px 40px rgba(0,0,0,0.5); border-color: rgba(255,255,255,0.12); }
        .icon-box { width: 44px; height: 44px; border-radius: 10px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,0.8); margin-bottom: 24px; transition: transform 0.3s; }
        .category-card:hover .icon-box { transform: scale(1.05); background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); color: white; }

        /* Badge */
        .badge { display: inline-flex; align-items: center; justify-content: center; gap: 4px; padding: 6px 12px; border-radius: 30px; font-size: 0.65rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; }
        .badge-primary { background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.8); border: 1px solid rgba(255,255,255,0.15); }
        .badge-success { background: rgba(255,255,255,0.15); color: white; border: 1px solid rgba(255,255,255,0.3); }
        .badge-danger { background: transparent; color: rgba(255,255,255,0.4); border: 1px solid rgba(255,255,255,0.1); }
        
        /* Actions */
        .action-icon { color: rgba(255,255,255,0.3); transition: color 0.3s, transform 0.3s; display: inline-block; cursor: pointer; padding: 4px; }
        .action-icon:hover { color: white; transform: translateY(-2px); }

        .btn { border: none; outline: none; padding: 12px 28px; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.15em; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 12px; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
        .btn-primary { background: white; color: black; box-shadow: 0 4px 15px rgba(255,255,255,0.1); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(255,255,255,0.2); background: #f8f8f8; }

        ::-webkit-scrollbar { width: 8px; height: 8px;}
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }
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
            <div style="font-size: 1.25rem; font-weight: 700; color: white; letter-spacing: -0.02em;">Categories Management</div>
            <div style="font-size: 0.85rem; color: rgba(255,255,255,0.4); margin-top: 4px; font-weight: 500;">Organize your book collection</div>
        </div>
        <div style="display: flex; align-items: center; gap: 24px;">
            <button class="btn btn-primary" onclick="openAddModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width: 16px; height: 16px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> 
                Add Category
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

        <!-- Categories Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php if($categories): ?>
                <?php foreach($categories as $category): ?>
                    <div class="category-card">
                        <div class="flex justify-between items-start mb-2 border-b border-white/5 pb-5">
                            <div class="icon-box">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px;"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                            </div>
                            <div class="flex gap-3">
                                <a href="#" class="action-icon btn-edit" data-id="<?php echo $category->id; ?>" title="Edit">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 16px; height: 16px;"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                </a>
                                <a href="#" class="action-icon btn-delete" data-id="<?php echo $category->id; ?>" data-name="<?php echo htmlspecialchars($category->name, ENT_QUOTES); ?>" style="color: rgba(255,255,255,0.2);" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.2)'" title="Delete">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                </a>
                            </div>
                        </div>
                        <h5 class="text-white font-bold text-lg mb-2 serif" style="letter-spacing: -0.01em;"><?php echo htmlspecialchars($category->name); ?></h5>
                        <p class="text-white/40 text-sm mb-6 flex-1 line-clamp-2 leading-relaxed font-medium"><?php echo htmlspecialchars($category->description ?? 'No description provided'); ?></p>
                        
                        <div class="flex justify-between items-center mt-auto pt-4 border-t border-white/5">
                            <span class="badge badge-primary mono shadow-sm"><?php echo $category->product_count; ?> Titles</span>
                            <?php if(isset($category->status) && $category->status == 1): ?>
                                <span class="badge badge-success">Active</span>
                            <?php elseif(isset($category->status)): ?>
                                <span class="badge badge-danger">Inactive</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full border border-white/5 bg-black/40 rounded-2xl p-16 flex flex-col items-center justify-center text-center">
                    <svg viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.15)" stroke-width="1.5" class="w-16 h-16 mb-4"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                    <div class="text-white/40 text-sm uppercase tracking-widest font-bold">No categories found</div>
                </div>
            <?php endif; ?>
        </div>
        
    </div>
</div>
</div>

<!-- ===== ADD / EDIT MODAL ===== -->
<div id="catModal" style="display:none; position:fixed; inset:0; z-index:999; background:rgba(0,0,0,0.75); backdrop-filter:blur(8px); align-items:center; justify-content:center;">
    <div style="background:#0f0f0f; border:1px solid rgba(255,255,255,0.1); border-radius:20px; padding:40px; width:100%; max-width:480px; position:relative; box-shadow:0 40px 80px rgba(0,0,0,0.8);">
        <!-- Close -->
        <button onclick="closeModal()" style="position:absolute; top:20px; right:20px; background:rgba(255,255,255,0.05); border:none; color:rgba(255,255,255,0.4); width:32px; height:32px; border-radius:8px; cursor:pointer; font-size:1.1rem; display:flex; align-items:center; justify-content:center; transition:all 0.2s;" onmouseover="this.style.color='white';this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.color='rgba(255,255,255,0.4)';this.style.background='rgba(255,255,255,0.05)'">✕</button>
        
        <div style="margin-bottom:28px;">
            <div id="modalTitle" style="font-size:1.1rem; font-weight:700; color:white; letter-spacing:-0.01em; margin-bottom:4px;">Add Category</div>
            <div style="font-size:0.8rem; color:rgba(255,255,255,0.35);">Fill in the details below</div>
        </div>

        <input type="hidden" id="editId">

        <div style="margin-bottom:20px;">
            <label style="display:block; font-size:0.75rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:rgba(255,255,255,0.4); margin-bottom:10px;">Category Name *</label>
            <input id="catName" type="text" placeholder="e.g. Science Fiction" style="width:100%; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.1); border-radius:10px; padding:14px 18px; color:white; font-family:'Plus Jakarta Sans', sans-serif; font-size:0.9rem; outline:none; transition:border-color 0.2s;" onfocus="this.style.borderColor='rgba(255,255,255,0.3)'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'">
        </div>
        <div style="margin-bottom:28px;">
            <label style="display:block; font-size:0.75rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:rgba(255,255,255,0.4); margin-bottom:10px;">Description</label>
            <textarea id="catDesc" rows="3" placeholder="Short description of this category..." style="width:100%; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.1); border-radius:10px; padding:14px 18px; color:white; font-family:'Plus Jakarta Sans', sans-serif; font-size:0.9rem; outline:none; resize:vertical; transition:border-color 0.2s;" onfocus="this.style.borderColor='rgba(255,255,255,0.3)'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'"></textarea>
        </div>

        <div style="display:flex; gap:12px;">
            <button onclick="closeModal()" style="flex:1; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.08); color:rgba(255,255,255,0.6); padding:14px; border-radius:10px; font-family:'Plus Jakarta Sans',sans-serif; font-size:0.8rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; cursor:pointer; transition:all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.08)'" onmouseout="this.style.background='rgba(255,255,255,0.05)'">Cancel</button>
            <button onclick="submitModal()" id="modalSubmitBtn" style="flex:2; background:white; border:none; color:black; padding:14px; border-radius:10px; font-family:'Plus Jakarta Sans',sans-serif; font-size:0.8rem; font-weight:800; text-transform:uppercase; letter-spacing:0.12em; cursor:pointer; transition:all 0.2s; box-shadow:0 4px 20px rgba(255,255,255,0.15);" onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 8px 30px rgba(255,255,255,0.25)'" onmouseout="this.style.transform='';this.style.boxShadow='0 4px 20px rgba(255,255,255,0.15)'">Save Category</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .swal-border { border: 1px solid rgba(255,255,255,0.1) !important; border-radius: 16px !important; }
    #catModal.open { display: flex !important; animation: fadeIn 0.25s ease; }
    @keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
    #catModal > div { animation: slideUp 0.3s cubic-bezier(0.16,1,0.3,1); }
    @keyframes slideUp { from { transform:translateY(20px); opacity:0; } to { transform:translateY(0); opacity:1; } }
</style>
<script>
    const HANDLER = '<?php echo APPURL; ?>admin/categories/handler.php';
    const swalOpts = { background:'#111', color:'#fff', customClass:{popup:'swal-border'} };

    // ── Nav active ───────────────────────────────────────────────────────────
    document.querySelectorAll('.nav-item').forEach(item => {
        if(item.textContent.trim().includes('Categories')) item.classList.add('active');
        else item.classList.remove('active');
    });

    // ── Modal helpers ────────────────────────────────────────────────────────
    function openAddModal() {
        document.getElementById('modalTitle').textContent = 'Add New Category';
        document.getElementById('modalSubmitBtn').textContent = 'Create Category';
        document.getElementById('editId').value = '';
        document.getElementById('catName').value = '';
        document.getElementById('catDesc').value = '';
        document.getElementById('catModal').classList.add('open');
    }

    function openEditModal(id) {
        fetch(HANDLER, { method:'POST', body: new URLSearchParams({action:'get', id}) })
            .then(r => r.json())
            .then(res => {
                if (!res.success) return showError(res.message);
                document.getElementById('modalTitle').textContent = 'Edit Category';
                document.getElementById('modalSubmitBtn').textContent = 'Save Changes';
                document.getElementById('editId').value = res.data.id;
                document.getElementById('catName').value = res.data.name;
                document.getElementById('catDesc').value = res.data.description || '';
                document.getElementById('catModal').classList.add('open');
            });
    }

    function closeModal() {
        document.getElementById('catModal').classList.remove('open');
    }

    // Close on backdrop click
    document.getElementById('catModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });

    // ── Submit (create or update) ─────────────────────────────────────────────
    function submitModal() {
        const id   = document.getElementById('editId').value;
        const name = document.getElementById('catName').value.trim();
        const desc = document.getElementById('catDesc').value.trim();
        if (!name) { showError('Category name is required.'); return; }

        const action = id ? 'update' : 'create';
        const body   = new URLSearchParams({action, name, description: desc});
        if (id) body.append('id', id);

        const btn = document.getElementById('modalSubmitBtn');
        btn.disabled = true;
        btn.textContent = 'Saving...';

        fetch(HANDLER, { method:'POST', body })
            .then(r => r.json())
            .then(res => {
                btn.disabled = false;
                btn.textContent = id ? 'Save Changes' : 'Create Category';
                closeModal();
                if (res.success) {
                    Swal.fire({...swalOpts, icon:'success', title:'SUCCESS', text: res.message,
                        confirmButtonColor:'#fff',
                        confirmButtonText:'<span style="color:black;font-weight:800;font-family:\'Plus Jakarta Sans\',sans-serif;">OK</span>'
                    }).then(() => location.reload());
                } else {
                    showError(res.message);
                }
            })
            .catch(() => { btn.disabled = false; showError('Network error. Please try again.'); });
    }

    // ── Delete ────────────────────────────────────────────────────────────────
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const id   = this.dataset.id;
            const name = this.dataset.name;
            Swal.fire({
                ...swalOpts,
                title: 'DELETE CATEGORY',
                html: `<span style="color:rgba(255,255,255,0.5);font-size:0.9rem;">You are about to permanently delete<br><strong style="color:white;">${name}</strong></span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#fff',
                cancelButtonColor: '#2a2a2a',
                confirmButtonText: '<span style="color:black;font-weight:800;font-family:\'Plus Jakarta Sans\',sans-serif;">DELETE</span>',
                cancelButtonText:  '<span style="color:rgba(255,255,255,0.7);font-family:\'Plus Jakarta Sans\',sans-serif;">CANCEL</span>'
            }).then(result => {
                if (!result.isConfirmed) return;
                fetch(HANDLER, { method:'POST', body: new URLSearchParams({action:'delete', id}) })
                    .then(r => r.json())
                    .then(res => {
                        if (res.success) {
                            Swal.fire({...swalOpts, icon:'success', title:'DELETED', text: res.message,
                                confirmButtonColor:'#fff',
                                confirmButtonText:'<span style="color:black;font-weight:800;font-family:\'Plus Jakarta Sans\',sans-serif;">OK</span>'
                            }).then(() => location.reload());
                        } else {
                            showError(res.message);
                        }
                    });
            });
        });
    });

    // ── Edit buttons ──────────────────────────────────────────────────────────
    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            openEditModal(this.dataset.id);
        });
    });

    // ── Error helper ─────────────────────────────────────────────────────────
    function showError(msg) {
        Swal.fire({...swalOpts, icon:'error', title:'ERROR', text: msg,
            confirmButtonColor:'#fff',
            confirmButtonText:'<span style="color:black;font-weight:800;font-family:\'Plus Jakarta Sans\',sans-serif;">OK</span>'
        });
    }
</script>
</body>
</html>
