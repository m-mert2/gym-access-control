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
    <title>Turnike Kontrolü - ESOĞÜ Spor Salonu</title>
    <link href="../assets/vendor/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/vendor/fontawesome.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .turnike-page {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .turnike-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .turnike-box {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 20px;
        }
        .card-reader-icon {
            font-size: 4rem;
            color: #667eea;
            text-align: center;
            margin: 20px 0;
        }
        .status-display {
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            margin: 20px 0;
            transition: all 0.3s ease;
        }
        .status-display.waiting {
            background: #e3f2fd;
            border: 2px solid #2196f3;
            color: #1976d2;
        }
        .status-display.success {
            background: #e8f5e9;
            border: 2px solid #4caf50;
            color: #2e7d32;
        }
        .status-display.error {
            background: #ffebee;
            border: 2px solid #f44336;
            color: #c62828;
        }
        .status-display.warning {
            background: #fff3e0;
            border: 2px solid #ff9800;
            color: #e65100;
        }
        .simulation-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border: 2px dashed #6c757d;
        }
        .kart-listesi {
            max-height: 400px;
            overflow-y: auto;
        }
        .kart-item {
            cursor: pointer;
            transition: all 0.2s;
            padding: 12px;
            margin-bottom: 8px;
            background: white;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
        }
        .kart-item:hover {
            border-color: #667eea;
            transform: translateX(5px);
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        }
        .kart-item.selected {
            border-color: #667eea;
            background: #f0f4ff;
        }
        .user-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-top: 5px;
        }
        .user-badge.aktif {
            background: #d4edda;
            color: #155724;
        }
        .user-badge.pasif {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body class="turnike-page">
    <div class="turnike-container">
        <div class="row">
            
            <div class="col-md-6">
                <div class="turnike-box">
                    <h3><i class="fas fa-vial"></i> Turnike Simülasyonu (Test)</h3>
                    <p class="text-muted">Kayıtlı kartlarla test yapın</p>
                    
                    <div class="simulation-section">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Kayıtlı Kartlar</label>
                            <input type="text" class="form-control mb-2" id="kartAra" placeholder="İsim, TC veya Kart UID ile ara...">
                        </div>
                        
                        <div class="kart-listesi" id="kartListesi">
                            <div class="text-center text-muted">
                                <div class="spinner-border" role="status"></div>
                                <p class="mt-2">Kartlar yükleniyor...</p>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <button class="btn btn-primary btn-lg w-100" id="simulasyonOkutBtn" disabled>
                                <i class="fas fa-id-card-alt"></i> Seçili Kartı Okut
                            </button>
                        </div>
                    </div>
                    
                    <div id="simulasyonSonuc" class="mt-3"></div>
                </div>
            </div>

<div class="col-md-6">
                <div class="turnike-box">
                    <h3><i class="fas fa-door-open"></i> Turnike Giriş Kontrolü</h3>
                    <p class="text-muted">Fiziksel kart okuyucu / Manuel test</p>
                    
                    <div class="card-reader-icon">
                        <i class="fas fa-id-card"></i>
                    </div>
                    
                    <h4 class="text-center">Kartınızı Okutunuz</h4>
                    
                    <div class="status-display waiting" id="statusDisplay">
                        <i class="fas fa-info-circle fa-2x mb-2"></i>
                        <h5>Bekleniyor...</h5>
                        <p class="mb-0">Lütfen kartınızı okutun</p>
                    </div>
                    
                    <div class="mt-4">
                        <label class="form-label">Kart UID (Manuel Test)</label>
                        <div class="input-group input-group-lg">
                            <input type="text" id="kartInput" class="form-control text-center" 
                                   placeholder="Kart UID giriniz" autofocus 
                                   style="font-family: monospace; font-size: 1.2rem;">
                            <button class="btn btn-success" onclick="kartKontrolEt()">
                                <i class="fas fa-check"></i> Kontrol Et
                            </button>
                        </div>
                        <small class="text-muted">Kart okuyucudan otomatik gelir veya manuel girebilirsiniz</small>
                    </div>
                    
                    <div class="mt-4 text-center">
                        <a href="dashboard.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Dashboard'a Dön
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/vendor/jquery.min.js"></script>
    <script src="../assets/vendor/bootstrap.bundle.min.js"></script>
    
    <script>
    let secilenKart = null;
    let tumKartlar = [];
    
    $(document).ready(function() {
        kartlariYukle();
        $('#kartInput').on('keypress', function(e) {
            if (e.which === 13) {
                kartKontrolEt();
            }
        });
        $('#kartAra').on('input', function() {
            const aranan = $(this).val().toLowerCase();
            tumKartlar.forEach(kart => {
                const kartDiv = $(`#kart-${kart.kart_id}`);
                const aranacakMetin = `${kart.isim} ${kart.soyisim} ${kart.tc_no} ${kart.kart_uid}`.toLowerCase();
                if (aranacakMetin.includes(aranan)) {
                    kartDiv.show();
                } else {
                    kartDiv.hide();
                }
            });
        });
    });
    
    function kartlariYukle() {
        $.ajax({
            url: '../api/kayitli_kartlar.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    tumKartlar = response.data;
                    kartListesiniGoster(response.data);
                } else {
                    $('#kartListesi').html('<div class="alert alert-danger">Kartlar yüklenemedi!</div>');
                }
            },
            error: function() {
                $('#kartListesi').html('<div class="alert alert-danger">Bağlantı hatası!</div>');
            }
        });
    }
    
    function kartListesiniGoster(kartlar) {
        const liste = $('#kartListesi');
        liste.empty();
        
        if (kartlar.length === 0) {
            liste.html('<div class="alert alert-info">Kayıtlı kart bulunamadı</div>');
            return;
        }
        
        kartlar.forEach(kart => {
            const durumClass = kart.uyelik_durum == 1 ? 'aktif' : 'pasif';
            const durumText = kart.uyelik_durum == 1 ? 'Aktif' : 'Pasif';
            const kalanGiris = kart.kalan_giris === -1 ? 'Sınırsız' : kart.kalan_giris;
            
            const kartDiv = $(`
                <div class="kart-item" id="kart-${kart.kart_id}" data-kart-id="${kart.kart_id}" data-kart-uid="${kart.kart_uid}">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>${kart.isim} ${kart.soyisim}</strong>
                            <br>
                            <small class="text-muted">TC: ${kart.tc_no}</small>
                            <br>
                            <small class="text-muted">Kart: <code>${kart.kart_uid}</code></small>
                            <br>
                            <span class="user-badge ${durumClass}">${durumText}</span>
                            <span class="badge bg-secondary">${kart.tur_adi}</span>
                        </div>
                        <div class="text-end">
                            <small class="text-muted">Kalan Giriş</small>
                            <br>
                            <strong class="fs-5">${kalanGiris}</strong>
                        </div>
                    </div>
                </div>
            `);
            
            kartDiv.on('click', function() {
                $('.kart-item').removeClass('selected');
                $(this).addClass('selected');
                secilenKart = {
                    kart_id: kart.kart_id,
                    kart_uid: kart.kart_uid,
                    isim: kart.isim,
                    soyisim: kart.soyisim
                };
                $('#simulasyonOkutBtn').prop('disabled', false);
            });
            
            liste.append(kartDiv);
        });
    }
    
    $('#simulasyonOkutBtn').on('click', function() {
        if (!secilenKart) return;
        
        $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Okutiliyor...');
        $('#simulasyonSonuc').html('');
        turnikeGecisKontrol(secilenKart.kart_uid, true);
    });
    
    function kartKontrolEt() {
        const kartUID = $('#kartInput').val().trim();
        if (!kartUID) {
            alert('Lütfen kart UID giriniz!');
            return;
        }
        
        $('#kartInput').prop('disabled', true);
        turnikeGecisKontrol(kartUID, false);
    }
    
    function turnikeGecisKontrol(kartUID, simulasyon) {
        const statusDiv = simulasyon ? $('#simulasyonSonuc') : $('#statusDisplay');
        
        if (!simulasyon) {
            statusDiv.removeClass('waiting success error warning')
                     .addClass('waiting')
                     .html('<div class="spinner-border mb-2"></div><h5>Kontrol Ediliyor...</h5><p class="mb-0">Lütfen bekleyin</p>');
        }
        
        $.ajax({
            url: '../api/turnike_kontrol.php',
            type: 'POST',
            data: JSON.stringify({
                kart_uid: kartUID,
                turnike_id: 1
            }),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const html = `
                        <div class="status-display success">
                            <i class="fas fa-check-circle fa-3x mb-3"></i>
                            <h4>${response.mesaj}</h4>
                            <h3 class="mt-3">${response.uye_adi}</h3>
                            <p class="mb-0">Kalan Giriş: <strong>${response.kalan_giris === -1 ? 'Sınırsız' : response.kalan_giris}</strong></p>
                            <p class="mb-0">Üyelik: <strong>${response.uyelik_turu}</strong></p>
                        </div>
                    `;
                    
                    if (simulasyon) {
                        statusDiv.html(html);
                        playSound('success');
                    } else {
                        statusDiv.removeClass('waiting warning error').addClass('success')
                                .html(html.replace('status-display success', ''));
                    }
                    setTimeout(function() {
                        if (simulasyon) {
                            statusDiv.html('');
                            $('#simulasyonOkutBtn').prop('disabled', false)
                                .html('<i class="fas fa-id-card-alt"></i> Seçili Kartı Okut');
                        } else {
                            statusDiv.removeClass('success').addClass('waiting')
                                    .html('<i class="fas fa-info-circle fa-2x mb-2"></i><h5>Bekleniyor...</h5><p class="mb-0">Lütfen kartınızı okutun</p>');
                            $('#kartInput').val('').prop('disabled', false).focus();
                        }
                    }, 3000);
                    
                } else {
                    const html = `
                        <div class="status-display ${response.hata_tipi === 'UYARI' ? 'warning' : 'error'}">
                            <i class="fas fa-${response.hata_tipi === 'UYARI' ? 'exclamation-triangle' : 'times-circle'} fa-3x mb-3"></i>
                            <h4>GİRİŞ REDDEDİLDİ</h4>
                            <p class="fs-5 mb-0">${response.mesaj}</p>
                            ${response.uye_adi ? `<p class="mt-2"><strong>${response.uye_adi}</strong></p>` : ''}
                        </div>
                    `;
                    
                    if (simulasyon) {
                        statusDiv.html(html);
                        playSound('error');
                    } else {
                        statusDiv.removeClass('waiting success').addClass(response.hata_tipi === 'UYARI' ? 'warning' : 'error')
                                .html(html.replace(/status-display (warning|error)/, ''));
                    }
                    setTimeout(function() {
                        if (simulasyon) {
                            statusDiv.html('');
                            $('#simulasyonOkutBtn').prop('disabled', false)
                                .html('<i class="fas fa-id-card-alt"></i> Seçili Kartı Okut');
                        } else {
                            statusDiv.removeClass('warning error').addClass('waiting')
                                    .html('<i class="fas fa-info-circle fa-2x mb-2"></i><h5>Bekleniyor...</h5><p class="mb-0">Lütfen kartınızı okutun</p>');
                            $('#kartInput').val('').prop('disabled', false).focus();
                        }
                    }, 3000);
                }
            },
            error: function(xhr, status, error) {
                const html = `
                    <div class="status-display error">
                        <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                        <h4>SISTEM HATASI</h4>
                        <p class="mb-0">Bağlantı hatası oluştu!</p>
                    </div>
                `;
                
                if (simulasyon) {
                    statusDiv.html(html);
                    $('#simulasyonOkutBtn').prop('disabled', false)
                        .html('<i class="fas fa-id-card-alt"></i> Seçili Kartı Okut');
                } else {
                    statusDiv.removeClass('waiting success warning').addClass('error')
                            .html(html.replace('status-display error', ''));
                    $('#kartInput').prop('disabled', false);
                }
            }
        });
    }
    
    function playSound(type) {
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            oscillator.frequency.value = type === 'success' ? 800 : 400;
            oscillator.type = 'sine';
            
            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
            
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.5);
        } catch (e) {
            console.log('Ses çalınamadı:', e);
        }
    }
    </script>
</body>
</html>
