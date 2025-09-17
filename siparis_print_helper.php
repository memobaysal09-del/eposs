<?php
function print_order_with_active_connection($order_data) {
    $print_data = [
        'action' => 'print_order_auto',
        'table_id' => $order_data['table_id'] ?? '',
        'order_id' => $order_data['order_id'] ?? '',
        'date' => date('d.m.Y H:i'),
        'items' => $order_data['items'] ?? [],
        'total' => $order_data['total'] ?? 0
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'Print.php');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($print_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($response === false) {
        return ['success' => false, 'error' => 'Print request failed'];
    }
    
    $result = json_decode($response, true);
    if ($http_code === 200 && isset($result['success']) && $result['success']) {
        return ['success' => true, 'message' => $result['message'] ?? 'Print successful'];
    }
    
    return ['success' => false, 'error' => $result['error'] ?? 'Unknown print error'];
}
?>
