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

$data = array();

try {
    $query = "
        SELECT COUNT(DISTINCT kb.kisisel_id) AS aktif_uye
        FROM kisisel_bilgiler kb
        INNER JOIN uyelikler u ON kb.kisisel_id = u.kisisel_id
        WHERE u.durum = 1 AND u.bitis_tarihi >= CAST(GETDATE() AS DATE)
    ";
    $result = sqlsrv_query($conn, $query);
    if ($result && sqlsrv_fetch($result)) {
        $data['aktif_uye'] = sqlsrv_get_field($result, 0);
    }
    $query = "SELECT COUNT(*) AS toplam FROM kisisel_bilgiler";
    $result = sqlsrv_query($conn, $query);
    $toplam_uye = 0;
    if ($result && sqlsrv_fetch($result)) {
        $toplam_uye = sqlsrv_get_field($result, 0);
    }
    $data['pasif_uye'] = $toplam_uye - ($data['aktif_uye'] ?? 0);
    $query = "
        SELECT 
            SUM(CASE WHEN UPPER(cinsiyet) = 'ERKEK' OR UPPER(cinsiyet) = 'E' THEN 1 ELSE 0 END) AS erkek,
            SUM(CASE WHEN UPPER(cinsiyet) = 'KADIN' OR UPPER(cinsiyet) = 'K' THEN 1 ELSE 0 END) AS kadin,
            SUM(CASE WHEN cinsiyet IS NULL OR (UPPER(cinsiyet) NOT IN ('ERKEK', 'E', 'KADIN', 'K')) THEN 1 ELSE 0 END) AS diger
        FROM kisisel_bilgiler
    ";
    $result = sqlsrv_query($conn, $query);
    if ($result && sqlsrv_fetch($result)) {
        $data['erkek'] = sqlsrv_get_field($result, 0) ?? 0;
        $data['kadin'] = sqlsrv_get_field($result, 1) ?? 0;
        $data['diger'] = sqlsrv_get_field($result, 2) ?? 0;
    }
    $query = "
        SELECT 
            ut.tur_adi,
            COUNT(DISTINCT u.uyelik_id) AS sayi
        FROM uyelik_turleri ut
        LEFT JOIN uyelikler u ON ut.tur_id = u.tur_id 
            AND u.durum = 1 
            AND u.bitis_tarihi >= CAST(GETDATE() AS DATE)
        GROUP BY ut.tur_adi
        ORDER BY sayi DESC
    ";
    $result = sqlsrv_query($conn, $query);
    $data['uyelik_turleri'] = array();
    
    if ($result) {
        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $data['uyelik_turleri'][] = $row;
        }
    }
    $query = "
        SELECT 
            uye_grubu,
            COUNT(*) AS sayi
        FROM kisisel_bilgiler
        GROUP BY uye_grubu
        ORDER BY sayi DESC
    ";
    $result = sqlsrv_query($conn, $query);
    $data['uye_gruplari'] = array();
    
    if ($result) {
        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $data['uye_gruplari'][] = $row;
        }
    }

    sqlsrv_close($conn);

    echo json_encode([
        'success' => true,
        'data' => $data
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
