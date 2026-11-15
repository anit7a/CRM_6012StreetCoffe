-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 15, 2026 at 04:30 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `crm_6012`
--

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` int(10) UNSIGNED DEFAULT NULL,
  `complaint_date` datetime NOT NULL DEFAULT current_timestamp(),
  `category` enum('Keluhan','Saran') NOT NULL,
  `description` text NOT NULL,
  `pic` varchar(100) DEFAULT NULL,
  `status` enum('Baru','Diproses','Selesai') NOT NULL DEFAULT 'Baru',
  `response` text DEFAULT NULL,
  `responded_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`id`, `customer_id`, `complaint_date`, `category`, `description`, `pic`, `status`, `response`, `responded_at`) VALUES
(1, NULL, '2026-11-03 20:50:13', 'Keluhan', 'baik oke aman', 'nit', 'Selesai', 'baik, akan segeraa ditindaklanjuti', '2026-11-15 22:27:51');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `join_date` date NOT NULL DEFAULT curdate(),
  `member_status` enum('Member','Non-member') NOT NULL DEFAULT 'Member',
  `points` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `phone`, `address`, `join_date`, `member_status`, `points`, `created_at`) VALUES
(1, 'Andi Pratama', '081234560001', 'Yogyakarta', '2026-01-05', 'Member', 40, '2026-11-03 13:34:17'),
(2, 'Siti Rahma', '081234560002', 'Sleman', '2026-01-08', 'Member', 10, '2026-11-03 13:34:17'),
(3, 'Bima Saputra', '081234560003', 'Bantul', '2026-01-12', 'Non-member', 0, '2026-11-03 13:34:17'),
(4, 'Nadia Putri', '081234560004', 'Yogyakarta', '2026-02-01', 'Member', 15, '2026-11-03 13:34:17'),
(5, 'Rizky Maulana', '081234560005', 'Depok, Sleman', '2026-02-10', 'Member', 4, '2026-11-03 13:34:17'),
(6, 'Dewi Lestari', '081234560006', 'Kasihan, Bantul', '2026-02-14', 'Non-member', 0, '2026-11-03 13:34:17'),
(7, 'Fajar Nugroho', '081234560007', 'Yogyakarta', '2026-03-03', 'Member', 21, '2026-11-03 13:34:17'),
(8, 'Intan Permata', '081234560008', 'Mlati, Sleman', '2026-03-07', 'Member', 8, '2026-11-03 13:34:17'),
(9, 'Dimas Arya', '081234560009', 'Yogyakarta', '2026-03-15', 'Non-member', 0, '2026-11-03 13:34:17'),
(10, 'Aulia Safitri', '081234560010', 'Gamping, Sleman', '2026-03-20', 'Member', 10, '2026-11-03 13:34:17'),
(11, 'Yoga Firmansyah', '081234560011', 'Yogyakarta', '2026-04-02', 'Member', 3, '2026-11-03 13:34:17'),
(12, 'Citra Amelia', '081234560012', 'Bantul', '2026-04-09', 'Member', 5, '2026-11-03 13:34:17'),
(13, 'Rangga Wijaya', '081234560013', 'Yogyakarta', '2026-04-18', 'Non-member', 0, '2026-11-03 13:34:17'),
(14, 'Maya Anggraini', '081234560014', 'Sleman', '2026-05-01', 'Member', 11, '2026-11-03 13:34:17'),
(15, 'Arif Setiawan', '081234560015', 'Yogyakarta', '2026-05-12', 'Member', 2, '2026-11-03 13:34:17'),
(16, 'Laila Nurfadila', '081234560016', 'Bantul', '2026-05-20', 'Non-member', 4, '2026-11-03 13:34:17'),
(17, 'Kevin Alexander', '081234560017', 'Yogyakarta', '2026-06-02', 'Member', 34, '2026-11-03 13:34:17'),
(18, 'Nisa Khairunnisa', '081234560018', 'Sleman', '2026-06-11', 'Member', 7, '2026-11-03 13:34:17'),
(19, 'Bagas Ramadhan', '081234560019', 'Yogyakarta', '2026-06-22', 'Non-member', 4, '2026-11-03 13:34:17'),
(20, 'Putri Maharani', '081234560020', 'Kulon Progo', '2026-07-01', 'Member', 11, '2026-11-03 13:34:17'),
(21, 'anita', '0812345566', 'barsa', '2026-11-03', 'Member', 10, '2026-11-03 14:13:24'),
(22, 'Dira', '098248946', 'purwokerto', '2026-11-11', 'Member', 0, '2026-11-11 16:11:31');

-- --------------------------------------------------------

--
-- Table structure for table `menus`
--

CREATE TABLE `menus` (
  `id` int(10) UNSIGNED NOT NULL,
  `category` varchar(60) NOT NULL,
  `name` varchar(150) NOT NULL,
  `price` int(10) UNSIGNED NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menus`
--

INSERT INTO `menus` (`id`, `category`, `name`, `price`, `is_active`) VALUES
(1, 'Coffee Classics', 'Americano', 15000, 1),
(2, 'Coffee Classics', 'Iced White', 17000, 1),
(3, 'Coffee Classics', 'Dolce Latte', 17000, 1),
(4, 'Milkbased Coffee', 'Palm Sugar', 18000, 1),
(5, 'Milkbased Coffee', 'Caramel Latte', 18000, 1),
(6, 'Milkbased Coffee', 'Hazelnut', 20000, 1),
(7, 'Milkbased Coffee', 'Butterscotch', 20000, 1),
(8, 'Milkbased Coffee', 'Salted Caramel', 20000, 1),
(9, 'Milkbased Coffee', 'Cream Cheese', 21000, 1),
(10, 'Milkbased Coffee', 'Popcorn', 21000, 1),
(11, 'Milkbased Coffee', 'Baileys', 21000, 0),
(12, 'Milkbased Non Coffee', 'Coklat', 17000, 1),
(13, 'Milkbased Non Coffee', 'Redvelvet', 17000, 1),
(14, 'Milkbased Non Coffee', 'Regal Caramel', 18000, 1),
(15, 'Milkbased Non Coffee', 'Choco Caramel', 18000, 1),
(16, 'Milkbased Non Coffee', 'Matcha', 21000, 1),
(17, 'Milkbased Non Coffee', 'Cookies N Cream', 21000, 1),
(18, 'Milkbased Non Coffee', 'Baileys Choco', 21000, 1),
(19, 'Milkbased Non Coffee', 'Redvelvet Cheese Cream', 21000, 1),
(20, 'Milkbased Non Coffee', 'Strawberry Cheese Cream', 22000, 1),
(21, 'Yakult Series', 'Mango Yakult', 18000, 1),
(22, 'Yakult Series', 'Orange Yakult', 18000, 1),
(23, 'Yakult Series', 'Lychee Yakult', 18000, 1),
(24, 'Yakult Series', 'Lemon Yakult', 18000, 1),
(25, 'Americano Series', 'Am Lemon', 17000, 1),
(26, 'Americano Series', 'Am Lychee', 17000, 1),
(27, 'Americano Series', 'Am Orange', 18000, 1),
(28, 'Americano Series', 'Am Strawberry', 18000, 1),
(29, 'Additional', 'Extra Shoot', 3000, 1),
(30, 'Additional', 'Sprite', 3000, 1);

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` int(10) UNSIGNED DEFAULT NULL,
  `menu_name` varchar(150) NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL,
  `total_amount` bigint(20) UNSIGNED NOT NULL,
  `points_awarded` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `transaction_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `customer_id`, `menu_name`, `quantity`, `total_amount`, `points_awarded`, `transaction_date`) VALUES
(1, NULL, 'Kopi susu', 1, 19000, 1, '2026-11-03 20:48:48'),
(2, 21, 'Kopi Susu, Matcha, Chocolate(2)', 4, 81000, 8, '2026-11-03 21:15:26'),
(3, 1, 'Matcha', 1, 20000, 2, '2026-11-03 21:16:01'),
(4, 8, 'Americano  Ice', 1, 20000, 2, '2026-11-09 18:55:24'),
(6, 1, 'Latte', 1, 19000, 1, '2026-11-09 19:04:34'),
(7, 1, 'Matcha Latte(2), Americano, Kopi Susu Gula Aren, Chocolate Ice,', 5, 101000, 10, '2026-11-09 19:06:35'),
(8, 16, 'Americano  Ice', 2, 40000, 4, '2026-11-11 21:29:24'),
(9, 2, 'Latte', 1, 22000, 2, '2026-11-11 21:31:31'),
(10, 14, 'matcha latte', 1, 23000, 2, '2026-11-11 21:31:55'),
(11, 19, 'Americano  Ice, Red Velved Latte', 2, 43000, 4, '2026-11-11 21:32:50'),
(12, 1, 'Matcha, Red Velved', 2, 45000, 4, '2026-11-11 21:33:41'),
(13, 21, 'Chocolate Ice', 1, 22000, 2, '2026-11-11 21:34:33'),
(14, 17, 'Americano Ice Double Shot', 1, 25000, 2, '2026-11-11 21:39:10'),
(15, 17, 'Matcha Latte, Americano(4), Kopi Susu Gula Aren, Chocolate Ice(2), Red Velved(3)', 11, 148000, 14, '2026-11-11 23:09:37'),
(16, 1, 'Americano x1, Palm Sugar x1', 2, 33000, 3, '2026-11-15 22:22:57');

-- --------------------------------------------------------

--
-- Table structure for table `transaction_items`
--

CREATE TABLE `transaction_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `menu_name` varchar(150) NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL,
  `unit_price` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaction_items`
--

INSERT INTO `transaction_items` (`id`, `transaction_id`, `menu_name`, `quantity`, `unit_price`) VALUES
(1, 16, 'Americano', 1, 15000),
(2, 16, 'Palm Sugar', 1, 18000);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `password`, `created_at`) VALUES
(1, 'Admin', 'admin', '$2y$10$5cQzjpdrdAU4CBecu/kJWuJz8hVWYlNv7yuOPaXFNxvRCKsgSJWEu', '2026-11-03 13:42:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_complaint_customer` (`customer_id`),
  ADD KEY `idx_complaint_date` (`complaint_date`),
  ADD KEY `idx_complaint_status` (`status`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_customer_name` (`name`),
  ADD KEY `idx_customer_phone` (`phone`);

--
-- Indexes for table `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_transaction_date` (`transaction_date`),
  ADD KEY `idx_transaction_customer` (`customer_id`);

--
-- Indexes for table `transaction_items`
--
ALTER TABLE `transaction_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_transaction` (`transaction_id`);

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
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `menus`
--
ALTER TABLE `menus`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `transaction_items`
--
ALTER TABLE `transaction_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `fk_complaint_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_transaction_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
