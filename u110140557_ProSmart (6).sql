-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 03, 2025 at 05:20 AM
-- Server version: 11.8.3-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u110140557_ProSmart`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `role` enum('admin','super_admin') DEFAULT 'admin',
  `status` enum('active','inactive') DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `email`, `password_hash`, `full_name`, `role`, `status`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'farukdesk', 'farukdesk@gmail.com', '827ccb0eea8a706c4c34a16891f84e7b', 'Faruk Desk', 'admin', 'active', NULL, '2025-09-29 20:09:54', '2025-09-29 20:09:54');

-- --------------------------------------------------------

--
-- Table structure for table `api_keys`
--

CREATE TABLE `api_keys` (
  `id` int(11) NOT NULL,
  `service` varchar(50) NOT NULL COMMENT 'Service name (e.g., openai, chatgpt)',
  `api_key` text NOT NULL COMMENT 'Encrypted or hashed API key',
  `status` enum('active','inactive','expired') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_used_at` timestamp NULL DEFAULT NULL,
  `usage_count` int(11) DEFAULT 0,
  `notes` text DEFAULT NULL COMMENT 'Additional notes about the API key'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `api_keys`
--

INSERT INTO `api_keys` (`id`, `service`, `api_key`, `status`, `created_at`, `updated_at`, `last_used_at`, `usage_count`, `notes`) VALUES
(1, 'gemini', 'AIzaSyAGZb51t7yjBPNk99p4fBfwtin-7cizo1c', 'active', '2025-09-30 18:17:30', '2025-10-02 18:50:51', NULL, 50000000, 'Google Gemini 1.5 Flash API key for cover letter analysis');

-- --------------------------------------------------------

--
-- Table structure for table `api_usage_log`
--

CREATE TABLE `api_usage_log` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL COMMENT 'Client IP address',
  `license_key` varchar(255) DEFAULT NULL COMMENT 'User license key for per-user rate limiting',
  `endpoint` varchar(100) NOT NULL COMMENT 'API endpoint called',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `api_usage_log`
--

INSERT INTO `api_usage_log` (`id`, `ip_address`, `license_key`, `endpoint`, `created_at`) VALUES
(82, '2a02:6b67:d3a4:d500:7381:7a4b:5b1d:9510', 'SMARTAPPLY-PRO-AWJF-TQM5-PL8J-PSAZ-6N3G', 'cover-letter-analysis', '2025-10-02 19:38:06'),
(83, '2a02:6b67:d3a4:d500:7381:7a4b:5b1d:9510', 'SMARTAPPLY-PRO-AWJF-TQM5-PL8J-PSAZ-6N3G', 'cover-letter-analysis', '2025-10-02 19:39:06'),
(84, '2a02:6b67:d3a4:d500:7381:7a4b:5b1d:9510', 'SMARTAPPLY-PRO-AWJF-TQM5-PL8J-PSAZ-6N3G', 'cover-letter-analysis', '2025-10-02 19:39:35'),
(85, '2a02:6b67:d3a4:d500:7381:7a4b:5b1d:9510', 'SMARTAPPLY-PRO-AWJF-TQM5-PL8J-PSAZ-6N3G', 'cover-letter-analysis', '2025-10-02 19:41:45'),
(86, '2a02:6b67:d3a4:d500:7381:7a4b:5b1d:9510', 'SMARTAPPLY-PRO-AWJF-TQM5-PL8J-PSAZ-6N3G', 'cover-letter-analysis', '2025-10-02 19:55:11'),
(87, '2a02:6b67:d3a4:d500:7381:7a4b:5b1d:9510', 'SMARTAPPLY-PRO-AWJF-TQM5-PL8J-PSAZ-6N3G', 'cover-letter-analysis', '2025-10-02 19:55:16'),
(88, '2a02:6b67:d3a4:d500:7381:7a4b:5b1d:9510', 'SMARTAPPLY-PRO-AWJF-TQM5-PL8J-PSAZ-6N3G', 'cover-letter-analysis', '2025-10-02 20:51:38'),
(89, '2a02:6b67:d3a4:d500:7381:7a4b:5b1d:9510', 'SMARTAPPLY-PRO-AWJF-TQM5-PL8J-PSAZ-6N3G', 'cover-letter-analysis', '2025-10-02 20:51:43'),
(90, '2a02:6b67:d3a4:d500:7381:7a4b:5b1d:9510', 'SMARTAPPLY-PRO-AWJF-TQM5-PL8J-PSAZ-6N3G', 'cover-letter-analysis', '2025-10-02 20:52:06'),
(91, '2a02:6b67:d3a4:d500:7381:7a4b:5b1d:9510', 'SMARTAPPLY-PRO-AWJF-TQM5-PL8J-PSAZ-6N3G', 'cover-letter-analysis', '2025-10-02 20:55:43'),
(92, '2a02:6b67:d3a4:d500:7381:7a4b:5b1d:9510', 'SMARTAPPLY-PRO-AWJF-TQM5-PL8J-PSAZ-6N3G', 'cover-letter-analysis', '2025-10-02 20:56:12'),
(93, '2a02:6b67:d3a4:d500:7381:7a4b:5b1d:9510', 'SMARTAPPLY-PRO-AWJF-TQM5-PL8J-PSAZ-6N3G', 'cover-letter-analysis', '2025-10-02 21:04:34'),
(94, '2a02:6b67:d3a4:d500:7381:7a4b:5b1d:9510', 'SMARTAPPLY-PRO-AWJF-TQM5-PL8J-PSAZ-6N3G', 'cover-letter-analysis', '2025-10-02 21:04:48'),
(95, '2a02:6b67:d3a4:d500:7381:7a4b:5b1d:9510', 'SMARTAPPLY-PRO-AWJF-TQM5-PL8J-PSAZ-6N3G', 'cover-letter-analysis', '2025-10-02 21:07:47'),
(96, '2a02:6b67:d3a4:d500:7381:7a4b:5b1d:9510', 'SMARTAPPLY-PRO-AWJF-TQM5-PL8J-PSAZ-6N3G', 'cover-letter-analysis', '2025-10-02 21:08:10');

-- --------------------------------------------------------

--
-- Table structure for table `cover_letter_analysis_log`
--

CREATE TABLE `cover_letter_analysis_log` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `license_key` varchar(255) DEFAULT NULL COMMENT 'User license key for analytics',
  `total_score` int(11) NOT NULL COMMENT 'Total score (0-40)',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cover_letter_analysis_log`
--

INSERT INTO `cover_letter_analysis_log` (`id`, `ip_address`, `license_key`, `total_score`, `created_at`) VALUES
(1, '2a02:6b67:d3a4:d500:7381:7a4b:5b1d:9510', 'SMARTAPPLY-PRO-AWJF-TQM5-PL8J-PSAZ-6N3G', 5, '2025-10-02 19:34:37'),
(2, '2a02:6b67:d3a4:d500:7381:7a4b:5b1d:9510', 'SMARTAPPLY-PRO-AWJF-TQM5-PL8J-PSAZ-6N3G', 5, '2025-10-02 19:34:43'),
(3, '2a02:6b67:d3a4:d500:7381:7a4b:5b1d:9510', 'SMARTAPPLY-PRO-AWJF-TQM5-PL8J-PSAZ-6N3G', 0, '2025-10-02 19:34:45'),
(4, '2a02:6b67:d3a4:d500:7381:7a4b:5b1d:9510', 'SMARTAPPLY-PRO-AWJF-TQM5-PL8J-PSAZ-6N3G', 15, '2025-10-02 19:35:32'),
(5, '2a02:6b67:d3a4:d500:7381:7a4b:5b1d:9510', 'SMARTAPPLY-PRO-AWJF-TQM5-PL8J-PSAZ-6N3G', 4, '2025-10-02 19:39:16'),
(6, '2a02:6b67:d3a4:d500:7381:7a4b:5b1d:9510', 'SMARTAPPLY-PRO-AWJF-TQM5-PL8J-PSAZ-6N3G', 35, '2025-10-02 19:55:23'),
(7, '2a02:6b67:d3a4:d500:7381:7a4b:5b1d:9510', 'SMARTAPPLY-PRO-AWJF-TQM5-PL8J-PSAZ-6N3G', 34, '2025-10-02 19:55:31'),
(8, '2a02:6b67:d3a4:d500:7381:7a4b:5b1d:9510', 'SMARTAPPLY-PRO-AWJF-TQM5-PL8J-PSAZ-6N3G', 33, '2025-10-02 20:56:23');

-- --------------------------------------------------------

--
-- Table structure for table `email_logs`
--

CREATE TABLE `email_logs` (
  `id` int(11) NOT NULL,
  `license_key` varchar(255) NOT NULL,
  `email_to` varchar(255) NOT NULL,
  `email_type` enum('license_delivery','renewal_reminder','expiry_notice') NOT NULL,
  `status` enum('pending','sent','failed') DEFAULT 'pending',
  `sent_at` datetime DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `licenses`
--

CREATE TABLE `licenses` (
  `id` int(11) NOT NULL,
  `license_key` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `plan_type` enum('monthly','annual','lifetime') NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'USD',
  `status` enum('active','expired','cancelled','refunded') DEFAULT 'active',
  `purchase_date` datetime DEFAULT current_timestamp(),
  `expiry_date` datetime DEFAULT NULL,
  `stripe_payment_intent_id` varchar(255) DEFAULT NULL,
  `stripe_customer_id` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `licenses`
--

INSERT INTO `licenses` (`id`, `license_key`, `full_name`, `email`, `plan_type`, `amount_paid`, `currency`, `status`, `purchase_date`, `expiry_date`, `stripe_payment_intent_id`, `stripe_customer_id`, `created_at`, `updated_at`) VALUES
(1, 'SMARTAPPLY-PRO-AWJF-TQM5-PL8J-PSAZ-6N3G', 'Md Omar Faruk', 'farukdesk@gmail.com', 'monthly', 19.00, 'USD', 'active', '2025-09-30 11:13:11', '2025-10-30 11:13:11', NULL, NULL, '2025-09-30 11:13:11', '2025-09-30 11:13:11'),
(2, 'SMARTAPPLY-PRO-AWJF-TQM5-PL8J-TSAZ-6N3G', 'Md Omar Faruk', 'farukdesk@gmail.com', 'monthly', 19.00, 'USD', 'active', '2025-09-30 11:13:11', '2025-10-30 11:13:11', NULL, NULL, '2025-09-30 11:13:11', '2025-09-30 11:13:11');

-- --------------------------------------------------------

--
-- Table structure for table `license_verifications`
--

CREATE TABLE `license_verifications` (
  `id` int(11) NOT NULL,
  `license_key` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `verification_date` datetime DEFAULT current_timestamp(),
  `status` enum('valid','invalid','expired') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `license_verifications`
--

INSERT INTO `license_verifications` (`id`, `license_key`, `ip_address`, `user_agent`, `verification_date`, `status`) VALUES
(1, 'SMARTAPPLY-PRO-AWJF-TQM5-PL8J-PSAZ-6N3G', '2a02:6b67:d3a4:d500:7381:7a4b:5b1d:9510', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 15:34:59', 'valid'),
(3, 'SMARTAPPLY-PRO-AWJF-TQM5-PL8J-TSAZ-6N3G', '2a02:6b67:d3a4:d500:7381:7a4b:5b1d:9510', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 15:37:28', 'valid'),
(4, 'SMARTAPPLY-PRO-AWJF-TQM5-PL8J-PSAZ-6N3G', '2a02:6b67:d3a4:d500:7381:7a4b:5b1d:9510', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 18:11:17', 'valid'),
(5, 'SMARTAPPLY-PRO-AWJF-TQM5-PL8J-PSAZ-6N3G', '2a02:6b67:d3a4:d500:7381:7a4b:5b1d:9510', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 13:48:08', 'valid');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(100) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `plan_type` enum('monthly','annual','lifetime') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'USD',
  `payment_status` enum('pending','processing','succeeded','failed','cancelled') DEFAULT 'pending',
  `stripe_payment_intent_id` varchar(255) DEFAULT NULL,
  `stripe_customer_id` varchar(255) DEFAULT NULL,
  `license_key` varchar(255) DEFAULT NULL,
  `order_status` enum('pending','processing','completed','cancelled','refunded') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_events`
--

CREATE TABLE `payment_events` (
  `id` int(11) NOT NULL,
  `stripe_event_id` varchar(255) NOT NULL,
  `event_type` varchar(100) NOT NULL,
  `license_key` varchar(255) DEFAULT NULL,
  `payment_intent_id` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `currency` varchar(3) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `processed_at` datetime DEFAULT current_timestamp(),
  `raw_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`raw_data`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_accounts`
--

CREATE TABLE `user_accounts` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `email_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(255) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_subscriptions`
--

CREATE TABLE `user_subscriptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `license_key` varchar(255) NOT NULL,
  `plan_type` enum('monthly','annual','lifetime') NOT NULL,
  `status` enum('active','expired','cancelled') DEFAULT 'active',
  `start_date` datetime DEFAULT current_timestamp(),
  `expiry_date` datetime DEFAULT NULL,
  `auto_renew` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `api_keys`
--
ALTER TABLE `api_keys`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_service` (`service`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `api_usage_log`
--
ALTER TABLE `api_usage_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ip_endpoint_time` (`ip_address`,`endpoint`,`created_at`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_license_endpoint_time` (`license_key`,`endpoint`,`created_at`);

--
-- Indexes for table `cover_letter_analysis_log`
--
ALTER TABLE `cover_letter_analysis_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_ip_address` (`ip_address`),
  ADD KEY `idx_license_key` (`license_key`);

--
-- Indexes for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email_logs_license` (`license_key`);

--
-- Indexes for table `licenses`
--
ALTER TABLE `licenses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `license_key` (`license_key`),
  ADD KEY `idx_licenses_email` (`email`),
  ADD KEY `idx_licenses_status` (`status`),
  ADD KEY `idx_licenses_expiry` (`expiry_date`);

--
-- Indexes for table `license_verifications`
--
ALTER TABLE `license_verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_verifications_license` (`license_key`),
  ADD KEY `idx_verifications_date` (`verification_date`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `license_key` (`license_key`);

--
-- Indexes for table `payment_events`
--
ALTER TABLE `payment_events`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stripe_event_id` (`stripe_event_id`),
  ADD KEY `idx_payment_events_stripe_id` (`stripe_event_id`),
  ADD KEY `license_key` (`license_key`);

--
-- Indexes for table `user_accounts`
--
ALTER TABLE `user_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_subscriptions`
--
ALTER TABLE `user_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `license_key` (`license_key`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `api_keys`
--
ALTER TABLE `api_keys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `api_usage_log`
--
ALTER TABLE `api_usage_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT for table `cover_letter_analysis_log`
--
ALTER TABLE `cover_letter_analysis_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `email_logs`
--
ALTER TABLE `email_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `licenses`
--
ALTER TABLE `licenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `license_verifications`
--
ALTER TABLE `license_verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_events`
--
ALTER TABLE `payment_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_accounts`
--
ALTER TABLE `user_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_subscriptions`
--
ALTER TABLE `user_subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD CONSTRAINT `email_logs_ibfk_1` FOREIGN KEY (`license_key`) REFERENCES `licenses` (`license_key`);

--
-- Constraints for table `license_verifications`
--
ALTER TABLE `license_verifications`
  ADD CONSTRAINT `license_verifications_ibfk_1` FOREIGN KEY (`license_key`) REFERENCES `licenses` (`license_key`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`approved_by`) REFERENCES `admin_users` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`license_key`) REFERENCES `licenses` (`license_key`);

--
-- Constraints for table `payment_events`
--
ALTER TABLE `payment_events`
  ADD CONSTRAINT `payment_events_ibfk_1` FOREIGN KEY (`license_key`) REFERENCES `licenses` (`license_key`);

--
-- Constraints for table `user_subscriptions`
--
ALTER TABLE `user_subscriptions`
  ADD CONSTRAINT `user_subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_accounts` (`id`),
  ADD CONSTRAINT `user_subscriptions_ibfk_2` FOREIGN KEY (`license_key`) REFERENCES `licenses` (`license_key`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
