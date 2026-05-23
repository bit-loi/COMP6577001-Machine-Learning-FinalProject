<?php
/**
 * Fraud Check View Template
 * 
 * Renders the manual transaction risk check page.
 */
?>
<link rel="stylesheet" href="<?php echo APPURL; ?>admin/assets/css/admin-components.css">

<div style="display:grid;grid-template-columns:1fr 420px;gap:24px;align-items:start;">

    <!-- LEFT: Form -->
    <div class="table-card">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
            <div style="width:44px;height:44px;background:#fef2f2;border-radius:12px;display:flex;align-items:center;justify-content:center;">
                <i data-lucide="shield-alert" style="width:22px;height:22px;color:#dc2626;"></i>
            </div>
            <div>
                <div style="font-size:0.95rem;font-weight:800;color:#0f172a;">Manual Fraud Check</div>
                <div style="font-size:0.75rem;color:#94a3b8;">Enter transaction details to be verified by the Isolation Forest model</div>
            </div>
        </div>

        <form id="fraud-form">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="input-group">
                    <label class="input-label">Transaction Amount ($) *</label>
                    <input type="number" class="input-field" id="inp-amount" placeholder="e.g. 150.00" step="0.01" min="0" required>
                    <div class="input-hint">Total transaction amount</div>
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
                    <button type="button" onclick="loadPreset('normal')" style="padding:6px 14px;background:#f0fdf4;color:#15803d;border:1.5px solid #86efac;border-radius:8px;font-size:0.75rem;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:6px;">Normal Transaction ($80)</button>
                    <button type="button" onclick="loadPreset('suspicious')" style="padding:6px 14px;background:#fefce8;color:#854d0e;border:1.5px solid #fde047;border-radius:8px;font-size:0.75rem;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:6px;">Suspicious ($2,500)</button>
                    <button type="button" onclick="loadPreset('fraud')" style="padding:6px 14px;background:#fef2f2;color:#dc2626;border:1.5px solid #fca5a5;border-radius:8px;font-size:0.75rem;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:6px;">High Risk ($15,000)</button>
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
                <div id="result-icon" style="width:48px;height:48px;border-radius:50%;display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;"></div>
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
                <div style="display:flex;gap:10px;margin-bottom:10px;"><span style="font-weight:800;color:#EE4D2D;min-width:20px;">1</span> Input transaction details in the left form</div>
                <div style="display:flex;gap:10px;margin-bottom:10px;"><span style="font-weight:800;color:#EE4D2D;min-width:20px;">2</span> Data is sent to the <code style="background:#f1f5f9;padding:1px 5px;border-radius:4px;">Flask API /predict</code></div>
                <div style="display:flex;gap:10px;margin-bottom:10px;"><span style="font-weight:800;color:#EE4D2D;min-width:20px;">3</span> Isolation Forest model processes 224 features</div>
                <div style="display:flex;gap:10px;"><span style="font-weight:800;color:#EE4D2D;min-width:20px;">4</span> Results are displayed: Normal or Anomaly</div>
            </div>
        </div>

        <div class="table-card">
            <div style="font-size:0.85rem;font-weight:700;color:#0f172a;margin-bottom:14px;">Score Interpretation</div>
            <div style="display:flex;flex-direction:column;gap:10px;font-size:0.78rem;">
                <div style="display:flex;align-items:center;gap:10px;padding:10px;background:#f0fdf4;border-radius:8px;border:1px solid #bbf7d0;">
                    <div><strong>Normal Transaction</strong><br><span style="color:#64748b;">Model predicts a legitimate transaction</span></div>
                </div>
                <div style="display:flex;align-items:center;gap:10px;padding:10px;background:#fefce8;border-radius:8px;border:1px solid #fde047;">
                    <div><strong>Borderline</strong><br><span style="color:#64748b;">Requires additional verification</span></div>
                </div>
                <div style="display:flex;align-items:center;gap:10px;padding:10px;background:#fef2f2;border-radius:8px;border:1px solid #fecaca;">
                    <div><strong>Anomaly Detected</strong><br><span style="color:#64748b;">Suspicious pattern detected by Isolation Forest</span></div>
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
window.fraudConfig = {
    apiUrl: 'http://localhost:5000/predict'
};
</script>
<script src="<?php echo APPURL; ?>admin/assets/js/fraud-form.js?v=<?php echo time(); ?>"></script>
