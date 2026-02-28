
<?php

header('Content-Type: application/json; charset=utf-8');

define('CRON_KEY', 'ogugym_2025_secure_key');

if (!isset($_GET['key']) || $_GET['key'] !== CRON_KEY) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Yetkisiz erişim! Doğru key parametresi gerekli.'
    ]);
    exit;
}

require_once '../includes/db_config.php';

$conn = getDBConnection();
if (!$conn) {
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı bağlantısı başarısız'
    ]);
    exit;
}

try {
    $query = "EXEC sp_YillikVeriTemizligi";
    $result = sqlsrv_query($conn, $query);
    
    if ($result === false) {
        throw new Exception('Stored procedure çalıştırılamadı: ' . print_r(sqlsrv_errors(), true));
    }
    
    $messages = [];
    do {
        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $messages[] = $row;
        }
    } while (sqlsrv_next_result($result));
    
    sqlsrv_free_stmt($result);
    sqlsrv_close($conn);
    
    $logFile = __DIR__ . '/../logs/veri_temizligi.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logEntry = sprintf(
        "[%s] Veri temizliği çalıştırıldı - IP: %s\n",
        date('Y-m-d H:i:s'),
        $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    );
    
    file_put_contents($logFile, $logEntry, FILE_APPEND);
    
    echo json_encode([
        'success' => true,
        'message' => 'Veri temizliği başarıyla tamamlandı',
        'timestamp' => date('Y-m-d H:i:s'),
        'details' => $messages
    ]);
    
} catch (Exception $e) {
    if ($conn) {
        sqlsrv_close($conn);
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Hata: ' . $e->getMessage()
    ]);
}

?>
