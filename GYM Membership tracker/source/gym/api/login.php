<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
require_once '../includes/db_config.php';
startSecureSession();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Sadece POST isteklerine izin verilir'
    ]);
    exit;
}
$username = isset($_POST['username']) ? sanitizeInput($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$rememberMe = isset($_POST['remember_me']) ? (bool)$_POST['remember_me'] : false;
if (empty($username) || empty($password)) {
    echo json_encode([
        'success' => false,
        'message' => 'Kullanıcı adı ve şifre gereklidir'
    ]);
    exit;
}

try {
    $conn = getDBConnection();
    
    if ($conn === false) {
        throw new Exception('Veritabanı bağlantısı başarısız');
    }
    $query = "SELECT user_id, username, password_hash, rol, aktif 
              FROM kullanicilar 
              WHERE username = ? AND aktif = 1";
    
    $params = [$username];
    $stmt = sqlsrv_query($conn, $query, $params);
    
    if ($stmt === false) {
        if (isDevelopmentMode()) {
            handleDevelopmentLogin($username, $password, $rememberMe);
        } else {
            throw new Exception('Kullanıcı sorgulanamadı');
        }
    } else {
        $user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        sqlsrv_free_stmt($stmt);
        
        if ($user) {
            if ($password === $user['password_hash']) {
                loginSuccess($user, $rememberMe, $conn);
            } else {
                loginFailed($username, 'Yanlış şifre', $conn);
            }
        } else {
            loginFailed($username, 'Kullanıcı bulunamadı', $conn);
        }
    }
    
    closeDBConnection($conn);
    
} catch (Exception $e) {
    logError('Login hatası: ' . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Bir hata oluştu. Lütfen sistem yöneticisi ile iletişime geçin.'
    ]);
}

function loginSuccess($user, $rememberMe, $conn) {
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['rol'];
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();
    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
    loadUserPermissions();
    if ($rememberMe) {
        $cookieToken = bin2hex(random_bytes(32));
        setcookie('remember_token', $cookieToken, time() + (86400 * 30), '/', '', false, true);
    }
    logLoginAttempt($user['user_id'], $user['username'], true, $conn);
    echo json_encode([
        'success' => true,
        'message' => 'Giriş başarılı',
        'user' => [
            'id' => $user['user_id'],
            'username' => $user['username'],
            'role' => $user['rol']
        ],
        'redirect' => $user['rol'] === 'vezne' ? 'uyelik_olustur.php' : 'dashboard.php'
    ]);
}

function loginFailed($username, $reason, $conn) {
    logLoginAttempt(null, $username, false, $conn, $reason);
    echo json_encode([
        'success' => false,
        'message' => 'Kullanıcı adı veya şifre hatalı'
    ]);
}

function logLoginAttempt($userId, $username, $success, $conn, $reason = '') {
    $query = "INSERT INTO giris_loglari 
              (kullanici_id, kullanici_adi, basarili, ip_adresi, sebep, tarih) 
              VALUES (?, ?, ?, ?, ?, GETDATE())";
    
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    $params = [
        $userId,
        $username,
        $success ? 1 : 0,
        $ipAddress,
        $reason
    ];
    
    $stmt = sqlsrv_query($conn, $query, $params);
    
    if ($stmt === false) {
        logError('Giriş log kaydedilemedi: ' . print_r(sqlsrv_errors(), true));
    } else {
        sqlsrv_free_stmt($stmt);
    }
}

function isDevelopmentMode() {
    return !defined('PRODUCTION_MODE') || PRODUCTION_MODE === false;
}

function handleDevelopmentLogin($username, $password, $rememberMe) {
    $testUsers = [
        'admin' => [
            'kullanici_id' => 1,
            'kullanici_adi' => 'admin',
            'sifre' => 'admin123',
            'rol' => 'admin'
        ],
        'personel' => [
            'kullanici_id' => 2,
            'kullanici_adi' => 'personel',
            'sifre' => 'personel123',
            'rol' => 'personel'
        ],
        'vezne' => [
            'kullanici_id' => 3,
            'kullanici_adi' => 'vezne',
            'sifre' => 'vezne123',
            'rol' => 'vezne'
        ]
    ];
    
    if (isset($testUsers[$username]) && $testUsers[$username]['sifre'] === $password) {
        $user = $testUsers[$username];
        $_SESSION['user_id'] = $user['kullanici_id'];
        $_SESSION['username'] = $user['kullanici_adi'];
        $_SESSION['role'] = $user['rol'];
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['dev_mode'] = true;
        
        logInfo("Geliştirme modu girişi: $username");
        
        echo json_encode([
            'success' => true,
            'message' => 'Giriş başarılı (Geliştirme Modu)',
            'user' => [
                'id' => $user['kullanici_id'],
                'username' => $user['kullanici_adi'],
                'role' => $user['rol']
            ],
            'redirect' => $user['rol'] === 'vezne' ? 'vezne_uyelik.php' : 'dashboard.php',
            'warning' => 'Geliştirme modu aktif - Veritabanı tabloları oluşturulmalı!'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Kullanıcı adı veya şifre hatalı',
            'hint' => 'Test için: admin/admin123 veya personel/personel123'
        ]);
    }
    exit;
}

?>
