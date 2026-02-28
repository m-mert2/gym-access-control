<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../includes/db_config.php';
$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

if (!$data) {
    echo json_encode([
        'success' => false,
        'message' => 'Geçersiz veri formatı'
    ]);
    exit;
}
if (empty($data['kisisel_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Kişisel ID gerekli'
    ]);
    exit;
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
    $checkKisiQuery = "SELECT kisisel_id, isim, soyisim FROM kisisel_bilgiler WHERE kisisel_id = ?";
    $checkKisiStmt = sqlsrv_query($conn, $checkKisiQuery, array($data['kisisel_id']));
    
    if ($checkKisiStmt === false) {
        throw new Exception('Kişi kontrol sorgusu başarısız: ' . print_r(sqlsrv_errors(), true));
    }
    
    $kisi = sqlsrv_fetch_array($checkKisiStmt, SQLSRV_FETCH_ASSOC);
    if (!$kisi) {
        sqlsrv_free_stmt($checkKisiStmt);
        closeDBConnection($conn);
        echo json_encode([
            'success' => false,
            'message' => 'Kişi bulunamadı!'
        ]);
        exit;
    }
    sqlsrv_free_stmt($checkKisiStmt);
    $checkKartQuery = "SELECT kart_id, kart_uid FROM kartlar WHERE kisisel_id = ?";
    $checkKartStmt = sqlsrv_query($conn, $checkKartQuery, array($data['kisisel_id']));
    
    if ($checkKartStmt === false) {
        throw new Exception('Kart kontrol sorgusu başarısız: ' . print_r(sqlsrv_errors(), true));
    }
    
    if (sqlsrv_fetch($checkKartStmt)) {
        sqlsrv_free_stmt($checkKartStmt);
        closeDBConnection($conn);
        echo json_encode([
            'success' => false,
            'message' => 'Bu kişiye ait kart zaten tanımlı!'
        ]);
        exit;
    }
    sqlsrv_free_stmt($checkKartStmt);
    $kartUid = 'DEMO-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
    $uniqueCheckQuery = "SELECT kart_id FROM kartlar WHERE kart_uid = ?";
    $uniqueCheckStmt = sqlsrv_query($conn, $uniqueCheckQuery, array($kartUid));
    
    if ($uniqueCheckStmt === false) {
        throw new Exception('UID kontrol sorgusu başarısız: ' . print_r(sqlsrv_errors(), true));
    }
    while (sqlsrv_fetch($uniqueCheckStmt)) {
        sqlsrv_free_stmt($uniqueCheckStmt);
        $kartUid = 'DEMO-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
        $uniqueCheckStmt = sqlsrv_query($conn, $uniqueCheckQuery, array($kartUid));
    }
    sqlsrv_free_stmt($uniqueCheckStmt);
    $insertKart = "INSERT INTO kartlar (kart_uid, kisisel_id, aktif) VALUES (?, ?, 1)";
    $stmtKart = sqlsrv_query($conn, $insertKart, array($kartUid, $data['kisisel_id']));
    
    if ($stmtKart === false) {
        throw new Exception('Kart INSERT başarısız: ' . print_r(sqlsrv_errors(), true));
    }

    sqlsrv_free_stmt($stmtKart);
    $idQuery = "SELECT @@IDENTITY AS kart_id";
    $idStmt = sqlsrv_query($conn, $idQuery);
    
    if ($idStmt === false) {
        throw new Exception('ID sorgusu başarısız: ' . print_r(sqlsrv_errors(), true));
    }
    
    $idRow = sqlsrv_fetch_array($idStmt, SQLSRV_FETCH_ASSOC);
    $kartId = $idRow['kart_id'];
    sqlsrv_free_stmt($idStmt);
    closeDBConnection($conn);

    echo json_encode([
        'success' => true,
        'message' => 'Kart başarıyla tanımlandı',
        'kart_id' => (int)$kartId,
        'kart_uid' => $kartUid,
        'kisi_adi' => $kisi['isim'] . ' ' . $kisi['soyisim']
    ]);

} catch (Exception $e) {
    if ($conn) {
        closeDBConnection($conn);
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Hata: ' . $e->getMessage()
    ]);
}
?>
