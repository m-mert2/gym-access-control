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
    <title>Üye Listesi - ESOĞÜ Spor Salonu</title>
    <link href="../assets/vendor/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/vendor/fontawesome.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="dashboard-page">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="top-navbar">
            <div class="page-title">
                <h3>Üye Listesi</h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Ana Sayfa</a></li>
                        <li class="breadcrumb-item active">Üye Listesi</li>
                    </ol>
                </nav>
            </div>
            <div class="user-info">
                <div><strong><?php echo htmlspecialchars($username); ?></strong><br><small class="text-muted"><?php echo ucfirst($role); ?></small></div>
                <div class="user-avatar"><?php echo strtoupper(substr($username, 0, 1)); ?></div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-users"></i> Tüm Üyeler</h5>
                <a href="uye_kayit.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Yeni Üye
                </a>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <input type="text" id="searchInput" class="form-control" placeholder="Ara: İsim, TC, Kart UID...">
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped" id="uyelerTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Ad Soyad</th>
                                <th>TC No</th>
                                <th>Üyelik Türü</th>
                                <th>Kart UID</th>
                                <th>Başlangıç</th>
                                <th>Bitiş</th>
                                <th>Kalan Giriş</th>
                                <th>Durum</th>
                            </tr>
                        </thead>
                        <tbody id="uyelerTableBody">
                            <tr>
                                <td colspan="9" class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Yükleniyor...</span>
                                    </div>
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
        loadUyeler();
        $('#searchInput').on('keyup', function() {
            const value = $(this).val().toLowerCase();
            $('#uyelerTableBody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });
    });
    
    function loadUyeler() {
        $.ajax({
            url: '../api/uye_listesi.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const tbody = $('#uyelerTableBody');
                    tbody.empty();
                    
                    if (response.data.length === 0) {
                        tbody.append('<tr><td colspan="9" class="text-center text-muted">Henüz üye yok</td></tr>');
                        return;
                    }
                    
                    response.data.forEach(function(uye) {
                        const durumClass = uye.uyelik_durumu === 'Aktif' ? 'success' : 'danger';
                        const row = `
                            <tr>
                                <td>${uye.kisisel_id}</td>
                                <td>${uye.isim} ${uye.soyisim}</td>
                                <td>${uye.tc_no || '-'}</td>
                                <td>${uye.tur_adi || '-'}</td>
                                <td>${uye.kart_uid || '-'}</td>
                                <td>${uye.baslangic_tarihi || '-'}</td>
                                <td>${uye.bitis_tarihi || '-'}</td>
                                <td><strong>${uye.kalan_giris !== null ? uye.kalan_giris : '-'}</strong></td>
                                <td><span class="badge bg-${durumClass}">${uye.uyelik_durumu}</span></td>
                            </tr>
                        `;
                        tbody.append(row);
                    });
                } else {
                    $('#uyelerTableBody').html('<tr><td colspan="9" class="text-center text-danger">Hata: ' + response.message + '</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Üye listesi yüklenemedi:', error);
                $('#uyelerTableBody').html('<tr><td colspan="9" class="text-center text-danger">Veriler yüklenemedi. Console\'u kontrol edin.</td></tr>');
            }
        });
    }
    </script>
</body>
</html>
