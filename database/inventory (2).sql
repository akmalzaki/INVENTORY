-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 16, 2025 at 01:50 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `inventory`
--

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

CREATE TABLE `assets` (
  `id` int NOT NULL,
  `asset_name` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `stock` int NOT NULL,
  `asset_info_detail` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `img` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `asset_type` varchar(15) COLLATE utf8mb4_general_ci NOT NULL,
  `created_by` int NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assets`
--

INSERT INTO `assets` (`id`, `asset_name`, `stock`, `asset_info_detail`, `img`, `asset_type`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'PC', 10, 'PC office', '1747281961_cpu.jpg', 'slow moving', 1, '2024-10-28 00:00:00', '2025-05-15 11:10:44'),
(2, 'Kabel UTP', 27, 'jumlah = meter', '1747282018_lan.jpg', 'slow moving', 2, '2024-10-30 00:00:00', '2025-05-15 11:12:44'),
(3, 'Mouse', 10, 'Mouse office', '1747282052_mouse.jpg', 'slow moving', 2, '2024-10-31 00:00:00', '2025-05-15 11:11:25'),
(4, 'Roll Ticket', 43, 'Roll ticket TVM dan loket', '1747282085_roll tiket.jpg', 'fast moving', 1, '2024-11-12 00:00:00', '2025-05-15 11:11:08'),
(5, 'Keyboard', 10, 'Keyboard office', '1747281898_keyboard.jpg', 'slow moving', 1, '2025-01-13 00:00:00', '2025-05-15 11:13:09'),
(6, 'Dinamo', 10, 'Dinamo mesin tiket', '1747282127_dinamo.jpg', 'slow moving', 1, '2025-04-22 00:00:00', '2025-05-15 11:10:52'),
(7, 'Monitor', 20, 'monitor PIDS', '1747281934_monitor.jpg', 'slow moving', 1, '2025-05-03 00:00:00', '2025-05-15 11:21:11'),
(8, 'Thermal Paste', 2, 'Thermal paste CPU', 'thermal paste.jpg', 'slow moving', 1, '2025-05-15 00:00:00', '2025-05-15 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `checkout`
--

CREATE TABLE `checkout` (
  `id` int NOT NULL,
  `asset_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `quantity_received` int NOT NULL,
  `quantity_ordered` int NOT NULL,
  `quantity_remaining` int NOT NULL,
  `checkout_by` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `checkout_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `checkout`
--

INSERT INTO `checkout` (`id`, `asset_name`, `quantity_received`, `quantity_ordered`, `quantity_remaining`, `checkout_by`, `checkout_at`) VALUES
(31, 'PC', 1, 1, 7, 'staff 1', '2024-01-02 08:41:35'),
(37, 'Mouse', 4, 4, 15, 'staff 2', '2024-01-04 11:33:10'),
(38, 'Roll Ticket', 4, 4, 6, 'staff 2', '2024-02-08 14:14:21'),
(39, 'Monitor', 10, 10, 14, 'staff 2', '2024-03-05 09:24:00'),
(40, 'Keyboard', 5, 5, 6, 'staff 1', '2024-04-08 09:44:37'),
(41, 'Kabel UTP', 1, 1, 13, 'staff 1', '2024-04-16 11:14:02'),
(42, 'Roll Ticket', 10, 10, 3, 'staff 1', '2024-05-13 11:15:39'),
(43, 'Monitor', 1, 1, 14, 'staff 2', '2024-06-04 11:17:05'),
(44, 'Dinamo', 3, 3, 0, 'staff 1', '2024-07-16 11:17:51'),
(45, 'Kabel UTP', 5, 5, 25, 'staff 1', '2024-08-16 11:46:51'),
(46, 'PC', 2, 2, 23, 'staff 2', '2024-09-09 11:47:41'),
(47, 'Mouse', 4, 4, 19, 'staff 2', '2024-11-13 11:49:25'),
(48, 'Monitor', 7, 7, 12, 'staff 1', '2024-11-13 11:49:30'),
(49, 'Dinamo', 7, 7, 5, 'staff 2', '2024-11-13 11:49:34'),
(50, 'Roll Ticket', 5, 5, 0, 'staff 1', '2024-12-17 11:52:13'),
(51, 'Roll Ticket', 5, 5, 95, 'staff 1', '2023-01-04 12:03:37'),
(52, 'Roll Ticket', 10, 10, 85, 'staff 1', '2023-02-09 12:03:42'),
(53, 'Kabel UTP', 15, 15, 70, 'staff 1', '2023-03-08 12:03:46'),
(54, 'Roll Ticket', 12, 12, 58, 'staff 2', '2024-11-18 08:42:22'),
(55, 'Roll Ticket', 8, 8, 2, 'staff 1', '2025-04-17 17:25:02'),
(56, 'Dinamo', 11, 11, 10, 'staff 2', '2025-04-26 11:31:03'),
(57, 'Keyboard', 37, 37, 93, 'staff 2', '2025-05-03 09:50:55'),
(58, 'Thermal Paste', 1, 1, 1, 'staff 2', '2025-05-03 10:27:52');

-- --------------------------------------------------------

--
-- Table structure for table `stocks`
--

CREATE TABLE `stocks` (
  `id` int NOT NULL,
  `asset_id` int NOT NULL,
  `created_by` int DEFAULT NULL,
  `quantity` int DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `first_name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `last_name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` date NOT NULL,
  `role` enum('admin','staff','user') COLLATE utf8mb4_general_ci DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `password`, `email`, `created_at`, `role`) VALUES
(1, 'Admin', 'Akmal', '123456789', 'akmal@ims.com', '2024-10-22', 'admin'),
(2, 'Admin', 'Fatah', 'qwertyui', 'fatah@ims.com', '2024-10-24', 'admin'),
(3, 'user', '1', '1234', 'user1@ims.com', '2024-12-08', 'user'),
(4, 'Admin', 'Egi', '123', 'egi@ims.com', '2025-01-13', 'admin'),
(5, 'staff', '1', '123', 'staff1@ims.com', '2025-01-13', 'staff'),
(6, 'staff', '2', '123', 'staff2@ims.com', '2025-04-18', 'staff'),
(7, 'user', '2', '123', 'user2@ims.com', '2025-05-11', 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user` (`created_by`);

--
-- Indexes for table `checkout`
--
ALTER TABLE `checkout`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stocks`
--
ALTER TABLE `stocks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `asset_id` (`asset_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assets`
--
ALTER TABLE `assets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `checkout`
--
ALTER TABLE `checkout`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assets`
--
ALTER TABLE `assets`
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `stocks`
--
ALTER TABLE `stocks`
  ADD CONSTRAINT `stocks_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `stocks_ibfk_2` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
