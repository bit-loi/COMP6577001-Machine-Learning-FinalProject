<?php
$currentPage = $currentPage ?? '';
$navItems = [
    'wallet'    => ['label' => 'Wallet & Topup', 'icon' => 'wallet',    'url' => APPURL . 'account/wallet.php',    'desc' => 'Balance & top-up'],
    'purchases' => ['label' => 'My Purchases',   'icon' => 'package',   'url' => APPURL . 'account/purchases.php', 'desc' => 'Order history'],
];
?>
<aside class="account-sidebar">
    <div class="account-card" style="overflow:hidden;">
        <div style="padding:24px 22px;background:linear-gradient(180deg,#FFF9F7 0%,#fff 100%);border-bottom:1px solid #f0f0f0;">
            <div style="display:flex;align-items:center;gap:14px;">
                <div style="width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,#FFE6D5,#FECBA6);display:flex;align-items:center;justify-content:center;color:#EE4D2D;box-shadow:0 4px 12px rgba(238,77,45,0.2);flex-shrink:0;">
                    <i data-lucide="user" style="width:22px;height:22px;"></i>
                </div>
                <div style="min-width:0;">
                    <div style="font-size:0.68rem;color:#999;text-transform:uppercase;letter-spacing:0.06em;font-weight:700;margin-bottom:2px;">My Account</div>
                    <div style="font-size:0.95rem;font-weight:800;color:#111;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo htmlspecialchars($accountName ?? 'User'); ?></div>
                </div>
            </div>
        </div>

        <nav style="padding:10px 8px;">
            <?php foreach ($navItems as $key => $item):
                $active = ($currentPage === $key);
            ?>
            <a href="<?php echo $item['url']; ?>"
               style="display:flex;align-items:center;gap:14px;padding:14px 16px;margin-bottom:4px;border-radius:12px;text-decoration:none;transition:all .2s;
                      background:<?php echo $active ? 'linear-gradient(90deg,#FFF4ED,#FFF9F7)' : 'transparent'; ?>;
                      border:1px solid <?php echo $active ? '#FECBA6' : 'transparent'; ?>;
                      box-shadow:<?php echo $active ? '0 4px 14px rgba(238,77,45,0.12)' : 'none'; ?>;"
               onmouseover="if(!<?php echo $active ? 'true' : 'false'; ?>){this.style.background='#fafafa';this.style.borderColor='#eee';this.style.boxShadow='0 2px 8px rgba(0,0,0,0.05)'}"
               onmouseout="if(!<?php echo $active ? 'true' : 'false'; ?>){this.style.background='';this.style.borderColor='transparent';this.style.boxShadow=''}">
                <span style="width:40px;height:40px;border-radius:11px;display:flex;align-items:center;justify-content:center;flex-shrink:0;
                             background:<?php echo $active ? '#EE4D2D' : '#f5f5f5'; ?>;
                             color:<?php echo $active ? '#fff' : '#888'; ?>;
                             box-shadow:<?php echo $active ? '0 4px 10px rgba(238,77,45,0.3)' : 'none'; ?>;">
                    <i data-lucide="<?php echo $item['icon']; ?>" style="width:18px;height:18px;"></i>
                </span>
                <span style="flex:1;min-width:0;">
                    <span style="display:block;font-size:0.88rem;font-weight:<?php echo $active ? '700' : '600'; ?>;color:<?php echo $active ? '#EE4D2D' : '#222'; ?>;"><?php echo $item['label']; ?></span>
                    <span style="display:block;font-size:0.72rem;color:#999;margin-top:1px;"><?php echo $item['desc']; ?></span>
                </span>
                <i data-lucide="chevron-right" style="width:16px;height:16px;color:<?php echo $active ? '#EE4D2D' : '#ccc'; ?>;flex-shrink:0;"></i>
            </a>
            <?php endforeach; ?>
        </nav>

        <div style="padding:12px 16px 16px;border-top:1px solid #f5f5f5;">
            <a href="<?php echo APPURL; ?>" style="display:flex;align-items:center;justify-content:center;gap:8px;padding:10px;border-radius:10px;font-size:0.82rem;font-weight:600;color:#666;text-decoration:none;background:#f8f8f8;transition:all .15s;"
               onmouseover="this.style.background='#FFF4ED';this.style.color='#EE4D2D'" onmouseout="this.style.background='#f8f8f8';this.style.color='#666'">
                <i data-lucide="arrow-left" style="width:14px;height:14px;"></i> Back to Shop
            </a>
        </div>
    </div>
</aside>
