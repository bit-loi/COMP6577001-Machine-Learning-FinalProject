<style>
.account-page { background: #f0f2f5; min-height: 78vh; padding: 28px 0 56px; }
.account-wrap { max-width: 1180px; margin: 0 auto; padding: 0 20px; }
.account-breadcrumb { display: flex; align-items: center; gap: 8px; font-size: 0.78rem; color: #888; margin-bottom: 20px; }
.account-breadcrumb a { color: #888; text-decoration: none; transition: color .15s; }
.account-breadcrumb a:hover { color: #EE4D2D; }
.account-breadcrumb span { color: #ccc; }
.account-breadcrumb strong { color: #333; font-weight: 600; }
.account-layout { display: grid; grid-template-columns: 260px 1fr; gap: 24px; align-items: start; }
@media (max-width: 900px) {
    .account-layout { grid-template-columns: 1fr; }
    .account-sidebar { order: -1; }
}
.account-card {
    background: #fff;
    border-radius: 16px;
    border: 1px solid #e8e8e8;
    box-shadow: 0 4px 24px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04);
    overflow: hidden;
}
.account-card-pad { padding: 28px 32px; }
.account-card-title {
    font-size: 1.05rem; font-weight: 700; color: #111;
    margin: 0 0 20px; display: flex; align-items: center; gap: 10px;
}
.account-card-title .icon-wrap {
    width: 36px; height: 36px; border-radius: 10px;
    background: #FFF4ED; color: #EE4D2D;
    display: flex; align-items: center; justify-content: center;
}
.alert-success {
    padding: 14px 18px; background: #ecfdf5; color: #059669;
    border-radius: 12px; font-size: 0.88rem; font-weight: 500;
    border: 1px solid #bbf7d0; display: flex; align-items: center; gap: 10px;
    box-shadow: 0 2px 8px rgba(5,150,105,0.08);
}
.alert-error {
    padding: 14px 18px; background: #fef2f2; color: #dc2626;
    border-radius: 12px; font-size: 0.88rem; font-weight: 500;
    border: 1px solid #fecaca; display: flex; align-items: center; gap: 10px;
    box-shadow: 0 2px 8px rgba(220,38,38,0.08);
}
.wallet-hero {
    background: linear-gradient(135deg, #C53D20 0%, #EE4D2D 45%, #FF6B35 100%);
    border-radius: 16px; padding: 32px 36px; color: #fff;
    position: relative; overflow: hidden;
    box-shadow: 0 12px 40px rgba(238,77,45,0.35), 0 4px 12px rgba(0,0,0,0.08);
}
.wallet-hero::before {
    content: ''; position: absolute; top: -60px; right: -40px;
    width: 200px; height: 200px; background: rgba(255,255,255,0.1);
    border-radius: 50%;
}
.wallet-hero::after {
    content: ''; position: absolute; bottom: -40px; left: 20%;
    width: 120px; height: 120px; background: rgba(255,255,255,0.06);
    border-radius: 50%;
}
.wallet-hero-inner { position: relative; z-index: 1; }
.wallet-hero-label { font-size: 0.8rem; opacity: 0.92; margin-bottom: 8px; display: flex; align-items: center; gap: 8px; }
.wallet-hero-amount { font-size: 2.4rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 6px; }
.wallet-hero-sub { font-size: 0.82rem; opacity: 0.88; }
.amount-btn {
    padding: 12px 22px; border: 2px solid #e8e8e8; border-radius: 12px;
    background: #fff; font-size: 0.9rem; font-weight: 700; color: #333;
    cursor: pointer; transition: all .2s; font-family: inherit;
    box-shadow: 0 2px 6px rgba(0,0,0,0.04);
}
.amount-btn:hover, .amount-btn.active {
    border-color: #EE4D2D; background: #FFF4ED; color: #EE4D2D;
    box-shadow: 0 4px 12px rgba(238,77,45,0.15); transform: translateY(-1px);
}
.method-card {
    display: flex; align-items: center; gap: 10px; padding: 14px 18px;
    border: 2px solid #e8e8e8; border-radius: 12px; cursor: pointer;
    font-size: 0.85rem; font-weight: 600; transition: all .2s;
    box-shadow: 0 2px 6px rgba(0,0,0,0.03);
}
.method-card:has(input:checked) {
    border-color: #EE4D2D; background: #FFF9F7;
    box-shadow: 0 4px 14px rgba(238,77,45,0.12);
}
.btn-primary {
    padding: 14px 36px; background: linear-gradient(180deg, #FF6B35, #EE4D2D);
    color: #fff; font-weight: 700; font-size: 0.92rem; border: none;
    border-radius: 12px; cursor: pointer; font-family: inherit;
    box-shadow: 0 6px 20px rgba(238,77,45,0.35);
    transition: transform .15s, box-shadow .15s;
}
.btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(238,77,45,0.4); }
.tx-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 18px 0; border-bottom: 1px solid #f0f0f0; gap: 16px;
}
.tx-row:last-child { border-bottom: none; }
.tx-icon {
    width: 46px; height: 46px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}
.tx-icon.credit { background: linear-gradient(135deg, #d1fae5, #a7f3d0); }
.tx-icon.debit  { background: linear-gradient(135deg, #fee2e2, #fecaca); }
.status-tabs { display: flex; gap: 4px; flex-wrap: wrap; padding: 4px; background: #f5f5f5; border-radius: 12px; margin-bottom: 24px; }
.status-tab {
    padding: 10px 18px; border-radius: 10px; font-size: 0.82rem; font-weight: 600;
    color: #666; text-decoration: none; transition: all .2s;
}
.status-tab:hover { color: #EE4D2D; background: rgba(238,77,45,0.06); }
.status-tab.active {
    background: #fff; color: #EE4D2D;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}
.order-card {
    background: #fff; border-radius: 16px; border: 1px solid #e8e8e8;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06); margin-bottom: 16px; overflow: hidden;
    transition: box-shadow .2s, transform .2s;
}
.order-card:hover { box-shadow: 0 8px 32px rgba(0,0,0,0.1); transform: translateY(-2px); }
.order-card-head {
    display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;
    padding: 16px 24px; background: linear-gradient(180deg, #fafafa, #f5f5f5);
    border-bottom: 1px solid #eee;
}
.badge-status {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 12px; border-radius: 20px; font-size: 0.72rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.04em;
}
.badge-pending    { background: #fef3c7; color: #b45309; }
.badge-processing { background: #dbeafe; color: #1d4ed8; }
.badge-completed  { background: #d1fae5; color: #047857; }
.badge-cancelled  { background: #fee2e2; color: #b91c1c; }
.empty-state {
    text-align: center; padding: 56px 32px;
}
.empty-state .empty-icon {
    width: 88px; height: 88px; margin: 0 auto 20px;
    background: linear-gradient(135deg, #FFF4ED, #FFE6D5);
    border-radius: 50%; display: flex; align-items: center; justify-content: center;
    box-shadow: 0 8px 24px rgba(238,77,45,0.12);
}
</style>
