<?php
session_start();

$count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $count += intval($item['quantity']);
    }
}

echo $count;
?>