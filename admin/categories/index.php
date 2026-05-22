<?php
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../../auth/login.php");
    exit();
}

require_once '../../config/config.php';

// Generate CSRF token for category CRUD
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

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

$catIcons = [
  'electronics'      => ['icon'=>'smartphone', 'bg'=>'#EFF6FF', 'color'=>'#1D4ED8', 'border'=>'#BFDBFE'],
  'fashion'          => ['icon'=>'shirt',      'bg'=>'#FDF2F8', 'color'=>'#9D174D', 'border'=>'#FBCFE8'],
  'home-living'      => ['icon'=>'sofa',       'bg'=>'#F0FDF4', 'color'=>'#166534', 'border'=>'#BBF7D0'],
  'beauty'           => ['icon'=>'sparkles',   'bg'=>'#FFF1F2', 'color'=>'#BE123C', 'border'=>'#FECACA'],
  'sports'           => ['icon'=>'dumbbell',   'bg'=>'#FFF7ED', 'color'=>'#C2410C', 'border'=>'#FED7AA'],
  'books-stationery' => ['icon'=>'book-open',  'bg'=>'#FEFCE8', 'color'=>'#92400E', 'border'=>'#FEF08A'],
  'groceries'        => ['icon'=>'apple',      'bg'=>'#F0FDF4', 'color'=>'#15803D', 'border'=>'#BBF7D0'],
  'toys-games'       => ['icon'=>'gamepad-2',  'bg'=>'#F5F3FF', 'color'=>'#6D28D9', 'border'=>'#DDD6FE'],
];
?>
<?php
$pageTitle = 'Categories Management';
$pageDescription = 'Organize your product catalogue';
$topbarAction = '<button onclick="openAddModal()" class="bg-[#EE4D2D] text-white text-xs font-semibold px-4 py-2 rounded-lg flex items-center gap-2 hover:bg-[#D74226] transition-colors shadow-sm">
    <i data-lucide="plus" style="width:16px; height:16px;"></i> Add Category
</button>';
require_once '../includes/header.php';
?>

        <!-- Categories Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php if($categories): ?>
                <?php foreach($categories as $category): 
                    $slug = $category->slug ?? '';
                    $style = $catIcons[$slug] ?? ['icon'=>'package', 'bg'=>'#FFF4ED', 'color'=>'#EE4D2D', 'border'=>'#FFD9C6'];
                ?>
                    <div class="bg-white border border-gray-200 rounded-xl p-6 transition-all hover:shadow-lg flex flex-col h-full">
                        <div class="flex justify-between items-start mb-2 border-b border-gray-100 pb-5">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-6 transition-transform" style="background: <?php echo $style['bg']; ?>; border: 1px solid <?php echo $style['border']; ?>; color: <?php echo $style['color']; ?>;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-atom"><circle cx="12" cy="12" r="1"/><ellipse cx="12" cy="12" rx="3" ry="10"/><ellipse cx="12" cy="12" rx="3" ry="10" transform="rotate(60 12 12)"/><ellipse cx="12" cy="12" rx="3" ry="10" transform="rotate(120 12 12)"/></svg>
                            </div>
                            <div class="flex gap-2">
                                <a href="#" class="action-icon btn-edit" data-id="<?php echo $category->id; ?>" title="Edit">
                                    <i data-lucide="pencil" style="width: 16px; height: 16px;"></i>
                                </a>
                                <a href="#" class="action-icon btn-delete" data-id="<?php echo $category->id; ?>" data-name="<?php echo htmlspecialchars($category->name, ENT_QUOTES); ?>" style="color: #ef4444;" title="Delete">
                                    <i data-lucide="trash-2" style="width: 16px; height: 16px;"></i>
                                </a>
                            </div>
                        </div>
                        <h5 class="text-gray-900 font-bold text-lg mb-2"><?php echo htmlspecialchars($category->name); ?></h5>
                        <p class="text-gray-500 text-sm mb-6 flex-1 line-clamp-2 leading-relaxed font-medium"><?php echo htmlspecialchars($category->description ?? 'No description provided'); ?></p>
                        
                        <div class="flex justify-between items-center mt-auto pt-4 border-t border-gray-100">
                            <span class="badge badge-info shadow-sm"><?php echo $category->product_count; ?> Products</span>
                            <?php if(isset($category->status) && $category->status == 1): ?>
                                <span class="badge badge-success">Active</span>
                            <?php elseif(isset($category->status)): ?>
                                <span class="badge badge-danger">Inactive</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full border border-gray-200 bg-white rounded-2xl p-16 flex flex-col items-center justify-center text-center">
                    <i data-lucide="inbox" style="width: 64px; height: 64px; color: #cbd5e1; margin-bottom: 16px;"></i>
                    <div class="text-gray-400 text-sm uppercase tracking-widest font-bold">No categories found</div>
                </div>
            <?php endif; ?>
        </div>
        
    </div>
<?php require_once '../includes/footer.php'; ?>

<!-- ===== ADD / EDIT MODAL ===== -->
<div id="catModal" style="display:none; position:fixed; inset:0; z-index:999; background:rgba(0,0,0,0.5); backdrop-filter:blur(4px); align-items:center; justify-content:center;">
    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:32px; width:100%; max-width:480px; position:relative; box-shadow:0 20px 25px -5px rgba(0,0,0,0.1);">
        <!-- Close -->
        <button onclick="closeModal()" style="position:absolute; top:16px; right:16px; background:#f1f5f9; border:none; color:#64748b; width:32px; height:32px; border-radius:8px; cursor:pointer; font-size:1.1rem; display:flex; align-items:center; justify-content:center; transition:all 0.2s;" onmouseover="this.style.color='#0f172a';this.style.background='#e2e8f0'" onmouseout="this.style.color='#64748b';this.style.background='#f1f5f9'">✕</button>
        
        <div style="margin-bottom:24px;">
            <div id="modalTitle" style="font-size:1.1rem; font-weight:700; color:#0f172a; margin-bottom:4px;">Add Category</div>
            <div style="font-size:0.8rem; color:#64748b;">Fill in the details below</div>
        </div>

        <input type="hidden" id="editId">

        <div style="margin-bottom:20px;">
            <label style="display:block; font-size:0.75rem; font-weight:700; text-transform:uppercase; color:#475569; margin-bottom:8px;">Category Name *</label>
            <input id="catName" type="text" placeholder="e.g. Science Fiction" style="width:100%; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:12px 16px; color:#0f172a; font-family:'Inter', sans-serif; font-size:0.9rem; outline:none; transition:border-color 0.2s;" onfocus="this.style.borderColor='#EE4D2D'" onblur="this.style.borderColor='#e2e8f0'">
        </div>
        <div style="margin-bottom:28px;">
            <label style="display:block; font-size:0.75rem; font-weight:700; text-transform:uppercase; color:#475569; margin-bottom:8px;">Description</label>
            <textarea id="catDesc" rows="3" placeholder="Short description of this category..." style="width:100%; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:12px 16px; color:#0f172a; font-family:'Inter', sans-serif; font-size:0.9rem; outline:none; resize:vertical; transition:border-color 0.2s;" onfocus="this.style.borderColor='#EE4D2D'" onblur="this.style.borderColor='#e2e8f0'"></textarea>
        </div>

        <div style="display:flex; gap:12px;">
            <button onclick="closeModal()" style="flex:1; background:#f1f5f9; border:none; color:#475569; padding:12px; border-radius:10px; font-family:'Inter',sans-serif; font-size:0.8rem; font-weight:700; cursor:pointer; transition:all 0.2s;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">Cancel</button>
            <button onclick="submitModal()" id="modalSubmitBtn" style="flex:2; background:#EE4D2D; border:none; color:white; padding:12px; border-radius:10px; font-family:'Inter',sans-serif; font-size:0.8rem; font-weight:700; cursor:pointer; transition:all 0.2s;" onmouseover="this.style.background='#D74226'" onmouseout="this.style.background='#EE4D2D'">Save Category</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .swal-border { border-radius: 16px !important; }
    #catModal.open { display: flex !important; animation: fadeIn 0.25s ease; }
    @keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
    #catModal > div { animation: slideUp 0.3s cubic-bezier(0.16,1,0.3,1); }
    @keyframes slideUp { from { transform:translateY(20px); opacity:0; } to { transform:translateY(0); opacity:1; } }
</style>
<script>
    const HANDLER = '<?php echo APPURL; ?>admin/categories/handler.php';
    const CSRF_TOKEN = '<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>';
    const swalOpts = { background:'#fff', color:'#333', customClass:{popup:'swal-border'} };

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
        const body   = new URLSearchParams({action, name, description: desc, csrf_token: CSRF_TOKEN});
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
                        confirmButtonText:'<span style="color:black;font-weight:800;font-family:\'Inter\',sans-serif;">OK</span>'
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
                confirmButtonText: '<span style="color:black;font-weight:800;font-family:\'Inter\',sans-serif;">DELETE</span>',
                cancelButtonText:  '<span style="color:rgba(255,255,255,0.7);font-family:\'Inter\',sans-serif;">CANCEL</span>'
            }).then(result => {
                if (!result.isConfirmed) return;
                fetch(HANDLER, { method:'POST', body: new URLSearchParams({action:'delete', id, csrf_token: CSRF_TOKEN}) })
                    .then(r => r.json())
                    .then(res => {
                        if (res.success) {
                            Swal.fire({...swalOpts, icon:'success', title:'DELETED', text: res.message,
                                confirmButtonColor:'#fff',
                                confirmButtonText:'<span style="color:black;font-weight:800;font-family:\'Inter\',sans-serif;">OK</span>'
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
            confirmButtonColor:'#EE4D2D',
            confirmButtonText:'<span style="color:white;font-weight:700;font-family:\'Inter\',sans-serif;">OK</span>'
        });
    }
</script>
