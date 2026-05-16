<?php
$currentSellerPage = $currentSellerPage ?? 'dashboard';
$sellerNav = [
    'dashboard' => ['label' => 'Dashboard',     'icon' => 'layout-dashboard', 'url' => APPURL . 'seller/index.php',  'desc' => 'Ringkasan toko'],
    'products'  => ['label' => 'My Products',   'icon' => 'package',          'url' => APPURL . 'seller/index.php#products', 'desc' => 'Kelola listing'],
    'add'       => ['label' => 'Add Product',   'icon' => 'plus-circle',      'url' => APPURL . 'seller/create.php', 'desc' => 'Tambah produk baru'],
    'wallet'    => ['label' => 'Wallet',        'icon' => 'wallet',           'url' => APPURL . 'account/wallet.php', 'desc' => 'Saldo penghasilan'],
];
?>
<aside class="seller-sidebar-wrap">
    <div class="seller-card" style="overflow:hidden;">
        <div style="padding:22px 20px;background:linear-gradient(180deg,#FFF4ED,#fff);border-bottom:1px solid #f0f0f0;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:46px;height:46px;border-radius:12px;background:linear-gradient(135deg,#EE4D2D,#FF6B35);display:flex;align-items:center;justify-content:center;color:#fff;box-shadow:0 4px 14px rgba(238,77,45,0.35);flex-shrink:0;">
                    <i data-lucide="store" style="width:22px;height:22px;"></i>
                </div>
                <div style="min-width:0;">
                    <div style="font-size:0.68rem;color:#999;text-transform:uppercase;letter-spacing:0.06em;font-weight:700;">Seller Centre</div>
                    <div style="font-size:0.92rem;font-weight:800;color:#111;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo htmlspecialchars($sellerName ?? 'My Shop'); ?></div>
                </div>
            </div>
        </div>
        <nav style="padding:8px;">
            <?php foreach ($sellerNav as $key => $item):
                $active = ($currentSellerPage === $key);
            ?>
            <a href="<?php echo $item['url']; ?>"
               style="display:flex;align-items:center;gap:12px;padding:13px 14px;margin-bottom:3px;border-radius:11px;text-decoration:none;transition:all .2s;
                      background:<?php echo $active ? 'linear-gradient(90deg,#FFF4ED,#FFF9F7)' : 'transparent'; ?>;
                      border:1px solid <?php echo $active ? '#FECBA6' : 'transparent'; ?>;
                      box-shadow:<?php echo $active ? '0 3px 12px rgba(238,77,45,0.1)' : 'none'; ?>;"
               onmouseover="if(!<?php echo $active ? 'true' : 'false'; ?>){this.style.background='#fafafa';this.style.borderColor='#eee'}"
               onmouseout="if(!<?php echo $active ? 'true' : 'false'; ?>){this.style.background='';this.style.borderColor='transparent'}">
                <span style="width:36px;height:36px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:<?php echo $active ? '#EE4D2D' : '#f5f5f5'; ?>;color:<?php echo $active ? '#fff' : '#888'; ?>;">
                    <i data-lucide="<?php echo $item['icon']; ?>" style="width:17px;height:17px;"></i>
                </span>
                <span style="flex:1;min-width:0;">
                    <span style="display:block;font-size:0.86rem;font-weight:<?php echo $active ? '700' : '600'; ?>;color:<?php echo $active ? '#EE4D2D' : '#222'; ?>;"><?php echo $item['label']; ?></span>
                    <span style="display:block;font-size:0.7rem;color:#999;"><?php echo $item['desc']; ?></span>
                </span>
                <i data-lucide="chevron-right" style="width:14px;height:14px;color:<?php echo $active ? '#EE4D2D' : '#ccc'; ?>;"></i>
            </a>
            <?php endforeach; ?>
        </nav>
        <div style="padding:10px 14px 14px;border-top:1px solid #f5f5f5;">
            <a href="<?php echo APPURL; ?>" style="display:flex;align-items:center;justify-content:center;gap:6px;padding:9px;border-radius:9px;font-size:0.8rem;font-weight:600;color:#666;text-decoration:none;background:#f8f8f8;transition:all .15s;"
               onmouseover="this.style.background='#FFF4ED';this.style.color='#EE4D2D'" onmouseout="this.style.background='#f8f8f8';this.style.color='#666'">
                <i data-lucide="arrow-left" style="width:13px;height:13px;"></i> Kembali ke Toko
            </a>
        </div>
    </div>
</aside>
