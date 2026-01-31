-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 31, 2026 at 10:17 AM
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
-- Database: `gate_security_fresh_db`
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
  `access_type` enum('time_in','time_out') DEFAULT NULL,
  `gate_location` varchar(50) DEFAULT 'main_gate',
  `access_timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `access_logs`
--

INSERT INTO `access_logs` (`id`, `rfid_id`, `card_id`, `full_name`, `access_result`, `denial_reason`, `access_type`, `gate_location`, `access_timestamp`, `ip_address`) VALUES
(37, '123', 1, '123', 'granted', NULL, 'time_in', 'main_gate', '2026-01-31 04:11:57', '::1'),
(38, '123', 1, '123', 'granted', NULL, 'time_out', 'main_gate', '2026-01-31 04:12:30', '::1'),
(39, '123', 1, '123', 'granted', NULL, 'time_in', 'main_gate', '2026-01-31 04:13:00', '::1'),
(40, '123', 1, '123', 'granted', NULL, 'time_out', 'main_gate', '2026-01-31 04:14:20', '::1'),
(41, '123', 1, '123', 'granted', NULL, 'time_in', 'main_gate', '2026-01-31 04:19:06', '::1'),
(42, '123', 1, '123', 'granted', NULL, 'time_out', 'main_gate', '2026-01-31 04:19:59', '::1'),
(43, '123', 1, '123', 'granted', NULL, 'time_in', 'main_gate', '2026-01-31 04:23:35', '::1'),
(44, 'RFID001', NULL, 'Unknown', 'denied', 'Card not found in system', 'time_in', 'main_gate', '2026-01-31 09:13:48', '::1'),
(45, '123', 1, '123', 'granted', NULL, 'time_out', 'main_gate', '2026-01-31 09:15:13', '::1');

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
(1, 'admin', '$2y$10$Y9KxNqo/wBt/pGwolzM2P.DJzuhnt8rP1F9KPDaO2LpnuhTyld8T.', 'System Administrator', 'admin@holyfamily.edu.ph', '2026-01-31 09:13:11', 0, NULL, '2026-01-31 03:17:26', '2026-01-31 09:13:11');

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
(1, '123', '123', 'student', '123', 'active', 1, '2026-01-31 03:18:06', '2026-01-31 03:18:06');

-- --------------------------------------------------------

--
-- Table structure for table `rfid_scan_timeouts`
--

CREATE TABLE `rfid_scan_timeouts` (
  `id` int(11) UNSIGNED NOT NULL,
  `rfid_id` varchar(50) NOT NULL,
  `last_scan_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `timeout_until` timestamp NULL DEFAULT NULL,
  `scan_count` int(3) DEFAULT 1,
  `current_status` enum('in','out') DEFAULT 'out',
  `gate_location` varchar(50) DEFAULT 'main_gate'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `rfid_scan_timeouts`
--

INSERT INTO `rfid_scan_timeouts` (`id`, `rfid_id`, `last_scan_time`, `timeout_until`, `scan_count`, `current_status`, `gate_location`) VALUES
(14, '123', '2026-01-31 09:15:13', NULL, 1, 'out', 'main_gate'),
(15, 'RFID001', '2026-01-31 09:13:48', NULL, 1, 'in', 'main_gate');

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
(1, 'session_timeout', '1800', 'Session timeout in seconds (30 minutes)', NULL, '2026-01-31 03:17:26'),
(2, 'max_login_attempts', '3', 'Maximum failed login attempts before lockout', NULL, '2026-01-31 03:17:26'),
(3, 'lockout_duration', '900', 'Account lockout duration in seconds (15 minutes)', NULL, '2026-01-31 03:17:26'),
(4, 'gate_open_duration', '10', 'Gate open duration in seconds', NULL, '2026-01-31 03:17:26'),
(5, 'system_name', 'Holy Family High School Gate Security', 'System name for display', NULL, '2026-01-31 03:17:26'),
(6, 'log_retention_days', '180', 'Number of days to retain access logs', NULL, '2026-01-31 03:17:26'),
(7, 'enable_alerts', '1', 'Enable security alerts (1=enabled, 0=disabled)', NULL, '2026-01-31 03:17:26'),
(8, 'rfid_scan_timeout', '30', 'RFID scan timeout in seconds (prevents rapid re-scanning)', NULL, '2026-01-31 03:17:26'),
(9, 'rfid_max_scans_before_timeout', '3', 'Maximum scans before timeout is applied', NULL, '2026-01-31 03:17:26'),
(10, 'rfid_timeout_duration', '30', 'RFID timeout duration in seconds (5 minutes)', NULL, '2026-01-31 03:35:12');

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
  ADD KEY `idx_access_type` (`access_type`),
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
-- Indexes for table `rfid_scan_timeouts`
--
ALTER TABLE `rfid_scan_timeouts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rfid_timeout` (`rfid_id`,`timeout_until`),
  ADD KEY `idx_timeout_until` (`timeout_until`),
  ADD KEY `idx_rfid_status` (`rfid_id`,`current_status`);

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
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `rfid_cards`
--
ALTER TABLE `rfid_cards`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `rfid_scan_timeouts`
--
ALTER TABLE `rfid_scan_timeouts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
