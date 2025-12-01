<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /unitop/backend/lesson/school/project_pizza/sign_in.php");
    exit();
}

require __DIR__ . '/../../../includes/db_connect.php';

$errors = [];
$success = "";

// Xử lý submit
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $Tencombo = trim($_POST["Tencombo"] ?? "");
    $giamgia = intval($_POST["giamgia"] ?? 0);

    // Xử lý ảnh
    $Anh = "default.png"; // ảnh mặc định

    if (isset($_FILES["Anh"]) && $_FILES["Anh"]["error"] === 0) {
        $targetDir = "../../../img/";
        $ext = pathinfo($_FILES["Anh"]["name"], PATHINFO_EXTENSION);
        $fileName = "combo_" . time() . "." . $ext;
        $targetPath = $targetDir . $fileName;

        if (move_uploaded_file($_FILES["Anh"]["tmp_name"], $targetPath)) {
            $Anh = $fileName;
        }
    }

    if ($Tencombo === "") $errors[] = "Tên combo không được để trống";
    if ($giamgia < 0 || $giamgia > 100) $errors[] = "Giảm giá phải từ 0–100%";

    if (empty($errors)) {

        $sql = "INSERT INTO combo (Tencombo, Anh, giamgia) VALUES (?, ?, ?)";
        $stmt = $ketnoi->prepare($sql);
        $stmt->bind_param("ssi", $Tencombo, $Anh, $giamgia);

        if ($stmt->execute()) {
            header("Location: index.php");
            exit;
        } else {
            $errors[] = "Lỗi SQL: " . $ketnoi->error;
        }
    }
}
?>



<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Thêm Combo</title>

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

        .form-container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
    </style>
</head>

<body>

    <?php include __DIR__ . '/../../navbar_admin.php'; ?>

    <div class="container mt-4">
        <h2 class="main-title"><i class="fa-solid fa-plus"></i> Thêm Combo</h2>

        <a href="/unitop/backend/lesson/school/project_pizza/admin/view/combo" class="btn btn-secondary mt-2">
            <i class="fa-solid fa-arrow-left"></i> Quay lại danh sách
        </a>

        <div class="form-container mt-4">

            <?php if (!empty($errors)) { ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $err) echo "<div>- $err</div>"; ?>
                </div>
            <?php } ?>

            <form method="POST" enctype="multipart/form-data">

                <div class="mb-3">
                    <label class="form-label">Tên Combo:</label>
                    <input type="text" name="Tencombo" class="form-control" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Giảm giá (%):</label>
                    <input type="number" name="giamgia" class="form-control" min="0" max="100" value="0" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Ảnh Combo:</label>
                    <input type="file" name="Anh" class="form-control">
                    <small class="text-muted">Nếu không chọn ảnh → dùng ảnh mặc định.</small>
                </div>

                <button type="submit" class="btn btn-success px-4">
                    <i class="fa-solid fa-check"></i> Lưu Combo
                </button>

            </form>
        </div>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

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