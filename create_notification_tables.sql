-- Create notification tables for real-time notifications system
-- Run this in phpMyAdmin or MySQL Workbench

-- Table for storing user notifications
CREATE TABLE IF NOT EXISTS user_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    refno VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    status VARCHAR(50) NOT NULL,
    request_type VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_read TINYINT(1) DEFAULT 0,
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
);

-- Table for storing request status snapshots for change detection
CREATE TABLE IF NOT EXISTS user_request_snapshots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    refno VARCHAR(50) NOT NULL,
    request_type VARCHAR(100) NOT NULL,
    last_status VARCHAR(50) NOT NULL,
    last_checked TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_refno (user_id, refno),
    INDEX idx_user_id (user_id),
    INDEX idx_last_checked (last_checked)
);

-- Table for storing account status snapshots
CREATE TABLE IF NOT EXISTS user_account_status_snapshot (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    account_status VARCHAR(50) NOT NULL,
    last_checked TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_account (user_id),
    INDEX idx_user_id (user_id)
);