<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../includes/db_config.php';
startSecureSession();

$conn = getDBConnection();
if (!$conn) {
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı bağlantısı başarısız'
    ]);
    exit;
}

try {
    $query = "SELECT tur_id, tur_adi, varsayilan_giris_hakki, ucret FROM uyelik_turleri ORDER BY tur_id";
    $stmt = sqlsrv_query($conn, $query);
    
    if ($stmt === false) {
        throw new Exception('Sorgu başarısız: ' . print_r(sqlsrv_errors(), true));
    }

    $turler = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        if (strtolower($row['tur_adi']) === 'sınırsız' || strtolower($row['tur_adi']) === 'sinirsiz') {
            if (!hasPermission('uyelik.sinirsiz')) {
                continue;
            }
        }
        $turler[] = $row;
    }

    sqlsrv_free_stmt($stmt);
    closeDBConnection($conn);

    echo json_encode([
        'success' => true,
        'data' => $turler,
        'turler' => $turler
    ]);

} catch (Exception $e) {
    if ($conn) {
        closeDBConnection($conn);
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
