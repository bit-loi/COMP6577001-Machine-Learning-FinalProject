<?php
session_start();
require_once '../../config/config.php';
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../../auth/login.php'); exit();
}

// -------------------------------------------------------
// Helpers
// -------------------------------------------------------
function calcRisk(float $prob, float $revenue): array {
    if ($prob >= 0.75 && $revenue >= 100) return ['High', 'Send loyalty voucher'];
    elseif ($prob >= 0.75)               return ['High', 'Send reactivation email'];
    elseif ($prob >= 0.50)               return ['Medium', 'Send product recommendation'];
    else                                  return ['Low', 'No immediate action'];
}

// -------------------------------------------------------
// CSV Upload handler
// -------------------------------------------------------
$importMsg   = '';
$importType  = '';   // 'success' | 'error' | 'info'
$importStats = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $importMsg  = 'Upload failed. Please try again.';
        $importType = 'error';
    } elseif (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) !== 'csv') {
        $importMsg  = 'Only CSV files are accepted.';
        $importType = 'error';
    } else {
        $handle = fopen($file['tmp_name'], 'r');
        $header = fgetcsv($handle); // skip header row

        // Normalize header keys
        $header = array_map(fn($h) => strtolower(trim($h)), $header);

        $sql = "INSERT INTO churn_scores
                    (customer_id, snapshot_date, country, orders_last_window, revenue_last_window,
                     recency_days, customer_age_days, predicted_churn_probability, predicted_churn,
                     risk_level, recommended_action)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    country                      = VALUES(country),
                    orders_last_window           = VALUES(orders_last_window),
                    revenue_last_window          = VALUES(revenue_last_window),
                    recency_days                 = VALUES(recency_days),
                    customer_age_days            = VALUES(customer_age_days),
                    predicted_churn_probability  = VALUES(predicted_churn_probability),
                    predicted_churn              = VALUES(predicted_churn),
                    risk_level                   = VALUES(risk_level),
                    recommended_action           = VALUES(recommended_action)";

        $stmt     = $conn->prepare($sql);
        $inserted = $updated = $skipped = 0;
        $errors   = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 2) { $skipped++; continue; }
            $data = array_combine($header, $row);

            $custId   = trim($data['customer_id']                  ?? '');
            $snap     = trim($data['snapshot_date']                ?? date('Y-m-d'));
            $country  = trim($data['country']                      ?? '');
            $orders   = intval($data['orders_last_window']         ?? 0);
            $revenue  = floatval($data['revenue_last_window']      ?? 0);
            $recency  = intval($data['recency_days']               ?? 0);
            $age      = intval($data['customer_age_days']          ?? 0);
            $prob     = floatval($data['predicted_churn_probability'] ?? 0);
            $churn    = intval($data['predicted_churn']            ?? ($prob >= 0.5 ? 1 : 0));

            if (!$custId) { $skipped++; continue; }

            [$risk, $action] = calcRisk($prob, $revenue);

            try {
                $stmt->execute([$custId, $snap, $country, $orders, $revenue, $recency, $age, $prob, $churn, $risk, $action]);
                if ($stmt->rowCount() == 1) $inserted++;
                else $updated++;
            } catch (PDOException $e) {
                $errors[] = "Row $custId: " . $e->getMessage();
                $skipped++;
            }
        }
        fclose($handle);

        $importStats = compact('inserted', 'updated', 'skipped', 'errors');
        $importMsg   = "Import complete: {$inserted} new rows, {$updated} updated, {$skipped} skipped.";
        $importType  = empty($errors) ? 'success' : 'info';
    }
}

$pageTitle       = 'Import Churn Scores';
$pageDescription = 'Upload CSV output from ML model';
require_once '../includes/header.php';
?>

<!-- Breadcrumb -->
<div style="display:flex;align-items:center;gap:8px;margin-bottom:24px;font-size:0.82rem;color:#94a3b8;">
    <a href="<?php echo APPURL; ?>admin/churn/" style="color:#EE4D2D;text-decoration:none;font-weight:600;">Churn Prediction</a>
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
    <span>Import CSV</span>
</div>

<?php if ($importMsg): ?>
<div style="margin-bottom:24px;padding:16px 20px;border-radius:12px;font-size:0.875rem;font-weight:600;
    <?php echo match($importType) {
        'success' => 'background:#dcfce7;color:#166534;border:1px solid #bbf7d0;',
        'error'   => 'background:#fef2f2;color:#991b1b;border:1px solid #fecaca;',
        default   => 'background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe;',
    }; ?>
    display:flex;align-items:center;gap:10px;">
    <?php if($importType === 'success'): ?>
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
    <?php elseif($importType === 'error'): ?>
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
    <?php else: ?>
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <?php endif; ?>
    <?php echo htmlspecialchars($importMsg); ?>
</div>
<?php if(!empty($importStats['errors'])): ?>
<div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:12px 16px;margin-bottom:20px;font-size:0.78rem;color:#991b1b;">
    <?php foreach($importStats['errors'] as $e): ?><div>⚠ <?php echo htmlspecialchars($e); ?></div><?php endforeach; ?>
</div>
<?php endif; ?>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 380px;gap:24px;align-items:start;">

    <!-- Upload form -->
    <div class="table-card">
        <div style="font-size:0.875rem;font-weight:700;color:#0f172a;margin-bottom:20px;display:flex;align-items:center;gap:8px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#EE4D2D" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            Upload Churn Scores CSV
        </div>
        <form method="POST" enctype="multipart/form-data">
            <label style="display:block;margin-bottom:8px;font-size:0.8rem;font-weight:600;color:#475569;">Select CSV File</label>
            <div id="drop-zone" style="border:2px dashed #e2e8f0;border-radius:12px;padding:40px 20px;text-align:center;cursor:pointer;transition:all .2s;margin-bottom:20px;background:#fafafa;"
                ondragover="event.preventDefault();this.style.borderColor='#EE4D2D';this.style.background='#FFF4ED';"
                ondragleave="this.style.borderColor='#e2e8f0';this.style.background='#fafafa';"
                ondrop="handleDrop(event);"
                onclick="document.getElementById('csv_input').click();">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" style="margin:0 auto 12px;display:block;"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/></svg>
                <div id="drop-label" style="font-size:0.875rem;font-weight:600;color:#64748b;">Click to select or drag & drop CSV</div>
                <div style="font-size:0.72rem;color:#94a3b8;margin-top:4px;">Max 10MB • .csv only</div>
            </div>
            <input type="file" id="csv_input" name="csv_file" accept=".csv" style="display:none;" onchange="updateLabel(this);">

            <button type="submit" style="width:100%;padding:13px;background:#EE4D2D;color:#fff;border:none;border-radius:10px;font-size:0.875rem;font-weight:700;cursor:pointer;transition:background .15s;display:flex;align-items:center;justify-content:center;gap:8px;" onmouseover="this.style.background='#C53D20'" onmouseout="this.style.background='#EE4D2D'">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>
                Import to Database
            </button>
        </form>
    </div>

    <!-- CSV Format Guide -->
    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="table-card">
            <div style="font-size:0.875rem;font-weight:700;color:#0f172a;margin-bottom:16px;">Expected CSV Format</div>
            <div style="background:#0f172a;border-radius:10px;padding:16px;overflow-x:auto;">
                <pre style="margin:0;font-size:0.65rem;color:#7dd3fc;font-family:monospace;line-height:1.6;">customer_id,snapshot_date,country,
orders_last_window,
revenue_last_window,
recency_days,
customer_age_days,
predicted_churn_probability,
predicted_churn</pre>
            </div>
            <div style="margin-top:12px;font-size:0.75rem;color:#64748b;line-height:1.6;">
                <strong>Note:</strong> <code>risk_level</code> and <code>recommended_action</code> are <em>auto-calculated</em> by the system — you don't need to include them in the CSV.
            </div>
        </div>
        <div class="table-card">
            <div style="font-size:0.875rem;font-weight:700;color:#0f172a;margin-bottom:12px;">Risk Level Logic</div>
            <div style="display:flex;flex-direction:column;gap:8px;font-size:0.78rem;">
                <div style="display:flex;align-items:center;gap:10px;padding:8px 12px;background:#fef2f2;border-radius:8px;">
                    <span style="width:10px;height:10px;background:#ef4444;border-radius:50%;flex-shrink:0;"></span>
                    <div><strong>High</strong> (≥ 0.75) — Send voucher or reactivation email</div>
                </div>
                <div style="display:flex;align-items:center;gap:10px;padding:8px 12px;background:#fefce8;border-radius:8px;">
                    <span style="width:10px;height:10px;background:#eab308;border-radius:50%;flex-shrink:0;"></span>
                    <div><strong>Medium</strong> (0.50 – 0.74) — Send product recommendation</div>
                </div>
                <div style="display:flex;align-items:center;gap:10px;padding:8px 12px;background:#f0fdf4;border-radius:8px;">
                    <span style="width:10px;height:10px;background:#22c55e;border-radius:50%;flex-shrink:0;"></span>
                    <div><strong>Low</strong> (< 0.50) — No immediate action</div>
                </div>
            </div>
        </div>
        <a href="<?php echo APPURL; ?>admin/churn/seed_churn_demo.php" style="display:flex;align-items:center;justify-content:center;gap:8px;padding:12px;background:#f1f5f9;color:#475569;border-radius:10px;text-decoration:none;font-size:0.82rem;font-weight:600;transition:background .15s;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
            Load Demo Data Instead
        </a>
    </div>

</div>

<script>
function updateLabel(input) {
    const label = document.getElementById('drop-label');
    if (input.files.length > 0) {
        label.textContent = '✓ ' + input.files[0].name;
        label.style.color = '#EE4D2D';
    }
}
function handleDrop(e) {
    e.preventDefault();
    const zone = document.getElementById('drop-zone');
    zone.style.borderColor = '#e2e8f0';
    zone.style.background = '#fafafa';
    const dt = e.dataTransfer;
    const input = document.getElementById('csv_input');
    input.files = dt.files;
    updateLabel(input);
}
</script>

<?php require_once '../includes/footer.php'; ?>
