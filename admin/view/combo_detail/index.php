<?php
require __DIR__ . '/../../../includes/db_connect.php';

if (!isset($_GET["MaCombo"])) {
    die("Thiếu tham số MaCombo!");
}

$MaCombo = intval($_GET["MaCombo"]);

// Lấy thông tin combo
$sql_combo = "SELECT * FROM combo WHERE MaCombo = $MaCombo";
$kq_combo = mysqli_query($ketnoi, $sql_combo);
$combo = mysqli_fetch_assoc($kq_combo);

if (!$combo) {
    die("Không tìm thấy combo!");
}

// Lấy danh sách chi tiết combo + JOIN lấy giá
$sql_ct = "
SELECT 
    ct.id,
    ct.SoLuong,
    sp.MaSP,
    sp.TenSP,
    sp.Anh AS AnhSP,
    sz.TenSize,
    ss.Gia
FROM chitietcombo ct
JOIN sanpham sp      ON ct.MaSP = sp.MaSP
LEFT JOIN size sz     ON ct.MaSize = sz.MaSize
LEFT JOIN sanpham_size ss ON ct.MaSP = ss.MaSP AND ct.MaSize = ss.MaSize
WHERE ct.MaCombo = $MaCombo
";

$kq_ct = mysqli_query($ketnoi, $sql_ct);

// Tính tổng tiền combo:
$tongTruocGiam = 0;
$dataCombo = [];

if ($kq_ct && $kq_ct->num_rows > 0) {
    while ($row = mysqli_fetch_assoc($kq_ct)) {

        $donGia = $row["Gia"] ?? 0;
        $soLuong = $row["SoLuong"];
        $thanhTien = $donGia * $soLuong;

        $row["ThanhTien"] = $thanhTien;

        $tongTruocGiam += $thanhTien;

        $dataCombo[] = $row;
    }
}

$giamgia = $combo["giamgia"];
$tongSauGiam = $tongTruocGiam - ($tongTruocGiam * $giamgia / 100);

// delete chi tiết combo
if (isset($_POST["delete_id"])) {
    $id = intval($_POST["delete_id"]);
    $sql_del = "DELETE FROM chitietcombo WHERE id = $id";
    mysqli_query($ketnoi, $sql_del);

    if (mysqli_affected_rows($ketnoi) > 0) {
        echo "OK";
    } else {
        echo "ERR";
    }
    exit;
}

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Chi tiết Combo</title>

    <!-- Bootstrap -->
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

        .back-btn {
            font-weight: 500;
        }

        .img-thumb {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
        }
    </style>
</head>

<body>

        
         <?php include __DIR__ . '/../../navbar_admin.php'; ?>
    <!-- Nội dung -->
    <div class="container mt-4">

        <a href="/admin/view/combo/index.php" class="btn btn-secondary back-btn">
            <i class="fa-solid fa-arrow-left"></i> Quay lại combo
        </a>

        <h2 class="main-title"><i class="fa-solid fa-circle-info"></i> Chi tiết Combo: <?= $combo["Tencombo"] ?></h2>
        <p class="text-muted">Mã Combo: <strong><?= $combo["MaCombo"] ?></strong></p>
        <a href="./create.php?MaCombo=<?= $MaCombo ?>&Tencombo=<?= urlencode($combo['Tencombo']) ?>"
            class="btn btn-success">
            <i class="fa-solid fa-plus"></i> Thêm món vào combo
        </a>

        <div class="table-responsive mt-4">
            <table class="table table-bordered table-hover align-middle">
                <thead class="text-center">
                    <tr>
                        <th>ID</th>
                        <th>Ảnh</th>
                        <th>Tên món</th>
                        <th>Size</th>
                        <th>Số lượng</th>
                        <th>Đơn giá</th>
                        <th>Thành tiền</th>
                        <?php if(!empty($dataCombo)) echo "<th>Thao tác</th>"; ?>
                        
                    </tr>
                </thead>

                <tbody class="text-center">
                    <?php if (!empty($dataCombo)) {
                        foreach ($dataCombo as $row) {

                            $imgPath = "../../../img/" . $row["AnhSP"];
                            if (!file_exists($imgPath) || empty($row["AnhSP"])) {
                                $imgPath = "../../../img/combo.png";
                            }
                    ?>
                            <tr>
                                <td><?= $row["id"] ?></td>
                                <td><img src="<?= $imgPath ?>" class="img-thumb"></td>
                                <td><?= $row["TenSP"] ?></td>
                                <td><?= $row["TenSize"] ?: "Không có" ?></td>
                                <td><?= $row["SoLuong"] ?></td>
                                <td><?= number_format($row["Gia"], 0, ',', '.') ?> đ</td>
                                <td class="text-success fw-bold">
                                    <?= number_format($row["ThanhTien"], 0, ',', '.') ?> đ
                                </td>
                                <td>
                                    <button class="btn btn-danger btn-sm btn-delete" data-id="<?= $row['id'] ?>">
                                        Xóa
                                    </button>
                                </td>
                            </tr>
                        <?php
                        }
                    } else { ?>
                        <tr>
                            <td colspan="7" class="text-muted">Không có sản phẩm nào trong combo</td>
                        </tr>
                    <?php } ?>


                </tbody>


            </table>
        </div>

        <!-- Tổng tiền trước giảm -->
        <h4 class="text-end mt-4">
            Tổng tiền:
            <span class="fw-bold text-primary">
                <?= number_format($tongTruocGiam, 0, ',', '.') ?> đ
            </span>
        </h4>

        <!-- Giảm giá -->
        <h5 class="text-end">
            Giảm giá: <strong class="text-danger"><?= $giamgia ?>%</strong>
        </h5>

        <!-- Tổng tiền sau giảm -->
        <h4 class="text-end">
            Tổng tiền sau giảm giá:
            <span class="fw-bold text-success">
                <?= number_format($tongSauGiam, 0, ',', '.') ?> đ
            </span>
        </h4>


    </div>
    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            document.querySelectorAll('.btn-delete').forEach(btn => {
                btn.addEventListener('click', function() {
                    let id = this.dataset.id;

                    Swal.fire({
                        title: "Bạn chắc chắn?",
                        text: "Món này sẽ bị xóa khỏi combo!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#d33",
                        cancelButtonColor: "#3085d6",
                        confirmButtonText: "Xóa",
                        cancelButtonText: "Hủy"
                    }).then((result) => {
                        if (result.isConfirmed) {

                            // gọi AJAX
                            fetch("", {
                                    method: "POST",
                                    headers: {
                                        "Content-Type": "application/x-www-form-urlencoded"
                                    },
                                    body: "delete_id=" + id
                                })
                                .then(res => res.text())
                                .then(data => {

                                    if (data.trim() === "OK") {
                                        Swal.fire({
                                            title: "Đã xóa thành công!",
                                            text: "Đang tải lại trang...",
                                            icon: "success",
                                            timer: 1500, // thời gian tự tắt (ms)
                                            timerProgressBar: true,
                                            showConfirmButton: false
                                        });

                                        setTimeout(() => {
                                            location.reload();
                                        }, 1500);


                                    } else {
                                        Swal.fire({
                                            title: "Lỗi!",
                                            text: "Không thể xóa món.",
                                            icon: "error"
                                        });
                                    }

                                });
                        }
                    });

                });
            });

        });
    </script>


</body>

</html>