<?php

// require './process/check_admin.php';
$id=$_GET['ma'];
require __DIR__ . '/../../includes/db_connect.php';

$sql="select * from loaisanpham";
$list_loaisp=mysqli_query($ketnoi,$sql);
$sql="select * from sanpham where MaSP=$id";

$ketqua=mysqli_query($ketnoi,$sql);
$inforSP=mysqli_fetch_array($ketqua);
mysqli_close($ketnoi);

?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>

    <form action="../../admin/process/update_process.php" method="post" enctype="multipart/form-data">
        <table>
           <tr>
                
                <td>
                    <input type="hidden" name="ma" value="<?php echo $inforSP['MaSP'] ?>" >

                </td>
            </tr>
            <tr>
                <td>Tên sản phẩm </td>
                <td>
                    <input type="text" name="tensp" value="<?php echo $inforSP['TenSP'] ?>">

                </td>
            </tr>
            <tr>
                <td>Đổi ảnh mới </td>
                <td>
                    <input type="file" name="Anhnew">
                    <br>
                    <img src="../../<?php echo $inforSP['Anh'] ?>" alt="" width="50px" height="auto" name="Anhold">
                </td>
            </tr>
            <tr>
                <td>Mã loại</td>
                <td>
                    <select name="maloai" id="">
                        <?php foreach($list_loaisp as $values ){ ?>

                        <option value="<?php echo $values["MaLoai"] ?>"
                            <?php if($inforSP['MaLoai']==$values["MaLoai"])  ?> selected>
                            <?php echo $values["TenLoai"] ?>



                        </option>
                        <?php } ?>

                    </select>

                </td>
            </tr>
            <tr>
                <td>Mô tả </td>
                <td>
                    <textarea name="mota" id="" row="20" col="10">
                        <?php echo $inforSP['MoTa'] ?>

                    </textarea>

                </td>
            </tr>




        </table>
        <button type="submit">insert</button>

    </form>



</body>

</html>