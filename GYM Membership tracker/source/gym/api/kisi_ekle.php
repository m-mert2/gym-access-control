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
if (empty($data['isim']) || empty($data['soyisim']) || empty($data['tc_no']) || empty($data['uye_grubu'])) {
    echo json_encode([
        'success' => false,
        'message' => 'İsim, Soyisim, TC Kimlik No ve Üye Grubu alanları zorunludur'
    ]);
    exit;
}
$gecerli_gruplar = ['Öğrenci', 'AkademikPersonel', 'İdari Personel', 'Personel Yakını', 'Emekli Personel', 'Kamu', 'Dış', 'M.D'];
if (!in_array($data['uye_grubu'], $gecerli_gruplar)) {
    echo json_encode([
        'success' => false,
        'message' => 'Geçersiz üye grubu'
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
    
    $checkQuery = "SELECT kisisel_id FROM kisisel_bilgiler WHERE tc_no = ?";
    $checkStmt = sqlsrv_query($conn, $checkQuery, array($data['tc_no']));
    
    if ($checkStmt === false) {
        throw new Exception('TC No kontrol sorgusu başarısız: ' . print_r(sqlsrv_errors(), true));
    }
    
    if (sqlsrv_fetch($checkStmt)) {
        sqlsrv_free_stmt($checkStmt);
        closeDBConnection($conn);
        echo json_encode([
            'success' => false,
            'message' => 'Bu TC Kimlik No zaten kayıtlı!'
        ]);
        exit;
    }
    sqlsrv_free_stmt($checkStmt);
    $query = "INSERT INTO kisisel_bilgiler (isim, soyisim, cinsiyet, tc_no, email, telefon, adres, bolum, ogrenci_no, uye_grubu, kayit_tarihi) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, GETDATE())";
    
    $params = array(
        $data['isim'],
        $data['soyisim'],
        isset($data['cinsiyet']) ? $data['cinsiyet'] : null,
        $data['tc_no'],
        isset($data['email']) ? $data['email'] : null,
        isset($data['telefon']) ? $data['telefon'] : null,
        isset($data['adres']) ? $data['adres'] : null,
        isset($data['bolum']) ? $data['bolum'] : null,
        isset($data['ogrenci_no']) ? $data['ogrenci_no'] : null,
        $data['uye_grubu']
    );

    $stmt = sqlsrv_query($conn, $query, $params);
    
    if ($stmt === false) {
        throw new Exception('INSERT sorgusu başarısız: ' . print_r(sqlsrv_errors(), true));
    }

    sqlsrv_free_stmt($stmt);
    $idQuery = "SELECT @@IDENTITY AS kisisel_id";
    $idStmt = sqlsrv_query($conn, $idQuery);
    
    if ($idStmt === false) {
        throw new Exception('ID sorgusu başarısız: ' . print_r(sqlsrv_errors(), true));
    }
    
    $idRow = sqlsrv_fetch_array($idStmt, SQLSRV_FETCH_ASSOC);
    $kisiselId = $idRow['kisisel_id'];
    
    sqlsrv_free_stmt($idStmt);
    closeDBConnection($conn);

    echo json_encode([
        'success' => true,
        'message' => 'Kişi başarıyla kaydedildi',
        'kisisel_id' => (int)$kisiselId
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
