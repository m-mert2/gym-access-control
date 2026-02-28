<?php
function sendReportEmail($toEmail, $reportHTML, $subject = 'ESOGÜ Spor Salonu - Rapor', $attachment = null) {
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        $autoloadPath = __DIR__ . '/../vendor/autoload.php';
        if (file_exists($autoloadPath)) {
            require_once $autoloadPath;
        } else {
            return [
                'success' => false,
                'message' => 'PHPMailer kütüphanesi bulunamadı. Composer ile yükleyin: composer require phpmailer/phpmailer'
            ];
        }
    }
    
    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'demo@gmail.com';
        $mail->Password = 'demo_password';  
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        $mail->setFrom('carpoteniasoftwares@gmail.com', 'ESOGÜ Spor Salonu');
        $mail->addAddress($toEmail);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $reportHTML;
        $mail->AltBody = strip_tags($reportHTML);
        if ($attachment && isset($attachment['content']) && isset($attachment['filename'])) {
            $mail->addStringAttachment($attachment['content'], $attachment['filename']);
        }
        $mail->send();
        
        return [
            'success' => true,
            'message' => 'Rapor başarıyla gönderildi!'
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => "Mail gönderilemedi: {$mail->ErrorInfo}"
        ];
    }
}

function generateReportHTML($data) {
    $stats = $data['stats'] ?? [];
    $logs = $data['data'] ?? [];
    $dateRange = $data['dateRange'] ?? '';
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { background: #007bff; color: white; padding: 20px; text-align: center; }
            .stats { display: flex; justify-content: space-around; margin: 20px 0; }
            .stat-box { background: #f8f9fa; padding: 15px; border-radius: 5px; text-align: center; flex: 1; margin: 0 10px; }
            .stat-box h3 { margin: 5px 0; color: #007bff; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th { background: #343a40; color: white; padding: 12px; text-align: left; }
            td { padding: 10px; border-bottom: 1px solid #ddd; }
            tr:nth-child(even) { background: #f8f9fa; }
            .badge { padding: 5px 10px; border-radius: 3px; color: white; font-size: 12px; }
            .badge-success { background: #28a745; }
            .badge-danger { background: #dc3545; }
            .footer { margin-top: 30px; padding: 15px; background: #f8f9fa; text-align: center; font-size: 12px; color: #6c757d; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>🏋️ ESOGÜ Spor Salonu</h1>
            <h2>Giriş Raporu</h2>
            <p>' . htmlspecialchars($dateRange) . '</p>
        </div>
        
        <div class="stats">
            <div class="stat-box">
                <h4>Toplam Giriş</h4>
                <h3>' . ($stats['toplam'] ?? 0) . '</h3>
            </div>
            <div class="stat-box">
                <h4>Başarılı</h4>
                <h3 style="color: #28a745;">' . ($stats['basarili'] ?? 0) . '</h3>
            </div>
            <div class="stat-box">
                <h4>Reddedilen</h4>
                <h3 style="color: #dc3545;">' . ($stats['reddedilen'] ?? 0) . '</h3>
            </div>
        </div>
        
        <h3>Detaylı Giriş Kayıtları</h3>
        <table>
            <thead>
                <tr>
                    <th>Tarih/Saat</th>
                    <th>Ad Soyad</th>
                    <th>Kart UID</th>
                    <th>Durum</th>
                    <th>Turnike</th>
                </tr>
            </thead>
            <tbody>';
    
    if (empty($logs)) {
        $html .= '<tr><td colspan="5" style="text-align: center; color: #6c757d;">Bu tarih aralığında kayıt bulunamadı</td></tr>';
    } else {
        foreach ($logs as $log) {
            $sonucStr = strtoupper($log['sonuc'] ?? '');
            $basarili = strpos($sonucStr, 'BASARILI') !== false || strpos($sonucStr, 'GİRİŞ') !== false || $sonucStr === '1';
            
            $durumBadge = $basarili ? 'success' : 'danger';
            $durumText = $basarili ? 'Başarılı' : 'Reddedildi';
            
            $html .= '
                <tr>
                    <td>' . htmlspecialchars($log['tarih_saat'] ?? '-') . '</td>
                    <td>' . htmlspecialchars($log['ad_soyad'] ?? 'Bilinmiyor') . '</td>
                    <td>' . htmlspecialchars($log['kart_uid'] ?? '-') . '</td>
                    <td><span class="badge badge-' . $durumBadge . '">' . $durumText . '</span></td>
                    <td>Turnike ' . htmlspecialchars($log['turnike_id'] ?? '1') . '</td>
                </tr>';
        }
    }
    
    $html .= '
            </tbody>
        </table>
        
        <div class="footer">
            <p><strong>ESOGÜ Spor Salonu Yönetim Sistemi</strong></p>
            <p>Bu rapor otomatik olarak oluşturulmuştur - ' . date('d.m.Y H:i') . '</p>
        </div>
    </body>
    </html>';
    
    return $html;
}
?>
