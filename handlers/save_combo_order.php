<?php
session_start();
require '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $combo_id = intval($_POST['combo_id']);
    $combo_items = json_decode($_POST['combo_items'], true);
    
    // ✅ Lưu vào session
    $_SESSION['combo_order'] = [
        'combo_id' => $combo_id,
        'items' => $combo_items
    ];
    
    echo json_encode(['success' => true]);
}
?>