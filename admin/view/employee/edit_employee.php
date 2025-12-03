<?php
session_start();

// ⚠️ SỬA: Thêm dòng này để giả lập đăng nhập (test tạm)
$_SESSION['user_id'] = 1;  // ← THÊM DÒNG NÀY

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /unitop/backend/lesson/school/project_pizza/sign_in.php");
    exit();
}
if($_SESSION['phanquyen'] != 0){
    echo "Bạn không có quyền truy cập trang này.";
    exit();
}
require __DIR__ . '/../../../includes/db_connect.php';


if (!$ketnoi) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

// Kiểm tra có ID không
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "ID không hợp lệ!";
    header("Location: create_account.php");
    exit();
}

$id = (int)$_GET['id'];

// Lấy thông tin nhân viên
$sql = "SELECT * FROM admin WHERE id='$id'";
$result = mysqli_query($ketnoi, $sql);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = "Nhân viên không tồn tại!";
    header("Location: create_account.php");
    exit();
}

$employee = mysqli_fetch_assoc($result);

// Lấy thông báo từ session
$success = isset($_SESSION['success']) ? $_SESSION['success'] : "";
$error = isset($_SESSION['error']) ? $_SESSION['error'] : "";
unset($_SESSION['success'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Phân Quyền Nhân Viên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <style>
        body {
            background-color: #f9fafb;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .form-label {
            font-weight: 600;
            color: #495057;
        }
        .btn-warning {
            background: linear-gradient(90deg, #ffc107, #ffb300);
            border: none;
            color: white;
        }
        .info-text {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<?php include '../../navbar_admin.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0"><i class="fa-solid fa-user-shield"></i> Sửa Phân Quyền Nhân Viên</h5>
                </div>
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fa-solid fa-check-circle"></i> <?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fa-solid fa-exclamation-circle"></i> <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="../../process/process_edit_employee.php">
                        <input type="hidden" name="id" value="<?php echo $employee['id']; ?>">

                        <div class="mb-3">
                            <label class="form-label">Tên nhân viên</label>
                            <div class="info-text">
                                <i class="fa-solid fa-user"></i> <?php echo htmlspecialchars($employee['ten']); ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <div class="info-text">
                                <i class="fa-solid fa-envelope"></i> <?php echo htmlspecialchars($employee['email']); ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Phân quyền <span class="text-danger">*</span></label>
                            <select name="phanquyen" class="form-select" required>
                                <option value="1" <?php echo ($employee['phanquyen'] == 1) ? 'selected' : ''; ?>>
                                    Nhân viên (1)
                                </option>
                                <option value="0" <?php echo ($employee['phanquyen'] == 0) ? 'selected' : ''; ?>>
                                    Quản lý (0)
                                </option>
                            </select>
                            <small class="text-muted">0: Quản lý | 1: Nhân viên</small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-warning">
                                <i class="fa-solid fa-save"></i> Cập Nhật Phân Quyền
                            </button>
                            <a href="create_account.php" class="btn btn-secondary">
                                <i class="fa-solid fa-arrow-left"></i> Quay Lại
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php mysqli_close($ketnoi); ?>