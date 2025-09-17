<?php
header('Content-Type: application/json; charset=UTF-8');

// Hata yakalama için output buffering başlat
ob_start();

try {
    // Veritabanı bağlantısını kontrol et
    if (!file_exists('db.php')) {
        throw new Exception('Database configuration file not found');
    }
    
    require_once 'db.php';
    
    // Veritabanı bağlantısını kontrol et
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception('Database connection failed');
    }

    // POST verilerini al
    $input = file_get_contents('php://input');
    if (empty($input)) {
        throw new Exception('No input data received');
    }
    
    $data = json_decode($input, true);
    if (!$data) {
        throw new Exception('Invalid JSON data: ' . json_last_error_msg());
    }

    // Yazıcı ayarlarını veritabanından al
    function getPrinterSettings($conn) {
        $result = $conn->query("SELECT * FROM printer_settings ORDER BY id DESC LIMIT 1");
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        // Varsayılan ayarlar
        return [
            'company_name' => 'Restaurant Name',
            'company_address' => '123 Main Street',
            'company_phone' => '(555) 123-4567',
            'footer_text' => 'Thank You, Come Again!',
            'receipt_width' => 80,
            'logo_alignment' => 'center',
            'logo_path' => null,
            'logo_width' => 200,
            'logo_height' => 100,
            'connection_type' => 'wifi',
            'printer_ip' => '192.168.0.23',
            'printer_port' => 9100,
            'ethernet_ip' => '192.168.0.23',
            'ethernet_port' => 9100,
            'order_items_font_size' => '',
            'company_name_font ' => '',
            'company_name_size ' => '',
            'bluetooth_mac' => '',
            'printer_name' => 'Epson_TM-m30II'
        ];
    }

    // ESC/POS komutları için yardımcı fonksiyonlar
    class ESCPOSCommands {
        const ESC = "\x1B";
        const GS = "\x1D";
        const LF = "\x0A";
        const CR = "\x0D";
        
        // Yazıcıyı başlat
        public static function init() {
            return self::ESC . "@";
        }
        
        // Metin hizalama
        public static function align($alignment = 'left') {
            $alignments = [
                'left' => 0,
                'center' => 1,
                'right' => 2
            ];
            return self::ESC . "a" . chr($alignments[$alignment] ?? 0);
        }
        
        // Yazı tipi boyutu
        public static function textSize($width = 1, $height = 1) {
            $size = (($width - 1) << 4) | ($height - 1);
            return self::GS . "!" . chr($size);
        }
        
        // Kalın yazı
        public static function bold($enable = true) {
            return self::ESC . "E" . ($enable ? chr(1) : chr(0));
        }
        
        // Çizgi çek
        public static function drawLine($char = '-', $length = 48) {
            return str_repeat($char, $length) . self::LF;
        }
        
        // Kağıt kes
        public static function cut() {
            return self::GS . "V" . chr(66) . chr(0);
        }
        
        // Kasa çekmecesini aç
        public static function openDrawer() {
            return self::ESC . "p" . chr(0) . chr(25) . chr(250);
        }
    }

    // Fiş içeriğini oluştur
    function generateReceipt($data, $settings) {
        $receipt = "";
        
        // Yazıcıyı başlat
        $receipt .= ESCPOSCommands::init();
        
        // Logo ve şirket bilgileri
        $receipt .= ESCPOSCommands::align('center');
        $receipt .= ESCPOSCommands::textSize(2, 2);
        $receipt .= ESCPOSCommands::bold(true);
        $receipt .= $settings['company_name'] . ESCPOSCommands::LF;
        $receipt .= ESCPOSCommands::bold(false);
        $receipt .= ESCPOSCommands::textSize(1, 1);
        
        if (!empty($settings['company_address'])) {
            $receipt .= $settings['company_address'] . ESCPOSCommands::LF;
        }
        
        if (!empty($settings['company_phone'])) {
            $receipt .= $settings['company_phone'] . ESCPOSCommands::LF;
        }
        
        $receipt .= ESCPOSCommands::LF;
        
        // Sipariş başlığı
        $receipt .= ESCPOSCommands::bold(true);
        $receipt .= "ORDER RECEIPT" . ESCPOSCommands::LF;
        $receipt .= ESCPOSCommands::bold(false);
        
        // Masa ve tarih bilgileri
        $receipt .= ESCPOSCommands::align('left');
        $receipt .= "Table: " . ($data['table_id'] ?? 'N/A') . ESCPOSCommands::LF;
        $receipt .= "Date: " . ($data['date'] ?? date('Y-m-d H:i:s')) . ESCPOSCommands::LF;
        $receipt .= ESCPOSCommands::drawLine('-', 48);
        
   // Sipariş kalemleri
$total = 0;
if (isset($data['items']) && is_array($data['items'])) {
    foreach ($data['items'] as $item) {
        $itemName = $item['name'] ?? 'Unknown Item';
        $itemPrice = floatval($item['price'] ?? 0);
        $itemQuantity = intval($item['quantity'] ?? 1);
        $itemTotal = $itemPrice * $itemQuantity;
        $total += $itemTotal;
        
        // Ürün adı, miktar ve toplam fiyat aynı satırda
        $line = $itemQuantity . "X " . $itemName;
        $priceText = "\x9C" . number_format($itemTotal, 2);
        
        // Satır uzunluğunu hesapla ve fiyatı sağa hizala
        $lineLength = 48; // Toplam satır uzunluğu
        $currentLength = strlen($line);
        $spaces = max(0, $lineLength - $currentLength - strlen($priceText));
        
        $receipt .= $line . str_repeat(' ', $spaces) . $priceText . ESCPOSCommands::LF;
        
        // Opsiyonlar varsa
        if (isset($item['hasOptions']) && $item['hasOptions'] && isset($item['options'])) {
            foreach ($item['options'] as $option) {
                $optionText = "      ++ " . $option['name'];
                if ($option['price'] > 0) {
                    $optionText .= "(+\x9C" . number_format($option['price'], 2) . ")";
                }
                $receipt .= $optionText . ESCPOSCommands::LF;
            }
        }
        
        // Ürünler arası satır atlama YOK - bu satırı sildim
    }
}

        // Toplam
        $receipt .= ESCPOSCommands::drawLine('=', 48);
        $receipt .= ESCPOSCommands::bold(true);
        $receipt .= ESCPOSCommands::textSize(1, 2);
        
        $totalText = "TOTAL: \x9C" . number_format($total, 2);
        $receipt .= ESCPOSCommands::align('right');
        $receipt .= $totalText . ESCPOSCommands::LF;
        
        $receipt .= ESCPOSCommands::textSize(1, 1);
        $receipt .= ESCPOSCommands::bold(false);
        $receipt .= ESCPOSCommands::align('center');
        
        // Footer
        $receipt .= ESCPOSCommands::LF;
        if (!empty($settings['footer_text'])) {
            $receipt .= $settings['footer_text'] . ESCPOSCommands::LF;
        }
        
        $receipt .= ESCPOSCommands::LF . ESCPOSCommands::LF;
        
        // Kağıdı kes
        $receipt .= ESCPOSCommands::cut();
        
        return $receipt;
    }

    // Yazıcıya gönder
    function sendToPrinter($receiptData, $settings) {
        $connectionType = $settings['connection_type'] ?? 'wifi';
        
        switch ($connectionType) {
            case 'wifi':
            case 'ethernet':
                return sendToNetworkPrinter($receiptData, $settings);
                
            case 'usb':
                return sendToUSBPrinter($receiptData, $settings);
                
            case 'bluetooth':
                return sendToBluetoothPrinter($receiptData, $settings);
                
            default:
                return ['success' => false, 'error' => 'Unsupported connection type: ' . $connectionType];
        }
    }

    // Ağ yazıcısına gönder (WiFi/Ethernet)
    function sendToNetworkPrinter($data, $settings) {
        $ip = '';
        $port = 9100;
        
        if ($settings['connection_type'] === 'wifi') {
            $ip = $settings['printer_ip'] ?? '192.168.0.23';
            $port = intval($settings['printer_port'] ?? 9100);
        } else {
            $ip = $settings['ethernet_ip'] ?? '192.168.0.23';
            $port = intval($settings['ethernet_port'] ?? 9100);
        }
        
        if (empty($ip)) {
            return ['success' => false, 'error' => 'Printer IP address not configured'];
        }
        
        $socket = @fsockopen($ip, $port, $errno, $errstr, 5);
        if (!$socket) {
            return ['success' => false, 'error' => "Could not connect to printer at $ip:$port - $errstr ($errno)"];
        }
        
        // Veriyi gönder
        $bytes_sent = @fwrite($socket, $data);
        fclose($socket);
        
        if ($bytes_sent === false) {
            return ['success' => false, 'error' => 'Failed to send data to printer'];
        }
        
        return ['success' => true, 'connection_type' => $settings['connection_type'], 'bytes_sent' => $bytes_sent];
    }

    // USB yazıcısına gönder
    function sendToUSBPrinter($data, $settings) {
        $printerName = $settings['printer_name'] ?? '';
        
        if (empty($printerName)) {
            return ['success' => false, 'error' => 'USB printer name not configured'];
        }
        
        // Windows için
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $handle = @fopen("//./". $printerName, "w");
            if (!$handle) {
                return ['success' => false, 'error' => 'Could not open USB printer: ' . $printerName];
            }
            
            $result = fwrite($handle, $data);
            fclose($handle);
            
            if ($result === false) {
                return ['success' => false, 'error' => 'Failed to write to USB printer'];
            }
            
            return ['success' => true, 'connection_type' => 'usb', 'bytes_sent' => $result];
        }
        
        // Linux için
        $handle = @fopen("/dev/usb/lp0", "w");
        if (!$handle) {
            // Alternatif yol dene
            $handle = @fopen("/dev/lp0", "w");
        }
        
        if (!$handle) {
            return ['success' => false, 'error' => 'Could not open USB printer device'];
        }
        
        $result = fwrite($handle, $data);
        fclose($handle);
        
        if ($result === false) {
            return ['success' => false, 'error' => 'Failed to write to USB printer'];
        }
        
        return ['success' => true, 'connection_type' => 'usb', 'bytes_sent' => $result];
    }

    // Bluetooth yazıcısına gönder
    function sendToBluetoothPrinter($data, $settings) {
        $mac = $settings['bluetooth_mac'] ?? '';
        
        if (empty($mac)) {
            return ['success' => false, 'error' => 'Bluetooth MAC address not configured'];
        }
        
        // Bluetooth bağlantısı için rfcomm kullan (Linux)
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            $command = "echo " . escapeshellarg($data) . " | rfcomm connect /dev/rfcomm0 " . escapeshellarg($mac) . " 1";
            $output = [];
            $return_var = 0;
            exec($command, $output, $return_var);
            
            if ($return_var === 0) {
                return ['success' => true, 'connection_type' => 'bluetooth'];
            } else {
                return ['success' => false, 'error' => 'Bluetooth connection failed: ' . implode(' ', $output)];
            }
        }
        
        return ['success' => false, 'error' => 'Bluetooth printing not supported on this system'];
    }

    // Ana işlem
    // Yazıcı ayarlarını al
    $settings = getPrinterSettings($conn);
    
    // İşlem tipini kontrol et
    $action = $data['action'] ?? '';
    
    switch ($action) {
        case 'print_order_auto':
            // Fiş içeriğini oluştur
            $receiptData = generateReceipt($data, $settings);
            
            // Yazıcıya gönder
            $result = sendToPrinter($receiptData, $settings);
            
            ob_clean();
            echo json_encode($result);
            break;
            
        case 'test_print':
            // Test yazdırma
            $testData = [
                'table_id' => 'TEST',
                'date' => date('Y-m-d H:i:s'),
                'items' => [
                    [
                        'name' => 'Test Item',
                        'price' => 10.50,
                        'quantity' => 1,
                        'hasOptions' => false
                    ]
                ],
                'total' => 10.50
            ];
            
            $receiptData = generateReceipt($testData, $settings);
            $result = sendToPrinter($receiptData, $settings);
            
            ob_clean();
            echo json_encode($result);
            break;
            
        case 'open_drawer':
            // Kasa çekmecesini aç
            $drawerCommand = ESCPOSCommands::openDrawer();
            $result = sendToPrinter($drawerCommand, $settings);
            
            ob_clean();
            echo json_encode($result);
            break;
            
        default:
            ob_clean();
            echo json_encode(['success' => false, 'error' => 'Invalid action: ' . $action]);
            break;
    }
    
} catch (Exception $e) {
    ob_clean();
    error_log('Print.php Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Print system error: ' . $e->getMessage()]);
} catch (Error $e) {
    // Fatal error'ları da yakala
    ob_clean();
    error_log('Print.php Fatal Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Print system fatal error: ' . $e->getMessage()]);
}

// Veritabanı bağlantısını kapat
if (isset($conn)) {
    $conn->close();
}

// Output buffer'ı sonlandır
ob_end_flush();
?>
