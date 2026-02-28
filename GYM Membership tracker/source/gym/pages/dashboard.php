<?php

require_once '../includes/db_config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Misafir';
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'user';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ESOĞÜ Spor Salonu</title>

<link href="../assets/vendor/bootstrap.min.css" rel="stylesheet">
    
    <link href="../assets/vendor/fontawesome.min.css" rel="stylesheet">
    
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="dashboard-page">
    <?php include 'sidebar.php'; ?>

<div class="main-content">
        
        <div class="top-navbar">
            <div class="page-title">
                <h3>Dashboard</h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item active">Ana Sayfa</li>
                    </ol>
                </nav>
            </div>
            
            <div class="user-info">
                <a href="../index.php" class="btn btn-danger btn-sm me-2" style="text-decoration: none;">
                    <i class="fas fa-sign-out-alt me-1"></i>Çıkış Yap
                </a>
                <div>
                    <strong><?php echo htmlspecialchars($username); ?></strong>
                    <br>
                    <small class="text-muted"><?php echo ucfirst($role); ?></small>
                </div>
                <div class="user-avatar">
                    <?php echo strtoupper(substr($username, 0, 1)); ?>
                </div>
            </div>
        </div>

<div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#uyeDetayModal" title="Detayları görmek için tıklayın">
                    <div class="stat-card-icon primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <h6>Toplam Üye <i class="fas fa-info-circle text-muted" style="font-size: 0.8rem;"></i></h6>
                    <h3 id="totalMembers">-</h3>
                    <small><i class="fas fa-arrow-up"></i> Bu ay</small>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-card-icon success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h6>Aktif Üyeler</h6>
                    <h3 id="activeMembers">-</h3>
                    <small><i class="fas fa-info-circle"></i> Geçerli üyelik</small>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-card-icon warning">
                        <i class="fas fa-door-open"></i>
                    </div>
                    <h6>Bugünkü Girişler</h6>
                    <h3 id="todayEntries">-</h3>
                    <small><i class="fas fa-calendar-day"></i> Bugün</small>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-card-icon info">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h6>Bu Saat</h6>
                    <h3 id="currentHour">-</h3>
                    <small id="currentTime">-</small>
                </div>
            </div>
        </div>

<div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-line"></i> Son Giriş Logları
                        </h5>
                    </div>
                    <div class="card-body">
                        <table class="table" id="recentLogsTable">
                            <thead>
                                <tr>
                                    <th>Tarih/Saat</th>
                                    <th>Üye</th>
                                    <th>Kart UID</th>
                                    <th>Durum</th>
                                    <th>Kalan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5" class="text-center">
                                        <div class="spinner-border text-primary" role="status"></div>
                                        <p class="mt-2">Loglar yükleniyor...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-bell"></i> Sistem Durumu
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['dev_mode']) && $_SESSION['dev_mode']): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Geliştirme Modu</strong>
                            <p class="mb-0 mt-2">Veritabanı tabloları henüz oluşturulmadı. Sistemi tam olarak kullanmak için SQL dosyalarınızı çalıştırın.</p>
                        </div>
                        <?php endif; ?>
                        
                        <div class="list-group">
                            <div class="list-group-item">
                                <i class="fas fa-check-circle text-success"></i>
                                Veritabanı Bağlantısı
                            </div>
                            <div class="list-group-item">
                                <i class="fas fa-check-circle text-success"></i>
                                Apache Sunucusu
                            </div>
                            <div class="list-group-item">
                                <i class="fas fa-check-circle text-success"></i>
                                PHP <?php echo phpversion(); ?>
                            </div>
                            <div class="list-group-item">
                                <i class="fas fa-network-wired text-info"></i>
                                Yerel Ağ (LAN)
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <a href="../test_kurulum.php" class="btn btn-outline-primary btn-sm w-100" target="_blank">
                                <i class="fas fa-vial"></i> Kurulum Testi
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<div class="modal fade" id="uyeDetayModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-users"></i> Üye İstatistikleri Detayları</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-user-check"></i> Aktif/Pasif Dağılımı</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span><i class="fas fa-check-circle text-success"></i> Aktif Üyeler</span>
                                        <strong class="text-success" id="detayAktif">-</strong>
                                    </div>
                                    <div class="progress mb-3" style="height: 25px;">
                                        <div class="progress-bar bg-success" id="aktifBar" style="width: 0%">0%</div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span><i class="fas fa-times-circle text-danger"></i> Pasif Üyeler</span>
                                        <strong class="text-danger" id="detayPasif">-</strong>
                                    </div>
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar bg-danger" id="pasifBar" style="width: 0%">0%</div>
                                    </div>
                                </div>
                            </div>
                        </div>

<div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-venus-mars"></i> Cinsiyet Dağılımı</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span><i class="fas fa-mars text-primary"></i> Erkek</span>
                                        <strong class="text-primary" id="detayErkek">-</strong>
                                    </div>
                                    <div class="progress mb-3" style="height: 25px;">
                                        <div class="progress-bar bg-primary" id="erkekBar" style="width: 0%">0%</div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span><i class="fas fa-venus text-danger"></i> Kadın</span>
                                        <strong class="text-danger" id="detayKadin">-</strong>
                                    </div>
                                    <div class="progress mb-3" style="height: 25px;">
                                        <div class="progress-bar bg-danger" id="kadinBar" style="width: 0%">0%</div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span><i class="fas fa-question-circle text-muted"></i> Belirtilmemiş</span>
                                        <strong class="text-muted" id="detayDiger">-</strong>
                                    </div>
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar bg-secondary" id="digerBar" style="width: 0%">0%</div>
                                    </div>
                                </div>
                            </div>
                        </div>

<div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-id-card"></i> Üyelik Türü Dağılımı</h6>
                                </div>
                                <div class="card-body">
                                    <div id="uyelikTuruListesi">
                                        <div class="text-center">
                                            <div class="spinner-border text-primary"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

<div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-user-tag"></i> Üye Grubu Dağılımı</h6>
                                </div>
                                <div class="card-body">
                                    <div id="uyeGrubuListesi">
                                        <div class="text-center">
                                            <div class="spinner-border text-primary"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                </div>
            </div>
        </div>
    </div>

<script src="../assets/vendor/jquery.min.js"></script>
    
    <script src="../assets/vendor/bootstrap.bundle.min.js"></script>
    
    <script src="../assets/js/main.js"></script>
    
    <script>
    function updateClock() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        
        document.getElementById('currentHour').textContent = hours + ':' + minutes;
        document.getElementById('currentTime').textContent = 'Saniye: ' + seconds;
    }
    function loadDashboardStats() {
        $.ajax({
            url: '../api/dashboard_stats.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const stats = response.stats;
                    $('#totalMembers').text(stats.toplam_uye || 0);
                    $('#activeMembers').text(stats.aktif_uyelik || 0);
                    $('#todayEntries').text(stats.bugun_giris || 0);
                    if (response.son_girisler && response.son_girisler.length > 0) {
                        const tbody = $('#recentLogsTable tbody');
                        tbody.empty();
                        
                        response.son_girisler.forEach(function(log) {
                            const sonucStr = (log.sonuc || '').toUpperCase();
                            const basarili = sonucStr.includes('BASARILI') || sonucStr.includes('GİRİŞ') || sonucStr === '1';
                            
                            const row = `
                                <tr>
                                    <td>${log.tarih_saat}</td>
                                    <td>${log.ad_soyad || 'Bilinmiyor'}</td>
                                    <td>${log.kart_uid || '-'}</td>
                                    <td>
                                        <span class="badge ${basarili ? 'bg-success' : 'bg-danger'}">
                                            ${basarili ? 'Başarılı' : 'Reddedildi'}
                                        </span>
                                    </td>
                                    <td>${log.kalan_giris !== null ? log.kalan_giris : '-'}</td>
                                </tr>
                            `;
                            tbody.append(row);
                        });
                    }
                } else {
                    console.error('Dashboard istatistikleri yüklenemedi:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Hatası:', error);
                console.log('Response:', xhr.responseText);
                $('#totalMembers').text('0');
                $('#activeMembers').text('0');
                $('#todayEntries').text('0');
            }
        });
    }
    function loadUyeDetaylari() {
        $.ajax({
            url: '../api/uye_detay_stats.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const d = response.data;
                    $('#detayAktif').text(d.aktif_uye || 0);
                    $('#detayPasif').text(d.pasif_uye || 0);
                    const toplam = (d.aktif_uye || 0) + (d.pasif_uye || 0);
                    if (toplam > 0) {
                        const aktifYuzde = Math.round((d.aktif_uye / toplam) * 100);
                        const pasifYuzde = 100 - aktifYuzde;
                        $('#aktifBar').css('width', aktifYuzde + '%').text(aktifYuzde + '%');
                        $('#pasifBar').css('width', pasifYuzde + '%').text(pasifYuzde + '%');
                    }
                    $('#detayErkek').text(d.erkek || 0);
                    $('#detayKadin').text(d.kadin || 0);
                    $('#detayDiger').text(d.diger || 0);
                    const toplamCinsiyet = (d.erkek || 0) + (d.kadin || 0) + (d.diger || 0);
                    if (toplamCinsiyet > 0) {
                        const erkekYuzde = Math.round((d.erkek / toplamCinsiyet) * 100);
                        const kadinYuzde = Math.round((d.kadin / toplamCinsiyet) * 100);
                        const digerYuzde = 100 - erkekYuzde - kadinYuzde;
                        $('#erkekBar').css('width', erkekYuzde + '%').text(erkekYuzde + '%');
                        $('#kadinBar').css('width', kadinYuzde + '%').text(kadinYuzde + '%');
                        $('#digerBar').css('width', digerYuzde + '%').text(digerYuzde + '%');
                    }
                    const container = $('#uyelikTuruListesi');
                    container.empty();
                    if (d.uyelik_turleri && d.uyelik_turleri.length > 0) {
                        d.uyelik_turleri.forEach(function(tur) {
                            const yuzde = toplam > 0 ? Math.round((tur.sayi / toplam) * 100) : 0;
                            const html = `
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span><i class="fas fa-id-badge text-info"></i> ${tur.tur_adi}</span>
                                        <strong>${tur.sayi}</strong>
                                    </div>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-info" style="width: ${yuzde}%">${yuzde}%</div>
                                    </div>
                                </div>
                            `;
                            container.append(html);
                        });
                    } else {
                        container.html('<p class="text-muted text-center">Henüz üyelik türü verisi yok</p>');
                    }
                    const grubuContainer = $('#uyeGrubuListesi');
                    grubuContainer.empty();
                    if (d.uye_gruplari && d.uye_gruplari.length > 0) {
                        const colors = ['primary', 'success', 'warning', 'danger', 'info', 'secondary', 'dark'];
                        d.uye_gruplari.forEach(function(grup, index) {
                            const yuzde = toplam > 0 ? Math.round((grup.sayi / toplam) * 100) : 0;
                            const color = colors[index % colors.length];
                            const html = `
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span><i class="fas fa-users text-${color}"></i> ${grup.uye_grubu}</span>
                                        <strong>${grup.sayi}</strong>
                                    </div>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-${color}" style="width: ${yuzde}%">${yuzde}%</div>
                                    </div>
                                </div>
                            `;
                            grubuContainer.append(html);
                        });
                    } else {
                        grubuContainer.html('<p class="text-muted text-center">Henüz üye grubu verisi yok</p>');
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Üye detayları yüklenemedi:', error);
            }
        });
    }
    $('#uyeDetayModal').on('show.bs.modal', function() {
        loadUyeDetaylari();
    });
    $(document).ready(function() {
        loadDashboardStats();
        updateClock();
        setInterval(updateClock, 1000);
        setInterval(loadDashboardStats, 30000);
    });
    
    console.log('🏋️ Dashboard yüklendi');
    </script>
</body>
</html>
