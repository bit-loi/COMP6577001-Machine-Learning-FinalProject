<style>
.seller-page { background: #f0f2f5; min-height: 78vh; padding: 28px 0 56px; }
.seller-wrap { max-width: 1240px; margin: 0 auto; padding: 0 20px; }
.seller-breadcrumb { display: flex; align-items: center; gap: 8px; font-size: 0.78rem; color: #888; margin-bottom: 20px; }
.seller-breadcrumb a { color: #888; text-decoration: none; transition: color .15s; }
.seller-breadcrumb a:hover { color: #EE4D2D; }
.seller-breadcrumb span { color: #ccc; }
.seller-breadcrumb strong { color: #333; font-weight: 600; }
.seller-layout { display: grid; grid-template-columns: 260px 1fr; gap: 24px; align-items: start; }
@media (max-width: 900px) {
    .seller-layout { grid-template-columns: 1fr; }
    .seller-sidebar-wrap { order: -1; }
}
.seller-card {
    background: #fff; border-radius: 16px; border: 1px solid #e8e8e8;
    box-shadow: 0 4px 24px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04);
    overflow: hidden;
}
.seller-card-pad { padding: 24px 28px; }
.stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
@media (max-width: 1100px) { .stat-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 500px)  { .stat-grid { grid-template-columns: 1fr; } }
.seller-two-col { display: grid; grid-template-columns: 1fr 340px; gap: 20px; align-items: start; }
@media (max-width: 1000px) { .seller-two-col { grid-template-columns: 1fr; } }
.stat-box {
    background: #fff; border-radius: 16px; border: 1px solid #e8e8e8;
    padding: 22px 24px; position: relative; overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    transition: transform .2s, box-shadow .2s;
}
.stat-box:hover { transform: translateY(-3px); box-shadow: 0 8px 28px rgba(0,0,0,0.09); }
.stat-box .stat-icon {
    width: 44px; height: 44px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center; margin-bottom: 14px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
.stat-box .stat-label { font-size: 0.75rem; color: #888; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 4px; }
.stat-box .stat-value { font-size: 1.65rem; font-weight: 800; color: #111; letter-spacing: -0.02em; }
.stat-box .stat-sub { font-size: 0.72rem; color: #aaa; margin-top: 4px; }
.welcome-banner {
    background: linear-gradient(135deg, #C53D20 0%, #EE4D2D 50%, #FF6B35 100%);
    border-radius: 16px; padding: 28px 32px; color: #fff; margin-bottom: 24px;
    display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;
    box-shadow: 0 12px 40px rgba(238,77,45,0.3); position: relative; overflow: hidden;
}
.welcome-banner::before {
    content: ''; position: absolute; right: -30px; top: -30px;
    width: 160px; height: 160px; background: rgba(255,255,255,0.1); border-radius: 50%;
}
.welcome-banner-inner { position: relative; z-index: 1; }
.welcome-banner h1 { font-size: 1.4rem; font-weight: 800; margin: 0 0 6px; }
.welcome-banner p { font-size: 0.88rem; opacity: 0.9; margin: 0; max-width: 480px; }
.btn-seller {
    display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px;
    background: #fff; color: #EE4D2D; font-weight: 700; font-size: 0.88rem;
    border-radius: 12px; text-decoration: none; border: none; cursor: pointer;
    box-shadow: 0 4px 16px rgba(0,0,0,0.15); transition: transform .15s, box-shadow .15s;
    font-family: inherit; flex-shrink: 0;
}
.btn-seller:hover { transform: translateY(-2px); box-shadow: 0 6px 24px rgba(0,0,0,0.2); }
.btn-seller-outline {
    display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px;
    background: transparent; color: #EE4D2D; font-weight: 600; font-size: 0.85rem;
    border-radius: 10px; text-decoration: none; border: 2px solid #EE4D2D;
    transition: all .15s; font-family: inherit;
}
.btn-seller-outline:hover { background: #FFF4ED; }
.product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 20px; }
.product-card-seller {
    background: #fff; border-radius: 14px; border: 1px solid #e8e8e8;
    overflow: hidden; transition: transform .2s, box-shadow .2s;
    box-shadow: 0 2px 12px rgba(0,0,0,0.05);
}
.product-card-seller:hover { transform: translateY(-4px); box-shadow: 0 10px 32px rgba(0,0,0,0.1); }
.sale-row {
    display: flex; align-items: center; gap: 14px; padding: 14px 0;
    border-bottom: 1px solid #f0f0f0;
}
.sale-row:last-child { border-bottom: none; }
.how-step {
    display: flex; align-items: flex-start; gap: 14px; padding: 14px 0;
    border-bottom: 1px solid #f5f5f5;
}
.how-step:last-child { border-bottom: none; }
.how-num {
    width: 32px; height: 32px; border-radius: 10px; background: linear-gradient(135deg,#10b981,#059669);
    color: #fff; font-weight: 800; font-size: 0.85rem;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    box-shadow: 0 4px 10px rgba(16,185,129,0.3);
}
</style>
