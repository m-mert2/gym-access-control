<?php
require_once '../includes/db_config.php';
startSecureSession();
if (!isLoggedIn()) {
    header('Location: ../index.php');
    exit;
}
$username = $_SESSION['username'];
$role = $_SESSION['role'] ?? 'personel';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Üye Kayıt - ESOĞÜ Spor Salonu</title>
    <link href="../assets/vendor/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/vendor/fontawesome.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="dashboard-page">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="top-navbar">
            <div class="page-title">
                <h3>Üye Kayıt</h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Ana Sayfa</a></li>
                        <li class="breadcrumb-item active">Üye Kayıt</li>
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
                <h5><i class="fas fa-user-plus"></i> Yeni Üye Kaydı</h5>
            </div>
            <div class="card-body">
                <form id="uyeKayitForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Üyelik Türü *</label>
                            <select class="form-select" id="turId" name="tur_id" required>
                                <option value="">Yükleniyor...</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Başlangıç Tarihi *</label>
                            <input type="date" class="form-control" id="baslangicTarihi" name="baslangic_tarihi" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ad *</label>
                            <input type="text" class="form-control" id="isim" name="isim" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Soyad *</label>
                            <input type="text" class="form-control" id="soyisim" name="soyisim" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">TC Kimlik No</label>
                            <input type="text" class="form-control" id="tcNo" name="tc_no" maxlength="11">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cinsiyet</label>
                            <select class="form-select" id="cinsiyet" name="cinsiyet">
                                <option value="">Seçiniz</option>
                                <option value="E">Erkek</option>
                                <option value="K">Kadın</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">E-posta</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Telefon</label>
                            <input type="tel" class="form-control" id="telefon" name="telefon">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bölüm</label>
                            <input type="text" class="form-control" id="bolum" name="bolum">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Öğrenci No</label>
                            <input type="text" class="form-control" id="ogrenciNo" name="ogrenci_no">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Adres</label>
                        <textarea class="form-control" id="adres" name="adres" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Kart UID *</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="kartUid" name="kart_uid" placeholder="Kartı okutunuz..." required>
                            <button class="btn btn-info" type="button" onclick="simulateCardRead()">
                                <i class="fas fa-id-card"></i> Simüle Et
                            </button>
                        </div>
                        <small class="text-muted">Simüle Et butonu rastgele kart UID oluşturur</small>
                    </div>
                    
                    <hr>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Üyeyi Kaydet
                    </button>
                    <button type="reset" class="btn btn-secondary">
                        <i class="fas fa-undo"></i> Temizle
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <script src="../assets/vendor/jquery.min.js"></script>
    <script src="../assets/vendor/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    
    <script>
    $(document).ready(function() {
        $('#baslangicTarihi').val(new Date().toISOString().split('T')[0]);
        loadUyelikTurleri();
        $('#uyeKayitForm').on('submit', function(e) {
            e.preventDefault();
            kaydetUye();
        });
    });
    
    function loadUyelikTurleri() {
        $('#turId').html(`
            <option value="">Seçiniz</option>
            <option value="1">Günlük (1 giriş - 50 TL)</option>
            <option value="2">Aylık (22 giriş - 300 TL)</option>
            <option value="3">3 Aylık (66 giriş - 750 TL)</option>
            <option value="4">6 Aylık (132 giriş - 1400 TL)</option>
            <option value="5">Yıllık (264 giriş - 2500 TL)</option>
            <option value="6">Sınırsız (0 TL)</option>
            <option value="7">Görevli (0 TL)</option>
        `);
    }
    
    function simulateCardRead() {
        const uid = Math.floor(1000000000 + Math.random() * 9000000000).toString();
        $('#kartUid').val(uid);
    }
    
    function kaydetUye() {
        const formData = {
            tur_id: $('#turId').val(),
            baslangic_tarihi: $('#baslangicTarihi').val(),
            isim: $('#isim').val(),
            soyisim: $('#soyisim').val(),
            tc_no: $('#tcNo').val(),
            cinsiyet: $('#cinsiyet').val(),
            email: $('#email').val(),
            telefon: $('#telefon').val(),
            bolum: $('#bolum').val(),
            ogrenci_no: $('#ogrenciNo').val(),
            adres: $('#adres').val(),
            kart_uid: $('#kartUid').val()
        };
        
        if (!formData.isim || !formData.soyisim || !formData.kart_uid || !formData.tur_id) {
            alert('Lütfen zorunlu alanları doldurun!');
            return;
        }
        
        $.ajax({
            url: '../api/uye_kaydet.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            success: function(response) {
                if (response.success) {
                    alert('Üye başarıyla kaydedildi!\n\n' + 
                          'Üyelik ID: ' + response.data.uyelik_id + '\n' +
                          'Kart ID: ' + response.data.kart_id + '\n' +
                          'Bitiş Tarihi: ' + response.data.bitis_tarihi);
                    $('#uyeKayitForm')[0].reset();
                    $('#baslangicTarihi').val(new Date().toISOString().split('T')[0]);
                } else {
                    alert('Hata: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Hatası:', error);
                console.log('Response:', xhr.responseText);
                alert('Üye kaydedilemedi! Console\'u kontrol edin.');
            }
        });
    }
    </script>
</body>
</html>
