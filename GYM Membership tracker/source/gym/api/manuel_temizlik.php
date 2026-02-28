<?php
error_reporting(0);
ini_set('display_errors', 0);

session_start();
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode([
        'success' => false,
        'message' => 'Bu işlem için admin yetkisi gerekli!'
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
    $stmt = sqlsrv_query($conn, $query, array(), array("Scrollable" => SQLSRV_CURSOR_CLIENT_BUFFERED));
    
    if ($stmt === false) {
        $errors = sqlsrv_errors();
        if ($errors) {
            $hasRealError = false;
            foreach ($errors as $error) {
                if (isset($error['SQLSTATE']) && $error['SQLSTATE'] !== '01000') {
                    $hasRealError = true;
                    break;
                }
            }
            
            if ($hasRealError) {
                sqlsrv_close($conn);
                echo json_encode([
                    'success' => false,
                    'message' => 'Veritabanı hatası oluştu'
                ]);
                exit;
            }
        }
    }
    $result = [
        'silinen_kisi' => 0,
        'silinen_kart' => 0,
        'silinen_uyelik' => 0,
        'durum' => 'Bilinmiyor'
    ];
    
    if ($stmt && sqlsrv_fetch($stmt)) {
        $result['silinen_kisi'] = sqlsrv_get_field($stmt, 0) ?? 0;
        $result['silinen_kart'] = sqlsrv_get_field($stmt, 1) ?? 0;
        $result['silinen_uyelik'] = sqlsrv_get_field($stmt, 2) ?? 0;
        $result['durum'] = sqlsrv_get_field($stmt, 3) ?? 'Bilinmiyor';
    }
    
    if ($stmt) {
        sqlsrv_free_stmt($stmt);
    }
    sqlsrv_close($conn);
    $logFile = __DIR__ . '/../logs/manuel_temizlik.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    
    if ($result) {
        $logEntry = sprintf(
            "[%s] Manuel temizlik - Kullanıcı: %s, Silinen kişi: %d, Kart: %d, Üyelik: %d\n",
            date('Y-m-d H:i:s'),
            $_SESSION['username'] ?? 'admin',
            $result['silinen_kisi'],
            $result['silinen_kart'],
            $result['silinen_uyelik']
        );
        
        @file_put_contents($logFile, $logEntry, FILE_APPEND);
    }
    
    if ($result['silinen_kisi'] > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Veri temizliği başarıyla tamamlandı!',
            'silinen_kisi' => $result['silinen_kisi'],
            'silinen_kart' => $result['silinen_kart'],
            'silinen_uyelik' => $result['silinen_uyelik'],
            'silinen_log' => 0
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'Silinecek 1 yıldan eski kayıt bulunamadı.',
            'silinen_kisi' => 0,
            'silinen_kart' => 0,
            'silinen_uyelik' => 0,
            'silinen_log' => 0
        ], JSON_UNESCAPED_UNICODE);
    }
    
} catch (Exception $e) {
    if (isset($conn) && $conn) {
        @sqlsrv_close($conn);
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Hata oluştu'
    ], JSON_UNESCAPED_UNICODE);
}
?>
