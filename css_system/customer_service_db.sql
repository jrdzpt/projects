-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 10, 2026 at 08:34 AM
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
-- Database: `customer_service_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `cs_dropdown_options`
--

CREATE TABLE `cs_dropdown_options` (
  `id` int(10) UNSIGNED NOT NULL,
  `category` varchar(50) NOT NULL,
  `value` varchar(150) NOT NULL,
  `sort_order` int(10) UNSIGNED DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cs_dropdown_options`
--

INSERT INTO `cs_dropdown_options` (`id`, `category`, `value`, `sort_order`, `is_active`) VALUES
(13, 'area_dept', 'ISD', 1, 1),
(14, 'area_dept', 'AUDIT', 2, 1),
(15, 'area_dept', 'CORPLAN', 3, 1),
(16, 'area_dept', 'FSD', 4, 1),
(17, 'area_dept', 'OGM', 5, 1),
(18, 'area_dept', 'APALIT', 6, 1),
(19, 'area_dept', 'MACABEBE', 7, 1),
(20, 'area_dept', 'MASANTOL', 8, 1),
(21, 'area_dept', 'STO TOMAS', 9, 1),
(22, 'area_dept', 'MINALIN', 10, 1),
(23, 'area_dept', 'SAN SIMON', 11, 1),
(44, 'concern', 'Net Metering', 1, 1),
(45, 'concern', 'No Power', 2, 1),
(46, 'concern', 'New Application Inquiry', 3, 1),
(47, 'concern', 'Follow-up', 4, 1),
(48, 'concern', 'Bill Inquiry', 5, 1),
(49, 'concern', 'Loose Connection', 6, 1),
(50, 'concern', 'Collection (Cheque)', 7, 1),
(51, 'concern', 'Relocation (Lines/Meter)', 8, 1),
(52, 'concern', 'Trim Trees', 9, 1),
(53, 'concern', 'Reconnection', 10, 1),
(54, 'concern', 'PSR Concerns', 11, 1),
(55, 'concern', 'Change Name', 12, 1),
(56, 'concern', 'Inspection', 13, 1),
(57, 'concern', 'Downed Powerline', 14, 1),
(58, 'concern', 'Sparkling Lines', 15, 1),
(59, 'concern', 'Complaints on KWLT/Meter', 16, 1),
(60, 'concern', 'Busted Meter / Transformer', 17, 1),
(61, 'concern', 'Low Voltage', 18, 1),
(62, 'concern', 'Payment Center', 19, 1),
(63, 'concern', 'Apprehended', 20, 1),
(64, 'concern', 'Illegal Tapping', 21, 1),
(66, 'area_dept', 'CSR', 12, 1),
(68, 'concern', 'No Bill', 23, 1);

-- --------------------------------------------------------

--
-- Table structure for table `cs_records`
--

CREATE TABLE `cs_records` (
  `id` int(10) UNSIGNED NOT NULL,
  `reference_no` varchar(30) NOT NULL,
  `account_number` varchar(50) NOT NULL,
  `account_name` varchar(150) NOT NULL,
  `address` text NOT NULL,
  `landmark` varchar(255) DEFAULT NULL,
  `contact_no` varchar(20) NOT NULL,
  `messenger_caller` varchar(150) NOT NULL,
  `concern` varchar(150) NOT NULL,
  `area_dept` varchar(150) NOT NULL,
  `date_forwarded` date NOT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(100) NOT NULL DEFAULT 'Open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cs_records`
--

INSERT INTO `cs_records` (`id`, `reference_no`, `account_number`, `account_name`, `address`, `landmark`, `contact_no`, `messenger_caller`, `concern`, `area_dept`, `date_forwarded`, `notes`, `status`, `created_at`, `updated_at`) VALUES
(1, '123456', '12345678', 'Juan dela Cruz', '123 Rizal St., Brgy. Poblacion, Quezon City', 'Near SM City', '09171234567', 'Maria Santos', '', 'ISD', '2026-03-05', 'rr', 'Closed', '2026-03-05 06:06:22', '2026-03-10 03:20:24'),
(2, '100326', '01234560', 'Sample Name One', '103 Apalit, Pampanga', 'Near Robinson', '09676048097', 'Sample Caller', '', 'CSR', '2026-03-10', '', 'Open', '2026-03-10 01:50:45', '2026-03-10 03:20:24'),
(5, 'CC031026001', '06121701', 'LUZANO, ROBINSON', 'SN ROQUE, MACABEBE', '', '09000000000', 'Tracy Manansala Lozano - FB', 'other concern', 'STO TOMAS', '2026-03-10', '', 'Open', '2026-03-10 02:02:07', '2026-03-10 05:31:37');

-- --------------------------------------------------------

--
-- Table structure for table `cs_records_archive`
--

CREATE TABLE `cs_records_archive` (
  `id` int(10) UNSIGNED NOT NULL,
  `original_id` int(10) UNSIGNED NOT NULL,
  `reference_no` varchar(30) NOT NULL,
  `account_number` varchar(50) NOT NULL,
  `account_name` varchar(150) NOT NULL,
  `address` text NOT NULL,
  `landmark` varchar(255) DEFAULT NULL,
  `contact_no` varchar(20) NOT NULL,
  `messenger_caller` varchar(150) NOT NULL,
  `concern` varchar(150) NOT NULL,
  `area_dept` varchar(150) NOT NULL,
  `date_forwarded` date NOT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `archived_by` varchar(100) DEFAULT 'system'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cs_records_archive`
--

INSERT INTO `cs_records_archive` (`id`, `original_id`, `reference_no`, `account_number`, `account_name`, `address`, `landmark`, `contact_no`, `messenger_caller`, `concern`, `area_dept`, `date_forwarded`, `notes`, `status`, `created_at`, `archived_at`, `archived_by`) VALUES
(3, 6, '12345671', '67890568', 'sample two', '113 Apalit, Pampanga', 'Near SM', '09171234560', 'Maria', 'Trim Trees', 'other dept', '2026-03-10', '', 'Open', '2026-03-10 02:58:16', '2026-03-10 03:29:16', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cs_dropdown_options`
--
ALTER TABLE `cs_dropdown_options`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_cat_val` (`category`,`value`),
  ADD KEY `idx_category` (`category`);

--
-- Indexes for table `cs_records`
--
ALTER TABLE `cs_records`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference_no` (`reference_no`),
  ADD KEY `idx_reference` (`reference_no`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_date` (`date_forwarded`),
  ADD KEY `idx_concern` (`concern`),
  ADD KEY `idx_area` (`area_dept`);

--
-- Indexes for table `cs_records_archive`
--
ALTER TABLE `cs_records_archive`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_orig_id` (`original_id`),
  ADD KEY `idx_ref_no` (`reference_no`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cs_dropdown_options`
--
ALTER TABLE `cs_dropdown_options`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `cs_records`
--
ALTER TABLE `cs_records`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `cs_records_archive`
--
ALTER TABLE `cs_records_archive`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
