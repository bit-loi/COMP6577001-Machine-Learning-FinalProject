<?php require 'includes/header.php'; ?>
<?php require 'config/config.php'; ?>
<?php require 'includes/book-cover.php'; ?>

<?php
    $stmt = $conn->prepare("SELECT * FROM products WHERE status = 1 ORDER BY created_at DESC LIMIT 8"); 
    $stmt->execute();
    $allProducts = $stmt->fetchAll(PDO::FETCH_OBJ);
?>

<style>
    .glass { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.05); }
    .gradient-text { background: linear-gradient(to right, #fff, #666); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

    /* ===== HERO STYLES ===== */
    #hero-section {
        position: relative;
        min-height: 100vh;
        width: 100%;
        background: #000;
        overflow: hidden;
    }
    #us-bg {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
    }
    #us-bg [data-us-project] {
        width: 100%;
        height: 100%;
        min-height: 100vh;
        position: relative;
        overflow: hidden;
    }
    #us-bg [data-us-project] canvas {
        clip-path: inset(0 0 8% 0);
    }
    /* hide branding */
    [data-us-project] a[href*="unicorn"],
    [data-us-project] button[title*="unicorn"],
    [data-us-project] div[title*="Made with"],
    [data-us-project] .unicorn-brand,
    [data-us-project] [class*="brand"],
    [data-us-project] [class*="credit"],
    [data-us-project] [class*="watermark"] {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
        position: absolute !important;
        left: -9999px !important;
    }
    /* Mobile stars fallback */
    .stars-bg {
        background-image:
            radial-gradient(1px 1px at 20% 30%, white, transparent),
            radial-gradient(1px 1px at 60% 70%, white, transparent),
            radial-gradient(1px 1px at 50% 50%, white, transparent),
            radial-gradient(1px 1px at 80% 10%, white, transparent),
            radial-gradient(1px 1px at 90% 60%, white, transparent),
            radial-gradient(1px 1px at 33% 80%, white, transparent),
            radial-gradient(1px 1px at 15% 60%, white, transparent),
            radial-gradient(1px 1px at 70% 40%, white, transparent);
        background-size: 200% 200%, 180% 180%, 250% 250%, 220% 220%, 190% 190%, 240% 240%, 210% 210%, 230% 230%;
        opacity: 0.25;
    }
    /* Dither pattern */
    .dither-bar {
        background-image:
            repeating-linear-gradient(0deg, transparent 0px, transparent 1px, white 1px, white 2px),
            repeating-linear-gradient(90deg, transparent 0px, transparent 1px, white 1px, white 2px);
        background-size: 3px 3px;
    }
    /* Corner accents */
    .corner { position: absolute; width: 48px; height: 48px; z-index: 20; }
    .corner-tl { top: 0; left: 0; border-top: 2px solid rgba(255,255,255,0.25); border-left: 2px solid rgba(255,255,255,0.25); }
    .corner-tr { top: 0; right: 0; border-top: 2px solid rgba(255,255,255,0.25); border-right: 2px solid rgba(255,255,255,0.25); }
    .corner-bl { bottom: 5vh; left: 0; border-bottom: 2px solid rgba(255,255,255,0.25); border-left: 2px solid rgba(255,255,255,0.25); }
    .corner-br { bottom: 5vh; right: 0; border-bottom: 2px solid rgba(255,255,255,0.25); border-right: 2px solid rgba(255,255,255,0.25); }
    /* Hero content */
    #hero-content {
        position: relative;
        z-index: 10;
        display: flex;
        align-items: center;
        min-height: 100vh;
        padding-top: 5vh;
    }
    /* Animations */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(24px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to   { opacity: 1; }
    }
    @keyframes scanline {
        0%   { transform: translateY(-100%); }
        100% { transform: translateY(100vh); }
    }
    @keyframes blink {
        0%, 100% { opacity: 1; } 50% { opacity: 0; }
    }
    @keyframes barPulse {
        0%, 100% { transform: scaleY(1); opacity: 0.3; }
        50%       { transform: scaleY(1.8); opacity: 0.7; }
    }
    .hero-line-1 { animation: fadeInUp 0.8s ease 0.2s both; }
    .hero-line-2 { animation: fadeInUp 0.8s ease 0.4s both; }
    .hero-line-3 { animation: fadeInUp 0.8s ease 0.6s both; }
    .hero-line-4 { animation: fadeInUp 0.8s ease 0.8s both; }
    .hero-line-5 { animation: fadeInUp 0.8s ease 1.0s both; }
    .hero-line-6 { animation: fadeInUp 0.8s ease 1.2s both; }
    .scanline {
        position: absolute;
        left: 0; right: 0;
        height: 2px;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.03), transparent);
        animation: scanline 6s linear infinite;
        z-index: 5;
        pointer-events: none;
    }
    .hero-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 24px;
        font-family: 'JetBrains Mono', 'Courier New', monospace;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.15em;
        text-transform: uppercase;
        text-decoration: none;
        border: 1px solid white;
        color: white;
        background: transparent;
        cursor: pointer;
        transition: background 0.2s, color 0.2s;
        position: relative;
    }
    .hero-btn:hover { background: white; color: #000; }
    .hero-btn-secondary {
        border-color: rgba(255,255,255,0.35);
        color: rgba(255,255,255,0.7);
    }
    .hero-btn-secondary:hover { background: white; color: #000; border-color: white; }
    /* Corner hover accents on primary btn */
    .hero-btn .btn-corner-tl,
    .hero-btn .btn-corner-br {
        position: absolute;
        width: 8px; height: 8px;
        opacity: 0;
        transition: opacity 0.2s;
    }
    .hero-btn .btn-corner-tl { top: -2px; left: -2px; border-top: 1px solid white; border-left: 1px solid white; }
    .hero-btn .btn-corner-br { bottom: -2px; right: -2px; border-bottom: 1px solid white; border-right: 1px solid white; }
    .hero-btn:hover .btn-corner-tl,
    .hero-btn:hover .btn-corner-br { opacity: 1; }
    .mono-xs { font-family: 'JetBrains Mono', 'Courier New', monospace; font-size: 9px; color: rgba(255,255,255,0.35); letter-spacing: 0.1em; }
    @media (max-width: 768px) {
        #us-bg { display: none; }
        .corner { width: 28px; height: 28px; }
        #hero-statusbar { padding: 8px 16px; }
    }
</style>

<!-- ===== HERO SECTION ===== -->
<section id="hero-section">

    <!-- UnicornStudio BG (Always display) -->
    <div id="us-bg">
        <div data-us-project="whwOGlfJ5Rz2rHaEUgHl"></div>
    </div>

    <!-- Pure CSS Stars Fallback (Always display behind WebGL) -->
    <div class="stars-bg" style="position: absolute; top:0; left:0; width:100%; height:100%; z-index:0;"></div>

    <!-- Scanline effect -->
    <div class="scanline"></div>

    <!-- Corner accents -->
    <div class="corner corner-tl"></div>
    <div class="corner corner-tr"></div>
    <div class="corner corner-bl"></div>
    <div class="corner corner-br"></div>

    <!-- Main content -->
    <div id="hero-content">
        <div style="padding: 0 32px 0 48px; max-width: 1280px; margin: 0 auto; width: 100%;">
            <div style="max-width: 520px; margin-left: clamp(0px, 8vw, 120px);">

                <!-- Eyebrow line -->
                <div class="hero-line-1" style="display:flex; align-items:center; gap:12px; margin-bottom:20px; opacity:0.5;">
                    <div style="width:32px; height:1px; background:white;"></div>
                    <span style="font-family:'JetBrains Mono','Courier New',monospace; font-size:10px; letter-spacing:0.25em; color:white; text-transform:uppercase;">001 · Premeditatio Malorum</span>
                    <div style="flex:1; height:1px; background:white;"></div>
                </div>

                <!-- Dither accent -->
                <div class="hero-line-2" style="position:relative;">
                    <div class="dither-bar hidden lg:block" style="position:absolute; left:-12px; top:0; bottom:0; width:4px; opacity:0.35; border-radius:2px;"></div>

                    <!-- Title -->
                    <h1 class="hero-line-2" style="font-family:'JetBrains Mono','Courier New',monospace; font-size:clamp(2rem,5vw,3.75rem); font-weight:700; color:white; line-height:1.05; letter-spacing:0.08em; margin:0 0 8px 0;">
                        MEMENTO
                    </h1>
                    <h1 class="hero-line-3" style="font-family:'JetBrains Mono','Courier New',monospace; font-size:clamp(2rem,5vw,3.75rem); font-weight:700; color:rgba(255,255,255,0.65); line-height:1.05; letter-spacing:0.08em; margin:0 0 24px 0;">
                        MORI.
                    </h1>
                </div>

                <!-- Dot row -->
                <div class="hero-line-3 hidden lg:flex" style="gap:4px; margin-bottom:20px; opacity:0.2;" id="dot-row"></div>

                <!-- Description -->
                <p class="hero-line-4" style="font-family:'JetBrains Mono','Courier New',monospace; font-size:0.8rem; color:rgba(255,255,255,0.55); line-height:1.9; margin:0 0 32px 0; max-width:400px;">
                    Curated literature for those who contemplate the inevitable.<br>
                    Books that challenge, endure, and illuminate the human condition.
                </p>

                <!-- CTA Buttons -->
                <div class="hero-line-5" style="display:flex; flex-wrap:wrap; gap:12px; margin-bottom:32px;">
                    <a href="<?php echo APPURL; ?>categories/index.php" class="hero-btn">
                        <span class="btn-corner-tl"></span>
                        <span class="btn-corner-br"></span>
                        EXPLORE COLLECTION
                    </a>
                    <a href="<?php echo APPURL; ?>auth/register.php" class="hero-btn hero-btn-secondary">
                        JOIN THE CIRCLE
                    </a>
                </div>

                <!-- Bottom notation -->
                <div class="hero-line-6 hidden lg:flex" style="align-items:center; gap:12px; opacity:0.25;">
                    <span style="font-family:'JetBrains Mono',monospace; font-size:10px; color:white;">∞</span>
                    <div style="flex:1; height:1px; background:white;"></div>
                    <span style="font-family:'JetBrains Mono',monospace; font-size:10px; color:white; letter-spacing:0.15em;">VITRUVIAN · EST. MMXXV</span>
                </div>

            </div>
        </div>
    </div>

    <!-- Status bar -->
    <div id="hero-statusbar">
        <div style="display:flex; align-items:center; gap:24px;">
            <span class="mono-xs">SYSTEM.ACTIVE</span>
            <div style="display:flex; gap:3px; align-items:flex-end; height:16px;" id="bar-viz"></div>
            <span class="mono-xs">V1.0.0</span>
        </div>
        <div style="display:flex; align-items:center; gap:8px;">
            <span class="mono-xs hidden lg:inline">◐ RENDERING</span>
            <div style="display:flex; gap:4px; align-items:center;">
                <div class="pulse-dot"></div>
                <div class="pulse-dot"></div>
                <div class="pulse-dot"></div>
            </div>
            <span class="mono-xs hidden lg:inline">FRAME: ∞</span>
        </div>
    </div>

</section>
<!-- ===== END HERO ===== -->

<script>
// ── UnicornStudio embed ──────────────────────────────────────────
!function(){
  if(!window.UnicornStudio){
    window.UnicornStudio={isInitialized:!1};
    var i=document.createElement('script');
    i.src='https://cdn.jsdelivr.net/gh/hiunicornstudio/unicornstudio.js@v1.4.33/dist/unicornStudio.umd.js';
    i.onload=function(){
      window.UnicornStudio.isInitialized||(UnicornStudio.init(),window.UnicornStudio.isInitialized=!0);
    };
    (document.head||document.body).appendChild(i);
  }
}();

// ── Hide branding periodically ───────────────────────────────────
function hideBranding(){
  var el=document.querySelector('[data-us-project]');
  if(el){
    el.querySelectorAll('*').forEach(function(n){
      var t=(n.textContent||'').toLowerCase();
      if(t.includes('made with')||t.includes('unicorn'))n.remove();
    });
  }
}
hideBranding();
var _hbInterval=setInterval(hideBranding,150);
setTimeout(function(){ clearInterval(_hbInterval); },8000);
[500,1500,3000,5000].forEach(function(d){ setTimeout(hideBranding,d); });

// ── Generate dot row ─────────────────────────────────────────────
var dotRow=document.getElementById('dot-row');
if(dotRow){
  for(var d=0;d<48;d++){
    var dot=document.createElement('div');
    dot.style.cssText='width:3px;height:3px;background:white;border-radius:50%;flex-shrink:0;';
    dotRow.appendChild(dot);
  }
}

// ── Generate bar visualizer ──────────────────────────────────────
var barViz=document.getElementById('bar-viz');
if(barViz){
  for(var b=0;b<10;b++){
    var bar=document.createElement('div');
    var h=Math.floor(Math.random()*10)+4;
    bar.style.cssText='width:3px;background:rgba(255,255,255,0.3);border-radius:1px;height:'+h+'px;animation:barPulse '+(0.8+Math.random()*0.8).toFixed(2)+'s ease-in-out '+(Math.random()*0.5).toFixed(2)+'s infinite;';
    barViz.appendChild(bar);
  }
}
</script>

<!-- ===== Bento Feature Section (React) ===== -->
<div id="react-collection-feature"></div>

<!-- ===== New Arrivals Product Grid ===== -->
<section class="max-w-7xl mx-auto px-6 pb-32 space-y-12" id="products">
    <div class="flex flex-col md:flex-row justify-between items-end gap-6">
        <div class="space-y-2">
            <div class="flex items-center gap-3">
                <div style="width:24px;height:1px;background:rgba(255,255,255,0.2);"></div>
                <span style="font-family:'JetBrains Mono',monospace;font-size:9px;letter-spacing:0.25em;color:rgba(255,255,255,0.2);text-transform:uppercase;">New Arrivals</span>
            </div>
            <h2 class="text-3xl md:text-4xl font-serif text-white">Latest <span class="gradient-text italic">Additions</span></h2>
        </div>
        <a href="<?php echo APPURL; ?>categories/index.php" class="text-xs uppercase tracking-widest text-white/50 hover:text-white transition-colors flex items-center gap-2">
            Explore All <i data-lucide="arrow-right" class="w-3 h-3"></i>
        </a>
    </div>

    <!-- Products Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">
        <?php if($allProducts): ?>
            <?php foreach($allProducts as $product) : ?>
                <div class="group cursor-pointer">
                    <div class="relative aspect-[3/4.5] bg-[#0c0c0c] rounded-xl overflow-hidden border border-white/5 shadow-xl transition-all duration-500 group-hover:-translate-y-2 group-hover:shadow-[0_20px_50px_rgba(0,0,0,0.5)]">
                        <!-- Book Cover Mockup -->
                        <div class="absolute inset-0 flex items-center justify-center p-8">
                            <div class="relative w-full h-full shadow-[20px_20px_40px_rgba(0,0,0,0.8)] transform -rotate-2 group-hover:rotate-0 transition-transform duration-700">
                                <?php echo getBookCoverImage($product->isbn, $product->name, 'M', 'w-full h-full object-cover rounded-sm'); ?>
                                <div class="absolute inset-x-0 bottom-0 h-1/3 bg-gradient-to-t from-black/60 to-transparent"></div>
                            </div>
                        </div>
                        
                        <!-- Hover Action -->
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                            <a href="<?php echo APPURL; ?>shopping/single.php?id=<?php echo $product->id; ?>" class="bg-white text-black px-6 py-2.5 rounded-full text-xs font-semibold uppercase tracking-widest transform translate-y-4 group-hover:translate-y-0 transition-transform">
                                Discover
                            </a>
                        </div>
                    </div>

                    <div class="mt-6 space-y-1">
                        <div class="flex justify-between items-start">
                            <h3 class="text-white font-medium text-sm group-hover:text-white/70 transition-colors line-clamp-1 flex-1">
                                <?php echo htmlspecialchars($product->name); ?>
                            </h3>
                            <span class="text-white/40 text-xs ml-4">#<?php echo substr($product->isbn, -4); ?></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <p class="text-white/80 font-semibold">$<?php echo number_format($product->price, 2); ?></p>
                            <?php if(isset($product->discount_price) && $product->discount_price > 0): ?>
                                <p class="text-white/20 text-xs line-through">$<?php echo number_format($product->discount_price, 2); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-full h-[400px] flex items-center justify-center glass rounded-3xl">
                <div class="text-center opacity-20">
                    <i data-lucide="book-x" class="w-12 h-12 mx-auto mb-4"></i>
                    <p class="uppercase tracking-widest text-xs">Collection is being curated</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>



<?php require 'includes/footer.php'; ?>

