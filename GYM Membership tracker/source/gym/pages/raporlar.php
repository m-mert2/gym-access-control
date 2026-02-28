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
    <title>Raporlar - ESOĞÜ Spor Salonu</title>
    <link href="../assets/vendor/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/vendor/fontawesome.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="dashboard-page">
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="top-navbar">
            <div class="page-title">
                <h3>Raporlar</h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Ana Sayfa</a></li>
                        <li class="breadcrumb-item active">Raporlar</li>
                    </ol>
                </nav>
            </div>
            <div class="user-info">
                <div><strong><?php echo htmlspecialchars($username); ?></strong><br><small class="text-muted"><?php echo ucfirst($role); ?></small></div>
                <div class="user-avatar"><?php echo strtoupper(substr($username, 0, 1)); ?></div>
            </div>
        </div>

<div class="card mb-4">
            <div class="card-header">
                <h5><i class="fas fa-filter"></i> Rapor Filtreleri</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Rapor Türü</label>
                        <select class="form-select" id="raporTuru">
                            <option value="gunluk">Günlük Rapor</option>
                            <option value="haftalik">Haftalık Rapor</option>
                            <option value="aylik" selected>Aylık Rapor</option>
                            <option value="yillik">Yıllık Rapor</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Başlangıç Tarihi</label>
                        <input type="date" class="form-control" id="baslangicTarih">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Bitiş Tarihi</label>
                        <input type="date" class="form-control" id="bitisTarih">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">&nbsp;</label>
                        <button class="btn btn-primary w-100" onclick="raporOlustur()">
                            <i class="fas fa-chart-bar"></i> Rapor Oluştur
                        </button>
                    </div>
                </div>
            </div>
        </div>

<div class="row">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-card-icon primary">
                        <i class="fas fa-door-open"></i>
                    </div>
                    <h6>Toplam Giriş</h6>
                    <h3 id="toplamGiris">-</h3>
                    <small>Seçili dönem</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-card-icon success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h6>Başarılı Giriş</h6>
                    <h3 id="basariliGiris">-</h3>
                    <small>Kabul edildi</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-card-icon danger">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <h6>Reddedilen</h6>
                    <h3 id="reddedilenGiris">-</h3>
                    <small>Red edildi</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-card-icon warning">
                        <i class="fas fa-users"></i>
                    </div>
                    <h6>Yeni Üye</h6>
                    <h3 id="yeniUye">-</h3>
                    <small>Kayıt olan</small>
                </div>
            </div>
        </div>

<div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-list"></i> Detaylı Rapor</h5>
                <div>
                    <button class="btn btn-danger btn-sm" onclick="mailGonder()">
                        <i class="fas fa-envelope"></i> Mail Gönder
                    </button>
                    <button class="btn btn-success btn-sm" onclick="excelAktar()">
                        <i class="fas fa-file-excel"></i> Excel'e Aktar
                    </button>
                    <button class="btn btn-info btn-sm" onclick="jsonAktar()">
                        <i class="fas fa-code"></i> JSON'a Aktar
                    </button>
                    <button class="btn btn-warning btn-sm" onclick="xmlAktar()">
                        <i class="fas fa-sitemap"></i> XML'e Aktar
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="raporTablosu">
                        <thead>
                            <tr>
                                <th>Tarih</th>
                                <th>Ad Soyad</th>
                                <th>Kart UID</th>
                                <th>Durum</th>
                                <th>Turnike</th>
                            </tr>
                        </thead>
                        <tbody id="raporTbody">
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    Rapor oluşturmak için yukarıdaki filtreleri kullanın
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

<div class="card mt-4">
            <div class="card-header">
                <h5><i class="fas fa-chart-line"></i> Günlük Giriş Grafiği</h5>
            </div>
            <div class="card-body">
                <canvas id="girisGrafik" height="80"></canvas>
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
        const birAyOnce = new Date();
        birAyOnce.setMonth(birAyOnce.getMonth() - 1);
        $('#baslangicTarih').val(birAyOnce.toISOString().split('T')[0]);
        raporOlustur();
    });
    
    function raporOlustur() {
        const baslangic = $('#baslangicTarih').val();
        const bitis = $('#bitisTarih').val();
        
        if (!baslangic || !bitis) {
            alert('Lütfen tarih aralığı seçin!');
            return;
        }
        $('#raporTbody').html('<tr><td colspan="5" class="text-center"><div class="spinner-border"></div></td></tr>');
        
        $.ajax({
            url: '../api/giris_loglari.php',
            type: 'GET',
            data: {
                baslangic: baslangic,
                bitis: bitis,
                limit: 1000
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#toplamGiris').text(response.stats.toplam || 0);
                    $('#basariliGiris').text(response.stats.basarili || 0);
                    $('#reddedilenGiris').text(response.stats.reddedilen || 0);
                    const tbody = $('#raporTbody');
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
                        
                        const row = `
                            <tr>
                                <td>${log.tarih_saat}</td>
                                <td>${log.ad_soyad || 'Bilinmiyor'}</td>
                                <td>${log.kart_uid || '-'}</td>
                                <td><span class="badge bg-${durumClass}">${durumText}</span></td>
                                <td>Turnike ${log.turnike_id || '1'}</td>
                            </tr>
                        `;
                        tbody.append(row);
                    });
                } else {
                    $('#raporTbody').html('<tr><td colspan="5" class="text-center text-danger">Hata: ' + response.message + '</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Rapor yüklenemedi:', error);
                $('#raporTbody').html('<tr><td colspan="5" class="text-center text-danger">Veriler yüklenemedi!</td></tr>');
            }
        });
    }
    
    function excelAktar() {
        const baslangic = $('#baslangicTarih').val();
        const bitis = $('#bitisTarih').val();
        let csv = 'Tarih,Ad Soyad,Kart UID,Durum,Turnike\n';
        
        $('#raporTbody tr').each(function() {
            const cols = $(this).find('td');
            if (cols.length === 5) {
                csv += cols.eq(0).text() + ',';
                csv += cols.eq(1).text() + ',';
                csv += cols.eq(2).text() + ',';
                csv += cols.eq(3).text().trim() + ',';
                csv += cols.eq(4).text() + '\n';
            }
        });
        const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `giris_raporu_${baslangic}_${bitis}.csv`;
        link.click();
        
        alert('Excel dosyası indirildi!');
    }
    
    function pdfAktar() {
        alert('PDF aktarım özelliği yakında eklenecek!');
    }
    
    function jsonAktar() {
        const baslangic = $('#baslangicTarih').val();
        const bitis = $('#bitisTarih').val();
        const jsonData = {
            rapor: 'Giriş Logları Raporu',
            tarih: new Date().toISOString(),
            filtreler: {
                baslangic_tarihi: baslangic,
                bitis_tarihi: bitis
            },
            istatistikler: {
                toplam_giris: $('#toplamGiris').text(),
                basarili_giris: $('#basariliGiris').text(),
                reddedilen_giris: $('#reddedilenGiris').text()
            },
            veriler: []
        };
        
        $('#raporTbody tr').each(function() {
            const cols = $(this).find('td');
            if (cols.length === 5) {
                jsonData.veriler.push({
                    tarih_saat: cols.eq(0).text(),
                    ad_soyad: cols.eq(1).text(),
                    kart_uid: cols.eq(2).text(),
                    durum: cols.eq(3).text().trim(),
                    turnike: cols.eq(4).text()
                });
            }
        });
        const blob = new Blob([JSON.stringify(jsonData, null, 2)], { type: 'application/json' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `giris_raporu_${baslangic}_${bitis}.json`;
        link.click();
        
        alert('JSON dosyası indirildi!');
    }
    
    function xmlAktar() {
        const baslangic = $('#baslangicTarih').val();
        const bitis = $('#bitisTarih').val();
        let xml = '<?xml version="1.0" encoding="UTF-8"?>\n';
        xml += '<Rapor>\n';
        xml += '  <BasliklAdi>Giriş Logları Raporu</BasliklAdi>\n';
        xml += '  <TarihOluşturma>' + new Date().toISOString() + '</TarihOluşturma>\n';
        xml += '  <Filtreler>\n';
        xml += '    <BaslangicTarihi>' + baslangic + '</BaslangicTarihi>\n';
        xml += '    <BitisTarihi>' + bitis + '</BitisTarihi>\n';
        xml += '  </Filtreler>\n';
        xml += '  <İstatistikler>\n';
        xml += '    <ToplamGiriş>' + $('#toplamGiris').text() + '</ToplamGiriş>\n';
        xml += '    <BasarıliGiriş>' + $('#basariliGiris').text() + '</BasarıliGiriş>\n';
        xml += '    <ReddedilenGiriş>' + $('#reddedilenGiris').text() + '</ReddedilenGiriş>\n';
        xml += '  </İstatistikler>\n';
        xml += '  <Veriler>\n';
        
        $('#raporTbody tr').each(function() {
            const cols = $(this).find('td');
            if (cols.length === 5) {
                xml += '    <Kayit>\n';
                xml += '      <TarihSaat>' + escapeXml(cols.eq(0).text()) + '</TarihSaat>\n';
                xml += '      <AdSoyad>' + escapeXml(cols.eq(1).text()) + '</AdSoyad>\n';
                xml += '      <KartUID>' + escapeXml(cols.eq(2).text()) + '</KartUID>\n';
                xml += '      <Durum>' + escapeXml(cols.eq(3).text().trim()) + '</Durum>\n';
                xml += '      <Turnike>' + escapeXml(cols.eq(4).text()) + '</Turnike>\n';
                xml += '    </Kayit>\n';
            }
        });
        
        xml += '  </Veriler>\n';
        xml += '</Rapor>';
        const blob = new Blob([xml], { type: 'application/xml' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `giris_raporu_${baslangic}_${bitis}.xml`;
        link.click();
        
        alert('XML dosyası indirildi!');
    }
    
    function escapeXml(str) {
        if (!str) return '';
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&apos;');
    }
    let currentReportData = null;
    
    function mailGonder() {
        if ($('#toplamGiris').text() === '-') {
            alert('⚠️ Önce rapor oluşturmalısınız!');
            return;
        }
        const email = prompt('📧 Raporu göndermek istediğiniz email adresini girin:', '');
        
        if (!email) {
            return;
        }
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert('❌ Geçersiz email adresi!');
            return;
        }
        const format = prompt('📎 Dosya formatı seçin:\n\n1 - Sadece HTML (mail içinde)\n2 - JSON dosyası ekle\n3 - XML dosyası ekle\n\nSeçiminiz (1/2/3):', '1');
        
        if (!format || !['1', '2', '3'].includes(format)) {
            alert('❌ Geçersiz format seçimi!');
            return;
        }
        
        const formatMap = {
            '1': 'html',
            '2': 'json',
            '3': 'xml'
        };
        const reportData = {
            stats: {
                toplam: parseInt($('#toplamGiris').text()) || 0,
                basarili: parseInt($('#basariliGiris').text()) || 0,
                reddedilen: parseInt($('#reddedilenGiris').text()) || 0
            },
            data: []
        };
        $('#raporTbody tr').each(function() {
            const cols = $(this).find('td');
            if (cols.length === 5) {
                const durum = cols.eq(3).text().trim();
                reportData.data.push({
                    tarih_saat: cols.eq(0).text(),
                    ad_soyad: cols.eq(1).text(),
                    kart_uid: cols.eq(2).text(),
                    sonuc: durum.includes('Başarılı') ? 'BASARILI' : 'REDDEDILDI',
                    turnike_id: cols.eq(4).text().replace('Turnike ', '')
                });
            }
        });
        const originalBtn = $('button:contains("Mail Gönder")');
        const originalText = originalBtn.html();
        originalBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Gönderiliyor...');
        $.ajax({
            url: '../api/mail_rapor_gonder.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                email: email,
                reportData: reportData,
                baslangic: $('#baslangicTarih').val(),
                bitis: $('#bitisTarih').val(),
                format: formatMap[format]
            }),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const formatText = formatMap[format] === 'html' ? 'HTML' : formatMap[format].toUpperCase();
                    alert('✅ ' + response.message + '\n📧 Mail gönderildi: ' + email + '\n📎 Format: ' + formatText);
                } else {
                    alert('❌ ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Mail gönderme hatası:', error);
                alert('❌ Mail gönderilemedi. Hata: ' + error);
            },
            complete: function() {
                originalBtn.prop('disabled', false).html(originalText);
            }
        });
    }
    </script>
</body>
</html>
