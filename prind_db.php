<?php
// Hata raporlama (GEÇİCİ: sadece yerelde 1 yap)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

$host = 'localhost';
$dbname = 'if0_38069225_epost';   // mevcut DB adın
$username = 'root';               // DÜZELTİLDİ: root@localhost değil
$password = 'memet2151';          // kendi parolan

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    error_log('MySQL bağlantı hatası: ' . $conn->connect_error);
    http_response_code(500);
    exit('Veritabanı bağlantı hatası.');
}

$conn->set_charset('utf8mb4');

// Bağlantı hatası kontrolü
if ($conn->connect_error) {
    error_log("MySQL bağlantı hatası: " . $conn->connect_error);
    die("Veritabanı bağlantı hatası. Lütfen daha sonra tekrar deneyin.");
}

// Türkçe karakter desteği için
$conn->set_charset("utf8mb4");

// Tabloları oluştur
function createTables($conn) {
    // Kategoriler tablosu
    $sql = "CREATE TABLE IF NOT EXISTS categories (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        order_index INT(6) UNSIGNED DEFAULT 0
    )";
    
    if ($conn->query($sql) !== TRUE) {
        error_log("Tablo oluşturma hatası: " . $conn->error);
        return false;
    }
    
    // Ürünler tablosu
    $sql = "CREATE TABLE IF NOT EXISTS products (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        category_id INT(6) UNSIGNED,
        name VARCHAR(100) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        available BOOLEAN DEFAULT TRUE,
        icerik TEXT NULL,
        order_index INT(6) UNSIGNED DEFAULT 0,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
    )";
    
    if ($conn->query($sql) !== TRUE) {
        error_log("Tablo oluşturma hatası: " . $conn->error);
        return false;
    }
    
    // Masalar tablosu
    $sql = "CREATE TABLE IF NOT EXISTS tables (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        number INT(6) NOT NULL UNIQUE,
        name VARCHAR(100),
        status ENUM('available', 'occupied') DEFAULT 'available'
    )";
    
    if ($conn->query($sql) !== TRUE) {
        error_log("Tablo oluşturma hatası: " . $conn->error);
        return false;
    }
    
    // Siparişler tablosu
    $sql = "CREATE TABLE IF NOT EXISTS orders (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        table_id INT(6) UNSIGNED,
        total_amount DECIMAL(10,2),
        order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
        FOREIGN KEY (table_id) REFERENCES tables(id) ON DELETE CASCADE
    )";
    
    if ($conn->query($sql) !== TRUE) {
        error_log("Tablo oluşturma hatası: " . $conn->error);
        return false;
    }
    
    // Sipariş detayları tablosu
    $sql = "CREATE TABLE IF NOT EXISTS order_items (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        order_id INT(6) UNSIGNED,
        product_id INT(6) UNSIGNED,
        quantity INT(3),
        price DECIMAL(10,2),
        options TEXT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )";
    
    if ($conn->query($sql) !== TRUE) {
        error_log("Tablo oluşturma hatası: " . $conn->error);
        return false;
    }
    
    // Printer ayarları tablosu - GÜNCELLENDİ
    $sql = "CREATE TABLE IF NOT EXISTS printer_settings (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        company_name VARCHAR(255) DEFAULT 'Restaurant Adı',
        company_name_font VARCHAR(100) DEFAULT 'Arial, sans-serif',
        company_name_size TINYINT DEFAULT 18,
        company_address VARCHAR(255) DEFAULT 'Örnek Mah. Örnek Cad. No:123',
        company_phone VARCHAR(50) DEFAULT '0 (212) 345 67 89',
        footer_text VARCHAR(255) DEFAULT 'Teşekkür Ederiz, Yine Bekleriz!',
        receipt_width INT(3) DEFAULT 80,
        logo_alignment VARCHAR(10) DEFAULT 'center',
        logo_path VARCHAR(255) NULL,
        logo_width INT(4) DEFAULT 200,
        logo_height INT(4) DEFAULT 100,
        order_items_font_size TINYINT DEFAULT 12,
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
    
    if ($conn->query($sql) !== TRUE) {
        error_log("Tablo oluşturma hatası: " . $conn->error);
        return false;
    }
    
    // Opsiyon grupları tablosu
    $sql = "CREATE TABLE IF NOT EXISTS option_groups (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        is_required BOOLEAN DEFAULT FALSE,
        min_selection INT(2) DEFAULT 0,
        max_selection INT(2) DEFAULT 1
    )";
    
    if ($conn->query($sql) !== TRUE) {
        error_log("Tablo oluşturma hatası: " . $conn->error);
        return false;
    }
    
    // Opsiyonlar tablosu
    $sql = "CREATE TABLE IF NOT EXISTS options (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        group_id INT(6) UNSIGNED,
        name VARCHAR(100) NOT NULL,
        price DECIMAL(10,2) DEFAULT 0,
        FOREIGN KEY (group_id) REFERENCES option_groups(id) ON DELETE CASCADE
    )";
    
    if ($conn->query($sql) !== TRUE) {
        error_log("Tablo oluşturma hatası: " . $conn->error);
        return false;
    }
    
    // Ürün-opsiyon grupları ilişki tablosu
    $sql = "CREATE TABLE IF NOT EXISTS product_option_groups (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        product_id INT(6) UNSIGNED,
        option_group_id INT(6) UNSIGNED,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        FOREIGN KEY (option_group_id) REFERENCES option_groups(id) ON DELETE CASCADE
    )";
    
    if ($conn->query($sql) !== TRUE) {
        error_log("Tablo oluşturma hatası: " . $conn->error);
        return false;
    }

    $sql = "CREATE TABLE IF NOT EXISTS category_option_groups (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        category_id INT(6) UNSIGNED,
        option_group_id INT(6) UNSIGNED,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
        FOREIGN KEY (option_group_id) REFERENCES option_groups(id) ON DELETE CASCADE,
        UNIQUE KEY unique_category_option (category_id, option_group_id)
    )";
    if ($conn->query($sql) !== TRUE) {
        error_log("Tablo oluşturma hatası: " . $conn->error);
        return false;
    }
    
    // Raporlar tablosu
    $sql = "CREATE TABLE IF NOT EXISTS reports (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        type ENUM('daily', 'weekly', 'monthly', 'yearly', 'custom') NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        data TEXT NOT NULL,
        total_revenue DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql) !== TRUE) {
        error_log("Tablo oluşturma hatası: " . $conn->error);
        return false;
    }
    
    // Kasa işlemleri tablosu
    $sql = "CREATE TABLE IF NOT EXISTS cash_drawer_operations (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        table_id INT(6) UNSIGNED NULL,
        operation_type ENUM('open', 'close') NOT NULL,
        amount DECIMAL(10,2) DEFAULT 0,
        operation_time DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (table_id) REFERENCES tables(id) ON DELETE SET NULL
    )";
    
    if ($conn->query($sql) !== TRUE) {
        error_log("Tablo oluşturma hatası: " . $conn->error);
        return false;
    }
    
    // Auto-seed is disabled by default to avoid re-populating after bulk deletes.
    if (!defined('ENABLE_AUTO_SEED')) { define('ENABLE_AUTO_SEED', false); }

    return true;
}

// Tabloları güncelleme fonksiyonu
function updateTables($conn) {
    // Add order_index column to categories table if it doesn't exist
    $result = $conn->query("SHOW COLUMNS FROM categories LIKE 'order_index'");
    if ($result->num_rows == 0) {
        $conn->query("ALTER TABLE categories ADD COLUMN order_index INT(6) UNSIGNED DEFAULT 0");
    }
    
    // Add order_index column to products table if it doesn't exist
    $result = $conn->query("SHOW COLUMNS FROM products LIKE 'order_index'");
    if ($result->num_rows == 0) {
        $conn->query("ALTER TABLE products ADD COLUMN order_index INT(6) UNSIGNED DEFAULT 0");
    }
}

// Kategori opsiyonlarını o kategorideki tüm ürünlere uygula
function applyCategoryOptionsToProducts($conn, $category_id, $option_groups) {
    // Önce bu kategorideki tüm ürünleri al
    $products_stmt = $conn->prepare("SELECT id FROM products WHERE category_id = ?");
    $products_stmt->bind_param("i", $category_id);
    $products_stmt->execute();
    $products_result = $products_stmt->get_result();
    
    $product_ids = [];
    while ($product_row = $products_result->fetch_assoc()) {
        $product_ids[] = $product_row['id'];
    }
    $products_stmt->close();
    
    // Her ürün için opsiyon gruplarını ekle
    foreach ($product_ids as $product_id) {
        foreach ($option_groups as $group_id) {
            // Önce bu ürün için bu opsiyon grubunun zaten var olup olmadığını kontrol et
            $check_stmt = $conn->prepare("SELECT id FROM product_option_groups WHERE product_id = ? AND option_group_id = ?");
            $check_stmt->bind_param("ii", $product_id, $group_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows === 0) {
                // Yoksa ekle
                $insert_stmt = $conn->prepare("INSERT INTO product_option_groups (product_id, option_group_id) VALUES (?, ?)");
                $insert_stmt->bind_param("ii", $product_id, $group_id);
                $insert_stmt->execute();
                $insert_stmt->close();
            }
            
            $check_stmt->close();
        }
    }
    
    return count($product_ids);
}

// İlk kurulum için tabloları oluştur
createTables($conn);
updateTables($conn);

// Sipariş detaylarını getir
function getOrderDetails($conn, $table_id) {
    $stmt = $conn->prepare("
        SELECT o.id as order_id, o.total_amount, o.order_date, o.status, 
               oi.product_id, oi.quantity, oi.price, oi.options, p.name as product_name
        FROM orders o 
        LEFT JOIN order_items oi ON o.id = oi.order_id 
        LEFT JOIN products p ON oi.product_id = p.id 
        WHERE o.table_id = ? AND o.status = 'active'
    ");
    $stmt->bind_param("i", $table_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $orderDetails = [];
    while ($row = $result->fetch_assoc()) {
        $orderDetails[] = $row;
    }
    $stmt->close();
    
    return $orderDetails;
}

// Rapor verilerini oluşturan fonksiyon
function generateReportData($conn, $start_date, $end_date, $report_type) {
    try {
        
        // Önce gerekli tabloların var olup olmadığını kontrol et
        $orders_exists = $conn->query("SHOW TABLES LIKE 'orders'")->num_rows > 0;
        $order_items_exists = $conn->query("SHOW TABLES LIKE 'order_items'")->num_rows > 0;
        
        if (!$orders_exists) {
            // Orders tablosunu oluştur
            $create_orders = "CREATE TABLE IF NOT EXISTS orders (
                id INT AUTO_INCREMENT PRIMARY KEY,
                table_number INT NOT NULL,
                total_amount DECIMAL(10,2) NOT NULL,
                payment_method VARCHAR(50) DEFAULT 'cash',
                status VARCHAR(20) DEFAULT 'completed',
                order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            $conn->query($create_orders);
        }
        
        if (!$order_items_exists) {
            // Order items tablosunu oluştur
            $create_order_items = "CREATE TABLE IF NOT EXISTS order_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                order_id INT NOT NULL,
                product_name VARCHAR(255) NOT NULL,
                quantity INT NOT NULL,
                price DECIMAL(10,2) NOT NULL,
                FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
            )";
            $conn->query($create_order_items);
        }
        
        // Gerçek verileri veritabanından al
        // Toplam gelir
        $stmt = $conn->prepare("
            SELECT COALESCE(SUM(total_amount), 0) as total_revenue 
            FROM orders 
            WHERE DATE(order_date) BETWEEN ? AND ? AND status = 'completed'
        ");
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        $revenue_row = $result->fetch_assoc();
        $total_revenue = $revenue_row['total_revenue'];
        $stmt->close();
        
        // Toplam sipariş sayısı
        $stmt = $conn->prepare("
            SELECT COUNT(*) as total_orders 
            FROM orders 
            WHERE DATE(order_date) BETWEEN ? AND ? AND status = 'completed'
        ");
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        $orders_row = $result->fetch_assoc();
        $total_orders = $orders_row['total_orders'];
        $stmt->close();
        
        // Toplam satılan ürün sayısı
        $stmt = $conn->prepare("
            SELECT COALESCE(SUM(oi.quantity), 0) as total_products 
            FROM order_items oi 
            JOIN orders o ON oi.order_id = o.id 
            WHERE DATE(o.order_date) BETWEEN ? AND ? AND o.status = 'completed'
        ");
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        $products_row = $result->fetch_assoc();
        $total_products = $products_row['total_products'];
        $stmt->close();
        
        // Ortalama sipariş değeri
        $avg_order_value = $total_orders > 0 ? round($total_revenue / $total_orders, 2) : 0;
        
        $revenue_labels = [];
        $revenue_data = [];
        
        // Son 7 günün verilerini al
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $revenue_labels[] = date('d/m', strtotime($date));
            
            $stmt = $conn->prepare("
                SELECT COALESCE(SUM(total_amount), 0) as daily_revenue 
                FROM orders 
                WHERE DATE(order_date) = ? AND status = 'completed'
            ");
            $stmt->bind_param("s", $date);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $revenue_data[] = (float)$row['daily_revenue'];
            $stmt->close();
        }
        
        // En çok satan ürünler
        $stmt = $conn->prepare("
            SELECT oi.product_name, SUM(oi.quantity) as total_sold, SUM(oi.quantity * oi.price) as revenue
            FROM order_items oi 
            JOIN orders o ON oi.order_id = o.id 
            WHERE DATE(o.order_date) BETWEEN ? AND ? AND o.status = 'completed'
            GROUP BY oi.product_name 
            ORDER BY total_sold DESC 
            LIMIT 5
        ");
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        $top_products = [];
        while ($row = $result->fetch_assoc()) {
            $top_products[] = $row;
        }
        $stmt->close();
        
        // Ödeme yöntemlerine göre dağılım
        $stmt = $conn->prepare("
            SELECT payment_method, COUNT(*) as count, SUM(total_amount) as amount
            FROM orders 
            WHERE DATE(order_date) BETWEEN ? AND ? AND status = 'completed'
            GROUP BY payment_method
        ");
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        $payment_methods = [];
        while ($row = $result->fetch_assoc()) {
            $payment_methods[] = $row;
        }
        $stmt->close();
        
        // Saatlik satış dağılımı
        $hourly_sales = [];
        for ($hour = 0; $hour < 24; $hour++) {
            $hour_start = sprintf('%02d:00:00', $hour);
            $hour_end = sprintf('%02d:59:59', $hour);
            
            $stmt = $conn->prepare("
                SELECT COALESCE(SUM(total_amount), 0) as hourly_revenue
                FROM orders 
                WHERE DATE(order_date) BETWEEN ? AND ? 
                AND TIME(order_date) BETWEEN ? AND ?
                AND status = 'completed'
            ");
            $stmt->bind_param("ssss", $start_date, $end_date, $hour_start, $hour_end);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $hourly_sales[] = (float)$row['hourly_revenue'];
            $stmt->close();
        }
        
        return [
            'success' => true,
            'data' => [
                'total_revenue' => $total_revenue,
                'total_orders' => $total_orders,
                'total_products' => $total_products,
                'avg_order_value' => $avg_order_value,
                'revenue_labels' => $revenue_labels,
                'revenue_data' => $revenue_data,
                'top_products' => $top_products,
                'payment_methods' => $payment_methods,
                'hourly_sales' => $hourly_sales
            ]
        ];
        
    } catch (Exception $e) {
        error_log("Rapor oluşturma hatası: " . $e->getMessage());
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Raporu veritabanına kaydet
function saveReport($conn, $name, $type, $start_date, $end_date, $data, $total_revenue) {
    $json_data = json_encode($data);
    
    $stmt = $conn->prepare("
        INSERT INTO reports (name, type, start_date, end_date, data, total_revenue)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sssssd", $name, $type, $start_date, $end_date, $json_data, $total_revenue);
    
    if ($stmt->execute()) {
        $report_id = $stmt->insert_id;
        $stmt->close();
        return $report_id;
    } else {
        error_log("Rapor kaydetme hatası: " . $stmt->error);
        $stmt->close();
        return false;
    }
}

// Kaydedilmiş raporları getir
function getSavedReports($conn) {
    $result = $conn->query("
        SELECT id, name, type, start_date, end_date, total_revenue, created_at
        FROM reports 
        ORDER BY created_at DESC
        LIMIT 20
    ");
    
    $reports = [];
    while ($row = $result->fetch_assoc()) {
        $reports[] = $row;
    }
    
    return $reports;
}

// Belirli bir raporu getir
function getReport($conn, $report_id) {
    $stmt = $conn->prepare("
        SELECT id, name, type, start_date, end_date, data, total_revenue, created_at
        FROM reports 
        WHERE id = ?
    ");
    $stmt->bind_param("i", $report_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $report = $result->fetch_assoc();
        $report['data'] = json_decode($report['data'], true);
        $stmt->close();
        return $report;
    } else {
        $stmt->close();
        return null;
    }
}

// Raporu sil
function deleteReport($conn, $report_id) {
    $stmt = $conn->prepare("DELETE FROM reports WHERE id = ?");
    $stmt->bind_param("i", $report_id);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// Kasa açma/kapatma işlemlerini kaydet
function saveCashDrawerOperation($conn, $table_id, $operation_type, $amount) {
    $stmt = $conn->prepare("
        INSERT INTO cash_drawer_operations (table_id, operation_type, amount)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param("iss", $table_id, $operation_type, $amount);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// Son kasa işlemlerini getir
function getCashDrawerOperations($conn, $limit = 10) {
    $stmt = $conn->prepare("
        SELECT cdo.*, t.number as table_number
        FROM cash_drawer_operations cdo
        LEFT JOIN tables t ON cdo.table_id = t.id
        ORDER BY cdo.operation_time DESC
        LIMIT ?
    ");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $operations = [];
    while ($row = $result->fetch_assoc()) {
        $operations[] = $row;
    }
    $stmt->close();
    
    return $operations;
}

// Kasa durumunu kontrol et (açık/kapalı)
function getCashDrawerStatus($conn) {
    $result = $conn->query("
        SELECT operation_type 
        FROM cash_drawer_operations 
        ORDER BY operation_time DESC 
        LIMIT 1
    ");
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['operation_type'] === 'open';
    }
    
    return false; // Varsayılan olarak kapalı
}

// Printer ayarlarını al
function getPrinterSettings($conn) {
    $result = $conn->query("SELECT * FROM printer_settings ORDER BY id DESC LIMIT 1");
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    } else {
        // Varsayılan ayarları döndür
        return [
            'company_name' => 'Restaurant Adı',
            'company_name_font' => 'Arial, sans-serif',
            'company_name_size' => 18,
            'company_address' => 'Örnek Mah. Örnek Cad. No:123',
            'company_phone' => '0 (212) 345 67 89',
            'footer_text' => 'Teşekkür Ederiz, Yine Bekleriz!',
            'receipt_width' => 80,
            'logo_alignment' => 'center',
            'logo_path' => null,
            'logo_width' => 200,
            'logo_height' => 100,
            'order_items_font_size' => 12,
            'printer_ip' => null,
            'printer_port' => 9100,
            'connection_type' => 'wifi',
            'bluetooth_mac' => null,
            'ethernet_ip' => null,
            'ethernet_port' => 9100,
            'printer_name' => null
        ];
    }
}

// Printer ayarlarını kaydet
function savePrinterSettings($conn, $settings) {
    // Önce mevcut kaydı kontrol et
    $result = $conn->query("SELECT id FROM printer_settings ORDER BY id DESC LIMIT 1");
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $id = $row['id'];
        
        // Güncelleme sorgusu
        $sql = "UPDATE printer_settings SET 
            company_name = ?,
            company_name_font = ?,
            company_name_size = ?,
            company_address = ?,
            company_phone = ?,
            footer_text = ?,
            receipt_width = ?,
            logo_alignment = ?,
            logo_path = ?,
            logo_width = ?,
            logo_height = ?,
            order_items_font_size = ?,
            printer_ip = ?,
            printer_port = ?,
            connection_type = ?,
            bluetooth_mac = ?,
            ethernet_ip = ?,
            ethernet_port = ?,
            printer_name = ?,
            updated_at = CURRENT_TIMESTAMP
            WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssisssisiiisisssssi",
            $settings['company_name'],
            $settings['company_name_font'],
            $settings['company_name_size'],
            $settings['company_address'],
            $settings['company_phone'],
            $settings['footer_text'],
            $settings['receipt_width'],
            $settings['logo_alignment'],
            $settings['logo_path'],
            $settings['logo_width'],
            $settings['logo_height'],
            $settings['order_items_font_size'],
            $settings['printer_ip'],
            $settings['printer_port'],
            $settings['connection_type'],
            $settings['bluetooth_mac'],
            $settings['ethernet_ip'],
            $settings['ethernet_port'],
            $settings['printer_name'],
            $id
        );
    } else {
        // Yeni ekleme sorgusu
        $sql = "INSERT INTO printer_settings (
            company_name, company_name_font, company_name_size, company_address, company_phone, 
            footer_text, receipt_width, logo_alignment, logo_path, logo_width, logo_height,
            order_items_font_size, printer_ip, printer_port, connection_type, bluetooth_mac, 
            ethernet_ip, ethernet_port, printer_name
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssisssisiiisissssss",
            $settings['company_name'],
            $settings['company_name_font'],
            $settings['company_name_size'],
            $settings['company_address'],
            $settings['company_phone'],
            $settings['footer_text'],
            $settings['receipt_width'],
            $settings['logo_alignment'],
            $settings['logo_path'],
            $settings['logo_width'],
            $settings['logo_height'],
            $settings['order_items_font_size'],
            $settings['printer_ip'],
            $settings['printer_port'],
            $settings['connection_type'],
            $settings['bluetooth_mac'],
            $settings['ethernet_ip'],
            $settings['ethernet_port'],
            $settings['printer_name']
        );
    }
    
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// Logo yükleme fonksiyonu
function uploadLogo($file) {
    $target_dir = __DIR__ . "/uploads/";
    
    // Uploads dizini yoksa oluştur
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    
    $target_file = $target_dir . basename($file["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Dosya boyutu kontrolü (max 2MB)
    if ($file["size"] > 2000000) {
        return ['success' => false, 'error' => 'Dosya boyutu 2MB\'dan büyük olamaz.'];
    }
    
    // İzin verilen dosya formatları
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($imageFileType, $allowed_types)) {
        return ['success' => false, 'error' => 'Sadece JPG, JPEG, PNG & GIF dosyaları yüklenebilir.'];
    }
    
    // Dosyayı yükle
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ['success' => true, 'file_path' => 'uploads/' . basename($file["name"])];
    } else {
        return ['success' => false, 'error' => 'Dosya yüklenirken bir hata oluştu.'];
    }
}

// Logo silme fonksiyonu
function deleteLogo($file_path) {
    if (file_exists(__DIR__ . '/' . $file_path) && is_file(__DIR__ . '/' . $file_path)) {
        return unlink(__DIR__ . '/' . $file_path);
    }
    return false;
}
?>