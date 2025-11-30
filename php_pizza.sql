-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 09, 2025 lúc 02:23 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `php_pizza`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `ten` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phanquyen` int(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `admin`
--

INSERT INTO `admin` (`id`, `ten`, `password`, `email`, `phanquyen`) VALUES
(1, 'luong123', '123', 'luong@gmail.com', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chitietdonhang`
--

CREATE TABLE `chitietdonhang` (
  `id` int(11) NOT NULL,
  `MaDH` int(11) NOT NULL,
  `MaSP` int(11) NOT NULL,
  `MaSize` int(11) DEFAULT NULL,
  `SoLuong` int(11) NOT NULL,
  `ThanhTien` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `chitietdonhang`
--

INSERT INTO `chitietdonhang` (`id`, `MaDH`, `MaSP`, `MaSize`, `SoLuong`, `ThanhTien`) VALUES
(1, 1, 14, 2, 2, 258000.00),
(2, 1, 15, 2, 1, 119000.00),
(3, 2, 14, 1, 1, 109000.00),
(4, 3, 14, 3, 1, 149000.00),
(5, 4, 14, 2, 1, 129000.00),
(6, 5, 14, 2, 1, 129000.00),
(7, 6, 14, 2, 1, 129000.00),
(8, 7, 14, 3, 1, 149000.00),
(9, 8, 15, 3, 1, 139000.00),
(10, 8, 34, 3, 1, 139000.00),
(11, 8, 14, 3, 1, 149000.00),
(12, 9, 14, 2, 1, 129000.00),
(13, 9, 14, 3, 2, 298000.00),
(14, 10, 14, 2, 1, 129000.00),
(15, 11, 14, 3, 1, 149000.00),
(16, 12, 14, 3, 1, 149000.00),
(17, 13, 14, 3, 1, 149000.00),
(18, 14, 15, 3, 1, 139000.00),
(19, 15, 14, 3, 1, 149000.00),
(20, 16, 14, 3, 1, 149000.00),
(21, 17, 14, 1, 1, 109000.00),
(22, 18, 34, 2, 1, 119000.00),
(23, 19, 15, 3, 1, 139000.00),
(24, 20, 14, 3, 1, 149000.00),
(25, 21, 14, 3, 1, 149000.00),
(26, 22, 14, 2, 1, 129000.00),
(27, 23, 14, 3, 3, 447000.00),
(28, 23, 6, 11, 3, 30000.00),
(29, 24, 14, 1, 1, 109000.00),
(30, 27, 14, 3, 15, 2235000.00),
(31, 29, 14, 2, 1, 129000.00),
(32, 30, 14, 1, 1, 109000.00),
(33, 31, 14, 3, 9, 1341000.00),
(34, 31, 35, 3, 4, 596000.00),
(35, 31, 34, 2, 2, 238000.00),
(36, 32, 14, 3, 1, 149000.00),
(37, 33, 14, 2, 1, 129000.00),
(38, 34, 15, 3, 1, 139000.00),
(39, 34, 35, 3, 1, 149000.00),
(40, 34, 6, 11, 3, 30000.00),
(41, 35, 7, 12, 1, 15000.00),
(42, 35, 5, 12, 1, 24000.00),
(43, 35, 33, 3, 1, 119000.00),
(44, 35, 14, 3, 1, 149000.00);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chitietgiohang`
--

CREATE TABLE `chitietgiohang` (
  `CartID` int(11) NOT NULL,
  `MaSP` int(11) NOT NULL,
  `MaSize` int(11) DEFAULT NULL,
  `Quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `chitietgiohang`
--

INSERT INTO `chitietgiohang` (`CartID`, `MaSP`, `MaSize`, `Quantity`) VALUES
(26, 14, 3, 1),
(27, 15, 2, 1),
(28, 14, 3, 1),
(32, 14, 3, 5),
(32, 15, 3, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `donhang`
--

CREATE TABLE `donhang` (
  `MaDH` int(11) NOT NULL,
  `MaKH` int(11) DEFAULT NULL,
  `ngaydat` datetime DEFAULT current_timestamp(),
  `TongTien` decimal(10,2) NOT NULL,
  `trangthai` varchar(50) DEFAULT 'Chờ xử lý',
  `diachinguoinhan` text DEFAULT NULL,
  `sdtnguoinhan` varchar(20) DEFAULT NULL,
  `Tennguoinhan` varchar(20) DEFAULT NULL,
  `is_guest` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `donhang`
--

INSERT INTO `donhang` (`MaDH`, `MaKH`, `ngaydat`, `TongTien`, `trangthai`, `diachinguoinhan`, `sdtnguoinhan`, `Tennguoinhan`, `is_guest`) VALUES
(1, 11, '2025-10-29 22:43:21', 377000.00, 'Chờ xử lý', '', '-2099239230', 'Luongle', 0),
(2, 11, '2025-10-29 22:45:13', 109000.00, 'Chờ xử lý', '', '-2099239230', 'Luongle', 0),
(3, 11, '2025-10-29 23:19:24', 149000.00, 'Chờ xử lý', 'fg', '-2099239230', 'Luongle', 0),
(4, 11, '2025-10-29 23:29:22', 129000.00, 'Chờ xử lý', 'fg', '-2099239230', 'Luongle', 0),
(5, 11, '2025-10-29 23:32:47', 129000.00, 'Giao thành công', 'gfgf', '-2099239232', 'Luongle', 0),
(6, 11, '2025-10-29 23:39:20', 129000.00, 'Chờ xử lý', 'ffgfg', '-2099239232', 'Luongle', 0),
(7, 11, '2025-10-29 23:49:46', 149000.00, 'Chờ xử lý', 'ffgfg', '-2099239232', 'Luongle', 0),
(8, 11, '2025-10-30 11:31:04', 427000.00, 'Chờ xử lý', 'hghgh', '0112245454', 'lê văn lương', 0),
(9, 11, '2025-10-30 14:58:03', 427000.00, 'Chờ xử lý', 'Phường Hợp Minh, Thành phố Yên Bái, Tỉnh Yên Bái', '0112245454', 'Luongle12', 0),
(10, 11, '2025-10-30 15:01:54', 129000.00, 'Chờ xử lý', 'Phường Hợp Minh, Thành phố Yên Bái, Tỉnh Yên Bái', '-2099239232', 'Luongle', 0),
(11, 11, '2025-10-30 15:05:19', 149000.00, 'Chờ xử lý', 'Phường Hợp Minh, Thành phố Yên Bái, Tỉnh Yên Bái', '-2099239232', 'Luongle', 0),
(12, 11, '2025-10-30 15:06:55', 149000.00, 'Chờ xử lý', 'Phường Hợp Minh, Thành phố Yên Bái, Tỉnh Yên Bái', '-2099239232', 'Luongle', 0),
(13, 11, '2025-10-30 15:16:07', 149000.00, 'Chờ xử lý', 'Phường Hợp Minh, Thành phố Yên Bái, Tỉnh Yên Bái', '-2099239232', 'Luongle', 0),
(14, 11, '2025-10-30 15:31:37', 139000.00, 'Chờ xử lý', '62/11 đặng tất, Xã Xín Cái, Huyện Mèo Vạc, Tỉnh Hà Giang', '-2099239232', 'Luongle', 0),
(15, 11, '2025-10-30 15:32:22', 149000.00, 'Chờ xử lý', '62/11 đặng tất, Xã Xín Cái, Huyện Mèo Vạc, Tỉnh Hà Giang', '-2099239232', 'Luongle', 0),
(16, 11, '2025-10-30 15:43:05', 149000.00, 'Chờ xử lý', 'Xã Xín Cái, Huyện Mèo Vạc, Tỉnh Hà Giang', '-2099239232', 'Luongle', 0),
(17, 11, '2025-10-30 15:51:46', 109000.00, 'Chờ xử lý', 'Xã Xín Cái, Huyện Mèo Vạc, Tỉnh Hà Giang', '-2099239232', 'Luongle', 0),
(18, 11, '2025-10-30 15:57:38', 119000.00, 'Chờ xử lý', 'Xã Xín Cái, Huyện Mèo Vạc, Tỉnh Hà Giang', '-2099239232', 'Luongle', 0),
(19, 11, '2025-10-30 15:59:00', 139000.00, 'Chờ giao', 'Xã Xín Cái, Huyện Mèo Vạc, Tỉnh Hà Giang', '-2099239232', 'Luongle', 0),
(20, 11, '2025-10-30 16:07:00', 149000.00, 'Chờ xử lý', 'Xã Xín Cái, Huyện Mèo Vạc, Tỉnh Hà Giang', '-2099239232', 'Luongle', 0),
(21, 11, '2025-10-30 17:07:21', 149000.00, 'Chờ xử lý', 'Xã Xín Cái, Huyện Mèo Vạc, Tỉnh Hà Giang', '-2099239232', 'Luongle', 0),
(22, 11, '2025-10-30 17:19:42', 129000.00, 'Chờ xử lý', 'Xã Xín Cái, Huyện Mèo Vạc, Tỉnh Hà Giang', '-2099239232', 'Luongle', 0),
(23, 11, '2025-10-30 21:35:23', 477000.00, 'Chờ xử lý', '62/11 đặng tất, Xã Bình Sơn, Thành phố Sông Công, Tỉnh Thái Nguyên', '2099239232', 'Luongleghghn', 0),
(24, 17, '2025-10-30 23:14:09', 109000.00, 'Đang giao', 'luong, Xã Tam Thanh, Huyện Tân Sơn, Tỉnh Phú Thọ', '0112', 'luongletytbvb445', 0),
(27, 11, '2025-11-03 23:00:33', 2235000.00, 'Chờ xử lý', '62/11 đặng tất, Xã Xín Cái, Huyện Mèo Vạc, Tỉnh Hà Giang', '2099239232', 'Luongleghghn', 0),
(29, NULL, '2025-11-03 23:54:53', 129000.00, 'Chờ xử lý', '62/11 đặng tất, Xã Thống Nhất, Huyện Lạc Thủy, Tỉnh Hoà Bình', '0112245454', 'Luongle12', 1),
(30, 11, '2025-11-03 23:57:22', 109000.00, 'Chờ xử lý', '62/11 đặng tất, Xã Minh Xuân, Huyện Lục Yên, Tỉnh Yên Bái', '0112245454', 'Luongle12', 0),
(31, 11, '2025-11-05 15:14:43', 2175000.00, 'Chờ xử lý', '62/11 đặng tất, Xã Tiên Hội, Huyện Đại Từ, Tỉnh Thái Nguyên', '0112245454', 'Luongle12', 0),
(32, 11, '2025-11-05 16:22:34', 149000.00, 'Chờ xử lý', '64gg, Xã Đông Quan', '0112245454', 'Luongle12', 0),
(33, 24, '2025-11-08 15:08:24', 129000.00, 'Chờ xử lý', '62/11 đặng tất, Xã Vạn Phái, Thành phố Phổ Yên, Tỉnh Thái Nguyên', '9989898989', 't565', 0),
(34, 24, '2025-11-08 22:20:38', 318000.00, 'Chờ xử lý', '62/11 đặng tất, Phường Tứ Liên, Quận Tây Hồ, Thành phố Hà Nội', '0799462980', 't565', 0),
(35, 24, '2025-11-08 22:38:14', 307000.00, 'Chờ xử lý', '62/11 đặng tất, Xã Vạn Phái, Thành phố Phổ Yên, Tỉnh Thái Nguyên', '0112245454', 'Luongle12', 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `giohang`
--

CREATE TABLE `giohang` (
  `CartID` int(11) NOT NULL,
  `MaKH` int(11) NOT NULL,
  `ngaytao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `giohang`
--

INSERT INTO `giohang` (`CartID`, `MaKH`, `ngaytao`) VALUES
(26, 12, '2025-10-30 14:40:34'),
(27, 16, '2025-10-30 15:25:33'),
(28, 17, '2025-10-30 16:18:59'),
(32, 11, '2025-11-05 09:22:50');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `khachhang`
--

CREATE TABLE `khachhang` (
  `MaKH` int(11) NOT NULL,
  `HoTen` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `SoDT` varchar(20) DEFAULT NULL,
  `MatKhau` varchar(255) NOT NULL,
  `ngaytao` timestamp NOT NULL DEFAULT current_timestamp(),
  `token` int(50) NOT NULL,
  `tinhthanhpho` varchar(50) DEFAULT NULL,
  `huyenquan` varchar(50) DEFAULT NULL,
  `xaphuong` varchar(50) DEFAULT NULL,
  `sonha` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `khachhang`
--

INSERT INTO `khachhang` (`MaKH`, `HoTen`, `Email`, `SoDT`, `MatKhau`, `ngaytao`, `token`, `tinhthanhpho`, `huyenquan`, `xaphuong`, `sonha`) VALUES
(11, 'li', 'levanluong18t@gmail.com', '0000000000', '123', '2025-10-25 16:11:15', 5, 'Tỉnh Phú Thọ', 'Huyện Cẩm Khê', 'Xã Hùng Việt', '62/11 đặng tất'),
(12, '[value-2]', 'levanluong1t@gmail.com', '[value-4]', '123', '2025-10-25 16:16:25', 0, '[value-9]', '[value-10]', '[value-11]', '[value-12]'),
(16, '[value-2]', 'levanluongt@gmail.com', '[value-4]', '1', '2025-10-25 16:32:25', 0, '[value-9]', '[value-10]', '[value-11]', '[value-12]'),
(17, '[value-2]', 'levanluong@gmail.com', '[value-4]', '123', '2025-10-30 05:17:14', 0, '[value-9]', '[value-10]', '[value-11]', '[value-12]'),
(24, 'Luongle12', 'tranthuhuon55ntu@gmail.com', '0112245454', '$2y$10$WuG6aqMdZ6R870bjxVGYNufLea5xa8sLXoQQPxtgu4EFuFY0W.d6i', '2025-11-07 16:09:43', 0, 'Tỉnh Thái Nguyên', 'Thành phố Phổ Yên', 'Xã Vạn Phái', '62/11 đặng tất');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `loaisanpham`
--

CREATE TABLE `loaisanpham` (
  `MaLoai` int(11) NOT NULL,
  `TenLoai` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `loaisanpham`
--

INSERT INTO `loaisanpham` (`MaLoai`, `TenLoai`) VALUES
(1, 'Pizza Hải Sản Cao Cấp'),
(2, 'Pizza Thập Cẩm'),
(3, 'Pizza Truyền Thống'),
(5, 'Pizza Đặc Biệt'),
(6, 'Khai Vị'),
(7, 'Mỳ Ý - Pasta'),
(8, 'Salad'),
(9, 'Tráng Miệng'),
(10, 'Đồ Uống');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sanpham`
--

CREATE TABLE `sanpham` (
  `MaSP` int(11) NOT NULL,
  `TenSP` varchar(100) NOT NULL,
  `MoTa` text DEFAULT NULL,
  `Anh` varchar(255) DEFAULT NULL,
  `MaLoai` int(11) NOT NULL,
  `NgayThem` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `sanpham`
--

INSERT INTO `sanpham` (`MaSP`, `TenSP`, `MoTa`, `Anh`, `MaLoai`, `NgayThem`) VALUES
(5, '7 Up', 'Nước giải khát có gas hương chanh', '/img/7up.png', 10, '2025-10-25'),
(6, 'Pepsi', 'Nước giải khát có gas hương cola', '/img/pepsi.png', 10, '2025-10-25'),
(7, 'Coca Cola', 'Nước giải khát có gas hương cola', '/img/coca.png', 10, '2025-10-25'),
(8, 'Fanta Nho', 'Nước giải khát có gas hương nho', '/img/fanta.png', 10, '2025-10-25'),
(9, 'Pepsi Không Đường Vị Chanh', 'Pepsi không đường hương vị chanh tươi mát', '/img/pepsi_khong_duong_chanh.png', 10, '2025-10-25'),
(10, 'Pepsi Không Đường', 'Pepsi không đường hương vị truyền thống', '/img/pepsi_khong_duong.png', 10, '2025-10-25'),
(11, 'Pepsi Lon', 'Pepsi hương vị truyền thống, lon tiện lợi', '/img/pepsi_lon.png', 10, '2025-10-25'),
(12, 'Mirinda Soda Kem Lon', 'Nước giải khát Mirinda vị soda kem, lon tiện lợi', '/img/mirinda_soda_kem.png', 10, '2025-10-25'),
(13, 'Pizza Hải Sản Pesto Xanh', '                        Hải sản tươi ngon với sốt pesto xanh đặc biệt, phủ phô mai hảo hạng\r\n                    ', 'img/690171b515cd0.png', 5, '2025-10-25'),
(14, 'Pizza Hải Sản Pesto Xanh Full Topping', '                        Phiên bản đặc biệt với topping hải sản phủ đầy, sốt pesto thơm ngon\r\n                    ', 'img/690171cb99f0e.png', 5, '2025-10-25'),
(15, 'Pizza 4 Cheese Dừa Non', '                        Sự kết hợp độc đáo giữa 4 loại phô mai và dừa non tươi mát, mật hoa dừa\r\n                    ', 'img/690172223b65c.png', 5, '2025-10-25'),
(16, 'Pizza 4 Cheese Dừa Non Tôm Nõn', '                        Pizza 4 cheese với thêm tôm nõn tươi và dừa non, mật hoa dừa thơm ngon\r\n                    ', 'img/690172576572f.png', 10, '2025-10-25'),
(17, 'Pizza Seafood Cocktail', '                        Hải sản cocktail cao cấp với tôm, mực, nghêu và sốt đặc biệt\r\n                    ', 'img/6901715e6de38.jpg', 1, '2025-10-25'),
(18, 'Pizza Hawaiian', '                        Pizza truyền thống với thịt nguội, dứa và phô mai mozzarella\r\n                    ', 'img/6901718a68df9.jpg', 1, '2025-10-25'),
(23, 'Pizza Thịt Nguội Xúc Xích', 'Thịt nguội, xúc xích, dứa và xốt Thousand Island.\r\n                    ', 'img/690170c2cdcd0.jpg', 2, '2025-10-29'),
(24, 'Pizza Hải Sản Cao Cấp', '\r\n          Tôm, cua, mực, nghêu và xốt Marinara.          ', 'img/6901732e941bb.png', 1, '2025-10-29'),
(25, 'Pizza Tôm Cocktail', '\r\n              Tôm, nấm, dứa, cà chua và xốt Thousand Island.      ', 'img/6901736ff0cf4.png', 1, '2025-10-29'),
(26, 'Pizza Thịt Xông Khói Đặc Biệt', 'Thịt giăm bông, thịt xông khói, ớt xanh và cà chua.\r\n                    ', 'img/690173ca084ef.png', 2, '2025-10-29'),
(27, 'Pizza Thịt Nguội Kiểu Canada', 'Thịt nguội và bắp ngọt.\r\n                    ', 'img/6901741044318.jpg', 1, '2025-10-29'),
(28, 'Pizza Gà Nướng 3 Vị', 'Gà nướng, gà bơ tỏi và gà ướp xốt nấm.\r\n                    ', 'img/6901745b886b7.jpg', 1, '2025-10-29'),
(29, 'Pizza 5 Loại Thịt Đặc Biệt', '\r\n          Xúc xích lợn, bò đặc trưng của Ý, giăm bông và thịt xông khói.          ', 'img/690174d312846.png', 2, '2025-10-29'),
(30, 'Pizza Gà Nướng Dứa  Pizza Gà Nướng Dứa', 'Thịt gà và dứa.\r\n                    ', 'img/690175223976a.jpg', 1, '2025-10-29'),
(31, 'Pizza Xúc Xích Ý  ', '\r\n             Xúc xích kiểu Ý trên nền xốt cà chua.       ', 'img/69017562e3cd4.jpg', 3, '2025-10-29'),
(32, 'Pizza Thịt Nguội & Nấm', '\r\n               Giăm bông và nấm.     ', 'img/690175a87be73.jpg', 3, '2025-10-29'),
(33, 'Khai Vị Tổng Hợp (Cánh Gà Tẩm Bột Chiên Giòn)', '\r\n            Cánh gà tẩm bột chiên giòn + Khoai tây chiên + Bánh mì bơ tỏi        ', 'img/690176b776432.png', 6, '2025-10-29'),
(34, 'Khai Vị Tổng Hợp (Cánh Gà Nướng BBQ)', 'Cánh gà nướng BBQ + Khoai tây chiên + Bánh mì bơ tỏi\r\n                    ', 'img/690176ed4d62a.png', 6, '2025-10-29'),
(35, 'Cánh Gà Nướng BBQ (6 miếng)', 'Cánh gà nướng thấm vị với lớp da mỏng giòn.\r\n                    ', 'img/6901772c7f15d.png', 6, '2025-10-29'),
(36, 'Mực Chiên Giòn', '                                                Mực tẩm bột chiên giòn dùng kèm xốt ngò tây.\r\n                    \r\n                    \r\n                    ', 'img/69017a82bac98.png', 6, '2025-10-29'),
(37, 'Bánh Phô Mai Xoắn', 'Phô mai trắng được nướng với bơ, tỏi và dùng kèm xốt Cocktail.\r\n                    ', 'img/690177b489f3f.png', 6, '2025-10-29'),
(38, 'Gà Nướng BBQ Vắt Chanh (2 miếng)', 'Thịt gà thấm đẫm gia vị, da gà màu vàng ươm bắt mắt.\r\n                    ', 'img/690860d5b0832.png', 6, '2025-11-03'),
(39, 'Salad Trộn Dầu Giấm', 'Rau với xốt dầu giấm.\r\n                    ', 'img/690861323bace.png', 8, '2025-11-03'),
(40, 'Salad Đặc Sắc', 'Salad rau và trái cây tươi dùng kèm xốt kem.\r\n                    ', 'img/690861602d6c1.png', 8, '2025-11-03'),
(41, 'Salad Gà Giòn Không Xương', 'Salad gà giòn với trứng cút, thịt xông khói, phô mai và xốt Thousand Island.\r\n                    ', 'img/690861a41bc19.png', 1, '2025-11-03');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sanpham_size`
--

CREATE TABLE `sanpham_size` (
  `id` int(11) NOT NULL,
  `MaSP` int(11) NOT NULL,
  `MaSize` int(11) NOT NULL,
  `Anh` varchar(255) DEFAULT NULL,
  `Gia` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `sanpham_size`
--

INSERT INTO `sanpham_size` (`id`, `MaSP`, `MaSize`, `Anh`, `Gia`) VALUES
(5, 5, 12, 'img/6901849201ab5.png', 24000.00),
(6, 6, 11, 'img/690184bb5d33a.png', 10000.00),
(7, 7, 11, 'img/690184e7af3ad.png', 12000.00),
(8, 7, 12, 'img/69018508cf111.png', 15000.00),
(9, 9, 11, 'img/690185f4e8dc9.png', 13000.00),
(10, 9, 12, 'img/6901861a744c0.png', 24000.00),
(11, 10, 11, 'img/6901866c0024e.png', 12000.00),
(12, 10, 12, 'img/690186d0c9c78.png', 30000.00),
(13, 12, 1, 'img/6901871464b32.png', 15000.00),
(14, 5, 12, 'img/6901874745d87.png', 32000.00),
(46, 13, 1, 'img/690171b515cd0.png', 99000.00),
(47, 13, 2, 'img/690171b515cd0.png', 119000.00),
(48, 13, 3, 'img/690171b515cd0.png', 139000.00),
(49, 14, 1, 'img/690171cb99f0e.png', 109000.00),
(50, 14, 2, 'img/690171cb99f0e.png', 129000.00),
(51, 14, 3, 'img/690171cb99f0e.png', 149000.00),
(52, 15, 1, 'img/690172223b65c.png', 99000.00),
(53, 15, 2, 'img/690172223b65c.png', 119000.00),
(54, 15, 3, 'img/690172223b65c.png', 139000.00),
(55, 16, 1, 'img/690172576572f.png', 109000.00),
(56, 16, 2, 'img/690172576572f.png', 129000.00),
(57, 16, 3, 'img/690172576572f.png', 149000.00),
(58, 17, 1, 'img/6901715e6de38.jpg', 99000.00),
(59, 17, 2, 'img/6901715e6de38.jpg', 119000.00),
(60, 17, 3, 'img/6901715e6de38.jpg', 139000.00),
(61, 18, 1, 'img/6901718a68df9.jpg', 89000.00),
(62, 18, 2, 'img/6901718a68df9.jpg', 109000.00),
(63, 18, 3, 'img/6901718a68df9.jpg', 129000.00),
(64, 23, 1, 'img/690170c2cdcd0.jpg', 89000.00),
(65, 23, 2, 'img/690170c2cdcd0.jpg', 109000.00),
(66, 23, 3, 'img/690170c2cdcd0.jpg', 129000.00),
(67, 24, 1, 'img/6901732e941bb.png', 109000.00),
(68, 24, 2, 'img/6901732e941bb.png', 129000.00),
(69, 24, 3, 'img/6901732e941bb.png', 149000.00),
(70, 25, 1, 'img/6901736ff0cf4.png', 99000.00),
(71, 25, 2, 'img/6901736ff0cf4.png', 119000.00),
(72, 25, 3, 'img/6901736ff0cf4.png', 139000.00),
(73, 26, 1, 'img/690173ca084ef.png', 109000.00),
(74, 26, 2, 'img/690173ca084ef.png', 129000.00),
(75, 26, 3, 'img/690173ca084ef.png', 149000.00),
(76, 27, 1, 'img/6901741044318.jpg', 89000.00),
(77, 27, 2, 'img/6901741044318.jpg', 109000.00),
(78, 27, 3, 'img/6901741044318.jpg', 129000.00),
(79, 28, 1, 'img/6901745b886b7.jpg', 99000.00),
(80, 28, 2, 'img/6901745b886b7.jpg', 119000.00),
(81, 28, 3, 'img/6901745b886b7.jpg', 139000.00),
(82, 29, 1, 'img/690174d312846.png', 109000.00),
(83, 29, 2, 'img/690174d312846.png', 129000.00),
(84, 29, 3, 'img/690174d312846.png', 149000.00),
(85, 30, 1, 'img/690175223976a.jpg', 89000.00),
(86, 30, 2, 'img/690175223976a.jpg', 109000.00),
(87, 30, 3, 'img/690175223976a.jpg', 129000.00),
(88, 31, 1, 'img/69017562e3cd4.jpg', 89000.00),
(89, 31, 2, 'img/69017562e3cd4.jpg', 109000.00),
(90, 31, 3, 'img/69017562e3cd4.jpg', 129000.00),
(91, 32, 1, 'img/690175a87be73.jpg', 99000.00),
(92, 32, 2, 'img/690175a87be73.jpg', 119000.00),
(93, 32, 3, 'img/690175a87be73.jpg', 139000.00),
(94, 33, 1, 'img/690176b776432.png', 79000.00),
(95, 33, 2, 'img/690176b776432.png', 99000.00),
(96, 33, 3, 'img/690176b776432.png', 119000.00),
(97, 34, 1, 'img/690176ed4d62a.png', 99000.00),
(98, 34, 2, 'img/690176ed4d62a.png', 119000.00),
(99, 34, 3, 'img/690176ed4d62a.png', 139000.00),
(100, 35, 1, 'img/6901772c7f15d.png', 109000.00),
(101, 35, 2, 'img/6901772c7f15d.png', 129000.00),
(102, 35, 3, 'img/6901772c7f15d.png', 149000.00),
(103, 36, 1, 'img/69017a82bac98.png', 89000.00),
(104, 36, 2, 'img/69017a82bac98.png', 109000.00),
(105, 36, 3, 'img/69017a82bac98.png', 129000.00),
(106, 37, 1, 'img/690177b489f3f.png', 69000.00),
(107, 37, 2, 'img/690177b489f3f.png', 89000.00),
(108, 37, 3, 'img/690177b489f3f.png', 109000.00);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `size`
--

CREATE TABLE `size` (
  `MaSize` int(11) NOT NULL,
  `TenSize` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `size`
--

INSERT INTO `size` (`MaSize`, `TenSize`) VALUES
(1, 'Small (Nhỏ 6 inch)'),
(2, 'Medium (Vừa 9 inch)'),
(3, 'Large (Lớn 12 inch)'),
(4, 'Extra Large (Siêu Lớn)'),
(5, 'Personal (Cá Nhân)'),
(6, 'Family (Gia Đình)'),
(7, 'Party (Tiệc Tùng)'),
(8, 'Combo 2 Người'),
(9, 'Combo 4 Người'),
(10, 'Combo 6 Người'),
(11, '330ml'),
(12, '1.2l');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `chitietdonhang`
--
ALTER TABLE `chitietdonhang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `MaDH` (`MaDH`),
  ADD KEY `MaSP` (`MaSP`);

--
-- Chỉ mục cho bảng `chitietgiohang`
--
ALTER TABLE `chitietgiohang`
  ADD KEY `CartID` (`CartID`),
  ADD KEY `MaSP` (`MaSP`);

--
-- Chỉ mục cho bảng `donhang`
--
ALTER TABLE `donhang`
  ADD PRIMARY KEY (`MaDH`),
  ADD KEY `MaKH` (`MaKH`);

--
-- Chỉ mục cho bảng `giohang`
--
ALTER TABLE `giohang`
  ADD PRIMARY KEY (`CartID`),
  ADD KEY `MaKH` (`MaKH`);

--
-- Chỉ mục cho bảng `khachhang`
--
ALTER TABLE `khachhang`
  ADD PRIMARY KEY (`MaKH`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Chỉ mục cho bảng `loaisanpham`
--
ALTER TABLE `loaisanpham`
  ADD PRIMARY KEY (`MaLoai`);

--
-- Chỉ mục cho bảng `sanpham`
--
ALTER TABLE `sanpham`
  ADD PRIMARY KEY (`MaSP`),
  ADD KEY `MaLoai` (`MaLoai`);

--
-- Chỉ mục cho bảng `sanpham_size`
--
ALTER TABLE `sanpham_size`
  ADD PRIMARY KEY (`id`),
  ADD KEY `MaSP` (`MaSP`),
  ADD KEY `MaSize` (`MaSize`);

--
-- Chỉ mục cho bảng `size`
--
ALTER TABLE `size`
  ADD PRIMARY KEY (`MaSize`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `chitietdonhang`
--
ALTER TABLE `chitietdonhang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT cho bảng `donhang`
--
ALTER TABLE `donhang`
  MODIFY `MaDH` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT cho bảng `giohang`
--
ALTER TABLE `giohang`
  MODIFY `CartID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT cho bảng `khachhang`
--
ALTER TABLE `khachhang`
  MODIFY `MaKH` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT cho bảng `loaisanpham`
--
ALTER TABLE `loaisanpham`
  MODIFY `MaLoai` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `sanpham`
--
ALTER TABLE `sanpham`
  MODIFY `MaSP` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT cho bảng `sanpham_size`
--
ALTER TABLE `sanpham_size`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- AUTO_INCREMENT cho bảng `size`
--
ALTER TABLE `size`
  MODIFY `MaSize` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `chitietdonhang`
--
ALTER TABLE `chitietdonhang`
  ADD CONSTRAINT `chitietdonhang_ibfk_1` FOREIGN KEY (`MaDH`) REFERENCES `donhang` (`MaDH`),
  ADD CONSTRAINT `chitietdonhang_ibfk_2` FOREIGN KEY (`MaSP`) REFERENCES `sanpham` (`MaSP`);

--
-- Các ràng buộc cho bảng `chitietgiohang`
--
ALTER TABLE `chitietgiohang`
  ADD CONSTRAINT `chitietgiohang_ibfk_1` FOREIGN KEY (`CartID`) REFERENCES `giohang` (`CartID`),
  ADD CONSTRAINT `chitietgiohang_ibfk_2` FOREIGN KEY (`MaSP`) REFERENCES `sanpham` (`MaSP`);

--
-- Các ràng buộc cho bảng `donhang`
--
ALTER TABLE `donhang`
  ADD CONSTRAINT `donhang_ibfk_1` FOREIGN KEY (`MaKH`) REFERENCES `khachhang` (`MaKH`);

--
-- Các ràng buộc cho bảng `giohang`
--
ALTER TABLE `giohang`
  ADD CONSTRAINT `giohang_ibfk_1` FOREIGN KEY (`MaKH`) REFERENCES `khachhang` (`MaKH`);

--
-- Các ràng buộc cho bảng `sanpham`
--
ALTER TABLE `sanpham`
  ADD CONSTRAINT `sanpham_ibfk_1` FOREIGN KEY (`MaLoai`) REFERENCES `loaisanpham` (`MaLoai`);

--
-- Các ràng buộc cho bảng `sanpham_size`
--
ALTER TABLE `sanpham_size`
  ADD CONSTRAINT `sanpham_size_ibfk_1` FOREIGN KEY (`MaSP`) REFERENCES `sanpham` (`MaSP`),
  ADD CONSTRAINT `sanpham_size_ibfk_2` FOREIGN KEY (`MaSize`) REFERENCES `size` (`MaSize`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
