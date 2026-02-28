<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../includes/db_config.php';

try {
    $conn = getDBConnection();
    $query = "
        SELECT 
            k.kart_id,
            k.kart_uid,
            k.aktif as kart_aktif,
            kb.kisisel_id,
            kb.isim,
            kb.soyisim,
            kb.tc_no,
            kb.email,
            u.uyelik_id,
            u.kalan_giris,
            u.durum as uyelik_durum,
            u.baslangic_tarihi,
            u.bitis_tarihi,
            ut.tur_adi
        FROM kartlar k
        INNER JOIN kisisel_bilgiler kb ON k.kisisel_id = kb.kisisel_id
        LEFT JOIN uyelikler u ON k.uyelik_id = u.uyelik_id
        LEFT JOIN uyelik_turleri ut ON u.tur_id = ut.tur_id
        WHERE k.aktif = 1 AND kb.kisisel_id IS NOT NULL
        ORDER BY 
            CASE WHEN u.uyelik_id IS NULL THEN 0 ELSE 1 END,
            kb.isim, kb.soyisim
    ";
    
    $stmt = sqlsrv_query($conn, $query);
    
    if ($stmt === false) {
        throw new Exception('Sorgu hatası: ' . print_r(sqlsrv_errors(), true));
    }
    
    $kartlar = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $baslangic = null;
        $bitis = null;
        
        if (isset($row['baslangic_tarihi']) && $row['baslangic_tarihi']) {
            if (is_object($row['baslangic_tarihi']) && method_exists($row['baslangic_tarihi'], 'format')) {
                $baslangic = $row['baslangic_tarihi']->format('Y-m-d');
            } else {
                $baslangic = $row['baslangic_tarihi'];
            }
        }
        
        if (isset($row['bitis_tarihi']) && $row['bitis_tarihi']) {
            if (is_object($row['bitis_tarihi']) && method_exists($row['bitis_tarihi'], 'format')) {
                $bitis = $row['bitis_tarihi']->format('Y-m-d');
            } else {
                $bitis = $row['bitis_tarihi'];
            }
        }
        
        $kartlar[] = [
            'kart_id' => $row['kart_id'],
            'kart_uid' => $row['kart_uid'],
            'kart_aktif' => $row['kart_aktif'],
            'kisisel_id' => $row['kisisel_id'],
            'isim' => $row['isim'],
            'soyisim' => $row['soyisim'],
            'tc_no' => $row['tc_no'],
            'email' => $row['email'] ?? '',
            'uyelik_id' => $row['uyelik_id'],
            'kalan_giris' => (int)$row['kalan_giris'],
            'uyelik_durum' => $row['uyelik_durum'],
            'baslangic_tarihi' => $baslangic,
            'bitis_tarihi' => $bitis,
            'tur_adi' => $row['tur_adi']
        ];
    }
    
    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);
    
    echo json_encode([
        'success' => true,
        'data' => $kartlar,
        'count' => count($kartlar)
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Kartlar yüklenirken hata oluştu',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
