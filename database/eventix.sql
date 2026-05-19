CREATE DATABASE IF NOT EXISTS eventix_db;
USE eventix_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','organizer') NOT NULL,
    status ENUM('pending','approved','rejected') DEFAULT 'approved',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS volunteers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    college VARCHAR(150),
    skills TEXT,
    payment_qr VARCHAR(255),
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organizer_id INT NOT NULL,
    event_name VARCHAR(150) NOT NULL,
    event_type VARCHAR(100),
    event_date DATE NOT NULL,
    event_time TIME NOT NULL,
    venue VARCHAR(150) NOT NULL,
    google_map_link TEXT,
    description TEXT,
    required_volunteers INT DEFAULT 0,
    contact_person VARCHAR(100),
    contact_phone VARCHAR(20),
    volunteer_payment DECIMAL(10,2) DEFAULT 0,
    payment_timeline VARCHAR(100),
    payment_method VARCHAR(100),
    volunteer_duties TEXT,
    instructions TEXT,
    status ENUM('upcoming','completed','cancelled') DEFAULT 'upcoming',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (organizer_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS volunteer_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    volunteer_id INT NOT NULL,
    event_id INT NOT NULL,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    attendance_status ENUM('pending','approved','rejected') DEFAULT 'pending',
    payment_status ENUM('pending','paid') DEFAULT 'pending',
    UNIQUE KEY unique_volunteer_event (volunteer_id, event_id),
    FOREIGN KEY (volunteer_id) REFERENCES volunteers(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);

INSERT INTO users (name, email, phone, password, role, status)
VALUES (
    'Admin',
    'admin@eventix.com',
    '0000000000',
    '$2y$10$wH3XkBlyjAUn8KbMxURXOO4grhZiS0lbp6Ufh9svKs1lXbEJmE2Wq',
    'admin',
    'approved'
);