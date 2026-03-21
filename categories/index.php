<?php require '../includes/header.php'; ?>
<?php require '../config/config.php'; ?>

<?php
    // Fetch all categories with product count
    $stmt = $conn->prepare("SELECT c.*, COUNT(p.id) as product_count 
                           FROM categories c 
                           LEFT JOIN products p ON c.id = p.category_id AND p.status = 1
                           GROUP BY c.id 
                           ORDER BY c.name ASC");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_OBJ);

    // Category visual config — monochrome white only
    $config = [
        ['icon' => 'briefcase',     'label' => 'COMMERCE'],
        ['icon' => 'smile',          'label' => 'YOUTH'],
        ['icon' => 'laptop',         'label' => 'DIGITAL'],
        ['icon' => 'globe',          'label' => 'WORLD'],
        ['icon' => 'heart',          'label' => 'PASSION'],
        ['icon' => 'flask-conical',  'label' => 'SCIENCE'],
        ['icon' => 'graduation-cap', 'label' => 'ACADEMY'],
        ['icon' => 'book-open',      'label' => 'FICTION'],
    ];
?>

<style>
    /* ─── Page base ─────────────────────── */
    #cat-page {
        min-height: 100vh;
        background: #000;
        color: #fff;
        font-family: 'JetBrains Mono', 'Courier New', monospace;
    }

    /* ─── Hero strip ────────────────────── */
    #cat-hero {
        position: relative;
        padding: clamp(80px,12vh,140px) 0 clamp(40px,6vh,80px);
        overflow: hidden;
    }
    #cat-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            radial-gradient(ellipse 80% 60% at 50% 0%, rgba(255,255,255,0.04) 0%, transparent 70%);
        pointer-events: none;
    }
    .cat-hero-grid-line {
        position: absolute;
        background: rgba(255,255,255,0.03);
    }
    .cat-hero-grid-line.v { width: 1px; top: 0; bottom: 0; }
    .cat-hero-grid-line.h { height: 1px; left: 0; right: 0; }

    /* ─── Category Cards ────────────────── */
    .cat-card {
        position: relative;
        border: 1px solid rgba(255,255,255,0.06);
        border-radius: 20px;
        overflow: hidden;
        background: rgba(255,255,255,0.02);
        transition: transform 0.45s cubic-bezier(.22,.68,0,1.2),
                    border-color 0.35s ease,
                    box-shadow 0.45s ease;
        cursor: pointer;
        text-decoration: none;
        display: flex;
        flex-direction: column;
        padding: 28px;
        min-height: 240px;
    }
    .cat-card:hover {
        transform: translateY(-6px);
        border-color: rgba(255,255,255,0.14);
        box-shadow: 0 28px 70px rgba(0,0,0,0.7),
                    inset 0 1px 0 rgba(255,255,255,0.06);
        text-decoration: none;
    }

    /* White shimmer on hover — no colors */
    .cat-card::after {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: inherit;
        background: radial-gradient(circle at 30% 90%, rgba(255,255,255,0.04) 0%, transparent 60%);
        opacity: 0;
        transition: opacity 0.5s ease;
        pointer-events: none;
    }
    .cat-card:hover::after { opacity: 1; }

    .cat-card .card-noise {
        position: absolute;
        inset: 0;
        opacity: 0.025;
        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
        background-size: 180px 180px;
        pointer-events: none;
    }


    /* icon wrapper */
    .cat-icon-wrap {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.06);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.3s, border-color 0.3s;
        margin-bottom: auto;
    }
    .cat-card:hover .cat-icon-wrap {
        background: rgba(255,255,255,0.08);
        border-color: rgba(255,255,255,0.1);
    }

    /* index number */
    .cat-index {
        font-size: 9px;
        letter-spacing: 0.3em;
        color: rgba(255,255,255,0.18);
    }

    /* title */
    .cat-title {
        font-family: 'Georgia', serif;
        font-size: clamp(1.1rem, 2vw, 1.35rem);
        font-weight: 400;
        color: #fff;
        letter-spacing: -0.01em;
        margin: 0 0 6px;
        line-height: 1.25;
    }

    /* description */
    .cat-desc {
        font-size: 11px;
        line-height: 1.7;
        color: rgba(255,255,255,0.35);
        letter-spacing: 0.02em;
        margin: 0;
    }

    /* count pill */
    .cat-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 18px;
        padding: 4px 12px;
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 999px;
        font-size: 9px;
        letter-spacing: 0.2em;
        color: rgba(255,255,255,0.4);
        transition: border-color 0.3s, color 0.3s;
        width: fit-content;
    }
    .cat-card:hover .cat-pill {
        border-color: rgba(255,255,255,0.2);
        color: rgba(255,255,255,0.7);
    }
    .cat-pill-dot {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: currentColor;
        opacity: 0.6;
    }

    /* arrow */
    .cat-arrow {
        position: absolute;
        bottom: 24px;
        right: 24px;
        opacity: 0;
        transform: translate(-6px, 6px);
        transition: opacity 0.3s, transform 0.35s cubic-bezier(.22,.68,0,1.2);
    }
    .cat-card:hover .cat-arrow {
        opacity: 1;
        transform: translate(0, 0);
    }

    /* ─── CTA strip ─────────────────────── */
    #cat-cta {
        margin: 0 auto;
        max-width: 900px;
        border: 1px solid rgba(255,255,255,0.06);
        border-radius: 20px;
        padding: 52px 48px;
        background: rgba(255,255,255,0.02);
        backdrop-filter: blur(8px);
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    #cat-cta::before {
        content: '';
        position: absolute;
        top: -80px; left: 50%; transform: translateX(-50%);
        width: 400px; height: 200px;
        background: radial-gradient(ellipse, rgba(255,255,255,0.04), transparent 70%);
        pointer-events: none;
    }

    /* ─── empty state ───────────────────── */
    .cat-empty {
        grid-column: 1 / -1;
        padding: 120px 0;
        text-align: center;
    }

    /* ─── Scan line animation ───────────── */
    @keyframes catScan {
        0%   { transform: translateY(-100%); opacity: 0; }
        10%  { opacity: 1; }
        90%  { opacity: 1; }
        100% { transform: translateY(200vh); opacity: 0; }
    }
    .cat-scanline {
        position: fixed;
        top: 0; left: 0; right: 0;
        height: 2px;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.03), transparent);
        animation: catScan 8s linear infinite;
        pointer-events: none;
        z-index: 999;
    }

    /* ─── Stagger fade-in ───────────────── */
    @keyframes catFadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .cat-card { animation: catFadeUp 0.6s ease both; }
</style>

<div id="cat-page">
    <div class="cat-scanline"></div>

    <!-- ══ HERO ══════════════════════════════════ -->
    <div id="cat-hero">
        <!-- Grid lines -->
        <div class="cat-hero-grid-line v" style="left:16.66%"></div>
        <div class="cat-hero-grid-line v" style="left:33.33%"></div>
        <div class="cat-hero-grid-line v" style="left:50%"></div>
        <div class="cat-hero-grid-line v" style="left:66.66%"></div>
        <div class="cat-hero-grid-line v" style="left:83.33%"></div>
        <div class="cat-hero-grid-line h" style="bottom:0"></div>

        <div style="max-width:1280px; margin:0 auto; padding:0 clamp(20px,5vw,80px); position:relative; z-index:2;">

            <!-- Eyebrow -->
            <div style="display:flex; align-items:center; gap:16px; margin-bottom:28px; opacity:0.4;">
                <div style="width:40px; height:1px; background:white;"></div>
                <span style="font-size:9px; letter-spacing:0.3em; text-transform:uppercase; color:white;">
                    002 · Genres &amp; Subjects
                </span>
                <div style="flex:1; height:1px; background:white;"></div>
            </div>

            <!-- Title -->
            <div style="max-width:640px;">
                <h1 style="font-family:'Georgia',serif; font-size:clamp(2.4rem,6vw,4.5rem); font-weight:400; line-height:1.05; letter-spacing:-0.02em; margin:0 0 20px; color:#fff;">
                    Browse by<br>
                    <span style="background:linear-gradient(to right,#fff,#555); -webkit-background-clip:text; -webkit-text-fill-color:transparent; font-style:italic;">
                        Category
                    </span>
                </h1>
                <p style="font-size:11px; line-height:2; color:rgba(255,255,255,0.35); letter-spacing:0.08em; max-width:400px; margin:0;">
                    Every great library begins with order.<br>
                    Explore our curated collection, organised by genre and discipline.
                </p>
            </div>

            <!-- Stats row -->
            <div style="display:flex; gap:32px; margin-top:36px; flex-wrap:wrap;">
                <div style="display:flex; align-items:baseline; gap:8px;">
                    <span style="font-family:'Georgia',serif; font-size:1.8rem; color:#fff;"><?php echo count($categories); ?></span>
                    <span style="font-size:9px; letter-spacing:0.25em; color:rgba(255,255,255,0.2); text-transform:uppercase;">Genres</span>
                </div>
                <?php
                    $totalBooks = array_sum(array_column($categories, 'product_count'));
                ?>
                <div style="display:flex; align-items:baseline; gap:8px;">
                    <span style="font-family:'Georgia',serif; font-size:1.8rem; color:#fff;"><?php echo $totalBooks; ?>+</span>
                    <span style="font-size:9px; letter-spacing:0.25em; color:rgba(255,255,255,0.2); text-transform:uppercase;">Volumes</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ══ CATEGORIES GRID ═══════════════════════ -->
    <div style="max-width:1280px; margin:0 auto; padding:clamp(32px,6vh,64px) clamp(20px,5vw,80px) clamp(60px,10vh,120px);">
        <div style="
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 16px;
        ">
            <?php if($categories): ?>
                <?php foreach($categories as $index => $category) :
                    $c = $config[$index % count($config)];
                    $delay = ($index * 0.07);
                ?>
                    <a href="category.php?id=<?php echo $category->id; ?>"
                       class="cat-card"
                       style="animation-delay: <?php echo $delay; ?>s;">

                        <!-- Noise texture -->
                        <div class="card-noise"></div>

                        <!-- Top row: icon + label -->
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:auto;">
                            <div class="cat-icon-wrap">
                                <i data-lucide="<?php echo $c['icon']; ?>" style="width:18px;height:18px;color:rgba(255,255,255,0.55);stroke-width:1.5;"></i>
                            </div>
                            <span class="cat-index"><?php echo str_pad($index + 1, 2, '0', STR_PAD_LEFT); ?></span>
                        </div>

                        <!-- Bottom: text -->
                        <div style="margin-top:32px; position:relative; z-index:1;">
                            <div style="width:24px; height:1px; background:rgba(255,255,255,0.12); margin-bottom:14px; transition:width 0.4s ease;" class="cat-divider"></div>
                            <h3 class="cat-title"><?php echo htmlspecialchars($category->name); ?></h3>
                            <p class="cat-desc"><?php echo htmlspecialchars($category->description ?? 'Explore our curated selection'); ?></p>

                            <!-- Count pill -->
                            <div class="cat-pill">
                                <div class="cat-pill-dot" style="background:rgba(255,255,255,0.5);"></div>
                                <?php echo $category->product_count; ?> <?php echo $category->product_count == 1 ? 'Volume' : 'Volumes'; ?>
                            </div>
                        </div>

                        <!-- Arrow -->
                        <div class="cat-arrow">
                            <i data-lucide="arrow-up-right" style="width:16px;height:16px;color:rgba(255,255,255,0.5);"></i>
                        </div>
                    </a>
                <?php endforeach; ?>

            <?php else: ?>
                <div class="cat-empty">
                    <i data-lucide="library" style="width:48px;height:48px;color:rgba(255,255,255,0.1);margin-bottom:20px;display:block;margin-left:auto;margin-right:auto;"></i>
                    <p style="font-size:10px; letter-spacing:0.3em; text-transform:uppercase; color:rgba(255,255,255,0.2);">No categories yet</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ══ CTA ══════════════════════════════════ -->
    <div style="padding: 0 clamp(20px,5vw,80px) clamp(80px,12vh,140px);">
        <div id="cat-cta">
            <!-- Corner accents -->
            <div style="position:absolute; top:16px; left:16px; width:20px; height:20px; border-top:1px solid rgba(255,255,255,0.1); border-left:1px solid rgba(255,255,255,0.1);"></div>
            <div style="position:absolute; bottom:16px; right:16px; width:20px; height:20px; border-bottom:1px solid rgba(255,255,255,0.1); border-right:1px solid rgba(255,255,255,0.1);"></div>

            <div style="position:relative; z-index:1;">
                <p style="font-size:9px; letter-spacing:0.3em; color:rgba(255,255,255,0.2); text-transform:uppercase; margin-bottom:16px;">Looking for something specific?</p>
                <h2 style="font-family:'Georgia',serif; font-size:clamp(1.6rem,3vw,2.4rem); font-weight:400; color:#fff; margin:0 0 12px; letter-spacing:-0.01em;">
                    Can't find what you seek?
                </h2>
                <p style="font-size:11px; line-height:1.9; color:rgba(255,255,255,0.35); max-width:420px; margin:0 auto 32px; letter-spacing:0.04em;">
                    Our editors curate new titles constantly. Browse the full archive or reach out — we'd be glad to assist.
                </p>
                <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
                    <a href="<?php echo APPURL; ?>"
                       style="display:inline-flex;align-items:center;gap:8px;padding:11px 28px;border:1px solid white;color:white;font-size:10px;letter-spacing:0.2em;text-transform:uppercase;text-decoration:none;transition:background 0.2s,color 0.2s;"
                       onmouseover="this.style.background='white';this.style.color='black';"
                       onmouseout="this.style.background='transparent';this.style.color='white';">
                        <i data-lucide="book" style="width:13px;height:13px;"></i>
                        Full Collection
                    </a>
                    <a href="<?php echo APPURL; ?>pages/contact.php"
                       style="display:inline-flex;align-items:center;gap:8px;padding:11px 28px;border:1px solid rgba(255,255,255,0.25);color:rgba(255,255,255,0.6);font-size:10px;letter-spacing:0.2em;text-transform:uppercase;text-decoration:none;transition:background 0.2s,color 0.2s,border-color 0.2s;"
                       onmouseover="this.style.background='white';this.style.color='black';this.style.borderColor='white';"
                       onmouseout="this.style.background='transparent';this.style.color='rgba(255,255,255,0.6)';this.style.borderColor='rgba(255,255,255,0.25)';">
                        <i data-lucide="mail" style="width:13px;height:13px;"></i>
                        Contact Us
                    </a>
                </div>
            </div>
        </div>
    </div>

</div><!-- #cat-page -->

<script>
// Expand divider line on card hover
document.querySelectorAll('.cat-card').forEach(card => {
    const line = card.querySelector('.cat-divider');
    card.addEventListener('mouseenter', () => { if(line) line.style.width = '40px'; });
    card.addEventListener('mouseleave', () => { if(line) line.style.width = '24px'; });
});
</script>

<?php require '../includes/footer.php'; ?>