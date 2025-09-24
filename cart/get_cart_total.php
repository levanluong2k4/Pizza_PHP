<?php
session_start();

$total = 0;
$count = 0;

if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
        $count += intval($item['quantity']);
    }
}

echo json_encode([
    'total' => $total,
    'count' => $count
]);
?>