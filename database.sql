-- NEU Library Visitor Log Database
-- Run this SQL file in phpMyAdmin or MySQL CLI


-- ── VISIT LOG TABLE ──────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS visit_log (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(120)  NOT NULL,
    rfid          VARCHAR(100)  NOT NULL,
    program       VARCHAR(100)  NOT NULL,
    reason        VARCHAR(80)   NOT NULL,
    timestamp     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_rfid      (rfid),
    INDEX idx_timestamp (timestamp)
) ENGINE=InnoDB;

-- ── BLOCKED VISITORS TABLE ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS blocked_visitors (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    rfid        VARCHAR(100)  NOT NULL UNIQUE,
    name        VARCHAR(120)  NOT NULL,
    program     VARCHAR(100)  DEFAULT NULL,
    block_reason VARCHAR(200) NOT NULL DEFAULT 'Admin restricted',
    blocked_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── ADMIN ACCOUNTS TABLE ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS admins (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(60)  NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL   -- stored as bcrypt hash
) ENGINE=InnoDB;

-- Default admin: username=admin  password=admin123
INSERT INTO admins (username, password)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')
ON DUPLICATE KEY UPDATE username = username;
-- NOTE: The hash above is bcrypt of "admin123".
--       Generate your own with: password_hash('yourpassword', PASSWORD_BCRYPT)

-- ── SEED DEMO VISIT DATA ─────────────────────────────────────────────────────
INSERT INTO visit_log (name, rfid, program, reason, timestamp) VALUES
('Angel Grace B. Jordan',      '24-13429-685',        'BSIT',         'Reading',          NOW() - INTERVAL 1  HOUR),
('Al Christian R. Limos',    '24-10845-559',        'BSIT',         'Researching',      NOW() - INTERVAL 8  HOUR),
('Ana Reyes',         'faculty@neu.edu.ph','Faculty – CCS','Meeting',          NOW() - INTERVAL 15 HOUR),
('Rose Villanueva',   '2024-00404',        'BSECE',        'Borrowing Books',  NOW() - INTERVAL 29 HOUR),
('Rose Villanueva',   '2024-00404',        'BSECE',        'Reading',          NOW() - INTERVAL 78 HOUR),       
('Juan Dela Cruz',    '2023-00202',        'BSCS',         'Other',            NOW() - INTERVAL 130 HOUR);