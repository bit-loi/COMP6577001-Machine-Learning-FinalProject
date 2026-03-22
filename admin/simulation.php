<?php
session_start();

// Check if user is admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ML Simulation — Admin Console</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,700;1,700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    
    <!-- Load Chart.js for ML metrics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #020202; color: white; margin: 0; display: flex; min-height: 100vh; overflow-x: hidden; }
        .mono { font-family: 'JetBrains Mono', monospace; }
        .serif { font-family: 'Playfair Display', serif; }

        /* Sidebar Styles (matching index.php) */
        .sidebar { width: 240px; min-height: 100vh; background: #050505; border-right: 1px solid rgba(255,255,255,0.04); display: flex; flex-direction: column; position: fixed; top: 0; left: 0; z-index: 50; }
        .sidebar-logo { padding: 32px 24px; border-bottom: 1px solid rgba(255,255,255,0.04); }
        .sidebar-nav { flex: 1; padding: 24px 16px; }
        .nav-item { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 10px; color: rgba(255,255,255,0.4); font-size: 0.85rem; font-weight: 600; text-decoration: none; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); margin-bottom: 4px; cursor: pointer; }
        .nav-item:hover { background: rgba(255,255,255,0.03); color: rgba(255,255,255,0.9); transform: translateX(4px); }
        .nav-item.active { background: rgba(255,255,255,0.1); color: white; border-left: 2px solid white; border-radius: 4px 10px 10px 4px; }
        .nav-item svg { width: 18px; height: 18px; flex-shrink: 0; transition: transform 0.3s; color: inherit; }
        .nav-section { font-size: 0.65rem; font-weight: 800; letter-spacing: 0.2em; text-transform: uppercase; color: rgba(255,255,255,0.2); padding: 16px 16px 12px; }

        /* Main content Styles */
        .main { margin-left: 240px; flex: 1; min-height: 100vh; display: flex; flex-direction: column; background: #050505; }
        .topbar { height: 80px; border-bottom: 1px solid rgba(255,255,255,0.04); display: flex; align-items: center; justify-content: space-between; padding: 0 48px; background: rgba(2,2,2,0.8); backdrop-filter: blur(24px); position: sticky; top: 0; z-index: 40; }
        
        .content { flex: 1; padding: 48px; display: flex; flex-direction: column; gap: 32px; max-width: 1600px; margin: 0 auto; width: 100%; }
        
        /* Dashboard Grid */
        .grid-layout { display: grid; grid-template-columns: 1fr 340px; gap: 32px; align-items: stretch; }
        
        /* Terminal */
        .terminal-container { position: relative; padding: 1px; border-radius: 16px; background: rgba(255,255,255,0.05); box-shadow: 0 30px 60px rgba(0,0,0,0.6); display: flex; flex-direction: column; height: 100%; min-height: 480px; }
        .terminal-header { height: 40px; background: rgba(10,10,10,0.95); border-radius: 15px 15px 0 0; border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: space-between; padding: 0 20px; }
        .terminal-dots { display: flex; gap: 8px; }
        .terminal-dot { width: 12px; height: 12px; border-radius: 50%; opacity: 0.8; }
        .terminal-dot.red { background: #444; }
        .terminal-dot.yellow { background: #666; }
        .terminal-dot.green { background: #888; }
        .terminal-title { font-family: 'JetBrains Mono', monospace; font-size: 0.7rem; color: rgba(255,255,255,0.3); letter-spacing: 0.05em; }
        
        .terminal { flex: 1; background: rgba(7,7,7,0.95); border-radius: 0 0 15px 15px; padding: 24px; font-family: 'JetBrains Mono', monospace; font-size: 0.85rem; color: #eaeaea; overflow-y: auto; display: flex; flex-direction: column; position: relative; }
        .terminal::after { content: ''; position: absolute; inset: 0; background: repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(0,0,0,0.15) 2px, rgba(0,0,0,0.15) 4px); pointer-events: none; }
        
        .terminal-line { margin-bottom: 8px; line-height: 1.5; opacity: 0; animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; letter-spacing: -0.01em; position: relative; z-index: 2; }
        .terminal-line.error { color: #fff; border-left: 2px solid #fff; padding-left: 8px; }
        .terminal-line.warning { color: #ccc; }
        .terminal-line.info { color: #aaa; }
        
        @keyframes slideUp { 
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Status Cards */
        .status-card { position: relative; background: rgba(15,15,15,0.6); border: 1px solid rgba(255,255,255,0.06); border-radius: 16px; padding: 32px; backdrop-filter: blur(20px); transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.4s, border-color 0.4s; overflow: hidden; display: flex; flex-direction: column; justify-content: center; }
        .status-card:hover { transform: translateY(-4px); box-shadow: 0 20px 40px rgba(0,0,0,0.5); border-color: rgba(255,255,255,0.12); }
        
        .status-icon { margin-bottom: 16px; color: rgba(255,255,255,0.8); }
        .status-title { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.15em; color: rgba(255,255,255,0.5); margin-bottom: 12px; font-weight: 700; display: flex; align-items: center; gap: 8px; }
        .status-value { font-size: 2.75rem; font-family: 'JetBrains Mono', monospace; font-weight: 300; color: white; margin-bottom: 8px; letter-spacing: -0.04em; }
        .status-desc { font-size: 0.85rem; color: rgba(255,255,255,0.4); font-weight: 500; }

        .card-danger { border-color: rgba(255,255,255,0.3); background: rgba(20,20,20,0.8); }
        .card-danger:hover { border-color: rgba(255,255,255,0.6); box-shadow: 0 20px 40px rgba(255,255,255,0.05); }
        .card-danger .status-title { color: #fff; }
        .card-danger .status-value { color: #fff; font-weight: 500;}
        .card-danger .status-icon { color: #fff; }

        /* Chart container */
        .chart-wrapper { background: rgba(255,255,255,0.03); padding: 1px; border-radius: 16px; margin-top: 32px; border: 1px solid rgba(255,255,255,0.05); }
        .chart-container { background: rgba(12,12,12,0.9); border-radius: 15px; padding: 32px; position: relative; }
        
        /* Buttons */
        .actions-bar { display: flex; align-items: center; gap: 16px; background: rgba(255,255,255,0.02); padding: 20px 32px; border-radius: 16px; border: 1px solid rgba(255,255,255,0.05); backdrop-filter: blur(10px); }
        
        .btn { border: none; outline: none; padding: 14px 28px; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.15em; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 12px; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); position: relative; overflow: hidden; }
        
        .btn-primary { background: white; color: black; box-shadow: 0 4px 15px rgba(255,255,255,0.1); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(255,255,255,0.2); background: #f8f8f8; }
        .btn-primary:active { transform: translateY(0); box-shadow: 0 4px 10px rgba(255,255,255,0.1); }
        
        .btn-secondary { background: transparent; color: white; border: 1px solid rgba(255,255,255,0.2); }
        .btn-secondary:hover { background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.4); }
        .btn-secondary:active { transform: translateY(2px); }
        
        /* Status Badge */
        .status-badge { display: flex; align-items: center; gap: 12px; padding: 10px 20px; background: rgba(0,0,0,0.6); border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 30px; margin-left: auto; }
        .status-dot { width: 8px; height: 8px; background: #fff; border-radius: 50%; box-shadow: 0 0 12px #fff; animation: pulse 2s infinite; }
        .status-text { font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; color: #fff; letter-spacing: 0.1em; font-weight: 600; }
        
        @keyframes pulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4); }
            70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(255, 255, 255, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(255, 255, 255, 0); }
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 10px; height: 10px; }
        ::-webkit-scrollbar-track { background: rgba(0,0,0,0.2); }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 5px; border: 2px solid rgba(0,0,0,1); }
        ::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }
    </style>
</head>
<body>

<!-- Include Sidebar -->
<?php include 'includes/sidebar.php'; ?>

<!-- Main Content -->
<div class="main">
    
    <!-- Topbar -->
    <div class="topbar">
        <div>
            <div style="font-size: 1.25rem; font-weight: 700; color: white; letter-spacing: -0.02em;">ML Intelligence Simulation</div>
            <div style="font-size: 0.85rem; color: rgba(255,255,255,0.4); margin-top: 4px; font-weight: 500;">Live Demonstration Model</div>
        </div>
        <div style="display: flex; align-items: center; gap: 20px;">
            <div style="display: flex; align-items: center; gap: 12px; padding: 8px 16px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); border-radius: 30px;">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: #333; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 800; color: white; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);">
                    <?php echo strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)); ?>
                </div>
                <div>
                    <div style="font-size: 0.85rem; font-weight: 700; color: white;"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></div>
                    <div style="font-size: 0.7rem; color: rgba(255,255,255,0.4); font-weight: 500;">Administrator</div>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="actions-bar">
            <button id="btn-start" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-4 h-4" style="width: 16px; height: 16px;"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                Deploy Simulation
            </button>
            <button id="btn-stop" class="btn btn-secondary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-4 h-4" style="width: 16px; height: 16px;"><rect x="6" y="6" width="12" height="12"/></svg>
                Halt Engine
            </button>
            
            <div class="status-badge">
                <div class="status-dot"></div>
                <span class="status-text">PYTHON FLASK ENGINE ONLINE</span>
            </div>
        </div>

        <div class="grid-layout">
            <div style="display: flex; flex-direction: column;">
                <!-- Terminal -->
                <div class="terminal-container">
                    <div class="terminal-header">
                        <div class="terminal-dots">
                            <div class="terminal-dot red"></div>
                            <div class="terminal-dot yellow"></div>
                            <div class="terminal-dot green"></div>
                        </div>
                        <div class="terminal-title">isolation_forest_hook.py</div>
                        <div style="width: 44px;"></div>
                    </div>
                    <div class="terminal" id="terminal-out">
                        <div class="terminal-line info">> INIT ML_ENGINE_HOOK [v2.4.1]...</div>
                        <div class="terminal-line info">> SYSTEM RESOURCES OK. MEMORY ALLOCATION: 4.2GB</div>
                        <div class="terminal-line info">> MODEL LOADED: ISOLATION_FOREST_V3 (CONFIDENCE BASELINE: 0.98)</div>
                        <div class="terminal-line warning">> AWAITING DEPLOYMENT COMMAND...</div>
                    </div>
                </div>

                <!-- Chart -->
                <div class="chart-wrapper">
                    <div class="chart-container">
                        <div class="status-title" style="margin-bottom: 24px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                            Live Anomaly Scores
                        </div>
                        <div style="height: 200px; width: 100%;">
                            <canvas id="liveChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Side Stats -->
            <div style="display: flex; flex-direction: column; gap: 24px;">
                <div class="status-card">
                    <div class="status-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 24px; height: 24px;"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                    </div>
                    <div class="status-title">Transactions Processed</div>
                    <div class="status-value" id="stat-count">0</div>
                    <div class="status-desc">In current live session</div>
                </div>
                
                <div class="status-card card-danger">
                    <div class="status-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 24px; height: 24px;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    </div>
                    <div class="status-title">Anomalies Detected</div>
                    <div class="status-value" id="stat-anomalies">0</div>
                    <div class="status-desc">Fraudulent patterns isolated</div>
                </div>

                <div class="status-card">
                    <div class="status-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 24px; height: 24px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    </div>
                    <div class="status-title">Avg Confidence</div>
                    <div class="status-value" id="stat-confidence">0%</div>
                    <div class="status-desc">Algorithmic certainty score</div>
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
                borderColor: '#ffffff',
                backgroundColor: 'rgba(255, 255, 255, 0.05)',
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
                    grid: { color: 'rgba(255,255,255,0.05)' },
                    ticks: { color: 'rgba(255,255,255,0.3)', font: { family: 'JetBrains Mono', size: 10 } }
                }
            },
            plugins: { legend: { display: false } },
            animation: { duration: 0 }
        }
    });

    // Simulation Engine
    const terminal = document.getElementById('terminal-out');
    const logs = [];
    let isRunning = false;
    let simInterval;
    let trxCount = 0;
    let anomalyCount = 0;

    function logTerminal(msg, type = '') {
        const time = new Date().toISOString().split('T')[1].slice(0, 8);
        const div = document.createElement('div');
        div.className = `terminal-line ${type}`;
        div.textContent = `[${time}] ${msg}`;
        terminal.appendChild(div);
        terminal.scrollTop = terminal.scrollHeight;
        
        // limit terminal lines to 50
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
</body>
</html>
