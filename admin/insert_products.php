<?php

require './process/check_admin.php';
require "../includes/db_connect.php";
$sql="select * from loaisanpham";
$result=mysqli_query($ketnoi,$sql);

?>

<!DOCTYPE html>
<html lang="en">



<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <form action="../admin/process/insert_product.php" method="post" enctype="multipart/form-data">
        <table>

            <tr>
                <td>Tên sản phẩm </td>
                <td>
                    <input type="text" name="tensp">

                </td>
            </tr>
            <tr>
                <td>Ảnh </td>
                <td>
                    <input type="file" name="Anh">

                </td>
            </tr>
            <tr>
                <td>Mã loại</td>
                <td>
                    <select name="maloai" id="">
                        <?php foreach($result as $values ){ ?>
                        <option value="<?php echo $values["MaLoai"] ?>"><?php echo $values["TenLoai"] ?> </option>
                        <?php } ?>

                    </select>

                </td>
            </tr>
            <tr>
                <td>Mô tả </td>
                <td>
                    <textarea name="mota" id="" row="20" col="10">

                    </textarea>

                </td>
            </tr>




        </table>
        <button type="submit">insert</button>

    </form>


</body>

</html>