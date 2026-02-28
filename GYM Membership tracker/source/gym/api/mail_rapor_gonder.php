<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../includes/db_config.php';
require_once '../includes/mail_config.php';
startSecureSession();
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Oturum gerekli']);
    exit;
}
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz veri formatı']);
    exit;
}
$toEmail = trim($data['email'] ?? '');
if (empty($toEmail)) {
    echo json_encode(['success' => false, 'message' => 'Email adresi gerekli']);
    exit;
}

if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz email adresi']);
    exit;
}
$reportData = $data['reportData'] ?? [];
if (empty($reportData)) {
    echo json_encode(['success' => false, 'message' => 'Rapor verisi bulunamadı']);
    exit;
}
$format = strtolower($data['format'] ?? 'html');
if (!in_array($format, ['html', 'json', 'xml'])) {
    $format = 'html';
}
$baslangic = $data['baslangic'] ?? '';
$bitis = $data['bitis'] ?? '';
$dateRange = '';
if ($baslangic && $bitis) {
    $dateRange = date('d.m.Y', strtotime($baslangic)) . ' - ' . date('d.m.Y', strtotime($bitis));
}
$reportData['dateRange'] = $dateRange;
$htmlContent = generateReportHTML($reportData);
$attachment = null;

if ($format === 'json') {
    $jsonData = [
        'tarih_araligi' => $dateRange,
        'olusturma_zamani' => date('d.m.Y H:i:s'),
        'istatistikler' => $reportData['stats'],
        'kayitlar' => $reportData['data']
    ];
    
    $attachment = [
        'content' => json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        'filename' => 'giris_raporu_' . date('Y-m-d') . '.json'
    ];
    
} elseif ($format === 'xml') {
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<Rapor>' . "\n";
    $xml .= '  <TarihAraligi>' . htmlspecialchars($dateRange) . '</TarihAraligi>' . "\n";
    $xml .= '  <OlusturmaZamani>' . date('d.m.Y H:i:s') . '</OlusturmaZamani>' . "\n";
    $xml .= '  <Istatistikler>' . "\n";
    $xml .= '    <ToplamGiris>' . ($reportData['stats']['toplam'] ?? 0) . '</ToplamGiris>' . "\n";
    $xml .= '    <BasariliGiris>' . ($reportData['stats']['basarili'] ?? 0) . '</BasariliGiris>' . "\n";
    $xml .= '    <ReddedilenGiris>' . ($reportData['stats']['reddedilen'] ?? 0) . '</ReddedilenGiris>' . "\n";
    $xml .= '  </Istatistikler>' . "\n";
    $xml .= '  <Kayitlar>' . "\n";
    
    foreach ($reportData['data'] as $log) {
        $xml .= '    <Kayit>' . "\n";
        $xml .= '      <TarihSaat>' . htmlspecialchars($log['tarih_saat'] ?? '') . '</TarihSaat>' . "\n";
        $xml .= '      <AdSoyad>' . htmlspecialchars($log['ad_soyad'] ?? '') . '</AdSoyad>' . "\n";
        $xml .= '      <KartUID>' . htmlspecialchars($log['kart_uid'] ?? '') . '</KartUID>' . "\n";
        $xml .= '      <Sonuc>' . htmlspecialchars($log['sonuc'] ?? '') . '</Sonuc>' . "\n";
        $xml .= '      <TurnikeID>' . htmlspecialchars($log['turnike_id'] ?? '1') . '</TurnikeID>' . "\n";
        $xml .= '    </Kayit>' . "\n";
    }
    
    $xml .= '  </Kayitlar>' . "\n";
    $xml .= '</Rapor>';
    
    $attachment = [
        'content' => $xml,
        'filename' => 'giris_raporu_' . date('Y-m-d') . '.xml'
    ];
}
$result = sendReportEmail($toEmail, $htmlContent, 'ESOGÜ Spor Salonu - Giriş Raporu', $attachment);

echo json_encode($result);
?>
