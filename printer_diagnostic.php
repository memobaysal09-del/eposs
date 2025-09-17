<?php
// printer_diagnostic.php - Kapsamlı yazıcı tanı aracı

function diagnosticTest($ip = '192.168.0.23', $port = 9100, $timeout = 10) {
    echo "=== TERMAL YAZICI TANI ARACI ===\n";
    echo "Yazıcı: $ip:$port\n";
    echo "Timeout: ${timeout}s\n\n";
    
    $tests = [
        'network_ping' => 'Ağ Bağlantısı Testi',
        'socket_connect' => 'Socket Bağlantısı Testi', 
        'basic_commands' => 'Temel ESC/POS Komutları',
        'character_test' => 'Karakter Kodlama Testi',
        'full_receipt' => 'Tam Fiş Testi'
    ];
    
    $results = [];
    
    // 1. Ağ Ping Testi
    echo "1. {$tests['network_ping']}...\n";
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $ping = exec("ping -n 1 -w 1000 $ip", $output, $return);
    } else {
        $ping = exec("ping -c 1 -W 1 $ip", $output, $return);
    }
    
    if ($return === 0) {
        echo "   ✓ Ping başarılı - Yazıcı ağda erişilebilir\n";
        $results['network_ping'] = true;
    } else {
        echo "   ✗ Ping başarısız - Yazıcı ağda erişilemiyor\n";
        echo "   → IP adresini kontrol edin\n";
        echo "   → Yazıcının açık olduğundan emin olun\n";
        $results['network_ping'] = false;
    }
    echo "\n";
    
    // 2. Socket Bağlantısı Testi
    echo "2. {$tests['socket_connect']}...\n";
    $context = stream_context_create(['socket' => ['timeout' => $timeout]]);
    $socket = @stream_socket_client("tcp://$ip:$port", $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $context);
    
    if ($socket) {
        echo "   ✓ Socket bağlantısı başarılı\n";
        echo "   → Port $port açık ve dinliyor\n";
        $results['socket_connect'] = true;
        
        // 3. Temel ESC/POS Komutları
        echo "\n3. {$tests['basic_commands']}...\n";
        try {
            $WAKE_UP = chr(27).chr(61).chr(1);  // ESC = 1
            $INIT = chr(27).chr(64);            // ESC @
            $STATUS = chr(16).chr(4).chr(1);    // DLE EOT 1 - Real-time status
            
            echo "   → Yazıcıyı uyandırıyor...\n";
            fwrite($socket, $WAKE_UP);
            fflush($socket);
            usleep(200000); // 200ms
            
            echo "   → Yazıcıyı başlatıyor...\n";
            fwrite($socket, $INIT);
            fflush($socket);
            usleep(200000); // 200ms
            
            echo "   → Durum sorguluyor...\n";
            fwrite($socket, $STATUS);
            fflush($socket);
            
            // Durum yanıtını oku
            stream_set_timeout($socket, 2);
            $status_response = fread($socket, 1);
            
            if ($status_response !== false && strlen($status_response) > 0) {
                $status_byte = ord($status_response);
                echo "   ✓ Yazıcı durumu alındı: " . sprintf("0x%02X", $status_byte) . "\n";
                
                // Durum analizi
                if (($status_byte & 0x08) == 0) echo "   → Yazıcı çevrimiçi\n";
                else echo "   → UYARI: Yazıcı çevrimdışı\n";
                
                if (($status_byte & 0x20) == 0) echo "   → Kağıt var\n";
                else echo "   → UYARI: Kağıt yok\n";
                
                if (($status_byte & 0x40) == 0) echo "   → Kapak kapalı\n";
                else echo "   → UYARI: Kapak açık\n";
                
            } else {
                echo "   ⚠ Durum yanıtı alınamadı (normal olabilir)\n";
            }
            
            $results['basic_commands'] = true;
            
        } catch (Exception $e) {
            echo "   ✗ Komut testi başarısız: " . $e->getMessage() . "\n";
            $results['basic_commands'] = false;
        }
        
        // 4. Karakter Kodlama Testi
        echo "\n4. {$tests['character_test']}...\n";
        try {
            $TURKISH_CP = chr(27).'t'.chr(18);  // CP1254
            $ALIGN_C = chr(27).'a'.chr(1);
            $FEED = chr(10);
            
            fwrite($socket, $TURKISH_CP);
            fwrite($socket, $ALIGN_C);
            
            $test_text = "TEST: ÇĞİÖŞÜ çğıöşü ₺";
            $encoded = @iconv('UTF-8', 'CP1254//TRANSLIT', $test_text);
            
            if ($encoded !== false) {
                fwrite($socket, $encoded . $FEED . $FEED);
                fflush($socket);
                echo "   ✓ Türkçe karakter testi gönderildi\n";
                $results['character_test'] = true;
            } else {
                echo "   ✗ Karakter kodlama başarısız\n";
                $results['character_test'] = false;
            }
            
        } catch (Exception $e) {
            echo "   ✗ Karakter testi başarısız: " . $e->getMessage() . "\n";
            $results['character_test'] = false;
        }
        
        // 5. Tam Fiş Testi
        echo "\n5. {$tests['full_receipt']}...\n";
        try {
            $receipt_data = buildTestReceipt();
            fwrite($socket, $receipt_data);
            fflush($socket);
            
            echo "   ✓ Test fişi gönderildi\n";
            echo "   → Yazıcıdan çıktı kontrol edin\n";
            $results['full_receipt'] = true;
            
        } catch (Exception $e) {
            echo "   ✗ Fiş testi başarısız: " . $e->getMessage() . "\n";
            $results['full_receipt'] = false;
        }
        
        fclose($socket);
        
    } else {
        echo "   ✗ Socket bağlantısı başarısız: $errstr ($errno)\n";
        echo "   → Port $port kapalı olabilir\n";
        echo "   → Yazıcı ayarlarını kontrol edin\n";
        $results['socket_connect'] = false;
        $results['basic_commands'] = false;
        $results['character_test'] = false;
        $results['full_receipt'] = false;
    }
    
    // Sonuç özeti
    echo "\n=== SONUÇ ÖZETİ ===\n";
    $passed = 0;
    $total = count($tests);
    
    foreach ($tests as $key => $name) {
        $status = $results[$key] ?? false;
        echo ($status ? "✓" : "✗") . " $name\n";
        if ($status) $passed++;
    }
    
    echo "\nBaşarı oranı: $passed/$total (" . round(($passed/$total)*100) . "%)\n";
    
    // Öneriler
    echo "\n=== ÖNERİLER ===\n";
    if (!$results['network_ping']) {
        echo "• Yazıcının IP adresini kontrol edin\n";
        echo "• Yazıcının açık ve ağa bağlı olduğundan emin olun\n";
        echo "• Ağ ayarlarını kontrol edin\n";
    }
    
    if ($results['network_ping'] && !$results['socket_connect']) {
        echo "• Port 9100'ün açık olduğunu kontrol edin\n";
        echo "• Yazıcı modelinin ESC/POS desteklediğini doğrulayın\n";
        echo "• Güvenlik duvarı ayarlarını kontrol edin\n";
    }
    
    if ($results['socket_connect'] && !$results['basic_commands']) {
        echo "• Yazıcı modelinin ESC/POS komutlarını desteklediğini doğrulayın\n";
        echo "• Yazıcı ayarlarında 'ESC/POS' modunun aktif olduğunu kontrol edin\n";
    }
    
    if ($passed == $total) {
        echo "• Tüm testler başarılı! Yazıcınız çalışmaya hazır.\n";
    }
    
    return $results;
}

function buildTestReceipt() {
    $INIT = chr(27).chr(64);                    // ESC @
    $WAKE_UP = chr(27).chr(61).chr(1);         // ESC = 1
    $TURKISH_CP = chr(27).'t'.chr(18);         // CP1254
    $ALIGN_C = chr(27).'a'.chr(1);             // Center
    $ALIGN_L = chr(27).'a'.chr(0);             // Left
    $BOLD_ON = chr(27).'E'.chr(1);             // Bold on
    $BOLD_OFF = chr(27).'E'.chr(0);            // Bold off
    $DOUBLE_ON = chr(29).'!'.chr(0x11);        // Double size
    $DOUBLE_OFF = chr(29).'!'.chr(0x00);       // Normal size
    $CUT = chr(29).chr(86).chr(65);            // Full cut
    $FEED = chr(27).'d'.chr(3);                // Feed 3 lines
    
    $receipt = "";
    
    // Initialization sequence
    $receipt .= $WAKE_UP;
    $receipt .= $INIT;
    $receipt .= $TURKISH_CP;
    
    // Header
    $receipt .= $ALIGN_C;
    $receipt .= $BOLD_ON . $DOUBLE_ON;
    $receipt .= iconv('UTF-8', 'CP1254//TRANSLIT', "TEST FİŞİ") . "\n";
    $receipt .= $DOUBLE_OFF . $BOLD_OFF;
    $receipt .= iconv('UTF-8', 'CP1254//TRANSLIT', date('Y-m-d H:i:s')) . "\n";
    $receipt .= str_repeat('-', 42) . "\n";
    
    // Content
    $receipt .= $ALIGN_L;
    $receipt .= iconv('UTF-8', 'CP1254//TRANSLIT', "Test Ürün 1 x1        10.50 ₺") . "\n";
    $receipt .= iconv('UTF-8', 'CP1254//TRANSLIT', "Test Ürün 2 x2        25.00 ₺") . "\n";
    $receipt .= str_repeat('-', 42) . "\n";
    
    // Total
    $receipt .= $BOLD_ON;
    $receipt .= iconv('UTF-8', 'CP1254//TRANSLIT', "TOPLAM:               35.50 ₺") . "\n";
    $receipt .= $BOLD_OFF;
    
    // Footer
    $receipt .= $ALIGN_C;
    $receipt .= iconv('UTF-8', 'CP1254//TRANSLIT', "Teşekkürler!") . "\n";
    $receipt .= iconv('UTF-8', 'CP1254//TRANSLIT', "ÇĞİÖŞÜ çğıöşü") . "\n";
    
    // End
    $receipt .= $FEED;
    $receipt .= $CUT;
    
    return $receipt;
}

// CLI kullanımı
if (php_sapi_name() === 'cli') {
    $ip = $argv[1] ?? '192.168.0.23';
    $port = intval($argv[2] ?? 9100);
    $timeout = intval($argv[3] ?? 10);
    
    diagnosticTest($ip, $port, $timeout);
} else {
    // Web kullanımı
    if (isset($_GET['test'])) {
        header('Content-Type: text/plain; charset=utf-8');
        $ip = $_GET['ip'] ?? '192.168.0.23';
        $port = intval($_GET['port'] ?? 9100);
        diagnosticTest($ip, $port);
    } else {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Yazıcı Tanı Aracı</title>
            <meta charset="utf-8">
        </head>
        <body>
            <h1>Termal Yazıcı Tanı Aracı</h1>
            <form method="get">
                <p>
                    <label>Yazıcı IP:</label>
                    <input type="text" name="ip" value="192.168.0.23" required>
                </p>
                <p>
                    <label>Port:</label>
                    <input type="number" name="port" value="9100" required>
                </p>
                <input type="hidden" name="test" value="1">
                <button type="submit">Tanı Testini Başlat</button>
            </form>
        </body>
        </html>
        <?php
    }
}
?>
