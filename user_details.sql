-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 23, 2025 at 12:36 PM
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
-- Database: `lostfounddb`
--

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

INSERT INTO `user_details` (`id`, `full_name`, `username`, `email`, `nic`, `address_line1`, `address_line2`, `contact_number`, `password`, `created_at`) VALUES
(1, 'Okithma De Silva', NULL, 'chamanisilva7312@gmail.com', '200309810234', 'No. 180, Waduramulla Watta', 'Panadura', '07771011548', '$2y$10$6JcfTR5L.GruWY45502tdO8/zFSVX/UJcRfzy6zbCLYcz7ZiIGcKC', '2025-08-23 08:40:01', 'active', '2025-08-23 08:40:01', 0),
(6, 'shashini  nethmika', 'shashini2003', 'wowcdesilva2003@gmail.com', '200309810237', 'no 180,waduramulla watta', 'panadura', '0887539430', '$2y$10$Ohlm2sT2LbaP/w.7Riwp7eqCCG6LND5FHt1l1dZfS6RiOwBmYys6y', '2025-08-23 09:49:51', 'active', '2025-08-23 09:49:51', 0);

-- Add default ordinary user (username=uoc, password=uoc) and a mock admin user
INSERT INTO `user_details` (full_name, username, email, nic, address_line1, address_line2, contact_number, password, created_at, status, last_updated, role) VALUES
('Default User', 'uoc', 'uoc@example.com', '0000000000', 'Default Address', '', '0000000000', '$2y$10$S284Lhl9zreSujCTPLg4dONnND7BsSN1GtUcgJtuZ/EmhsTDCMko6', NOW(), 'active', NOW(), 0),
('Site Admin', 'siteadmin', 'admin@lostandfound.com', '1111111111', 'Admin Address', '', '0000000000', '$2y$10$/psBgfGTFLPDCst/K2wGSOF.RhbydRtzU/4edsUM1ZmTQAUBsXS3y', NOW(), 'active', NOW(), 1);

--
-- Indexes for dumped tables
--

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
-- AUTO_INCREMENT for table `user_details`
--
ALTER TABLE `user_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

ALTER TABLE user_details 
ADD COLUMN status VARCHAR(10) DEFAULT 'active',
ADD COLUMN last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Table for lost items
CREATE TABLE IF NOT EXISTS lost_items (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    item_name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    location VARCHAR(150) NOT NULL,
    lost_date DATE NOT NULL,
    image_path VARCHAR(255) DEFAULT NULL,
    status ENUM('pending', 'approved', 'rejected', 'found') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES user_details(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table for found items
CREATE TABLE IF NOT EXISTS found_items (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    item_name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    location VARCHAR(150) NOT NULL,
    found_date DATE NOT NULL,
    image_path VARCHAR(255) DEFAULT NULL,
    status ENUM('pending', 'approved', 'rejected', 'returned') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES user_details(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert some sample data
INSERT INTO lost_items (user_id, item_name, category, description, location, lost_date, status) VALUES
(1, 'iPhone 12 Pro', 'Electronics', 'Black iPhone 12 Pro with a blue case. Lost somewhere in the city center.', 'City Center', '2023-10-15', 'pending'),
(6, 'Wallet', 'Personal Items', 'Brown leather wallet containing ID cards and some cash.', 'Central Park', '2023-10-18', 'approved'),
(1, 'Gold Watch', 'Accessories', 'Vintage gold wristwatch with leather strap.', 'Shopping Mall', '2023-10-20', 'found');

INSERT INTO found_items (user_id, item_name, category, description, location, found_date, status) VALUES
(6, 'Backpack', 'Bags', 'Blue backpack with books and a water bottle.', 'University Library', '2023-10-16', 'pending'),
(1, 'Keys', 'Personal Items', 'Set of keys with a keychain.', 'Bus Station', '2023-10-19', 'approved'),
(6, 'Sunglasses', 'Accessories', 'Black Ray-Ban sunglasses in a case.', 'Coffee Shop', '2023-10-21', 'returned');

-- Table for item claims
CREATE TABLE IF NOT EXISTS item_claims (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    item_id INT(11) NOT NULL,
    item_type ENUM('lost', 'found') NOT NULL,
    claim_description TEXT NOT NULL,
    proof_details TEXT,
    status ENUM('pending', 'under_review', 'approved', 'rejected') DEFAULT 'pending',
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES user_details(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample claims data
INSERT INTO item_claims (user_id, item_id, item_type, claim_description, proof_details, status) VALUES
(1, 1, 'lost', 'This is my iPhone that I lost last week. I can provide the IMEI number and purchase receipt.', 'IMEI: 123456789012345, Purchase date: 2023-01-15', 'under_review'),
(6, 2, 'found', 'I believe this is my wallet. I lost it in Central Park and it contains my ID cards.', 'ID numbers matching the wallet contents', 'pending'),
(1, 4, 'found', 'This backpack looks like mine. I lost it at the library last week.', 'Description of contents matches my missing backpack', 'pending');

-- Add a resolved_date column to track when claims are resolved
ALTER TABLE item_claims 
ADD COLUMN resolved_date DATE DEFAULT NULL AFTER status;

-- Table for system settings
CREATE TABLE IF NOT EXISTS system_settings (
    id INT(11) NOT NULL AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT,
    setting_group VARCHAR(50) DEFAULT 'general',
    setting_type ENUM('text', 'number', 'boolean', 'select', 'textarea') DEFAULT 'text',
    options TEXT,
    label VARCHAR(100) NOT NULL,
    description TEXT,
    display_order INT(11) DEFAULT 0,
    is_public BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default system settings
INSERT INTO system_settings (setting_key, setting_value, setting_group, setting_type, options, label, description, display_order) VALUES
('site_name', 'Lost & Found Portal', 'general', 'text', NULL, 'Site Name', 'The name of your Lost & Found website', 1),
('site_description', 'Reuniting lost items with their owners', 'general', 'textarea', NULL, 'Site Description', 'A brief description of your website', 2),
('admin_email', 'admin@lostandfound.com', 'general', 'text', NULL, 'Admin Email', 'The primary email address for administrative communications', 3),
('items_per_page', '10', 'general', 'number', NULL, 'Items Per Page', 'Number of items to display per page in listings', 4),
('user_registration', '1', 'general', 'boolean', NULL, 'User Registration', 'Allow new users to register accounts', 5),
('require_email_verification', '1', 'general', 'boolean', NULL, 'Email Verification', 'Require email verification for new user accounts', 6),
('notify_new_items', '1', 'notifications', 'boolean', NULL, 'Notify New Items', 'Send notifications when new items are reported', 10),
('notify_new_claims', '1', 'notifications', 'boolean', NULL, 'Notify New Claims', 'Send notifications when new claims are submitted', 11),
('auto_approve_items', '0', 'moderation', 'boolean', NULL, 'Auto Approve Items', 'Automatically approve new items without manual review', 20),
('claim_approval_required', '1', 'moderation', 'boolean', NULL, 'Claim Approval Required', 'Require admin approval for item claims', 21),
('items_expire_after', '90', 'moderation', 'number', NULL, 'Items Expire After', 'Number of days after which items are automatically archived', 22),
('theme_color', '#fbb117', 'appearance', 'select', '["#fbb117", "#2196F3", "#4CAF50", "#9C27B0", "#FF9800", "#F44336"]', 'Theme Color', 'Primary color theme for the website', 30),
('logo_url', 'logo2.png', 'appearance', 'text', NULL, 'Logo URL', 'Path to the website logo image', 31),
('smtp_enabled', '0', 'email', 'boolean', NULL, 'Enable SMTP', 'Use SMTP for sending emails instead of PHP mail()', 40),
('smtp_host', '', 'email', 'text', NULL, 'SMTP Host', 'Your SMTP server hostname', 41),
('smtp_port', '587', 'email', 'number', NULL, 'SMTP Port', 'Port for SMTP connection (usually 587 for TLS)', 42),
('smtp_username', '', 'email', 'text', NULL, 'SMTP Username', 'Username for SMTP authentication', 43),
('smtp_password', '', 'email', 'text', NULL, 'SMTP Password', 'Password for SMTP authentication', 44);

-- Table for admin activity logs
CREATE TABLE IF NOT EXISTS admin_activity_logs (
    id INT(11) NOT NULL AUTO_INCREMENT,
    admin_id INT(11) NOT NULL,
    activity_type VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX admin_id (admin_id),
    INDEX activity_type (activity_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample activity logs
INSERT INTO admin_activity_logs (admin_id, activity_type, description, ip_address) VALUES
(1, 'login', 'Admin user logged in', '192.168.1.100'),
(1, 'settings_update', 'Updated site name to Lost & Found Portal', '192.168.1.100'),
(1, 'user_management', 'Deleted user account ID: 5', '192.168.1.100'),
(1, 'item_approval', 'Approved lost item ID: 3', '192.168.1.100');
