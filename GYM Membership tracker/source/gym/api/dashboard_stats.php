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
$stats = array();
$query = "SELECT COUNT(*) AS toplam FROM kisisel_bilgiler";
$result = sqlsrv_query($conn, $query);
if ($result && sqlsrv_fetch($result)) {
    $stats['toplam_uye'] = sqlsrv_get_field($result, 0);
}
$query = "SELECT COUNT(*) AS aktif FROM uyelikler WHERE durum = 1 AND bitis_tarihi >= CAST(GETDATE() AS DATE)";
$result = sqlsrv_query($conn, $query);
if ($result && sqlsrv_fetch($result)) {
    $stats['aktif_uyelik'] = sqlsrv_get_field($result, 0);
}
$query = "SELECT COUNT(*) AS bugun FROM giris_log WHERE CAST(tarih_saat AS DATE) = CAST(GETDATE() AS DATE) AND UPPER(sonuc) LIKE '%BASARILI%'";
$result = sqlsrv_query($conn, $query);
if ($result && sqlsrv_fetch($result)) {
    $stats['bugun_giris'] = sqlsrv_get_field($result, 0);
}
$query = "SELECT COUNT(*) AS aktif_kart FROM kartlar WHERE aktif = 1";
$result = sqlsrv_query($conn, $query);
if ($result && sqlsrv_fetch($result)) {
    $stats['aktif_kart'] = sqlsrv_get_field($result, 0);
}
$query = "
    SELECT TOP 10
        gl.log_id,
        gl.tarih_saat,
        gl.sonuc,
        CONCAT(kb.isim, ' ', kb.soyisim) AS ad_soyad,
        ka.kart_uid,
        t.turnike_id,
        u.kalan_giris
    FROM giris_log gl
    LEFT JOIN kisisel_bilgiler kb ON gl.kisisel_id = kb.kisisel_id
    LEFT JOIN kartlar ka ON gl.kart_id = ka.kart_id
    LEFT JOIN turnikeler t ON gl.turnike_id = t.turnike_id
    LEFT JOIN uyelikler u ON gl.uyelik_id = u.uyelik_id
    ORDER BY gl.tarih_saat DESC
";

$result = sqlsrv_query($conn, $query);
$son_girisler = array();

if ($result) {
    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        if ($row['tarih_saat'] instanceof DateTime) {
            $row['tarih_saat'] = $row['tarih_saat']->format('Y-m-d H:i:s');
        }
        $son_girisler[] = $row;
    }
}
$query = "
    SELECT COUNT(*) AS dolacak 
    FROM uyelikler 
    WHERE bitis_tarihi = CAST(GETDATE() AS DATE) AND durum = 1
";
$result = sqlsrv_query($conn, $query);
if ($result && sqlsrv_fetch($result)) {
    $stats['bugun_dolacak'] = sqlsrv_get_field($result, 0);
}
$query = "
    SELECT COUNT(*) AS azalanlar 
    FROM uyelikler 
    WHERE kalan_giris <= 5 AND kalan_giris > 0 AND durum = 1
";
$result = sqlsrv_query($conn, $query);
if ($result && sqlsrv_fetch($result)) {
    $stats['giris_hakki_azalanlar'] = sqlsrv_get_field($result, 0);
}

sqlsrv_close($conn);

echo json_encode([
    'success' => true,
    'stats' => $stats,
    'son_girisler' => $son_girisler
]);
?>
