<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../includes/db_config.php';

$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

if (!$data || empty($data['tur_adi']) || empty($data['varsayilan_giris_hakki'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Tür adı ve giriş hakkı zorunludur'
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
    $checkQuery = "SELECT tur_id FROM uyelik_turleri WHERE tur_adi = ?";
    $checkStmt = sqlsrv_query($conn, $checkQuery, array($data['tur_adi']));
    
    if ($checkStmt === false) {
        throw new Exception('Kontrol sorgusu başarısız: ' . print_r(sqlsrv_errors(), true));
    }
    
    if (sqlsrv_fetch($checkStmt)) {
        sqlsrv_free_stmt($checkStmt);
        closeDBConnection($conn);
        echo json_encode([
            'success' => false,
            'message' => 'Bu isimde bir üyelik türü zaten var!'
        ]);
        exit;
    }
    sqlsrv_free_stmt($checkStmt);
    $insertQuery = "INSERT INTO uyelik_turleri (tur_adi, varsayilan_giris_hakki, ucret) VALUES (?, ?, ?)";
    $params = array(
        $data['tur_adi'],
        $data['varsayilan_giris_hakki'],
        isset($data['ucret']) ? $data['ucret'] : 0
    );
    
    $stmt = sqlsrv_query($conn, $insertQuery, $params);
    
    if ($stmt === false) {
        throw new Exception('INSERT başarısız: ' . print_r(sqlsrv_errors(), true));
    }

    sqlsrv_free_stmt($stmt);
    $idQuery = "SELECT @@IDENTITY AS tur_id";
    $idStmt = sqlsrv_query($conn, $idQuery);
    $idRow = sqlsrv_fetch_array($idStmt, SQLSRV_FETCH_ASSOC);
    $turId = $idRow['tur_id'];
    sqlsrv_free_stmt($idStmt);
    
    closeDBConnection($conn);

    echo json_encode([
        'success' => true,
        'message' => 'Üyelik türü başarıyla eklendi',
        'tur_id' => (int)$turId
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
