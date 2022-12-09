-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Máy chủ: localhost:3306
-- Thời gian đã tạo: Th12 09, 2022 lúc 03:02 AM
-- Phiên bản máy phục vụ: 5.7.33
-- Phiên bản PHP: 8.1.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `thoitrang`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `table_order`
--

CREATE TABLE `table_order` (
  `id` int(11) UNSIGNED NOT NULL,
  `id_user` int(11) DEFAULT '0',
  `code` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_status` int(11) DEFAULT '0',
  `order_payment` int(11) DEFAULT '0',
  `total_price` double DEFAULT '0',
  `notes` mediumtext COLLATE utf8mb4_unicode_ci,
  `date_created` int(11) DEFAULT '0',
  `date_updated` int(11) DEFAULT '0',
  `numb` int(11) DEFAULT '0',
  `order_detail` json NOT NULL,
  `order_info` json NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `table_order_group`
--

CREATE TABLE `table_order_group` (
  `id` int(11) UNSIGNED NOT NULL,
  `id_order` int(11) DEFAULT '0',
  `id_shop` int(11) DEFAULT '0',
  `id_member` int(11) DEFAULT '0',
  `total_price` double DEFAULT '0',
  `notes` mediumtext COLLATE utf8mb4_unicode_ci,
  `sector_prefix` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_status` int(11) DEFAULT '0',
  `date_created` int(11) DEFAULT '0',
  `date_updated` int(11) DEFAULT '0',
  `numb` int(11) DEFAULT '0',
  `order_group_detail` json NOT NULL,
  `order_group_info` json NOT NULL,
  `order_payment` int(11) NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `table_order`
--
ALTER TABLE `table_order`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `table_order_group`
--
ALTER TABLE `table_order_group`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `table_order`
--
ALTER TABLE `table_order`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `table_order_group`
--
ALTER TABLE `table_order_group`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
