<?php
header('Content-Type: application/json; charset=utf-8');

$host = "sql205.infinityfree.com";
$dbname = "if0_38069225_epost";
$username = "if0_38069225";
$password = "BSweCvlos2opcpS";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // Opsiyonel filtre parametreleri al
    $start = $_GET['start'] ?? null;
    $end = $_GET['end'] ?? null;
    $customerId = $_GET['customer_id'] ?? null;

    // Genişletilmiş sorgu (ürünleri de dahil ediyoruz)
    $sql = "
        SELECT
            c.id            AS customer_id,
            c.customer_name AS customer_name,
            o.id            AS order_id,
            o.order_date    AS order_date,
            p.id            AS product_id,
            p.product_sku   AS product_sku,
            p.product_name  AS product_name,
            oi.quantity     AS quantity,
            oi.unit_price   AS unit_price,
            (oi.quantity * oi.unit_price) AS line_total
        FROM customers c
        JOIN orders o ON o.customer_id = c.id
        JOIN order_items oi ON oi.order_id = o.id
        JOIN products p ON p.id = oi.product_id
        WHERE 1=1
    ";

    $params = [];

    if ($start && $end) {
        $sql .= " AND o.order_date BETWEEN :start AND :end";
        $params[':start'] = $start;
        $params[':end'] = $end;
    }
    if ($customerId) {
        $sql .= " AND c.id = :customer_id";
        $params[':customer_id'] = $customerId;
    }

    $sql .= " ORDER BY c.customer_name, o.order_date DESC, o.id, p.product_name";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    echo json_encode([
        "success" => true,
        "count"   => count($rows),
        "data"    => $rows
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error"   => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
