<?php
// require './process/check_admin.php';
require __DIR__ . '/../../includes/db_connect.php';

$sql="SELECT ss.id, ss.Gia, sp.TenSP, ss.Anh, s.TenSize  
FROM sanpham_size ss, sanpham sp, size s  
WHERE ss.MaSP = sp.MaSP  
AND ss.MaSize = s.MaSize;
";
$kq=mysqli_query($ketnoi,$sql);



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <a href="insert_product_size.php">Thêm sản phẩm</a>
    <form action="" method="post">

        <table border="1" cellspacing="0" cellpadding="5">
            <tr border="0">
                <th>
                    Tên sản phẩm
                </th>
                <th>Tên Size</th>
               
                <th> ẢNH </th>
                <th> Giá</th>
            </tr>
            <?php 
        foreach($kq as $value ){

?>
            <tr>

                <td><?php echo $value["TenSP"] ?></td>
                <td><?php echo $value["TenSize"] ?></td>
                <td><?php echo $value["Gia"] ?></td>
                <td><img src="../../<?php echo $value["Anh"] ?>" alt="" width="50px" height="auto"></td>

                <td><a href="update_product.php?ma=<?php echo $value["id"] ?>">Sữa </a></td>
                <td><a href="../process/delete.php?id=<?php echo $value["id"]?>"
                        onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này không?');">Xóa </a></td>
            </tr>
            <?php } ?>

        </table>



    </form>
</body>

</html>