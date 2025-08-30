-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 29, 2025 at 02:14 PM
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
-- Database: `lostfounddb`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_activity_logs`
--

CREATE TABLE `admin_activity_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `activity_type` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_activity_logs`
--

INSERT INTO `admin_activity_logs` (`id`, `admin_id`, `activity_type`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'login', 'Admin user logged in', '192.168.1.100', NULL, '2025-08-29 12:12:49'),
(2, 1, 'settings_update', 'Updated site name to Lost & Found Portal', '192.168.1.100', NULL, '2025-08-29 12:12:49'),
(3, 1, 'user_management', 'Deleted user account ID: 5', '192.168.1.100', NULL, '2025-08-29 12:12:49'),
(4, 1, 'item_approval', 'Approved lost item ID: 3', '192.168.1.100', NULL, '2025-08-29 12:12:49');

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `email`, `created_at`) VALUES
(1, 'admin', '$2y$10$cq5ipx9AW5pvYWyrX2v13uS6Jog4McA962nmunNqRhE7QGxDyoc8C', 'admin@lostandfound.com', '2025-08-23 18:12:48');

-- --------------------------------------------------------

--
-- Table structure for table `found_items`
--

CREATE TABLE `found_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `category` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `location` varchar(150) NOT NULL,
  `found_date` date NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected','returned') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `found_items`
--

INSERT INTO `found_items` (`id`, `user_id`, `item_name`, `category`, `description`, `location`, `found_date`, `image_path`, `status`, `created_at`) VALUES
(1, 6, 'Backpack', 'Bags', 'Blue backpack with books and a water bottle.', 'University Library', '2023-10-16', NULL, 'pending', '2025-08-29 12:12:49'),
(2, 1, 'Keys', 'Personal Items', 'Set of keys with a keychain.', 'Bus Station', '2023-10-19', NULL, 'approved', '2025-08-29 12:12:49'),
(3, 6, 'Sunglasses', 'Accessories', 'Black Ray-Ban sunglasses in a case.', 'Coffee Shop', '2023-10-21', NULL, 'returned', '2025-08-29 12:12:49');

-- --------------------------------------------------------

--
-- Table structure for table `item_claims`
--

CREATE TABLE `item_claims` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `item_type` enum('lost','found') NOT NULL,
  `claim_description` text NOT NULL,
  `proof_details` text DEFAULT NULL,
  `status` enum('pending','under_review','approved','rejected') DEFAULT 'pending',
  `resolved_date` date DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `item_claims`
--

INSERT INTO `item_claims` (`id`, `user_id`, `item_id`, `item_type`, `claim_description`, `proof_details`, `status`, `resolved_date`, `admin_notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'lost', 'This is my iPhone that I lost last week. I can provide the IMEI number and purchase receipt.', 'IMEI: 123456789012345, Purchase date: 2023-01-15', 'under_review', NULL, NULL, '2025-08-29 12:12:49', '2025-08-29 12:12:49'),
(2, 6, 2, 'found', 'I believe this is my wallet. I lost it in Central Park and it contains my ID cards.', 'ID numbers matching the wallet contents', 'pending', NULL, NULL, '2025-08-29 12:12:49', '2025-08-29 12:12:49'),
(3, 1, 4, 'found', 'This backpack looks like mine. I lost it at the library last week.', 'Description of contents matches my missing backpack', 'pending', NULL, NULL, '2025-08-29 12:12:49', '2025-08-29 12:12:49');

-- --------------------------------------------------------

--
-- Table structure for table `lost_items`
--

CREATE TABLE `lost_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `category` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `location` varchar(150) NOT NULL,
  `lost_date` date NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected','found') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lost_items`
--

INSERT INTO `lost_items` (`id`, `user_id`, `item_name`, `category`, `description`, `location`, `lost_date`, `image_path`, `status`, `created_at`) VALUES
(1, 1, 'iPhone 12 Pro', 'Electronics', 'Black iPhone 12 Pro with a blue case. Lost somewhere in the city center.', 'City Center', '2023-10-15', NULL, 'pending', '2025-08-29 12:12:49'),
(2, 6, 'Wallet', 'Personal Items', 'Brown leather wallet containing ID cards and some cash.', 'Central Park', '2023-10-18', NULL, 'approved', '2025-08-29 12:12:49'),
(3, 1, 'Gold Watch', 'Accessories', 'Vintage gold wristwatch with leather strap.', 'Shopping Mall', '2023-10-20', NULL, 'found', '2025-08-29 12:12:49');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_group` varchar(50) DEFAULT 'general',
  `setting_type` enum('text','number','boolean','select','textarea') DEFAULT 'text',
  `options` text DEFAULT NULL,
  `label` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_public` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_group`, `setting_type`, `options`, `label`, `description`, `display_order`, `is_public`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'Lost & Found Portal', 'general', 'text', NULL, 'Site Name', 'The name of your Lost & Found website', 1, 0, '2025-08-29 12:12:49', '2025-08-29 12:12:49'),
(2, 'site_description', 'Reuniting lost items with their owners', 'general', 'textarea', NULL, 'Site Description', 'A brief description of your website', 2, 0, '2025-08-29 12:12:49', '2025-08-29 12:12:49'),
(3, 'admin_email', 'admin@lostandfound.com', 'general', 'text', NULL, 'Admin Email', 'The primary email address for administrative communications', 3, 0, '2025-08-29 12:12:49', '2025-08-29 12:12:49'),
(4, 'items_per_page', '10', 'general', 'number', NULL, 'Items Per Page', 'Number of items to display per page in listings', 4, 0, '2025-08-29 12:12:49', '2025-08-29 12:12:49'),
(5, 'user_registration', '1', 'general', 'boolean', NULL, 'User Registration', 'Allow new users to register accounts', 5, 0, '2025-08-29 12:12:49', '2025-08-29 12:12:49'),
(6, 'require_email_verification', '1', 'general', 'boolean', NULL, 'Email Verification', 'Require email verification for new user accounts', 6, 0, '2025-08-29 12:12:49', '2025-08-29 12:12:49'),
(7, 'notify_new_items', '1', 'notifications', 'boolean', NULL, 'Notify New Items', 'Send notifications when new items are reported', 10, 0, '2025-08-29 12:12:49', '2025-08-29 12:12:49'),
(8, 'notify_new_claims', '1', 'notifications', 'boolean', NULL, 'Notify New Claims', 'Send notifications when new claims are submitted', 11, 0, '2025-08-29 12:12:49', '2025-08-29 12:12:49'),
(9, 'auto_approve_items', '0', 'moderation', 'boolean', NULL, 'Auto Approve Items', 'Automatically approve new items without manual review', 20, 0, '2025-08-29 12:12:49', '2025-08-29 12:12:49'),
(10, 'claim_approval_required', '1', 'moderation', 'boolean', NULL, 'Claim Approval Required', 'Require admin approval for item claims', 21, 0, '2025-08-29 12:12:49', '2025-08-29 12:12:49'),
(11, 'items_expire_after', '90', 'moderation', 'number', NULL, 'Items Expire After', 'Number of days after which items are automatically archived', 22, 0, '2025-08-29 12:12:49', '2025-08-29 12:12:49'),
(12, 'theme_color', '#fbb117', 'appearance', 'select', '[\"#fbb117\", \"#2196F3\", \"#4CAF50\", \"#9C27B0\", \"#FF9800\", \"#F44336\"]', 'Theme Color', 'Primary color theme for the website', 30, 0, '2025-08-29 12:12:49', '2025-08-29 12:12:49'),
(13, 'logo_url', 'logo2.png', 'appearance', 'text', NULL, 'Logo URL', 'Path to the website logo image', 31, 0, '2025-08-29 12:12:49', '2025-08-29 12:12:49'),
(14, 'smtp_enabled', '0', 'email', 'boolean', NULL, 'Enable SMTP', 'Use SMTP for sending emails instead of PHP mail()', 40, 0, '2025-08-29 12:12:49', '2025-08-29 12:12:49'),
(15, 'smtp_host', '', 'email', 'text', NULL, 'SMTP Host', 'Your SMTP server hostname', 41, 0, '2025-08-29 12:12:49', '2025-08-29 12:12:49'),
(16, 'smtp_port', '587', 'email', 'number', NULL, 'SMTP Port', 'Port for SMTP connection (usually 587 for TLS)', 42, 0, '2025-08-29 12:12:49', '2025-08-29 12:12:49'),
(17, 'smtp_username', '', 'email', 'text', NULL, 'SMTP Username', 'Username for SMTP authentication', 43, 0, '2025-08-29 12:12:49', '2025-08-29 12:12:49'),
(18, 'smtp_password', '', 'email', 'text', NULL, 'SMTP Password', 'Password for SMTP authentication', 44, 0, '2025-08-29 12:12:49', '2025-08-29 12:12:49');

-- --------------------------------------------------------

--
-- Table structure for table `user_details`
--

CREATE TABLE `user_details` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `username` varchar(250) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `nic` varchar(20) NOT NULL,
  `address_line1` varchar(150) NOT NULL,
  `address_line2` varchar(150) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(10) DEFAULT 'active',
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `role` TINYINT(1) NOT NULL DEFAULT 0 -- 0 = ordinary user, 1 = admin
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_details`
--

INSERT INTO `user_details` (`id`, `full_name`, `username`, `email`, `nic`, `address_line1`, `address_line2`, `contact_number`, `password`, `created_at`, `status`, `last_updated`, `role`) VALUES
(1, 'Okithma De Silva', NULL, 'chamanisilva7312@gmail.com', '200309810234', 'No. 180, Waduramulla Watta', 'Panadura', '07771011548', '$2y$10$6JcfTR5L.GruWY45502tdO8/zFSVX/UJcRfzy6zbCLYcz7ZiIGcKC', '2025-08-23 08:40:01', 'active', '2025-08-29 12:12:49', 0),
(6, 'Dehemi  Sawmya', 'shashini2003', 'wowcdesilva2003@gmail.com', '200309810237', 'no 180,waduramulla watta', 'panadura', '0887539430', '$2y$10$Ohlm2sT2LbaP/w.7Riwp7eqCCG6LND5FHt1l1dZfS6RiOwBmYys6y', '2025-08-23 09:49:51', 'active', '2025-08-29 12:12:49', 0);

-- Add default ordinary user (username=uoc, password=uoc) and a mock admin user
INSERT INTO `user_details` (`id`, full_name, username, email, nic, address_line1, address_line2, contact_number, password, created_at, status, last_updated, role) VALUES
(7, 'Default User', 'uoc', 'uoc@example.com', '0000000000', 'Default Address', '', '0000000000', '$2y$10$S284Lhl9zreSujCTPLg4dONnND7BsSN1GtUcgJtuZ/EmhsTDCMko6', NOW(), 'active', NOW(), 0),
(8, 'Site Admin', 'siteadmin', 'admin@lostandfound.com', '1111111111', 'Admin Address', '', '0000000000', '$2y$10$/psBgfGTFLPDCst/K2wGSOF.RhbydRtzU/4edsUM1ZmTQAUBsXS3y', NOW(), 'active', NOW(), 1),
(9, 'Admin UOC', 'uoc', 'uoc.admin@example.com', '2222222222', 'Admin Address', '', '0000000000', '$2y$10$S284Lhl9zreSujCTPLg4dONnND7BsSN1GtUcgJtuZ/EmhsTDCMko6', NOW(), 'active', NOW(), 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_id`
--

--
-- Table structure for table `reported_items`
--

CREATE TABLE `reported_items` (
  `id` int(11) NOT NULL,
  `item_id` int(11) AS (`id`) VIRTUAL,
  `user_id` int(11) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `item_name` varchar(150) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `lost_date` date DEFAULT NULL,
  `lost_location` varchar(150) DEFAULT NULL,
  `status` enum('pending','found','claimed') DEFAULT 'pending',
  `image_path` varchar(255) DEFAULT NULL,
  `reported_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `claimed_items`
--

CREATE TABLE `claimed_items` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `item_name` varchar(150) NOT NULL,
  `reported_by` varchar(150) DEFAULT NULL,
  `claimed_by` varchar(150) NOT NULL,
  `claimer_id` varchar(50) DEFAULT NULL,
  `contact_number` varchar(30) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `claimed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `user_id` (
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_activity_logs`
--
ALTER TABLE `admin_activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `activity_type` (`activity_type`);

--
-- Indexes for table `admin_users`
-- (admin_users removed; indexes not required)

--
-- Indexes for table `found_items`
--
ALTER TABLE `found_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `item_claims`
--
ALTER TABLE `item_claims`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

-- Indexes for table `reported_items`
ALTER TABLE `reported_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category` (`category`),
  ADD KEY `status` (`status`);

-- Indexes for table `claimed_items`
ALTER TABLE `claimed_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `lost_items`
--
ALTER TABLE `lost_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `user_details`
--
ALTER TABLE `user_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_activity_logs`
--
ALTER TABLE `admin_activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

-- (admin_users table removed; authentication uses user_details.role)
--
-- AUTO_INCREMENT for table `found_items`
--
ALTER TABLE `found_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `item_claims`
--
ALTER TABLE `item_claims`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

-- AUTO_INCREMENT for table `reported_items`
ALTER TABLE `reported_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

-- AUTO_INCREMENT for table `claimed_items`
ALTER TABLE `claimed_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `lost_items`
--
ALTER TABLE `lost_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `user_details`
--
ALTER TABLE `user_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `found_items`
--
ALTER TABLE `found_items`
  ADD CONSTRAINT `found_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_details` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `item_claims`
--
ALTER TABLE `item_claims`
  ADD CONSTRAINT `item_claims_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_details` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lost_items`
--
ALTER TABLE `lost_items`
  ADD CONSTRAINT `lost_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_details` (`id`) ON DELETE CASCADE;

-- Constraints for table `reported_items`
ALTER TABLE `reported_items`
  ADD CONSTRAINT `reported_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_details` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
