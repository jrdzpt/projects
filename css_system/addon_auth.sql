-- ══════════════════════════════════════════════════════════════════════
--  PELCO III — Customer Service System
--  ADD-ON: User Login + Audit Trail
--  Run this in phpMyAdmin > SQL tab (safe to run on existing install)
-- ══════════════════════════════════════════════════════════════════════

USE customer_service_db;


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


INSERT IGNORE INTO `cs_users` (`username`, `password_hash`, `full_name`, `role`) VALUES
('admin', 'admin123', 'Administrator', 'admin');

INSERT IGNORE INTO `cs_users` (`username`, `password_hash`, `full_name`, `role`) VALUES
('staff1', 'staff123', 'CS Staff One', 'staff');
