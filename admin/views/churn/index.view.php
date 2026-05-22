<?php
/**
 * Churn Index View Template
 * 
 * Renders the churn prediction list dashboard.
 * Expects variables: $latestSnap, $counts, $rows, $riskFilter, $searchFilter
 */
?>
<link rel="stylesheet" href="<?php echo APPURL; ?>admin/assets/css/admin-components.css">

<!-- Summary Cards -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px;">
    <div class="stat-mini">
        <div class="stat-mini-icon" style="background:#eff6ff;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div>
            <div style="font-size:1.5rem;font-weight:800;color:#0f172a;"><?php echo $counts->total ?? 0; ?></div>
            <div style="font-size:0.72rem;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Total Scored</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon" style="background:#fef2f2;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        </div>
        <div>
            <div style="font-size:1.5rem;font-weight:800;color:#dc2626;"><?php echo $counts->high_c ?? 0; ?></div>
            <div style="font-size:0.72rem;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Critical</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon" style="background:#fefce8;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ca8a04" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <div>
            <div style="font-size:1.5rem;font-weight:800;color:#ca8a04;"><?php echo $counts->med_c ?? 0; ?></div>
            <div style="font-size:0.72rem;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">At Risk</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon" style="background:#f0fdf4;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div>
            <div style="font-size:1.5rem;font-weight:800;color:#16a34a;"><?php echo $counts->low_c ?? 0; ?></div>
            <div style="font-size:0.72rem;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Loyal</div>
        </div>
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
            <?php if($latestSnap): ?><span style="font-size:0.72rem;color:#94a3b8;background:#f1f5f9;padding:3px 8px;border-radius:20px;">Snapshot: <?php echo htmlspecialchars($latestSnap); ?></span><?php endif; ?>
        </div>
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <!-- Search -->
            <form method="GET" style="display:flex;gap:6px;" id="searchForm">
                <input type="hidden" name="risk" value="<?php echo htmlspecialchars($riskFilter); ?>">
                <input type="text" name="search" id="searchInput" value="<?php echo htmlspecialchars($searchFilter); ?>" placeholder="Search Customer ID…" style="padding:7px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:0.78rem;font-family:inherit;outline:none;width:180px;" onfocus="this.style.borderColor='#EE4D2D'" onblur="this.style.borderColor='#e2e8f0'">
                <button type="submit" id="searchBtn" style="padding:7px 12px;background:#EE4D2D;color:#fff;border:none;border-radius:8px;font-size:0.78rem;font-weight:600;cursor:pointer;transition:background 0.2s;">Search</button>
            </form>
            <!-- Risk filter -->
            <a href="?risk=all&search=<?php echo urlencode($searchFilter); ?>" class="filter-btn <?php echo $riskFilter==='all'?'active':''; ?>">
                All
            </a>
            <a href="?risk=Critical&search=<?php echo urlencode($searchFilter); ?>" class="filter-btn <?php echo $riskFilter==='Critical'?'active':''; ?>">
                <span style="width:8px;height:8px;border-radius:50%;background:#ef4444;box-shadow:0 0 0 2px #fca5a5;"></span> Critical
            </a>
            <a href="?risk=At+Risk&search=<?php echo urlencode($searchFilter); ?>" class="filter-btn <?php echo $riskFilter==='At Risk'?'active':''; ?>">
                <span style="width:8px;height:8px;border-radius:50%;background:#eab308;box-shadow:0 0 0 2px #fde047;"></span> At Risk
            </a>
            <a href="?risk=Loyal&search=<?php echo urlencode($searchFilter); ?>" class="filter-btn <?php echo $riskFilter==='Loyal'?'active':''; ?>">
                <span style="width:8px;height:8px;border-radius:50%;background:#22c55e;box-shadow:0 0 0 2px #86efac;"></span> Loyal
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
            $barColor = $r->risk_level === 'Critical' ? '#ef4444' : ($r->risk_level === 'At Risk' ? '#eab308' : '#22c55e');
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
                <span class="pill risk-<?php echo strtolower(str_replace(' ', '-', $r->risk_level)); ?>">
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
<div id="toast" class="toast"></div>

<!-- Chart & Action Config -->
<script>
window.churnChartData = {
    counts: <?php echo json_encode($counts); ?>,
    distData: <?php echo json_encode($distData); ?>
};
window.churnConfig = {
    actionHandlerUrl: '<?php echo APPURL; ?>admin/churn/action_handler.php'
};
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?php echo APPURL; ?>admin/assets/js/churn-charts.js"></script>
<script src="<?php echo APPURL; ?>admin/assets/js/churn-actions.js"></script>
