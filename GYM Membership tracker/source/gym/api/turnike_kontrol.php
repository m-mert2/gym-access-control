<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../includes/db_config.php';
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['kart_uid'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Kart UID gerekli',
        'hata_tipi' => 'HATA'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$kart_uid = trim($data['kart_uid']);
$turnike_id = isset($data['turnike_id']) ? intval($data['turnike_id']) : 1;

try {
    $conn = getDBConnection();
    $query = "{CALL sp_TurnikeGecisKontrol(?, ?)}";
    $params = [
        [$kart_uid, SQLSRV_PARAM_IN],
        [$turnike_id, SQLSRV_PARAM_IN]
    ];
    
    $stmt = sqlsrv_query($conn, $query, $params);
    
    if ($stmt === false) {
        throw new Exception('Stored procedure çağrılamadı: ' . print_r(sqlsrv_errors(), true));
    }
    $result = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    
    if (!$result) {
        throw new Exception('Sonuç alınamadı');
    }
    $durum = $result['Durum'] ?? 0;
    $mesaj = $result['Mesaj'] ?? 'Bilinmeyen hata';
    $kalan_hak = $result['KalanHak'] ?? 0;
    $basarili = ($durum == 1);
    
    if ($basarili) {
        $uyeQuery = "
            SELECT 
                kb.isim,
                kb.soyisim,
                ut.tur_adi,
                u.kalan_giris
            FROM kartlar k
            INNER JOIN kisisel_bilgiler kb ON k.kisisel_id = kb.kisisel_id
            INNER JOIN uyelikler u ON k.uyelik_id = u.uyelik_id
            INNER JOIN uyelik_turleri ut ON u.tur_id = ut.tur_id
            WHERE k.kart_uid = ?
        ";
        
        $uyeStmt = sqlsrv_query($conn, $uyeQuery, array($kart_uid));
        
        if ($uyeStmt && $uyeBilgi = sqlsrv_fetch_array($uyeStmt, SQLSRV_FETCH_ASSOC)) {
            sqlsrv_free_stmt($uyeStmt);
            sqlsrv_free_stmt($stmt);
            sqlsrv_close($conn);
            
            echo json_encode([
                'success' => true,
                'mesaj' => $mesaj,
                'uye_adi' => trim($uyeBilgi['isim'] . ' ' . $uyeBilgi['soyisim']),
                'kalan_giris' => (int)$uyeBilgi['kalan_giris'],
                'uyelik_turu' => $uyeBilgi['tur_adi'],
                'kart_uid' => $kart_uid
            ], JSON_UNESCAPED_UNICODE);
        } else {
            sqlsrv_free_stmt($stmt);
            sqlsrv_close($conn);
            
            echo json_encode([
                'success' => true,
                'mesaj' => $mesaj,
                'uye_adi' => 'Bilinmiyor',
                'kalan_giris' => $kalan_hak,
                'uyelik_turu' => 'Bilinmiyor',
                'kart_uid' => $kart_uid
            ], JSON_UNESCAPED_UNICODE);
        }
    } else {
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);
        
        echo json_encode([
            'success' => false,
            'mesaj' => $mesaj,
            'hata_tipi' => 'HATA',
            'kart_uid' => $kart_uid
        ], JSON_UNESCAPED_UNICODE);
    }
    
} catch (Exception $e) {
    if (isset($conn)) {
        @sqlsrv_close($conn);
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Sistem hatası oluştu',
        'hata_tipi' => 'HATA'
    ], JSON_UNESCAPED_UNICODE);
}
?>
