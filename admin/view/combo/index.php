<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /unitop/backend/lesson/school/project_pizza/sign_in.php");
    exit();
}
require __DIR__ . '/../../../includes/db_connect.php';
// TRUY VẤN LẤY DANH SÁCH COMBO
$sql = "
SELECT 
    c.MaCombo,
    c.Tencombo,
    c.Anh,
    c.giamgia,
    COALESCE(SUM(sps.Gia * ct.SoLuong), 0) AS TongGoc,
    COALESCE(
        SUM(sps.Gia * ct.SoLuong) - 
        (SUM(sps.Gia * ct.SoLuong) * c.giamgia / 100),
        0
    ) AS TongSauGiam
FROM combo c
LEFT JOIN chitietcombo ct ON c.MaCombo = ct.MaCombo
LEFT JOIN sanpham_size sps 
    ON ct.MaSP = sps.MaSP AND ct.MaSize = sps.MaSize
GROUP BY c.MaCombo
ORDER BY c.MaCombo ASC
";
$kq = mysqli_query($ketnoi, $sql);

$kq = mysqli_query($ketnoi, $sql);

// XÓA COMBO TRỰC TIẾP TẠI TRANG NÀY
if (isset($_POST['delete_ajax'])) {
    $MaCombo = intval($_POST['delete_ajax']);

    $stmt1 = $ketnoi->prepare("DELETE FROM chitietcombo WHERE MaCombo = ?");
    $stmt1->bind_param("i", $MaCombo);
    $stmt1->execute();

    $stmt2 = $ketnoi->prepare("DELETE FROM combo WHERE MaCombo = ?");
    $stmt2->bind_param("i", $MaCombo);
    $stmt2->execute();

    echo "success";
    exit;
}



?>


<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Danh sách Combo</title>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

    <style>
        body {
            background-color: #f9fafb;
            font-family: "Segoe UI", sans-serif;
        }

        .navbar {
            background: linear-gradient(90deg, #28a745, #66bb6a);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .navbar-brand {
            font-weight: bold;
            color: #fff !important;
        }

        .navbar-nav .nav-link {
            color: #fff !important;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .navbar-nav .nav-link:hover,
        .navbar-nav .dropdown:hover .nav-link {
            background-color: rgba(255, 255, 255, 0.25);
            border-radius: 8px;
        }

        .dropdown-menu a {
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .dropdown-menu a:hover {
            background-color: #e8f5e9;
            color: #28a745;
        }

        .dropdown-submenu {
            position: relative;
        }

        .dropdown-submenu .dropdown-menu {
            top: 0;
            left: 100%;
            margin-top: -1px;
        }

        .navbar-nav>li:last-child .dropdown-submenu .dropdown-menu {
            left: auto;
            right: 100%;
        }

        .dropdown-submenu>a::after {
            content: "\f054";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            margin-left: auto;
            font-size: 0.8em;
        }

        .navbar-nav {
            margin: 0 auto;
        }

        .logout-btn {
            color: #fff;
            font-weight: 600;
            text-decoration: none;
            border: 1px solid rgba(255, 255, 255, 0.7);
            border-radius: 6px;
            padding: 6px 14px;
            transition: 0.3s;
        }

        .logout-btn:hover {
            background: white;
            color: #28a745;
        }

        .main-title {
            color: #28a745;
            font-weight: 700;
            margin-top: 50px;
        }

        .text-muted {
            color: #6c757d !important;
        }
    </style>
</head>

<body>


    <?php include __DIR__ . '/../../navbar_admin.php'; ?>

    <!-- Danh sách combo -->
    <div class="container mt-5">

        <div class="d-flex justify-content-between align-items-center">
            <h2 class="main-title"><i class="fa-solid fa-layer-group"></i> Danh sách Combo</h2>


        </div>
        <a href="./create.php" class="btn btn-success">
            <i class="fa-solid fa-plus"></i> Thêm Combo
        </a>
        <div class="table-responsive mt-4">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr class="text-center">
                        <th>Mã Combo</th>
                        <th>Tên Combo</th>
                        <th>Ảnh</th>
                        <th>Giảm giá (%)</th>
                        <th>Tổng tiền</th>
                        <th style="width: 220px;">Hành động</th>

                    </tr>
                </thead>

                <tbody style="text-align: center;">
                    <?php
                    if ($kq && $kq->num_rows > 0) {
                        while ($row = $kq->fetch_assoc()) { ?>
                            <tr>
                                <td><?= $row["MaCombo"] ?></td>
                                <td><?= $row["Tencombo"] ?></td>

                                <?php
                                $imagePath = "../../../" . $row["Anh"];
                                $defaultImage = "../../../img/Birthday_WebBanner.jpg";

                                // Kiểm tra file ảnh tồn tại
                                if (!file_exists($imagePath) || empty($row["Anh"])) {
                                    $imagePath = $defaultImage;
                                }
                                ?>
                                <td class="text-center">
                                    <img src="<?= $imagePath ?>"
                                        style="width:60px; height:60px; object-fit:cover; border-radius:6px;">
                                </td>


                                <td><?= $row["giamgia"] ?>%</td>
                                <td><?= number_format($row["TongSauGiam"], 0, ',', '.') ?> đ</td>

                                <td class="text-center">
                                    <a href="./edit.php?MaCombo=<?= $row['MaCombo'] ?>"
                                        class="btn btn-warning btn-sm mx-1">
                                        Sửa
                                    </a>

                                    <a class="btn btn-danger btn-sm mx-1"
                                        onclick="deleteCombo(<?= $row['MaCombo'] ?>, this)">
                                        Xóa
                                    </a>

                                    <a href="../combo_detail/index.php?MaCombo=<?= $row['MaCombo'] ?>"
                                        class="btn btn-info btn-sm ">
                                        Chi tiết
                                    </a>
                                </td>

                            </tr>
                        <?php
                        }
                    } else { ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">Không có dữ liệu</td>
                        </tr>
                    <?php } ?>
                </tbody>

            </table>
        </div>

    </div>


    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script XÓA COMBO -->
    <script>
        function deleteCombo(MaCombo, btn) {
            Swal.fire({
                title: "Bạn có chắc muốn xóa?",
                text: "Thao tác này không thể hoàn tác!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Xóa",
                cancelButtonText: "Hủy"
            }).then((result) => {
                if (result.isConfirmed) {

                    fetch("index.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: "delete_ajax=" + MaCombo
                        })
                        .then(res => res.text())
                        .then(data => {
                            if (data.trim() === "success") {

                                Swal.fire({
                                    icon: "success",
                                    title: "Đã xóa!",
                                    timer: 1200,
                                    showConfirmButton: false
                                });

                                // Xóa dòng khỏi bảng (không cần reload)
                                btn.closest("tr").remove();

                            } else {
                                Swal.fire({
                                    icon: "error",
                                    title: "Xóa thất bại!",
                                    text: data
                                });
                            }
                        })
                        .catch(err => {
                            Swal.fire({
                                icon: "error",
                                title: "Lỗi máy chủ!",
                                text: err
                            });
                        });

                }
            });
        }
    </script>


    <!-- Script navbar dropdown submenu -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dropdownSubmenus = document.querySelectorAll('.dropdown-submenu');

            dropdownSubmenus.forEach(function(submenu) {
                const submenuLink = submenu.querySelector('a[data-bs-toggle="dropdown"]');
                const submenuDropdown = submenu.querySelector('.dropdown-menu');

                submenu.addEventListener('mouseenter', function() {
                    submenuDropdown.classList.add('show');
                });

                submenu.addEventListener('mouseleave', function() {
                    submenuDropdown.classList.remove('show');
                });

                submenuLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    submenuDropdown.classList.toggle('show');
                });
            });

            document.addEventListener('click', function(e) {
                if (!e.target.closest('.dropdown-submenu')) {
                    document.querySelectorAll('.dropdown-submenu .dropdown-menu').forEach(function(menu) {
                        menu.classList.remove('show');
                    });
                }
            });
        });
    </script>

</body>

</html>