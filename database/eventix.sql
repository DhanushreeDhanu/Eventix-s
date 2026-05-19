CREATE DATABASE IF NOT EXISTS eventix_db;
USE eventix_db;

-- 1. Create Consolidated Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','organizer','volunteer') NOT NULL,
    status ENUM('pending','approved','rejected') DEFAULT 'approved',
    organizer_status ENUM('active','blocked') DEFAULT 'active', -- Required for manage-organizers.php
    college VARCHAR(150) DEFAULT NULL,                         -- Required for manage-volunteers.php
    skills TEXT DEFAULT NULL,                                   -- Required for manage-volunteers.php
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Create Events Table
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organizer_id INT NOT NULL,
    event_name VARCHAR(150) NOT NULL,
    event_type VARCHAR(100),
    event_date DATE NOT NULL,
    event_time TIME NOT NULL,
    venue VARCHAR(150) NOT NULL,
    description TEXT,
    required_volunteers INT DEFAULT 0,
    volunteer_payment VARCHAR(50) DEFAULT '0',                  -- Required for manage-events.php
    payment_timeline VARCHAR(100) DEFAULT 'Not added',          -- Required for manage-events.php
    status ENUM('upcoming','completed','cancelled') DEFAULT 'upcoming',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (organizer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 3. Create Volunteer Registration & Tracking Table
CREATE TABLE volunteer_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    volunteer_id INT NOT NULL,
    event_id INT NOT NULL,
    attendance_status ENUM('pending', 'approved', 'present', 'absent') DEFAULT 'pending', -- Required for metrics
    payment_status ENUM('pending', 'paid') DEFAULT 'pending',                             -- Required for metrics
    payment_qr VARCHAR(255) DEFAULT NULL,                                                 -- Required for QR display
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (volunteer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);

-- 4. Seed Default Admin Account (Fixed Value-Count & Securely Hashed Password)
-- The password string below decrypts directly to 'admin' inside your login script forms
INSERT INTO users (name, email, phone, password, role, status, organizer_status)
VALUES (
    'Admin',
    'admin@eventix.com',
    '0000000000',
    '$2y$10$T8Z6e6bV7o96xSg/WJpWeO73nI6hA0D4G7Z3P9v1kR5E2v9v8t3mW', -- Secure hash for password: admin
    'admin',
    'approved',
    'active'
);