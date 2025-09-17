<?php
// add_to_order.php - Handle adding products with options to the order
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Get the JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit;
}

try {
    // Validate required fields
    if (!isset($input['product_id']) || !isset($input['table_id']) || !isset($input['price'])) {
        throw new Exception('Missing required fields');
    }

    $product_id = intval($input['product_id']);
    $table_id = intval($input['table_id']);
    $price = floatval($input['price']);
    $options = isset($input['options']) ? $input['options'] : [];
    $extras = isset($input['extras']) ? $input['extras'] : [];
    $swaps_in = isset($input['swaps_in']) ? $input['swaps_in'] : [];
    $swaps_out = isset($input['swaps_out']) ? $input['swaps_out'] : [];

    // Check if there's an active order for this table
    $stmt = $conn->prepare("SELECT id FROM orders WHERE table_id = ? AND status = 'active'");
    $stmt->bind_param("i", $table_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
        $order_id = $order['id'];
    } else {
        // Create a new order
        $stmt = $conn->prepare("INSERT INTO orders (table_id, total_amount, status) VALUES (?, 0, 'active')");
        $stmt->bind_param("i", $table_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to create order: ' . $stmt->error);
        }
        
        $order_id = $conn->insert_id;
        $stmt->close();
        
        // Update table status to occupied
        $stmt = $conn->prepare("UPDATE tables SET status = 'occupied' WHERE id = ?");
        $stmt->bind_param("i", $table_id);
        $stmt->execute();
        $stmt->close();
    }

    // Add the product to the order
    $options_json = !empty($options) ? json_encode($options) : null;
    
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, options) VALUES (?, ?, 1, ?, ?)");
    $stmt->bind_param("iids", $order_id, $product_id, $price, $options_json);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to add product to order: ' . $stmt->error);
    }
    
    $order_item_id = $conn->insert_id;
    $stmt->close();

    // Add extras if any
    if (!empty($extras)) {
        foreach ($extras as $extra) {
            $extra_id = intval($extra['extra_id']);
            $quantity = intval($extra['quantity']);
            
            $stmt = $conn->prepare("INSERT INTO order_item_extras (order_item_id, extra_id, quantity) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $order_item_id, $extra_id, $quantity);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Add swaps in if any
    if (!empty($swaps_in)) {
        foreach ($swaps_in as $swap_in) {
            $swap_in_id = intval($swap_in['swap_in_id']);
            $quantity = intval($swap_in['quantity']);
            
            $stmt = $conn->prepare("INSERT INTO order_item_swaps_in (order_item_id, swap_in_id, quantity) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $order_item_id, $swap_in_id, $quantity);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Add swaps out if any
    if (!empty($swaps_out)) {
        foreach ($swaps_out as $swap_out) {
            $swap_out_id = intval($swap_out['swap_out_id']);
            $quantity = intval($swap_out['quantity']);
            
            $stmt = $conn->prepare("INSERT INTO order_item_swaps_out (order_item_id, swap_out_id, quantity) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $order_item_id, $swap_out_id, $quantity);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Update the order total
    $stmt = $conn->prepare("
        UPDATE orders o 
        SET total_amount = (
            SELECT COALESCE(SUM(price), 0) 
            FROM order_items 
            WHERE order_id = o.id
        ) 
        WHERE o.id = ?
    ");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => true, 'message' => 'Product added to order successfully']);

} catch (Exception $e) {
    error_log("Error in add_to_order.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>