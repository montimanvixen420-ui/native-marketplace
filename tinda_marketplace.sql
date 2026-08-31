SET SESSION sql_require_primary_key = 0;
-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 31, 2026 at 03:26 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.5.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tinda_marketplace`
--

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` int(10) UNSIGNED NOT NULL,
  `seller_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `branch_code` varchar(50) DEFAULT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `hours` varchar(150) DEFAULT NULL,
  `operating_hours` varchar(150) DEFAULT NULL,
  `latitude` decimal(10,7) NOT NULL,
  `longitude` decimal(10,7) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `status` enum('active','inactive','archived') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `seller_id`, `name`, `branch_code`, `address`, `city`, `phone`, `email`, `hours`, `operating_hours`, `latitude`, `longitude`, `is_active`, `status`, `created_at`, `updated_at`) VALUES
(2, 2, 'aswerty', NULL, 'anabu', 'imus city', '+63 917 123 3211', NULL, 'Mon-Sat, 9AM-6PM', NULL, 14.3655958, 120.9869385, 1, 'active', '2026-08-21 07:30:24', '2026-08-21 07:30:24'),
(3, 2, 'dota2', NULL, 'malagasang', 'imus city', '+63 917 323 1233', NULL, 'Mon-Sat, 8AM-5PM', NULL, 14.5995000, 120.9842000, 1, 'active', '2026-08-21 07:53:23', '2026-08-21 07:53:23'),
(4, 2, '1231231', NULL, '12313213', '13213123', '+63 912 321 3131', NULL, 'Mon-Sat, 8AM-5PM', NULL, 14.5995000, 120.9842000, 1, 'active', '2026-08-27 23:19:55', '2026-08-27 23:20:28'),
(5, 2, '123', NULL, '123', '123', '+63 952 325 3534', NULL, 'Mon - Th, 10AM-7PM', NULL, 14.6053922, 120.9918242, 1, 'active', '2026-08-30 15:31:52', '2026-08-30 23:54:00');

-- --------------------------------------------------------

--
-- Table structure for table `branch_damage_reports`
--

CREATE TABLE `branch_damage_reports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `variant_size` varchar(120) NOT NULL DEFAULT '',
  `variant_color` varchar(120) NOT NULL DEFAULT '',
  `quantity` int(10) UNSIGNED NOT NULL,
  `note` text DEFAULT NULL,
  `reported_by_user_id` int(10) UNSIGNED NOT NULL,
  `status` enum('reported','approved','rejected') NOT NULL DEFAULT 'reported',
  `reviewed_by_user_id` int(10) UNSIGNED DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branch_damage_reports`
--

INSERT INTO `branch_damage_reports` (`id`, `branch_id`, `product_id`, `variant_size`, `variant_color`, `quantity`, `note`, `reported_by_user_id`, `status`, `reviewed_by_user_id`, `reviewed_at`, `created_at`) VALUES
(1, 2, 7, '', '', 2, 'sira e', 19, 'approved', 15, '2026-08-29 14:42:37', '2026-08-29 14:39:39');

-- --------------------------------------------------------

--
-- Table structure for table `branch_inventory_transfers`
--

CREATE TABLE `branch_inventory_transfers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `variant_size` varchar(120) NOT NULL DEFAULT '',
  `variant_color` varchar(120) NOT NULL DEFAULT '',
  `quantity` int(10) UNSIGNED NOT NULL,
  `direction` enum('inventory_to_pos','pos_to_inventory','inventory_to_seller') NOT NULL,
  `performed_by_user_id` int(10) UNSIGNED NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branch_inventory_transfers`
--

INSERT INTO `branch_inventory_transfers` (`id`, `branch_id`, `product_id`, `variant_size`, `variant_color`, `quantity`, `direction`, `performed_by_user_id`, `note`, `created_at`) VALUES
(1, 2, 7, '', '', 2, 'inventory_to_pos', 15, NULL, '2026-08-29 13:19:59'),
(2, 2, 1, '', '', 20, 'inventory_to_pos', 15, NULL, '2026-08-29 13:21:47'),
(3, 2, 7, '', '', 2, 'pos_to_inventory', 15, NULL, '2026-08-29 14:55:40'),
(4, 2, 1, '', '', 20, 'pos_to_inventory', 15, NULL, '2026-08-29 14:55:54'),
(5, 2, 1, '', '', 33, 'inventory_to_pos', 15, 'Created Branch POS listing #27', '2026-08-29 15:02:15'),
(6, 2, 1, '', '', 10, 'inventory_to_pos', 15, 'Created Branch POS listing #28', '2026-08-29 15:02:55');

-- --------------------------------------------------------

--
-- Table structure for table `branch_managers`
--

CREATE TABLE `branch_managers` (
  `id` int(10) UNSIGNED NOT NULL,
  `seller_id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `status` enum('active','inactive','archived') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branch_managers`
--

INSERT INTO `branch_managers` (`id`, `seller_id`, `branch_id`, `user_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 2, 15, 'active', '2026-08-25 10:49:48', '2026-08-31 05:23:32');

-- --------------------------------------------------------

--
-- Table structure for table `branch_pos_stock`
--

CREATE TABLE `branch_pos_stock` (
  `branch_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `variant_size` varchar(120) NOT NULL DEFAULT '',
  `variant_color` varchar(120) NOT NULL DEFAULT '',
  `stock` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branch_pos_stock`
--

INSERT INTO `branch_pos_stock` (`branch_id`, `product_id`, `variant_size`, `variant_color`, `stock`, `updated_at`) VALUES
(2, 1, '', '', 0, '2026-08-29 14:55:54'),
(2, 7, '', '', 0, '2026-08-29 14:55:40'),
(2, 27, '', '', 15, '2026-08-31 08:41:37'),
(2, 28, 'XL', 'blue', 10, '2026-08-29 15:25:45');

-- --------------------------------------------------------

--
-- Table structure for table `branch_staff`
--

CREATE TABLE `branch_staff` (
  `branch_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branch_stock`
--

CREATE TABLE `branch_stock` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `variant_size` varchar(30) NOT NULL DEFAULT '',
  `variant_color` varchar(50) NOT NULL DEFAULT '',
  `branch_id` int(10) UNSIGNED NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branch_stock`
--

INSERT INTO `branch_stock` (`id`, `product_id`, `variant_size`, `variant_color`, `branch_id`, `stock`, `updated_at`) VALUES
(16, 1, '', '', 2, 20, '2026-08-29 15:02:55'),
(17, 5, '', '', 2, 20, '2026-08-28 10:07:16'),
(18, 8, '', '', 2, 20, '2026-08-28 10:07:16'),
(19, 9, '', '', 2, 20, '2026-08-28 10:07:16'),
(20, 16, '', '', 2, 22, '2026-08-28 10:07:16'),
(21, 17, '', '', 2, 26, '2026-08-28 10:07:16'),
(22, 19, '', '', 2, 16, '2026-08-29 10:34:37'),
(23, 20, '', '', 2, 38, '2026-08-28 10:07:16'),
(24, 3, '', '', 3, 18, '2026-08-28 10:07:16'),
(26, 6, '', '', 3, 0, NULL),
(27, 7, '', '', 3, 19, '2026-08-28 10:07:16'),
(28, 8, '', '', 3, 0, NULL),
(29, 16, '', '', 3, 0, NULL),
(30, 17, '', '', 3, 0, NULL),
(31, 18, '', '', 3, 20, '2026-08-28 10:07:16'),
(32, 20, '', '', 3, 0, NULL),
(36, 7, '', '', 2, 8, '2026-08-29 14:55:40');

-- --------------------------------------------------------

--
-- Table structure for table `branch_stock_adjustments`
--

CREATE TABLE `branch_stock_adjustments` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `variant_size` varchar(30) NOT NULL DEFAULT '',
  `variant_color` varchar(50) NOT NULL DEFAULT '',
  `branch_id` int(10) UNSIGNED NOT NULL,
  `adjusted_by_user_id` int(11) NOT NULL,
  `adjusted_by_role` varchar(20) NOT NULL,
  `previous_stock` int(11) NOT NULL,
  `new_stock` int(11) NOT NULL,
  `change_amount` int(11) NOT NULL,
  `reason` varchar(30) NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branch_stock_adjustments`
--

INSERT INTO `branch_stock_adjustments` (`id`, `product_id`, `variant_size`, `variant_color`, `branch_id`, `adjusted_by_user_id`, `adjusted_by_role`, `previous_stock`, `new_stock`, `change_amount`, `reason`, `note`, `created_at`) VALUES
(1, 1, '', '', 2, 0, 'system', 0, 43, 43, 'initial_stock', 'Migrated from single shared stock during per-branch stock rollout.', '2026-08-28 10:07:16'),
(2, 5, '', '', 2, 0, 'system', 0, 20, 20, 'initial_stock', 'Migrated from single shared stock during per-branch stock rollout.', '2026-08-28 10:07:16'),
(3, 8, '', '', 2, 0, 'system', 0, 20, 20, 'initial_stock', 'Migrated from single shared stock during per-branch stock rollout.', '2026-08-28 10:07:16'),
(4, 9, '', '', 2, 0, 'system', 0, 20, 20, 'initial_stock', 'Migrated from single shared stock during per-branch stock rollout.', '2026-08-28 10:07:16'),
(5, 16, '', '', 2, 0, 'system', 0, 22, 22, 'initial_stock', 'Migrated from single shared stock during per-branch stock rollout.', '2026-08-28 10:07:16'),
(6, 17, '', '', 2, 0, 'system', 0, 26, 26, 'initial_stock', 'Migrated from single shared stock during per-branch stock rollout.', '2026-08-28 10:07:16'),
(7, 19, '', '', 2, 0, 'system', 0, 41, 41, 'initial_stock', 'Migrated from single shared stock during per-branch stock rollout.', '2026-08-28 10:07:16'),
(8, 20, '', '', 2, 0, 'system', 0, 38, 38, 'initial_stock', 'Migrated from single shared stock during per-branch stock rollout.', '2026-08-28 10:07:16'),
(9, 3, '', '', 3, 0, 'system', 0, 18, 18, 'initial_stock', 'Migrated from single shared stock during per-branch stock rollout.', '2026-08-28 10:07:16'),
(11, 7, '', '', 3, 0, 'system', 0, 19, 19, 'initial_stock', 'Migrated from single shared stock during per-branch stock rollout.', '2026-08-28 10:07:16'),
(12, 18, '', '', 3, 0, 'system', 0, 20, 20, 'initial_stock', 'Migrated from single shared stock during per-branch stock rollout.', '2026-08-28 10:07:16'),
(13, 19, '', '', 2, 15, 'manager', 41, 36, -5, 'restock', NULL, '2026-08-29 05:11:43'),
(14, 19, '', '', 2, 15, 'manager', 36, 0, -36, 'damaged', NULL, '2026-08-29 05:12:31'),
(15, 19, '', '', 2, 15, 'manager', 0, 20, 20, 'restock', NULL, '2026-08-29 05:13:40'),
(16, 19, '', '', 2, 0, 'system', 20, 19, -1, 'sale', 'Order #17', '2026-08-29 10:04:29'),
(17, 19, '', '', 2, 0, 'system', 19, 18, -1, 'sale', 'Order #18', '2026-08-29 10:06:06'),
(18, 19, '', '', 2, 4, 'customer', 18, 17, -1, 'sale', 'Order #19', '2026-08-29 10:21:31'),
(19, 19, '', '', 2, 4, 'customer', 17, 16, -1, 'sale', 'Order #21', '2026-08-29 10:34:37'),
(20, 7, '', '', 2, 2, 'seller', 0, 10, 10, 'restock', 'Seller allocation', '2026-08-29 12:32:48'),
(21, 1, '', '', 2, 2, 'seller', 0, 20, 20, 'restock', 'Seller allocation', '2026-08-29 13:21:01'),
(22, 7, '', '', 2, 15, 'branch_manager', 8, 6, -2, 'damaged', 'sira e', '2026-08-29 14:42:37'),
(23, 28, 'XL', 'blue', 2, 4, 'customer', 10, 0, -10, 'sale', 'Order #22', '2026-08-29 15:25:25'),
(24, 28, 'XL', 'blue', 2, 4, 'customer', 0, 10, 10, 'restock', 'Order #22 cancelled', '2026-08-29 15:25:45'),
(25, 27, '', '', 2, 4, 'customer', 33, 32, -1, 'sale', 'Order #23', '2026-08-29 15:42:16'),
(26, 27, '', '', 2, 4, 'customer', 32, 31, -1, 'sale', 'Order #24', '2026-08-29 15:42:46'),
(27, 27, '', '', 2, 4, 'customer', 31, 30, -1, 'sale', 'Order #25', '2026-08-30 10:55:02'),
(28, 27, '', '', 2, 0, 'system', 30, 29, -1, 'sale', 'Order #26', '2026-08-31 08:02:38'),
(29, 27, '', '', 2, 0, 'system', 29, 28, -1, 'sale', 'Order #27', '2026-08-31 08:02:50'),
(30, 27, '', '', 2, 0, 'system', 28, 27, -1, 'sale', 'Order #28', '2026-08-31 08:06:07'),
(31, 27, '', '', 2, 0, 'system', 27, 25, -2, 'sale', 'Order #29', '2026-08-31 08:06:49'),
(32, 27, '', '', 2, 0, 'system', 25, 24, -1, 'sale', 'Order #30', '2026-08-31 08:15:06'),
(33, 27, '', '', 2, 0, 'system', 24, 23, -1, 'sale', 'Order #31', '2026-08-31 08:15:12'),
(34, 27, '', '', 2, 0, 'system', 23, 22, -1, 'sale', 'Order #32', '2026-08-31 08:15:21'),
(35, 27, '', '', 2, 0, 'system', 22, 21, -1, 'sale', 'Order #33', '2026-08-31 08:16:16'),
(36, 27, '', '', 2, 0, 'system', 21, 20, -1, 'sale', 'Order #34', '2026-08-31 08:24:03'),
(37, 27, '', '', 2, 0, 'system', 20, 19, -1, 'sale', 'Order #35', '2026-08-31 08:28:47'),
(38, 27, '', '', 2, 0, 'system', 19, 18, -1, 'sale', 'Order #36', '2026-08-31 08:32:54'),
(39, 27, '', '', 2, 0, 'system', 18, 17, -1, 'sale', 'Order #37', '2026-08-31 08:39:09'),
(40, 27, '', '', 2, 0, 'system', 17, 16, -1, 'sale', 'Order #38', '2026-08-31 08:39:31'),
(41, 27, '', '', 2, 0, 'system', 16, 15, -1, 'sale', 'Order #39', '2026-08-31 08:41:37');

-- --------------------------------------------------------

--
-- Table structure for table `branch_stock_allocations`
--

CREATE TABLE `branch_stock_allocations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `seller_id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `variant_size` varchar(120) NOT NULL DEFAULT '',
  `variant_color` varchar(120) NOT NULL DEFAULT '',
  `quantity_allocated` int(10) UNSIGNED NOT NULL,
  `quantity_used` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `quantity_remaining` int(10) UNSIGNED NOT NULL,
  `allocated_by_user_id` int(10) UNSIGNED NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branch_stock_allocations`
--

INSERT INTO `branch_stock_allocations` (`id`, `seller_id`, `branch_id`, `product_id`, `variant_size`, `variant_color`, `quantity_allocated`, `quantity_used`, `quantity_remaining`, `allocated_by_user_id`, `note`, `created_at`, `updated_at`) VALUES
(1, 2, 2, 7, '', '', 10, 2, 8, 2, NULL, '2026-08-29 12:32:48', '2026-08-29 14:55:40'),
(2, 2, 2, 1, '', '', 20, 0, 20, 2, NULL, '2026-08-29 13:21:01', '2026-08-29 14:55:54');

-- --------------------------------------------------------

--
-- Table structure for table `customer_reports`
--

CREATE TABLE `customer_reports` (
  `id` int(10) UNSIGNED NOT NULL,
  `reporter_id` int(10) UNSIGNED NOT NULL,
  `target_type` enum('product','seller') NOT NULL,
  `target_id` int(10) UNSIGNED NOT NULL,
  `reason` varchar(120) NOT NULL,
  `details` text NOT NULL,
  `status` enum('open','reviewing','resolved','dismissed') NOT NULL DEFAULT 'open',
  `reviewer_id` int(10) UNSIGNED DEFAULT NULL,
  `review_note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_reports`
--

INSERT INTO `customer_reports` (`id`, `reporter_id`, `target_type`, `target_id`, `reason`, `details`, `status`, `reviewer_id`, `review_note`, `created_at`, `reviewed_at`) VALUES
(1, 4, 'product', 3, 'Counterfeit or misleading item', 'nothing happend', 'resolved', 1, NULL, '2026-08-21 14:44:46', '2026-08-21 22:45:44'),
(2, 4, 'seller', 2, 'Inappropriate content', 'nothing happend', 'dismissed', 1, NULL, '2026-08-21 14:45:06', '2026-08-23 13:06:17'),
(3, 4, 'seller', 2, 'Counterfeit or misleading item', 'adasdadasdasdadasdasd', 'resolved', 1, NULL, '2026-08-23 05:21:06', '2026-08-23 13:21:48'),
(4, 4, 'product', 3, 'Scam or suspicious activity', 'asdasdadasdasdasdasdasdasdasd', 'dismissed', 1, NULL, '2026-08-23 05:21:21', '2026-08-23 13:34:35'),
(5, 4, 'seller', 2, 'Counterfeit or misleading item', 'asdadsadadasdasdasdasd', 'resolved', 1, NULL, '2026-08-23 05:28:22', '2026-08-23 13:39:48'),
(6, 4, 'product', 20, 'Counterfeit or misleading item', 'asdasdasdad', 'open', NULL, NULL, '2026-08-29 05:04:09', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `damaged_products`
--

CREATE TABLE `damaged_products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `seller_id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `variant_size` varchar(120) NOT NULL DEFAULT '',
  `variant_color` varchar(120) NOT NULL DEFAULT '',
  `quantity` int(10) UNSIGNED NOT NULL,
  `reason` varchar(100) NOT NULL,
  `note` text DEFAULT NULL,
  `recorded_by_user_id` int(10) UNSIGNED NOT NULL,
  `recorded_by_role` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `damaged_products`
--

INSERT INTO `damaged_products` (`id`, `seller_id`, `branch_id`, `product_id`, `variant_size`, `variant_color`, `quantity`, `reason`, `note`, `recorded_by_user_id`, `recorded_by_role`, `created_at`) VALUES
(1, 2, 2, 7, '', '', 2, 'Damaged stock', 'sira e', 15, 'branch_manager', '2026-08-29 14:42:37');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('new','reviewed') NOT NULL DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(10) UNSIGNED NOT NULL,
  `seller_id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED DEFAULT NULL,
  `customer_id` int(10) UNSIGNED DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `processed_by_user_id` int(11) DEFAULT NULL,
  `shipping_address_id` int(10) UNSIGNED DEFAULT NULL,
  `shipping_recipient_name` varchar(150) DEFAULT NULL,
  `shipping_phone` varchar(30) DEFAULT NULL,
  `shipping_address_text` text DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` enum('cash','gcash','card','paymongo','other') NOT NULL DEFAULT 'cash',
  `order_type` enum('pos','online') NOT NULL DEFAULT 'pos',
  `status` enum('pending','packed','shipped','completed','cancelled','refunded') NOT NULL DEFAULT 'pending',
  `courier` varchar(100) DEFAULT NULL,
  `tracking_number` varchar(120) DEFAULT NULL,
  `packed_at` datetime DEFAULT NULL,
  `shipped_at` datetime DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `seller_id`, `branch_id`, `customer_id`, `customer_name`, `processed_by_user_id`, `shipping_address_id`, `shipping_recipient_name`, `shipping_phone`, `shipping_address_text`, `total_amount`, `payment_method`, `order_type`, `status`, `courier`, `tracking_number`, `packed_at`, `shipped_at`, `delivered_at`, `cancelled_at`, `created_at`) VALUES
(1, 2, NULL, 4, NULL, NULL, NULL, NULL, NULL, NULL, 200.00, 'cash', 'online', 'completed', 'JNT', '1', '2026-08-15 11:43:43', '2026-08-15 11:43:55', '2026-08-15 11:44:01', NULL, '2026-08-15 03:43:27'),
(2, 2, NULL, NULL, 'Walk-in customer', NULL, NULL, NULL, NULL, NULL, 200.00, 'gcash', 'pos', 'completed', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-15 05:03:06'),
(3, 2, NULL, 4, NULL, NULL, NULL, NULL, NULL, NULL, 200.00, 'cash', 'online', 'packed', NULL, NULL, '2026-08-15 22:56:53', NULL, NULL, NULL, '2026-08-15 05:04:51'),
(4, 2, NULL, NULL, 'Walk-in customer', NULL, NULL, NULL, NULL, NULL, 200.00, 'gcash', 'pos', 'completed', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-15 17:39:36'),
(5, 2, NULL, 4, NULL, NULL, 1, 'vixen montiman', '09604734765', 'anabu, anabu1, imus, cavite, 4103', 200.00, 'cash', 'online', 'completed', 'J&T Express', 'JNT-981087377701', '2026-08-24 18:16:42', '2026-08-24 18:16:44', '2026-08-24 18:16:50', NULL, '2026-08-21 08:15:48'),
(6, 2, NULL, 7, NULL, NULL, 2, '123', '09604734765', '123, 123, 123, 123, 13, 123', 123.00, 'cash', 'online', 'completed', 'LBC Express', 'LBC-228981815391', '2026-08-24 18:18:01', '2026-08-24 18:18:03', '2026-08-24 18:18:10', NULL, '2026-08-24 10:15:57'),
(7, 2, NULL, 7, NULL, NULL, 2, '123', '09604734765', '123, 123, 123, 123, 13, 123', 123.00, 'cash', 'online', 'completed', 'LBC Express', 'LBC-906934941345', '2026-08-24 18:31:54', '2026-08-24 18:31:55', '2026-08-24 18:32:00', NULL, '2026-08-24 10:31:39'),
(8, 2, NULL, 7, NULL, NULL, 2, '123', '09604734765', '123, 123, 123, 123, 13, 123', 2000.00, 'cash', 'online', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-24 10:46:17'),
(9, 2, 2, 4, NULL, NULL, 1, 'vixen montiman', '09604734765', 'anabu, anabu1, imus, cavite, 4103', 20.00, 'cash', 'online', 'completed', 'J&T Express', 'JNT-263896285086', '2026-08-28 13:30:29', '2026-08-28 13:30:31', '2026-08-28 13:30:39', NULL, '2026-08-26 10:34:08'),
(10, 2, 2, 4, NULL, NULL, 1, 'vixen montiman', '09604734765', 'anabu, anabu1, imus, cavite, 4103', 20.00, 'cash', 'online', 'completed', 'JRS Express', 'JRS-561604510390', '2026-08-28 13:32:04', '2026-08-28 13:32:05', '2026-08-28 13:32:11', NULL, '2026-08-26 10:46:25'),
(11, 2, 2, NULL, 'Walk-in customer', NULL, NULL, NULL, NULL, NULL, 200.00, 'cash', 'pos', 'completed', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-29 05:05:30'),
(12, 2, 2, NULL, 'Walk-in customer', NULL, NULL, NULL, NULL, NULL, 2000.00, 'cash', 'pos', 'completed', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-29 05:05:42'),
(13, 2, 2, NULL, 'Walk-in customer', NULL, NULL, NULL, NULL, NULL, 1353.00, 'cash', 'pos', 'completed', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-29 05:06:25'),
(14, 2, 2, NULL, 'Walk-in customer', NULL, NULL, NULL, NULL, NULL, 1230.00, 'cash', 'pos', 'completed', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-29 05:12:15'),
(15, 2, 2, NULL, 'Walk-in customer', NULL, NULL, NULL, NULL, NULL, 123.00, 'cash', 'pos', 'completed', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-29 05:12:36'),
(16, 2, 2, NULL, 'Walk-in customer', NULL, NULL, NULL, NULL, NULL, 246.00, 'cash', 'pos', 'completed', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-29 05:54:19'),
(17, 2, 2, NULL, 'Walk-in customer', NULL, NULL, NULL, NULL, NULL, 123.00, 'cash', 'pos', 'completed', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-29 10:04:29'),
(18, 2, 2, NULL, 'Walk-in customer', NULL, NULL, NULL, NULL, NULL, 123.00, 'cash', 'pos', 'completed', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-29 10:06:06'),
(19, 2, 2, 4, NULL, NULL, 1, 'vixen montiman', '09604734765', 'anabu, anabu1, imus, cavite, 4103', 123.00, 'cash', 'online', 'completed', 'J&T Express', 'JNT-423074286393', '2026-08-29 18:22:44', '2026-08-29 18:22:46', '2026-08-29 18:22:52', NULL, '2026-08-29 10:21:31'),
(20, 2, NULL, NULL, 'Walk-in customer', NULL, NULL, NULL, NULL, NULL, 123.00, 'cash', 'pos', 'completed', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-29 10:26:17'),
(21, 2, 2, 4, NULL, NULL, 1, 'vixen montiman', '09604734765', 'anabu, anabu1, imus, cavite, 4103', 123.00, 'cash', 'online', 'completed', 'LBC Express', 'LBC-584559860565', '2026-08-29 18:34:49', '2026-08-29 18:34:50', '2026-08-29 18:34:56', NULL, '2026-08-29 10:34:37'),
(22, 2, 2, 4, NULL, NULL, 1, 'vixen montiman', '09604734765', 'anabu, anabu1, imus, cavite, 4103', 1000.00, 'cash', 'online', 'cancelled', NULL, NULL, NULL, NULL, NULL, '2026-08-29 23:25:45', '2026-08-29 15:25:25'),
(23, 2, 2, 4, NULL, NULL, 1, 'vixen montiman', '09604734765', 'anabu, anabu1, imus, cavite, 4103', 123.00, 'paymongo', 'online', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-29 15:42:16'),
(24, 2, 2, 4, NULL, NULL, 1, 'vixen montiman', '09604734765', 'anabu, anabu1, imus, cavite, 4103', 123.00, 'paymongo', 'online', 'completed', 'J&T Express', 'JNT-257368832084', '2026-08-29 23:43:39', '2026-08-29 23:43:41', '2026-08-29 23:43:46', NULL, '2026-08-29 15:42:46'),
(25, 2, 2, 4, NULL, NULL, 1, 'vixen montiman', '09604734765', 'anabu, anabu1, imus, cavite, 4103', 123.00, 'paymongo', 'online', 'packed', 'J&T Express', 'JNT-877895166899', '2026-08-30 23:56:03', NULL, NULL, NULL, '2026-08-30 10:55:02'),
(26, 2, 2, NULL, 'Walk-in customer', NULL, NULL, NULL, NULL, NULL, 123.00, 'other', 'pos', 'completed', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-31 08:02:38'),
(27, 2, 2, NULL, 'Walk-in customer', NULL, NULL, NULL, NULL, NULL, 123.00, 'gcash', 'pos', 'completed', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-31 08:02:50'),
(28, 2, 2, NULL, 'Walk-in customer', NULL, NULL, NULL, NULL, NULL, 123.00, 'gcash', 'pos', 'completed', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-31 08:06:07'),
(29, 2, 2, NULL, 'Walk-in customer', NULL, NULL, NULL, NULL, NULL, 246.00, 'gcash', 'pos', 'completed', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-31 08:06:49'),
(30, 2, 2, NULL, 'Walk-in customer', NULL, NULL, NULL, NULL, NULL, 123.00, 'other', 'pos', 'completed', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-31 08:15:06'),
(31, 2, 2, NULL, 'Walk-in customer', NULL, NULL, NULL, NULL, NULL, 123.00, 'gcash', 'pos', 'completed', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-31 08:15:12'),
(32, 2, 2, NULL, 'Walk-in customer', NULL, NULL, NULL, NULL, NULL, 123.00, 'card', 'pos', 'completed', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-31 08:15:21'),
(33, 2, 2, NULL, 'Walk-in customer', NULL, NULL, NULL, NULL, NULL, 123.00, 'gcash', 'pos', 'completed', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-31 08:16:16'),
(34, 2, 2, NULL, 'Walk-in customer', NULL, NULL, NULL, NULL, NULL, 123.00, 'cash', 'pos', 'completed', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-31 08:24:03'),
(35, 2, 2, NULL, 'Walk-in customer', 21, NULL, NULL, NULL, NULL, 123.00, 'cash', 'pos', 'completed', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-31 08:28:47'),
(36, 2, 2, NULL, 'Walk-in customer', 18, NULL, NULL, NULL, NULL, 123.00, 'cash', 'pos', 'completed', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-31 08:32:54'),
(37, 2, 2, NULL, 'Walk-in customer', NULL, NULL, NULL, NULL, NULL, 123.00, 'cash', 'pos', 'completed', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-31 08:39:09'),
(38, 2, 2, NULL, 'Walk-in customer', NULL, NULL, NULL, NULL, NULL, 123.00, 'cash', 'pos', 'completed', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-31 08:39:31'),
(39, 2, 2, NULL, 'Walk-in customer', NULL, NULL, NULL, NULL, NULL, 123.00, 'cash', 'pos', 'completed', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-31 08:41:37');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED DEFAULT NULL,
  `variant_id` int(10) UNSIGNED DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `variant_label` varchar(120) DEFAULT NULL,
  `variant_size` varchar(30) NOT NULL DEFAULT '',
  `variant_color` varchar(50) NOT NULL DEFAULT '',
  `unit_price` decimal(10,2) NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `variant_id`, `product_name`, `variant_label`, `variant_size`, `variant_color`, `unit_price`, `quantity`, `subtotal`, `created_at`) VALUES
(1, 1, 1, NULL, 'clothes', NULL, '', '', 200.00, 1, 200.00, '2026-08-15 03:43:27'),
(2, 2, 3, NULL, 'clothes', NULL, '', '', 200.00, 1, 200.00, '2026-08-15 05:03:06'),
(3, 3, 3, NULL, 'clothes', NULL, '', '', 200.00, 1, 200.00, '2026-08-15 05:04:51'),
(4, 4, 1, NULL, 'clothes', NULL, '', '', 200.00, 1, 200.00, '2026-08-15 17:39:36'),
(5, 5, 1, NULL, 'clothes', NULL, '', '', 200.00, 1, 200.00, '2026-08-21 08:15:48'),
(6, 6, 19, NULL, '123', NULL, '', '', 123.00, 1, 123.00, '2026-08-24 10:15:57'),
(7, 7, 19, NULL, '123', NULL, '', '', 123.00, 1, 123.00, '2026-08-24 10:31:39'),
(8, 8, 7, NULL, 'clothes', NULL, '', '', 2000.00, 1, 2000.00, '2026-08-24 10:46:17'),
(9, 9, 20, NULL, 'clothes', NULL, '', '', 20.00, 1, 20.00, '2026-08-26 10:34:08'),
(10, 10, 20, NULL, 'clothes', NULL, '', '', 20.00, 1, 20.00, '2026-08-26 10:46:25'),
(11, 11, 1, NULL, 'clothes', NULL, '', '', 200.00, 1, 200.00, '2026-08-29 05:05:30'),
(12, 12, 1, NULL, 'clothes', NULL, '', '', 200.00, 10, 2000.00, '2026-08-29 05:05:42'),
(13, 13, 19, NULL, '123', NULL, '', '', 123.00, 11, 1353.00, '2026-08-29 05:06:25'),
(14, 14, 19, NULL, '123', NULL, '', '', 123.00, 10, 1230.00, '2026-08-29 05:12:15'),
(15, 15, 19, NULL, '123', NULL, '', '', 123.00, 1, 123.00, '2026-08-29 05:12:36'),
(16, 16, 19, NULL, '123', NULL, '', '', 123.00, 2, 246.00, '2026-08-29 05:54:19'),
(17, 17, 19, NULL, '123', NULL, '', '', 123.00, 1, 123.00, '2026-08-29 10:04:29'),
(18, 18, 19, NULL, 'try lang', NULL, '', '', 123.00, 1, 123.00, '2026-08-29 10:06:06'),
(19, 19, 19, NULL, 'try lang', NULL, '', '', 123.00, 1, 123.00, '2026-08-29 10:21:31'),
(20, 20, 19, NULL, 'try lang', NULL, '', '', 123.00, 1, 123.00, '2026-08-29 10:26:17'),
(21, 21, 19, NULL, 'try lang', NULL, '', '', 123.00, 1, 123.00, '2026-08-29 10:34:37'),
(22, 22, 28, 10, 'etotalaga', 'XL / blue', 'XL', 'blue', 100.00, 10, 1000.00, '2026-08-29 15:25:25'),
(23, 23, 27, NULL, 'etotalaga', NULL, '', '', 123.00, 1, 123.00, '2026-08-29 15:42:16'),
(24, 24, 27, NULL, 'etotalaga', NULL, '', '', 123.00, 1, 123.00, '2026-08-29 15:42:46'),
(25, 25, 27, NULL, 'etotalaga', NULL, '', '', 123.00, 1, 123.00, '2026-08-30 10:55:02'),
(26, 26, 27, NULL, 'etotalaga', NULL, '', '', 123.00, 1, 123.00, '2026-08-31 08:02:38'),
(27, 27, 27, NULL, 'etotalaga', NULL, '', '', 123.00, 1, 123.00, '2026-08-31 08:02:50'),
(28, 28, 27, NULL, 'etotalaga', NULL, '', '', 123.00, 1, 123.00, '2026-08-31 08:06:07'),
(29, 29, 27, NULL, 'etotalaga', NULL, '', '', 123.00, 2, 246.00, '2026-08-31 08:06:49'),
(30, 30, 27, NULL, 'etotalaga', NULL, '', '', 123.00, 1, 123.00, '2026-08-31 08:15:06'),
(31, 31, 27, NULL, 'etotalaga', NULL, '', '', 123.00, 1, 123.00, '2026-08-31 08:15:12'),
(32, 32, 27, NULL, 'etotalaga', NULL, '', '', 123.00, 1, 123.00, '2026-08-31 08:15:21'),
(33, 33, 27, NULL, 'etotalaga', NULL, '', '', 123.00, 1, 123.00, '2026-08-31 08:16:16'),
(34, 34, 27, NULL, 'etotalaga', NULL, '', '', 123.00, 1, 123.00, '2026-08-31 08:24:03'),
(35, 35, 27, NULL, 'etotalaga', NULL, '', '', 123.00, 1, 123.00, '2026-08-31 08:28:47'),
(36, 36, 27, NULL, 'etotalaga', NULL, '', '', 123.00, 1, 123.00, '2026-08-31 08:32:54'),
(37, 37, 27, NULL, 'etotalaga', NULL, '', '', 123.00, 1, 123.00, '2026-08-31 08:39:09'),
(38, 38, 27, NULL, 'etotalaga', NULL, '', '', 123.00, 1, 123.00, '2026-08-31 08:39:31'),
(39, 39, 27, NULL, 'etotalaga', NULL, '', '', 123.00, 1, 123.00, '2026-08-31 08:41:37');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(10) UNSIGNED NOT NULL,
  `seller_id` int(10) UNSIGNED NOT NULL,
  `stock_request_id` int(10) UNSIGNED DEFAULT NULL,
  `inventory_source_product_id` int(10) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `size_guide` text DEFAULT NULL,
  `fit_information` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stock` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `category` varchar(100) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','pending_review','rejected') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `seller_id`, `stock_request_id`, `inventory_source_product_id`, `name`, `description`, `size_guide`, `fit_information`, `price`, `stock`, `category`, `image_url`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 2, NULL, 'etotalaga', 'Good For Fashion', 'S:Bust 70cm, Waist 60cm', 'Fabric', 200.00, 12, 'Branded Clothes', 'uploads/products/product_6a8bdf1e5932f1.87929802.jpg', 'active', '2026-08-14 09:59:09', '2026-08-29 13:21:01'),
(3, 2, 5, NULL, 'clothes', '12313123', 'S:Bust 81cm, Waist 70cm', 'fabric', 200.00, 18, 'Clothes', 'uploads/products/product_6a7fdfc51fd150.81288658.jpg', 'active', '2026-08-15 03:40:53', '2026-08-24 10:11:08'),
(5, 2, 6, NULL, 'clothes', '213', '123', '123', 123.00, 20, 'Clothes', 'uploads/products/product_6a80b90698a683.96726761.jpeg', 'inactive', '2026-08-15 19:07:50', '2026-08-24 06:00:08'),
(6, 2, 1, NULL, 'clothes', '123', '123', '123', 123.00, 0, 'Clothes', 'uploads/products/product_6a80b9ca22d417.19554574.jpeg', 'inactive', '2026-08-15 19:11:06', '2026-08-24 06:00:16'),
(7, 2, 4, NULL, 'clothes', 'asdadasdadasd', 'asdasdasdadasd', 'adasdasdasdadasd', 2000.00, 9, 'clothes', 'uploads/products/product_6a8a88b53e70a6.67344496.png', 'active', '2026-08-23 05:44:21', '2026-08-29 12:32:48'),
(8, 2, 3, NULL, 'clothes', '123131232', '12313123213', '12313123', 21.00, 20, 'Clothes', 'uploads/products/product_6a8acfaaa35a27.54260150.png', 'inactive', '2026-08-23 10:47:06', '2026-08-24 06:00:51'),
(9, 2, NULL, NULL, 'clothes', '123', '123', '123', 21.00, 20, 'Clothes', 'uploads/products/product_6a8ad030e86a30.54518802.jpeg', 'inactive', '2026-08-23 10:49:20', '2026-08-24 05:59:25'),
(16, 2, 10, NULL, '123', '123', '123', '132', 232.00, 0, 'Clothes', 'uploads/products/product_6a8ad3e7aa5566.67911415.jpeg', 'inactive', '2026-08-23 11:05:11', '2026-08-29 14:58:01'),
(17, 2, 9, NULL, '123', '123', '123', '13', 200.00, 26, 'Clothes', 'uploads/products/product_6a8ad4baa775f8.48965584.jpeg', 'inactive', '2026-08-23 11:08:42', '2026-08-24 06:00:31'),
(18, 2, 8, NULL, 'clothes', '123', '123', '123', 123.00, 20, 'Clothes', 'uploads/products/product_6a8ad4e4645f95.80451237.jpeg', 'inactive', '2026-08-23 11:09:24', '2026-08-24 06:00:25'),
(19, 2, 11, NULL, 'try lang', '123', '123', '123', 123.00, 15, 'clothes', 'uploads/products/product_6a8c18fe6a0917.05246679.png', 'active', '2026-08-24 10:12:14', '2026-08-29 10:34:37'),
(20, 2, 12, NULL, 'clothes', '123', '123', '123', 20.00, 29, 'clothes', 'uploads/products/product_6a8c217f902245.85125826.jpg', 'active', '2026-08-24 10:48:31', '2026-08-29 14:48:21'),
(21, 2, NULL, 16, '123', '123', '123', '123', 123.00, 0, 'clothes', 'uploads/products/product_6a92f37908eb69.34062476.jpg', 'pending_review', '2026-08-29 14:58:01', '2026-08-29 14:58:01'),
(22, 2, NULL, 1, 'etotalaga', '213', '123', '123123', 123.00, 23, 'Clothes', 'uploads/products/product_6a92f38f35e5d8.12572701.jpg', 'active', '2026-08-29 14:58:23', '2026-08-29 14:58:23'),
(23, 2, NULL, 1, 'etotalaga', '123', '123', '123', 123.00, 23, 'Branded Clothes', 'uploads/products/product_6a92f3bcc30917.90652066.jpg', 'active', '2026-08-29 14:59:08', '2026-08-29 14:59:08'),
(24, 2, NULL, 1, 'etotalaga', '123', '123', '123', 123.00, 23, 'Branded Clothes', 'uploads/products/product_6a92f3dc14edf4.45987539.jpg', 'active', '2026-08-29 14:59:40', '2026-08-29 14:59:40'),
(25, 2, NULL, 1, 'etotalaga', '123', '123', '123', 123.00, 23, 'Branded Clothes', 'uploads/products/product_6a92f3f270b769.59109265.jpg', 'active', '2026-08-29 15:00:02', '2026-08-29 15:00:02'),
(26, 2, NULL, 1, 'etotalaga', '123', '123', '123', 123.00, 31, 'Branded Clothes', 'uploads/products/product_6a92f41838fe53.82723910.jpg', 'active', '2026-08-29 15:00:40', '2026-08-30 14:54:25'),
(27, 2, NULL, 1, 'etotalaga', '123', '123', '123', 123.00, 0, 'Branded Clothes', 'uploads/products/product_6a92f47752a530.01500176.jpg', 'active', '2026-08-29 15:02:15', '2026-08-29 15:02:15'),
(28, 2, NULL, 1, 'etotalaga', '123', '123', '123', 100.00, 0, 'Branded Clothes', 'uploads/products/product_6a92f49f9b3e26.96310067.jpg', 'active', '2026-08-29 15:02:55', '2026-08-29 15:02:55');

-- --------------------------------------------------------

--
-- Table structure for table `product_branches`
--

CREATE TABLE `product_branches` (
  `product_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_branches`
--

INSERT INTO `product_branches` (`product_id`, `branch_id`) VALUES
(1, 2),
(3, 3),
(4, 3),
(5, 2),
(6, 3),
(7, 2),
(7, 3),
(8, 2),
(8, 3),
(9, 2),
(10, 3),
(11, 2),
(16, 2),
(16, 3),
(17, 2),
(17, 3),
(18, 3),
(19, 2),
(20, 2),
(20, 3);

-- --------------------------------------------------------

--
-- Table structure for table `product_moderation_flags`
--

CREATE TABLE `product_moderation_flags` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `flag_type` enum('keyword','image') NOT NULL DEFAULT 'keyword',
  `matched_keywords` text NOT NULL,
  `status` enum('pending','approved','rejected','superseded') NOT NULL DEFAULT 'pending',
  `reviewer_id` int(10) UNSIGNED DEFAULT NULL,
  `review_note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_moderation_flags`
--

INSERT INTO `product_moderation_flags` (`id`, `product_id`, `flag_type`, `matched_keywords`, `status`, `reviewer_id`, `review_note`, `created_at`, `resolved_at`) VALUES
(1, 1, 'image', 'New product image requires manual review', 'approved', 1, NULL, '2026-08-14 09:59:09', '2026-08-15 03:43:12'),
(3, 3, 'image', 'New product image requires manual review', 'approved', 1, NULL, '2026-08-15 03:40:53', '2026-08-15 03:43:13'),
(5, 5, 'image', 'New product image requires manual review', 'approved', 1, NULL, '2026-08-15 19:07:50', '2026-08-23 05:40:16'),
(6, 6, 'image', 'New product image requires manual review', 'approved', 1, NULL, '2026-08-15 19:11:06', '2026-08-23 05:40:16'),
(7, 7, 'image', 'New product image requires manual review', 'approved', 1, NULL, '2026-08-23 05:44:21', '2026-08-23 05:44:32'),
(8, 8, 'image', 'New product image requires manual review', 'approved', 1, NULL, '2026-08-23 10:47:06', '2026-08-23 10:47:39'),
(9, 9, 'image', 'New product image requires manual review', 'rejected', 1, 'wala to', '2026-08-23 10:49:20', '2026-08-23 10:49:29'),
(12, 16, 'image', 'New product image requires manual review', 'approved', 1, NULL, '2026-08-23 11:05:11', '2026-08-23 11:08:21'),
(13, 17, 'image', 'New product image requires manual review', 'approved', 1, NULL, '2026-08-23 11:08:42', '2026-08-23 11:09:03'),
(14, 18, 'image', 'New product image requires manual review', 'approved', 1, NULL, '2026-08-23 11:09:24', '2026-08-23 11:09:30'),
(15, 1, 'image', 'New product image requires manual review', 'approved', 1, NULL, '2026-08-24 06:02:24', '2026-08-24 06:02:42'),
(16, 1, 'image', 'New product image requires manual review', 'approved', 1, NULL, '2026-08-24 06:04:34', '2026-08-24 06:04:42'),
(17, 1, 'image', 'New product image requires manual review', 'approved', 1, NULL, '2026-08-24 06:05:18', '2026-08-24 06:05:22'),
(18, 19, 'image', 'New product image requires manual review', 'approved', 1, NULL, '2026-08-24 10:12:14', '2026-08-24 10:12:35'),
(19, 20, 'image', 'New product image requires manual review', 'approved', 1, NULL, '2026-08-24 10:48:31', '2026-08-24 10:48:43'),
(20, 21, 'image', 'New product image requires manual review', 'pending', NULL, NULL, '2026-08-29 14:58:01', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_reviews`
--

CREATE TABLE `product_reviews` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_item_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `customer_id` int(10) UNSIGNED NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL,
  `fit_feedback` enum('too_small','true_to_size','too_large') NOT NULL,
  `comment` text DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ;

--
-- Dumping data for table `product_reviews`
--

INSERT INTO `product_reviews` (`id`, `order_item_id`, `product_id`, `customer_id`, `rating`, `fit_feedback`, `comment`, `photo_path`, `created_at`) VALUES
(1, 1, 1, 4, 5, 'true_to_size', 'good choice', NULL, '2026-08-16 01:38:44');

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `size` varchar(30) NOT NULL,
  `color` varchar(50) NOT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `stock` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`id`, `product_id`, `size`, `color`, `sku`, `stock`, `created_at`) VALUES
(6, 21, 'Small', 'red', NULL, 22, '2026-08-29 14:58:01'),
(7, 22, 'Small', 'Red', NULL, 23, '2026-08-29 14:58:23'),
(8, 23, 'Medium', 'red', NULL, 23, '2026-08-29 14:59:08'),
(9, 24, 'Medium', 'red', NULL, 23, '2026-08-29 14:59:40'),
(10, 28, 'XL', 'blue', NULL, 10, '2026-08-29 15:02:55');

-- --------------------------------------------------------

--
-- Table structure for table `prohibited_items`
--

CREATE TABLE `prohibited_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `prohibited_items`
--

INSERT INTO `prohibited_items` (`id`, `item_name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'firearm', 'Firearms and weapon listings are prohibited.', '2026-08-09 09:51:10', '2026-08-09 09:51:10'),
(2, 'pistol', 'Firearms and weapon listings are prohibited.', '2026-08-09 09:51:10', '2026-08-09 09:51:10'),
(3, 'rifle', 'Firearms and weapon listings are prohibited.', '2026-08-09 09:51:10', '2026-08-09 09:51:10'),
(4, 'shotgun', 'Firearms and weapon listings are prohibited.', '2026-08-09 09:51:10', '2026-08-09 09:51:10'),
(5, 'ammunition', 'Ammunition is prohibited.', '2026-08-09 09:51:10', '2026-08-09 09:51:10'),
(6, 'explosive', 'Explosive materials are prohibited.', '2026-08-09 09:51:10', '2026-08-09 09:51:10'),
(7, 'bomb', 'Explosive devices are prohibited.', '2026-08-09 09:51:10', '2026-08-09 09:51:10'),
(8, 'grenade', 'Explosive devices are prohibited.', '2026-08-09 09:51:10', '2026-08-09 09:51:10'),
(9, 'illegal drugs', 'Illegal or controlled drugs are prohibited.', '2026-08-09 09:51:10', '2026-08-09 09:51:10'),
(10, 'narcotics', 'Illegal or controlled drugs are prohibited.', '2026-08-09 09:51:10', '2026-08-09 09:51:10'),
(11, 'counterfeit', 'Counterfeit goods are prohibited.', '2026-08-09 09:51:10', '2026-08-09 09:51:10'),
(12, 'fake brand', 'Counterfeit goods are prohibited.', '2026-08-09 09:51:10', '2026-08-09 09:51:10'),
(13, 'stolen goods', 'Stolen goods are prohibited.', '2026-08-09 09:51:10', '2026-08-09 09:51:10'),
(14, 'hazardous chemicals', 'Hazardous chemicals are prohibited.', '2026-08-09 09:51:10', '2026-08-09 09:51:10'),
(15, 'poison', 'Poisons are prohibited.', '2026-08-09 09:51:11', '2026-08-09 09:51:11'),
(16, 'pornographic content', 'Adult content is prohibited.', '2026-08-09 09:51:11', '2026-08-09 09:51:11');

-- --------------------------------------------------------

--
-- Table structure for table `restock_notifications`
--

CREATE TABLE `restock_notifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `customer_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `variant_id` int(10) UNSIGNED DEFAULT NULL,
  `notified_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `restock_notifications`
--

INSERT INTO `restock_notifications` (`id`, `customer_id`, `product_id`, `variant_id`, `notified_at`, `created_at`) VALUES
(1, 4, 6, NULL, NULL, '2026-08-23 10:33:19'),
(2, 4, 27, NULL, NULL, '2026-08-29 15:26:13');

-- --------------------------------------------------------

--
-- Table structure for table `return_requests`
--

CREATE TABLE `return_requests` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_item_id` int(10) UNSIGNED NOT NULL,
  `customer_id` int(10) UNSIGNED NOT NULL,
  `reason` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `status` enum('requested','approved','rejected','refunded') NOT NULL DEFAULT 'requested',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `return_requests`
--

INSERT INTO `return_requests` (`id`, `order_item_id`, `customer_id`, `reason`, `details`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 4, 'wrong_size', NULL, 'approved', '2026-08-15 13:03:42', '2026-08-15 22:52:07'),
(2, 9, 4, 'damaged', NULL, 'rejected', '2026-08-28 13:30:59', '2026-08-28 13:31:22'),
(3, 10, 4, 'damaged', NULL, 'requested', '2026-08-28 13:32:30', '2026-08-28 13:32:30');

-- --------------------------------------------------------

--
-- Table structure for table `seller_applications`
--

CREATE TABLE `seller_applications` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `application_role` enum('admin','supplier') NOT NULL DEFAULT 'admin',
  `business_name` varchar(150) NOT NULL,
  `business_description` text NOT NULL,
  `phone` varchar(30) NOT NULL,
  `business_address` varchar(255) NOT NULL,
  `logo_path` varchar(255) NOT NULL,
  `document_path` varchar(255) NOT NULL,
  `selfie_path` varchar(255) DEFAULT NULL,
  `verification_status` enum('pending_review','verified','rejected') NOT NULL DEFAULT 'pending_review',
  `review_notes` text DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `seller_applications`
--

INSERT INTO `seller_applications` (`id`, `user_id`, `application_role`, `business_name`, `business_description`, `phone`, `business_address`, `logo_path`, `document_path`, `selfie_path`, `verification_status`, `review_notes`, `reviewed_at`, `created_at`) VALUES
(1, 2, 'supplier', 'arigato', 'clothing brand', '12345678901', 'anabu imus cavite', '', 'verify_6a78583c5de8f7.65052200.jpg', 'verify_6a78583c60a565.70923395.jpg', 'verified', NULL, '2026-08-09 18:38:51', '2026-08-09 10:36:44'),
(2, 3, 'admin', 'mahiwaga', 'clothing', '111234678007', 'imus cavite', '', 'verify_6a785a49c87ee2.11395016.png', 'verify_6a785a49ca30e5.57177899.jpg', 'verified', NULL, '2026-08-09 18:45:50', '2026-08-09 10:45:29'),
(3, 5, 'admin', 'walalang', 'clothes', '12345678998', 'anabu cavite', '', 'verify_6a80b0f9e44a20.74927033.jpg', 'verify_6a80b0f9e49950.36970213.jpg', 'verified', NULL, '2026-08-21 00:39:37', '2026-08-15 18:33:29');

-- --------------------------------------------------------

--
-- Table structure for table `seller_inventory_transfers`
--

CREATE TABLE `seller_inventory_transfers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `seller_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `variant_size` varchar(120) NOT NULL DEFAULT '',
  `variant_color` varchar(120) NOT NULL DEFAULT '',
  `quantity` int(10) UNSIGNED NOT NULL,
  `direction` enum('inventory_to_pos','pos_to_inventory') NOT NULL,
  `performed_by_user_id` int(10) UNSIGNED NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `seller_inventory_transfers`
--

INSERT INTO `seller_inventory_transfers` (`id`, `seller_id`, `product_id`, `variant_size`, `variant_color`, `quantity`, `direction`, `performed_by_user_id`, `note`, `created_at`) VALUES
(1, 2, 20, '', '', 9, 'inventory_to_pos', 2, NULL, '2026-08-29 13:26:42'),
(2, 2, 20, '', '', 5, 'pos_to_inventory', 2, NULL, '2026-08-29 13:42:11'),
(3, 2, 20, '', '', 5, 'inventory_to_pos', 2, NULL, '2026-08-29 14:48:21'),
(4, 2, 21, '', '', 22, 'inventory_to_pos', 2, 'Created POS listing from Seller Inventory product #16', '2026-08-29 14:58:01'),
(5, 2, 26, '', '', 2, 'inventory_to_pos', 2, NULL, '2026-08-30 14:54:25');

-- --------------------------------------------------------

--
-- Table structure for table `seller_pos_stock`
--

CREATE TABLE `seller_pos_stock` (
  `seller_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `variant_size` varchar(120) NOT NULL DEFAULT '',
  `variant_color` varchar(120) NOT NULL DEFAULT '',
  `stock` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `seller_pos_stock`
--

INSERT INTO `seller_pos_stock` (`seller_id`, `product_id`, `variant_size`, `variant_color`, `stock`, `updated_at`) VALUES
(2, 20, '', '', 9, '2026-08-29 14:48:21'),
(2, 21, '', '', 22, '2026-08-29 14:58:01'),
(2, 26, '', '', 2, '2026-08-30 14:54:25');

-- --------------------------------------------------------

--
-- Table structure for table `shipping_addresses`
--

CREATE TABLE `shipping_addresses` (
  `id` int(10) UNSIGNED NOT NULL,
  `customer_id` int(10) UNSIGNED NOT NULL,
  `recipient_name` varchar(150) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `address_line1` varchar(255) NOT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `barangay` varchar(150) NOT NULL,
  `city` varchar(150) NOT NULL,
  `province` varchar(150) NOT NULL,
  `postal_code` varchar(12) NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shipping_addresses`
--

INSERT INTO `shipping_addresses` (`id`, `customer_id`, `recipient_name`, `phone`, `address_line1`, `address_line2`, `barangay`, `city`, `province`, `postal_code`, `is_default`, `created_at`) VALUES
(1, 4, 'vixen montiman', '09604734765', 'anabu', '', 'anabu1', 'imus', 'cavite', '4103', 1, '2026-08-21 08:15:28'),
(2, 7, '123', '09604734765', '123', '123', '123', '123', '13', '123', 1, '2026-08-24 10:14:54');

-- --------------------------------------------------------

--
-- Table structure for table `site_content`
--

CREATE TABLE `site_content` (
  `id` int(10) UNSIGNED NOT NULL,
  `type` enum('banner','announcement','site_text') NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff_profiles`
--

CREATE TABLE `staff_profiles` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `seller_id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED DEFAULT NULL,
  `created_by_manager_id` int(10) UNSIGNED DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `position` enum('cashier','inventory_staff','order_staff','customer_service','branch_manager') NOT NULL DEFAULT 'cashier',
  `profile_picture` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff_profiles`
--

INSERT INTO `staff_profiles` (`id`, `user_id`, `seller_id`, `branch_id`, `created_by_manager_id`, `first_name`, `last_name`, `phone`, `position`, `profile_picture`, `status`, `is_archived`, `created_at`) VALUES
(1, 15, 2, 2, NULL, 'montiman', 'vixen', '+631231312313', 'branch_manager', NULL, 'active', 0, '2026-08-25 10:49:48'),
(2, 16, 2, 2, 15, 'vixen', 'vixen', '12345678901', 'order_staff', NULL, 'active', 0, '2026-08-26 10:35:46'),
(3, 17, 2, 2, 15, 'sheynk', 'vixen', '12345678901', 'customer_service', NULL, 'active', 0, '2026-08-26 11:01:01'),
(4, 18, 2, 2, 15, 'cashier', 'to', '12345678901', 'cashier', NULL, 'active', 0, '2026-08-26 11:04:39'),
(5, 19, 2, 2, 15, 'inventory', 'to', '12345678901', 'inventory_staff', NULL, 'active', 0, '2026-08-26 11:05:18'),
(6, 20, 2, 2, 15, '123123', '1231231', '+631231232131', 'order_staff', NULL, 'active', 0, '2026-08-27 23:32:28'),
(7, 21, 2, 2, 15, '123', 'sdasd', '+639123231231', 'cashier', NULL, 'active', 0, '2026-08-31 08:18:19');

-- --------------------------------------------------------

--
-- Table structure for table `stock_requests`
--

CREATE TABLE `stock_requests` (
  `id` int(10) UNSIGNED NOT NULL,
  `seller_id` int(10) UNSIGNED NOT NULL,
  `supplier_id` int(10) UNSIGNED NOT NULL,
  `supply_id` int(10) UNSIGNED DEFAULT NULL,
  `item_name` varchar(255) NOT NULL,
  `quantity_requested` int(10) UNSIGNED NOT NULL,
  `note` text DEFAULT NULL,
  `status` enum('pending','fulfilled','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_requests`
--

INSERT INTO `stock_requests` (`id`, `seller_id`, `supplier_id`, `supply_id`, `item_name`, `quantity_requested`, `note`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 3, NULL, 'clothes', 20, NULL, 'fulfilled', '2026-08-09 10:50:32', '2026-08-09 10:50:47'),
(2, 2, 3, 1, 'clothes', 46, NULL, 'fulfilled', '2026-08-11 08:55:26', '2026-08-11 08:55:36'),
(3, 2, 3, 1, 'clothes', 20, NULL, 'fulfilled', '2026-08-11 08:58:16', '2026-08-11 08:58:22'),
(4, 2, 3, 1, 'clothes', 20, NULL, 'fulfilled', '2026-08-11 08:59:35', '2026-08-11 08:59:44'),
(5, 2, 3, 1, 'clothes', 20, NULL, 'fulfilled', '2026-08-11 09:04:28', '2026-08-11 09:06:08'),
(6, 2, 3, 1, 'clothes', 20, NULL, 'fulfilled', '2026-08-11 09:09:36', '2026-08-11 09:09:42'),
(7, 2, 3, 1, 'clothes', 20, NULL, 'fulfilled', '2026-08-11 09:14:35', '2026-08-11 09:14:52'),
(8, 2, 3, 1, 'clothes', 100, NULL, 'fulfilled', '2026-08-23 10:48:13', '2026-08-23 10:48:49'),
(9, 2, 3, 3, '123', 26, NULL, 'fulfilled', '2026-08-23 10:54:57', '2026-08-23 10:55:05'),
(10, 2, 3, 3, '123', 22, NULL, 'fulfilled', '2026-08-23 10:56:47', '2026-08-23 10:56:50'),
(11, 2, 3, 3, '123', 54, NULL, 'fulfilled', '2026-08-24 06:01:50', '2026-08-24 10:11:35'),
(12, 2, 3, 1, 'clothes', 40, NULL, 'fulfilled', '2026-08-24 10:47:53', '2026-08-24 10:47:59');

-- --------------------------------------------------------

--
-- Table structure for table `supplier_inventory`
--

CREATE TABLE `supplier_inventory` (
  `id` int(10) UNSIGNED NOT NULL,
  `supplier_id` int(10) UNSIGNED NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `unit` varchar(50) NOT NULL DEFAULT 'piece',
  `unit_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `quantity_available` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `supplier_inventory`
--

INSERT INTO `supplier_inventory` (`id`, `supplier_id`, `item_name`, `description`, `unit`, `unit_price`, `quantity_available`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 3, 'clothes', '', 'piece', 200.00, 0, 1, '2026-08-11 08:54:50', '2026-08-24 10:47:59'),
(2, 3, 'pants', '', 'piece', 100.00, 300, 1, '2026-08-15 04:55:11', '2026-08-15 04:55:11'),
(3, 3, '123', '', 'piece', 22.00, 0, 1, '2026-08-23 10:53:52', '2026-08-24 10:11:35');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `commission_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `payment_methods` varchar(255) NOT NULL DEFAULT 'cash,gcash,card,paymongo,other',
  `shipping_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `free_shipping_threshold` decimal(10,2) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `commission_rate`, `payment_methods`, `shipping_fee`, `free_shipping_threshold`, `updated_at`) VALUES
(1, 0.00, 'cash,gcash,card,other', 0.00, NULL, '2026-08-09 10:13:46');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('superadmin','admin','supplier','customer','staff','manager') NOT NULL,
  `status` enum('pending','approved','suspended','banned') NOT NULL DEFAULT 'approved',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'System Administrator', 'admin@tinda.local', '$2y$12$yfisDez88ZJcS3dTzGUFtux4xIFh.kbdORN2AnkV57hA9kwQ1vs52', 'superadmin', 'approved', '2026-08-09 10:06:08', '2026-08-09 10:06:08'),
(2, 'Vixen Montiman', 'montimanvixen420@gmail.com', '$2y$12$GMkdRisb2lyO/NQ1N9P.hu0jokSz9ymPOmqN5nA0E4XF6MHaCVeXe', 'admin', 'approved', '2026-08-09 10:08:29', '2026-08-15 14:15:58'),
(3, 'supplier123', 'alapanan01@gmail.com', '$2y$12$zuux5y21ItbsQrPp5z7Rw./DlPhTjl722yq1eiZA1dXlhsRRWHXkC', 'supplier', 'approved', '2026-08-09 10:16:06', '2026-08-15 14:15:51'),
(4, 'customer', 'alapanan02@gmail.com', '$2y$12$EoOeHJDaQHDx1VNOrbXcaemTWPC86JDW50oLIM3ZruVjEOVJEYYLG', 'customer', 'approved', '2026-08-10 09:39:22', '2026-08-15 13:16:07'),
(5, 'phantom', '123@gmail.com', '$2y$12$fx.YYmN.x0gqdiYDX.OzyOaNZ3li19VkGCuuJmoRb/ZMBmQsvlsmS', 'admin', 'approved', '2026-08-15 13:28:29', '2026-08-20 16:39:37'),
(6, '123', '123456789@gmail.com', '$2y$12$ecOgGBxHYr93xokpETtq0Ojm3In9jmgWNUiBKMz9vNx79duIjVimC', 'customer', 'approved', '2026-08-15 13:34:34', '2026-08-15 13:34:34'),
(7, 'phantom', 'phantom@gmail.com', '$2y$12$q6sNtW/byBHOVfxaKzoHHuiGoeFJBbc/HPsnBybF5ICrUc7f81vE2', 'customer', 'approved', '2026-08-20 16:41:44', '2026-08-20 16:41:44'),
(8, 'montiman cici', 'phantom1234@gmail.com', '$2y$12$8XCEHf.Y94jzRQqS.5n3J.O9yYK.lzc.JNnGaPXTzphuzouFQNia.', '', 'approved', '2026-08-21 08:07:19', '2026-08-21 08:07:19'),
(15, 'montiman vixen', 'branchmanager@test.com', '$2y$12$tmx4tWS0MMWmLr.qGhE/h.QqgdEN1gm98gpJ2uX6QFZx7Ya/JyaGC', 'manager', 'approved', '2026-08-25 10:49:48', '2026-08-26 02:03:45'),
(16, 'vixen vixen', 'orderstaff@gmail.com', '$2y$12$Q80YTDyHlRU1sdDS81nhbua0ofDy7nh.hQQWwaezEN0UUd/O00bn6', 'staff', 'approved', '2026-08-26 10:35:46', '2026-08-26 10:35:46'),
(17, 'sheynk vixen', 'customerstaff@gmail.com', '$2y$12$Tdsg1SSNaeqWIQIDn88xreY/KSQmPMw9M8micH6f3WcuNnAS7V6P6', 'staff', 'approved', '2026-08-26 11:01:01', '2026-08-26 11:01:01'),
(18, 'cashier to', 'cashier@gmail.com', '$2y$12$.AW2lpjbEd20qZol7VfwYe4PjgPQzObaIjGGyQwjsjftks720wcLW', 'staff', 'approved', '2026-08-26 11:04:39', '2026-08-26 11:04:39'),
(19, 'inventory to', 'inventorystaff@gmail.com', '$2y$12$03fhdZ5I3br2jMOw/Vyld.Ul9/13w0EhiSvijRRaV/HfEVpmZ0O5G', 'staff', 'approved', '2026-08-26 11:05:18', '2026-08-26 11:05:18'),
(20, '123123 1231231', 'wala@gmail.com', '$2y$12$4KgpxmziG/UefCIyDTo/m.Iq//JhLDIZmdm0FIaQjyrozft8eSceq', 'staff', 'approved', '2026-08-27 23:32:28', '2026-08-27 23:32:28'),
(21, '123 sdasd', 'cashier1@gmail.com', '$2y$12$Ww9lWKM2cX7PEBk5g/4eu.cirXgaXufgKS0nLajage7uTljdlAza6', 'staff', 'approved', '2026-08-31 08:18:19', '2026-08-31 08:18:19');

-- --------------------------------------------------------

--
-- Table structure for table `vouchers`
--

CREATE TABLE `vouchers` (
  `id` int(10) UNSIGNED NOT NULL,
  `seller_id` int(10) UNSIGNED NOT NULL,
  `code` varchar(40) NOT NULL,
  `discount_type` enum('fixed','percent','free_shipping') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL DEFAULT 0.00,
  `minimum_order` decimal(10,2) NOT NULL DEFAULT 0.00,
  `maximum_discount` decimal(10,2) DEFAULT NULL,
  `starts_at` datetime DEFAULT NULL,
  `ends_at` datetime DEFAULT NULL,
  `usage_limit` int(10) UNSIGNED DEFAULT NULL,
  `times_used` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wishlist_items`
--

CREATE TABLE `wishlist_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `customer_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `notes` text DEFAULT NULL,
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wishlist_items`
--

INSERT INTO `wishlist_items` (`id`, `customer_id`, `product_id`, `notes`, `priority`, `created_at`, `updated_at`) VALUES
(30, 4, 19, NULL, 'medium', '2026-08-26 09:52:45', '2026-08-26 09:52:45');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `branch_code` (`branch_code`),
  ADD KEY `fk_branches_seller` (`seller_id`),
  ADD KEY `idx_branches_map` (`is_active`,`latitude`,`longitude`);

--
-- Indexes for table `branch_damage_reports`
--
ALTER TABLE `branch_damage_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_damage_report_branch_status` (`branch_id`,`status`),
  ADD KEY `fk_damage_report_product` (`product_id`),
  ADD KEY `fk_damage_report_reporter` (`reported_by_user_id`),
  ADD KEY `fk_damage_report_reviewer` (`reviewed_by_user_id`);

--
-- Indexes for table `branch_inventory_transfers`
--
ALTER TABLE `branch_inventory_transfers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_branch_transfer_branch` (`branch_id`,`created_at`),
  ADD KEY `fk_branch_transfer_product` (`product_id`),
  ADD KEY `fk_branch_transfer_user` (`performed_by_user_id`);

--
-- Indexes for table `branch_managers`
--
ALTER TABLE `branch_managers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_manager_user` (`user_id`),
  ADD UNIQUE KEY `uniq_branch_manager` (`branch_id`),
  ADD KEY `idx_manager_seller` (`seller_id`);

--
-- Indexes for table `branch_pos_stock`
--
ALTER TABLE `branch_pos_stock`
  ADD PRIMARY KEY (`branch_id`,`product_id`,`variant_size`,`variant_color`),
  ADD KEY `fk_branch_pos_stock_product` (`product_id`);

--
-- Indexes for table `branch_staff`
--
ALTER TABLE `branch_staff`
  ADD PRIMARY KEY (`branch_id`,`user_id`),
  ADD KEY `fk_branch_staff_user` (`user_id`);

--
-- Indexes for table `branch_stock`
--
ALTER TABLE `branch_stock`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_branch_stock` (`product_id`,`variant_size`,`variant_color`,`branch_id`),
  ADD KEY `idx_bs_branch` (`branch_id`);

--
-- Indexes for table `branch_stock_adjustments`
--
ALTER TABLE `branch_stock_adjustments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_bsa_product` (`product_id`),
  ADD KEY `idx_bsa_branch` (`branch_id`),
  ADD KEY `idx_bsa_created` (`created_at`);

--
-- Indexes for table `branch_stock_allocations`
--
ALTER TABLE `branch_stock_allocations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_allocation_branch_stock` (`branch_id`,`product_id`,`variant_size`,`variant_color`),
  ADD KEY `idx_allocation_seller` (`seller_id`,`created_at`),
  ADD KEY `fk_allocation_product` (`product_id`),
  ADD KEY `fk_allocation_user` (`allocated_by_user_id`);

--
-- Indexes for table `customer_reports`
--
ALTER TABLE `customer_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_customer_reports_status` (`status`),
  ADD KEY `idx_customer_reports_target` (`target_type`,`target_id`),
  ADD KEY `fk_customer_reports_reporter` (`reporter_id`),
  ADD KEY `fk_customer_reports_reviewer` (`reviewer_id`);

--
-- Indexes for table `damaged_products`
--
ALTER TABLE `damaged_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_damage_seller_created` (`seller_id`,`created_at`),
  ADD KEY `idx_damage_branch_created` (`branch_id`,`created_at`),
  ADD KEY `fk_damage_product` (`product_id`),
  ADD KEY `fk_damage_user` (`recorded_by_user_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_feedback_user` (`user_id`),
  ADD KEY `idx_feedback_status_created` (`status`,`created_at`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_orders_seller` (`seller_id`),
  ADD KEY `idx_orders_customer` (`customer_id`),
  ADD KEY `idx_orders_status` (`status`),
  ADD KEY `fk_orders_shipping_address` (`shipping_address_id`),
  ADD KEY `idx_orders_branch` (`branch_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_items_order` (`order_id`),
  ADD KEY `idx_order_items_product` (`product_id`),
  ADD KEY `fk_order_items_variant` (`variant_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token_hash` (`token_hash`),
  ADD KEY `idx_password_resets_user` (`user_id`),
  ADD KEY `idx_password_resets_expiry` (`expires_at`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_products_stock_request` (`stock_request_id`),
  ADD KEY `idx_products_seller` (`seller_id`),
  ADD KEY `idx_products_status` (`status`),
  ADD KEY `idx_products_inventory_source` (`inventory_source_product_id`);

--
-- Indexes for table `product_branches`
--
ALTER TABLE `product_branches`
  ADD PRIMARY KEY (`product_id`,`branch_id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_branch_id` (`branch_id`);

--
-- Indexes for table `product_moderation_flags`
--
ALTER TABLE `product_moderation_flags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_moderation_product` (`product_id`),
  ADD KEY `fk_moderation_reviewer` (`reviewer_id`),
  ADD KEY `idx_moderation_queue` (`status`,`flag_type`,`created_at`);

--
-- Indexes for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_review_per_item` (`order_item_id`),
  ADD KEY `fk_review_product` (`product_id`),
  ADD KEY `fk_review_customer` (`customer_id`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_product_variant` (`product_id`,`size`,`color`);

--
-- Indexes for table `prohibited_items`
--
ALTER TABLE `prohibited_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_prohibited_item_name` (`item_name`);

--
-- Indexes for table `restock_notifications`
--
ALTER TABLE `restock_notifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_restock_subscription` (`customer_id`,`product_id`,`variant_id`),
  ADD KEY `fk_restock_product` (`product_id`),
  ADD KEY `fk_restock_variant` (`variant_id`);

--
-- Indexes for table `return_requests`
--
ALTER TABLE `return_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_return_per_item` (`order_item_id`),
  ADD KEY `fk_return_customer` (`customer_id`);

--
-- Indexes for table `seller_applications`
--
ALTER TABLE `seller_applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `seller_inventory_transfers`
--
ALTER TABLE `seller_inventory_transfers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_inventory_transfer_seller` (`seller_id`,`created_at`),
  ADD KEY `fk_inventory_transfer_product` (`product_id`),
  ADD KEY `fk_inventory_transfer_user` (`performed_by_user_id`);

--
-- Indexes for table `seller_pos_stock`
--
ALTER TABLE `seller_pos_stock`
  ADD PRIMARY KEY (`seller_id`,`product_id`,`variant_size`,`variant_color`),
  ADD KEY `fk_seller_pos_stock_product` (`product_id`);

--
-- Indexes for table `shipping_addresses`
--
ALTER TABLE `shipping_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_shipping_addresses_customer` (`customer_id`);

--
-- Indexes for table `site_content`
--
ALTER TABLE `site_content`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_site_content_active_type` (`is_active`,`type`);

--
-- Indexes for table `staff_profiles`
--
ALTER TABLE `staff_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_staff_user` (`user_id`),
  ADD KEY `idx_staff_seller` (`seller_id`),
  ADD KEY `idx_staff_branch` (`branch_id`),
  ADD KEY `idx_staff_creator` (`created_by_manager_id`);

--
-- Indexes for table `stock_requests`
--
ALTER TABLE `stock_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_stock_requests_seller_status` (`seller_id`,`status`),
  ADD KEY `idx_stock_requests_supplier_status` (`supplier_id`,`status`),
  ADD KEY `fk_stock_request_supply` (`supply_id`);

--
-- Indexes for table `supplier_inventory`
--
ALTER TABLE `supplier_inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_supplier_inventory_available` (`supplier_id`,`is_active`,`quantity_available`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_seller_voucher_code` (`seller_id`,`code`);

--
-- Indexes for table `wishlist_items`
--
ALTER TABLE `wishlist_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_customer_product` (`customer_id`,`product_id`),
  ADD KEY `fk_wishlist_product` (`product_id`),
  ADD KEY `idx_wishlist_customer` (`customer_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `branch_damage_reports`
--
ALTER TABLE `branch_damage_reports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `branch_inventory_transfers`
--
ALTER TABLE `branch_inventory_transfers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `branch_managers`
--
ALTER TABLE `branch_managers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `branch_stock`
--
ALTER TABLE `branch_stock`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `branch_stock_adjustments`
--
ALTER TABLE `branch_stock_adjustments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `branch_stock_allocations`
--
ALTER TABLE `branch_stock_allocations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `customer_reports`
--
ALTER TABLE `customer_reports`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `damaged_products`
--
ALTER TABLE `damaged_products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `product_moderation_flags`
--
ALTER TABLE `product_moderation_flags`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `prohibited_items`
--
ALTER TABLE `prohibited_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `restock_notifications`
--
ALTER TABLE `restock_notifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `return_requests`
--
ALTER TABLE `return_requests`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `seller_applications`
--
ALTER TABLE `seller_applications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `seller_inventory_transfers`
--
ALTER TABLE `seller_inventory_transfers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `shipping_addresses`
--
ALTER TABLE `shipping_addresses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `site_content`
--
ALTER TABLE `site_content`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `staff_profiles`
--
ALTER TABLE `staff_profiles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `stock_requests`
--
ALTER TABLE `stock_requests`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `supplier_inventory`
--
ALTER TABLE `supplier_inventory`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wishlist_items`
--
ALTER TABLE `wishlist_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `branches`
--
ALTER TABLE `branches`
  ADD CONSTRAINT `fk_branches_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `branch_damage_reports`
--
ALTER TABLE `branch_damage_reports`
  ADD CONSTRAINT `fk_damage_report_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_damage_report_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `fk_damage_report_reporter` FOREIGN KEY (`reported_by_user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_damage_report_reviewer` FOREIGN KEY (`reviewed_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `branch_inventory_transfers`
--
ALTER TABLE `branch_inventory_transfers`
  ADD CONSTRAINT `fk_branch_transfer_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_branch_transfer_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `fk_branch_transfer_user` FOREIGN KEY (`performed_by_user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `branch_managers`
--
ALTER TABLE `branch_managers`
  ADD CONSTRAINT `fk_manager_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_manager_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_manager_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `branch_pos_stock`
--
ALTER TABLE `branch_pos_stock`
  ADD CONSTRAINT `fk_branch_pos_stock_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_branch_pos_stock_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `branch_staff`
--
ALTER TABLE `branch_staff`
  ADD CONSTRAINT `fk_branch_staff_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_branch_staff_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `branch_stock`
--
ALTER TABLE `branch_stock`
  ADD CONSTRAINT `fk_bs_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_bs_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `branch_stock_adjustments`
--
ALTER TABLE `branch_stock_adjustments`
  ADD CONSTRAINT `fk_bsa_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_bsa_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `branch_stock_allocations`
--
ALTER TABLE `branch_stock_allocations`
  ADD CONSTRAINT `fk_allocation_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `fk_allocation_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `fk_allocation_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_allocation_user` FOREIGN KEY (`allocated_by_user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `customer_reports`
--
ALTER TABLE `customer_reports`
  ADD CONSTRAINT `fk_customer_reports_reporter` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_customer_reports_reviewer` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `damaged_products`
--
ALTER TABLE `damaged_products`
  ADD CONSTRAINT `fk_damage_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `fk_damage_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `fk_damage_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_damage_user` FOREIGN KEY (`recorded_by_user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `fk_feedback_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_orders_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_orders_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_orders_shipping_address` FOREIGN KEY (`shipping_address_id`) REFERENCES `shipping_addresses` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_order_items_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `fk_password_resets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_inventory_source` FOREIGN KEY (`inventory_source_product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_products_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_products_stock_request` FOREIGN KEY (`stock_request_id`) REFERENCES `stock_requests` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_moderation_flags`
--
ALTER TABLE `product_moderation_flags`
  ADD CONSTRAINT `fk_moderation_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_moderation_reviewer` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD CONSTRAINT `fk_review_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_review_order_item` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_review_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `fk_product_variants_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `restock_notifications`
--
ALTER TABLE `restock_notifications`
  ADD CONSTRAINT `fk_restock_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_restock_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_restock_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `return_requests`
--
ALTER TABLE `return_requests`
  ADD CONSTRAINT `fk_return_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_return_order_item` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `seller_applications`
--
ALTER TABLE `seller_applications`
  ADD CONSTRAINT `fk_seller_applications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `seller_inventory_transfers`
--
ALTER TABLE `seller_inventory_transfers`
  ADD CONSTRAINT `fk_inventory_transfer_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `fk_inventory_transfer_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_inventory_transfer_user` FOREIGN KEY (`performed_by_user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `seller_pos_stock`
--
ALTER TABLE `seller_pos_stock`
  ADD CONSTRAINT `fk_seller_pos_stock_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_seller_pos_stock_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shipping_addresses`
--
ALTER TABLE `shipping_addresses`
  ADD CONSTRAINT `fk_shipping_addresses_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `staff_profiles`
--
ALTER TABLE `staff_profiles`
  ADD CONSTRAINT `fk_staff_profile_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `fk_staff_profile_manager` FOREIGN KEY (`created_by_manager_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_staff_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_staff_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_requests`
--
ALTER TABLE `stock_requests`
  ADD CONSTRAINT `fk_stock_request_supply` FOREIGN KEY (`supply_id`) REFERENCES `supplier_inventory` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_stock_requests_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_stock_requests_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `supplier_inventory`
--
ALTER TABLE `supplier_inventory`
  ADD CONSTRAINT `fk_supplier_inventory_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vouchers`
--
ALTER TABLE `vouchers`
  ADD CONSTRAINT `fk_vouchers_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist_items`
--
ALTER TABLE `wishlist_items`
  ADD CONSTRAINT `fk_wishlist_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_wishlist_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
