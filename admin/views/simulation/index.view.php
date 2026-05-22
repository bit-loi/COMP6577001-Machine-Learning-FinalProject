<?php
/**
 * ML Simulation View Template
 * 
 * Renders the live fraud detection simulation terminal and analytics.
 */
?>
<link rel="stylesheet" href="<?php echo APPURL; ?>admin/assets/css/admin-components.css">

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

<script>
window.simConfig = {
    apiUrl: 'http://localhost:5000/predict'
};
// Active menu item
document.querySelectorAll('.nav-item').forEach(item => {
    if(item.textContent.includes('ML Simulation')) {
        item.classList.add('active');
    } else {
        item.classList.remove('active');
    }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?php echo APPURL; ?>admin/assets/js/simulation-engine.js"></script>
