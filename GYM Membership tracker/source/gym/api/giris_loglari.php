    <?php
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
$baslangic_tarihi = isset($_GET['baslangic']) ? $_GET['baslangic'] : date('Y-m-d', strtotime('-7 days'));
$bitis_tarihi = isset($_GET['bitis']) ? $_GET['bitis'] : date('Y-m-d');
$sonuc = isset($_GET['sonuc']) ? intval($_GET['sonuc']) : null;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 100;
$query = "
    SELECT TOP (?)
        gl.log_id,
        gl.tarih_saat,
        gl.sonuc,
        CONCAT(kb.isim, ' ', kb.soyisim) AS ad_soyad,
        kb.ogrenci_no,
        ka.kart_uid,
        t.turnike_id,
        u.kalan_giris AS sonraki_kalan_hak
    FROM giris_log gl
    LEFT JOIN kisisel_bilgiler kb ON gl.kisisel_id = kb.kisisel_id
    LEFT JOIN kartlar ka ON gl.kart_id = ka.kart_id
    LEFT JOIN turnikeler t ON gl.turnike_id = t.turnike_id
    LEFT JOIN uyelikler u ON gl.uyelik_id = u.uyelik_id
    WHERE CAST(gl.tarih_saat AS DATE) BETWEEN ? AND ?
";

$params = array($limit, $baslangic_tarihi, $bitis_tarihi);
if ($sonuc !== null) {
    $query .= " AND gl.sonuc = ?";
    $params[] = $sonuc;
}

$query .= " ORDER BY gl.tarih_saat DESC";

$stmt = sqlsrv_prepare($conn, $query, $params);

if (!$stmt || !sqlsrv_execute($stmt)) {
    $errors = sqlsrv_errors();
    echo json_encode([
        'success' => false,
        'message' => 'Sorgu hatası',
        'error' => $errors,
        'query' => $query,
        'params' => $params
    ], JSON_UNESCAPED_UNICODE);
    sqlsrv_close($conn);
    exit;
}

$loglar = array();
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    if ($row['tarih_saat'] instanceof DateTime) {
        $row['tarih_saat'] = $row['tarih_saat']->format('Y-m-d H:i:s');
    }
    
    $loglar[] = $row;
}

sqlsrv_free_stmt($stmt);
$stats_query = "
    SELECT 
        COUNT(*) AS toplam,
        SUM(CASE WHEN CAST(sonuc AS VARCHAR(20)) IN ('1', 'BASARILI') THEN 1 ELSE 0 END) AS basarili,
        SUM(CASE WHEN CAST(sonuc AS VARCHAR(20)) IN ('0', 'REDDEDILDI') THEN 1 ELSE 0 END) AS reddedilen
    FROM giris_log
    WHERE CAST(tarih_saat AS DATE) BETWEEN ? AND ?
";

$stats_params = array($baslangic_tarihi, $bitis_tarihi);
if ($sonuc !== null) {
    $stats_query .= " AND sonuc = ?";
    $stats_params[] = $sonuc;
}

$stmt = sqlsrv_prepare($conn, $stats_query, $stats_params);
$stats = array('toplam' => 0, 'basarili' => 0, 'reddedilen' => 0);

if ($stmt && sqlsrv_execute($stmt) && sqlsrv_fetch($stmt)) {
    $stats['toplam'] = sqlsrv_get_field($stmt, 0) ?? 0;
    $stats['basarili'] = sqlsrv_get_field($stmt, 1) ?? 0;
    $stats['reddedilen'] = sqlsrv_get_field($stmt, 2) ?? 0;
    sqlsrv_free_stmt($stmt);
}

sqlsrv_close($conn);

echo json_encode([
    'success' => true,
    'data' => $loglar,
    'count' => count($loglar),
    'stats' => $stats,
    'filters' => [
        'baslangic' => $baslangic_tarihi,
        'bitis' => $bitis_tarihi,
        'sonuc' => $sonuc,
        'limit' => $limit
    ]
]);
?>
