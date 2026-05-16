<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../auth/login.php"); exit();
}
require_once '../config/config.php';
$pageTitle = 'Fraud Detection';
$pageDescription = 'Manual Transaction Risk Check';
require_once 'includes/header.php';
?>

<style>
.input-group { margin-bottom: 20px; }
.input-label { display: block; font-size: 0.78rem; font-weight: 700; color: #475569; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em; }
.input-field { width: 100%; padding: 11px 14px; border: 1.5px solid #e2e8f0; border-radius: 10px; font-size: 0.875rem; font-family: 'Inter', sans-serif; color: #0f172a; outline: none; transition: border-color .2s, box-shadow .2s; background: #fafafa; }
.input-field:focus { border-color: #EE4D2D; box-shadow: 0 0 0 3px rgba(238,77,45,0.1); background: #fff; }
.input-hint { font-size: 0.7rem; color: #94a3b8; margin-top: 4px; }
.result-box { display: none; border-radius: 16px; padding: 28px; margin-top: 24px; }
.result-normal { background: linear-gradient(135deg, #f0fdf4, #dcfce7); border: 1.5px solid #86efac; }
.result-fraud  { background: linear-gradient(135deg, #fef2f2, #fee2e2); border: 1.5px solid #fca5a5; }
.result-title { font-size: 1.5rem; font-weight: 900; margin-bottom: 8px; }
.result-title.normal { color: #15803d; }
.result-title.fraud  { color: #dc2626; }
.metric-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-top: 20px; }
.metric-card { background: rgba(255,255,255,0.7); border-radius: 12px; padding: 16px; text-align: center; }
.metric-value { font-size: 1.5rem; font-weight: 800; color: #0f172a; }
.metric-label { font-size: 0.7rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 4px; }
.submit-btn { width: 100%; padding: 14px; background: #EE4D2D; color: #fff; border: none; border-radius: 12px; font-size: 0.9rem; font-weight: 700; cursor: pointer; font-family: 'Inter', sans-serif; transition: background .2s, transform .15s; display: flex; align-items: center; justify-content: center; gap: 8px; }
.submit-btn:hover { background: #C53D20; }
.submit-btn:active { transform: scale(0.98); }
.submit-btn:disabled { background: #cbd5e1; cursor: not-allowed; }
.score-bar-wrap { height: 10px; background: #e2e8f0; border-radius: 5px; overflow: hidden; margin-top: 6px; }
.score-bar { height: 100%; border-radius: 5px; transition: width 1s ease; }
</style>

<div style="display:grid;grid-template-columns:1fr 420px;gap:24px;align-items:start;">

    <!-- LEFT: Form -->
    <div class="table-card">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
            <div style="width:44px;height:44px;background:#fef2f2;border-radius:12px;display:flex;align-items:center;justify-content:center;">
                <i data-lucide="shield-alert" style="width:22px;height:22px;color:#dc2626;"></i>
            </div>
            <div>
                <div style="font-size:0.95rem;font-weight:800;color:#0f172a;">Manual Fraud Check</div>
                <div style="font-size:0.75rem;color:#94a3b8;">Masukkan detail transaksi untuk dicek oleh model Isolation Forest</div>
            </div>
        </div>

        <form id="fraud-form">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="input-group">
                    <label class="input-label">Transaction Amount ($) *</label>
                    <input type="number" class="input-field" id="inp-amount" placeholder="e.g. 150.00" step="0.01" min="0" required>
                    <div class="input-hint">Jumlah nominal transaksi</div>
                </div>
                <div class="input-group">
                    <label class="input-label">Card Type *</label>
                    <select class="input-field" id="inp-card4">
                        <option value="1">Visa</option>
                        <option value="2">Mastercard</option>
                        <option value="3">American Express</option>
                        <option value="4">Discover</option>
                    </select>
                </div>
                <div class="input-group">
                    <label class="input-label">Card Category *</label>
                    <select class="input-field" id="inp-card6">
                        <option value="1">Credit</option>
                        <option value="2">Debit</option>
                    </select>
                </div>
                <div class="input-group">
                    <label class="input-label">Card Number (Card1) *</label>
                    <input type="number" class="input-field" id="inp-card1" placeholder="e.g. 10486" min="0" max="18396" required>
                    <div class="input-hint">Encoded card identifier (0 - 18396)</div>
                </div>
                <div class="input-group">
                    <label class="input-label">Billing ZIP Code (addr1)</label>
                    <input type="number" class="input-field" id="inp-addr1" placeholder="e.g. 315" min="0" max="600">
                    <div class="input-hint">Encoded billing address</div>
                </div>
                <div class="input-group">
                    <label class="input-label">Billing Country (addr2)</label>
                    <input type="number" class="input-field" id="inp-addr2" placeholder="e.g. 87" min="0" max="300" step="0.1">
                    <div class="input-hint">Encoded country code</div>
                </div>
                <div class="input-group">
                    <label class="input-label">Purchaser Email Domain</label>
                    <select class="input-field" id="inp-pemail">
                        <option value="16">gmail.com</option>
                        <option value="11">yahoo.com</option>
                        <option value="3">outlook.com</option>
                        <option value="25">hotmail.com</option>
                        <option value="50">Anonymous / Rare Domain</option>
                    </select>
                </div>
                <div class="input-group">
                    <label class="input-label">Recipient Email Domain</label>
                    <select class="input-field" id="inp-remail">
                        <option value="16">gmail.com</option>
                        <option value="11">yahoo.com</option>
                        <option value="0">None (No recipient)</option>
                        <option value="50">Anonymous / Rare Domain</option>
                    </select>
                </div>
            </div>

            <!-- Quick presets -->
            <div style="margin-bottom:20px;">
                <div style="font-size:0.72rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:10px;">Quick Presets</div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <button type="button" onclick="loadPreset('normal')" style="padding:6px 14px;background:#f0fdf4;color:#15803d;border:1.5px solid #86efac;border-radius:8px;font-size:0.75rem;font-weight:700;cursor:pointer;">✓ Normal Transaction ($80)</button>
                    <button type="button" onclick="loadPreset('suspicious')" style="padding:6px 14px;background:#fefce8;color:#854d0e;border:1.5px solid #fde047;border-radius:8px;font-size:0.75rem;font-weight:700;cursor:pointer;">⚠ Suspicious ($2,500)</button>
                    <button type="button" onclick="loadPreset('fraud')" style="padding:6px 14px;background:#fef2f2;color:#dc2626;border:1.5px solid #fca5a5;border-radius:8px;font-size:0.75rem;font-weight:700;cursor:pointer;">✗ High Risk ($15,000)</button>
                </div>
            </div>

            <button type="submit" class="submit-btn" id="check-btn">
                <i data-lucide="shield" style="width:18px;height:18px;"></i>
                <span id="btn-text">Run Fraud Detection</span>
            </button>
        </form>

        <!-- Result -->
        <div id="result-box" class="result-box">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                <div id="result-icon" style="width:48px;height:48px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.5rem;"></div>
                <div>
                    <div id="result-title" class="result-title"></div>
                    <div id="result-subtitle" style="font-size:0.82rem;color:#475569;"></div>
                </div>
            </div>
            <div class="metric-row">
                <div class="metric-card">
                    <div class="metric-value" id="m-score">—</div>
                    <div class="metric-label">Anomaly Score</div>
                    <div class="score-bar-wrap"><div class="score-bar" id="score-bar" style="width:0%;background:#ef4444;"></div></div>
                </div>
                <div class="metric-card">
                    <div class="metric-value" id="m-verdict">—</div>
                    <div class="metric-label">Verdict</div>
                </div>
                <div class="metric-card">
                    <div class="metric-value" id="m-action">—</div>
                    <div class="metric-label">Action Required</div>
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT: Guide -->
    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="table-card">
            <div style="font-size:0.85rem;font-weight:700;color:#0f172a;margin-bottom:14px;display:flex;align-items:center;gap:8px;">
                <i data-lucide="info" style="width:16px;height:16px;color:#3b82f6;"></i> How It Works
            </div>
            <div style="font-size:0.8rem;color:#475569;line-height:1.8;">
                <div style="display:flex;gap:10px;margin-bottom:10px;"><span style="font-weight:800;color:#EE4D2D;min-width:20px;">1</span> Input detail transaksi di form kiri</div>
                <div style="display:flex;gap:10px;margin-bottom:10px;"><span style="font-weight:800;color:#EE4D2D;min-width:20px;">2</span> Data dikirim ke <code style="background:#f1f5f9;padding:1px 5px;border-radius:4px;">Flask API /predict</code></div>
                <div style="display:flex;gap:10px;margin-bottom:10px;"><span style="font-weight:800;color:#EE4D2D;min-width:20px;">3</span> Model Isolation Forest memproses 224 fitur</div>
                <div style="display:flex;gap:10px;"><span style="font-weight:800;color:#EE4D2D;min-width:20px;">4</span> Hasil ditampilkan: Normal atau Anomali</div>
            </div>
        </div>

        <div class="table-card">
            <div style="font-size:0.85rem;font-weight:700;color:#0f172a;margin-bottom:14px;">Score Interpretation</div>
            <div style="display:flex;flex-direction:column;gap:10px;font-size:0.78rem;">
                <div style="display:flex;align-items:center;gap:10px;padding:10px;background:#f0fdf4;border-radius:8px;border:1px solid #bbf7d0;">
                    <span style="font-size:1.1rem;">✅</span>
                    <div><strong>Normal Transaction</strong><br><span style="color:#64748b;">Model memprediksikan transaksi sah</span></div>
                </div>
                <div style="display:flex;align-items:center;gap:10px;padding:10px;background:#fefce8;border-radius:8px;border:1px solid #fde047;">
                    <span style="font-size:1.1rem;">⚠️</span>
                    <div><strong>Borderline</strong><br><span style="color:#64748b;">Perlu verifikasi tambahan</span></div>
                </div>
                <div style="display:flex;align-items:center;gap:10px;padding:10px;background:#fef2f2;border-radius:8px;border:1px solid #fecaca;">
                    <span style="font-size:1.1rem;">🚨</span>
                    <div><strong>Anomaly Detected</strong><br><span style="color:#64748b;">Pola mencurigakan terdeteksi oleh Isolation Forest</span></div>
                </div>
            </div>
        </div>

        <div class="table-card" style="background:#0f172a;border-color:#1e293b;">
            <div style="font-size:0.72rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:12px;">Model Info</div>
            <div style="font-size:0.78rem;color:#94a3b8;line-height:1.8;">
                <div><span style="color:#38bdf8;">Algorithm:</span> Isolation Forest</div>
                <div><span style="color:#38bdf8;">Dataset:</span> IEEE-CIS Fraud Detection</div>
                <div><span style="color:#38bdf8;">Features:</span> 224 transaction features</div>
                <div><span style="color:#38bdf8;">API:</span> <span style="color:#4ade80;">localhost:5000/predict</span></div>
            </div>
        </div>
    </div>
</div>

<script>
function loadPreset(type) {
    const presets = {
        normal: { amount: 80, card1: 14567, card4: '1', card6: '1', addr1: 350, addr2: 87, pemail: '16', remail: '16' },
        suspicious: { amount: 2500, card1: 1200, card4: '4', card6: '2', addr1: 25, addr2: 20, pemail: '50', remail: '50' },
        fraud: { amount: 15000, card1: 500, card4: '4', card6: '2', addr1: 5, addr2: 250, pemail: '50', remail: '50' }
    };
    const p = presets[type];
    document.getElementById('inp-amount').value = p.amount;
    document.getElementById('inp-card1').value = p.card1;
    document.getElementById('inp-card4').value = p.card4;
    document.getElementById('inp-card6').value = p.card6;
    document.getElementById('inp-addr1').value = p.addr1;
    document.getElementById('inp-addr2').value = p.addr2;
    document.getElementById('inp-pemail').value = p.pemail;
    document.getElementById('inp-remail').value = p.remail;
}

document.getElementById('fraud-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('check-btn');
    const btnText = document.getElementById('btn-text');
    btn.disabled = true;
    btnText.textContent = 'Analyzing...';

    const payload = {
        TransactionDT: Date.now() / 1000,
        TransactionAmt: parseFloat(document.getElementById('inp-amount').value),
        card1: parseInt(document.getElementById('inp-card1').value) || 10000,
        card2: 500, card3: 150,
        card4: parseInt(document.getElementById('inp-card4').value),
        card5: 220,
        card6: parseInt(document.getElementById('inp-card6').value),
        ProductCD: 4,
        addr1: parseFloat(document.getElementById('inp-addr1').value) || 300,
        addr2: parseFloat(document.getElementById('inp-addr2').value) || 87,
        P_emaildomain: parseInt(document.getElementById('inp-pemail').value),
        R_emaildomain: parseInt(document.getElementById('inp-remail').value),
    };

    try {
        const res = await fetch('http://localhost:5000/predict', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();

        const box = document.getElementById('result-box');
        const isFraud = data.is_anomaly === true;
        const score = parseFloat(data.anomaly_score).toFixed(4);
        const scorePercent = Math.min(100, Math.max(0, (parseFloat(score) - 0.1) / 0.5 * 100)).toFixed(0);

        box.className = 'result-box ' + (isFraud ? 'result-fraud' : 'result-normal');
        box.style.display = 'block';

        document.getElementById('result-icon').textContent = isFraud ? '🚨' : '✅';
        document.getElementById('result-title').textContent = isFraud ? 'ANOMALY DETECTED' : 'NORMAL TRANSACTION';
        document.getElementById('result-title').className = 'result-title ' + (isFraud ? 'fraud' : 'normal');
        document.getElementById('result-subtitle').textContent = isFraud
            ? 'Pola transaksi ini mencurigakan menurut model Isolation Forest.'
            : 'Transaksi ini terlihat normal dan aman.';

        document.getElementById('m-score').textContent = score;
        document.getElementById('m-verdict').textContent = isFraud ? '⚠ Fraud' : '✓ Safe';
        document.getElementById('m-action').textContent = isFraud ? 'Block' : 'Allow';
        document.getElementById('score-bar').style.width = scorePercent + '%';
        document.getElementById('score-bar').style.background = isFraud ? '#ef4444' : '#22c55e';

        box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    } catch(err) {
        alert('❌ Tidak dapat terhubung ke Flask API (localhost:5000). Pastikan main.py sedang berjalan!');
    }

    btn.disabled = false;
    btnText.textContent = 'Run Fraud Detection';
});
</script>

<?php require_once 'includes/footer.php'; ?>
