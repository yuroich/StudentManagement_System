-- Student Management System | Bicol University Polangui
-- Import this file in phpMyAdmin (or run: mysql -u root < database.sql)

CREATE DATABASE IF NOT EXISTS student_management
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE student_management;

CREATE TABLE IF NOT EXISTS users (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  full_name       VARCHAR(100) NOT NULL,
  email           VARCHAR(100) NOT NULL UNIQUE,
  username        VARCHAR(50)  NOT NULL UNIQUE,
  password        VARCHAR(255) NOT NULL,            -- bcrypt hash, never plain text
  role            ENUM('admin','user') NOT NULL DEFAULT 'user',
  avatar          VARCHAR(255) NULL,
  active_session  VARCHAR(128) NULL,                -- used to allow only ONE admin session
  last_activity   DATETIME NULL,
  failed_attempts INT NOT NULL DEFAULT 0,
  locked_until    DATETIME NULL,
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  -- 1 only for the admin row, NULL for users; UNIQUE => the database allows just ONE admin
  admin_flag      TINYINT GENERATED ALWAYS AS (IF(role = 'admin', 1, NULL)) STORED,
  UNIQUE KEY uq_single_admin (admin_flag)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS students (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  full_name   VARCHAR(120) NOT NULL,
  contact     VARCHAR(30)  NOT NULL DEFAULT 'N/A',
  gender      ENUM('Male','Female') NOT NULL,
  address     VARCHAR(255) NOT NULL,
  birth_date  DATE NOT NULL,
  course      VARCHAR(10) NOT NULL,
  year_level  TINYINT NOT NULL,
  block       CHAR(1) NOT NULL,
  status      VARCHAR(20) NOT NULL DEFAULT 'Regular',
  created_by  INT NULL,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_name (full_name),
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- The ONLY admin account.  username: admin   password: 1234
-- (Change the password later from Manage Account.)
INSERT INTO users (full_name, email, username, password, role)
SELECT 'Admin', 'admin@gmail.com', 'admin',
       '$2b$12$TSf/r7kIvAfm5EUkJ3QD4O91UBUU/pNHMutfAyrDAa3Ow5t1gEdZ2', 'admin'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'admin');
