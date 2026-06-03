/**
 * ML Simulation Engine
 * Expects global: window.simConfig = { apiUrl }
 */
(function () {
    const config = window.simConfig || { apiUrl: 'http://localhost:5000/predict' };
    const ctx = document.getElementById('liveChart');
    if (!ctx) return;

    const liveChart = new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: { labels: [], datasets: [{ label: 'Anomaly Score', data: [], borderColor: '#EE4D2D', backgroundColor: 'rgba(238,77,45,0.05)', borderWidth: 2, pointRadius: 0, fill: true, tension: 0.4 }] },
        options: { responsive: true, maintainAspectRatio: false, scales: { x: { display: false }, y: { display: true, grid: { color: '#f1f5f9' }, ticks: { color: '#94a3b8', font: { family: 'monospace', size: 10 } } } }, plugins: { legend: { display: false } }, animation: { duration: 0 } }
    });

    const terminal = document.getElementById('terminal-out');
    let isRunning = false, simInterval, trxCount = 0, anomalyCount = 0;

    function logTerminal(msg, type = '') {
        const time = new Date().toISOString().split('T')[1].slice(0, 8);
        const div = document.createElement('div');
        div.className = `mb-3 leading-relaxed ${type === 'error' ? 'text-red-600 bg-red-50 px-2 py-1 rounded font-bold' : (type === 'warning' ? 'text-orange-600 font-semibold' : 'text-gray-700')}`;
        div.textContent = `[${time}]   ${msg}`;
        terminal.appendChild(div);
        terminal.scrollTop = terminal.scrollHeight;
        if (terminal.children.length > 50) terminal.removeChild(terminal.firstChild);
    }

    function secureRandom() {
        const values = new Uint32Array(1);
        window.crypto.getRandomValues(values);
        return values[0] / 0x100000000;
    }

    function randInt(min, max) { return Math.floor(secureRandom() * (max - min + 1)) + min; }
    function randFloat(min, max) { return parseFloat((secureRandom() * (max - min) + min).toFixed(2)); }
    function pick(arr) { return arr[randInt(0, arr.length - 1)]; }

    function buildPayload() {
        const isAnomalous = secureRandom() < 0.20;
        let amt, card1, card2, card3, card4, card5, card6, addr1, addr2, pEmail, rEmail, txDT;

        if (isAnomalous) {
            const s = randInt(1, 4);
            if (s === 1) { amt=randFloat(3000,18000); card1=randInt(1000,3000); card2=randInt(100,200); card3=randFloat(100,200); card4=pick([1,2,3,4]); card5=randInt(100,200); card6=pick([1,2]); addr1=randInt(1,50); addr2=randFloat(10,30); pEmail=randInt(20,50); rEmail=randInt(20,60); txDT=Date.now()/1000-randInt(0,1000); }
            else if (s === 2) { amt=randFloat(0.01,2); card1=randInt(10000,18000); card2=randInt(550,700); card3=randFloat(100,200); card4=2; card5=randInt(200,300); card6=2; addr1=randInt(400,500); addr2=randFloat(80,100); pEmail=randInt(1,5); rEmail=randInt(30,50); txDT=Date.now()/1000-randInt(86000,87000); }
            else if (s === 3) { amt=randFloat(500,2500); card1=randInt(500,2000); card2=randInt(600,800); card3=randFloat(150,200); card4=4; card5=randInt(100,150); card6=2; addr1=randInt(500,600); addr2=randFloat(200,300); pEmail=randInt(40,80); rEmail=randInt(40,80); txDT=randFloat(50000,90000); }
            else { amt=randFloat(1500,9999); card1=randInt(2000,5000); card2=randInt(300,400); card3=randFloat(100,150); card4=pick([3,4]); card5=randInt(150,250); card6=1; addr1=randInt(100,200); addr2=randFloat(60,80); pEmail=randInt(60,100); rEmail=randInt(10,20); txDT=randFloat(100000,200000); }
        } else {
            amt=randFloat(5,450); card1=randInt(8000,18000); card2=randInt(400,600); card3=randFloat(140,165); card4=pick([1,1,1,2]); card5=randInt(200,240); card6=pick([1,2]); addr1=randInt(200,500); addr2=randFloat(80,100); pEmail=pick([16,11,16,11,3]); rEmail=pick([16,11,16,0]); txDT=Date.now()/1000-randInt(0,100000);
        }

        return { isAnomalous, amt, payload: { TransactionDT:txDT, TransactionAmt:parseFloat(amt), card1,card2,card3,card4,card5,card6, ProductCD:pick([1,2,3,4,4,5]), addr1,addr2, P_emaildomain:pEmail, R_emaildomain:rEmail } };
    }

    async function tick() {
        if (!isRunning) return;
        const trxId = randInt(10000, 99999);
        const { isAnomalous, amt, payload } = buildPayload();

        logTerminal(`Fetching features for TRX_ID: ${trxId} [${isAnomalous ? 'SUSPICIOUS_PROFILE' : 'STANDARD_PROFILE'}]...`, isAnomalous ? 'warning' : 'info');

        try {
            const res = await fetch(config.apiUrl, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
            if (!res.ok) throw new Error("Flask Engine 500");
            const data = await res.json();
            trxCount++;
            const score = data.anomaly_score.toFixed(4);
            const isFraud = data.is_anomaly === true;

            if (isFraud) { anomalyCount++; logTerminal(`TRX_ID: ${trxId} | AMT: $${parseFloat(amt).toFixed(2)} | SCORE: ${score} -> WARNING: ANOMALY DETECTED`, 'error'); document.getElementById('stat-anomalies').textContent = anomalyCount; }
            else { logTerminal(`TRX_ID: ${trxId} | AMT: $${parseFloat(amt).toFixed(2)} | SCORE: ${score} -> NORMAL`, 'info'); }

            document.getElementById('stat-count').textContent = trxCount;
            const conf = Math.min(100, Math.max(0, (1 - (score - 0.3) / 0.5) * 100)).toFixed(1);
            document.getElementById('stat-confidence').textContent = `${conf}%`;

            liveChart.data.labels.push('');
            liveChart.data.datasets[0].data.push(parseFloat(score));
            if (liveChart.data.labels.length > 20) { liveChart.data.labels.shift(); liveChart.data.datasets[0].data.shift(); }
            liveChart.update();
        } catch (e) {
            logTerminal(`[NETWORK ERROR] Could not reach Flask Python Engine. Is main.py running?`, 'error');
            isRunning = false; clearInterval(simInterval);
        }
    }

    document.getElementById('btn-start').addEventListener('click', () => { if (isRunning) return; isRunning = true; logTerminal('> ENGINE START: POLLING PREDICTIONS EVERY 1.5S...', 'warning'); simInterval = setInterval(tick, 1500); });
    document.getElementById('btn-stop').addEventListener('click', () => { if (!isRunning) return; isRunning = false; clearInterval(simInterval); logTerminal('> HALT SIGNAL SENT. ENGINE STOPPED.', 'warning'); });
})();
