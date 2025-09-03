-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 03, 2025 at 07:43 AM
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
-- Database: `gate_security_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `access_logs`
--

CREATE TABLE `access_logs` (
  `id` int(11) UNSIGNED NOT NULL,
  `rfid_id` varchar(50) NOT NULL,
  `card_id` int(11) UNSIGNED DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `access_result` enum('granted','denied') NOT NULL,
  `denial_reason` varchar(100) DEFAULT NULL,
  `gate_location` varchar(50) DEFAULT 'main_gate',
  `access_timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `access_logs`
--

INSERT INTO `access_logs` (`id`, `rfid_id`, `card_id`, `full_name`, `access_result`, `denial_reason`, `gate_location`, `access_timestamp`, `ip_address`) VALUES
(1, 'RFID001', 1, 'John Doe', 'granted', NULL, 'main_gate', '2025-09-03 04:32:41', NULL),
(2, 'RFID002', 2, 'Jane Smith', 'granted', NULL, 'main_gate', '2025-09-03 04:32:41', NULL),
(3, 'RFID003', 3, 'Mike Johnson', 'denied', 'Card suspended', 'main_gate', '2025-09-03 04:32:41', NULL),
(4, 'RFID004', 4, 'Sarah Wilson', 'granted', NULL, 'main_gate', '2025-09-03 04:32:41', NULL),
(5, 'RFID005', 5, 'Bob Brown', 'denied', 'Visitor access expired', 'main_gate', '2025-09-03 04:32:41', NULL),
(6, 'RFID001', 1, 'John Doe', 'granted', NULL, 'main_gate', '2025-09-03 04:32:41', NULL),
(7, 'UNKNOWN', NULL, 'Unknown', 'denied', 'Card not found', 'main_gate', '2025-09-03 04:32:41', NULL),
(8, 'RFID001', 1, 'John Doe', 'granted', NULL, 'main_gate', '2025-09-03 04:34:06', '::1'),
(9, 'RFID001', 1, 'John Doe', 'granted', NULL, 'main_gate', '2025-09-03 04:34:16', '::1'),
(10, 'RFID001', 1, 'John Doe', 'granted', NULL, 'main_gate', '2025-09-03 04:55:57', '::1'),
(11, 'RFID003', 3, 'Mike Johnson', 'denied', 'Card is inactive', 'main_gate', '2025-09-03 04:56:09', '::1'),
(12, 'RFID003', 3, 'Mike Johnson', 'denied', 'Card is inactive', 'main_gate', '2025-09-03 04:56:43', '::1'),
(13, 'UNKNOWN', NULL, 'Unknown', 'denied', 'Card not found in system', 'main_gate', '2025-09-03 04:56:44', '::1');

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `failed_attempts` int(3) DEFAULT 0,
  `locked_until` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `full_name`, `email`, `last_login`, `failed_attempts`, `locked_until`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$Aou3qOdTb3NhSkYRDhjcZuvEJzzRZ7EgGesmOzfEsR.VKO6VN07qy', 'System Administrator', 'admin@holyfamily.edu.ph', '2025-09-03 05:21:54', 0, NULL, '2025-09-03 04:18:36', '2025-09-03 05:21:54');

-- --------------------------------------------------------

--
-- Table structure for table `rfid_cards`
--

CREATE TABLE `rfid_cards` (
  `id` int(11) UNSIGNED NOT NULL,
  `rfid_id` varchar(50) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('student','teacher','staff','visitor') NOT NULL,
  `plate_number` varchar(20) DEFAULT NULL,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `created_by` int(11) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `rfid_cards`
--

INSERT INTO `rfid_cards` (`id`, `rfid_id`, `full_name`, `role`, `plate_number`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'RFID001', 'John Doe', 'teacher', 'ABC-1234', 'active', 1, '2025-09-03 04:32:41', '2025-09-03 04:32:41'),
(2, 'RFID002', 'Jane Smith', 'staff', 'XYZ-5678', 'active', 1, '2025-09-03 04:32:41', '2025-09-03 04:32:41'),
(3, 'RFID003', 'Mike Johnson', 'student', 'ASD-1233', 'inactive', 1, '2025-09-03 04:32:41', '2025-09-03 04:49:35'),
(4, 'RFID004', 'Sarah Wilson', 'teacher', 'DEF-9012', 'active', 1, '2025-09-03 04:32:41', '2025-09-03 04:32:41'),
(5, 'RFID005', 'Bob Brown', 'visitor', 'GHI-3456', 'active', 1, '2025-09-03 04:32:41', '2025-09-03 04:32:41'),
(6, 'RFID006', 'Mike Tyson', 'teacher', 'ASD-12334', 'active', 1, '2025-09-03 04:40:13', '2025-09-03 04:40:13');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) UNSIGNED NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `updated_by` int(11) UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `description`, `updated_by`, `updated_at`) VALUES
(1, 'session_timeout', '1800', 'Session timeout in seconds (30 minutes)', NULL, '2025-09-03 04:18:36'),
(2, 'max_login_attempts', '3', 'Maximum failed login attempts before lockout', NULL, '2025-09-03 04:18:36'),
(3, 'lockout_duration', '900', 'Account lockout duration in seconds (15 minutes)', NULL, '2025-09-03 04:18:36'),
(4, 'gate_open_duration', '10', 'Gate open duration in seconds', NULL, '2025-09-03 04:18:36'),
(5, 'system_name', 'Holy Family High School Gate Security', 'System name for display', NULL, '2025-09-03 04:18:36'),
(6, 'log_retention_days', '180', 'Number of days to retain access logs', NULL, '2025-09-03 04:18:36'),
(7, 'enable_alerts', '1', 'Enable security alerts (1=enabled, 0=disabled)', NULL, '2025-09-03 04:18:36');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `access_logs`
--
ALTER TABLE `access_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rfid_timestamp` (`rfid_id`,`access_timestamp`),
  ADD KEY `idx_timestamp` (`access_timestamp`),
  ADD KEY `idx_result` (`access_result`),
  ADD KEY `card_id` (`card_id`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `rfid_cards`
--
ALTER TABLE `rfid_cards`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rfid_id` (`rfid_id`),
  ADD KEY `idx_rfid_id` (`rfid_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `updated_by` (`updated_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `access_logs`
--
ALTER TABLE `access_logs`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `rfid_cards`
--
ALTER TABLE `rfid_cards`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `access_logs`
--
ALTER TABLE `access_logs`
  ADD CONSTRAINT `access_logs_ibfk_1` FOREIGN KEY (`card_id`) REFERENCES `rfid_cards` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `rfid_cards`
--
ALTER TABLE `rfid_cards`
  ADD CONSTRAINT `rfid_cards_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD CONSTRAINT `system_settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
