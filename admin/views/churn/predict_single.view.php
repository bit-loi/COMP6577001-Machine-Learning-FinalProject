<?php
/**
 * Churn Predict Single View Template
 * 
 * Renders the single customer churn check form and result card.
 */
?>
<link rel="stylesheet" href="<?php echo APPURL; ?>admin/assets/css/admin-components.css">

<!-- Breadcrumb -->
<div style="display:flex;align-items:center;gap:8px;margin-bottom:24px;font-size:0.82rem;color:#94a3b8;">
    <a href="<?php echo APPURL; ?>admin/churn/" style="color:#EE4D2D;text-decoration:none;font-weight:600;">Churn Prediction</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;"></i>
    <span>Single Customer Check</span>
</div>

<div style="display:grid;grid-template-columns:1fr 400px;gap:24px;align-items:start;">

    <!-- LEFT: Form -->
    <div class="table-card">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
            <div style="width:44px;height:44px;background:#FFF4ED;border-radius:12px;display:flex;align-items:center;justify-content:center;">
                <i data-lucide="user-search" style="width:22px;height:22px;color:#EE4D2D;"></i>
            </div>
            <div>
                <div style="font-size:0.95rem;font-weight:800;color:#0f172a;">Predict Customer Churn</div>
                <div style="font-size:0.75rem;color:#94a3b8;">Masukkan data customer untuk memprediksi kemungkinan churn</div>
            </div>
        </div>

        <form id="churn-form">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

                <div style="grid-column:1/-1;">
                    <label class="input-label">Customer ID *</label>
                    <input type="text" class="input-field" id="inp-custid" placeholder="e.g. C123456" required>
                    <div class="input-hint">ID unik customer yang ingin dicek</div>
                </div>

                <div>
                    <label class="input-label">Orders (Last Window) *</label>
                    <input type="number" class="input-field" id="inp-orders" placeholder="e.g. 5" min="0" required>
                    <div class="input-hint">Jumlah order dalam periode terakhir</div>
                </div>

                <div>
                    <label class="input-label">Revenue (Last Window) *</label>
                    <input type="number" class="input-field" id="inp-revenue" placeholder="e.g. 250.50" step="0.01" min="0" required>
                    <div class="input-hint">Total pendapatan dari customer ini ($)</div>
                </div>

                <div>
                    <label class="input-label">Recency Days *</label>
                    <input type="number" class="input-field" id="inp-recency" placeholder="e.g. 30" min="0" required>
                    <div class="input-hint">Berapa hari sejak transaksi terakhir</div>
                </div>

                <div>
                    <label class="input-label">Customer Age (Days) *</label>
                    <input type="number" class="input-field" id="inp-age" placeholder="e.g. 365" min="0" required>
                    <div class="input-hint">Berapa hari sejak customer pertama daftar</div>
                </div>

                <div>
                    <label class="input-label">Country</label>
                    <input type="text" class="input-field" id="inp-country" placeholder="e.g. United Kingdom">
                </div>

            </div>

            <div style="margin:20px 0;">
                <div style="font-size:0.72rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:10px;">Quick Presets</div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <button type="button" onclick="loadPreset('loyal')" style="padding:6px 14px;background:#f0fdf4;color:#15803d;border:1.5px solid #86efac;border-radius:8px;font-size:0.75rem;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:6px;"><i data-lucide="check-circle" style="width:14px;height:14px;"></i> Loyal Customer</button>
                    <button type="button" onclick="loadPreset('atRisk')" style="padding:6px 14px;background:#fefce8;color:#854d0e;border:1.5px solid #fde047;border-radius:8px;font-size:0.75rem;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:6px;"><i data-lucide="alert-triangle" style="width:14px;height:14px;"></i> At Risk Customer</button>
                    <button type="button" onclick="loadPreset('churned')" style="padding:6px 14px;background:#fef2f2;color:#dc2626;border:1.5px solid #fca5a5;border-radius:8px;font-size:0.75rem;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:6px;"><i data-lucide="x-circle" style="width:14px;height:14px;"></i> Likely Churned</button>
                </div>
            </div>

            <button type="submit" class="submit-btn" id="churn-btn">
                <i data-lucide="brain" style="width:18px;height:18px;"></i>
                <span id="churn-btn-text">Predict Churn Risk</span>
            </button>
        </form>

        <!-- Result Card -->
        <div id="churn-result" class="result-hidden" style="margin-top:28px;border-radius:16px;overflow:hidden;border:1.5px solid #e2e8f0;">

            <!-- Header bar -->
            <div id="result-header" style="padding:20px 24px;display:flex;align-items:center;justify-content:space-between;">
                <div>
                    <div style="font-size:0.72rem;font-weight:700;color:rgba(255,255,255,0.7);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:4px;">Prediction Result for</div>
                    <div id="result-custid" style="font-size:1.1rem;font-weight:900;color:#fff;font-family:monospace;"></div>
                </div>
                <div id="result-risk-pill"></div>
            </div>

            <!-- Body -->
            <div style="padding:24px;background:#fff;">
                <!-- Probability bar -->
                <div style="margin-bottom:24px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                        <span style="font-size:0.78rem;font-weight:700;color:#475569;">Churn Probability</span>
                        <span id="result-prob-pct" style="font-size:1.5rem;font-weight:900;color:#0f172a;"></span>
                    </div>
                    <div class="prob-bar-wrap">
                        <div class="prob-bar" id="result-prob-bar" style="width:0%;"></div>
                    </div>
                </div>

                <!-- Feature summary -->
                <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:20px;">
                    <div class="feature-card">
                        <div class="feature-val" id="r-orders">—</div>
                        <div class="feature-lbl">Orders</div>
                    </div>
                    <div class="feature-card">
                        <div class="feature-val" id="r-revenue">—</div>
                        <div class="feature-lbl">Revenue</div>
                    </div>
                    <div class="feature-card">
                        <div class="feature-val" id="r-recency">—</div>
                        <div class="feature-lbl">Recency</div>
                    </div>
                    <div class="feature-card">
                        <div class="feature-val" id="r-age">—</div>
                        <div class="feature-lbl">Age (days)</div>
                    </div>
                </div>

                <!-- Recommended Action -->
                <div id="result-action-box" style="border-radius:12px;padding:16px;display:flex;align-items:center;gap:12px;">
                    <i data-lucide="lightbulb" style="width:20px;height:20px;flex-shrink:0;"></i>
                    <div>
                        <div style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:2px;">Recommended Action</div>
                        <div id="result-action" style="font-size:0.875rem;font-weight:600;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT: Info -->
    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="table-card">
            <div style="font-size:0.85rem;font-weight:700;color:#0f172a;margin-bottom:14px;display:flex;align-items:center;gap:8px;">
                <i data-lucide="info" style="width:16px;height:16px;color:#3b82f6;"></i> Feature Guide
            </div>
            <div style="display:flex;flex-direction:column;gap:12px;font-size:0.78rem;color:#475569;">
                <div><strong>Orders Last Window:</strong> Total jumlah order dalam window waktu tertentu (misal 3 bulan). Semakin sedikit = lebih berisiko.</div>
                <div><strong>Revenue Last Window:</strong> Total nilai belanja. Customer dengan revenue rendah cenderung tidak setia.</div>
                <div><strong>Recency Days:</strong> Semakin banyak hari sejak terakhir belanja = semakin tinggi risiko churn.</div>
                <div><strong>Customer Age Days:</strong> Semakin lama customer bergabung, biasanya semakin loyal.</div>
            </div>
        </div>

        <div class="table-card">
            <div style="font-size:0.85rem;font-weight:700;color:#0f172a;margin-bottom:14px;">Risk Thresholds</div>
            <div style="display:flex;flex-direction:column;gap:8px;font-size:0.78rem;">
                <div style="display:flex;align-items:center;gap:10px;padding:8px 12px;background:#fef2f2;border-radius:8px;">
                    <span style="width:10px;height:10px;background:#ef4444;border-radius:50%;flex-shrink:0;"></span>
                    <div><strong>Critical</strong> — Probabilitas ≥ 75%</div>
                </div>
                <div style="display:flex;align-items:center;gap:10px;padding:8px 12px;background:#fefce8;border-radius:8px;">
                    <span style="width:10px;height:10px;background:#eab308;border-radius:50%;flex-shrink:0;"></span>
                    <div><strong>At Risk</strong> — Probabilitas 45% – 74%</div>
                </div>
                <div style="display:flex;align-items:center;gap:10px;padding:8px 12px;background:#f0fdf4;border-radius:8px;">
                    <span style="width:10px;height:10px;background:#22c55e;border-radius:50%;flex-shrink:0;"></span>
                    <div><strong>Loyal</strong> — Probabilitas &lt; 45%</div>
                </div>
            </div>
        </div>

        <div class="table-card" style="background:#0f172a;border-color:#1e293b;">
            <div style="font-size:0.72rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:12px;">Model Info</div>
            <div style="font-size:0.78rem;color:#94a3b8;line-height:1.8;">
                <div><span style="color:#38bdf8;">Algorithm:</span> Gradient Boosting (.joblib)</div>
                <div><span style="color:#38bdf8;">Dataset:</span> Online Retail II</div>
                <div><span style="color:#38bdf8;">Features:</span> RFM-based (4 features)</div>
                <div><span style="color:#38bdf8;">API:</span> <span style="color:#4ade80;">localhost:5000/predict/churn</span></div>
            </div>
        </div>

        <a href="<?php echo APPURL; ?>admin/churn/" style="display:flex;align-items:center;justify-content:center;gap:8px;padding:12px;background:#f1f5f9;color:#475569;border-radius:10px;text-decoration:none;font-size:0.82rem;font-weight:600;transition:background .15s;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
            <i data-lucide="arrow-left" style="width:15px;height:15px;"></i> Back to Churn Dashboard
        </a>
    </div>
</div>

<script>
const presets = {
    loyal:   { custid: 'C001234', orders: 12, revenue: 850.00, recency: 5,  age: 730, country: 'United Kingdom' },
    atRisk:  { custid: 'C005678', orders: 2,  revenue: 75.00,  recency: 45, age: 180, country: 'Germany' },
    churned: { custid: 'C009012', orders: 0,  revenue: 12.00,  recency: 180,age: 90,  country: 'France' }
};

function loadPreset(type) {
    const p = presets[type];
    if (!p) return;
    document.getElementById('inp-custid').value   = p.custid;
    document.getElementById('inp-orders').value   = p.orders;
    document.getElementById('inp-revenue').value  = p.revenue;
    document.getElementById('inp-recency').value  = p.recency;
    document.getElementById('inp-age').value      = p.age;
    document.getElementById('inp-country').value  = p.country;
}

document.getElementById('churn-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('churn-btn');
    const btnText = document.getElementById('churn-btn-text');
    btn.disabled = true;
    btnText.textContent = 'Predicting...';

    const custid  = document.getElementById('inp-custid').value;
    const orders  = parseInt(document.getElementById('inp-orders').value);
    const revenue = parseFloat(document.getElementById('inp-revenue').value);
    const recency = parseInt(document.getElementById('inp-recency').value);
    const age     = parseInt(document.getElementById('inp-age').value);

    try {
        const res = await fetch('http://localhost:5000/predict/churn', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ orders_last_window: orders, revenue_last_window: revenue, recency_days: recency, customer_age_days: age })
        });
        const data = await res.json();

        const prob    = parseFloat(data.predicted_churn_probability);
        const pct     = (prob * 100).toFixed(1);
        const risk    = data.risk_level;
        const action  = data.recommended_action;

        const colors = { Critical: { bg:'#dc2626', bar:'#ef4444', pill:'risk-high', action:'background:#fef2f2;color:#dc2626;border:1.5px solid #fecaca;' },
                         'At Risk':{ bg:'#ca8a04', bar:'#eab308', pill:'risk-medium', action:'background:#fefce8;color:#ca8a04;border:1.5px solid #fde047;' },
                         Loyal:   { bg:'#15803d', bar:'#22c55e', pill:'risk-low', action:'background:#f0fdf4;color:#15803d;border:1.5px solid #86efac;' } };
        const c = colors[risk] || colors['Loyal'];

        // Show result
        document.getElementById('churn-result').className = '';
        document.getElementById('result-header').style.background = c.bg;
        document.getElementById('result-custid').textContent = custid;
        document.getElementById('result-risk-pill').innerHTML = `<span class="pill-risk ${c.pill}">${risk}</span>`;
        document.getElementById('result-prob-pct').textContent = pct + '%';
        document.getElementById('result-prob-bar').style.width = pct + '%';
        document.getElementById('result-prob-bar').style.background = c.bar;
        document.getElementById('r-orders').textContent  = orders;
        document.getElementById('r-revenue').textContent = '$' + revenue.toFixed(0);
        document.getElementById('r-recency').textContent = recency + 'd';
        document.getElementById('r-age').textContent     = age;
        document.getElementById('result-action-box').style = c.action + 'border-radius:12px;padding:16px;display:flex;align-items:center;gap:12px;';
        document.getElementById('result-action').textContent = action;

        if (typeof lucide !== 'undefined') lucide.createIcons();
        document.getElementById('churn-result').scrollIntoView({ behavior: 'smooth', block: 'nearest' });

    } catch(err) {
        alert('❌ Tidak dapat terhubung ke Flask API (localhost:5000). Pastikan main.py sedang berjalan!\n\nError: ' + err.message);
    }

    btn.disabled = false;
    btnText.textContent = 'Predict Churn Risk';
});
</script>
