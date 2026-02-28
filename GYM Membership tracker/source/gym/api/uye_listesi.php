<?php

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../includes/db_config.php';

$conn = getDBConnection();
if (!$conn) {
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı bağlantısı başarısız'
    ]);
    exit;
}
$query = "
    SELECT 
        k.kisisel_id,
        k.isim,
        k.soyisim,
        k.tc_no,
        k.email,
        k.telefon,
        k.bolum,
        k.ogrenci_no,
        k.kayit_tarihi,
        u.uyelik_id,
        u.tur_id,
        ut.tur_adi,
        u.baslangic_tarihi,
        u.bitis_tarihi,
        u.kalan_giris,
        u.durum,
        ka.kart_id,
        ka.kart_uid,
        ka.aktif AS kart_aktif,
        ka.son_kullanim,
        CASE 
            WHEN u.bitis_tarihi < CAST(GETDATE() AS DATE) THEN 'Süresi Dolmuş'
            WHEN u.kalan_giris <= 0 THEN 'Hakkı Bitti'
            WHEN u.durum = 0 THEN 'Pasif'
            ELSE 'Aktif'
        END AS uyelik_durumu
    FROM kisisel_bilgiler k
    LEFT JOIN uyelikler u ON k.kisisel_id = u.kisisel_id
    LEFT JOIN uyelik_turleri ut ON u.tur_id = ut.tur_id
    LEFT JOIN kartlar ka ON k.kisisel_id = ka.kisisel_id AND u.uyelik_id = ka.uyelik_id
    ORDER BY k.kayit_tarihi DESC
";

$stmt = sqlsrv_query($conn, $query);

if ($stmt === false) {
    $errors = sqlsrv_errors();
    echo json_encode([
        'success' => false,
        'message' => 'Sorgu hatası',
        'error' => $errors[0]['message']
    ]);
    sqlsrv_close($conn);
    exit;
}

$uyeler = array();
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    if ($row['kayit_tarihi'] instanceof DateTime) {
        $row['kayit_tarihi'] = $row['kayit_tarihi']->format('Y-m-d H:i:s');
    }
    if ($row['baslangic_tarihi'] instanceof DateTime) {
        $row['baslangic_tarihi'] = $row['baslangic_tarihi']->format('Y-m-d');
    }
    if ($row['bitis_tarihi'] instanceof DateTime) {
        $row['bitis_tarihi'] = $row['bitis_tarihi']->format('Y-m-d');
    }
    if ($row['son_kullanim'] instanceof DateTime) {
        $row['son_kullanim'] = $row['son_kullanim']->format('Y-m-d H:i:s');
    }
    
    $uyeler[] = $row;
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

echo json_encode([
    'success' => true,
    'data' => $uyeler,
    'count' => count($uyeler)
]);
?>
