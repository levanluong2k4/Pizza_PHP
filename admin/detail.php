<?php
$conn = new mysqli("localhost", "root", "", "php_pizza");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

$maDH = isset($_GET['MaDH']) ? (int)$_GET['MaDH'] : 0;
if ($maDH <= 0) {
    die("Mã đơn hàng không hợp lệ.");
}

$sqlOrder = "
    SELECT d.*,
           k.HoTen   AS TenKH,
           k.Email   AS EmailKH,
           k.SoDT    AS SoDTKH
    FROM donhang d
    LEFT JOIN khachhang k ON d.MaKH = k.MaKH
    WHERE d.MaDH = ?
";
$stmtOrder = $conn->prepare($sqlOrder);
$stmtOrder->bind_param("i", $maDH);
$stmtOrder->execute();
$orderResult = $stmtOrder->get_result();
$order = $orderResult->fetch_assoc();
$stmtOrder->close();

if (!$order) {
    die("Không tìm thấy đơn hàng.");
}

$st = trim($order['trangthai'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action']; // next | cancel
    $currentStat = $st;
    $newStatus = null;
    $error = '';

    if (strcasecmp($currentStat, 'Chờ xử lý') == 0) {
        if ($action === 'next') {
            $newStatus = 'Đang giao';
        } elseif ($action === 'cancel') {
            $newStatus = 'Đã huỷ';
        }
    } elseif (strcasecmp($currentStat, 'Đang giao') == 0 || strcasecmp($currentStat, 'Chờ giao') == 0) {
        if ($action === 'next') {
            $newStatus = 'Hoàn thành';
        } elseif ($action === 'cancel') {
            $error = "Đơn đang giao, không thể huỷ.";
        }
    } else {
        $error = "Đơn ở trạng thái '$currentStat' không thể cập nhật nữa.";
    }

    if ($newStatus === null && $error === '') {
        $error = "Hành động không hợp lệ.";
    }

    if ($error !== '') {
        echo "<script>alert('$error'); window.location.href='detail.php?MaDH=$maDH';</script>";
        exit;
    }

    // Cập nhật DB
    $stmtUpd = $conn->prepare("UPDATE donhang SET trangthai = ? WHERE MaDH = ?");
    $stmtUpd->bind_param("si", $newStatus, $maDH);
    $stmtUpd->execute();
    $stmtUpd->close();

    header("Location: detail.php?MaDH=" . $maDH);
    exit;
}

$classStatus = match ($st) {
    "Chờ xử lý"          => "cho-xu-ly",
    "Đang giao"                      => "dang-giao",
    "Hoàn thành"  => "hoan-thanh",
    "Đã huỷ"                         => "da-huy",
    default                          => "cho-xu-ly",
};

$sqlItems = "
    SELECT c.*,
           sp.TenSP,
           s.TenSize,
           ss.Gia
    FROM chitietdonhang c
    JOIN sanpham sp      ON c.MaSP = sp.MaSP
    LEFT JOIN size s     ON c.MaSize = s.MaSize
    LEFT JOIN sanpham_size ss ON ss.MaSP = c.MaSP AND ss.MaSize = c.MaSize
    WHERE c.MaDH = ?
";
$stmtItems = $conn->prepare($sqlItems);
$stmtItems->bind_param("i", $maDH);
$stmtItems->execute();
$items = $stmtItems->get_result();
$stmtItems->close();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Chi tiết đơn hàng #<?= $maDH ?></title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

    <style>
        body {
            background-color: #f3f4f6;
            font-family: "Segoe UI", sans-serif;
        }

        /* Banner xanh giống Admin Panel */
        .page-hero {
            background: linear-gradient(90deg, #28a745, #32c85b);
            color: #fff;
            padding: 18px 0;
            margin-bottom: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, .08);
        }

        .page-hero h1 {
            font-size: 26px;
            font-weight: 800;
            margin: 0;
        }

        .page-hero .sub {
            opacity: 0.9;
            font-size: 14px;
        }

        .breadcrumb-link {
            color: #e8ffe9;
            text-decoration: none;
            font-size: 13px;
        }

        .breadcrumb-link:hover {
            text-decoration: underline;
        }

        .order-card {
            border-radius: 16px;
            border: none;
        }

        .card-title-strong {
            font-weight: 700;
            font-size: 18px;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 999px;
            font-weight: 600;
            color: #fff;
            font-size: 13px;
        }

        .cho-xu-ly {
            background: #f1c40f;
        }

        .dang-giao {
            background: #28a745;
        }

        .hoan-thanh {
            background: #007bff;
        }

        .da-huy {
            background: #e74c3c;
        }

        .table thead {
            background: #e7f7ec;
        }

        .table thead th {
            border-bottom-width: 0;
            font-weight: 700;
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #28a745;
            margin-bottom: 12px;
        }
    </style>
</head>

<body>

<!-- Banner -->
<div class="page-hero">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <div class="mb-1">
                    <a href="list_orders.php" class="breadcrumb-link">
                        <i class="fa-solid fa-arrow-left"></i> Quay về danh sách đơn
                    </a>
                </div>
                <h1>Chi tiết đơn hàng #<?= $maDH ?></h1>
                <div class="sub">
                    Khách hàng: <strong><?= htmlspecialchars($order['Tennguoinhan'] ?: $order['TenKH']) ?></strong> ·
                    Ngày đặt: <?= htmlspecialchars($order['ngaydat']) ?>
                </div>
            </div>
            <div class="text-end">
                <div class="mb-1 small">Trạng thái hiện tại</div>
                <span class="status-badge <?= $classStatus ?>">
                    <?= htmlspecialchars($st) ?>
                </span>
                <div class="mt-1 fw-semibold">
                    Tổng tiền: <?= number_format($order['TongTien']) ?>₫
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container mb-5">

    <!-- Thông tin đơn + cập nhật trạng thái -->
    <div class="row g-4 mb-4">
        <!-- Thông tin người nhận -->
        <div class="col-md-7">
            <div class="card shadow-sm order-card">
                <div class="card-body">
                    <h5 class="card-title-strong mb-3">
                        <i class="fa-solid fa-user-circle me-2 text-success"></i>
                        Thông tin giao hàng
                    </h5>
                    <p class="mb-1"><strong>Tên người nhận:</strong> <?= htmlspecialchars($order['Tennguoinhan']) ?></p>
                    <p class="mb-1"><strong>SĐT người nhận:</strong> <?= htmlspecialchars($order['sdtnguoinhan']) ?></p>
                    <p class="mb-3"><strong>Địa chỉ nhận:</strong> <?= htmlspecialchars($order['diachinguoinhan']) ?></p>

                    <?php if (!$order['is_guest'] && $order['TenKH']): ?>
                        <hr>
                        <p class="mb-1"><strong>Tài khoản đặt hàng:</strong> <?= htmlspecialchars($order['TenKH']) ?></p>
                        <p class="mb-0"><strong>Email:</strong> <?= htmlspecialchars($order['EmailKH']) ?></p>
                    <?php else: ?>
                        <p class="text-muted mb-0"><em>Đơn được đặt với tư cách khách vãng lai (guest).</em></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Cập nhật trạng thái -->
        <div class="col-md-5">
            <div class="card shadow-sm order-card">
                <div class="card-body">
                    <h5 class="card-title-strong mb-3">
                        <i class="fa-solid fa-pen-to-square me-2 text-success"></i>
                        Cập nhật trạng thái
                    </h5>

                    <?php if (strcasecmp($st, 'Chờ xử lý') == 0): ?>
                        <form method="post" class="d-flex flex-column gap-3">
                            <div class="small text-muted">
                                Luồng xử lý: <strong>Chờ xử lý → Đang giao → Hoàn thành</strong>.<br>
                                Từ trạng thái <strong>"Chờ xử lý"</strong> bạn có thể chuyển sang <strong>"Đang giao"</strong> hoặc <strong>Huỷ đơn</strong>.
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" name="action" value="next" class="btn btn-sm btn-primary">
                                    Chuyển sang "Đang giao"
                                </button>
                                <button type="submit" name="action" value="cancel" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Bạn có chắc chắn muốn huỷ đơn này không?');">
                                    Huỷ đơn
                                </button>
                            </div>
                        </form>

                    <?php elseif (strcasecmp($st, 'Đang giao') == 0 || strcasecmp($st, 'Chờ giao') == 0): ?>
                        <form method="post" class="d-flex flex-column gap-3">
                            <div class="small text-muted">
                                Đơn đang ở trạng thái <strong>"<?= htmlspecialchars($st) ?>"</strong>.<br>
                                Không thể huỷ đơn khi đang giao, chỉ có thể xác nhận <strong>"Giao thành công"</strong>.
                            </div>
                            <button type="submit" name="action" value="next" class="btn btn-sm btn-success">
                                Xác nhận"hoàn thành"
                            </button>
                        </form>

                    <?php else: ?>
                        <p class="text-muted mb-0">
                            Đơn ở trạng thái <strong><?= htmlspecialchars($st) ?></strong>, không thể cập nhật thêm.
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Danh sách sản phẩm -->
    <div class="card shadow-sm order-card">
        <div class="card-body">
            <h5 class="section-title">
                <i class="fa-solid fa-pizza-slice me-2"></i> Danh sách sản phẩm
            </h5>

            <div class="table-responsive">
                <table class="table table-hover align-middle text-center mb-0">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Size</th>
                            <th>Số lượng</th>
                            <th>Đơn giá</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($items && $items->num_rows > 0): ?>
                        <?php while ($it = $items->fetch_assoc()): ?>
                            <?php
                                // Lấy đơn giá: ưu tiên từ sanpham_size.Gia, nếu null thì tính từ ThanhTien / SoLuong
                                $gia = $it['Gia'] !== null ? (float)$it['Gia'] : (
                                    $it['SoLuong'] > 0 ? (float)$it['ThanhTien'] / (int)$it['SoLuong'] : 0
                                );
                            ?>
                            <tr>
                                <td class="text-start">
                                    <?= htmlspecialchars($it['TenSP']) ?>
                                </td>
                                <td><?= htmlspecialchars($it['TenSize'] ?? '-') ?></td>
                                <td><?= (int)$it['SoLuong'] ?></td>
                                <td><?= number_format($gia) ?>₫</td>
                                <td><?= number_format($it['ThanhTien']) ?>₫</td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-muted">Không có sản phẩm trong đơn hàng này.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>

</body>
</html>
