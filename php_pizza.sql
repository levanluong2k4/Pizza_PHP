-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th12 02, 2025 lúc 06:20 AM
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
  `phanquyen` int(1) DEFAULT NULL,
  `token` int(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `admin`
--

INSERT INTO `admin` (`id`, `ten`, `password`, `email`, `phanquyen`, `token`) VALUES
(3, 'luong', '$2y$10$3J8yJFKPTH8pHa1jIYfIvOCuYb21mI/hCXQ7XbgHBt2iL6cjUHx.6', 'luong.lv.64cntt@ntu.edu.vn', 0, 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `banan`
--

CREATE TABLE `banan` (
  `MaBan` int(11) NOT NULL,
  `SoBan` int(11) NOT NULL,
  `SoGhe` int(11) NOT NULL,
  `KhuVuc` varchar(50) DEFAULT NULL,
  `GhiChu` text DEFAULT NULL,
  `TrangThai` enum('dang_hoat_dong','bao_tri') DEFAULT 'dang_hoat_dong'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `banan`
--

INSERT INTO `banan` (`MaBan`, `SoBan`, `SoGhe`, `KhuVuc`, `GhiChu`, `TrangThai`) VALUES
(1, 1, 4, 'Tầng 1', NULL, 'dang_hoat_dong'),
(2, 2, 4, 'Tầng 1', NULL, 'dang_hoat_dong'),
(3, 3, 6, 'Tầng 1', NULL, 'dang_hoat_dong'),
(4, 4, 2, 'Tầng 1', NULL, 'dang_hoat_dong'),
(5, 5, 4, 'Tầng 1', NULL, 'dang_hoat_dong'),
(6, 6, 6, 'Tầng 2', NULL, 'dang_hoat_dong'),
(7, 7, 8, 'Tầng 2', NULL, 'dang_hoat_dong'),
(8, 8, 4, 'Tầng 2', NULL, 'dang_hoat_dong'),
(9, 9, 6, 'Tầng 2', NULL, 'dang_hoat_dong'),
(10, 10, 4, 'Tầng 2', NULL, 'dang_hoat_dong');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chitietcombo`
--

CREATE TABLE `chitietcombo` (
  `id` int(11) NOT NULL,
  `MaCombo` int(11) NOT NULL,
  `MaSP` int(11) NOT NULL,
  `MaSize` int(11) DEFAULT NULL,
  `SoLuong` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `chitietcombo`
--

INSERT INTO `chitietcombo` (`id`, `MaCombo`, `MaSP`, `MaSize`, `SoLuong`) VALUES
(16, 2, 16, 3, 1),
(17, 2, 37, 3, 1),
(18, 2, 14, 3, 1),
(19, 2, 7, 12, 1),
(20, 2, 9, 11, 1),
(21, 2, 23, 2, 1),
(22, 2, 34, 3, 1),
(23, 1, 32, 2, 1),
(24, 1, 36, 1, 1),
(25, 1, 33, 3, 1),
(26, 1, 6, 11, 1),
(28, 3, 14, 2, 1),
(29, 3, 17, 2, 1),
(30, 3, 34, 3, 1),
(31, 3, 32, 2, 1),
(32, 3, 9, 12, 1),
(33, 3, 12, 11, 4),
(34, 4, 27, 2, 1),
(35, 4, 27, 3, 1),
(36, 4, 35, 2, 1),
(37, 4, 6, 11, 4),
(38, 4, 16, 1, 2),
(40, 10, 32, 1, 1),
(41, 10, 39, 2, 1),
(42, 10, 10, 11, 9),
(43, 10, 29, 3, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chitietdatban`
--

CREATE TABLE `chitietdatban` (
  `MaChiTiet` int(11) NOT NULL,
  `MaDatBan` int(11) NOT NULL,
  `MaSP` int(11) NOT NULL,
  `MaSize` int(11) NOT NULL,
  `SoLuong` int(11) NOT NULL,
  `ThanhTien` decimal(20,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `chitietdatban`
--

INSERT INTO `chitietdatban` (`MaChiTiet`, `MaDatBan`, `MaSP`, `MaSize`, `SoLuong`, `ThanhTien`) VALUES
(11, 26, 32, 2, 1, 119000.00),
(12, 26, 36, 1, 1, 89000.00),
(13, 26, 33, 3, 1, 119000.00),
(14, 26, 6, 11, 1, 10000.00),
(15, 26, 5, 11, 1, 32000.00),
(16, 27, 32, 2, 1, 119000.00),
(17, 27, 36, 1, 1, 89000.00),
(18, 27, 33, 3, 1, 119000.00),
(19, 27, 6, 11, 1, 10000.00),
(20, 27, 5, 11, 1, 32000.00),
(21, 28, 32, 2, 1, 119000.00),
(22, 28, 36, 1, 1, 89000.00),
(23, 28, 33, 3, 1, 119000.00),
(24, 28, 6, 11, 1, 10000.00),
(25, 28, 5, 11, 1, 32000.00),
(26, 29, 32, 2, 1, 119000.00),
(27, 29, 36, 1, 1, 89000.00),
(28, 29, 33, 3, 1, 119000.00),
(29, 29, 6, 11, 1, 10000.00),
(30, 29, 5, 11, 1, 32000.00),
(31, 30, 32, 2, 1, 119000.00),
(32, 30, 36, 1, 1, 89000.00),
(33, 30, 33, 3, 1, 119000.00),
(34, 30, 6, 11, 1, 10000.00),
(35, 30, 5, 11, 1, 32000.00),
(36, 31, 32, 2, 1, 119000.00),
(37, 31, 36, 1, 1, 89000.00),
(38, 31, 33, 3, 1, 119000.00),
(39, 31, 6, 11, 1, 10000.00),
(40, 31, 5, 11, 1, 32000.00),
(41, 32, 16, 3, 1, 149000.00),
(42, 32, 37, 3, 1, 109000.00),
(43, 32, 14, 3, 1, 149000.00),
(44, 32, 7, 12, 1, 15000.00),
(45, 32, 9, 11, 1, 13000.00),
(46, 32, 23, 2, 1, 109000.00),
(47, 32, 34, 3, 1, 139000.00),
(48, 33, 16, 3, 1, 149000.00),
(49, 33, 37, 3, 1, 109000.00),
(50, 33, 14, 3, 1, 149000.00),
(51, 33, 7, 12, 1, 15000.00),
(52, 33, 9, 11, 1, 13000.00),
(53, 33, 23, 2, 1, 109000.00),
(54, 33, 34, 3, 1, 139000.00),
(55, 34, 16, 3, 1, 149000.00),
(56, 34, 37, 3, 1, 109000.00),
(57, 34, 14, 3, 1, 149000.00),
(58, 34, 7, 12, 1, 15000.00),
(59, 34, 9, 11, 1, 13000.00),
(60, 34, 23, 2, 1, 109000.00),
(61, 34, 34, 3, 1, 139000.00),
(62, 35, 16, 3, 1, 149000.00),
(63, 35, 37, 3, 1, 109000.00),
(64, 35, 14, 3, 1, 149000.00),
(65, 35, 7, 12, 1, 15000.00),
(66, 35, 9, 11, 1, 13000.00),
(67, 35, 23, 2, 1, 109000.00),
(68, 35, 34, 3, 1, 139000.00),
(69, 36, 14, 2, 1, 129000.00),
(70, 36, 17, 2, 1, 119000.00),
(71, 36, 34, 3, 1, 139000.00),
(72, 36, 32, 2, 1, 119000.00),
(73, 36, 9, 12, 1, 24000.00),
(74, 36, 12, 11, 4, 60000.00),
(80, 41, 16, 3, 1, 149000.00),
(81, 41, 37, 3, 1, 109000.00),
(82, 41, 14, 3, 1, 149000.00),
(83, 41, 7, 12, 1, 15000.00),
(84, 41, 9, 11, 1, 13000.00),
(85, 41, 23, 2, 1, 109000.00),
(86, 41, 33, 3, 1, 119000.00),
(87, 42, 16, 3, 1, 149000.00),
(88, 42, 37, 3, 1, 109000.00),
(89, 42, 14, 3, 1, 149000.00),
(90, 42, 7, 12, 1, 15000.00),
(91, 42, 9, 11, 1, 13000.00),
(92, 42, 23, 2, 1, 109000.00),
(93, 42, 34, 3, 1, 139000.00),
(129, 37, 32, 2, 2, 238000.00),
(130, 37, 36, 1, 1, 89000.00),
(131, 37, 33, 3, 1, 119000.00),
(132, 37, 6, 11, 1, 10000.00),
(133, 37, 5, 11, 1, 32000.00),
(135, 40, 5, 12, 3, 72000.00),
(136, 45, 16, 3, 1, 149000.00),
(137, 45, 37, 3, 1, 109000.00),
(138, 45, 14, 3, 1, 149000.00),
(139, 45, 7, 12, 1, 15000.00),
(140, 45, 9, 11, 1, 13000.00),
(141, 45, 23, 2, 1, 109000.00),
(142, 45, 34, 3, 1, 139000.00),
(145, 48, 5, 12, 1, 25000.00);

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
  `ThanhTien` decimal(10,2) NOT NULL,
  `MaCombo` int(11) DEFAULT NULL,
  `tenbe` varchar(20) DEFAULT NULL,
  `ngaysinhbe` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `chitietdonhang`
--

INSERT INTO `chitietdonhang` (`id`, `MaDH`, `MaSP`, `MaSize`, `SoLuong`, `ThanhTien`, `MaCombo`, `tenbe`, `ngaysinhbe`) VALUES
(65, 49, 14, 3, 1, 149000.00, NULL, NULL, NULL),
(66, 50, 14, 3, 2, 298000.00, NULL, NULL, NULL),
(67, 51, 14, 1, 1, 109000.00, NULL, NULL, NULL),
(68, 52, 14, 3, 1, 149000.00, NULL, NULL, NULL),
(69, 53, 14, 3, 1, 149000.00, NULL, NULL, NULL),
(70, 55, 13, 3, 1, 139000.00, NULL, NULL, NULL),
(71, 56, 14, 3, 1, 149000.00, NULL, NULL, NULL),
(72, 57, 15, 3, 1, 139000.00, NULL, NULL, NULL),
(73, 59, 26, 3, 1, 149000.00, NULL, NULL, NULL),
(74, 60, 15, 3, 1, 139000.00, NULL, NULL, NULL),
(75, 61, 15, 1, 1, 99000.00, NULL, NULL, NULL),
(76, 62, 26, 1, 1, 109000.00, NULL, NULL, NULL),
(77, 63, 14, 1, 1, 109000.00, NULL, NULL, NULL),
(78, 64, 14, 1, 1, 109000.00, NULL, NULL, NULL),
(79, 65, 17, 1, 1, 99000.00, NULL, NULL, NULL),
(80, 66, 14, 3, 2, 298000.00, NULL, NULL, NULL),
(81, 67, 26, 1, 1, 109000.00, NULL, NULL, NULL),
(82, 68, 26, 1, 1, 109000.00, NULL, NULL, NULL),
(83, 70, 26, 1, 1, 109000.00, NULL, NULL, NULL),
(84, 71, 17, 1, 1, 99000.00, NULL, NULL, NULL),
(85, 72, 26, 1, 1, 109000.00, NULL, NULL, NULL),
(86, 73, 32, 1, 1, 99000.00, NULL, NULL, NULL),
(87, 74, 32, 1, 1, 99000.00, NULL, NULL, NULL),
(88, 75, 26, 1, 1, 109000.00, NULL, NULL, NULL),
(89, 78, 32, 1, 1, 99000.00, NULL, NULL, NULL),
(90, 79, 26, 1, 1, 109000.00, NULL, NULL, NULL),
(91, 80, 14, 1, 1, 109000.00, NULL, NULL, NULL),
(92, 81, 14, 1, 1, 109000.00, NULL, NULL, NULL),
(93, 83, 14, 1, 1, 109000.00, NULL, NULL, NULL),
(94, 84, 14, 1, 1, 109000.00, NULL, NULL, NULL),
(95, 86, 17, 1, 1, 99000.00, NULL, NULL, NULL),
(96, 88, 14, 3, 5, 745000.00, NULL, NULL, NULL),
(97, 88, 15, 3, 1, 139000.00, NULL, NULL, NULL),
(98, 88, 17, 1, 1, 99000.00, NULL, NULL, NULL),
(101, 89, 26, 1, 3, 218000.00, NULL, NULL, NULL),
(102, 89, 17, 1, 2, 198000.00, NULL, NULL, NULL),
(103, 89, 9, 11, 1, 26000.00, NULL, NULL, NULL);

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
(65, 26, 1, 503),
(67, 17, 1, 1),
(110, 14, 1, 8),
(110, 9, 11, 2),
(110, 6, 11, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `combo`
--

CREATE TABLE `combo` (
  `MaCombo` int(11) NOT NULL,
  `Tencombo` varchar(20) NOT NULL,
  `Anh` varchar(50) NOT NULL,
  `giamgia` int(3) NOT NULL,
  `Tongtien` decimal(20,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `combo`
--

INSERT INTO `combo` (`MaCombo`, `Tencombo`, `Anh`, `giamgia`, `Tongtien`) VALUES
(1, 'Combo 5 người', 'img/Birthday_WebBanner.jpg', 1, 333630.00),
(2, 'Combo 10 người', 'img/Birthday_WebBanner.jpg', 2, 669340.00),
(3, 'Combo tiệc tùng', '/img/Birthday_WebBanner.jpg', 5, 517750.00),
(4, 'Combo tiệc sản khoái', 'img/Birthday_WebBanner.jpg', 0, 625000.00),
(10, 'Combo tiệc sinh nhật', 'combo_1764473037.jpg', 7, 390600.00);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `datban`
--

CREATE TABLE `datban` (
  `MaDatBan` int(11) NOT NULL,
  `MaKH` int(11) DEFAULT NULL,
  `HoTen` varchar(100) NOT NULL,
  `SDT` varchar(20) NOT NULL,
  `NgayGio` datetime NOT NULL,
  `LoaiDatBan` enum('thuong','tiec') NOT NULL,
  `MaBan` int(11) DEFAULT NULL,
  `MaPhong` int(11) DEFAULT NULL,
  `MaCombo` int(11) DEFAULT NULL,
  `GhiChu` text DEFAULT NULL,
  `TrangThaiDatBan` enum('da_dat','da_xac_nhan','dang_su_dung','thanh_cong','da_huy') DEFAULT 'da_dat',
  `NgayTao` datetime DEFAULT current_timestamp(),
  `TrangThaiThanhToan` enum('chuathanhtoan','dathanhtoan') NOT NULL DEFAULT 'chuathanhtoan',
  `is_guest` tinyint(1) NOT NULL,
  `Tongtien` decimal(20,2) NOT NULL
) ;

--
-- Đang đổ dữ liệu cho bảng `datban`
--

INSERT INTO `datban` (`MaDatBan`, `MaKH`, `HoTen`, `SDT`, `NgayGio`, `LoaiDatBan`, `MaBan`, `MaPhong`, `MaCombo`, `GhiChu`, `TrangThaiDatBan`, `NgayTao`, `TrangThaiThanhToan`, `is_guest`, `Tongtien`) VALUES
(1, NULL, 'Luongle12', '0124545400', '2025-11-17 11:30:00', 'thuong', 1, NULL, NULL, ' [Tự động hủy: Không thanh toán sau 5 phút]', 'thanh_cong', '2025-11-16 19:50:04', 'chuathanhtoan', 0, 0.00),
(2, NULL, 'Luongle123', '0124545400', '2025-11-20 18:00:00', 'thuong', 2, NULL, NULL, '', 'da_xac_nhan', '2025-11-16 20:52:29', 'chuathanhtoan', 0, 0.00),
(3, NULL, 'Luongle12', '0124545400', '2025-11-22 17:00:00', 'tiec', NULL, 5, 2, '', 'dang_su_dung', '2025-11-16 20:58:09', 'dathanhtoan', 0, 669340.00),
(4, NULL, 'Luongle123', '0124545400', '2025-11-22 12:00:00', 'tiec', NULL, 2, 2, '', 'thanh_cong', '2025-11-16 21:03:07', 'chuathanhtoan', 0, 669340.00),
(6, NULL, 'Luongle12', '0124545400', '2025-11-20 12:00:00', 'tiec', NULL, 1, 2, '', 'da_huy', '2025-11-16 22:22:30', '', 0, 669340.00),
(7, NULL, 'Luongle123', '0124545400', '2025-11-21 17:30:00', 'tiec', NULL, 1, 2, 'hghghg', 'thanh_cong', '2025-11-20 14:40:39', 'dathanhtoan', 0, 669340.00),
(8, NULL, 'Luongle12', '0124545400', '2025-11-21 16:00:00', 'tiec', NULL, 1, 2, 'fgff', '', '2025-11-20 14:48:04', 'chuathanhtoan', 0, 669340.00),
(10, NULL, 'Luongle12', '0124545400', '2025-11-22 16:00:00', 'tiec', NULL, 1, 1, '', '', '2025-11-20 23:16:27', 'chuathanhtoan', 0, 365310.00),
(21, NULL, 'Luongle12', '0124545400', '2025-11-24 15:30:00', 'tiec', NULL, 5, 1, ' [Tự động hủy: Không thanh toán sau 5 phút]', 'da_huy', '2025-11-21 11:16:52', 'chuathanhtoan', 0, 365310.00),
(22, NULL, 'Luongle12', '0124545400', '2025-12-02 11:00:00', 'tiec', NULL, 1, 1, ' [Tự động hủy: Không thanh toán sau 5 phút]', 'da_huy', '2025-11-21 12:36:22', 'chuathanhtoan', 0, 365310.00),
(23, NULL, 'Luongle12', '0124545400', '2025-11-22 11:00:00', 'tiec', NULL, 4, 1, ' [Tự động hủy: Không thanh toán sau 5 phút]', 'da_huy', '2025-11-21 15:11:52', 'chuathanhtoan', 0, 365310.00),
(26, NULL, 'Luongle12', '0124545400', '2025-11-22 16:30:00', 'tiec', NULL, 3, 1, ' [Tự động hủy: Không thanh toán sau 5 phút]', 'da_huy', '2025-11-21 15:54:51', 'chuathanhtoan', 0, 365310.00),
(27, NULL, 'Luongle12', '0124545400', '2025-11-23 17:00:00', 'tiec', NULL, 2, 1, ' [Tự động hủy: Không thanh toán sau 5 phút]', 'da_huy', '2025-11-22 07:04:10', 'chuathanhtoan', 0, 0.00),
(28, 11, 'Luongle12', '0124545400', '2025-12-07 16:30:00', 'tiec', NULL, 2, 1, ' [Tự động hủy: Không thanh toán sau 5 phút]', 'da_huy', '2025-11-22 07:07:28', 'chuathanhtoan', 0, 0.00),
(29, 11, 'Luongle12', '0124545400', '2025-11-30 11:00:00', 'tiec', NULL, 2, 1, ' [Tự động hủy: Không thanh toán sau 5 phút]', 'da_huy', '2025-11-22 14:07:50', 'chuathanhtoan', 0, 0.00),
(30, 11, 'Luongle12', '0124545400', '2025-11-30 18:00:00', 'tiec', NULL, 3, 1, ' [Tự động hủy: Không thanh toán sau 5 phút]', 'da_huy', '2025-11-22 14:27:13', 'chuathanhtoan', 0, 365310.00),
(31, 11, 'Luongle12', '0124545400', '2025-11-29 17:30:00', 'tiec', NULL, 3, 1, ' [Tự động hủy: Không thanh toán sau 5 phút]', 'da_huy', '2025-11-22 15:27:41', 'chuathanhtoan', 0, 365310.00),
(32, 11, 't565', '0124545400', '2025-11-29 18:30:00', 'tiec', NULL, 4, 2, ' [Tự động hủy: Không thanh toán sau 5 phút]', 'da_huy', '2025-11-22 15:32:09', 'chuathanhtoan', 0, 669340.00),
(33, 11, 'Luongle12', '0124545400', '2025-11-29 11:00:00', 'tiec', NULL, 5, 2, ' [Tự động hủy: Không thanh toán sau 5 phút]', 'da_huy', '2025-11-22 15:35:12', 'chuathanhtoan', 0, 669340.00),
(34, 11, 'Luongle12', '0124545400', '2025-11-30 16:00:00', 'tiec', NULL, 5, 2, ' [Tự động hủy: Không thanh toán sau 5 phút]', 'da_huy', '2025-11-22 15:37:15', 'chuathanhtoan', 0, 669340.00),
(35, 11, 'Luongle12', '0124545400', '2025-11-30 17:00:00', 'tiec', NULL, 2, 2, ' [Tự động hủy: Không thanh toán sau 5 phút]', 'da_huy', '2025-11-22 15:47:13', 'chuathanhtoan', 0, 669340.00),
(36, 11, 'Luongle12', '0124545400', '2025-12-20 11:00:00', 'tiec', NULL, 1, 3, ' [Tự động hủy: Không thanh toán sau 5 phút]', 'da_huy', '2025-11-22 15:53:10', 'chuathanhtoan', 0, 517750.00),
(37, 11, 'Luongle12', '0124545400', '2025-11-29 11:30:00', 'tiec', NULL, 4, 1, '', 'da_huy', '2025-11-22 20:48:17', 'dathanhtoan', 0, 483120.00),
(38, NULL, 'Luongle12', '0124545400', '2025-11-28 15:30:00', 'thuong', 1, NULL, NULL, ' [Tự động hủy: Không thanh toán sau 5 phút]', 'da_huy', '2025-11-22 21:02:31', 'chuathanhtoan', 0, 0.00),
(39, NULL, 't565', '0124545400', '2025-11-25 16:00:00', 'thuong', 3, NULL, NULL, ' [Tự động hủy: Không thanh toán sau 5 phút]', 'da_huy', '2025-11-22 21:08:45', 'chuathanhtoan', 0, 0.00),
(40, NULL, 'Luongle12', '0124545400', '2025-11-27 17:00:00', 'thuong', 1, NULL, NULL, 'ko', 'da_huy', '2025-11-22 22:29:54', 'chuathanhtoan', 0, 72000.00),
(41, NULL, 'Luongle12', '0124545400', '2025-11-29 18:00:00', 'tiec', NULL, 1, 2, ' [Tự động hủy: Không thanh toán sau 5 phút]', 'da_huy', '2025-11-23 21:14:29', 'chuathanhtoan', 0, 649740.00),
(42, NULL, 'Luongle12', '0124545400', '2025-11-28 11:30:00', 'tiec', NULL, 4, 2, ' [Tự động hủy: Không thanh toán sau 5 phút]', 'thanh_cong', '2025-11-26 20:34:39', 'dathanhtoan', 0, 669340.00),
(43, NULL, 'Luongle12', '0799462980', '2025-12-02 16:30:00', 'thuong', 1, NULL, NULL, 'Tiệc sinh nhật Lê Văn Lương 17/4/2004', 'thanh_cong', '2025-11-29 08:55:32', 'chuathanhtoan', 0, 0.00),
(45, NULL, 'Luongle12', '0124545400', '2025-12-04 17:00:00', 'tiec', NULL, 5, 2, ' [Tự động hủy: Không thanh toán sau 5 phút]', 'da_huy', '2025-11-30 17:15:30', 'chuathanhtoan', 0, 669340.00),
(46, NULL, 'Luongle12', '0124545400', '2025-12-06 11:30:00', 'thuong', 2, NULL, NULL, '', 'da_dat', '2025-11-30 21:12:10', 'chuathanhtoan', 0, 0.00),
(47, NULL, 'Luongle12', '0124545400', '2025-12-05 12:00:00', 'thuong', 1, NULL, NULL, '', 'da_dat', '2025-12-02 09:33:58', 'chuathanhtoan', 0, 0.00),
(48, NULL, 'trang', '0124545400', '2025-12-04 16:30:00', 'thuong', 1, NULL, NULL, '', 'thanh_cong', '2025-12-02 09:42:09', 'dathanhtoan', 0, 25000.00);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `donhang`
--

CREATE TABLE `donhang` (
  `MaDH` int(11) NOT NULL,
  `MaDHcode` varchar(50) DEFAULT NULL,
  `MaKH` int(11) DEFAULT NULL,
  `ngaydat` datetime DEFAULT current_timestamp(),
  `TongTien` decimal(10,2) NOT NULL,
  `trangthai` varchar(50) DEFAULT 'Chờ xử lý',
  `diachinguoinhan` text DEFAULT NULL,
  `sdtnguoinhan` varchar(20) DEFAULT NULL,
  `Tennguoinhan` varchar(20) DEFAULT NULL,
  `ghichu` text DEFAULT NULL,
  `is_guest` tinyint(1) NOT NULL,
  `phuongthucthanhtoan` varchar(20) DEFAULT NULL,
  `trangthaithanhtoan` enum('chuathanhtoan','dathanhtoan') NOT NULL DEFAULT 'chuathanhtoan'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `donhang`
--

INSERT INTO `donhang` (`MaDH`, `MaDHcode`, `MaKH`, `ngaydat`, `TongTien`, `trangthai`, `diachinguoinhan`, `sdtnguoinhan`, `Tennguoinhan`, `ghichu`, `is_guest`, `phuongthucthanhtoan`, `trangthaithanhtoan`) VALUES
(48, '1763090560', NULL, '2025-11-14 10:22:40', 149000.00, 'Chờ giao', '62/11 đặng tất,Xã Thống Nhất,Huyện Lạc Thủy,Tỉnh Hoà Bình', '0112245454', 'Luongle12', NULL, 1, 'Chuyển khoản', ''),
(49, '1763090824', NULL, '2025-11-14 10:27:04', 149000.00, 'Giao thành công', '62/11 đặng tất,Xã Thống Nhất,Huyện Lạc Thủy,Tỉnh Hoà Bình', '0112245454', 'Luongle12', NULL, 1, 'Chuyển khoản', ''),
(50, '1763091914', 24, '2025-11-14 10:45:14', 298000.00, 'Giao thành công', '62/11 đặng tất,Phường Hoà Hải,Quận Ngũ Hành Sơn,Thành phố Đà Nẵng', '0112245454', 'Luongle12', NULL, 0, 'Chuyển khoản', ''),
(51, '1763131586', NULL, '2025-11-14 21:46:26', 109000.00, 'Chờ giao', '62/11 đặng tất,Xã Đồng Thắng,Huyện Đình Lập,Tỉnh Lạng Sơn', '0112245454', 'hjh', NULL, 1, 'Chuyển khoản', ''),
(52, '1763135077', NULL, '2025-11-14 22:44:37', 149000.00, 'Chờ giao', '62/11 đặng tất,Xã Đồng Thắng,Huyện Đình Lập,Tỉnh Lạng Sơn', '0112245454', 'hjh', NULL, 1, 'Chuyển khoản', ''),
(53, '1763439726', 24, '2025-11-18 11:22:06', 149000.00, 'Chờ giao', '62/11 đặng tất,Phường Hoà Hải,Quận Ngũ Hành Sơn,Thành phố Đà Nẵng', '0112245454', 'Luongle12', NULL, 0, 'Chuyển khoản', ''),
(54, '1763439948', 24, '2025-11-18 11:25:48', 0.00, 'Hủy đơn', '62/11 đặng tất,Phường Hoà Hải,Quận Ngũ Hành Sơn,Thành phố Đà Nẵng', '0112245454', 'Luongle12', NULL, 0, '', 'chuathanhtoan'),
(55, '1763440468', 24, '2025-11-18 11:34:28', 139000.00, 'Chờ giao', '62/11 đặng tất,Phường Hoà Hải,Quận Ngũ Hành Sơn,Thành phố Đà Nẵng', '0112245454', 'Luongle12', NULL, 0, 'Tiền mặt', 'dathanhtoan'),
(56, '1763440482', 24, '2025-11-18 11:34:42', 149000.00, 'Hủy đơn', '62/11 đặng tất,Phường Hoà Hải,Quận Ngũ Hành Sơn,Thành phố Đà Nẵng', '0112245454', 'Luongle12', NULL, 0, 'Chuyển khoản', 'chuathanhtoan'),
(57, '1763448344', 24, '2025-11-18 13:45:44', 139000.00, 'Hủy đơn', '62/11 đặng tất,Phường Hoà Hải,Quận Ngũ Hành Sơn,Thành phố Đà Nẵng', '0112245454', 'Luongle12', NULL, 0, 'Chuyển khoản', 'chuathanhtoan'),
(58, '1763473614', 24, '2025-11-18 20:46:54', 0.00, 'Chờ giao', '62/11 đặng tất,Phường Hoà Hải,Quận Ngũ Hành Sơn,Thành phố Đà Nẵng', '0112245454', 'Luongle12', NULL, 0, 'Tiền mặt', 'chuathanhtoan'),
(59, '1763474577', 24, '2025-11-18 21:02:57', 149000.00, 'Chờ giao', '62/11 đặng tất,Phường Hoà Hải,Quận Ngũ Hành Sơn,Thành phố Đà Nẵng', '0112245454', 'Luongle12', NULL, 0, 'Tiền mặt', 'chuathanhtoan'),
(60, '1763474608', 24, '2025-11-18 21:03:28', 139000.00, 'Chờ giao', '62/11 đặng tất,Phường Hoà Hải,Quận Ngũ Hành Sơn,Thành phố Đà Nẵng', '0112245454', 'Luongle12', NULL, 0, 'Tiền mặt', 'chuathanhtoan'),
(61, '1763478903', 24, '2025-11-18 22:15:03', 99000.00, 'Chờ giao', '62/11 đặng tất,Phường Hoà Hải,Quận Ngũ Hành Sơn,Thành phố Đà Nẵng', '0112245454', 'Luongle12', NULL, 0, 'Tiền mặt', 'chuathanhtoan'),
(62, '1763479388', 24, '2025-11-18 22:23:08', 109000.00, 'Chờ giao', '62/11 đặng tất,Phường Hoà Hải,Quận Ngũ Hành Sơn,Thành phố Đà Nẵng', '0112245454', 'Luongle12', NULL, 0, 'Chuyển khoản', 'chuathanhtoan'),
(63, '1763480434', 24, '2025-11-18 22:40:34', 109000.00, 'Chờ giao', '62/11 đặng tất,Phường Hoà Hải,Quận Ngũ Hành Sơn,Thành phố Đà Nẵng', '0112245454', 'Luongle12', NULL, 0, 'Chuyển khoản', 'chuathanhtoan'),
(64, '1763480489', 24, '2025-11-18 22:41:29', 109000.00, 'Chờ giao', '62/11 đặng tất,Phường Hoà Hải,Quận Ngũ Hành Sơn,Thành phố Đà Nẵng', '0112245454', 'Luongle12', NULL, 0, 'Chuyển khoản', 'chuathanhtoan'),
(65, '1763482385', 24, '2025-11-18 23:13:05', 99000.00, 'Chờ giao', '62/11 đặng tất,Phường Hoà Hải,Quận Ngũ Hành Sơn,Thành phố Đà Nẵng', '0112245454', 'Luongle12', NULL, 0, 'Tiền mặt', 'chuathanhtoan'),
(66, '1763519128', NULL, '2025-11-19 09:25:28', 298000.00, 'Chờ giao', '62/11 đặng tất,Xã Yên Phú,Huyện Lạc Sơn,Tỉnh Hoà Bình', '0112245454', 'Luongle12', NULL, 1, 'Chuyển khoản', 'dathanhtoan'),
(67, '1763520118', NULL, '2025-11-19 09:41:58', 109000.00, 'Chờ giao', '62/11 đặng tất,Xã Yên Phú,Huyện Lạc Sơn,Tỉnh Hoà Bình', '0112245454', 'Luongle12', NULL, 1, 'Chuyển khoản', 'dathanhtoan'),
(68, '1763520769', 24, '2025-11-19 09:52:49', 109000.00, 'Chờ giao', '62/11 đặng tất,Phường Hoà Hải,Quận Ngũ Hành Sơn,Thành phố Đà Nẵng', '0112245454', 'Luongle12', NULL, 0, 'Chuyển khoản', 'chuathanhtoan'),
(69, '1763520784', 24, '2025-11-19 09:53:04', 0.00, 'Chờ giao', '62/11 đặng tất,Phường Hoà Hải,Quận Ngũ Hành Sơn,Thành phố Đà Nẵng', '0112245454', 'Luongle12', NULL, 0, 'Chuyển khoản', 'dathanhtoan'),
(70, '1763520981', 24, '2025-11-19 09:56:21', 109000.00, 'Chờ giao', '62/11 đặng tất,Phường Hoà Hải,Quận Ngũ Hành Sơn,Thành phố Đà Nẵng', '0112245454', 'Luongle12', NULL, 0, 'Chuyển khoản', 'dathanhtoan'),
(71, '1763521261', 24, '2025-11-19 10:01:01', 99000.00, 'Chờ giao', '62/11 đặng tất,Phường Hoà Hải,Quận Ngũ Hành Sơn,Thành phố Đà Nẵng', '0112245454', 'Luongle12', NULL, 0, 'Tiền mặt', 'chuathanhtoan'),
(72, '1763521295', 24, '2025-11-19 10:01:35', 109000.00, 'Chờ giao', '62/11 đặng tất,Phường Hoà Hải,Quận Ngũ Hành Sơn,Thành phố Đà Nẵng', '0112245454', 'Luongle12', NULL, 0, 'Chuyển khoản', 'chuathanhtoan'),
(73, '1763521323', 24, '2025-11-19 10:02:03', 99000.00, 'Chờ giao', '62/11 đặng tất,Phường Hoà Hải,Quận Ngũ Hành Sơn,Thành phố Đà Nẵng', '0112245454', 'Luongle12', NULL, 0, 'Chuyển khoản', 'chuathanhtoan'),
(74, '1763521413', 24, '2025-11-19 10:03:33', 99000.00, 'Chờ giao', '62/11 đặng tất,Phường Hoà Hải,Quận Ngũ Hành Sơn,Thành phố Đà Nẵng', '0112245454', 'Luongle12', NULL, 0, 'Tiền mặt', 'chuathanhtoan'),
(75, '1763521530', 24, '2025-11-19 10:05:30', 109000.00, 'Chờ giao', '62/11 đặng tất,Phường Hoà Hải,Quận Ngũ Hành Sơn,Thành phố Đà Nẵng', '0112245454', 'Luongle12', NULL, 0, 'Chuyển khoản', 'chuathanhtoan'),
(76, '1763521555', 24, '2025-11-19 10:05:55', 0.00, 'Hủy đơn', '62/11 đặng tất,Phường Hoà Hải,Quận Ngũ Hành Sơn,Thành phố Đà Nẵng', '0112245454', 'Luongle12', NULL, 0, 'Tiền mặt', 'chuathanhtoan'),
(77, '1763521576', 24, '2025-11-19 10:06:16', 0.00, 'Hủy đơn', '62/11 đặng tất,Phường Hoà Hải,Quận Ngũ Hành Sơn,Thành phố Đà Nẵng', '0112245454', 'Luongle12', NULL, 0, 'Chuyển khoản', 'chuathanhtoan'),
(78, '1763521602', 24, '2025-11-19 10:06:42', 99000.00, 'Chờ giao', '62/11 đặng tất,Phường Hoà Hải,Quận Ngũ Hành Sơn,Thành phố Đà Nẵng', '0112245454', 'Luongle12', NULL, 0, 'Tiền mặt', 'chuathanhtoan'),
(79, '1763521690', 24, '2025-11-19 10:08:10', 109000.00, 'Chờ giao', '62/11 đặng tất,Phường Hoà Hải,Quận Ngũ Hành Sơn,Thành phố Đà Nẵng', '0112245454', 'Luongle12', NULL, 0, 'Tiền mặt', 'chuathanhtoan'),
(80, '1763521880', NULL, '2025-11-19 10:11:20', 109000.00, 'Chờ giao', 'gggggggggggggg,Xã Xuân Cẩm,Huyện Hiệp Hòa,Tỉnh Bắc Giang', '0112245454', 'Luongle12', NULL, 1, 'Chuyển khoản', 'chuathanhtoan'),
(81, '1763522007', 24, '2025-11-19 10:13:27', 109000.00, 'Chờ giao', '62/11 đặng tất,Phường Hoà Hải,Quận Ngũ Hành Sơn,Thành phố Đà Nẵng', '0112245454', 'Luongle12', NULL, 0, 'Tiền mặt', 'chuathanhtoan'),
(82, '1763522036', 24, '2025-11-19 10:13:56', 0.00, 'Hủy đơn', '62/11 đặng tất,Phường Hoà Hải,Quận Ngũ Hành Sơn,Thành phố Đà Nẵng', '0112245454', 'Luongle12', NULL, 0, 'Tiền mặt', 'chuathanhtoan'),
(83, '1763522093', NULL, '2025-11-19 10:14:53', 109000.00, 'Chờ giao', '62/11 đặng tất,Xã Tú Lý,Huyện Đà Bắc,Tỉnh Hoà Bình', '0112245454', 'Luongle123', NULL, 1, 'Chuyển khoản', 'chuathanhtoan'),
(84, '1763522318', 24, '2025-11-19 10:18:38', 109000.00, 'Chờ giao', '62/11 đặng tất,Phường Hoà Hải,Quận Ngũ Hành Sơn,Thành phố Đà Nẵng', '0112245454', 'Luongle12', NULL, 0, 'Chuyển khoản', 'chuathanhtoan'),
(85, '1763522428', 24, '2025-11-19 10:20:28', 0.00, 'Hủy đơn', '62/11 đặng tất,Phường Hoà Hải,Quận Ngũ Hành Sơn,Thành phố Đà Nẵng', '0112245454', 'Luongle12', NULL, 0, 'Chuyển khoản', 'chuathanhtoan'),
(86, '1763522456', 24, '2025-11-19 10:20:56', 99000.00, 'Đang giao', '62/11 đặng tất,Phường Hoà Hải,Quận Ngũ Hành Sơn,Thành phố Đà Nẵng', '0112245454', 'Luongle12', NULL, 0, 'Chuyển khoản', 'chuathanhtoan'),
(87, '1763523389', 24, '2025-11-19 10:36:29', 0.00, 'Giao thành công', '62/11 đặng tất,Phường Hoà Hải,Quận Ngũ Hành Sơn,Thành phố Đà Nẵng', '0112245454', 'Luongle12', NULL, 0, 'Tiền mặt', 'chuathanhtoan'),
(88, '1763644458', 11, '2025-11-20 20:14:18', 983000.00, 'Giao thành công', '62/11 đặng tất,Xã Hùng Việt,Huyện Cẩm Khê,Tỉnh Phú Thọ', '0000000000', 'li', NULL, 0, 'Chuyển khoản', 'dathanhtoan'),
(89, '1764512205', 11, '2025-11-30 21:16:45', 538000.00, 'Giao thành công', '62/11 đặng tất,Xã Thanh Định,Huyện Định Hóa,Tỉnh Thái Nguyên', '0000000000', 'li', NULL, 0, 'Tiền mặt', 'chuathanhtoan');

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
(65, 24, '2025-11-19 06:27:41'),
(67, 25, '2025-11-24 02:56:47'),
(110, 11, '2025-11-30 14:18:22');

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
  `tinh_code` varchar(10) DEFAULT NULL,
  `huyenquan` varchar(50) DEFAULT NULL,
  `huyen_code` varchar(10) DEFAULT NULL,
  `xaphuong` varchar(50) DEFAULT NULL,
  `sonha` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `khachhang`
--

INSERT INTO `khachhang` (`MaKH`, `HoTen`, `Email`, `SoDT`, `MatKhau`, `ngaytao`, `token`, `tinhthanhpho`, `tinh_code`, `huyenquan`, `huyen_code`, `xaphuong`, `sonha`) VALUES
(11, 'li', 'levanluong18t@gmail.com', '0000000000', '$2y$10$pZGrkYI4T48Ay4xamN5EOuUIVuFDhxLaG8uKaqmgRTQgVWhoF3anC', '2025-10-25 16:11:15', 0, 'Tỉnh Thái Nguyên', '19', 'Huyện Định Hóa', '167', 'Xã Thanh Định', '62/11 đặng tất'),
(12, '[value-2]', 'levanluong1t@gmail.com', '[value-4]', '123', '2025-10-25 16:16:25', 0, '[value-9]', NULL, '[value-10]', NULL, '[value-11]', '[value-12]'),
(16, '[value-2]', 'levanluongt@gmail.com', '[value-4]', '1', '2025-10-25 16:32:25', 0, '[value-9]', NULL, '[value-10]', NULL, '[value-11]', '[value-12]'),
(17, '[value-2]', 'levanluong@gmail.com', '[value-4]', '123', '2025-10-30 05:17:14', 0, '[value-9]', NULL, '[value-10]', NULL, '[value-11]', '[value-12]'),
(24, 'Luongle10', 'luong.lv.64cntt@ntu.edu.vn', '0112245454', '$2y$10$HvGw9I3l4cmimC871Jxc8u1lnCY8xfsruSkKWnkHi.BNQmf4tzq.G', '2025-11-07 16:09:43', 0, 'Tỉnh Hà Giang', '2', 'Thành phố Hà Giang', '24', 'Phường Quang Trung', '62/11 đặng tất77'),
(25, 'LÊ VĂN LƯƠNG', 'tranthuhuon55ntu@gmail.com', '0122359572', '$2y$10$djmStGMnJB3geES2w50DZuBGcAdtJFcQdMSg1oLfj/ZIEBe/FKq7y', '2025-11-24 02:37:08', 0, 'Tỉnh Quảng Ninh', '22', 'Thành phố Đông Triều', '205', 'Phường Hoàng Quế', '62/11 đặng tất');

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
(10, 'Đồ Uống');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phongtiec`
--

CREATE TABLE `phongtiec` (
  `MaPhong` int(11) NOT NULL,
  `TenPhong` varchar(50) NOT NULL,
  `SoPhong` int(11) NOT NULL,
  `SucChua` int(11) NOT NULL,
  `GhiChu` text DEFAULT NULL,
  `TrangThai` enum('trong','da_dat','dang_su_dung','bao_tri') DEFAULT 'trong'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `phongtiec`
--

INSERT INTO `phongtiec` (`MaPhong`, `TenPhong`, `SoPhong`, `SucChua`, `GhiChu`, `TrangThai`) VALUES
(1, 'Phòng 1', 1, 20, NULL, 'trong'),
(2, 'Phòng 2', 2, 30, NULL, 'da_dat'),
(3, 'Phòng 3', 3, 25, NULL, 'da_dat'),
(4, 'Phòng 4', 4, 15, NULL, 'trong'),
(5, 'Phòng 5', 5, 40, NULL, 'da_dat');

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
(11, 'Pepsi Lon', '                        Pepsi hương vị truyền thống, lon tiện lợi\r\n                    ', 'img/691dd7ecf3f08.png', 10, '2025-10-25'),
(12, 'Mirinda Soda Kem Lon', 'Nước giải khát Mirinda vị soda kem, lon tiện lợi', '/img/mirinda_soda_kem.png', 10, '2025-10-25'),
(13, 'Pizza Hải Sản Pesto Xanh', '                        Hải sản tươi ngon với sốt pesto xanh đặc biệt, phủ phô mai hảo hạng\r\n                    ', 'img/690171b515cd0.png', 5, '2025-10-25'),
(14, 'Pizza Hải Sản Pesto Xanh Full Topping', '                        Phiên bản đặc biệt với topping hải sản phủ đầy, sốt pesto thơm ngon\r\n                    ', 'img/690171cb99f0e.png', 5, '2025-10-25'),
(15, 'Pizza 4 Cheese Dừa Non', '                        Sự kết hợp độc đáo giữa 4 loại phô mai và dừa non tươi mát, mật hoa dừa\r\n                    ', 'img/690172223b65c.png', 5, '2025-10-25'),
(16, 'Pizza 4 Cheese Dừa Non Tôm Nõn', '                        Pizza 4 cheese với thêm tôm nõn tươi và dừa non, mật hoa dừa thơm ngon\r\n                    ', 'img/690172576572f.png', 1, '2025-10-25'),
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
(41, 'Salad Gà Giòn Không Xương', 'Salad gà giòn với trứng cút, thịt xông khói, phô mai và xốt Thousand Island.\r\n                    ', 'img/690861a41bc19.png', 8, '2025-11-03'),
(42, 'Mỳ Ý Nghêu Xốt Cay', 'Mỳ ý xốt cay nồng và vị ngọt thanh của nghêu.\r\n                    ', 'img/691c7ee285d23.png', 7, '2025-11-18'),
(43, 'Mỳ Ý Nghêu Xốt Húng Quế', 'Mỳ ý xốt húng quế và vị ngọt thanh của nghêu.\r\n                    ', 'img/691c7f2429f47.png', 7, '2025-11-18'),
(45, 'Mỳ Ý Pesto', 'Mỳ ý, tôm, mực hoà quyện trên nền xốt Pesto Xanh.\r\n                    ', 'img/691c7f6312980.png', 7, '2025-11-18'),
(46, 'Mỳ Ý Dừa Non & Thịt Xông Khói Áp Chảo', '                        Mỳ ý xốt cay cùng dừa non và thịt xông khói áp chảo.\r\n                    \r\n                    ', 'img/691c7fd27ec22.png', 7, '2025-11-18'),
(48, 'vảighgh', 'fgfg', 'img/692d8b6f2d759.png', 8, '2025-12-01'),
(49, 'vảighgh', 'fgfg', 'img/692d8bab318e6.png', 8, '2025-12-01');

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
(5, 5, 12, 'img/6901849201ab5.png', 25000.00),
(6, 6, 11, 'img/690184bb5d33a.png', 10000.00),
(7, 7, 11, 'img/690184e7af3ad.png', 12000.00),
(8, 7, 12, 'img/69018508cf111.png', 15000.00),
(9, 9, 11, 'img/690185f4e8dc9.png', 13000.00),
(10, 9, 12, 'img/6901861a744c0.png', 24000.00),
(11, 10, 11, 'img/6901866c0024e.png', 12000.00),
(12, 10, 12, 'img/690186d0c9c78.png', 30000.00),
(13, 12, 11, 'img/6901871464b32.png', 15000.00),
(14, 5, 11, 'img/692e6517469ad.png', 32000.00),
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
(108, 37, 3, 'img/690177b489f3f.png', 109000.00),
(109, 6, 12, 'img/690184bb5d33a.png', 10000.00),
(110, 42, 1, 'img/691c80f2b2501.png', 56000.00),
(111, 39, 2, 'img/691c813a645a2.png', 64000.00),
(112, 39, 3, 'img/691c816723dc3.png', 89999.00),
(113, 40, 3, 'img/691c830f91750.png', 98000.00),
(114, 43, 1, 'img/691c8359d1e5b.png', 75000.00),
(115, 43, 2, 'img/691c84bf5fe7d.png', 76987.00),
(116, 42, 3, 'img/691c84f5e66aa.png', 89000.00),
(117, 45, 1, 'img/691c851868edf.png', 65999.00),
(118, 45, 2, 'img/691c852a59817.png', 99999.00),
(119, 46, 1, 'img/691c853ec0ca6.png', 89000.00);

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
(8, 'Combo 2 Người'),
(9, 'Combo  4 Người'),
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
-- Chỉ mục cho bảng `banan`
--
ALTER TABLE `banan`
  ADD PRIMARY KEY (`MaBan`),
  ADD UNIQUE KEY `SoBan` (`SoBan`);

--
-- Chỉ mục cho bảng `chitietcombo`
--
ALTER TABLE `chitietcombo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `MaCombo` (`MaCombo`),
  ADD KEY `MaSize` (`MaSize`),
  ADD KEY `MaSP` (`MaSP`);

--
-- Chỉ mục cho bảng `chitietdatban`
--
ALTER TABLE `chitietdatban`
  ADD PRIMARY KEY (`MaChiTiet`),
  ADD KEY `MaSP` (`MaSP`),
  ADD KEY `MaSize` (`MaSize`),
  ADD KEY `fk_chitietdatban_datban` (`MaDatBan`);

--
-- Chỉ mục cho bảng `chitietdonhang`
--
ALTER TABLE `chitietdonhang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `MaDH` (`MaDH`),
  ADD KEY `MaSP` (`MaSP`),
  ADD KEY `MaCombo` (`MaCombo`);

--
-- Chỉ mục cho bảng `chitietgiohang`
--
ALTER TABLE `chitietgiohang`
  ADD KEY `CartID` (`CartID`),
  ADD KEY `MaSP` (`MaSP`);

--
-- Chỉ mục cho bảng `combo`
--
ALTER TABLE `combo`
  ADD PRIMARY KEY (`MaCombo`);

--
-- Chỉ mục cho bảng `datban`
--
ALTER TABLE `datban`
  ADD PRIMARY KEY (`MaDatBan`),
  ADD KEY `MaBan` (`MaBan`),
  ADD KEY `MaPhong` (`MaPhong`),
  ADD KEY `MaCombo` (`MaCombo`),
  ADD KEY `MaKH` (`MaKH`);

--
-- Chỉ mục cho bảng `donhang`
--
ALTER TABLE `donhang`
  ADD PRIMARY KEY (`MaDH`),
  ADD UNIQUE KEY `MaDHcode` (`MaDHcode`),
  ADD KEY `MaKH` (`MaKH`),
  ADD KEY `MaKH_2` (`MaKH`);

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
-- Chỉ mục cho bảng `phongtiec`
--
ALTER TABLE `phongtiec`
  ADD PRIMARY KEY (`MaPhong`),
  ADD UNIQUE KEY `SoPhong` (`SoPhong`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `banan`
--
ALTER TABLE `banan`
  MODIFY `MaBan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `chitietcombo`
--
ALTER TABLE `chitietcombo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT cho bảng `chitietdatban`
--
ALTER TABLE `chitietdatban`
  MODIFY `MaChiTiet` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=146;

--
-- AUTO_INCREMENT cho bảng `chitietdonhang`
--
ALTER TABLE `chitietdonhang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT cho bảng `combo`
--
ALTER TABLE `combo`
  MODIFY `MaCombo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `datban`
--
ALTER TABLE `datban`
  MODIFY `MaDatBan` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `donhang`
--
ALTER TABLE `donhang`
  MODIFY `MaDH` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT cho bảng `giohang`
--
ALTER TABLE `giohang`
  MODIFY `CartID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT cho bảng `khachhang`
--
ALTER TABLE `khachhang`
  MODIFY `MaKH` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT cho bảng `loaisanpham`
--
ALTER TABLE `loaisanpham`
  MODIFY `MaLoai` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT cho bảng `phongtiec`
--
ALTER TABLE `phongtiec`
  MODIFY `MaPhong` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `sanpham`
--
ALTER TABLE `sanpham`
  MODIFY `MaSP` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT cho bảng `sanpham_size`
--
ALTER TABLE `sanpham_size`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=125;

--
-- AUTO_INCREMENT cho bảng `size`
--
ALTER TABLE `size`
  MODIFY `MaSize` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `chitietcombo`
--
ALTER TABLE `chitietcombo`
  ADD CONSTRAINT `chitietcombo_ibfk_1` FOREIGN KEY (`MaCombo`) REFERENCES `combo` (`MaCombo`),
  ADD CONSTRAINT `chitietcombo_ibfk_2` FOREIGN KEY (`MaSize`) REFERENCES `size` (`MaSize`),
  ADD CONSTRAINT `chitietcombo_ibfk_3` FOREIGN KEY (`MaSP`) REFERENCES `sanpham` (`MaSP`);

--
-- Các ràng buộc cho bảng `chitietdatban`
--
ALTER TABLE `chitietdatban`
  ADD CONSTRAINT `chitietdatban_ibfk_2` FOREIGN KEY (`MaSP`) REFERENCES `sanpham` (`MaSP`),
  ADD CONSTRAINT `chitietdatban_ibfk_3` FOREIGN KEY (`MaSize`) REFERENCES `size` (`MaSize`),
  ADD CONSTRAINT `fk_chitietdatban_datban` FOREIGN KEY (`MaDatBan`) REFERENCES `datban` (`MaDatBan`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `chitietdonhang`
--
ALTER TABLE `chitietdonhang`
  ADD CONSTRAINT `chitietdonhang_ibfk_1` FOREIGN KEY (`MaDH`) REFERENCES `donhang` (`MaDH`),
  ADD CONSTRAINT `chitietdonhang_ibfk_2` FOREIGN KEY (`MaSP`) REFERENCES `sanpham` (`MaSP`),
  ADD CONSTRAINT `chitietdonhang_ibfk_3` FOREIGN KEY (`MaCombo`) REFERENCES `chitietcombo` (`MaCombo`);

--
-- Các ràng buộc cho bảng `chitietgiohang`
--
ALTER TABLE `chitietgiohang`
  ADD CONSTRAINT `chitietgiohang_ibfk_1` FOREIGN KEY (`CartID`) REFERENCES `giohang` (`CartID`),
  ADD CONSTRAINT `chitietgiohang_ibfk_2` FOREIGN KEY (`MaSP`) REFERENCES `sanpham` (`MaSP`);

--
-- Các ràng buộc cho bảng `datban`
--
ALTER TABLE `datban`
  ADD CONSTRAINT `datban_ibfk_1` FOREIGN KEY (`MaBan`) REFERENCES `banan` (`MaBan`),
  ADD CONSTRAINT `datban_ibfk_2` FOREIGN KEY (`MaPhong`) REFERENCES `phongtiec` (`MaPhong`),
  ADD CONSTRAINT `datban_ibfk_3` FOREIGN KEY (`MaCombo`) REFERENCES `combo` (`MaCombo`),
  ADD CONSTRAINT `datban_ibfk_4` FOREIGN KEY (`MaKH`) REFERENCES `khachhang` (`MaKH`);

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

DELIMITER $$
--
-- Sự kiện
--
CREATE DEFINER=`root`@`localhost` EVENT `auto_cancel_unpaid_bookings` ON SCHEDULE EVERY 1 MINUTE STARTS '2025-11-22 21:48:26' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
    UPDATE datban 
    SET TrangThaiDatBan = 'da_huy',
        GhiChu = CONCAT(IFNULL(GhiChu, ''), ' [Tự động hủy: Không thanh toán sau 5 phút]')
    WHERE TrangThaiThanhToan = 'chuathanhtoan'
      AND TrangThaiDatBan = 'da_dat'
      AND LoaiDatBan = 'tiec'
      AND TIMESTAMPDIFF(SECOND, NgayTao, NOW()) >= 300;
END$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
