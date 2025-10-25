<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Panel</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

  <style>
    body {
      background-color: #f9fafb;
      font-family: "Segoe UI", sans-serif;
    }

    /* ✅ Navbar màu xanh lá gradient */
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

    /* ✅ Hover: nền sáng, text đậm */
    .navbar-nav .nav-link:hover,
    .navbar-nav .dropdown:hover .nav-link {
      background-color: rgba(255, 255, 255, 0.25);
      border-radius: 8px;
    }

    /* ✅ Dropdown items */
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

    /* ✅ Căn giữa menu */
    .navbar-nav {
      margin: 0 auto;
    }

    /* ✅ Nút Logout bên phải */
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

    /* ✅ Tiêu đề chính */
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
  <!-- ✅ NAVBAR -->
  <nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
      <a class="navbar-brand" href="#"><i class="fa-solid fa-leaf"></i> Admin Panel</a>

      <button class="navbar-toggler text-white" type="button" data-bs-toggle="collapse"
        data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false"
        aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNavDropdown">
        <!-- Menu chính căn giữa -->
        <ul class="navbar-nav text-center">
          <!-- Quản lý sản phẩm -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
              aria-expanded="false">
              <i class="fa-solid fa-box-open"></i> Quản lý sản phẩm
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="insert_product.php"><i class="fa-solid fa-circle-plus"></i> Thêm sản phẩm</a></li>
              <li><a class="dropdown-item" href="insert_product_size.php"><i class="fa-solid fa-ruler-combined"></i> Quản lý Size</a></li>
              <li><a class="dropdown-item" href="insert_category.php"><i class="fa-solid fa-tags"></i> Danh mục</a></li>
            </ul>
          </li>

          <!-- Quản lý khách hàng -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
              aria-expanded="false">
              <i class="fa-solid fa-users"></i> Quản lý khách hàng
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="list_customer.php"><i class="fa-solid fa-user"></i> Danh sách KH</a></li>
              <li><a class="dropdown-item" href="add_customer.php"><i class="fa-solid fa-user-plus"></i> Thêm KH</a></li>
            </ul>
          </li>

          <!-- Quản lý đơn hàng -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
              aria-expanded="false">
              <i class="fa-solid fa-receipt"></i> Quản lý đơn hàng
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="list_orders.php"><i class="fa-solid fa-file-invoice"></i> Danh sách đơn hàng</a></li>
              <li><a class="dropdown-item" href="report.php"><i class="fa-solid fa-chart-line"></i> Thống kê doanh thu</a></li>
            </ul>
          </li>
        </ul>

        <!-- ✅ Logout nằm bên phải -->
        <div class="ms-auto">
          <a href="logout.php" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a>
        </div>
      </div>
    </div>
  </nav>

  <!-- ✅ Nội dung chính -->
  <div class="container text-center">
    <h1 class="main-title">Chào mừng đến trang quản trị!</h1>
    <p class="text-muted">Chọn menu trên để bắt đầu quản lý dữ liệu hệ thống.</p>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
