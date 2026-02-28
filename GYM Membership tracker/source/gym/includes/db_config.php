<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');
define('DB_SERVER', 'localhost');
define('DB_NAME', 'OGUGYMDB');
define('DB_USERNAME', '');
define('DB_PASSWORD', '');
define('DB_CHARSET', 'UTF-8');
define('DB_TIMEOUT', 30);

function getDBConnection() {
    if (DB_PASSWORD === '') {
        logError('Veritabanı şifresi ayarlanmamış! includes/db_config.php dosyasını düzenleyin.');
        return false;
    }
    $connectionInfo = array(
        "Database" => DB_NAME,
        "UID" => DB_USERNAME,
        "PWD" => DB_PASSWORD,
        "CharacterSet" => DB_CHARSET,
        "LoginTimeout" => DB_TIMEOUT,
        "ReturnDatesAsStrings" => true,
        "Encrypt" => false,
        "TrustServerCertificate" => true
    );
    $conn = sqlsrv_connect(DB_SERVER, $connectionInfo);
    if ($conn === false) {
        $errors = sqlsrv_errors(SQLSRV_ERR_ALL);
        $errorMessage = "Veritabanı bağlantısı başarısız!\n";
        
        if ($errors) {
            foreach ($errors as $error) {
                $errorMessage .= "SQLSTATE: {$error['SQLSTATE']}\n";
                $errorMessage .= "Code: {$error['code']}\n";
                $errorMessage .= "Message: {$error['message']}\n";
            }
        }
        
        logError($errorMessage);
        return false;
    }

    return $conn;
}

function closeDBConnection($conn) {
    if ($conn !== false && $conn !== null) {
        sqlsrv_close($conn);
    }
}

function executeQuery($conn, $query, $params = array()) {
    $stmt = sqlsrv_query($conn, $query, $params);
    
    if ($stmt === false) {
        $errors = sqlsrv_errors(SQLSRV_ERR_ALL);
        $errorMessage = "SQL sorgu hatası: $query\n";
        
        if ($errors) {
            foreach ($errors as $error) {
                $errorMessage .= "SQLSTATE: {$error['SQLSTATE']}\n";
                $errorMessage .= "Message: {$error['message']}\n";
            }
        }
        
        logError($errorMessage);
        return false;
    }
    
    return $stmt;
}

function executeProcedure($conn, $procedureName, $params = array()) {
    $paramPlaceholders = array_fill(0, count($params), '?');
    $sql = "{CALL $procedureName(" . implode(',', $paramPlaceholders) . ")}";
    
    $stmt = sqlsrv_query($conn, $sql, $params);
    
    if ($stmt === false) {
        $errors = sqlsrv_errors(SQLSRV_ERR_ALL);
        $errorMessage = "Stored Procedure hatası: $procedureName\n";
        
        if ($errors) {
            foreach ($errors as $error) {
                $errorMessage .= "SQLSTATE: {$error['SQLSTATE']}\n";
                $errorMessage .= "Message: {$error['message']}\n";
            }
        }
        
        logError($errorMessage);
        return false;
    }
    
    return $stmt;
}

function fetchRow($conn, $query, $params = array()) {
    $stmt = executeQuery($conn, $query, $params);
    
    if ($stmt === false) {
        return false;
    }
    
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    sqlsrv_free_stmt($stmt);
    
    return $row !== false ? $row : false;
}

function fetchAll($conn, $query, $params = array()) {
    $stmt = executeQuery($conn, $query, $params);
    
    if ($stmt === false) {
        return array();
    }
    
    $results = array();
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $results[] = $row;
    }
    
    sqlsrv_free_stmt($stmt);
    
    return $results;
}

function logError($message) {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/db_errors.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

function logInfo($message) {
    $logDir = __DIR__ . '/../logs';
    
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/system.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] INFO: $message\n";
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function sanitizeForSQL($value) {
    return str_replace("'", "''", $value);
}

function testDBConnection() {
    $results = array(
        'success' => false,
        'message' => '',
        'details' => array()
    );
    if (!extension_loaded('sqlsrv')) {
        $results['message'] = 'SQLSRV PHP extension yüklü değil!';
        $results['details'][] = 'php.ini dosyasında extension=php_sqlsrv aktif olmalı';
        return $results;
    }

    $results['details'][] = '✓ SQLSRV extension yüklü';
    if (extension_loaded('pdo_sqlsrv')) {
        $results['details'][] = '✓ PDO SQLSRV extension yüklü';
    }
    $conn = getDBConnection();
    
    if ($conn === false) {
        $results['message'] = 'Veritabanı bağlantısı başarısız!';
        $results['details'][] = '✗ Bağlantı kurulamadı';
        $errors = sqlsrv_errors(SQLSRV_ERR_ALL);
        if ($errors) {
            foreach ($errors as $error) {
                $results['details'][] = "HATA: {$error['message']}";
            }
        }
        
        return $results;
    }

    $results['details'][] = '✓ Veritabanı bağlantısı başarılı';
    $serverInfo = sqlsrv_server_info($conn);
    if ($serverInfo) {
        $results['details'][] = "SQL Server Versiyon: {$serverInfo['SQLServerVersion']}";
        $results['details'][] = "Veritabanı: " . DB_NAME;
    }
    $testQuery = "SELECT GETDATE() as CurrentTime";
    $stmt = sqlsrv_query($conn, $testQuery);
    
    if ($stmt !== false) {
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        if ($row) {
            $results['details'][] = "✓ Sorgu testi başarılı";
            $currentTime = $row['CurrentTime'];
            if (is_object($currentTime) && method_exists($currentTime, 'format')) {
                $timeStr = $currentTime->format('Y-m-d H:i:s');
            } else {
                $timeStr = (string)$currentTime;
            }
            $results['details'][] = "Sunucu Zamanı: " . $timeStr;
        }
        sqlsrv_free_stmt($stmt);
    }

    closeDBConnection($conn);

    $results['success'] = true;
    $results['message'] = 'Veritabanı bağlantısı tamam!';

    return $results;
}

function startSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_secure', 0);
        
        session_start();
    }
}

function isLoggedIn() {
    startSecureSession();
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

function isAdmin() {
    startSecureSession();
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function hasPermission($yetki) {
    startSecureSession();
    if (isAdmin()) {
        return true;
    }
    if (!isLoggedIn()) {
        return false;
    }
    if (!isset($_SESSION['permissions'])) {
        loadUserPermissions();
    }
    
    return isset($_SESSION['permissions']) && in_array($yetki, $_SESSION['permissions']);
}

function loadUserPermissions() {
    if (!isset($_SESSION['user_id'])) {
        return;
    }
    
    $conn = getDBConnection();
    if (!$conn) {
        return;
    }
    
    $query = "SELECT y.yetki_adi 
              FROM kullanici_yetkileri ky
              INNER JOIN yetkiler y ON ky.yetki_id = y.yetki_id
              WHERE ky.user_id = ?";
    
    $stmt = sqlsrv_query($conn, $query, array($_SESSION['user_id']));
    
    if ($stmt !== false) {
        $_SESSION['permissions'] = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $_SESSION['permissions'][] = $row['yetki_adi'];
        }
        sqlsrv_free_stmt($stmt);
    }
    
    closeDBConnection($conn);
}

function requirePermission($yetki, $redirectUrl = '../index.php') {
    if (!hasPermission($yetki)) {
        header('Location: ' . $redirectUrl);
        exit;
    }
}

function getUserRole() {
    startSecureSession();
    return $_SESSION['role'] ?? null;
}

?>
