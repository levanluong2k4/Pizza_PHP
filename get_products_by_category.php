<?php
header('Content-Type: application/json');
require 'includes/db_connect.php';

$category_id = $_GET['category_id'] ?? 0;
$exclude_id  = $_GET['exclude_id'] ?? 0;

$response = [
    "success" => false,
    "products" => []
];

if ($category_id == 0) {
    echo json_encode($response);
    exit;
}

try {
    // Lấy SP cùng loại, trừ SP hiện tại
    $sql = "SELECT * FROM sanpham 
            WHERE MaLoai = ? AND MaSP != ?";
    $stmt = $ketnoi->prepare($sql);
    $stmt->bind_param("ss", $category_id, $exclude_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $products = [];

    while ($row = $result->fetch_assoc()) {
        // Lấy size của sản phẩm
        $sqlSize = "SELECT 
                        s.MaSize, s.TenSize, sps.Gia 
                    FROM size s
                    JOIN sanpham_size sps ON s.MaSize = sps.MaSize
                    WHERE sps.MaSP = ?";
        $stmtSize = $ketnoi->prepare($sqlSize);
        $stmtSize->bind_param("i", $row["MaSP"]);
        $stmtSize->execute();
        $sizesResult = $stmtSize->get_result();

        $sizes = [];
        while ($s = $sizesResult->fetch_assoc()) {
            $sizes[] = [
                "MaSize" => $s["MaSize"],
                "TenSize" => $s["TenSize"],
                "Gia" => (int)$s["Gia"]
            ];
        }
        $stmtSize->close();

        // Đẩy vào mảng products
        $products[] = [
            "MaSP" => $row["MaSP"],
            "TenSP" => $row["TenSP"],
            "Anh"   => "./" . $row["Anh"],
            "sizes" => $sizes
        ];
    }

    $stmt->close();

    $response['success'] = true;
    $response['products'] = $products;

} catch (Exception $e) {
    $response['success'] = false;
    $response['error'] = $e->getMessage();
}

echo json_encode($response);