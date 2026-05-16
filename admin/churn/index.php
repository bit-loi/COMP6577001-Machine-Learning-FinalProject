<?php
session_start();
require_once '../../config/config.php';
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../../auth/login.php'); exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'clear_all') {
    $conn->exec("SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE retention_actions; TRUNCATE TABLE churn_scores; SET FOREIGN_KEY_CHECKS = 1;");
    header('Location: index.php?msg=cleared');
    exit();
}

// Ensure tables exist
$conn->exec("CREATE TABLE IF NOT EXISTS churn_scores (
    id INT AUTO_INCREMENT PRIMARY KEY, customer_id VARCHAR(50) NOT NULL,
    snapshot_date DATE NOT NULL, country VARCHAR(100), orders_last_window INT,
    revenue_last_window DECIMAL(12,2), recency_days INT, customer_age_days INT,
    predicted_churn_probability DECIMAL(6,5), predicted_churn TINYINT,
    risk_level VARCHAR(20), recommended_action VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_customer_snapshot (customer_id, snapshot_date),
    INDEX idx_risk (risk_level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$conn->exec("CREATE TABLE IF NOT EXISTS retention_actions (
    id INT AUTO_INCREMENT PRIMARY KEY, customer_id VARCHAR(50) NOT NULL,
    churn_score_id INT NULL, action_type VARCHAR(100),
    action_status VARCHAR(50) DEFAULT 'done', admin_note TEXT,
    actioned_by VARCHAR(100), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_customer (customer_id),
    FOREIGN KEY (churn_score_id) REFERENCES churn_scores(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Filters
$riskFilter   = $_GET['risk']   ?? 'all';
$searchFilter = trim($_GET['search'] ?? '');
$latestSnap   = $conn->query("SELECT MAX(snapshot_date) as d FROM churn_scores")->fetch(PDO::FETCH_OBJ)->d ?? null;

// Summary counts
$counts = $conn->query("SELECT
    COUNT(*) as total,
    SUM(risk_level='High') as high_c,
    SUM(risk_level='Medium') as med_c,
    SUM(risk_level='Low') as low_c
    FROM churn_scores")->fetch(PDO::FETCH_OBJ);

// Build filtered query
$where = "WHERE 1=1";
$params = [];
if ($riskFilter !== 'all') { $where .= " AND risk_level = ?"; $params[] = $riskFilter; }
if ($searchFilter)         { $where .= " AND customer_id LIKE ?"; $params[] = "%$searchFilter%"; }
$stmt = $conn->prepare("SELECT cs.*,
    (SELECT GROUP_CONCAT(action_type ORDER BY created_at DESC SEPARATOR ',') FROM retention_actions ra WHERE ra.customer_id = cs.customer_id LIMIT 1) as past_actions
    FROM churn_scores cs $where ORDER BY predicted_churn_probability DESC");
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_OBJ);

// Chart data
$distData = $conn->query("SELECT
    SUM(predicted_churn_probability < 0.25) as d1,
    SUM(predicted_churn_probability >= 0.25 AND predicted_churn_probability < 0.50) as d2,
    SUM(predicted_churn_probability >= 0.50 AND predicted_churn_probability < 0.75) as d3,
    SUM(predicted_churn_probability >= 0.75) as d4
    FROM churn_scores")->fetch(PDO::FETCH_OBJ);

$pageTitle = 'Customer Retention Intelligence';
$pageDescription = 'Batch Churn Prediction · Decision Support System';
require_once '../includes/header.php';
?>
<style>
.risk-high   { background:#fef2f2;color:#dc2626;border:1px solid #fecaca; }
.risk-medium { background:#fefce8;color:#ca8a04;border:1px solid #fef08a; }
.risk-low    { background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0; }
.pill { display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:0.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em; }
.action-btn { display:inline-flex;align-items:center;gap:5px;padding:5px 10px;border-radius:7px;font-size:0.72rem;font-weight:600;border:1px solid #e2e8f0;background:#fff;color:#475569;cursor:pointer;transition:all .15s; }
.action-btn:hover { border-color:#EE4D2D;color:#EE4D2D;background:#FFF4ED; }
.action-btn.done { background:#f0fdf4;color:#16a34a;border-color:#bbf7d0; pointer-events:none; }
.stat-mini { background:#fff;border:1px solid #f1f5f9;border-radius:14px;padding:18px 20px;display:flex;align-items:center;gap:14px; }
.stat-mini-icon { width:42px;height:42px;border-radius:11px;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.filter-btn { display:inline-flex;align-items:center;gap:6px;padding:7px 16px;border-radius:8px;font-size:0.78rem;font-weight:600;border:1px solid #e2e8f0;background:#fff;color:#64748b;cursor:pointer;transition:all .15s;text-decoration:none; }
.filter-btn:hover,.filter-btn.active { background:#EE4D2D;color:#fff;border-color:#EE4D2D; }
</style>

<!-- Summary Cards -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px;">
    <div class="stat-mini">
        <div class="stat-mini-icon" style="background:#eff6ff;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div><div style="font-size:1.5rem;font-weight:800;color:#0f172a;"><?php echo $counts->total ?? 0; ?></div><div style="font-size:0.72rem;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Total Scored</div></div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon" style="background:#fef2f2;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        </div>
        <div><div style="font-size:1.5rem;font-weight:800;color:#dc2626;"><?php echo $counts->high_c ?? 0; ?></div><div style="font-size:0.72rem;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">High Risk</div></div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon" style="background:#fefce8;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ca8a04" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <div><div style="font-size:1.5rem;font-weight:800;color:#ca8a04;"><?php echo $counts->med_c ?? 0; ?></div><div style="font-size:0.72rem;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Medium Risk</div></div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon" style="background:#f0fdf4;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div><div style="font-size:1.5rem;font-weight:800;color:#16a34a;"><?php echo $counts->low_c ?? 0; ?></div><div style="font-size:0.72rem;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Low Risk</div></div>
    </div>
</div>

<!-- Charts -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px;">
    <div class="table-card">
        <div style="font-size:0.85rem;font-weight:700;color:#0f172a;margin-bottom:16px;">Risk Level Distribution</div>
        <div style="height:220px;"><canvas id="donutChart"></canvas></div>
    </div>
    <div class="table-card">
        <div style="font-size:0.85rem;font-weight:700;color:#0f172a;margin-bottom:16px;">Churn Probability Distribution</div>
        <div style="height:220px;"><canvas id="barChart"></canvas></div>
    </div>
</div>

<!-- Filter + Table -->
<div class="table-card">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <span style="font-size:0.85rem;font-weight:700;color:#0f172a;">Customer Risk Table</span>
            <?php if($latestSnap): ?><span style="font-size:0.72rem;color:#94a3b8;background:#f1f5f9;padding:3px 8px;border-radius:20px;">Snapshot: <?php echo $latestSnap; ?></span><?php endif; ?>
        </div>
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <!-- Search -->
            <form method="GET" style="display:flex;gap:6px;">
                <input type="hidden" name="risk" value="<?php echo htmlspecialchars($riskFilter); ?>">
                <input type="text" name="search" value="<?php echo htmlspecialchars($searchFilter); ?>" placeholder="Search Customer ID…" style="padding:7px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:0.78rem;font-family:inherit;outline:none;width:180px;" onfocus="this.style.borderColor='#EE4D2D'" onblur="this.style.borderColor='#e2e8f0'">
                <button type="submit" style="padding:7px 12px;background:#EE4D2D;color:#fff;border:none;border-radius:8px;font-size:0.78rem;font-weight:600;cursor:pointer;">Search</button>
            </form>
            <!-- Risk filter -->
            <a href="?risk=all&search=<?php echo urlencode($searchFilter); ?>" class="filter-btn <?php echo $riskFilter==='all'?'active':''; ?>">
                All
            </a>
            <a href="?risk=High&search=<?php echo urlencode($searchFilter); ?>" class="filter-btn <?php echo $riskFilter==='High'?'active':''; ?>">
                <span style="width:8px;height:8px;border-radius:50%;background:#ef4444;box-shadow:0 0 0 2px #fca5a5;"></span> High
            </a>
            <a href="?risk=Medium&search=<?php echo urlencode($searchFilter); ?>" class="filter-btn <?php echo $riskFilter==='Medium'?'active':''; ?>">
                <span style="width:8px;height:8px;border-radius:50%;background:#eab308;box-shadow:0 0 0 2px #fde047;"></span> Medium
            </a>
            <a href="?risk=Low&search=<?php echo urlencode($searchFilter); ?>" class="filter-btn <?php echo $riskFilter==='Low'?'active':''; ?>">
                <span style="width:8px;height:8px;border-radius:50%;background:#22c55e;box-shadow:0 0 0 2px #86efac;"></span> Low
            </a>
            <a href="<?php echo APPURL; ?>admin/churn/import_churn.php" style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;background:#0f172a;color:#fff;border-radius:8px;font-size:0.78rem;font-weight:600;text-decoration:none;">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                Import CSV
            </a>
            <!-- Clear Data Button -->
            <form method="POST" onsubmit="return confirm('Are you sure you want to delete all churn data? This action cannot be undone.');" style="margin:0;">
                <input type="hidden" name="action" value="clear_all">
                <button type="submit" style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;background:#ef4444;color:#fff;border:none;border-radius:8px;font-size:0.78rem;font-weight:600;cursor:pointer;transition:background .2s;" onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='#ef4444'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    Clear All
                </button>
            </form>
        </div>
    </div>

    <?php if($rows): ?>
    <div style="overflow-x:auto;">
    <table class="data-table" style="min-width:1000px;">
        <thead><tr>
            <th>Customer ID</th><th>Country</th><th>Orders</th><th>Revenue</th>
            <th>Recency</th><th>Probability</th><th>Risk</th><th>Recommended Action</th><th>Actions</th>
        </tr></thead>
        <tbody>
        <?php foreach($rows as $r):
            $prob = floatval($r->predicted_churn_probability);
            $pct  = round($prob * 100, 1);
            $barColor = $r->risk_level === 'High' ? '#ef4444' : ($r->risk_level === 'Medium' ? '#eab308' : '#22c55e');
            $pastActions = array_filter(explode(',', $r->past_actions ?? ''));
        ?>
        <tr id="row-<?php echo htmlspecialchars($r->customer_id); ?>">
            <td><span style="font-family:monospace;font-weight:700;color:#0f172a;"><?php echo htmlspecialchars($r->customer_id); ?></span></td>
            <td style="color:#64748b;"><?php echo htmlspecialchars($r->country ?? '—'); ?></td>
            <td style="font-weight:600;"><?php echo $r->orders_last_window ?? 0; ?></td>
            <td style="font-weight:600;color:#0f172a;">$<?php echo number_format($r->revenue_last_window ?? 0, 2); ?></td>
            <td style="color:#64748b;"><?php echo $r->recency_days ?? '—'; ?>d</td>
            <td>
                <div style="display:flex;align-items:center;gap:8px;">
                    <div style="flex:1;height:6px;background:#f1f5f9;border-radius:3px;min-width:60px;">
                        <div style="width:<?php echo $pct; ?>%;height:100%;background:<?php echo $barColor; ?>;border-radius:3px;"></div>
                    </div>
                    <span style="font-weight:700;font-size:0.8rem;color:<?php echo $barColor; ?>;min-width:36px;"><?php echo $pct; ?>%</span>
                </div>
            </td>
            <td>
                <span class="pill risk-<?php echo strtolower($r->risk_level); ?>">
                    <?php echo $r->risk_level; ?>
                </span>
            </td>
            <td style="font-size:0.8rem;color:#475569;max-width:200px;"><?php echo htmlspecialchars($r->recommended_action ?? '—'); ?></td>
            <td>
                <div style="display:flex;gap:5px;flex-wrap:wrap;" id="actions-<?php echo htmlspecialchars($r->customer_id); ?>">
                    <?php
                    $actionDefs = [
                        'voucher_sent'      => ['label'=>'Voucher Sent',  'icon'=>'M20 12V22H4V12', 'icon2'=>'m3 7 9-4 9 4'],
                        'email_sent'        => ['label'=>'Email Sent',    'icon'=>'M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z', 'icon2'=>'m22 6-10 7L2 6'],
                        'contacted'         => ['label'=>'Contacted',     'icon'=>'M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z', 'icon2'=>''],
                        'customer_returned' => ['label'=>'Returned ✓',    'icon'=>'M22 11.08V12a10 10 0 1 1-5.93-9.14', 'icon2'=>'22 4 12 14.01 9 11.01'],
                    ];
                    foreach($actionDefs as $atype => $adef):
                        $done = in_array($atype, $pastActions);
                    ?>
                    <button class="action-btn <?php echo $done?'done':''; ?>"
                        onclick="recordAction('<?php echo htmlspecialchars($r->customer_id); ?>', <?php echo $r->id; ?>, '<?php echo $atype; ?>', this)"
                        title="<?php echo $adef['label']; ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="<?php echo $adef['icon']; ?>"/>
                            <?php if($adef['icon2']): ?><polyline points="<?php echo $adef['icon2']; ?>"/><?php endif; ?>
                        </svg>
                        <?php echo $done ? '✓ ' : ''; ?><?php echo $adef['label']; ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php else: ?>
    <div style="text-align:center;padding:60px 20px;color:#94a3b8;">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#e2e8f0" stroke-width="1.5" style="margin:0 auto 16px;display:block;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        <div style="font-size:0.95rem;font-weight:600;color:#64748b;margin-bottom:8px;">No churn scores found</div>
        <div style="font-size:0.8rem;margin-bottom:20px;">Run the demo seeder or import a CSV to get started.</div>
        <div style="display:flex;gap:10px;justify-content:center;">
            <a href="<?php echo APPURL; ?>admin/churn/seed_churn_demo.php" style="display:inline-flex;align-items:center;gap:6px;padding:10px 20px;background:#EE4D2D;color:#fff;border-radius:8px;font-size:0.82rem;font-weight:700;text-decoration:none;">Load Demo Data</a>
            <a href="<?php echo APPURL; ?>admin/churn/import_churn.php" style="display:inline-flex;align-items:center;gap:6px;padding:10px 20px;background:#f1f5f9;color:#475569;border-radius:8px;font-size:0.82rem;font-weight:700;text-decoration:none;">Import CSV</a>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Notification toast -->
<div id="toast" style="position:fixed;bottom:24px;right:24px;padding:12px 20px;border-radius:10px;font-size:0.82rem;font-weight:600;color:#fff;background:#16a34a;box-shadow:0 8px 24px rgba(0,0,0,0.15);transform:translateY(80px);opacity:0;transition:all .3s;z-index:9999;display:flex;align-items:center;gap:8px;"></div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Donut chart
new Chart(document.getElementById('donutChart'), {
    type: 'doughnut',
    data: {
        labels: ['High Risk', 'Medium Risk', 'Low Risk'],
        datasets: [{ data: [<?php echo ($counts->high_c??0).','.(($counts->med_c??0)).','.(($counts->low_c??0)); ?>],
            backgroundColor: ['#ef4444','#eab308','#22c55e'], borderWidth: 0, borderRadius: 4 }]
    },
    options: { responsive:true, maintainAspectRatio:false, cutout:'65%',
        plugins: { legend: { position:'bottom', labels:{ color:'#64748b', font:{size:11}, padding:16, boxWidth:12 } } } }
});
// Bar chart (probability distribution)
new Chart(document.getElementById('barChart'), {
    type: 'bar',
    data: {
        labels: ['0–0.25\n(Very Low)', '0.25–0.50\n(Low)', '0.50–0.75\n(Medium)', '0.75–1.00\n(High)'],
        datasets: [{ label: 'Customers', data: [<?php echo ($distData->d1??0).','.(($distData->d2??0)).','.(($distData->d3??0)).','.(($distData->d4??0)); ?>],
            backgroundColor: ['#86efac','#fde047','#fb923c','#f87171'],
            borderRadius: 6, borderSkipped: false }]
    },
    options: { responsive:true, maintainAspectRatio:false,
        plugins:{ legend:{display:false} },
        scales:{ y:{ beginAtZero:true, grid:{color:'#f1f5f9'}, ticks:{color:'#94a3b8',font:{size:11}} },
                 x:{ grid:{display:false}, ticks:{color:'#94a3b8',font:{size:10}} } } }
});

// Action recording
function recordAction(customerId, scoreId, actionType, btn) {
    if (btn.classList.contains('done')) return;
    btn.disabled = true;
    btn.style.opacity = '0.6';
    fetch('<?php echo APPURL; ?>admin/churn/action_handler.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: `customer_id=${encodeURIComponent(customerId)}&churn_score_id=${scoreId}&action_type=${encodeURIComponent(actionType)}&admin_note=`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            btn.classList.add('done');
            btn.style.opacity = '1';
            btn.disabled = false;
            showToast('✓ ' + data.message);
        } else {
            btn.disabled = false; btn.style.opacity='1';
            showToast('⚠ ' + data.message, '#ef4444');
        }
    })
    .catch(() => { btn.disabled=false; btn.style.opacity='1'; showToast('Network error','#ef4444'); });
}

function showToast(msg, color='#16a34a') {
    const t = document.getElementById('toast');
    t.textContent = msg; t.style.background = color;
    t.style.transform='translateY(0)'; t.style.opacity='1';
    setTimeout(() => { t.style.transform='translateY(80px)'; t.style.opacity='0'; }, 3000);
}
</script>

<?php require_once '../includes/footer.php'; ?>
