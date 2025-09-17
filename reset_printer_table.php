<?php
// reset_printer_table.php
require_once 'db.php';

// Önce tabloyu sil
$conn->query("DROP TABLE IF EXISTS printer_settings");

// Sonra yeniden oluştur
$sql = "CREATE TABLE printer_settings (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(255) DEFAULT 'Restaurant Adı',
    company_name_font VARCHAR(100) DEFAULT 'Arial, sans-serif',
    company_name_size TINYINT DEFAULT 18,
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

if ($conn->query($sql)) {
    echo "Tablo başarıyla sıfırlandı!";
} else {
    echo "Hata: " . $conn->error;
}
?>