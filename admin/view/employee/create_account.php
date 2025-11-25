<?php
session_start();
/*
// Kiểm tra đăng nhập admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../login.php");
    exit();
}
*/

// Kết nối database
$ketnoi = mysqli_connect("localhost:8889", "root", "root", "php_pizza");
mysqli_set_charset($ketnoi, "utf8");

if (!$ketnoi) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

// Lấy thông báo từ session
$success = isset($_SESSION['success']) ? $_SESSION['success'] : "";
$error = isset($_SESSION['error']) ? $_SESSION['error'] : "";
unset($_SESSION['success'], $_SESSION['error']);

// Lấy danh sách nhân viên
$sql_list = "SELECT * FROM admin ORDER BY id DESC";
$employees = mysqli_query($ketnoi, $sql_list);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo Tài Khoản Nhân Viên</title>
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
        .btn-success {
            background: linear-gradient(90deg, #28a745, #66bb6a);
            border: none;
        }
        .table-hover tbody tr:hover {
            background-color: #e8f5e9;
        }
        .badge-admin {
            background-color: #dc3545;
        }
        .badge-staff {
            background-color: #17a2b8;
        }
    </style>
</head>
<body>

<?php include '../../navbar_admin.php'; ?>

<div class="container mt-5">
    <div class="row">
        <!-- Form tạo tài khoản -->
        <div class="col-lg-5 mb-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fa-solid fa-user-plus"></i> Tạo Tài Khoản Nhân Viên</h5>
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

                    <form method="POST" action="../../process/process_create_employee.php">
                        <div class="mb-3">
                            <label class="form-label">Tên nhân viên <span class="text-danger">*</span></label>
                            <input type="text" name="ten" class="form-control" 
                                   placeholder="Nhập tên nhân viên" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" 
                                   placeholder="email@example.com" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" 
                                   placeholder="Tối thiểu 6 ký tự" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                            <input type="password" name="confirm_password" class="form-control" 
                                   placeholder="Nhập lại mật khẩu" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Phân quyền <span class="text-danger">*</span></label>
                            <select name="phanquyen" class="form-select" required>
                                <option value="1">Nhân viên (1)</option>
                                <option value="0">Quản lý (0)</option>
                            </select>
                            <small class="text-muted">0: Quản lý | 1: Nhân viên</small>
                        </div>

                        <button type="submit" class="btn btn-success w-100">
                            <i class="fa-solid fa-save"></i> Tạo Tài Khoản
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Danh sách nhân viên -->
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fa-solid fa-users"></i> Danh Sách Nhân Viên</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Tên</th>
                                    <th>Email</th>
                                    <th>Phân quyền</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($employees) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($employees)): ?>
                                        <tr>
                                            <td><?php echo $row['id']; ?></td>
                                            <td><?php echo htmlspecialchars($row['ten']); ?></td>
                                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                                            <td>
                                                <?php if ($row['phanquyen'] == 0): ?>
                                                    <span class="badge badge-admin">Quản lý</span>
                                                <?php else: ?>
                                                    <span class="badge badge-staff">Nhân viên</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="edit_employee.php?id=<?php echo $row['id']; ?>" 
                                                   class="btn btn-sm btn-warning">
                                                    <i class="fa-solid fa-edit"></i>
                                                </a>
                                                <a href="../../process/delete_employee.php?id=<?php echo $row['id']; ?>" 
                                                   class="btn btn-sm btn-danger"
                                                   onclick="return confirm('Bạn có chắc muốn xóa?')">
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">
                                            Chưa có nhân viên nào
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php mysqli_close($ketnoi); ?>