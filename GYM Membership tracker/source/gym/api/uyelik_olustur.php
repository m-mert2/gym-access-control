<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../includes/db_config.php';
startSecureSession();
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'Oturum açmanız gerekiyor'
    ]);
    exit;
}
$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

if (!$data) {
    echo json_encode([
        'success' => false,
        'message' => 'Geçersiz veri formatı'
    ]);
    exit;
}
if (empty($data['kart_id']) || empty($data['kisisel_id']) || empty($data['tur_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Eksik parametreler'
    ]);
    exit;
}
if (isset($data['kalan_giris']) && $data['kalan_giris'] == -1) {
    if (!hasPermission('uyelik.sinirsiz')) {
        echo json_encode([
            'success' => false,
            'message' => 'Sınırsız üyelik hakkı tanımlama yetkiniz yok! (Sadece yönetici)'
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
    $checkQuery = "SELECT uyelik_id FROM uyelikler WHERE kisisel_id = ? AND durum = 1";
    $checkStmt = sqlsrv_query($conn, $checkQuery, array($data['kisisel_id']));
    
    if ($checkStmt === false) {
        throw new Exception('Üyelik kontrol sorgusu başarısız: ' . print_r(sqlsrv_errors(), true));
    }
    
    if (sqlsrv_fetch($checkStmt)) {
        sqlsrv_free_stmt($checkStmt);
        closeDBConnection($conn);
        echo json_encode([
            'success' => false,
            'message' => 'Bu kişinin zaten aktif bir üyeliği var!'
        ]);
        exit;
    }
    sqlsrv_free_stmt($checkStmt);
    $insertUyelik = "INSERT INTO uyelikler (kisisel_id, tur_id, baslangic_tarihi, bitis_tarihi, kalan_giris, durum) 
                     VALUES (?, ?, ?, ?, ?, 1)";
    
    $paramsUyelik = array(
        $data['kisisel_id'],
        $data['tur_id'],
        $data['baslangic_tarihi'],
        $data['bitis_tarihi'],
        $data['kalan_giris']
    );

    $stmtUyelik = sqlsrv_query($conn, $insertUyelik, $paramsUyelik);
    
    if ($stmtUyelik === false) {
        throw new Exception('Üyelik INSERT başarısız: ' . print_r(sqlsrv_errors(), true));
    }

    sqlsrv_free_stmt($stmtUyelik);
    $idQuery = "SELECT @@IDENTITY AS uyelik_id";
    $idStmt = sqlsrv_query($conn, $idQuery);
    
    if ($idStmt === false) {
        throw new Exception('ID sorgusu başarısız: ' . print_r(sqlsrv_errors(), true));
    }
    
    $idRow = sqlsrv_fetch_array($idStmt, SQLSRV_FETCH_ASSOC);
    $uyelikId = $idRow['uyelik_id'];
    sqlsrv_free_stmt($idStmt);
    $updateKart = "UPDATE kartlar SET uyelik_id = ? WHERE kart_id = ?";
    $stmtKart = sqlsrv_query($conn, $updateKart, array($uyelikId, $data['kart_id']));
    
    if ($stmtKart === false) {
        throw new Exception('Kart UPDATE başarısız: ' . print_r(sqlsrv_errors(), true));
    }

    sqlsrv_free_stmt($stmtKart);
    closeDBConnection($conn);

    echo json_encode([
        'success' => true,
        'message' => 'Üyelik başarıyla oluşturuldu',
        'uyelik_id' => (int)$uyelikId
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
