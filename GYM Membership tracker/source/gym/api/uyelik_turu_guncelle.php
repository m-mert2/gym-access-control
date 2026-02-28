<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../includes/db_config.php';

$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

if (!$data || empty($data['tur_id']) || empty($data['tur_adi']) || empty($data['varsayilan_giris_hakki'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Tür ID, tür adı ve giriş hakkı zorunludur'
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
    $updateQuery = "UPDATE uyelik_turleri SET tur_adi = ?, varsayilan_giris_hakki = ?, ucret = ? WHERE tur_id = ?";
    $params = array(
        $data['tur_adi'],
        $data['varsayilan_giris_hakki'],
        isset($data['ucret']) ? $data['ucret'] : 0,
        $data['tur_id']
    );
    
    $stmt = sqlsrv_query($conn, $updateQuery, $params);
    
    if ($stmt === false) {
        throw new Exception('UPDATE başarısız: ' . print_r(sqlsrv_errors(), true));
    }

    $rowsAffected = sqlsrv_rows_affected($stmt);
    sqlsrv_free_stmt($stmt);
    closeDBConnection($conn);

    if ($rowsAffected > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Üyelik türü başarıyla güncellendi'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Güncellenecek kayıt bulunamadı'
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
