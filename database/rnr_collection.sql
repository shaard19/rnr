-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 01, 2026 at 06:33 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rnr_collection`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `category_code` varchar(50) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `department_id` int(11) NOT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `category_name`, `category_code`, `description`, `department_id`, `status`) VALUES
(4, 'Braids', NULL, 'Abuja-Long', 3, 'Active'),
(5, 'Spoons', NULL, 'Silver', 4, 'Active'),
(6, 'Excecise Books', NULL, '120 Pages A4 Squared', 1, 'Active'),
(7, 'Men Trousers', NULL, 'Jeans', 2, 'Active'),
(8, 'Cups', NULL, 'Plastic Normal Kitchen Cups', 4, 'Active'),
(9, 'Enamel Plates', NULL, 'Enamel Plates', 4, 'Active'),
(10, 'Cooking Pot', NULL, 'Aluminium, Stainless Steel', 4, 'Active'),
(11, 'Pen', NULL, 'Blue Pen', 1, 'Active'),
(12, 'Lotion', NULL, 'nice and lovely lemon', 3, 'Active'),
(13, 'perfumes', NULL, 'Refill perfumes', 3, 'Active'),
(14, 'Perfume', NULL, 'Reffileable perfumes', 3, 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `customer_code` varchar(50) DEFAULT NULL,
  `customer_name` varchar(150) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `credit_balance` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `customer_code`, `customer_name`, `phone`, `email`, `address`, `status`, `credit_balance`) VALUES
(1, '01', 'Shadrack Kimingich Kitele', '0736063055', 'shaard92@outlook.com', '3666-30200, Pittsburgh', 'Active', 1070.00),
(2, '002', 'Sharon Wafula', '0711801136', 'sharonw@gmail.com', '2102-30200', 'Active', 0.00),
(3, '102', 'Lukas Wafula', '0710897689', 'lukas@gmail.com', '2102-30200, Kitale', 'Active', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `department_name` varchar(100) NOT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `department_name`, `status`) VALUES
(1, 'Student Stationery', 'Active'),
(2, 'Clothes', 'Active'),
(3, 'Beauty Products', 'Active'),
(4, 'Kitchen Utensils', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `permission_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `permission_name`) VALUES
(5, 'add_category'),
(11, 'add_customer'),
(2, 'add_product'),
(8, 'add_supplier'),
(7, 'delete_category'),
(13, 'delete_customer'),
(4, 'delete_product'),
(10, 'delete_supplier'),
(6, 'edit_category'),
(12, 'edit_customer'),
(3, 'edit_product'),
(9, 'edit_supplier'),
(14, 'make_sale'),
(21, 'make_sales'),
(23, 'manage_settings'),
(17, 'manage_users'),
(18, 'view_categories'),
(20, 'view_customer'),
(1, 'view_dashboard'),
(22, 'view_reports'),
(15, 'view_sales'),
(16, 'view_stock'),
(19, 'view_supplier');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `category_id` int(11) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `buying_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `selling_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `reorder_level` int(11) NOT NULL DEFAULT 0,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_code`, `product_name`, `category_id`, `supplier_id`, `unit`, `buying_price`, `selling_price`, `quantity`, `reorder_level`, `status`, `image`) VALUES
(1, 'BRA-00001', 'Abuja Braids', 4, 1, '1 Box', 75.00, 100.00, 7, 5, 'Inactive', NULL),
(2, 'SPO-00001', 'Table Spoons', 5, 2, '1 box', 125.00, 150.00, 18, 5, 'Active', NULL),
(3, 'EXC-00001', 'Excecise Books', 6, 3, '1 Box', 450.00, 680.00, 42, 5, 'Active', NULL),
(4, 'MEN-00001', 'Jeans Trousers', 7, 4, '1 Bale', 15000.00, 25000.00, 48, 5, 'Active', NULL),
(5, 'ENA-00001', 'Enamel Plate Black', 9, 4, '1 Pc', 150.00, 275.00, 3, 5, 'Active', NULL),
(6, 'CUP-00001', 'Enamel Cup', 8, 4, '1 pc', 150.00, 200.00, 0, 5, 'Active', NULL),
(8, 'PEN-00001', 'Blue Pen', 11, 3, '1 pc', 5.00, 10.00, 1, 5, 'Active', NULL),
(9, 'PEN-00002', 'Blue Pen Sharp Pointed', 11, 3, '1', 10.00, 20.00, 3, 5, 'Inactive', NULL),
(10, 'CUP-00002', 'kaulo heavy', 8, 2, '1', 80.00, 119.98, 15, 5, 'Active', NULL),
(11, 'BRA-00002', 'pink chiffon', 4, 1, '30', 290.00, 330.00, 0, 30, 'Active', NULL),
(12, 'PER-00001', 'pink chiffon', 13, 1, '3ml', 33.00, 100.00, 7, 5, 'Active', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL,
  `role` enum('Admin','Cashier') NOT NULL,
  `permission_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role`, `permission_id`) VALUES
(1, 'Admin', 1),
(2, 'Admin', 2),
(3, 'Admin', 3),
(4, 'Admin', 4),
(5, 'Admin', 5),
(6, 'Admin', 6),
(7, 'Admin', 7),
(8, 'Admin', 8),
(9, 'Admin', 9),
(10, 'Admin', 10),
(11, 'Admin', 11),
(12, 'Admin', 12),
(13, 'Admin', 13),
(14, 'Admin', 14),
(15, 'Admin', 15),
(16, 'Admin', 16),
(17, 'Admin', 17),
(18, 'Admin', 18),
(19, 'Admin', 19),
(20, 'Admin', 20),
(21, 'Admin', 21),
(22, 'Admin', 22),
(23, 'Cashier', 14),
(24, 'Cashier', 1),
(25, 'Cashier', 15),
(26, 'Cashier', 16),
(30, 'Admin', 23);

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `invoice_no` varchar(50) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `payment_method` enum('Cash','Lipa na M-Pesa') NOT NULL DEFAULT 'Cash',
  `payment_status` enum('Paid','Partial','Credit') NOT NULL DEFAULT 'Paid',
  `amount_paid` decimal(12,2) NOT NULL DEFAULT 0.00,
  `balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `sale_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES
(1, 'INV-20260730024142-199', NULL, 1, 100.00, 'Cash', 'Paid', 200.00, 0.00, '2026-07-30 03:41:42'),
(2, 'INV-20260730024239-358', NULL, 1, 680.00, 'Cash', 'Paid', 700.00, 0.00, '2026-07-30 03:42:39'),
(3, 'INV-20260730024657-502', NULL, 1, 100.00, 'Cash', 'Paid', 200.00, 0.00, '2026-07-30 03:46:57'),
(4, 'INV-20260730024715-991', NULL, 1, 680.00, 'Cash', 'Paid', 1000.00, 0.00, '2026-07-30 03:47:15'),
(5, 'INV-20260730082808-222', NULL, 2, 30.00, 'Cash', 'Paid', 50.00, 0.00, '2026-07-30 09:28:08'),
(6, 'INV-20260730082935-203', NULL, 3, 780.00, 'Cash', 'Paid', 800.00, 0.00, '2026-07-30 09:29:35'),
(7, 'INV-20260730150639-877', NULL, 1, 1360.00, 'Cash', 'Paid', 1500.00, 0.00, '2026-07-30 16:06:39'),
(8, 'INV-20260730150821-148', NULL, 1, 400.00, 'Cash', 'Paid', 500.00, 0.00, '2026-07-30 16:08:21'),
(9, 'INV-20260730150852-788', NULL, 1, 25000.00, 'Cash', 'Paid', 25000.00, 0.00, '2026-07-30 16:08:52'),
(10, 'INV-20260730151747-905', NULL, 1, 239.96, 'Cash', 'Paid', 500.00, 0.00, '2026-07-30 16:17:47'),
(11, 'INV-20260730152234-399', NULL, 1, 100.00, 'Cash', 'Paid', 200.00, 0.00, '2026-07-30 16:22:34'),
(12, 'INV-20260730152529-804', NULL, 3, 980.00, 'Cash', 'Paid', 1000.00, 0.00, '2026-07-30 16:25:29'),
(13, 'INV-20260730154744-171', NULL, 1, 150.00, 'Cash', 'Paid', 200.00, 0.00, '2026-07-30 16:47:44'),
(14, 'INV-20260730192104-143', NULL, 2, 10.00, 'Cash', 'Paid', 40.00, 0.00, '2026-07-30 20:21:04'),
(15, 'INV-20260730195402-DF4F0D', NULL, 2, 10.00, 'Cash', 'Credit', 0.00, 10.00, '2026-07-30 20:54:02'),
(16, 'INV-20260730195426-91BC39', NULL, 2, 119.98, 'Cash', 'Paid', 150.00, 0.00, '2026-07-30 20:54:26'),
(17, 'INV-20260730195545-6F457D', NULL, 2, 200.00, 'Cash', 'Paid', 500.00, 0.00, '2026-07-30 20:55:45'),
(18, 'INV-20260730195726-A2EB88', NULL, 2, 20.00, 'Cash', 'Paid', 20.00, 0.00, '2026-07-30 20:57:26'),
(19, 'INV-20260730200544-2409D3', NULL, 2, 10.00, 'Cash', 'Paid', 20.00, 0.00, '2026-07-30 21:05:44'),
(20, 'INV-20260730200707-351744', NULL, 2, 119.98, 'Cash', 'Paid', 120.00, 0.00, '2026-07-30 21:07:07'),
(21, 'INV-20260730201231-F3C7F6', NULL, 2, 10.00, 'Cash', 'Paid', 20.00, 0.00, '2026-07-30 21:12:31'),
(22, 'INV-20260730201754-63CF80', NULL, 2, 100.00, 'Cash', 'Paid', 100.00, 0.00, '2026-07-30 21:17:54'),
(23, 'INV-20260730201845-725B05', NULL, 2, 40.00, 'Cash', 'Paid', 50.00, 0.00, '2026-07-30 21:18:45'),
(24, 'INV-20260730202110-6DBA2D', NULL, 3, 10.00, 'Cash', 'Paid', 20.00, 0.00, '2026-07-30 21:21:10'),
(25, 'INV-20260730202201-84B798', 1, 3, 25000.00, 'Cash', 'Credit', 0.00, 25000.00, '2026-07-30 21:22:01'),
(26, 'INV-20260730203355-870182', NULL, 3, 150.00, 'Cash', 'Paid', 150.00, 0.00, '2026-07-30 21:33:55'),
(27, 'INV-20260730203912-DA04EF', NULL, 3, 119.98, 'Cash', 'Credit', 0.00, 119.98, '2026-07-30 21:39:12'),
(28, 'INV-20260730203958-5EF966', NULL, 3, 275.00, 'Lipa na M-Pesa', 'Paid', 300.00, 0.00, '2026-07-30 21:39:58'),
(29, 'INV-20260730205353-DA8A3B', 1, 3, 10.00, 'Cash', 'Credit', 0.00, 10.00, '2026-07-30 21:53:53'),
(30, 'INV-20260730205447-BABD1A', 1, 3, 160.00, 'Cash', 'Credit', 0.00, 160.00, '2026-07-30 21:54:47'),
(31, 'INV-20260730210042-FE36BF', 1, 3, 340.00, 'Cash', 'Credit', 0.00, 340.00, '2026-07-30 22:00:42'),
(32, 'INV-20260730210128-F46970', 1, 3, 340.00, 'Cash', 'Credit', 0.00, 340.00, '2026-07-30 22:01:28'),
(33, 'INV-20260730210259-2D6B7C', 1, 3, 160.00, 'Cash', 'Credit', 0.00, 160.00, '2026-07-30 22:02:59'),
(34, 'INV-20260730210648-409395', 1, 3, 160.00, 'Cash', 'Credit', 100.00, 60.00, '2026-07-30 22:06:48'),
(35, 'INV-20260731090231-AD708B', NULL, 1, 10.00, 'Cash', 'Paid', 20.00, 0.00, '2026-07-31 10:02:31'),
(36, 'INV-20260731090509-2F612E', NULL, 1, 330.00, 'Cash', 'Paid', 400.00, 0.00, '2026-07-31 10:05:09'),
(37, 'INV-20260731090530-567C68', 3, 1, 330.00, 'Cash', 'Paid', 400.00, 0.00, '2026-07-31 10:05:30'),
(38, 'INV-20260731103539-7F99C2', NULL, 1, 10.00, 'Cash', 'Paid', 10.00, 0.00, '2026-07-31 11:35:39'),
(39, 'INV-20260731132854-EE6A35', NULL, 3, 160.00, 'Cash', 'Paid', 250.00, 0.00, '2026-07-31 14:28:54'),
(40, 'INV-20260731163627-08C35A', NULL, 3, 10.00, 'Lipa na M-Pesa', 'Paid', 50.00, 0.00, '2026-07-31 17:36:27'),
(41, 'INV-20260731185725-2D4EA5', NULL, 3, 10.00, 'Cash', 'Paid', 20.00, 0.00, '2026-07-31 19:57:25'),
(42, 'INV-20260801084502-F1D4A5', 2, 1, 10.00, 'Cash', 'Paid', 20.00, 0.00, '2026-08-01 09:45:02');

-- --------------------------------------------------------

--
-- Table structure for table `sale_items`
--

CREATE TABLE `sale_items` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sale_items`
--

INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES
(1, 1, 1, 1, 100.00, 100.00),
(2, 2, 3, 1, 680.00, 680.00),
(3, 3, 1, 1, 100.00, 100.00),
(4, 4, 3, 1, 680.00, 680.00),
(5, 5, 9, 1, 20.00, 20.00),
(6, 5, 8, 1, 10.00, 10.00),
(7, 6, 1, 1, 100.00, 100.00),
(8, 6, 3, 1, 680.00, 680.00),
(9, 7, 3, 2, 680.00, 1360.00),
(10, 8, 6, 2, 200.00, 400.00),
(11, 9, 4, 1, 25000.00, 25000.00),
(12, 10, 10, 2, 119.98, 239.96),
(13, 11, 12, 1, 100.00, 100.00),
(14, 12, 1, 2, 100.00, 200.00),
(15, 12, 3, 1, 680.00, 680.00),
(16, 12, 12, 1, 100.00, 100.00),
(17, 13, 2, 1, 150.00, 150.00),
(18, 14, 8, 1, 10.00, 10.00),
(19, 15, 8, 1, 10.00, 10.00),
(20, 16, 10, 1, 119.98, 119.98),
(21, 17, 6, 1, 200.00, 200.00),
(22, 18, 8, 2, 10.00, 20.00),
(23, 19, 8, 1, 10.00, 10.00),
(24, 20, 10, 1, 119.98, 119.98),
(25, 21, 8, 1, 10.00, 10.00),
(26, 22, 12, 1, 100.00, 100.00),
(27, 23, 8, 4, 10.00, 40.00),
(28, 24, 8, 1, 10.00, 10.00),
(29, 25, 4, 1, 25000.00, 25000.00),
(30, 26, 2, 1, 150.00, 150.00),
(31, 27, 10, 1, 119.98, 119.98),
(32, 28, 5, 1, 275.00, 275.00),
(33, 29, 8, 1, 10.00, 10.00),
(34, 30, 8, 1, 10.00, 10.00),
(35, 30, 2, 1, 150.00, 150.00),
(36, 31, 8, 1, 10.00, 10.00),
(37, 31, 11, 1, 330.00, 330.00),
(38, 32, 8, 1, 10.00, 10.00),
(39, 32, 11, 1, 330.00, 330.00),
(40, 33, 8, 1, 10.00, 10.00),
(41, 33, 2, 1, 150.00, 150.00),
(42, 34, 8, 1, 10.00, 10.00),
(43, 34, 2, 1, 150.00, 150.00),
(44, 35, 8, 1, 10.00, 10.00),
(45, 36, 11, 1, 330.00, 330.00),
(46, 37, 11, 1, 330.00, 330.00),
(47, 38, 8, 1, 10.00, 10.00),
(48, 39, 8, 1, 10.00, 10.00),
(49, 39, 2, 1, 150.00, 150.00),
(50, 40, 8, 1, 10.00, 10.00),
(51, 41, 8, 1, 10.00, 10.00),
(52, 42, 8, 1, 10.00, 10.00);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_name` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_movements`
--

CREATE TABLE `stock_movements` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `movement_type` enum('IN','OUT') NOT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `movement_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_movements`
--

INSERT INTO `stock_movements` (`id`, `product_id`, `quantity`, `movement_type`, `reference`, `user_id`, `movement_date`) VALUES
(1, 8, 1, 'OUT', 'INV-20260730195402-DF4F0D', 2, '2026-07-30 20:54:02'),
(2, 10, 1, 'OUT', 'INV-20260730195426-91BC39', 2, '2026-07-30 20:54:26'),
(3, 6, 1, 'OUT', 'INV-20260730195545-6F457D', 2, '2026-07-30 20:55:45'),
(4, 8, 2, 'OUT', 'INV-20260730195726-A2EB88', 2, '2026-07-30 20:57:26'),
(5, 8, 1, 'OUT', 'INV-20260730200544-2409D3', 2, '2026-07-30 21:05:44'),
(6, 10, 1, 'OUT', 'INV-20260730200707-351744', 2, '2026-07-30 21:07:07'),
(7, 8, 1, 'OUT', 'INV-20260730201231-F3C7F6', 2, '2026-07-30 21:12:31'),
(8, 12, 1, 'OUT', 'INV-20260730201754-63CF80', 2, '2026-07-30 21:17:54'),
(9, 8, 4, 'OUT', 'INV-20260730201845-725B05', 2, '2026-07-30 21:18:45'),
(10, 8, 1, 'OUT', 'INV-20260730202110-6DBA2D', 3, '2026-07-30 21:21:10'),
(11, 4, 1, 'OUT', 'INV-20260730202201-84B798', 3, '2026-07-30 21:22:01'),
(12, 2, 1, 'OUT', 'INV-20260730203355-870182', 3, '2026-07-30 21:33:55'),
(13, 10, 1, 'OUT', 'INV-20260730203912-DA04EF', 3, '2026-07-30 21:39:12'),
(14, 5, 1, 'OUT', 'INV-20260730203958-5EF966', 3, '2026-07-30 21:39:58'),
(15, 8, 1, 'OUT', 'INV-20260730205353-DA8A3B', 3, '2026-07-30 21:53:53'),
(16, 8, 1, 'OUT', 'INV-20260730205447-BABD1A', 3, '2026-07-30 21:54:47'),
(17, 2, 1, 'OUT', 'INV-20260730205447-BABD1A', 3, '2026-07-30 21:54:47'),
(18, 8, 1, 'OUT', 'INV-20260730210042-FE36BF', 3, '2026-07-30 22:00:42'),
(19, 11, 1, 'OUT', 'INV-20260730210042-FE36BF', 3, '2026-07-30 22:00:42'),
(20, 8, 1, 'OUT', 'INV-20260730210128-F46970', 3, '2026-07-30 22:01:28'),
(21, 11, 1, 'OUT', 'INV-20260730210128-F46970', 3, '2026-07-30 22:01:28'),
(22, 8, 1, 'OUT', 'INV-20260730210259-2D6B7C', 3, '2026-07-30 22:02:59'),
(23, 2, 1, 'OUT', 'INV-20260730210259-2D6B7C', 3, '2026-07-30 22:02:59'),
(24, 8, 1, 'OUT', 'INV-20260730210648-409395', 3, '2026-07-30 22:06:48'),
(25, 2, 1, 'OUT', 'INV-20260730210648-409395', 3, '2026-07-30 22:06:48'),
(26, 8, 1, 'OUT', 'INV-20260731090231-AD708B', 1, '2026-07-31 10:02:31'),
(27, 11, 1, 'OUT', 'INV-20260731090509-2F612E', 1, '2026-07-31 10:05:09'),
(28, 11, 1, 'OUT', 'INV-20260731090530-567C68', 1, '2026-07-31 10:05:30'),
(29, 8, 1, 'OUT', 'INV-20260731103539-7F99C2', 1, '2026-07-31 11:35:39'),
(30, 8, 1, 'OUT', 'INV-20260731132854-EE6A35', 3, '2026-07-31 14:28:54'),
(31, 2, 1, 'OUT', 'INV-20260731132854-EE6A35', 3, '2026-07-31 14:28:54'),
(32, 8, 1, 'OUT', 'INV-20260731163627-08C35A', 3, '2026-07-31 17:36:27'),
(33, 8, 1, 'OUT', 'INV-20260731185725-2D4EA5', 3, '2026-07-31 19:57:25'),
(34, 8, 1, 'OUT', 'INV-20260801084502-F1D4A5', 1, '2026-08-01 09:45:02');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `supplier_name` varchar(150) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `supplier_name`, `phone`, `email`, `address`, `status`, `created_at`) VALUES
(1, 'Best Lady Cosmetics', '0710336014', 'bestlady@yahoo.com', '3666-30200, Kitale', 'Active', '2026-07-30 00:02:05'),
(2, 'Jalaram Drappers Limited', '0124154211', 'jdrappers@gmail.com', '320, Kitale', 'Active', '2026-07-30 02:31:43'),
(3, 'Lisa Paper Works', '0710336014', 'lisa@gmail.com', '253, Kitale', 'Active', '2026-07-30 02:33:36'),
(4, 'Tuskys Supermarket', '0124512511', 'tuskys@gmail.com', '121, Eldoret', 'Active', '2026-07-30 02:37:38');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Cashier') NOT NULL DEFAULT 'Cashier',
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `username`, `password`, `role`, `status`, `last_login`) VALUES
(1, 'Administrator', 'admin', '$2y$10$ifbb8vTVrqE/6v5Skp9A7Obp0SpesAUOE/woDNr1GfM4rFMX1OACu', 'Admin', 'Active', '2026-08-01 09:18:30'),
(2, 'Shadrack Kimingich Kitele', 'root', '$2y$10$pBMRdHu5LZu3F3PhghSNReyFU8taP.HVjtjFfS8rhOwJ7uM1C7TwG', 'Admin', 'Active', '2026-07-31 19:58:20'),
(3, 'Maureen Simiyu Kimingich', 'maureen', '$2y$10$S6S81AaFH2Fr9yQ1iUKMQ.0cehHUlj.p7oHhePEUfiMoKQuXo9uIG', 'Cashier', 'Active', '2026-07-31 19:56:50'),
(4, 'Claiton Andaye', 'Andaye', '$2y$10$WAcRZD6cbzXNY5BdvaKrzO9okCOLRqTXdhnViy2nBrZ4Fv2mKGBy2', 'Admin', 'Active', '2026-07-30 16:54:07');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customer_code` (`customer_code`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permission_name` (`permission_name`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_code` (`product_code`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_no` (`invoice_no`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_name` (`setting_name`);

--
-- Indexes for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `fk_categories_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_products_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `fk_role_permissions_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `fk_sales_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sales_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD CONSTRAINT `fk_sale_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sale_items_sale` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD CONSTRAINT `fk_stock_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_stock_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
