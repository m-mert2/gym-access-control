<?php
require_once __DIR__ . '/../includes/db_config.php';
startSecureSession();
$username = $_SESSION['username'] ?? 'Kullanıcı';
$role = $_SESSION['role'] ?? 'vezne';
$roleDisplay = [
    'admin' => 'Yönetici',
    'personel' => 'Personel',
    'vezne' => 'Vezne'
];
?>
<div class="sidebar">
    <div class="sidebar-header">
        <i class="fas fa-dumbbell fa-3x text-white"></i>
        <h4>ESOGÜ Spor Salonu</h4>
        <small>v3.0</small>
    </div>
    
    <div class="sidebar-menu">
        <ul>
            <?php if (hasPermission('dashboard.goruntule')): ?>
            <li>
                <a href="dashboard.php" <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (hasPermission('kisi.ekle')): ?>
            <li>
                <a href="kisi_ekle.php" <?php echo basename($_SERVER['PHP_SELF']) == 'kisi_ekle.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-user-plus"></i> Kişi Ekle
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (hasPermission('uyelik.olustur')): ?>
            <li>
                <a href="uyelik_olustur.php" <?php echo basename($_SERVER['PHP_SELF']) == 'uyelik_olustur.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-id-card-alt"></i> Üyelik Oluştur
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (hasPermission('uye.listele')): ?>
            <li>
                <a href="uye_listesi.php" <?php echo basename($_SERVER['PHP_SELF']) == 'uye_listesi.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-users"></i> Üye Listesi
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (hasPermission('turnike.kontrol')): ?>
            <li>
                <a href="turnike.php" <?php echo basename($_SERVER['PHP_SELF']) == 'turnike.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-door-open"></i> Turnike Kontrolü
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (hasPermission('giris.loglari')): ?>
            <li>
                <a href="giris_loglari.php" <?php echo basename($_SERVER['PHP_SELF']) == 'giris_loglari.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-clipboard-list"></i> Giriş Logları
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (hasPermission('rapor.goruntule')): ?>
            <li>
                <a href="raporlar.php" <?php echo basename($_SERVER['PHP_SELF']) == 'raporlar.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-chart-bar"></i> Raporlar
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (hasPermission('yonetim.panel')): ?>
            <li>
                <a href="yonetim.php" <?php echo basename($_SERVER['PHP_SELF']) == 'yonetim.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-cog"></i> Yönetim Paneli
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </div>
    
    <div style="position: absolute; bottom: 20px; width: 100%; padding: 0 20px;">
        <div style="color: rgba(255,255,255,0.8); padding: 10px; border-top: 1px solid rgba(255,255,255,0.2);">
            <div style="font-size: 14px;"><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($username); ?></div>
            <div style="font-size: 12px; opacity: 0.7;"><?php echo $roleDisplay[$role] ?? $role; ?></div>
        </div>
        <a href="../api/logout.php" style="color: rgba(255,255,255,0.8); display: block; padding: 10px; text-align: center; background: rgba(255,255,255,0.1); border-radius: 5px; text-decoration: none;">
            <i class="fas fa-sign-out-alt"></i> Çıkış Yap
        </a>
    </div>
</div>
