<?php
require './process/check_admin.php';
$ketnoi=mysqli_connect("localhost","root","","php_pizza");
$sql="select * from sanpham";
$kq=mysqli_query($ketnoi,$sql);
mysqli_set_charset($ketnoi, "utf8");


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <a href="insert_producs.php">Thêm sản phẩm</a>
    <form action="" method="post">

        <table border="1" cellspacing="0" cellpadding="5">
            <tr border="0">
                <th>
                    Mã sản phẩm
                </th>
                <th>Tên sản phẩm</th>
                <th>Mô tả</th>
                <th> ẢNH </th>
                <th> thao tác</th>
            </tr>
            <?php 
        foreach($kq as $value ){

?>
            <tr>

                <td><?php echo $value["MaSP"] ?></td>
                <td><?php echo $value["TenSP"] ?></td>
                <td><?php echo $value["MoTa"] ?></td>
                <td><img src="../<?php echo $value["Anh"] ?>" alt="" width="50px" height="auto"></td>

                <td><a href="update_product.php?ma=<?php echo $value["MaSP"] ?>">Sữa </a></td>
                <td><a href="./process/delete.php?id=<?php echo $value["MaSP"]?>"
                        onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này không?');">Xóa </a></td>
            </tr>
            <?php } ?>

        </table>



    </form>
</body>

</html>