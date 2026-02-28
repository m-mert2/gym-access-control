<?php

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../includes/db_config.php';
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode([
        'success' => false,
        'message' => 'Geçersiz veri formatı'
    ]);
    exit;
}
$required = ['isim', 'soyisim', 'kart_uid', 'tur_id', 'baslangic_tarihi'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        echo json_encode([
            'success' => false,
            'message' => "Zorunlu alan eksik: $field"
        ]);
        exit;
    }
}

$conn = getDBConnection();
if (!$conn) {
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı bağlantısı başarısız'
    ]);
    exit;
}

try {
    sqlsrv_begin_transaction($conn);
    $kart_uid = sanitizeInput($data['kart_uid']);
    $query = "SELECT kart_id FROM kartlar WHERE kart_uid = ?";
    $stmt = sqlsrv_prepare($conn, $query, array(&$kart_uid));
    
    if (!$stmt || !sqlsrv_execute($stmt)) {
        throw new Exception('Kart kontrolü başarısız');
    }
    
    $kart_id = null;
    if (sqlsrv_fetch($stmt)) {
        $kart_id = sqlsrv_get_field($stmt, 0);
    }
    sqlsrv_free_stmt($stmt);
    $query = "INSERT INTO kisisel_bilgiler (isim, soyisim, cinsiyet, tc_no, email, telefon, adres, bolum, ogrenci_no, kayit_tarihi)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, GETDATE())";
    
    $params = array(
        sanitizeInput($data['isim']),
        sanitizeInput($data['soyisim']),
        isset($data['cinsiyet']) ? sanitizeInput($data['cinsiyet']) : null,
        isset($data['tc_no']) ? sanitizeInput($data['tc_no']) : null,
        isset($data['email']) ? sanitizeInput($data['email']) : null,
        isset($data['telefon']) ? sanitizeInput($data['telefon']) : null,
        isset($data['adres']) ? sanitizeInput($data['adres']) : null,
        isset($data['bolum']) ? sanitizeInput($data['bolum']) : null,
        isset($data['ogrenci_no']) ? sanitizeInput($data['ogrenci_no']) : null
    );
    
    $stmt = sqlsrv_prepare($conn, $query, $params);
    if (!$stmt || !sqlsrv_execute($stmt)) {
        throw new Exception('Kişisel bilgiler eklenemedi');
    }
    sqlsrv_free_stmt($stmt);
    $query = "SELECT @@IDENTITY AS kisisel_id";
    $stmt = sqlsrv_query($conn, $query);
    sqlsrv_fetch($stmt);
    $kisisel_id = sqlsrv_get_field($stmt, 0);
    sqlsrv_free_stmt($stmt);
    $tur_id = intval($data['tur_id']);
    $query = "SELECT varsayilan_giris_hakki FROM uyelik_turleri WHERE tur_id = ?";
    $stmt = sqlsrv_prepare($conn, $query, array(&$tur_id));
    
    if (!$stmt || !sqlsrv_execute($stmt)) {
        throw new Exception('Üyelik türü bilgisi alınamadı');
    }
    
    if (!sqlsrv_fetch($stmt)) {
        sqlsrv_free_stmt($stmt);
        throw new Exception('Geçersiz üyelik türü');
    }
    
    $giris_hakki = sqlsrv_get_field($stmt, 0);
    sqlsrv_free_stmt($stmt);
    $baslangic = sanitizeInput($data['baslangic_tarihi']);
    $gecerlilik_gun_map = [
        1 => 1,
        2 => 30,
        3 => 90,
        4 => 180,
        5 => 365,
        6 => 3650,
        7 => 3650
    ];
    
    $gecerlilik_gun = isset($gecerlilik_gun_map[$tur_id]) ? $gecerlilik_gun_map[$tur_id] : 30;
    $bitis_tarihi = date('Y-m-d', strtotime($baslangic . " +{$gecerlilik_gun} days"));
    $query = "INSERT INTO uyelikler (kisisel_id, tur_id, baslangic_tarihi, bitis_tarihi, kalan_giris, durum)
              VALUES (?, ?, ?, ?, ?, 1)";
    
    $params = array($kisisel_id, $tur_id, $baslangic, $bitis_tarihi, $giris_hakki);
    $stmt = sqlsrv_prepare($conn, $query, $params);
    
    if (!$stmt || !sqlsrv_execute($stmt)) {
        $errors = sqlsrv_errors();
        $errorMsg = 'Üyelik kaydı oluşturulamadı';
        if ($errors) {
            $errorMsg .= ': ' . $errors[0]['message'];
        }
        throw new Exception($errorMsg);
    }
    sqlsrv_free_stmt($stmt);
    $query = "SELECT @@IDENTITY AS uyelik_id";
    $stmt = sqlsrv_query($conn, $query);
    sqlsrv_fetch($stmt);
    $uyelik_id = sqlsrv_get_field($stmt, 0);
    sqlsrv_free_stmt($stmt);
    if ($kart_id) {
        $query = "UPDATE kartlar SET kisisel_id = ?, uyelik_id = ?, aktif = 1, son_kullanim = GETDATE() WHERE kart_id = ?";
        $stmt = sqlsrv_prepare($conn, $query, array($kisisel_id, $uyelik_id, $kart_id));
    } else {
        $query = "INSERT INTO kartlar (kisisel_id, uyelik_id, kart_uid, aktif, son_kullanim)
                  VALUES (?, ?, ?, 1, GETDATE())";
        $stmt = sqlsrv_prepare($conn, $query, array($kisisel_id, $uyelik_id, $kart_uid));
    }
    
    if (!$stmt || !sqlsrv_execute($stmt)) {
        throw new Exception('Kart kaydı oluşturulamadı');
    }
    sqlsrv_free_stmt($stmt);
    
    if (!$kart_id) {
        $query = "SELECT @@IDENTITY AS kart_id";
        $stmt = sqlsrv_query($conn, $query);
        sqlsrv_fetch($stmt);
        $kart_id = sqlsrv_get_field($stmt, 0);
        sqlsrv_free_stmt($stmt);
    }
    sqlsrv_commit($conn);
    
    echo json_encode([
        'success' => true,
        'message' => 'Üye başarıyla kaydedildi',
        'data' => [
            'kisisel_id' => $kisisel_id,
            'uyelik_id' => $uyelik_id,
            'kart_id' => $kart_id,
            'bitis_tarihi' => $bitis_tarihi,
            'kalan_giris' => $giris_hakki
        ]
    ]);
    
} catch (Exception $e) {
    if (sqlsrv_begin_transaction($conn)) {
        sqlsrv_rollback($conn);
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

sqlsrv_close($conn);
?>
