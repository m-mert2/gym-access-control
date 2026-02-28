<?php
require_once '../includes/db_config.php';
$username = 'admin';
$role = 'admin';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Logları - ESOĞÜ Spor Salonu</title>
    <link href="../assets/vendor/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/vendor/fontawesome.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="dashboard-page">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="top-navbar">
            <div class="page-title">
                <h3>Giriş Logları</h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Ana Sayfa</a></li>
                        <li class="breadcrumb-item active">Giriş Logları</li>
                    </ol>
                </nav>
            </div>
            <div class="user-info">
                <div><strong><?php echo htmlspecialchars($username); ?></strong><br><small class="text-muted"><?php echo ucfirst($role); ?></small></div>
                <div class="user-avatar"><?php echo strtoupper(substr($username, 0, 1)); ?></div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-clipboard-list"></i> Tüm Giriş Kayıtları</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Başlangıç Tarihi</label>
                        <input type="date" class="form-control" id="baslangicTarih">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Bitiş Tarihi</label>
                        <input type="date" class="form-control" id="bitisTarih">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Durum</label>
                        <select class="form-select" id="durumFiltre">
                            <option value="">Tüm Durumlar</option>
                            <option value="1">Başarılı</option>
                            <option value="0">Reddedildi</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <button class="btn btn-primary w-100" onclick="loglariYukle()">
                            <i class="fas fa-search"></i> Filtrele
                        </button>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Tarih/Saat</th>
                                <th>Üye</th>
                                <th>Kart UID</th>
                                <th>Turnike</th>
                                <th>Durum</th>
                            </tr>
                        </thead>
                        <tbody id="logTbody">
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
    </div>
    
    <script src="../assets/vendor/jquery.min.js"></script>
    <script src="../assets/vendor/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    
    <script>
    $(document).ready(function() {
        const bugun = new Date().toISOString().split('T')[0];
        $('#bitisTarih').val(bugun);
        
        const yediGunOnce = new Date();
        yediGunOnce.setDate(yediGunOnce.getDate() - 7);
        $('#baslangicTarih').val(yediGunOnce.toISOString().split('T')[0]);
        loglariYukle();
    });
    
    function loglariYukle() {
        const baslangic = $('#baslangicTarih').val();
        const bitis = $('#bitisTarih').val();
        const durum = $('#durumFiltre').val();
        
        $('#logTbody').html('<tr><td colspan="5" class="text-center"><div class="spinner-border"></div></td></tr>');
        
        let url = '../api/giris_loglari.php?baslangic=' + baslangic + '&bitis=' + bitis;
        if (durum !== '') {
            url += '&sonuc=' + durum;
        }
        
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const tbody = $('#logTbody');
                    tbody.empty();
                    
                    if (response.data.length === 0) {
                        tbody.append('<tr><td colspan="5" class="text-center text-muted">Bu tarih aralığında kayıt yok</td></tr>');
                        return;
                    }
                    
                    response.data.forEach(function(log) {
                        const sonucStr = (log.sonuc || '').toUpperCase();
                        const basarili = sonucStr.includes('BASARILI') || sonucStr.includes('GİRİŞ') || sonucStr === '1';
                        
                        const durumClass = basarili ? 'success' : 'danger';
                        const durumText = basarili ? 'Başarılı' : 'Reddedildi';
                        let tarihSaat = log.tarih_saat;
                        if (tarihSaat && typeof tarihSaat === 'object' && tarihSaat.date) {
                            tarihSaat = tarihSaat.date.substring(0, 19);
                        }
                        
                        const row = `
                            <tr>
                                <td>${tarihSaat || '-'}</td>
                                <td>${log.ad_soyad || 'Bilinmiyor'}</td>
                                <td><code>${log.kart_uid || '-'}</code></td>
                                <td>Turnike ${log.turnike_id || '1'}</td>
                                <td><span class="badge bg-${durumClass}">${durumText}</span></td>
                            </tr>
                        `;
                        tbody.append(row);
                    });
                } else {
                    $('#logTbody').html('<tr><td colspan="5" class="text-center text-danger">Hata: ' + response.message + '</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Log yükleme hatası:', error);
                $('#logTbody').html('<tr><td colspan="5" class="text-center text-danger">Veriler yüklenemedi!</td></tr>');
            }
        });
    }
    </script>
</body>
</html>
