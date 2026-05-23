/**
 * Fraud Detection — Form Handler
 * Expects global: window.fraudConfig = { apiUrl }
 */
(function () {
    const config = window.fraudConfig || { apiUrl: 'http://localhost:5000/predict' };

    const presets = {
        normal:     { amount: 80,    card1: 14567, card4: '1', card6: '1', addr1: 350, addr2: 87,  pemail: '16', remail: '16' },
        suspicious: { amount: 2500,  card1: 1200,  card4: '4', card6: '2', addr1: 25,  addr2: 20,  pemail: '50', remail: '50' },
        fraud:      { amount: 15000, card1: 500,   card4: '4', card6: '2', addr1: 5,   addr2: 250, pemail: '50', remail: '50' }
    };

    window.loadPreset = function (type) {
        const p = presets[type];
        if (!p) return;
        document.getElementById('inp-amount').value = p.amount;
        document.getElementById('inp-card1').value  = p.card1;
        document.getElementById('inp-card4').value  = p.card4;
        document.getElementById('inp-card6').value  = p.card6;
        document.getElementById('inp-addr1').value  = p.addr1;
        document.getElementById('inp-addr2').value  = p.addr2;
        document.getElementById('inp-pemail').value = p.pemail;
        document.getElementById('inp-remail').value = p.remail;
    };

    const form = document.getElementById('fraud-form');
    if (!form) return;

    form.addEventListener('submit', async function (e) {
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
            const res = await fetch(config.apiUrl, {
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
            const iconEl = document.getElementById('result-icon');
            if (isFraud) {
                iconEl.style.background = '#fee2e2';
                iconEl.innerHTML = '<i data-lucide="shield-alert" style="width:24px;height:24px;color:#dc2626;"></i>';
            } else {
                iconEl.style.background = '#dcfce7';
                iconEl.innerHTML = '<i data-lucide="shield-check" style="width:24px;height:24px;color:#16a34a;"></i>';
            }
            document.getElementById('result-title').textContent = isFraud ? 'ANOMALY DETECTED' : 'NORMAL TRANSACTION';
            document.getElementById('result-title').className = 'result-title ' + (isFraud ? 'fraud' : 'normal');
            document.getElementById('result-subtitle').textContent = isFraud
                ? 'This transaction pattern is suspicious according to the Isolation Forest model.'
                : 'This transaction appears normal and safe.';
            document.getElementById('m-score').textContent = score;
            document.getElementById('m-verdict').textContent = isFraud ? 'Fraud' : 'Safe';
            document.getElementById('m-action').textContent = isFraud ? 'Block' : 'Allow';
            document.getElementById('score-bar').style.width = scorePercent + '%';
            document.getElementById('score-bar').style.background = isFraud ? '#ef4444' : '#22c55e';
            if (typeof lucide !== 'undefined') lucide.createIcons();
            box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } catch (err) {
            alert('Could not connect to Flask API. Make sure main.py is running!');
        }

        btn.disabled = false;
        btnText.textContent = 'Run Fraud Detection';
    });
})();
