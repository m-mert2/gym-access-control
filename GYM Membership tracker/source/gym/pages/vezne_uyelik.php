<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Üyelik Tanımlama - ESOĞÜ Spor Salonu</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/font-awesome.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 30px;
            text-align: center;
        }

        .header h1 {
            color: #667eea;
            font-weight: bold;
            margin: 0;
        }

        .header p {
            color: #6c757d;
            margin: 10px 0 0 0;
            font-size: 1.1rem;
        }

        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
        }

        .panel {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 30px;
            height: 100%;
        }

        .panel-title {
            font-size: 1.8rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 20px;
            text-align: center;
        }

        .big-button {
            width: 100%;
            padding: 25px;
            font-size: 1.5rem;
            font-weight: bold;
            border-radius: 15px;
            transition: all 0.3s;
        }

        .big-button:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }

        .card-kart {
            border: 2px solid #dee2e6;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .card-kart:hover {
            border-color: #667eea;
            transform: scale(1.02);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .card-kart.selected {
            border-color: #28a745;
            background: #d4edda;
        }

        .step-container {
            display: none;
        }

        .step-container.active {
            display: block;
        }

        .form-control-lg, .form-select-lg {
            font-size: 1.3rem;
            padding: 15px;
        }

        .info-box {
            background: #f8f9fa;
            border-left: 5px solid #667eea;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }

        .info-box h5 {
            color: #667eea;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <a href="../login.php" class="btn btn-danger logout-btn">
        <i class="fas fa-sign-out-alt me-2"></i>Çıkış
    </a>

    <div class="main-container">
        
        <div class="header">
            <h1><i class="fas fa-id-card-alt me-3"></i>Üyelik Tanımlama Sistemi</h1>
            <p>Vezne Personeli - Basit ve Hızlı İşlem</p>
        </div>

<div id="step1" class="step-container active">
            <div class="row">
                <div class="col-12">
                    <div class="panel">
                        <h2 class="panel-title">
                            <i class="fas fa-credit-card me-2"></i>ADIM 1: KART SEÇİMİ
                        </h2>

                        <div class="row mb-4">
                            <div class="col-md-8">
                                <input type="text" class="form-control form-control-lg" id="searchKart" placeholder="🔍 Ara: İsim, Soyisim, Kart Numarası...">
                            </div>
                            <div class="col-md-4">
                                <button type="button" class="btn btn-warning btn-lg w-100" onclick="kartOkut()" style="font-size: 1.2rem; padding: 12px;">
                                    <i class="fas fa-qrcode me-2"></i>KART OKUT
                                </button>
                            </div>
                        </div>

                        <div id="kartListesi">
                            <div class="text-center">
                                <div class="spinner-border text-primary" style="width: 4rem; height: 4rem;" role="status">
                                    <span class="visually-hidden">Yükleniyor...</span>
                                </div>
                                <p class="mt-3 text-muted">Kartlar yükleniyor...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

<div id="step2" class="step-container">
            <div class="row">
                <div class="col-lg-7">
                    <div class="panel">
                        <h2 class="panel-title">
                            <i class="fas fa-user-check me-2"></i>ADIM 2: ÜYELİK BİLGİLERİ
                        </h2>

                        <div class="info-box" id="kartBilgiBox">
                            <h5>Seçili Kart</h5>
                            <p class="mb-0" id="kartBilgiText"></p>
                        </div>

                        <form id="uyelikForm">
                            <input type="hidden" id="seciliKartId">
                            <input type="hidden" id="seciliKisiselId">

                            <div class="mb-4">
                                <label class="form-label" style="font-size: 1.3rem; font-weight: bold;">
                                    <i class="fas fa-calendar me-2"></i>Üyelik Türü Seçin
                                </label>
                                <select class="form-select form-select-lg" id="turId" required>
                                    <option value="">Seçiniz...</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label" style="font-size: 1.3rem; font-weight: bold;">
                                    <i class="fas fa-hourglass-half me-2"></i>Kalan Giriş Hakkı
                                </label>
                                <input type="text" class="form-control form-control-lg" id="kalanGiris" readonly>
                            </div>

                            <div class="mb-4">
                                <label class="form-label" style="font-size: 1.3rem; font-weight: bold;">
                                    <i class="fas fa-calendar-check me-2"></i>Geçerlilik Süresi
                                </label>
                                <input type="text" class="form-control form-control-lg" id="gecerlilikSure" readonly>
                            </div>

                            <div class="d-grid gap-3">
                                <button type="submit" class="big-button btn btn-success">
                                    <i class="fas fa-check-circle me-2"></i>ÜYELİK OLUŞTUR
                                </button>
                                <button type="button" class="big-button btn btn-secondary" onclick="geriDon()">
                                    <i class="fas fa-arrow-left me-2"></i>GERİ DÖN
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="panel">
                        <h3 class="text-center mb-4" style="color: #667eea;">
                            <i class="fas fa-info-circle me-2"></i>BİLGİLENDİRME
                        </h3>

                        <div class="alert alert-primary">
                            <h5><i class="fas fa-calendar-day me-2"></i>Günlük Üyelik</h5>
                            <ul class="mb-0">
                                <li>1 Giriş Hakkı</li>
                                <li>1 Gün Geçerli</li>
                            </ul>
                        </div>

                        <div class="alert alert-success">
                            <h5><i class="fas fa-calendar-alt me-2"></i>Aylık Üyelik</h5>
                            <ul class="mb-0">
                                <li>30 Giriş Hakkı</li>
                                <li>30 Gün Geçerli</li>
                            </ul>
                        </div>

                        <div class="alert alert-warning">
                            <h5><i class="fas fa-exclamation-triangle me-2"></i>Önemli</h5>
                            <p class="mb-0">Kişinin zaten aktif üyeliği varsa yeni üyelik tanımlayamazsınız.</p>
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
    function kartOkut() {
        const randomUID = Array.from({length: 8}, () => 
            Math.floor(Math.random() * 16).toString(16).toUpperCase()
        ).join('');
        
        $('#searchKart').val(randomUID);
        $('#searchKart').trigger('input');
        const btn = event.target.closest('button');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check me-2"></i>OKUNDU!';
        btn.classList.remove('btn-warning');
        btn.classList.add('btn-success');
        
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-warning');
        }, 1500);
    }

    $(document).ready(function() {
        loadKartlar();
        loadUyelikTurleri();

        $('#searchKart').on('input', function() {
            filterKartlar($(this).val());
        });

        $('#turId').on('change', function() {
            updateGecerlilikBilgileri();
        });

        $('#uyelikForm').on('submit', function(e) {
            e.preventDefault();
            uyelikTanimla();
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
                    $('#kartListesi').html('<div class="alert alert-warning text-center" style="font-size: 1.2rem;">Kayıtlı kart bulunamadı</div>');
                }
            },
            error: function() {
                $('#kartListesi').html('<div class="alert alert-danger text-center" style="font-size: 1.2rem;">Kartlar yüklenemedi</div>');
            }
        });
    }

    function renderKartlar(kartlar) {
        if (kartlar.length === 0) {
            $('#kartListesi').html('<div class="alert alert-warning text-center" style="font-size: 1.2rem;">Sonuç bulunamadı</div>');
            return;
        }

        let html = '<div class="row">';
        kartlar.forEach(kart => {
            const aktifUyelik = kart.uyelik_durum == 1;
            const opacity = aktifUyelik ? 'style="opacity: 0.5;"' : '';
            
            html += `
                <div class="col-md-6 mb-3" ${opacity}>
                    <div class="card-kart kart-sec-btn" 
                         data-kart-id="${kart.kart_id}" 
                         data-kisisel-id="${kart.kisisel_id}" 
                         data-isim="${kart.isim}" 
                         data-soyisim="${kart.soyisim}" 
                         data-kart-uid="${kart.kart_uid}" 
                         data-aktif="${aktifUyelik}">
                        <h4 class="mb-2">
                            <i class="fas fa-user-circle me-2" style="color: #667eea;"></i>
                            ${kart.isim} ${kart.soyisim}
                        </h4>
                        <p class="mb-1"><strong>Kart UID:</strong> ${kart.kart_uid}</p>
                        <span class="badge ${aktifUyelik ? 'bg-danger' : 'bg-success'}" style="font-size: 1rem; padding: 8px 15px;">
                            ${aktifUyelik ? 'ZATEN AKTİF ÜYELİK VAR' : 'ÜYELİK YOK - SEÇİLEBİLİR'}
                        </span>
                    </div>
                </div>
            `;
        });
        html += '</div>';

        $('#kartListesi').html(html);
        $('#kartListesi').off('click', '.kart-sec-btn').on('click', '.kart-sec-btn', function() {
            const $card = $(this);
            const aktif = $card.data('aktif') === true || $card.data('aktif') === 'true' || $card.data('aktif') === '1';
            
            if (aktif) {
                alert('⚠️ BU KİŞİNİN ZATEN AKTİF ÜYELİĞİ VAR!\n\nYeni üyelik tanımlayamazsınız.');
                return;
            }
            
            secKart(
                $card.data('kart-id'),
                $card.data('kisisel-id'),
                $card.data('isim'),
                $card.data('soyisim'),
                $card.data('kart-uid')
            );
        });
    }

    function filterKartlar(searchTerm) {
        if (!searchTerm) {
            renderKartlar(kartlarData);
            return;
        }

        const filtered = kartlarData.filter(kart => {
            const term = searchTerm.toLowerCase();
            return kart.isim.toLowerCase().includes(term) ||
                   kart.soyisim.toLowerCase().includes(term) ||
                   kart.kart_uid.toLowerCase().includes(term);
        });

        renderKartlar(filtered);
    }

    function loadUyelikTurleri() {
        $.ajax({
            url: '../api/uyelik_turleri.php',
            type: 'GET',
            success: function(response) {
                if (response.success && response.turler) {
                    let options = '<option value="">Seçiniz...</option>';
                    response.turler.forEach(tur => {
                        options += `<option value="${tur.tur_id}" data-giris="${tur.varsayilan_giris_hakki}" data-adi="${tur.tur_adi}">${tur.tur_adi}</option>`;
                    });
                    $('#turId').html(options);
                }
            }
        });
    }

    function secKart(kartId, kisiselId, isim, soyisim, kartUid) {
        $('#seciliKartId').val(kartId);
        $('#seciliKisiselId').val(kisiselId);
        $('#kartBilgiText').html(`<strong style="font-size: 1.3rem;">${isim} ${soyisim}</strong><br>Kart UID: ${kartUid}`);
        
        $('#step1').removeClass('active');
        $('#step2').addClass('active');
    }

    function updateGecerlilikBilgileri() {
        const selected = $('#turId option:selected');
        const giris = selected.data('giris');
        const adi = selected.data('adi');

        if (!adi) return;

        $('#kalanGiris').val(giris + ' giriş');
        
        if (adi === 'Günlük') {
            $('#gecerlilikSure').val('1 gün (Bugün)');
        } else if (adi === 'Aylık') {
            $('#gecerlilikSure').val('30 gün');
        }
    }

    function uyelikTanimla() {
        const kartId = $('#seciliKartId').val();
        const kisiselId = $('#seciliKisiselId').val();
        const turId = $('#turId').val();

        if (!turId) {
            alert('⚠️ LÜTFEN ÜYELİK TÜRÜ SEÇİN!');
            return;
        }

        const selected = $('#turId option:selected');
        const giris = parseInt(selected.data('giris'));
        const adi = selected.data('adi');
        const today = new Date();
        const baslangic = today.toISOString().split('T')[0];
        
        let bitis = new Date(today);
        if (adi === 'Günlük') {
            bitis.setDate(bitis.getDate() + 1);
        } else {
            bitis.setDate(bitis.getDate() + 30);
        }
        const bitisStr = bitis.toISOString().split('T')[0];

        const formData = {
            kart_id: kartId,
            kisisel_id: kisiselId,
            tur_id: turId,
            baslangic_tarihi: baslangic,
            bitis_tarihi: bitisStr,
            kalan_giris: giris
        };

        $.ajax({
            url: '../api/uyelik_olustur.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            success: function(response) {
                if (response.success) {
                    alert('✅ ÜYELİK BAŞARIYLA OLUŞTURULDU!\n\nÜyelik Türü: ' + adi + '\nGiriş Hakkı: ' + giris);
                    location.reload();
                } else {
                    alert('❌ HATA: ' + response.message);
                }
            },
            error: function(xhr) {
                console.error('AJAX Error:', xhr);
                alert('❌ SUNUCU HATASI!');
            }
        });
    }

    function geriDon() {
        $('#step1').addClass('active');
        $('#step2').removeClass('active');
        $('#uyelikForm')[0].reset();
    }
    </script>
</body>
</html>
