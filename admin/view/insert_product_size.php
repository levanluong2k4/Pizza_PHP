<?php

// require './process/check_admin.php';
require __DIR__ . '/../../includes/db_connect.php';

$sql="SELECT `MaSP`, `MaSize`, `Gia`, `Anh` FROM `sanpham_size` ";

$result=mysqli_query($ketnoi,$sql);

$sql_sanpham=" SELECT * 
FROM sanpham 
 ";
$result_sanpham=mysqli_query($ketnoi,$sql_sanpham);

$sql_size="SELECT * FROM `size` ";
$result_size=mysqli_query($ketnoi,$sql_size);


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <form action="../../admin/process/insert_product_size.php" method="post" enctype="multipart/form-data">
        <table>

            <tr>
                <td>Tên sản phẩm </td>
                  <td>
                    <select name="masanpham" id="">
                        <?php foreach($result_sanpham as $values ){ ?>
                        <option value="<?php echo $values["MaSP"] ?>"><?php echo $values["TenSP"] ?> </option>
                        <?php } ?>

                    </select>

                </td>
            </tr>
               <tr>
                <td>Tên size </td>
                  <td>
                    <select name="masize" id="">
                        <?php foreach($result_size as $values ){ ?>
                        <option value="<?php echo $values["MaSize"] ?>"><?php echo $values["TenSize"] ?> </option>
                        <?php } ?>

                    </select>

                </td>
            </tr>
            <tr>
                <td>Giá </td>
                <td>
                    <input type="number" name="gia">

            </td>
            <tr>
                <td>Ảnh </td>
                <td>
                    <input type="file" name="Anh">

                </td>
            </tr>



        </table>
        <button type="submit">insert</button>

    </form>


</body>

</html>