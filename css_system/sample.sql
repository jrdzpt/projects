
CREATE DATABASE IF NOT EXISTS customer_service_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE customer_service_db;


CREATE TABLE IF NOT EXISTS `cs_records` (
    `id`               INT(10) UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    `reference_no`     VARCHAR(30)         NOT NULL UNIQUE,
    `account_number`   VARCHAR(50)         NOT NULL,
    `account_name`     VARCHAR(150)        NOT NULL,
    `address`          TEXT                NOT NULL,
    `landmark`         VARCHAR(255)        DEFAULT NULL,
    `contact_no`       VARCHAR(20)         NOT NULL,
    `messenger_caller` VARCHAR(150)        NOT NULL,
    `concern`          VARCHAR(150)        NOT NULL,
    `area_dept`        VARCHAR(150)        NOT NULL,
    `date_forwarded`   DATE                NOT NULL,
    `notes`            TEXT                DEFAULT NULL,
    `status`           VARCHAR(100)        NOT NULL DEFAULT 'Open',
    `created_at`       TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_reference` (`reference_no`),
    INDEX `idx_status`    (`status`),
    INDEX `idx_date`      (`date_forwarded`),
    INDEX `idx_concern`   (`concern`),
    INDEX `idx_area`      (`area_dept`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `cs_records_archive` (
    `id`               INT(10) UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    `original_id`      INT(10) UNSIGNED    NOT NULL,
    `reference_no`     VARCHAR(30)         NOT NULL,
    `account_number`   VARCHAR(50)         NOT NULL,
    `account_name`     VARCHAR(150)        NOT NULL,
    `address`          TEXT                NOT NULL,
    `landmark`         VARCHAR(255)        DEFAULT NULL,
    `contact_no`       VARCHAR(20)         NOT NULL,
    `messenger_caller` VARCHAR(150)        NOT NULL,
    `concern`          VARCHAR(150)        NOT NULL,
    `area_dept`        VARCHAR(150)        NOT NULL,
    `date_forwarded`   DATE                NOT NULL,
    `notes`            TEXT                DEFAULT NULL,
    `status`           VARCHAR(100)        DEFAULT NULL,
    `created_at`       TIMESTAMP           NULL DEFAULT NULL,
    `archived_at`      TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `archived_by`      VARCHAR(100)        DEFAULT 'system',
    INDEX `idx_orig_id` (`original_id`),
    INDEX `idx_ref_no`  (`reference_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `cs_dropdown_options` (
    `id`         INT(10) UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    `category`   VARCHAR(50)         NOT NULL,
    `value`      VARCHAR(150)        NOT NULL,
    `sort_order` INT(10) UNSIGNED    DEFAULT 0,
    `is_active`  TINYINT(1)          DEFAULT 1,
    UNIQUE KEY `uq_cat_val` (`category`, `value`),
    INDEX `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `cs_users` (
    `id`            INT(10) UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    `username`      VARCHAR(60)         NOT NULL UNIQUE,
    `password_hash` VARCHAR(255)        NOT NULL,
    `full_name`     VARCHAR(150)        NOT NULL,
    `role`          ENUM('admin','staff') NOT NULL DEFAULT 'staff',
    `is_active`     TINYINT(1)          NOT NULL DEFAULT 1,
    `last_login`    TIMESTAMP           NULL DEFAULT NULL,
    `created_at`    TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `cs_audit_log` (
    `id`         INT(10) UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    `user_id`    INT(10) UNSIGNED    NOT NULL DEFAULT 0,
    `username`   VARCHAR(60)         NOT NULL DEFAULT '',
    `action`     VARCHAR(60)         NOT NULL,
    `record_id`  INT(10) UNSIGNED    NULL DEFAULT NULL,
    `details`    TEXT                DEFAULT NULL,
    `ip_address` VARCHAR(45)         DEFAULT NULL,
    `created_at` TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_username`  (`username`),
    INDEX `idx_action`    (`action`),
    INDEX `idx_record_id` (`record_id`),
    INDEX `idx_created`   (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT IGNORE INTO `cs_dropdown_options` (`category`, `value`, `sort_order`, `is_active`) VALUES
('concern', 'Net Metering',                  1,  1),
('concern', 'No Power',                      2,  1),
('concern', 'New Application Inquiry',       3,  1),
('concern', 'Follow-up',                     4,  1),
('concern', 'Bill Inquiry',                  5,  1),
('concern', 'Loose Connection',              6,  1),
('concern', 'Collection (Cheque)',           7,  1),
('concern', 'Relocation (Lines/Meter)',      8,  1),
('concern', 'Trim Trees',                    9,  1),
('concern', 'Reconnection',                 10,  1),
('concern', 'PSR Concerns',                 11,  1),
('concern', 'Change Name',                  12,  1),
('concern', 'Inspection',                   13,  1),
('concern', 'Downed Powerline',             14,  1),
('concern', 'Sparkling Lines',              15,  1),
('concern', 'Complaints on KWLT/Meter',     16,  1),
('concern', 'Busted Meter / Transformer',   17,  1),
('concern', 'Low Voltage',                  18,  1),
('concern', 'Payment Center',               19,  1),
('concern', 'Apprehended',                  20,  1),
('concern', 'Illegal Tapping',              21,  1),
('concern', 'No Bill',                      22,  1);


INSERT IGNORE INTO `cs_dropdown_options` (`category`, `value`, `sort_order`, `is_active`) VALUES
('area_dept', 'ISD',        1,  1),
('area_dept', 'AUDIT',      2,  1),
('area_dept', 'CORPLAN',    3,  1),
('area_dept', 'FSD',        4,  1),
('area_dept', 'OGM',        5,  1),
('area_dept', 'APALIT',     6,  1),
('area_dept', 'MACABEBE',   7,  1),
('area_dept', 'MASANTOL',   8,  1),
('area_dept', 'STO TOMAS',  9,  1),
('area_dept', 'MINALIN',   10,  1),
('area_dept', 'SAN SIMON', 11,  1),
('area_dept', 'CSR',       12,  1);


INSERT IGNORE INTO `cs_users` (`username`, `password_hash`, `full_name`, `role`) VALUES
('admin',  'RUN_FIXLOGIN', 'Administrator', 'admin'),
('staff1', 'RUN_FIXLOGIN', 'CS Staff One',  'staff');


-- ── Default users ─────────────────────────────────────────────────────
-- Passwords are set to a placeholder — you MUST run fixlogin.php after setup
-- OR manually update with a real hash using genhash.php
-- admin / admin123
-- staff1 / staff123

-- ══════════════════════════════════════════════════════════════════════
--  AFTER RUNNING THIS QUERY:
--
--  1. Go to http://localhost/css_system/fixlogin.php
--     This sets the correct password hashes for admin & staff1.
--
--  2. Delete fixlogin.php from your folder immediately after.
--
--  3. Log in at http://localhost/css_system/login.php
--     admin   → admin123
--     staff1  → staff123
--
--  To add a new user manually:
--    a. Visit http://localhost/css_system/genhash.php?p=YourPassword
--    b. Copy the hash output
--    c. INSERT INTO cs_users (username, password_hash, full_name, role)
--       VALUES ('newuser', 'PASTE_HASH', 'Full Name', 'staff');
--
--  To change a password:
--    a. Visit http://localhost/css_system/genhash.php?p=NewPassword
--    b. UPDATE cs_users SET password_hash='PASTE_HASH' WHERE username='x';
--
--  Roles: 'admin' or 'staff'
--  Disable user: UPDATE cs_users SET is_active=0 WHERE username='x';
-- ══════════════════════════════════════════════════════════════════════