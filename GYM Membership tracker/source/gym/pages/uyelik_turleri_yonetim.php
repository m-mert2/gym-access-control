<?php
require_once '../includes/db_config.php';
$username = 'admin';
$role = 'admin';

if ($role !== 'admin') {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Üyelik Türleri Yönetimi - ESOĞÜ Spor Salonu</title>
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
                    <h2><i class="fas fa-tags me-2"></i>Üyelik Türleri Yönetimi</h2>
                    <p class="text-muted">Üyelik paketlerini ekleyin, düzenleyin veya silin</p>
                </div>
            </div>

            <div class="row">
                
                <div class="col-lg-4 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Yeni Üyelik Türü</h5>
                        </div>
                        <div class="card-body">
                            <form id="yeniTurForm">
                                <div class="mb-3">
                                    <label class="form-label">Tür Adı <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="turAdi" name="tur_adi" required placeholder="Örn: Aylık, 3 Aylık">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Giriş Hakkı <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="girisHakki" name="varsayilan_giris_hakki" required min="1" placeholder="Örn: 30">
                                    <div class="form-text">Varsayılan giriş sayısı</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Ücret (₺)</label>
                                    <input type="number" class="form-control" id="ucret" name="ucret" step="0.01" min="0" placeholder="Örn: 150.00">
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-save me-2"></i>Kaydet
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

<div class="col-lg-8 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Mevcut Üyelik Türleri</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover" id="turlerTablosu">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Tür Adı</th>
                                            <th>Giriş Hakkı</th>
                                            <th>Ücret</th>
                                            <th class="text-center">İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody id="turlerListesi">
                                        <tr>
                                            <td colspan="5" class="text-center">
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
            </div>
        </div>
    </div>

<div class="modal fade" id="duzenleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Üyelik Türü Düzenle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="duzenleForm">
                        <input type="hidden" id="editTurId">
                        <div class="mb-3">
                            <label class="form-label">Tür Adı</label>
                            <input type="text" class="form-control" id="editTurAdi" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Giriş Hakkı</label>
                            <input type="number" class="form-control" id="editGirisHakki" required min="1">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ücret (₺)</label>
                            <input type="number" class="form-control" id="editUcret" step="0.01" min="0">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-warning" id="kaydetBtn">Güncelle</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/jquery-3.7.1.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    
    <script>
    $(document).ready(function() {
        let duzenleModal = new bootstrap.Modal(document.getElementById('duzenleModal'));
        function turleriYukle() {
            $.ajax({
                url: '../api/uyelik_turleri.php',
                type: 'GET',
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        let html = '';
                        response.data.forEach(function(tur) {
                            html += `
                                <tr>
                                    <td>${tur.tur_id}</td>
                                    <td><strong>${tur.tur_adi}</strong></td>
                                    <td><span class="badge bg-info">${tur.varsayilan_giris_hakki} giriş</span></td>
                                    <td><strong>${parseFloat(tur.ucret || 0).toFixed(2)} ₺</strong></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-warning duzenleBtn" data-id="${tur.tur_id}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger silBtn" data-id="${tur.tur_id}" data-adi="${tur.tur_adi}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                        $('#turlerListesi').html(html);
                    } else {
                        $('#turlerListesi').html('<tr><td colspan="5" class="text-center text-muted">Henüz üyelik türü eklenmemiş</td></tr>');
                    }
                },
                error: function() {
                    $('#turlerListesi').html('<tr><td colspan="5" class="text-center text-danger">Yükleme hatası!</td></tr>');
                }
            });
        }
        turleriYukle();
        $('#yeniTurForm').on('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                tur_adi: $('#turAdi').val().trim(),
                varsayilan_giris_hakki: parseInt($('#girisHakki').val()),
                ucret: parseFloat($('#ucret').val() || 0)
            };

            $.ajax({
                url: '../api/uyelik_turu_ekle.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(formData),
                success: function(response) {
                    if (response.success) {
                        alert('Üyelik türü başarıyla eklendi!');
                        $('#yeniTurForm')[0].reset();
                        turleriYukle();
                    } else {
                        alert('Hata: ' + response.message);
                    }
                },
                error: function() {
                    alert('Sunucu hatası oluştu!');
                }
            });
        });
        $(document).on('click', '.duzenleBtn', function() {
            const turId = $(this).data('id');
            $.ajax({
                url: '../api/uyelik_turleri.php',
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const tur = response.data.find(t => t.tur_id == turId);
                        if (tur) {
                            $('#editTurId').val(tur.tur_id);
                            $('#editTurAdi').val(tur.tur_adi);
                            $('#editGirisHakki').val(tur.varsayilan_giris_hakki);
                            $('#editUcret').val(parseFloat(tur.ucret || 0).toFixed(2));
                            duzenleModal.show();
                        }
                    }
                }
            });
        });
        $('#kaydetBtn').on('click', function() {
            const formData = {
                tur_id: $('#editTurId').val(),
                tur_adi: $('#editTurAdi').val().trim(),
                varsayilan_giris_hakki: parseInt($('#editGirisHakki').val()),
                ucret: parseFloat($('#editUcret').val() || 0)
            };

            $.ajax({
                url: '../api/uyelik_turu_guncelle.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(formData),
                success: function(response) {
                    if (response.success) {
                        alert('Üyelik türü başarıyla güncellendi!');
                        duzenleModal.hide();
                        turleriYukle();
                    } else {
                        alert('Hata: ' + response.message);
                    }
                },
                error: function() {
                    alert('Sunucu hatası oluştu!');
                }
            });
        });
        $(document).on('click', '.silBtn', function() {
            const turId = $(this).data('id');
            const turAdi = $(this).data('adi');
            
            if (confirm('"' + turAdi + '" üyelik türünü silmek istediğinizden emin misiniz?\n\nBu türe bağlı aktif üyelikler varsa silemezsiniz!')) {
                $.ajax({
                    url: '../api/uyelik_turu_sil.php',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ tur_id: turId }),
                    success: function(response) {
                        if (response.success) {
                            alert('Üyelik türü başarıyla silindi!');
                            turleriYukle();
                        } else {
                            alert('Hata: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Sunucu hatası oluştu!');
                    }
                });
            }
        });
    });
    </script>
</body>
</html>
