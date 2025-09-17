<?php
// add_all_tables.php
require_once 'db.php';

header('Content-Type: application/json');

$added = 0;
$existing = 0;

try {
    // Veritabanı bağlantısını kontrol et
    if ($conn->connect_error) {
        throw new Exception("Veritabanı bağlantı hatası: " . $conn->connect_error);
    }
    
    // 1'den 20'ye kadar tüm masaları kontrol et ve ekle
    for ($i = 1; $i <= 20; $i++) {
        // Masa numarasının zaten var olup olmadığını kontrol et
        $check_stmt = $conn->prepare("SELECT id FROM `tables` WHERE number = ?");
        $check_stmt->bind_param("i", $i);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $existing++;
            $check_stmt->close();
            continue;
        }
        $check_stmt->close();
        
        // Masa yoksa ekle (isimleri "tabel" olarak)
        $stmt = $conn->prepare("INSERT INTO `tables` (number, status, name) VALUES (?, 'available', ?)");
        $tableName = "table";
        $stmt->bind_param("is", $i, $tableName);
        
        if ($stmt->execute()) {
            $added++;
        } else {
            error_log("tabel ekleme hatası: " . $stmt->error);
        }
        $stmt->close();
    }
    
    echo json_encode(['success' => true, 'added' => $added, 'existing' => $existing]);
    
} catch (Exception $e) {
    error_log("Toplu tabel ekleme hatası: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

// Bağlantıyı kapat
if (isset($conn)) {
    $conn->close();
}
?>