<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../includes/db_config.php';

$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

if (!$data || empty($data['tur_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Tür ID gerekli'
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
    $checkQuery = "SELECT COUNT(*) as sayi FROM uyelikler WHERE tur_id = ? AND durum = 1";
    $checkStmt = sqlsrv_query($conn, $checkQuery, array($data['tur_id']));
    
    if ($checkStmt === false) {
        throw new Exception('Kontrol sorgusu başarısız: ' . print_r(sqlsrv_errors(), true));
    }
    
    $row = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);
    $aktifUyelikSayisi = $row['sayi'];
    sqlsrv_free_stmt($checkStmt);
    
    if ($aktifUyelikSayisi > 0) {
        closeDBConnection($conn);
        echo json_encode([
            'success' => false,
            'message' => 'Bu üyelik türüne bağlı ' . $aktifUyelikSayisi . ' aktif üyelik var! Önce bu üyelikleri sonlandırın.'
        ]);
        exit;
    }
    $deleteQuery = "DELETE FROM uyelik_turleri WHERE tur_id = ?";
    $stmt = sqlsrv_query($conn, $deleteQuery, array($data['tur_id']));
    
    if ($stmt === false) {
        throw new Exception('DELETE başarısız: ' . print_r(sqlsrv_errors(), true));
    }

    $rowsAffected = sqlsrv_rows_affected($stmt);
    sqlsrv_free_stmt($stmt);
    closeDBConnection($conn);

    if ($rowsAffected > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Üyelik türü başarıyla silindi'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Silinecek kayıt bulunamadı'
        ]);
    }

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
