USE eventix_db;

ALTER TABLE events
  ADD COLUMN IF NOT EXISTS end_time TIME NULL AFTER event_time,
  ADD COLUMN IF NOT EXISTS google_map_link VARCHAR(255) NULL AFTER venue,
  ADD COLUMN IF NOT EXISTS contact_person VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS contact_phone VARCHAR(20) NULL,
  ADD COLUMN IF NOT EXISTS volunteer_payment DECIMAL(10,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS payment_timeline VARCHAR(80) DEFAULT 'Within 2-3 days',
  ADD COLUMN IF NOT EXISTS payment_method VARCHAR(80) DEFAULT 'UPI Transfer',
  ADD COLUMN IF NOT EXISTS volunteer_duties TEXT NULL,
  ADD COLUMN IF NOT EXISTS instructions TEXT NULL;

ALTER TABLE volunteer_events
  ADD COLUMN IF NOT EXISTS attendance_status ENUM('pending','present','absent') DEFAULT 'pending',
  ADD COLUMN IF NOT EXISTS payment_status ENUM('pending','paid') DEFAULT 'pending',
  ADD COLUMN IF NOT EXISTS volunteer_qr VARCHAR(255) NULL,
  ADD COLUMN IF NOT EXISTS status ENUM('joined','cancelled','removed') DEFAULT 'joined',
  ADD COLUMN IF NOT EXISTS attendance_marked_at DATETIME NULL,
  ADD COLUMN IF NOT EXISTS payment_marked_at DATETIME NULL;

CREATE TABLE IF NOT EXISTS notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  role ENUM('admin','organizer','volunteer') NULL,
  title VARCHAR(150) NOT NULL,
  message TEXT NOT NULL,
  is_read TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX(user_id), INDEX(role)
);

CREATE UNIQUE INDEX IF NOT EXISTS uniq_volunteer_event ON volunteer_events(volunteer_id, event_id);
