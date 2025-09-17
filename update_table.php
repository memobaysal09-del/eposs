<?php
// update_table.php - Masa güncelleme
require_once 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['table_id'])) {
    $table_id = intval($_POST['table_id']);
    $name = trim($_POST['name']);
    $number = intval($_POST['number']);
    $status = isset($_POST['status']) ? $_POST['status'] : null;
    
    // Masa numarasının benzersiz olup olmadığını kontrol et (kendisi hariç)
    $check_stmt = $conn->prepare("SELECT id FROM tables WHERE number = ? AND id != ?");
    $check_stmt->bind_param("ii", $number, $table_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        echo json_encode(['success' => false, 'error' => 'Bu masa numarası zaten mevcut']);
        $check_stmt->close();
        exit();
    }
    $check_stmt->close();
    
    // Masayı güncelle (durum da dahil)
    if ($status) {
        $stmt = $conn->prepare("UPDATE tables SET name = ?, number = ?, status = ? WHERE id = ?");
        $stmt->bind_param("sisi", $name, $number, $status, $table_id);
    } else {
        $stmt = $conn->prepare("UPDATE tables SET name = ?, number = ? WHERE id = ?");
        $stmt->bind_param("sii", $name, $number, $table_id);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Geçersiz istek']);
}

$conn->close();
?>