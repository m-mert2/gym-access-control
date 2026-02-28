<?php
require_once '../includes/db_config.php';
startSecureSession();
if (!isLoggedIn()) {
    header('Location: ../index.php');
    exit;
}
$username = $_SESSION['username'] ?? 'Kullanıcı';
$userRole = $_SESSION['role'] ?? 'vezne';
$isAdmin = isAdmin();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Üyelik Oluştur - ESOĞÜ Spor Salonu</title>
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
                    <h2><i class="fas fa-id-card-alt me-2"></i>Üyelik Oluştur</h2>
                    <p class="text-muted">
                        Kayıtlı kişilere üyelik tanımlayın
                        <?php if ($isAdmin): ?>
                            <span class="badge bg-danger ms-2">Admin - Sınırsız hak tanıyabilir</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>

<div class="row mb-4" id="step1">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Adım 1: Kart Seçimi</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <input type="text" class="form-control" id="searchKart" placeholder="Ara: İsim, Soyisim, Kart UID...">
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-warning w-100" id="kartOkutBtn">
                                        <i class="fas fa-qrcode me-2"></i>Kart Okut (Simülasyon)
                                    </button>
                                </div>
                            </div>

                            <div class="row" id="kartListesi">
                                <div class="col-12 text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Yükleniyor...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

<div class="row" id="step2" style="display: none;">
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-user-check me-2"></i>Adım 2: Üyelik Tanımlama</h5>
                        </div>
                        <div class="card-body">
                            
                            <div class="alert alert-info" id="seciliKartInfo"></div>

                            <form id="uyelikForm">
                                <input type="hidden" id="seciliKartId" name="kart_id">
                                <input type="hidden" id="seciliKisiselId" name="kisisel_id">

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Üyelik Türü <span class="text-danger">*</span></label>
                                        <select class="form-select" id="turId" name="tur_id" required>
                                            <option value="">Seçiniz</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Başlangıç Tarihi</label>
                                        <input type="date" class="form-control" id="baslangicTarihi" name="baslangic_tarihi" readonly>
                                        <div class="form-text">Otomatik bugün atanır</div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Bitiş Tarihi</label>
                                        <input type="date" class="form-control" id="bitisTarihi" name="bitis_tarihi" readonly>
                                        <div class="form-text">Üyelik türüne göre otomatik hesaplanır</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Kalan Giriş Hakkı</label>
                                        <input type="number" class="form-control" id="kalanGiris" name="kalan_giris" readonly>
                                        <div class="form-text">Üyelik türüne göre otomatik atanır</div>
                                    </div>
                                </div>

                                <?php if ($isAdmin): ?>
                                <div class="mb-3" id="adminOzellik">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="sinirsizHak">
                                        <label class="form-check-label" for="sinirsizHak">
                                            <strong>Sınırsız Hak Tanı</strong> <span class="badge bg-danger">Admin Yetkisi</span>
                                        </label>
                                    </div>
                                    <div class="form-text text-warning">Bu seçenek işaretlenirse kalan giriş hakkı -1 olarak kaydedilir (sınırsız)</div>
                                </div>
                                <?php endif; ?>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-check me-2"></i>Üyelik Oluştur
                                    </button>
                                    <button type="button" class="btn btn-secondary" id="resetBtn">
                                        <i class="fas fa-arrow-left me-2"></i>Geri
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Uyarı</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Üyelik türleri:</strong></p>
                            <ul id="turListesi">
                                <li><em>Yükleniyor...</em></li>
                            </ul>

                            <?php if ($isAdmin): ?>
                            <div class="alert alert-danger mt-3 mb-0">
                                <i class="fas fa-crown me-2"></i>
                                <strong>Admin Yetkisi:</strong><br>
                                Personel veya özel durumlar için sınırsız giriş hakkı tanıyabilirsiniz.
                            </div>
                            <?php else: ?>
                            <div class="alert alert-info mt-3 mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                Sadece tanımlı üyelik türlerine göre üyelik oluşturabilirsiniz.
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/jquery-3.7.1.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    
    <script>
    let kartlarData = [];

    $(document).ready(function() {
        const today = new Date().toISOString().split('T')[0];
        $('#baslangicTarihi').val(today);
        
        loadKartlar();
        loadUyelikTurleri();

        $('#searchKart').on('keyup', function() {
            const term = $(this).val();
            console.log('Arama:', term);
            filterKartlar(term);
        });

        $('#turId').on('change', function() {
            hesaplaTarihler();
        });

        $('#sinirsizHak').on('change', function() {
            if ($(this).is(':checked')) {
                $('#kalanGiris').val(-1);
            } else {
                hesaplaTarihler();
            }
        });

        $('#uyelikForm').on('submit', function(e) {
            e.preventDefault();
            uyelikOlustur();
        });

        $('#kartOkutBtn').on('click', function() {
            kartOkut($(this));
        });

        $('#resetBtn').on('click', function() {
            resetForm();
        });
    });

    function loadKartlar() {
        $.ajax({
            url: '../api/kayitli_kartlar.php',
            type: 'GET',
            success: function(response) {
                if (response.success && (response.kartlar || response.data)) {
                    kartlarData = response.kartlar || response.data;
                    renderKartlar(kartlarData);
                } else {
                    $('#kartListesi').html('<div class="col-12"><div class="alert alert-warning">Kayıtlı kart bulunamadı</div></div>');
                }
            },
            error: function() {
                $('#kartListesi').html('<div class="col-12"><div class="alert alert-danger">Kartlar yüklenemedi</div></div>');
            }
        });
    }

    function renderKartlar(kartlar) {
        if (kartlar.length === 0) {
            $('#kartListesi').html('<div class="col-12"><div class="alert alert-warning">Sonuç bulunamadı</div></div>');
            return;
        }

        let html = '';
        kartlar.forEach(kart => {
            const aktifUyelik = kart.uyelik_durum == 1;
            const badge = aktifUyelik ? '<span class="badge bg-success">Aktif Üyelik Var</span>' : '<span class="badge bg-secondary">Üyeliksiz</span>';
            const disabled = aktifUyelik ? 'disabled' : '';
            const safeIsim = String(kart.isim || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
            const safeSoyisim = String(kart.soyisim || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
            const safeKartUid = String(kart.kart_uid || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
            
            html += `
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card h-100 ${aktifUyelik ? 'border-success' : ''}">
                        <div class="card-body">
                            <h6 class="card-title">${safeIsim} ${safeSoyisim}</h6>
                            <p class="card-text mb-1"><small class="text-muted">Kart UID: ${safeKartUid}</small></p>
                            ${badge}
                            ${aktifUyelik ? '<p class="text-danger mt-2 mb-0"><small><i class="fas fa-ban"></i> Zaten aktif üyelik var</small></p>' : ''}
                            <button class="btn btn-primary btn-sm mt-2 w-100 kart-sec-btn" 
                                data-kart-id="${kart.kart_id}" 
                                data-kisisel-id="${kart.kisisel_id}" 
                                data-isim="${safeIsim}" 
                                data-soyisim="${safeSoyisim}" 
                                data-kart-uid="${safeKartUid}" 
                                ${disabled}>
                                <i class="fas fa-hand-pointer"></i> Seç
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });

        $('#kartListesi').html(html);
        
        $('.kart-sec-btn').off('click').on('click', function() {
            secKart(
                $(this).data('kart-id'),
                $(this).data('kisisel-id'),
                $(this).data('isim'),
                $(this).data('soyisim'),
                $(this).data('kart-uid')
            );
        });
    }

    function filterKartlar(searchTerm) {
        console.log('filterKartlar çağrıldı, terim:', searchTerm);
        console.log('kartlarData:', kartlarData.length);
        
        if (!searchTerm || searchTerm.trim() === '') {
            console.log('Boş arama, tüm kartlar gösteriliyor');
            renderKartlar(kartlarData);
            return;
        }

        const filtered = kartlarData.filter(kart => {
            const term = searchTerm.toLowerCase();
            const isim = (kart.isim || '').toLowerCase();
            const soyisim = (kart.soyisim || '').toLowerCase();
            const kartUid = (kart.kart_uid || '').toLowerCase();
            
            const match = isim.includes(term) || soyisim.includes(term) || kartUid.includes(term);
            if (match) console.log('Eşleşen kart:', kart.isim, kart.soyisim);
            return match;
        });

        console.log('Filtrelenen:', filtered.length, '/', kartlarData.length);
        renderKartlar(filtered);
    }

    function loadUyelikTurleri() {
        $.ajax({
            url: '../api/uyelik_turleri.php',
            type: 'GET',
            success: function(response) {
                if (response.success && (response.turler || response.data)) {
                    const turler = response.turler || response.data;
                    let options = '<option value="">Seçiniz</option>';
                    let listHtml = '';
                    
                    turler.forEach(tur => {
                        let gun = 30;
                        if (tur.tur_adi.includes('Günlük')) gun = 1;
                        else if (tur.tur_adi.includes('Haftalık')) gun = 7;
                        else if (tur.tur_adi.includes('Aylık')) gun = 30;
                        else if (tur.tur_adi.includes('3 Aylık')) gun = 90;
                        else if (tur.tur_adi.includes('6 Aylık')) gun = 180;
                        else if (tur.tur_adi.includes('Yıllık')) gun = 365;
                        
                        options += `<option value="${tur.tur_id}" data-giris="${tur.varsayilan_giris_hakki}" data-gun="${gun}">${tur.tur_adi}</option>`;
                        listHtml += `<li><strong>${tur.tur_adi}:</strong> ${tur.varsayilan_giris_hakki} giriş, ~${gun} gün</li>`;
                    });
                    
                    $('#turId').html(options);
                    $('#turListesi').html(listHtml);
                }
            },
            error: function() {
                $('#turListesi').html('<li class="text-danger">Yüklenemedi</li>');
            }
        });
    }

    function secKart(kartId, kisiselId, isim, soyisim, kartUid) {
        console.log('secKart çağrıldı:', kartId, kisiselId, isim, soyisim, kartUid);
        $('#seciliKartId').val(kartId);
        $('#seciliKisiselId').val(kisiselId);
        $('#seciliKartInfo').html(`<i class="fas fa-user-circle me-2"></i><strong>${isim} ${soyisim}</strong> - Kart UID: ${kartUid}`);
        $('#step1').hide();
        $('#step2').show();
        console.log('Adım 2 gösterildi');
    }

    function hesaplaTarihler() {
        const turId = $('#turId').val();
        if (!turId) return;

        const selected = $('#turId option:selected');
        const girisHakki = parseInt(selected.data('giris'));
        const gun = parseInt(selected.data('gun'));

        $('#kalanGiris').val(girisHakki);

        const today = new Date();
        const bitis = new Date(today);
        bitis.setDate(bitis.getDate() + gun);
        $('#bitisTarihi').val(bitis.toISOString().split('T')[0]);
    }

    function uyelikOlustur() {
        console.log('uyelikOlustur çağrıldı');
        
        const formData = {
            kart_id: $('#seciliKartId').val(),
            kisisel_id: $('#seciliKisiselId').val(),
            tur_id: $('#turId').val(),
            baslangic_tarihi: $('#baslangicTarihi').val(),
            bitis_tarihi: $('#bitisTarihi').val(),
            kalan_giris: $('#sinirsizHak').is(':checked') ? -1 : parseInt($('#kalanGiris').val())
        };

        console.log('Form Data:', formData);

        if (!formData.tur_id) {
            alert('Lütfen üyelik türü seçin!');
            return;
        }

        console.log('API çağrısı yapılıyor...');
        
        $.ajax({
            url: '../api/uyelik_olustur.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            success: function(response) {
                console.log('API Response:', response);
                if (response.success) {
                    alert('Üyelik başarıyla oluşturuldu!');
                    location.reload();
                } else {
                    alert('Hata: ' + response.message);
                }
            },
            error: function(xhr) {
                console.error('AJAX Error:', xhr);
                console.error('Response Text:', xhr.responseText);
                alert('Sunucu hatası oluştu!');
            }
        });
    }

    function resetForm() {
        $('#step1').show();
        $('#step2').hide();
        $('#uyelikForm')[0].reset();
        $('#sinirsizHak').prop('checked', false);
    }
    function kartOkut($btn) {
        const randomUID = Array.from({length: 8}, () => 
            Math.floor(Math.random() * 16).toString(16).toUpperCase()
        ).join('');
        
        $('#searchKart').val(randomUID);
        $('#searchKart').trigger('keyup');
        const originalText = $btn.html();
        $btn.html('<i class="fas fa-check me-2"></i>Kart Okundu!');
        $btn.removeClass('btn-warning').addClass('btn-success');
        
        setTimeout(() => {
            $btn.html(originalText);
            $btn.removeClass('btn-success').addClass('btn-warning');
        }, 1500);
    }
    </script>
</body>
</html>
