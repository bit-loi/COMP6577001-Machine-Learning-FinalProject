<?php
session_start();

// Check if user is admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/config.php';
?>
<?php
$pageTitle = 'ML Intelligence Simulation';
$pageDescription = 'Live Fraud Detection Model';
require_once 'includes/header.php';
?>
        <div class="actions-bar bg-white border border-gray-200 shadow-sm p-6 rounded-2xl flex items-center gap-4 mb-8">
            <button id="btn-start" class="bg-[#EE4D2D] text-white px-6 py-3 rounded-xl font-bold flex items-center gap-2 hover:bg-[#D74226] transition-all shadow-md active:scale-95">
                <i data-lucide="play" style="width:18px; height:18px;"></i> Deploy Simulation
            </button>
            <button id="btn-stop" class="bg-gray-100 text-gray-700 px-6 py-3 rounded-xl font-bold flex items-center gap-2 hover:bg-gray-200 transition-all active:scale-95">
                <i data-lucide="square" style="width:18px; height:18px;"></i> Halt Engine
            </button>
            
            <div class="ml-auto flex items-center gap-3 px-4 py-2 bg-green-50 border border-green-100 rounded-full">
                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse shadow-[0_0_8px_rgba(34,197,94,0.6)]"></div>
                <span class="text-[10px] font-bold text-green-700 uppercase tracking-widest">Flask Engine Online</span>
            </div>
        </div>

        <div class="grid-layout">
            <div style="display: flex; flex-direction: column;">
                <!-- Terminal -->
                <div class="bg-white rounded-2xl overflow-hidden shadow-sm flex flex-col h-full min-h-[500px] border border-gray-200">
                    <div class="bg-gray-50 px-5 py-3 border-b border-gray-200 flex items-center justify-between">
                        <div class="flex gap-2">
                            <div class="w-3 h-3 rounded-full bg-red-400"></div>
                            <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                            <div class="w-3 h-3 rounded-full bg-green-400"></div>
                        </div>
                        <div class="text-[10px] font-mono text-gray-500 tracking-widest uppercase">isolation_forest_hook.py</div>
                        <div class="w-12"></div>
                    </div>
                    <div class="flex-1 p-6 font-mono text-sm text-gray-800 overflow-y-auto" id="terminal-out">
                        <div class="mb-3 text-gray-400 leading-relaxed">> &nbsp; INIT ML_ENGINE_HOOK [v2.4.1]...</div>
                        <div class="mb-3 text-gray-400 leading-relaxed">> &nbsp; SYSTEM RESOURCES OK. MEMORY ALLOCATION: 4.2GB</div>
                        <div class="mb-3 text-gray-400 leading-relaxed">> &nbsp; MODEL LOADED: ISOLATION_FOREST_V3 (CONFIDENCE BASELINE: 0.98)</div>
                        <div class="mb-3 text-orange-600 font-bold leading-relaxed">> &nbsp; AWAITING DEPLOYMENT COMMAND...</div>
                    </div>
                </div>

                <!-- Chart -->
                <div class="bg-white rounded-2xl p-8 mt-8 border border-gray-200 shadow-sm">
                    <div class="flex items-center gap-3 text-gray-500 text-xs font-bold uppercase tracking-widest mb-6">
                        <i data-lucide="activity" class="w-4 h-4 text-[#EE4D2D]"></i>
                        Live Anomaly Scores
                    </div>
                    <div style="height: 200px; width: 100%;">
                        <canvas id="liveChart"></canvas>
                    </div>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 24px;">
                <div class="bg-white border border-gray-200 rounded-2xl p-8 shadow-sm">
                    <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center mb-8">
                        <i data-lucide="package" class="w-6 h-6"></i>
                    </div>
                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] mb-4">Transactions Processed</div>
                    <div class="text-5xl font-mono font-bold text-gray-900 mb-2" id="stat-count">0</div>
                    <div class="text-xs text-gray-500 font-medium">In current live session</div>
                </div>
                
                <div class="bg-[#EE4D2D] rounded-2xl p-8 shadow-lg shadow-red-100 text-white">
                    <div class="w-12 h-12 bg-white/20 text-white rounded-xl flex items-center justify-center mb-8">
                        <i data-lucide="shield-alert" class="w-6 h-6"></i>
                    </div>
                    <div class="text-[10px] font-bold text-white/60 uppercase tracking-[0.2em] mb-4">Anomalies Detected</div>
                    <div class="text-5xl font-mono font-bold text-white mb-2" id="stat-anomalies">0</div>
                    <div class="text-xs text-white/60 font-medium">Fraudulent patterns isolated</div>
                </div>

                <div class="bg-white border border-gray-200 rounded-2xl p-8 shadow-sm">
                    <div class="w-12 h-12 bg-orange-100 text-orange-600 rounded-xl flex items-center justify-center mb-8">
                        <i data-lucide="bar-chart-3" class="w-6 h-6"></i>
                    </div>
                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] mb-4">Avg Confidence</div>
                    <div class="text-5xl font-mono font-bold text-gray-900 mb-2" id="stat-confidence">0%</div>
                    <div class="text-xs text-gray-500 font-medium">Algorithmic certainty score</div>
                </div>
            </div>
        </div>
    </div>


<script>
    // Navigation active state
    document.querySelectorAll('.nav-item').forEach(item => {
        if(item.textContent.includes('ML Simulation')) {
            item.classList.add('active');
        } else {
            item.classList.remove('active');
        }
    });

    // Chart.js Live Setup
    const ctx = document.getElementById('liveChart').getContext('2d');
    const liveChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Anomaly Score',
                data: [],
                borderColor: '#EE4D2D',
                backgroundColor: 'rgba(238, 77, 45, 0.05)',
                borderWidth: 2,
                pointRadius: 0,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { display: false },
                y: { 
                    display: true, 
                    grid: { color: '#f1f5f9' },
                    ticks: { color: '#94a3b8', font: { family: 'monospace', size: 10 } }
                }
            },
            plugins: { legend: { display: false } },
            animation: { duration: 0 }
        }
    });

    // Simulation Engine
    const terminal = document.getElementById('terminal-out');
    let isRunning = false;
    let simInterval;
    let trxCount = 0;
    let anomalyCount = 0;

    function logTerminal(msg, type = '') {
        const time = new Date().toISOString().split('T')[1].slice(0, 8);
        const div = document.createElement('div');
        div.className = `mb-3 leading-relaxed ${type === 'error' ? 'text-red-600 bg-red-50 px-2 py-1 rounded font-bold' : (type === 'warning' ? 'text-orange-600 font-semibold' : 'text-gray-700')}`;
        div.innerHTML = `[${time}] &nbsp; ${msg}`;
        terminal.appendChild(div);
        terminal.scrollTop = terminal.scrollHeight;
        
        if (terminal.children.length > 50) {
            terminal.removeChild(terminal.firstChild);
        }
    }

    // ── Helper: random int in range ────────────────────────────────────────────
    function randInt(min, max) { return Math.floor(Math.random() * (max - min + 1)) + min; }
    function randFloat(min, max) { return parseFloat((Math.random() * (max - min) + min).toFixed(2)); }
    function pick(arr) { return arr[Math.floor(Math.random() * arr.length)]; }

    async function tickSimulation() {
        if (!isRunning) return;
        
        const trxId = Math.floor(10000 + Math.random() * 90000);

        // ── Decide transaction profile ────────────────────────────────────────
        // ~20% chance of injecting an anomalous scenario
        const isAnomalousProfile = Math.random() < 0.20;

        let amt, card1, card2, card3, card4, card5, card6, addr1, addr2, pEmail, rEmail, txDT;

        if (isAnomalousProfile) {
            // Anomalous transactions: extreme amounts, mismatched cards, weird timestamps
            const scenario = randInt(1, 4);
            switch(scenario) {
                case 1: // Very high amount
                    amt    = randFloat(3000, 18000);
                    card1  = randInt(1000, 3000);   // very low card number (unusual)
                    card2  = randInt(100, 200);
                    card3  = randFloat(100, 200);
                    card4  = pick([1,2,3,4]);
                    card5  = randInt(100, 200);
                    card6  = pick([1,2]);
                    addr1  = randInt(1, 50);        // unusual low billing zip
                    addr2  = randFloat(10, 30);
                    pEmail = randInt(20, 50);       // rare email domain
                    rEmail = randInt(20, 60);
                    txDT   = Date.now() / 1000 - randInt(0, 1000); // recent
                    break;
                case 2: // Micro transaction flood (lots of tiny transactions)
                    amt    = randFloat(0.01, 2.00);
                    card1  = randInt(10000, 18000);
                    card2  = randInt(550, 700);
                    card3  = randFloat(100, 200);
                    card4  = 2;
                    card5  = randInt(200, 300);
                    card6  = 2;
                    addr1  = randInt(400, 500);
                    addr2  = randFloat(80, 100);
                    pEmail = randInt(1, 5);
                    rEmail = randInt(30, 50);       // mismatched domains
                    txDT   = Date.now() / 1000 - randInt(86000, 87000); // exactly 24h ago (suspicious)
                    break;
                case 3: // Suspicious card combination
                    amt    = randFloat(500, 2500);
                    card1  = randInt(500, 2000);
                    card2  = randInt(600, 800);
                    card3  = randFloat(150, 200);
                    card4  = 4;                    // rare card type
                    card5  = randInt(100, 150);
                    card6  = 2;
                    addr1  = randInt(500, 600);
                    addr2  = randFloat(200, 300);  // unusual country code
                    pEmail = randInt(40, 80);
                    rEmail = randInt(40, 80);
                    txDT   = randFloat(50000, 90000);  // unusual transaction timing
                    break;
                default: // Off-hours large transaction
                    amt    = randFloat(1500, 9999);
                    card1  = randInt(2000, 5000);
                    card2  = randInt(300, 400);
                    card3  = randFloat(100, 150);
                    card4  = pick([3,4]);
                    card5  = randInt(150, 250);
                    card6  = 1;
                    addr1  = randInt(100, 200);
                    addr2  = randFloat(60, 80);
                    pEmail = randInt(60, 100);
                    rEmail = randInt(10, 20);      // very mismatched
                    txDT   = randFloat(100000, 200000);
            }
        } else {
            // Normal transactions: realistic everyday purchases
            amt    = randFloat(5, 450);
            card1  = randInt(8000, 18000);
            card2  = randInt(400, 600);
            card3  = randFloat(140, 165);
            card4  = pick([1, 1, 1, 2]);          // mostly visa
            card5  = randInt(200, 240);
            card6  = pick([1, 2]);
            addr1  = randInt(200, 500);
            addr2  = randFloat(80, 100);
            pEmail = pick([16, 11, 16, 11, 3]);   // common email domains
            rEmail = pick([16, 11, 16, 0]);
            txDT   = Date.now() / 1000 - randInt(0, 100000);
        }

        logTerminal(`Fetching features for TRX_ID: ${trxId} [${isAnomalousProfile ? 'SUSPICIOUS_PROFILE' : 'STANDARD_PROFILE'}]...`, isAnomalousProfile ? 'warning' : 'info');
        
        try {
            const payload = {
                TransactionDT:  txDT,
                TransactionAmt: parseFloat(amt),
                card1, card2, card3, card4, card5, card6,
                ProductCD: pick([1, 2, 3, 4, 4, 5]),
                addr1, addr2,
                P_emaildomain: pEmail,
                R_emaildomain: rEmail
            };

            const res = await fetch('http://localhost:5000/predict', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            if (!res.ok) throw new Error("Flask Engine 500");

            const data = await res.json();
            trxCount++;
            
            const score = data.anomaly_score.toFixed(4);
            const isFraud = data.is_anomaly === true;
            
            if (isFraud) {
                anomalyCount++;
                logTerminal(`TRX_ID: ${trxId} | AMT: $${parseFloat(amt).toFixed(2)} | SCORE: ${score} -> ⚠ ANOMALY DETECTED`, 'error');
                document.getElementById('stat-anomalies').textContent = anomalyCount;
            } else {
                logTerminal(`TRX_ID: ${trxId} | AMT: $${parseFloat(amt).toFixed(2)} | SCORE: ${score} -> NORMAL`, 'info');
            }

            // Update Stats
            document.getElementById('stat-count').textContent = trxCount;
            
            // Confidence: normalize anomaly_score to 0-100%
            const conf = Math.min(100, Math.max(0, (1 - (score - 0.3) / 0.5) * 100)).toFixed(1);
            document.getElementById('stat-confidence').textContent = `${conf}%`;

            // Update Chart
            liveChart.data.labels.push('');
            liveChart.data.datasets[0].data.push(parseFloat(score));
            if (liveChart.data.labels.length > 20) {
                liveChart.data.labels.shift();
                liveChart.data.datasets[0].data.shift();
            }
            liveChart.update();

        } catch (e) {
            logTerminal(`[NETWORK ERROR] Could not reach Flask Python Engine at localhost:5000. Is main.py running?`, 'error');
            isRunning = false;
            clearInterval(simInterval);
        }
    }


    document.getElementById('btn-start').addEventListener('click', () => {
        if (isRunning) return;
        isRunning = true;
        logTerminal('> ENGINE START: POLLING PREDICTIONS EVERY 1.5S...', 'warning');
        simInterval = setInterval(tickSimulation, 1500);
    });

    document.getElementById('btn-stop').addEventListener('click', () => {
        if (!isRunning) return;
        isRunning = false;
        clearInterval(simInterval);
        logTerminal('> HALT SIGNAL SENT. ENGINE STOPPED.', 'warning');
    });

</script>
<?php require_once 'includes/footer.php'; ?>
