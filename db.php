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
    
    // Printer ayarları tablosu
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
// db.php dosyasına bu fonksiyonu ekleyin (updateTables fonksiyonunun yanına)
function updateProductTable($conn) {
    // products tablosunda order_index sütunu var mı kontrol et
    $result = $conn->query("SHOW COLUMNS FROM products LIKE 'order_index'");
    if ($result->num_rows == 0) {
        // order_index sütunu yoksa ekle
        $sql = "ALTER TABLE products ADD COLUMN order_index INT(6) UNSIGNED DEFAULT 0";
        
        if ($conn->query($sql)) {
            error_log("products tablosuna order_index sütunu eklendi.");
            return true;
        } else {
            error_log("products tablosuna order_index eklenirken hata: " . $conn->error);
            return false;
        }
    }
    return true;
}


function updateTables($conn) {
    // Add order_index column to categories table if it doesn't exist
    $result = $conn->query("SHOW COLUMNS FROM categories LIKE 'order_index'");
    if ($result->num_rows == 0) {
        $conn->query("ALTER TABLE categories ADD COLUMN order_index INT(6) UNSIGNED DEFAULT 0");
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
updateProductTable($conn);
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
            $daily_row = $result->fetch_assoc();
            $revenue_data[] = floatval($daily_row['daily_revenue']);
            $stmt->close();
        }
        
        // Kategori verileri (örnek veriler)
        $category_labels = ['Ana Yemekler', 'İçecekler', 'Tatlılar', 'Başlangıçlar'];
        $category_data = [40, 25, 20, 15];
        
        // Ödeme yöntemleri
        $stmt = $conn->prepare("
            SELECT 
                payment_method,
                COUNT(*) as count,
                SUM(total_amount) as total
            FROM orders 
            WHERE DATE(order_date) BETWEEN ? AND ? AND status = 'completed'
            GROUP BY payment_method
        ");
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $payment_labels = [];
        $payment_data = [];
        while ($row = $result->fetch_assoc()) {
            $payment_labels[] = $row['payment_method'] == 'cash' ? 'Nakit' : 'Kart';
            $payment_data[] = floatval($row['total']);
        }
        $stmt->close();
        
        // Eğer ödeme verisi yoksa örnek veri ekle
        if (empty($payment_labels)) {
            $payment_labels = ['Nakit', 'Kart'];
            $payment_data = [0, 0];
        }
        
        $table_labels = ['Masa 1', 'Masa 2', 'Masa 3', 'Masa 4', 'Masa 5'];
        $table_occupied = [2, 1, 3, 0, 2];
        $table_available = [1, 2, 0, 3, 1];
        
        $cash_drawer_labels = [];
        $cash_drawer_data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('d/m', strtotime("-$i days"));
            $cash_drawer_labels[] = $date;
            $cash_drawer_data[] = rand(5, 25); // Örnek veri
        }
        
        // Ürün performansı
        $stmt = $conn->prepare("
            SELECT 
                oi.product_name,
                SUM(oi.quantity) as total_sold,
                SUM(oi.quantity * oi.price) as total_revenue
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            WHERE DATE(o.order_date) BETWEEN ? AND ? AND o.status = 'completed'
            GROUP BY oi.product_name
            ORDER BY total_sold DESC
            LIMIT 10
        ");
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $popularity = 'Orta';
            if ($row['total_sold'] > 50) $popularity = 'Çok Yüksek';
            else if ($row['total_sold'] > 20) $popularity = 'Yüksek';
            else if ($row['total_sold'] > 10) $popularity = 'Orta';
            else $popularity = 'Düşük';
            
            $products[] = [
                'name' => $row['product_name'],
                'sales' => $row['total_sold'],
                'revenue' => number_format($row['total_revenue'], 2),
                'rating' => '4.5', // Örnek değer
                'popularity' => $popularity
            ];
        }
        $stmt->close();
        
        // Günlük satış verileri
        $stmt = $conn->prepare("
            SELECT 
                DATE(order_date) as sale_date,
                COUNT(*) as order_count,
                SUM(total_amount) as daily_revenue,
                AVG(total_amount) as avg_order,
                SUM(CASE WHEN payment_method = 'cash' THEN total_amount ELSE 0 END) as cash_total,
                SUM(CASE WHEN payment_method = 'card' THEN total_amount ELSE 0 END) as card_total
            FROM orders 
            WHERE DATE(order_date) BETWEEN ? AND ? AND status = 'completed'
            GROUP BY DATE(order_date)
            ORDER BY sale_date DESC
            LIMIT 10
        ");
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $sales = [];
        while ($row = $result->fetch_assoc()) {
            $sales[] = [
                'date' => date('d/m/Y', strtotime($row['sale_date'])),
                'orders' => $row['order_count'],
                'revenue' => number_format($row['daily_revenue'], 2),
                'avgOrder' => number_format($row['avg_order'], 2),
                'cash' => number_format($row['cash_total'], 2),
                'card' => number_format($row['card_total'], 2),
                'topProduct' => 'Karışık Pizza' // Örnek değer
            ];
        }
        $stmt->close();
        
        // Veri yapısını oluştur
        $data = [
            'stats' => [
                'totalRevenue' => (float)$total_revenue,
                'totalOrders' => (int)$total_orders,
                'totalProductsSold' => (int)$total_products,
                'avgOrderValue' => (float)$avg_order_value
            ],
            'charts' => [
                'revenue' => [
                    'labels' => $revenue_labels,
                    'data' => $revenue_data
                ],
                'categories' => [
                    'labels' => $category_labels,
                    'data' => $category_data
                ],
                'tableOccupancy' => [
                    'labels' => $table_labels,
                    'occupied' => $table_occupied,
                    'available' => $table_available
                ],
                'paymentMethods' => [
                    'labels' => $payment_labels,
                    'data' => $payment_data
                ],
                'cashDrawer' => [
                    'labels' => $cash_drawer_labels,
                    'data' => $cash_drawer_data
                ]
            ],
            'tables' => [
                'products' => $products,
                'sales' => $sales
            ]
        ];
        
        return $data;
        
    } catch (Exception $e) {
        error_log("Rapor oluşturma hatası: " . $e->getMessage());
        
        // Hata durumunda boş bir yapı döndür
        return [
            'stats' => [
                'totalRevenue' => 0,
                'totalOrders' => 0,
                'totalProductsSold' => 0,
                'avgOrderValue' => 0
            ],
            'charts' => [
                'revenue' => ['labels' => [], 'data' => []],
                'categories' => ['labels' => [], 'data' => []],
                'tableOccupancy' => ['labels' => [], 'occupied' => [], 'available' => []],
                'paymentMethods' => ['labels' => [], 'data' => []],
                'cashDrawer' => ['labels' => [], 'data' => []]
            ],
            'tables' => [
                'products' => [],
                'sales' => []
            ]
        ];
    }
}

// AJAX isteklerini işleme
if (!defined('DB_DISABLE_API') && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
$action = $_POST['action'] ?? $_GET['action'] ?? null;
if ($action !== null) {
    header('Content-Type: application/json');
    
// In db.php for add_order action
if ($action === 'add_order') {
    $table_id = intval($_POST['table_id']);
    $items = json_decode($_POST['items'], true);
    $payment_method = $_POST['payment_method'];
    $amount_paid = isset($_POST['amount_paid']) ? floatval($_POST['amount_paid']) : 0;
    
    // Calculate total from items
    $total = 0;
    foreach ($items as $item) {
        $total += floatval($item['price']) * intval($item['quantity']);
    }
    $total = round($total, 2); // Round to 2 decimal places
    
    // Process payment and save order...
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Order added successfully',
        'total' => $total,
        'amount_paid' => $amount_paid,
        'change' => $amount_paid - $total
    ]);
    exit;
}

// === Helpers for bulk delete ===
if (!function_exists('tableExists')) {
    function tableExists($conn, $table) {
        $tbl = $conn->real_escape_string($table);
        $res = $conn->query("SHOW TABLES LIKE '{$tbl}'");
        return $res && $res->num_rows > 0;
    }
}
if (!function_exists('deleteAllIfExists')) {
    function deleteAllIfExists($conn, $table) {
        if (tableExists($conn, $table)) {
            if (!$conn->query("DELETE FROM `{$table}`")) {
                throw new Exception($conn->error);
            }
        }
    }
}
switch ($action) {
		
        case 'add_table':
            if (isset($_POST['name']) && isset($_POST['number'])) {
                $tableName = trim($_POST['name']);
                $tableNumber = intval($_POST['number']);
                
                // Giriş validasyonu
                if (empty($tableNumber) || $tableNumber <= 0) {
                    echo json_encode(['success' => false, 'error' => 'Geçersiz masa numarası']);
                    break;
                }
                
                // Masa numarası zaten var mı kontrol et
                $check_stmt = $conn->prepare("SELECT id FROM tables WHERE number = ?");
                $check_stmt->bind_param("i", $tableNumber);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows > 0) {
                    echo json_encode(['success' => false, 'error' => 'Bu masa numarası zaten mevcut']);
                    $check_stmt->close();
                    break;
                }
                $check_stmt->close();

                $stmt = $conn->prepare("INSERT INTO tables (name, number, status) VALUES (?, ?, 'available')");
                $stmt->bind_param("si", $tableName, $tableNumber);

                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'id' => $stmt->insert_id]);
                } else {
                    echo json_encode(['success' => false, 'error' => $conn->error]);
                }
                $stmt->close();
            } else {
                echo json_encode(['success' => false, 'error' => 'Masa adı veya numarası eksik']);
            }
            break;
            
        case 'delete_table':
            if (isset($_GET['id'])) {
                $table_id = intval($_GET['id']);
                
                // Önce masaya ait aktif sipariş var mı kontrol et
                $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE table_id = ? AND status = 'active'");
                $check_stmt->bind_param("i", $table_id);
                $check_stmt->execute();
                $result = $check_stmt->get_result();
                $row = $result->fetch_assoc();
                $check_stmt->close();
                
                if ($row['count'] > 0) {
                    echo json_encode(['success' => false, 'error' => 'Bu masada aktif sipariş var. Önce siparişi tamamlayın.']);
                } else {
                    $stmt = $conn->prepare("DELETE FROM tables WHERE id = ?");
                    $stmt->bind_param("i", $table_id);
                    
                    if ($stmt->execute()) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['success' => false, 'error' => $conn->error]);
                    }
                    $stmt->close();
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Eksik parametre']);
            }
            break;
            
        case 'get_tables':
            $result = $conn->query("SELECT * FROM tables ORDER BY number");
            $tables = [];
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $tables[] = $row;
                }
            }
            echo json_encode($tables);
            break;
            // db.php dosyasına aşağıdaki case'i ekleyin
case 'delete_all_tables':
    try {
        // İşlemi transaction içinde yapalım
        $conn->begin_transaction();

        // Önce aktif siparişlerin sipariş detaylarını sil
        $delete_order_items = $conn->prepare("
            DELETE oi FROM order_items oi 
            INNER JOIN orders o ON oi.order_id = o.id 
            WHERE o.status = 'active'
        ");
        $delete_order_items->execute();
        $delete_order_items->close();

        // Aktif siparişleri sil
        $delete_orders = $conn->prepare("DELETE FROM orders WHERE status = 'active'");
        $delete_orders->execute();
        $delete_orders->close();

        // Tüm masaları sil
        $delete_tables = $conn->prepare("DELETE FROM tables");
        $delete_tables->execute();
        $delete_tables->close();

        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    break;
case 'get_categories':
    $result = $conn->query("SELECT * FROM categories ORDER BY order_index ASC, name ASC");
    $categories = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }
    echo json_encode($categories);
    break;

        case 'get_categories_ordered':
            $result = $conn->query("SELECT * FROM categories ORDER BY order_index ASC, name ASC");
            $categories = [];
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $categories[] = $row;
                }
            }
            echo json_encode($categories);
            break;
            
case 'get_products':
    if (isset($_GET['category_id'])) {
        $category_id = intval($_GET['category_id']);
        $stmt = $conn->prepare("SELECT * FROM products WHERE category_id = ? ORDER BY order_index ASC, name ASC");
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        echo json_encode($products);
        $stmt->close();
    } else {
        echo json_encode([]);
    }
    break;
 case 'update_product_order':
    if (isset($_POST['product_orders'])) {
        $product_orders = json_decode($_POST['product_orders'], true);
        
        if ($product_orders && is_array($product_orders)) {
            $conn->begin_transaction();
            try {
                $stmt = $conn->prepare("UPDATE products SET order_index = ? WHERE id = ?");
                
                foreach ($product_orders as $product_data) {
                    if (isset($product_data['id']) && isset($product_data['order_index'])) {
                        $order_index = intval($product_data['order_index']);
                        $product_id = intval($product_data['id']);
                        
                        $stmt->bind_param("ii", $order_index, $product_id);
                        $stmt->execute();
                    }
                }
                
                $conn->commit();
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                $conn->rollback();
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'error' => 'Geçersiz veri formatı']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Eksik parametreler']);
    }
    break;           
        case 'get_all_products':
            $result = $conn->query("
                SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                ORDER BY c.name, p.name
            ");
            $products = [];
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $products[] = $row;
                }
            }
            echo json_encode($products);
            break;
            
        case 'get_option_groups':
            $result = $conn->query("SELECT * FROM option_groups ORDER BY name");
            $optionGroups = [];
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $optionGroups[] = $row;
                }
            }
            echo json_encode($optionGroups);
            break;
            
        case 'get_options':
            if (isset($_GET['group_id'])) {
                $group_id = intval($_GET['group_id']);
                $stmt = $conn->prepare("SELECT * FROM options WHERE group_id = ? ORDER BY name");
                $stmt->bind_param("i", $group_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                $options = [];
                while ($row = $result->fetch_assoc()) {
                    $options[] = $row;
                }
                echo json_encode($options);
                $stmt->close();
            } else {
                echo json_encode([]);
            }
            break;
      case 'add_options_to_category_and_products':
    if (isset($_POST['category_id']) && isset($_POST['option_groups'])) {
        $category_id = intval($_POST['category_id']);
        $option_groups = json_decode($_POST['option_groups'], true);
        
        if (!is_array($option_groups) || empty($option_groups)) {
            echo json_encode(['success' => false, 'error' => 'Geçersiz opsiyon grubu verisi']);
            break;
        }
        
        // Önce kategoriye opsiyon gruplarını ekle
        $success = true;
        $error = '';
        $processed = 0;
        
        // ÖNCE: Bu kategoriye ait tüm mevcut opsiyon gruplarını temizle
        $delete_stmt = $conn->prepare("DELETE FROM category_option_groups WHERE category_id = ?");
        $delete_stmt->bind_param("i", $category_id);
        if (!$delete_stmt->execute()) {
            echo json_encode(['success' => false, 'error' => 'Temizleme hatası: ' . $delete_stmt->error]);
            $delete_stmt->close();
            break;
        }
        $delete_stmt->close();
        
        // Tüm seçili opsiyon gruplarını kategoriye ekle
        foreach ($option_groups as $group_id) {
            $group_id = intval($group_id);
            
            $stmt = $conn->prepare("INSERT INTO category_option_groups (category_id, option_group_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $category_id, $group_id);
            
            if (!$stmt->execute()) {
                $success = false;
                $error = $conn->error;
                break;
            }
            $stmt->close();
            $processed++;
        }
        
        // Şimdi bu kategorideki tüm ürünlere bu opsiyon gruplarını uygula
        $affected_products = 0;
        if ($success) {
            $affected_products = applyCategoryOptionsToProducts($conn, $category_id, $option_groups);
        }
        
        echo json_encode([
            'success' => $success, 
            'error' => $error,
            'processed' => $processed,
            'affected_products' => $affected_products,
            'message' => $success ? "Kategoriye ve {$affected_products} ürüne {$processed} opsiyon grubu eklendi" : ""
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Eksik parametre']);
    }
    break;      
// db.php dosyasında add_order case'ini güncelle
case 'add_order':
    error_log("add_order action called");
    error_log("POST data: " . print_r($_POST, true));
    
    if (isset($_POST['table_id']) && isset($_POST['items']) && isset($_POST['total_amount'])) {
        $table_id = intval($_POST['table_id']);
        $items = json_decode($_POST['items'], true);
        $total_amount = floatval($_POST['total_amount']);
        $payment_method = $_POST['payment_method'] ?? 'cash';
        $amount_paid = isset($_POST['amount_paid']) ? floatval($_POST['amount_paid']) : 0;
        $change_amount = isset($_POST['change_amount']) ? floatval($_POST['change_amount']) : 0;
        
        error_log("Parsed data - table_id: $table_id, total_amount: $total_amount");
        error_log("Items: " . print_r($items, true));
        
        // Siparişi kaydet
        $stmt = $conn->prepare("INSERT INTO orders (table_id, total_amount, payment_method, amount_paid, change_amount) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("idssd", $table_id, $total_amount, $payment_method, $amount_paid, $change_amount);
        
        if ($stmt->execute()) {
            $order_id = $conn->insert_id;
            error_log("Order created with ID: $order_id");
            
            // Sipariş öğelerini kaydet
            foreach ($items as $item) {
                $product_id = intval($item['product_id']);
                $quantity = intval($item['quantity']);
                $price = floatval($item['price']);
                $options = isset($item['options']) ? json_encode($item['options']) : null;
                
                error_log("Adding item: product_id=$product_id, quantity=$quantity, price=$price");
                
                $stmt2 = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, options) VALUES (?, ?, ?, ?, ?)");
                $stmt2->bind_param("iiids", $order_id, $product_id, $quantity, $price, $options);
                $stmt2->execute();
                $stmt2->close();
            }
            
            // Masanın durumunu güncelle
            $stmt3 = $conn->prepare("UPDATE tables SET status = 'available' WHERE id = ?");
            $stmt3->bind_param("i", $table_id);
            $stmt3->execute();
            $stmt3->close();
            
            echo json_encode([
                'success' => true, 
                'order_id' => $order_id,
                'total' => $total_amount,
                'amount_paid' => $amount_paid,
                'change' => $change_amount
            ]);
        } else {
            error_log("Order creation failed: " . $conn->error);
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        $stmt->close();
    } else {
        error_log("Missing parameters in add_order");
        echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    }
    break;
            
case 'add_category':
    if (isset($_POST['name'])) {
        $name = trim($_POST['name']);
        
        // Otomatik sıra numarası belirle: en yüksek sıra numarası + 1
        $order_index = 0;
        $result = $conn->query("SELECT MAX(order_index) as max_order FROM categories");
        if ($result && $row = $result->fetch_assoc()) {
            $order_index = $row['max_order'] + 1;
        }
        
        $stmt = $conn->prepare("INSERT INTO categories (name, order_index) VALUES (?, ?)");
        $stmt->bind_param("si", $name, $order_index);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'category_id' => $conn->insert_id]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Kategori adı gerekli']);
    }
    break;

case 'get_product_content':
    if (isset($_GET['product_id'])) {
        $product_id = intval($_GET['product_id']);
        $stmt = $conn->prepare("SELECT icerik FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $row = $result->fetch_assoc()) {
            echo json_encode(['success' => true, 'content' => $row['icerik']]);
        } else {
            echo json_encode(['success' => false, 'content' => '']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'content' => '']);
    }
    break;           
        case 'add_product':
            if (isset($_POST['category_id']) && isset($_POST['name']) && isset($_POST['price'])) {
                $category_id = intval($_POST['category_id']);
                $name = trim($_POST['name']);
                $price = floatval($_POST['price']);
                $available = isset($_POST['available']) ? 1 : 0;
                $icerik = isset($_POST['icerik']) ? trim($_POST['icerik']) : '';
                
                $stmt = $conn->prepare("INSERT INTO products (category_id, name, price, available, icerik) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("isdis", $category_id, $name, $price, $available, $icerik);
                
                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'product_id' => $conn->insert_id]);
                } else {
                    echo json_encode(['success' => false, 'error' => $conn->error]);
                }
                $stmt->close();
            } else {
                echo json_encode(['success' => false, 'error' => 'Eksik parametreler']);
            }
            break;
            
        case 'add_option_group':
            if (isset($_POST['name'])) {
                $name = trim($_POST['name']);
                $is_required = isset($_POST['is_required']) ? intval($_POST['is_required']) : 0;
                $min_selection = isset($_POST['min_selection']) ? intval($_POST['min_selection']) : 0;
                $max_selection = isset($_POST['max_selection']) ? intval($_POST['max_selection']) : 1;
                
                $stmt = $conn->prepare("INSERT INTO option_groups (name, is_required, min_selection, max_selection) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("siii", $name, $is_required, $min_selection, $max_selection);
                
                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'id' => $conn->insert_id]);
                } else {
                    echo json_encode(['success' => false, 'error' => $stmt->error]);
                }
                $stmt->close();
            } else {
                echo json_encode(['success' => false, 'error' => 'Opsiyon grubu adı gerekli']);
            }
            break;
            
        case 'add_option':
            if (isset($_POST['group_id']) && isset($_POST['name'])) {
                $group_id = intval($_POST['group_id']);
                $name = trim($_POST['name']);
                $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
                
                $stmt = $conn->prepare("INSERT INTO options (group_id, name, price) VALUES (?, ?, ?)");
                $stmt->bind_param("isd", $group_id, $name, $price);
                
                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'id' => $stmt->insert_id]);
                } else {
                    echo json_encode(['success' => false, 'error' => $conn->error]);
                }
                $stmt->close();
            } else {
                echo json_encode(['success' => false, 'error' => 'Eksik parametre']);
            }
            break;
            
        case 'add_options_to_product':
            if (isset($_POST['product_id']) && isset($_POST['option_groups'])) {
                $product_id = intval($_POST['product_id']);
                $option_groups_raw = $_POST['option_groups'] ?? [];
        if (is_string($option_groups_raw)) {
            $decoded = json_decode($option_groups_raw, true);
            $option_groups = is_array($decoded) ? $decoded : [];
        } else {
            $option_groups = $option_groups_raw;
        }
                
                // Önce bu ürüne ait tüm opsiyon gruplarını sil
                $stmt = $conn->prepare("DELETE FROM product_option_groups WHERE product_id = ?");
                $stmt->bind_param("i", $product_id);
                $stmt->execute();
                $stmt->close();
                
                // Yeni opsiyon gruplarını ekle
                $success = true;
                $error = '';
                
                foreach ($option_groups as $group_id) {
                    $stmt = $conn->prepare("INSERT INTO product_option_groups (product_id, option_group_id) VALUES (?, ?)");
                    $stmt->bind_param("ii", $product_id, $group_id);
                    
                    if (!$stmt->execute()) {
                        $success = false;
                        $error = $conn->error;
                        break;
                    }
                    $stmt->close();
                }
                
                echo json_encode(['success' => $success, 'error' => $error]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Eksik parametre']);
            }
            break;
            
        case 'add_options_to_products':
            if (isset($_POST['product_ids']) && isset($_POST['option_groups'])) {
                $product_ids = json_decode($_POST['product_ids']);
                $option_groups_raw = $_POST['option_groups'] ?? [];
        if (is_string($option_groups_raw)) {
            $decoded = json_decode($option_groups_raw, true);
            $option_groups = is_array($decoded) ? $decoded : [];
        } else {
            $option_groups = $option_groups_raw;
        }
                
                $success = true;
                $error = '';
                $processed = 0;
                
                // Tüm seçili ürünler için işlem yap
                foreach ($product_ids as $product_id) {
                    // Tüm seçili opsiyon gruplarını ekle
                    foreach ($option_groups as $group_id) {
                        // Önce aynı kombinasyonun zaten var olup olmadığını kontrol et
                        $check_stmt = $conn->prepare("SELECT id FROM product_option_groups WHERE product_id = ? AND option_group_id = ?");
                        $check_stmt->bind_param("ii", $product_id, $group_id);
                        $check_stmt->execute();
                        $check_result = $check_stmt->get_result();
                        
                        if ($check_result->num_rows === 0) {
                            // Yeni opsiyon grubunu ekle
                            $stmt = $conn->prepare("INSERT INTO product_option_groups (product_id, option_group_id) VALUES (?, ?)");
                            $stmt->bind_param("ii", $product_id, $group_id);
                            
                            if (!$stmt->execute()) {
                                $success = false;
                                $error = $conn->error;
                                break 2; // Hem iç hem dış döngüden çık
                            }
                            $stmt->close();
                        }
                        $check_stmt->close();
                    }
                    
                    $processed++;
                }
                
                echo json_encode([
                    'success' => $success, 
                    'error' => $error,
                    'processed' => $processed,
                    'message' => $success ? "{$processed} ürüne " . count($option_groups) . " opsiyon grubu eklendi" : ""
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Eksik parametre']);
            }
            break;
            
case 'update_table_status':
    if (isset($_POST['table_id']) && isset($_POST['status'])) {
        $table_id = intval($_POST['table_id']);
        $status = $_POST['status'];
        
        $stmt = $conn->prepare("UPDATE tables SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $table_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    }
    break;
            
        case 'get_printer_settings':
            $conn->query("CREATE TABLE IF NOT EXISTS printer_settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                printer_name VARCHAR(255) DEFAULT 'Epson_TM-m30II',
                printer_ip VARCHAR(45) DEFAULT '192.168.0.23',
                printer_port INT DEFAULT 9100,
                print_mode VARCHAR(20) DEFAULT 'browser',
                company_name VARCHAR(255) DEFAULT 'Restaurant Adı',
                company_address TEXT DEFAULT 'Örnek Mah. Örnek Cad. No:123',
                company_phone VARCHAR(50) DEFAULT '0 (212) 345 67 89',
                footer_text TEXT DEFAULT 'Teşekkür Ederiz, Yine Bekleriz!',
                receipt_width INT DEFAULT 80,
                logo_alignment VARCHAR(10) DEFAULT 'center',
                logo_path VARCHAR(500) DEFAULT NULL,
                logo_width INT DEFAULT 200,
                logo_height INT DEFAULT 100,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )");
            
            $result = $conn->query("SELECT * FROM printer_settings ORDER BY id DESC LIMIT 1");
            if ($result && $result->num_rows > 0) {
                $settings = $result->fetch_assoc();
                echo json_encode(['success' => true, 'settings' => $settings]);
            } else {
                // Varsayılan ayarlar
                echo json_encode(['success' => true, 'settings' => [
                    'printer_name' => 'Epson_TM-m30II',
                    'printer_ip' => '',
                    'printer_port' => '9100',
                    'print_mode' => 'browser',
                    'company_name' => 'Restaurant Adı',
                    'company_address' => 'Örnek Mah. Örnek Cad. No:123',
                    'company_phone' => '0 (212) 345 67 89',
                    'footer_text' => 'Teşekkür Ederiz, Yine Bekleriz!',
                    'receipt_width' => 80,
                    'logo_alignment' => 'center',
                    'logo_path' => null,
                    'logo_width' => 200,
                    'logo_height' => 100
                ]]);
            }
            break;
            
case 'save_printer_settings':
    error_log("save_printer_settings called");
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            // Gelen veriyi al
            $content_type = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
            
            if (strpos($content_type, 'application/json') !== false) {
                $input_data = json_decode(file_get_contents('php://input'), true);
            } else {
                $input_data = $_POST;
            }
            
            if (!$input_data) {
                throw new Exception('Geçersiz veri formatı');
            }
            
            // Değişkenleri al ve temizle
            $printer_name = isset($input_data['printer_name']) ? $conn->real_escape_string($input_data['printer_name']) : 'Epson_TM-m30II';
            $print_mode = isset($input_data['print_mode']) ? $conn->real_escape_string($input_data['print_mode']) : 'browser';
            $company_name = isset($input_data['company_name']) ? $conn->real_escape_string($input_data['company_name']) : 'Restaurant Adı';
            $company_address = isset($input_data['company_address']) ? $conn->real_escape_string($input_data['company_address']) : 'Örnek Mah. Örnek Cad. No:123';
            $company_phone = isset($input_data['company_phone']) ? $conn->real_escape_string($input_data['company_phone']) : '0 (212) 345 67 89';
            $footer_text = isset($input_data['footer_text']) ? $conn->real_escape_string($input_data['footer_text']) : 'Teşekkür Ederiz, Yine Bekleriz!';
            $receipt_width = isset($input_data['receipt_width']) ? intval($input_data['receipt_width']) : 80;
            $logo_alignment = isset($input_data['logo_alignment']) ? $conn->real_escape_string($input_data['logo_alignment']) : 'center';
            $logo_path = isset($input_data['logo_path']) ? $conn->real_escape_string($input_data['logo_path']) : null;
            $logo_width = isset($input_data['logo_width']) ? intval($input_data['logo_width']) : 200;
            $logo_height = isset($input_data['logo_height']) ? intval($input_data['logo_height']) : 100;
            
            // Mevcut ayarları kontrol et
            $result = $conn->query("SELECT id FROM printer_settings ORDER BY id DESC LIMIT 1");
            
            if ($result && $result->num_rows > 0) {
                // Güncelleme
                $row = $result->fetch_assoc();
                $stmt = $conn->prepare("UPDATE printer_settings SET 
                    printer_name=?, print_mode=?, 
                    company_name=?, company_address=?, company_phone=?, footer_text=?, 
                    receipt_width=?, logo_alignment=?, logo_path=?, logo_width=?, logo_height=?
                    WHERE id=?");
                
                $stmt->bind_param("ssssssissiii", 
                    $printer_name, $print_mode,
                    $company_name, $company_address, $company_phone, $footer_text,
                    $receipt_width, $logo_alignment, $logo_path, $logo_width, $logo_height,
                    $row['id']);
            } else {
                // Yeni ekleme
                $stmt = $conn->prepare("INSERT INTO printer_settings 
                    (printer_name, print_mode, company_name, company_address, company_phone, footer_text, 
                     receipt_width, logo_alignment, logo_path, logo_width, logo_height) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                $stmt->bind_param("ssssssissii", 
                    $printer_name, $print_mode,
                    $company_name, $company_address, $company_phone, $footer_text,
                    $receipt_width, $logo_alignment, $logo_path, $logo_width, $logo_height);
            }
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Ayarlar başarıyla kaydedildi.']);
            } else {
                throw new Exception("Execute hatası: " . $stmt->error);
            }
            
            $stmt->close();
            
        } catch (Exception $e) {
            error_log("Printer settings save error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Geçersiz istek.']);
    }
    break;

        case 'upload_logo':
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['logo'])) {
                $uploadDir = 'uploads/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $file = $_FILES['logo'];
                $fileName = time() . '_' . basename($file['name']);
                $targetPath = $uploadDir . $fileName;
                
                // Check file type
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!in_array($file['type'], $allowedTypes)) {
                    echo json_encode(['success' => false, 'error' => 'Sadece resim dosyaları yüklenebilir.']);
                    break;
                }
                
                // Check file size (max 2MB)
                if ($file['size'] > 2 * 1024 * 1024) {
                    echo json_encode(['success' => false, 'error' => 'Dosya boyutu 2MB\'dan küçük olmalıdır.']);
                    break;
                }
                
                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    echo json_encode(['success' => true, 'logo_path' => $targetPath]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Dosya yüklenirken hata oluştu.']);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Geçersiz dosya.']);
            }
            break;

        case 'get_order_details':
            if (isset($_GET['table_id'])) {
                $table_id = intval($_GET['table_id']);
                $orderDetails = getOrderDetails($conn, $table_id);
                echo json_encode($orderDetails);
            } else {
                echo json_encode([]);
            }
            break;
            
// Ödeme tamamlandığında sipariş durumunu güncelle - BURAYA EKLEYİN
case 'complete_order':
    if (isset($_POST['order_id'])) {
        $order_id = intval($_POST['order_id']);
        
        // First get the table_id from the order
        $stmt = $conn->prepare("SELECT table_id FROM orders WHERE id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();
        $stmt->close();
        
        if ($order) {
            $table_id = $order['table_id'];
            
            // Update order status
            $stmt = $conn->prepare("UPDATE orders SET status = 'completed' WHERE id = ?");
            $stmt->bind_param("i", $order_id);
            
            if ($stmt->execute()) {
                // Update table status to available
                $stmt2 = $conn->prepare("UPDATE tables SET status = 'available' WHERE id = ?");
                $stmt2->bind_param("i", $table_id);
                $stmt2->execute();
                $stmt2->close();
                
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => $conn->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'error' => 'Order not found']);
        }
    }
    break;

            // db.php dosyasına aşağıdaki case'i ekleyin
case 'get_mysql_version':
    $version = $conn->server_version;
    $major = floor($version / 10000);
    $minor = floor(($version - $major * 10000) / 100);
    $patch = $version - $major * 10000 - $minor * 100;
    $version_str = "$major.$minor.$patch";
    echo json_encode(['success' => true, 'version' => $version_str]);
    break;
	
case 'update_product':
    if (isset($_POST['product_id']) && isset($_POST['name']) && isset($_POST['price']) && isset($_POST['category_id'])) {
        $product_id = intval($_POST['product_id']);
        $name = trim($_POST['name']);
        $price = floatval($_POST['price']);
        $category_id = intval($_POST['category_id']);
        $available = isset($_POST['available']) ? 1 : 0;
        $icerik = isset($_POST['icerik']) ? trim($_POST['icerik']) : '';
        $order_index = isset($_POST['order_index']) ? intval($_POST['order_index']) : 0;
        
        $stmt = $conn->prepare("UPDATE products SET name=?, price=?, category_id=?, available=?, icerik=?, order_index=? WHERE id=?");
        $stmt->bind_param("sdiisii", $name, $price, $category_id, $available, $icerik, $order_index, $product_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Eksik parametreler']);
    }
    break;
            
        case 'delete_product':
            if (isset($_POST['product_id'])) {
                $product_id = intval($_POST['product_id']);
                
                $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
                $stmt->bind_param("i", $product_id);
                
                if ($stmt->execute()) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => $conn->error]);
                }
                $stmt->close();
            } else {
                echo json_encode(['success' => false, 'error' => 'Eksik parametre']);
            }
            break;
  // db.php dosyasına logo yükleme kodu ekleyin
if (isset($_GET['action']) && $_GET['action'] == 'upload_logo' && isset($_FILES['logo'])) {
    $target_dir = "uploads/logos/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_name = uniqid() . '_' . basename($_FILES["logo"]["name"]);
    $target_file = $target_dir . $file_name;
    
    if (move_uploaded_file($_FILES["logo"]["tmp_name"], $target_file)) {
        echo json_encode(['success' => true, 'logo_path' => $target_file]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Logo yüklenirken hata oluştu.']);
    }
    exit;
}          
case 'update_category':
    if (isset($_POST['category_id']) && isset($_POST['name'])) {
        $category_id = intval($_POST['category_id']);
        $name = trim($_POST['name']);
        $order_index = isset($_POST['order_index']) ? intval($_POST['order_index']) : 0;
        
        $stmt = $conn->prepare("UPDATE categories SET name=?, order_index=? WHERE id=?");
        $stmt->bind_param("sii", $name, $order_index, $category_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Eksik parametreler']);
    }
    break;

case 'update_category_order':
    if (isset($_POST['category_orders'])) {
        $category_orders = json_decode($_POST['category_orders'], true);
        
        if ($category_orders && is_array($category_orders)) {
            $conn->begin_transaction();
            try {
                $stmt = $conn->prepare("UPDATE categories SET order_index = ? WHERE id = ?");
                
                foreach ($category_orders as $category_data) {
                    if (isset($category_data['id']) && isset($category_data['order_index'])) {
                        $order_index = intval($category_data['order_index']);
                        $category_id = intval($category_data['id']);
                        
                        $stmt->bind_param("ii", $order_index, $category_id);
                        $stmt->execute();
                    }
                }
                
                $conn->commit();
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                $conn->rollback();
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'error' => 'Geçersiz veri formatı']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Eksik parametreler']);
    }
    break;
            
        case 'delete_category':
            if (isset($_POST['category_id'])) {
                $category_id = intval($_POST['category_id']);
                
                // Kategoriye ait ürünleri kontrol et
                $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
                $check_stmt->bind_param("i", $category_id);
                $check_stmt->execute();
                $result = $check_stmt->get_result();
                $row = $result->fetch_assoc();
                $check_stmt->close();
                
                if ($row['count'] > 0) {
                    echo json_encode(['success' => false, 'error' => 'Bu kategoriye ait ürünler var. Önce ürünleri silin.']);
                } else {
                    $stmt = $conn->prepare("DELETE FROM categories WHERE id=?");
                    $stmt->bind_param("i", $category_id);
                    
                    if ($stmt->execute()) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['success' => false, 'error' => $conn->error]);
                    }
                    $stmt->close();
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Eksik parametre']);
            }
            break;
            
        case 'update_option_group':
            if (isset($_POST['group_id']) && isset($_POST['name'])) {
                $group_id = intval($_POST['group_id']);
                $name = trim($_POST['name']);
                $is_required = isset($_POST['is_required']) ? intval($_POST['is_required']) : 0;
                $min_selection = isset($_POST['min_selection']) ? intval($_POST['min_selection']) : 0;
                $max_selection = isset($_POST['max_selection']) ? intval($_POST['max_selection']) : 1;
                
                $stmt = $conn->prepare("UPDATE option_groups SET name=?, is_required=?, min_selection=?, max_selection=? WHERE id=?");
                $stmt->bind_param("siiii", $name, $is_required, $min_selection, $max_selection, $group_id);
                
                if ($stmt->execute()) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => $conn->error]);
                }
                $stmt->close();
            } else {
                echo json_encode(['success' => false, 'error' => 'Eksik parametreler']);
            }
            break;
            case 'delete_all_option_groups':
    try {
        $conn->begin_transaction();
        
        // Önce product_option_groups tablosunu temizle
        $conn->query("DELETE FROM product_option_groups");
        
        // Sonra category_option_groups tablosunu temizle
        $conn->query("DELETE FROM category_option_groups");
        
        // Sonra options tablosunu temizle
        $conn->query("DELETE FROM options");
        
        // En son option_groups tablosunu temizle
        $conn->query("DELETE FROM option_groups");
        
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Tüm opsiyon grupları ve ilişkili veriler başarıyla silindi']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => 'Silme işlemi sırasında hata oluştu: ' . $e->getMessage()]);
    }
    break;
  case 'delete_option_group':
    if (isset($_POST['group_id'])) {
        $group_id = intval($_POST['group_id']);
        
        // İşlemi transaction içinde yapalım
        $conn->begin_transaction();
        
        try {
            // Önce product_option_groups tablosundan ilgili kayıtları sil
            $stmt1 = $conn->prepare("DELETE FROM product_option_groups WHERE option_group_id = ?");
            $stmt1->bind_param("i", $group_id);
            $stmt1->execute();
            $stmt1->close();
            
            // Sonra category_option_groups tablosundan ilgili kayıtları sil
            $stmt2 = $conn->prepare("DELETE FROM category_option_groups WHERE option_group_id = ?");
            $stmt2->bind_param("i", $group_id);
            $stmt2->execute();
            $stmt2->close();
            
            // Sonra options tablosundan ilgili kayıtları sil
            $stmt3 = $conn->prepare("DELETE FROM options WHERE group_id = ?");
            $stmt3->bind_param("i", $group_id);
            $stmt3->execute();
            $stmt3->close();
            
            // En son option_groups tablosundan ilgili kaydı sil
            $stmt4 = $conn->prepare("DELETE FROM option_groups WHERE id = ?");
            $stmt4->bind_param("i", $group_id);
            $stmt4->execute();
            $stmt4->close();
            
            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Opsiyon grubu ve ilişkili tüm veriler başarıyla silindi']);
            
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['success' => false, 'error' => 'Silme işlemi sırasında hata oluştu: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Eksik parametre']);
    }
    break;
            
        case 'update_option':
            if (isset($_POST['option_id']) && isset($_POST['name'])) {
                $option_id = intval($_POST['option_id']);
                $name = trim($_POST['name']);
                $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
                
                $stmt = $conn->prepare("UPDATE options SET name=?, price=? WHERE id=?");
                $stmt->bind_param("sdi", $name, $price, $option_id);
                
                if ($stmt->execute()) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => $conn->error]);
                }
                $stmt->close();
            } else {
                echo json_encode(['success' => false, 'error' => 'Eksik parametreler']);
            }
            break;
  case 'delete_option':
    if (isset($_POST['option_id'])) {
        $option_id = intval($_POST['option_id']);
        
        // İlk önce order_items tablosundaki options alanında bu opsiyonun referanslarını temizle
        $result = $conn->query("SELECT id, options FROM order_items WHERE options IS NOT NULL");
        while ($row = $result->fetch_assoc()) {
            if (!empty($row['options'])) {
                $options = json_decode($row['options'], true);
                $updated = false;
                
                // Her bir sipariş öğesindeki opsiyonları kontrol et
                foreach ($options as $key => $option) {
                    if (isset($option['id']) && $option['id'] == $option_id) {
                        unset($options[$key]);
                        $updated = true;
                    }
                }
                
                // Eğer opsiyon silindiyse, güncelle
                if ($updated) {
                    $new_options = json_encode(array_values($options));
                    $update_stmt = $conn->prepare("UPDATE order_items SET options = ? WHERE id = ?");
                    $update_stmt->bind_param("si", $new_options, $row['id']);
                    $update_stmt->execute();
                    $update_stmt->close();
                }
            }
        }
        
        // Sonra options tablosundan ilgili kaydı sil
        $stmt = $conn->prepare("DELETE FROM options WHERE id = ?");
        $stmt->bind_param("i", $option_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Opsiyon başarıyla silindi']);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Eksik parametre']);
    }
    break;           
        case 'get_products_for_option_group':
            if (isset($_GET['group_id'])) {
                $group_id = intval($_GET['group_id']);
                
                $stmt = $conn->prepare("
                    SELECT p.*, c.name as category_name 
                    FROM products p 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    WHERE p.id IN (SELECT product_id FROM product_option_groups WHERE option_group_id = ?)
                    ORDER BY c.name, p.name
                ");
                $stmt->bind_param("i", $group_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                $products = [];
                while ($row = $result->fetch_assoc()) {
                    $products[] = $row;
                }
                echo json_encode($products);
                $stmt->close();
            } else {
                echo json_encode([]);
            }
            break;
 case 'remove_option_group_from_category':
    if (isset($_POST['category_id']) && isset($_POST['group_id'])) {
        $category_id = intval($_POST['category_id']);
        $group_id = intval($_POST['group_id']);
        
        $stmt = $conn->prepare("DELETE FROM category_option_groups WHERE category_id = ? AND option_group_id = ?");
        $stmt->bind_param("ii", $category_id, $group_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Opsiyon grubu kategoriden kaldırıldı']);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Eksik parametre']);
    }
    break;           
case 'remove_option_group_from_product':
    if (isset($_POST['product_id']) && isset($_POST['group_id'])) {
        $product_id = intval($_POST['product_id']);
        $group_id = intval($_POST['group_id']);
        
        $stmt = $conn->prepare("DELETE FROM product_option_groups WHERE product_id = ? AND option_group_id = ?");
        $stmt->bind_param("ii", $product_id, $group_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Opsiyon grubu üründen kaldırıldı']);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Eksik parametre']);
    }
    break;
            
        case 'get_reports':
            header('Content-Type: application/json');
            try {
                if (isset($_GET['start_date']) && isset($_GET['end_date']) && isset($_GET['report_type'])) {
                    $start_date = $conn->real_escape_string($_GET['start_date']);
                    $end_date = $conn->real_escape_string($_GET['end_date']);
                    $report_type = $conn->real_escape_string($_GET['report_type']);
                    
                    // Burada rapor verilerini oluştur
                    $report_data = generateReportData($conn, $start_date, $end_date, $report_type);
                    echo json_encode($report_data);
                } else {
                    echo json_encode(['error' => 'Eksik parametreler']);
                }
            } catch (Exception $e) {
                error_log("get_reports hatası: " . $e->getMessage());
                echo json_encode(['error' => 'Rapor yüklenirken hata oluştu: ' . $e->getMessage()]);
            }
            break;

        case 'save_report':
            if (isset($_POST['name']) && isset($_POST['type']) && isset($_POST['start_date']) && isset($_POST['end_date']) && isset($_POST['data'])) {
                $name = $conn->real_escape_string($_POST['name']);
                $type = $conn->real_escape_string($_POST['type']);
                $start_date = $conn->real_escape_string($_POST['start_date']);
                $end_date = $conn->real_escape_string($_POST['end_date']);
                $data = $conn->real_escape_string($_POST['data']);
                
                try {
                    // Toplam geliri hesapla
                    $data_obj = json_decode($_POST['data'], true);
                    $total_revenue = isset($data_obj['stats']['totalRevenue']) ? $data_obj['stats']['totalRevenue'] : 0;
                    
                    // Reports tablosunun var olup olmadığını kontrol et
                    $table_check = $conn->query("SHOW TABLES LIKE 'reports'");
                    if ($table_check->num_rows == 0) {
                        // Reports tablosunu oluştur
                        $create_table = "CREATE TABLE IF NOT EXISTS reports (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            name VARCHAR(255) NOT NULL,
                            type VARCHAR(50) NOT NULL,
                            start_date DATE NOT NULL,
                            end_date DATE NOT NULL,
                            data LONGTEXT NOT NULL,
                            total_revenue DECIMAL(10,2) DEFAULT 0,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                        )";
                        $conn->query($create_table);
                    }
                    
                    $stmt = $conn->prepare("INSERT INTO reports (name, type, start_date, end_date, data, total_revenue) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssssd", $name, $type, $start_date, $end_date, $data, $total_revenue);
                    
                    if ($stmt->execute()) {
                        echo json_encode(['success' => true, 'message' => 'Rapor başarıyla kaydedildi']);
                    } else {
                        echo json_encode(['success' => false, 'error' => 'Veritabanı hatası: ' . $stmt->error]);
                    }
                    $stmt->close();
                } catch (Exception $e) {
                    error_log("Save report error: " . $e->getMessage());
                    echo json_encode(['success' => false, 'error' => 'Rapor kaydedilirken hata oluştu: ' . $e->getMessage()]);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Eksik parametreler']);
            }
            break;

        case 'get_saved_reports':
            $result = $conn->query("SELECT id, name, type, start_date, end_date, total_revenue, created_at, 
                                   CONCAT(start_date, ' - ', end_date) as date_range 
                                   FROM reports ORDER BY created_at DESC");
            
            $reports = [];
            while ($row = $result->fetch_assoc()) {
                $reports[] = $row;
            }
            
            echo json_encode($reports);
            break;

        case 'clear_old_reports':
            $threshold_date = date('Y-m-d', strtotime('-30 days'));
            $result = $conn->query("DELETE FROM reports WHERE created_at < '$threshold_date'");
            
            if ($result) {
                echo json_encode(['success' => true, 'deleted_count' => $conn->affected_rows]);
            } else {
                echo json_encode(['success' => false, 'error' => $conn->error]);
            }
            break;
            
// add_options_to_category case'ini bulun ve aşağıdaki gibi değiştirin:
case 'add_options_to_category':
    error_log("[DEBUG] add_options_to_category called with category_id: " . ($_POST['category_id'] ?? 'NOT SET'));
    error_log("[DEBUG] POST data: " . print_r($_POST, true));
    
    if (isset($_POST['category_id']) && isset($_POST['option_groups'])) {
        $category_id = intval($_POST['category_id']);
        $option_groups_raw = $_POST['option_groups'] ?? [];
        if (is_string($option_groups_raw)) {
            $decoded = json_decode($option_groups_raw, true);
            $option_groups = is_array($decoded) ? $decoded : [];
        } else {
            $option_groups = $option_groups_raw;
        }
        
        error_log("[DEBUG] Processing category_id: $category_id with option_groups: " . print_r($option_groups, true));

        if (!is_array($option_groups) || empty($option_groups)) {
            echo json_encode(['success' => false, 'error' => 'Geçersiz opsiyon grubu verisi']);
            break;
        }
        
        $success = true;
        $error = '';
        $processed = 0;
        
        // ÖNCE: Bu kategoriye ait tüm mevcut opsiyon gruplarını temizle
        $delete_stmt = $conn->prepare("DELETE FROM category_option_groups WHERE category_id = ?");
        $delete_stmt->bind_param("i", $category_id);
        if (!$delete_stmt->execute()) {
            echo json_encode(['success' => false, 'error' => 'Temizleme hatası: ' . $delete_stmt->error]);
            $delete_stmt->close();
            break;
        }
        $delete_stmt->close();
        
        // Tüm seçili opsiyon gruplarını kategoriye ekle
        foreach ($option_groups as $group_id) {
            $group_id = intval($group_id);
            
            // Yeni opsiyon grubunu ekle
            $stmt = $conn->prepare("INSERT INTO category_option_groups (category_id, option_group_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $category_id, $group_id);
            
            if (!$stmt->execute()) {
                $success = false;
                $error = $conn->error;
                break;
            }
            $stmt->close();
            $processed++;
        }
        
        echo json_encode([
            'success' => $success, 
            'error' => $error,
            'processed' => $processed,
            'message' => $success ? "Kategoriye {$processed} opsiyon grubu eklendi" : ""
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Eksik parametre']);
    }
    break;
        case 'get_category_options':
            if (isset($_GET['category_id'])) {
                $category_id = intval($_GET['category_id']);
                
                // Kategoriye ait opsiyon gruplarını getir
                $stmt = $conn->prepare("
                    SELECT og.* 
                    FROM option_groups og
                    JOIN category_option_groups cog ON og.id = cog.option_group_id
                    WHERE cog.category_id = ?
                    ORDER BY og.name
                ");
                $stmt->bind_param("i", $category_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                $optionGroups = [];
                while ($row = $result->fetch_assoc()) {
                    $group_id = $row['id'];
                    
                    // Her grup için opsiyonları getir
                    $stmt2 = $conn->prepare("SELECT * FROM options WHERE group_id = ? ORDER BY name");
                    $stmt2->bind_param("i", $group_id);
                    $stmt2->execute();
                    $result2 = $stmt2->get_result();
                    
                    $options = [];
                    while ($option = $result2->fetch_assoc()) {
                        $options[] = $option;
                    }
                    $stmt2->close();
                    
                    $row['options'] = $options;
                    $optionGroups[] = $row;
                }
                $stmt->close();
                
                echo json_encode($optionGroups);
            } else {
                echo json_encode([]);
            }
            break;

        case 'get_product_options':
            if (isset($_GET['product_id'])) {
                $product_id = intval($_GET['product_id']);
                
                // Ürüne ait opsiyon gruplarını getir
                $stmt = $conn->prepare("
                    SELECT og.* 
                    FROM option_groups og
                    JOIN product_option_groups pog ON og.id = pog.option_group_id
                    WHERE pog.product_id = ?
                    ORDER BY og.name
                ");
                $stmt->bind_param("i", $product_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                $optionGroups = [];
                while ($row = $result->fetch_assoc()) {
                    $group_id = $row['id'];
                    
                    // Her grup için opsiyonları getir
                    $stmt2 = $conn->prepare("SELECT * FROM options WHERE group_id = ? ORDER BY name");
                    $stmt2->bind_param("i", $group_id);
                    $stmt2->execute();
                    $result2 = $stmt2->get_result();
                    
                    $options = [];
                    while ($option = $result2->fetch_assoc()) {
                        $options[] = $option;
                    }
                    $stmt2->close();
                    
                    $row['options'] = $options;
                    $optionGroups[] = $row;
                }
                $stmt->close();
                
                echo json_encode($optionGroups);
            } else {
                echo json_encode([]);
            }
            break;
            
case 'remove_option_group_from_category':
    if (isset($_POST['category_id']) && isset($_POST['group_id'])) {
        $category_id = intval($_POST['category_id']);
        $group_id = intval($_POST['group_id']);
        
        $stmt = $conn->prepare("DELETE FROM category_option_groups WHERE category_id = ? AND option_group_id = ?");
        $stmt->bind_param("ii", $category_id, $group_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Opsiyon grubu kategoriden kaldırıldı']);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Eksik parametre']);
    }
    break;

        case 'cleanup_duplicates':
            try {
                // Remove duplicate entries from product_option_groups
                $conn->query("DELETE pog1 FROM product_option_groups pog1
                            INNER JOIN product_option_groups pog2 
                            WHERE pog1.id > pog2.id 
                            AND pog1.product_id = pog2.product_id 
                            AND pog1.option_group_id = pog2.option_group_id");
                
                // Remove duplicate entries from category_option_groups
                $conn->query("DELETE cog1 FROM category_option_groups cog1
                            INNER JOIN category_option_groups cog2 
                            WHERE cog1.id > cog2.id 
                            AND cog1.category_id = cog2.category_id 
                            AND cog1.option_group_id = cog2.option_group_id");
                
                echo json_encode(['success' => true, 'message' => 'Çoğaltılmış kayıtlar temizlendi']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            break;

        case 'cleanup_category_duplicates':
            $cleanup_sql = "
                DELETE cog1 FROM category_option_groups cog1
                INNER JOIN category_option_groups cog2 
                WHERE cog1.id > cog2.id 
                AND cog1.category_id = cog2.category_id 
                AND cog1.option_group_id = cog2.option_group_id
            ";
            
            if ($conn->query($cleanup_sql)) {
                $affected = $conn->affected_rows;
                echo json_encode([
                    'success' => true, 
                    'message' => "Kategori çoğaltmaları temizlendi. $affected kayıt silindi."
                ]);
            } else {
                echo json_encode([
                    'success' => false, 
                    'error' => 'Temizleme sırasında hata: ' . $conn->error
                ]);
            }
            break;

        case 'cleanup_product_duplicates':
            $cleanup_sql = "
                DELETE pog1 FROM product_option_groups pog1
                INNER JOIN product_option_groups pog2 
                WHERE pog1.id > pog2.id 
                AND pog1.product_id = pog2.product_id 
                AND pog1.option_group_id = pog2.option_group_id
            ";
            
            if ($conn->query($cleanup_sql)) {
                $affected = $conn->affected_rows;
                echo json_encode([
                    'success' => true, 
                    'message' => "Ürün opsiyon çoğaltmaları temizlendi. $affected kayıt silindi."
                ]);
            } else {
                echo json_encode([
                    'success' => false, 
                    'error' => 'Temizleme sırasında hata: ' . $conn->error
                ]);
            }
            break;
        
        case 'save_order':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['table_id']) || !isset($input['total_amount']) || !isset($input['items'])) {
                echo json_encode(['success' => false, 'error' => 'Eksik parametreler']);
                break;
            }
            
            $table_id = intval($input['table_id']);
            $total_amount = floatval($input['total_amount']);
            $items = $input['items'];
            
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // Insert order
                $stmt = $conn->prepare("INSERT INTO orders (table_id, total_amount, status) VALUES (?, ?, 'completed')");
                $stmt->bind_param("id", $table_id, $total_amount);
                
                if (!$stmt->execute()) {
                    throw new Exception('Sipariş kaydedilemedi: ' . $stmt->error);
                }
                
                $order_id = $conn->insert_id;
                $stmt->close();
                
                // Insert order items
                $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, options) VALUES (?, ?, ?, ?, ?)");
                
                foreach ($items as $item) {
                    $product_id = intval($item['product_id']);
                    $quantity = intval($item['quantity']);
                    $price = floatval($item['price']);
                    $options = $item['options'];
                    
                    $stmt->bind_param("iiiDs", $order_id, $product_id, $quantity, $price, $options);
                    
                    if (!$stmt->execute()) {
                        throw new Exception('Sipariş detayları kaydedilemedi: ' . $stmt->error);
                    }
                }
                
                $stmt->close();
                
                // Commit transaction
                $conn->commit();
                
                echo json_encode(['success' => true, 'order_id' => $order_id]);
                
            } catch (Exception $e) {
                // Rollback transaction
                $conn->rollback();
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            break;

        case 'delete_all_products':
    try {
        if (method_exists($conn, 'begin_transaction')) { $conn->begin_transaction(); } else { $conn->query("START TRANSACTION"); }
        // Temporarily disable FK checks to avoid constraint issues during bulk deletes
        @$conn->query("SET FOREIGN_KEY_CHECKS=0");

        // Clear known product-related relation tables if they exist
        $productRelated = [
            'product_option_groups',
            'product_options',
            'product_images',
            'product_categories',
            'product_variants',
            'inventory',
            'product_prices',
            'product_addons',
            'product_extras'
        ];
        foreach ($productRelated as $tbl) { deleteAllIfExists($conn, $tbl); }

        // Finally delete all products
        deleteAllIfExists($conn, 'products');

        // Re-enable FK checks
        @$conn->query("SET FOREIGN_KEY_CHECKS=1");
        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        if (method_exists($conn, 'rollback')) { $conn->rollback(); } else { $conn->query("ROLLBACK"); }
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    break;

case 'delete_all_categories':
    try {
        if (method_exists($conn, 'begin_transaction')) { $conn->begin_transaction(); } else { $conn->query("START TRANSACTION"); }
        // Temporarily disable FK checks to avoid constraint issues during bulk deletes
        @$conn->query("SET FOREIGN_KEY_CHECKS=0");

        // First remove product-related relations, then products
        $productRelated = [
            'product_option_groups',
            'product_options',
            'product_images',
            'product_categories',
            'product_variants',
            'inventory',
            'product_prices',
            'product_addons',
            'product_extras'
        ];
        foreach ($productRelated as $tbl) { deleteAllIfExists($conn, $tbl); }
        deleteAllIfExists($conn, 'products');

        // Then remove category-related relations, then categories
        $categoryRelated = [
            'category_option_groups',
            'category_images',
            'category_translations'
        ];
        foreach ($categoryRelated as $tbl) { deleteAllIfExists($conn, $tbl); }
        deleteAllIfExists($conn, 'categories');

        // Re-enable FK checks
        @$conn->query("SET FOREIGN_KEY_CHECKS=1");
        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        if (method_exists($conn, 'rollback')) { $conn->rollback(); } else { $conn->query("ROLLBACK"); }
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    break;

        case 'add_options_to_category':
            $category_id = $_POST['category_id'] ?? null;
            $option_groups = $_POST['option_groups'] ?? [];
            
            if (!$category_id || !is_array($option_groups) || empty($option_groups)) {
                echo json_encode(['success' => false, 'error' => 'Geçersiz kategori veya opsiyon grubu verisi']);
                break;
            }
            
            // First, remove existing option groups for this category
            $stmt = $conn->prepare("DELETE FROM category_option_groups WHERE category_id = ?");
            $stmt->bind_param("i", $category_id);
            $stmt->execute();
            $stmt->close();
            
            // Add new option groups
            $stmt = $conn->prepare("INSERT IGNORE INTO category_option_groups (category_id, option_group_id) VALUES (?, ?)");
            $success_count = 0;
            
            foreach ($option_groups as $option_group_id) {
                $stmt->bind_param("ii", $category_id, $option_group_id);
                if ($stmt->execute()) {
                    $success_count++;
                }
            }
            $stmt->close();
            
            if ($success_count > 0) {
                echo json_encode(['success' => true, 'message' => $success_count . ' opsiyon grubu kategoriye eklendi']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Opsiyon grupları eklenemedi']);
            }
            break;

        case 'add_options_to_products':
            $product_ids = $_POST['product_ids'] ?? [];
            $option_groups = $_POST['option_groups'] ?? [];
            
            if (!is_array($product_ids) || empty($product_ids) || !is_array($option_groups) || empty($option_groups)) {
                echo json_encode(['success' => false, 'error' => 'Geçersiz ürün veya opsiyon grubu verisi']);
                break;
            }
            
            $success_count = 0;
            $stmt = $conn->prepare("INSERT IGNORE INTO product_option_groups (product_id, option_group_id) VALUES (?, ?)");
            
            foreach ($product_ids as $product_id) {
                foreach ($option_groups as $option_group_id) {
                    $stmt->bind_param("ii", $product_id, $option_group_id);
                    if ($stmt->execute()) {
                        $success_count++;
                    }
                }
            }
            $stmt->close();
            
            if ($success_count > 0) {
                echo json_encode(['success' => true, 'message' => $success_count . ' opsiyon grubu ürünlere eklendi']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Opsiyon grupları eklenemedi veya zaten mevcut']);
            }
            break;

default:
            echo json_encode(['success' => false, 'error' => 'Geçersiz işlem']);
            break;
    }
    
    $conn->close();
    exit();
	

}
}
