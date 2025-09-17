<?php
// save_printer_settings.php - Basit ve çalışan versiyon
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/php_errors.log');

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Basit response fonksiyonu
function sendResponse($success, $message, $data = []) {
    http_response_code($success ? 200 : 500);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // JSON verisini al
    $input = file_get_contents('php://input');
    if (!$input) {
        sendResponse(false, 'Geçersiz istek: Boş veri');
    }
    
    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        sendResponse(false, 'Geçersiz JSON: ' . json_last_error_msg());
    }

    // Veritabanı bağlantısı
    require_once __DIR__ . '/db.php';
    
    if (!isset($conn) || !($conn instanceof mysqli)) {
        sendResponse(false, 'Veritabanı bağlantı hatası');
    }

    // Tabloyu kontrol et ve gerekirse oluştur
    $create_table_sql = "CREATE TABLE IF NOT EXISTS printer_settings (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        company_name VARCHAR(255) DEFAULT 'Restaurant Adı',
        company_name_font VARCHAR(100) DEFAULT 'Arial, sans-serif',
        company_name_size TINYINT DEFAULT 18,
        order_items_font_size TINYINT DEFAULT 12,
        company_address VARCHAR(255) DEFAULT 'Örnek Mah. Örnek Cad. No:123',
        company_phone VARCHAR(50) DEFAULT '0 (212) 345 67 89',
        footer_text VARCHAR(255) DEFAULT 'Teşekkür Ederiz, Yine Bekleriz!',
        receipt_width INT(3) DEFAULT 58,
        logo_alignment VARCHAR(10) DEFAULT 'center',
        logo_path VARCHAR(255) NULL,
        logo_width INT(4) DEFAULT 200,
        logo_height INT(4) DEFAULT 100,
        printer_ip VARCHAR(45) NULL,
        printer_port INT DEFAULT 9100,
        connection_type VARCHAR(20) DEFAULT 'wifi',
        bluetooth_mac VARCHAR(20) NULL,
        ethernet_ip VARCHAR(45) NULL,
        ethernet_port INT DEFAULT 9100,
        printer_name VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($create_table_sql)) {
        sendResponse(false, 'Tablo oluşturma hatası: ' . $conn->error);
    }

    // Mevcut kaydı kontrol et
    $check = $conn->query("SELECT id FROM printer_settings LIMIT 1");
    $exists = ($check && $check->num_rows > 0);

    // İzin verilen alanlar
    $allowed_fields = [
        'company_name', 'company_name_font', 'company_name_size', 
        'order_items_font_size',
        'company_address', 'company_phone', 'footer_text', 'receipt_width', 
        'logo_alignment', 'logo_path', 'logo_width', 'logo_height', 
        'printer_ip', 'printer_port', 'connection_type', 
        'bluetooth_mac', 'ethernet_ip', 'ethernet_port', 'printer_name'
    ];

    // Temizlenmiş veriler - sadece boş olmayan bağlantı alanlarını güncelle
    $clean_data = [];
    $connection_fields = ['printer_ip', 'bluetooth_mac', 'ethernet_ip', 'printer_name'];
    
    foreach ($allowed_fields as $field) {
        if (isset($data[$field])) {
            // Bağlantı alanları için özel kontrol
            if (in_array($field, $connection_fields)) {
                // Sadece boş olmayan değerleri kabul et
                if (!empty($data[$field]) && $data[$field] !== null && $data[$field] !== '') {
                    $clean_data[$field] = $data[$field];
                }
            } else {
                // Diğer alanlar için normal işlem
                $clean_data[$field] = $data[$field];
            }
        }
    }
    
    // connection_type her zaman güncellensin
    if (isset($data['connection_type'])) {
        $clean_data['connection_type'] = $data['connection_type'];
    }

    if (empty($clean_data)) {
        sendResponse(false, 'Kaydedilecek veri yok');
    }

    if ($exists) {
        // UPDATE
        $sql = "UPDATE printer_settings SET ";
        $set_parts = [];
        $params = [];
        $types = '';
        
        foreach ($clean_data as $field => $value) {
            $set_parts[] = "$field = ?";
            $params[] = $value;
            $types .= 's';
        }
        
        $sql .= implode(', ', $set_parts) . " WHERE id = (SELECT id FROM (SELECT id FROM printer_settings LIMIT 1) as temp)";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            sendResponse(false, 'Sorgu hazırlama hatası: ' . $conn->error);
        }
        
        $stmt->bind_param($types, ...$params);
    } else {
        // INSERT
        $fields = array_keys($clean_data);
        $placeholders = array_fill(0, count($fields), '?');
        $params = array_values($clean_data);
        $types = str_repeat('s', count($fields));
        
        $sql = "INSERT INTO printer_settings (" . implode(', ', $fields) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            sendResponse(false, 'Sorgu hazırlama hatası: ' . $conn->error);
        }
        
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        sendResponse(false, 'Sorgu çalıştırma hatası: ' . $stmt->error);
    }

    sendResponse(true, 'Ayarlar başarıyla kaydedildi', [
        'affected_rows' => $stmt->affected_rows,
        'action' => $exists ? 'update' : 'insert'
    ]);

} catch (Exception $e) {
    error_log('save_printer_settings Exception: ' . $e->getMessage());
    sendResponse(false, 'Hata: ' . $e->getMessage());
} catch (Error $e) {
    error_log('save_printer_settings Error: ' . $e->getMessage());
    sendResponse(false, 'Sistem hatası: ' . $e->getMessage());
}
?>
