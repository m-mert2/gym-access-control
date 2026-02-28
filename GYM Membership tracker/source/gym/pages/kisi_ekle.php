<?php
require_once '../includes/db_config.php';
startSecureSession();
if (!isLoggedIn()) {
    header('Location: ../index.php');
    exit;
}
requirePermission('kisi.ekle', 'dashboard.php');
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kişi Ekle - ESOGÜ Spor Salonu</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'header.php'; ?>
        
        <div class="container-fluid p-4">
            <div class="row mb-4">
                <div class="col-12">
                    <h2><i class="fas fa-user-plus me-2"></i>Kişi Ekle</h2>
                    <p class="text-muted">Kişisel bilgiler tablosuna yeni kayıt ekleyin</p>
                </div>
            </div>

<div class="row mb-3">
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Bu formda sadece kişisel bilgiler kaydedilir. <strong>TC Kimlik No zorunludur</strong> ve her kişi için benzersiz olmalıdır. Üyelik tanımlamak için <strong>"Üyelik Oluştur"</strong> menüsünü kullanın.
                    </div>
                </div>
            </div>

<div class="row">
                <div class="col-lg-8">
                    
                    <div class="card shadow-sm" id="adim1Card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-id-card me-2"></i>Adım 1: Kişisel Bilgiler</h5>
                        </div>
                        <div class="card-body">
                            <form id="kisiEkleForm">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">İsim <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="isim" name="isim" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Soyisim <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="soyisim" name="soyisim" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">TC Kimlik No <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="tcNo" name="tc_no" maxlength="11" pattern="[0-9]{11}" required>
                                        <div class="form-text">11 haneli sayı (Zorunlu)</div>
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
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Telefon</label>
                                        <input type="text" class="form-control" id="telefon" name="telefon" placeholder="05XX XXX XX XX">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Üye Grubu <span class="text-danger">*</span></label>
                                        <select class="form-select" id="uyeGrubu" name="uye_grubu" required>
                                            <option value="">Seçiniz</option>
                                            <option value="Öğrenci">Öğrenci</option>
                                            <option value="AkademikPersonel">Akademik Personel</option>
                                            <option value="İdari Personel">İdari Personel</option>
                                            <option value="Personel Yakını">Personel Yakını</option>
                                            <option value="Emekli Personel">Emekli Personel</option>
                                            <option value="Kamu">Kamu</option>
                                            <option value="Dış">Dış</option>
                                            <option value="M.D">M.D</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Bölüm</label>
                                        <input type="text" class="form-control" id="bolum" name="bolum" placeholder="Örn: Bilgisayar Mühendisliği">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Öğrenci No</label>
                                        <input type="text" class="form-control" id="ogrenciNo" name="ogrenci_no">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Adres</label>
                                    <textarea class="form-control" id="adres" name="adres" rows="3"></textarea>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-arrow-right me-2"></i>İleri: Kart Tanımla
                                    </button>
                                    <button type="reset" class="btn btn-secondary">
                                        <i class="fas fa-redo me-2"></i>Temizle
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

<div class="card shadow-sm mt-3" id="adim2Card" style="display: none;">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-id-badge me-2"></i>Adım 2: Kart Tanımlama</h5>
                        </div>
                        <div class="card-body text-center py-5">
                            <div id="kartBeklemeEkrani">
                                <i class="fas fa-address-card fa-5x text-muted mb-4"></i>
                                <h4>Kart Okutun</h4>
                                <p class="text-muted mb-4">Demo modunda otomatik kart UID oluşturulacak</p>
                                <button type="button" class="btn btn-success btn-lg" id="kartOkutBtn">
                                    <i class="fas fa-credit-card me-2"></i>Kart Okut (Demo)
                                </button>
                                <div class="mt-3">
                                    <small class="text-muted">Kişi: <strong id="kisiAdiBilgi"></strong></small>
                                </div>
                            </div>

                            <div id="kartBasariliEkrani" style="display: none;">
                                <i class="fas fa-check-circle fa-5x text-success mb-4"></i>
                                <h4 class="text-success">Kart Başarıyla Tanımlandı!</h4>
                                <div class="alert alert-success mt-3">
                                    <strong>Kart UID:</strong> <span id="kartUidBilgi"></span>
                                </div>
                                <p class="mt-3">Şimdi "Üyelik Oluştur" menüsünden üyelik tanımlayabilirsiniz.</p>
                                <button type="button" class="btn btn-primary" onclick="window.location.href='uyelik_olustur.php'">
                                    <i class="fas fa-user-plus me-2"></i>Üyelik Oluştur
                                </button>
                                <button type="button" class="btn btn-secondary ms-2" onclick="window.location.reload()">
                                    <i class="fas fa-plus me-2"></i>Yeni Kişi Ekle
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Bilgilendirme</h5>
                        </div>
                        <div class="card-body">
                            <div id="adim1Bilgi">
                                <h6>📋 Adım 1: Kişisel Bilgiler</h6>
                                <p class="small text-muted">Kişisel bilgileri girin</p>
                                
                                <h6 class="mt-3">Zorunlu Alanlar:</h6>
                                <ul>
                                    <li>İsim</li>
                                    <li>Soyisim</li>
                                    <li><strong>TC Kimlik No (11 haneli)</strong></li>
                                </ul>

                                <h6 class="mt-3">İsteğe Bağlı:</h6>
                                <ul>
                                    <li>Cinsiyet</li>
                                    <li>Email</li>
                                    <li>Telefon</li>
                                    <li>Bölüm</li>
                                    <li>Öğrenci No</li>
                                    <li>Adres</li>
                                </ul>
                            </div>

                            <div id="adim2Bilgi" style="display: none;">
                                <h6>💳 Adım 2: Kart Tanımlama</h6>
                                <p class="small text-muted">Demo modda çalışıyorsunuz</p>
                                
                                <div class="alert alert-info small mb-2">
                                    <i class="fas fa-info-circle me-1"></i>
                                    "Kart Okut" butonuna tıklayın, otomatik olarak benzersiz bir kart UID oluşturulacak
                                </div>

                                <div class="alert alert-warning small mb-0">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    Kart tanımlandıktan sonra "Üyelik Oluştur" menüsünden üyelik ataması yapın
                                </div>
                            </div>

                            <div class="alert alert-success mt-3 mb-0 small">
                                <strong>İşlem Sırası:</strong><br>
                                1️⃣ Kişisel Bilgiler<br>
                                2️⃣ Kart Tanımlama<br>
                                3️⃣ Üyelik Oluşturma
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/jquery-3.7.1.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    
    <script>
    let kayitliKisiselId = null;
    let kayitliKisiAdi = '';

    $(document).ready(function() {
        $('#kisiEkleForm').on('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                isim: $('#isim').val().trim(),
                soyisim: $('#soyisim').val().trim(),
                tc_no: $('#tcNo').val().trim(),
                uye_grubu: $('#uyeGrubu').val(),
                cinsiyet: $('#cinsiyet').val() || null,
                email: $('#email').val().trim() || null,
                telefon: $('#telefon').val().trim() || null,
                bolum: $('#bolum').val().trim() || null,
                ogrenci_no: $('#ogrenciNo').val().trim() || null,
                adres: $('#adres').val().trim() || null
            };
            if (!formData.isim || !formData.soyisim || !formData.tc_no || !formData.uye_grubu) {
                alert('İsim, Soyisim, TC Kimlik No ve Üye Grubu alanları zorunludur!');
                return;
            }
            if (formData.tc_no.length !== 11) {
                alert('TC Kimlik No 11 haneli olmalıdır!');
                return;
            }
            $.ajax({
                url: '../api/kisi_ekle.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(formData),
                beforeSend: function() {
                    $('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Kaydediliyor...');
                },
                success: function(response) {
                    if (response.success) {
                        kayitliKisiselId = response.kisisel_id;
                        kayitliKisiAdi = formData.isim + ' ' + formData.soyisim;
                        $('#adim1Card').slideUp();
                        $('#adim2Card').slideDown();
                        $('#adim1Bilgi').hide();
                        $('#adim2Bilgi').show();
                        $('#kisiAdiBilgi').text(kayitliKisiAdi);
                        $('html, body').animate({ scrollTop: 0 }, 500);
                    } else {
                        alert('Hata: ' + response.message);
                        $('button[type="submit"]').prop('disabled', false).html('<i class="fas fa-arrow-right me-2"></i>İleri: Kart Tanımla');
                    }
                },
                error: function(xhr) {
                    console.error('AJAX Error:', xhr);
                    alert('Sunucu hatası oluştu!');
                    $('button[type="submit"]').prop('disabled', false).html('<i class="fas fa-arrow-right me-2"></i>İleri: Kart Tanımla');
                }
            });
        });
        $('#kartOkutBtn').on('click', function() {
            if (!kayitliKisiselId) {
                alert('Önce kişisel bilgileri kaydedin!');
                return;
            }
            $.ajax({
                url: '../api/kart_tanimla.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    kisisel_id: kayitliKisiselId
                }),
                beforeSend: function() {
                    $('#kartOkutBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Kart oluşturuluyor...');
                },
                success: function(response) {
                    if (response.success) {
                        $('#kartUidBilgi').text(response.kart_uid);
                        $('#kartBeklemeEkrani').slideUp();
                        $('#kartBasariliEkrani').slideDown();
                    } else {
                        alert('Hata: ' + response.message);
                        $('#kartOkutBtn').prop('disabled', false).html('<i class="fas fa-credit-card me-2"></i>Kart Okut (Demo)');
                    }
                },
                error: function(xhr) {
                    console.error('AJAX Error:', xhr);
                    alert('Sunucu hatası oluştu!');
                    $('#kartOkutBtn').prop('disabled', false).html('<i class="fas fa-credit-card me-2"></i>Kart Okut (Demo)');
                }
            });
        });
    });
    </script>
</body>
</html>
