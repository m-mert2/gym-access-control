<?php
require_once '../includes/db_config.php';
startSecureSession();
if (!isLoggedIn()) {
    header('Location: ../index.php');
    exit;
}
requirePermission('yonetim.panel', 'dashboard.php');

$username = $_SESSION['username'] ?? 'Admin';
$role = $_SESSION['role'] ?? 'admin';
?>
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetim Paneli - ESOĞÜ Spor Salonu</title>
    <link href="../assets/vendor/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/vendor/fontawesome.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="dashboard-page">
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="top-navbar">
            <div class="page-title">
                <h3>Yönetim Paneli</h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Ana Sayfa</a></li>
                        <li class="breadcrumb-item active">Yönetim</li>
                    </ol>
                </nav>
            </div>
            <div class="user-info">
                <div><strong><?php echo htmlspecialchars($username); ?></strong><br><small class="text-muted">Admin</small></div>
                <div class="user-avatar"><?php echo strtoupper(substr($username, 0, 1)); ?></div>
            </div>
        </div>
        
        <div class="row">
            
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5><i class="fas fa-cog"></i> Sistem Ayarları</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="fas fa-users"></i> Kullanıcı Yönetimi
                                <span class="badge bg-secondary float-end">4 kullanıcı</span>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="fas fa-door-closed"></i> Turnike Yönetimi
                                <span class="badge bg-secondary float-end">Aktif</span>
                            </a>
                            <a href="uyelik_turleri_yonetim.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-tags"></i> Üyelik Türleri Yönetimi
                                <span class="badge bg-success float-end">Yönet</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

<div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5><i class="fas fa-heartbeat"></i> Sistem Durumu</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Veritabanı:</strong>
                            <span class="badge bg-success float-end">Bağlı</span>
                        </div>
                        <div class="mb-3">
                            <strong>Web Sunucu:</strong>
                            <span class="badge bg-success float-end">Çalışıyor</span>
                        </div>
                        <div class="mb-3">
                            <strong>PHP Versiyonu:</strong>
                            <span class="badge bg-info float-end"><?php echo PHP_VERSION; ?></span>
                        </div>
                        <div class="mb-3">
                            <strong>SQL Server:</strong>
                            <span class="badge bg-success float-end">OGUGYMDB</span>
                        </div>
                        <hr>
                        <a href="../test_kurulum.php" class="btn btn-sm btn-outline-primary w-100" target="_blank">
                            <i class="fas fa-vial"></i> Sistem Testini Çalıştır
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5><i class="fas fa-chart-bar"></i> Raporlar</h5>
                    </div>
                    <div class="card-body text-center py-4">
                        <i class="fas fa-file-chart-line fa-3x text-warning mb-3"></i>
                        <p class="text-muted mb-3">Detaylı raporlar ve analizler</p>
                        <a href="raporlar.php" class="btn btn-warning btn-lg">
                            <i class="fas fa-chart-bar me-2"></i>Raporları Görüntüle
                        </a>
                    </div>
                </div>
            </div>

<div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h5><i class="fas fa-trash-alt"></i> Veri Temizliği</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Dikkat!</strong> 1 yıldan eski kayıtlar otomatik silinir.
                        </div>
                        <p class="text-muted">
                            <i class="fas fa-info-circle"></i> Sistem, kayıt tarihinden 1 yıl geçmiş kişileri ve ilgili tüm verilerini (üyelikler, kartlar, loglar) otomatik olarak siler.
                        </p>
                        <hr>
                        <div class="d-grid gap-2">
                            <button class="btn btn-danger" id="manuelTemizlikBtn">
                                <i class="fas fa-broom me-2"></i>Manuel Temizlik Çalıştır
                            </button>
                            <small class="text-muted text-center">
                                <i class="fas fa-clock"></i> Son temizlik: <span id="sonTemizlik">-</span>
                            </small>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    
    <script src="../assets/vendor/jquery.min.js"></script>
    <script src="../assets/vendor/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    
    <script>
    $(document).ready(function() {
        $('#manuelTemizlikBtn').on('click', function() {
            if (!confirm('⚠️ DİKKAT!\n\n1 yıldan eski TÜM kayıtlar silinecek:\n- Kişisel bilgiler\n- Üyelikler\n- Kartlar\n- Giriş logları\n\nDevam etmek istediğinize emin misiniz?')) {
                return;
            }
            
            const btn = $(this);
            const originalText = btn.html();
            
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Temizleniyor...');
            
            $.ajax({
                url: '../api/manuel_temizlik.php',
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('✅ ' + response.message + '\n\n' + 
                              'Silinen kayıtlar:\n' +
                              '• Kişi: ' + (response.silinen_kisi || 0) + '\n' +
                              '• Üyelik: ' + (response.silinen_uyelik || 0) + '\n' +
                              '• Kart: ' + (response.silinen_kart || 0) + '\n' +
                              '• Log: ' + (response.silinen_log || 0));
                        $('#sonTemizlik').text(new Date().toLocaleString('tr-TR'));
                    } else {
                        alert('❌ Hata: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Hatası:', error);
                    alert('❌ Sunucu hatası: ' + error);
                },
                complete: function() {
                    btn.prop('disabled', false).html(originalText);
                }
            });
        });
    });
    </script>
</body>
</html>
