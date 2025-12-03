<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /unitop/backend/lesson/school/project_pizza/sign_in.php");
    exit();
}
if($_SESSION['phanquyen'] != 0){
    echo "Bạn không có quyền truy cập trang này.";
    exit();
}

// Kết nối database
require __DIR__ . '/../../../includes/db_connect.php';

if (!$ketnoi) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

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
        .current-user-row {
            background-color: #fff3cd;
        }
    </style>
</head>
<body>

<?php include '../../navbar_admin.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <!-- Create account form -->
        <div class="col-lg-6">
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

                    <form method="POST" action="/unitop/backend/lesson/school/project_pizza/admin/process/process_create_employee.php">
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

        <!-- Employee list -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fa-solid fa-users"></i> Danh Sách Nhân Viên</h5>
                </div>
                <div class="card-body">
                    <?php
                    // detect common employee/user table names and columns
                    $candidates = ['nhanvien','nhan_vien','employees','employee','admins','admin','users','staff','tbl_nhanvien'];
                    $foundTable = '';
                    foreach ($candidates as $cand) {
                        $safe = mysqli_real_escape_string($ketnoi, $cand);
                        $check = mysqli_query($ketnoi, "SHOW TABLES LIKE '$safe'");
                        if ($check && mysqli_num_rows($check) > 0) { $foundTable = $cand; break; }
                    }

                    if ($foundTable) {
                        $cols = [];
                        $res_cols = mysqli_query($ketnoi, "SHOW COLUMNS FROM `$foundTable`");
                        if ($res_cols) {
                            while ($c = mysqli_fetch_assoc($res_cols)) $cols[] = $c['Field'];
                        }

                        $idCandidates = ['id','ID','MaNV','MaNhanVien','manv','ma_nv','ma'];
                        $nameCandidates = ['name','ten','HoTen','hoten','fullname','full_name'];
                        $emailCandidates = ['email','Email','EMAIL'];
                        $roleCandidates = ['role','phanquyen','quyen','permission','level','role_id'];

                        $idField = null; $nameField = null; $emailField = null; $roleField = null;
                        foreach ($idCandidates as $f) if (in_array($f,$cols)) { $idField = $f; break; }
                        foreach ($nameCandidates as $f) if (in_array($f,$cols)) { $nameField = $f; break; }
                        foreach ($emailCandidates as $f) if (in_array($f,$cols)) { $emailField = $f; break; }
                        foreach ($roleCandidates as $f) if (in_array($f,$cols)) { $roleField = $f; break; }

                        $selectCols = [];
                        if ($idField) $selectCols[] = "`$idField`";
                        if ($nameField) $selectCols[] = "`$nameField`";
                        if ($emailField) $selectCols[] = "`$emailField`";
                        if ($roleField) $selectCols[] = "`$roleField`";
                        if (empty($selectCols)) {
                            $take = array_slice($cols,0,4);
                            foreach ($take as $t) $selectCols[] = "`$t`";
                        }
                        $selectSQL = implode(',', $selectCols);

                        $q = "SELECT $selectSQL FROM `$foundTable` ORDER BY ".($idField?"`$idField` DESC":"1 DESC")." LIMIT 200";
                        $r = mysqli_query($ketnoi, $q);

                        if ($r && mysqli_num_rows($r) > 0) {
                            echo '<div class="table-responsive">';
                            echo '<table class="table table-sm align-middle">';
                            echo '<thead><tr>';
                            // headers
                            foreach ($selectCols as $col) {
                                $clean = trim($col, '`');
                                $label = '';
                                if ($clean === $idField) $label = 'ID';
                                elseif ($clean === $nameField) $label = 'Tên';
                                elseif ($clean === $emailField) $label = 'Email';
                                elseif ($clean === $roleField) $label = 'Phân quyền';
                                else $label = ucfirst(str_replace('_',' ', $clean));
                                echo '<th>'.htmlspecialchars($label).'</th>';
                            }
                            echo '<th>Thao tác</th>';
                            echo '</tr></thead><tbody>';

                            while ($rowEmp = mysqli_fetch_assoc($r)) {
                                $idVal = $idField && isset($rowEmp[$idField]) ? $rowEmp[$idField] : '';
                                $isCurrentUser = ($idVal == $_SESSION['user_id']);
                                
                                // Thêm class để highlight tài khoản đang đăng nhập
                                $rowClass = $isCurrentUser ? 'current-user-row' : '';
                                echo '<tr class="'.$rowClass.'">';
                                
                                foreach ($selectCols as $col) {
                                    $cname = trim($col,'`');
                                    $val = $rowEmp[$cname] ?? '';
                                    // role badge
                                    if ($cname === $roleField) {
                                        $badge = '<span class="badge bg-info">'.htmlspecialchars($val).'</span>';
                                        // convert common numeric roles
                                        if ($val === '0' || $val === 0) $badge = '<span class="badge bg-danger">Quản lý</span>';
                                        if ($val === '1' || $val === 1) $badge = '<span class="badge bg-primary">Nhân viên</span>';
                                        echo '<td>'.$badge.'</td>';
                                    } else {
                                        $displayVal = htmlspecialchars($val);
                                        // Thêm label "Bạn" nếu là tài khoản đang đăng nhập
                                        if ($cname === $nameField && $isCurrentUser) {
                                            $displayVal .= ' <span class="badge bg-warning text-dark">Bạn</span>';
                                        }
                                        echo '<td>'.$displayVal.'</td>';
                                    }
                                }
                                
                                // actions
                                $editLink = $idVal ? 'edit_employee.php?id='.urlencode($idVal) : '#';
                                $delLink = $idVal ? '../../process/delete_employee.php?id='.urlencode($idVal) : '#';
                                
                                echo '<td class="text-nowrap">';
                                echo '<a class="btn btn-sm btn-warning me-1" href="'.htmlspecialchars($editLink).'" title="Sửa"><i class="fa-solid fa-pen"></i></a>';
                                
                                // Chỉ hiển thị nút xóa nếu KHÔNG PHẢI tài khoản đang đăng nhập
                                if (!$isCurrentUser) {
                                    echo '<a class="btn btn-sm btn-danger" href="'.htmlspecialchars($delLink).'" onclick="return confirm(\'Bạn chắc chắn muốn xóa?\')" title="Xóa"><i class="fa-solid fa-trash"></i></a>';
                                } else {
                                    echo '<button class="btn btn-sm btn-secondary" disabled title="Không thể xóa tài khoản đang đăng nhập"><i class="fa-solid fa-ban"></i></button>';
                                }
                                echo '</td>';

                                echo '</tr>';
                            }

                            echo '</tbody></table></div>';
                        } else {
                            echo '<p class="text-muted">Chưa có dữ liệu nhân viên trong bảng <strong>'.htmlspecialchars($foundTable).'</strong>.</p>';
                            echo '<a href="create_account.php" class="btn btn-outline-secondary">Tạo tài khoản nhân viên</a>';
                        }

                    } else {
                        echo '<p class="text-muted">Chưa có dữ liệu nhân viên. (Hiển thị placeholder)</p>';
                        echo '<a href="create_account.php" class="btn btn-outline-secondary">Tạo tài khoản nhân viên</a>';
                    }
                    ?>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php mysqli_close($ketnoi); ?>