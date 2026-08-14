/*
 * ======================================================
 *              TUTOR MODULE DATABASE SETUP
 * ======================================================
 */

-- 1. Create database (if not exists)
CREATE DATABASE IF NOT EXISTS tutor_db;

-- 2. Activate database
USE tutor_db;

-- ==================== Tutors ====================
CREATE TABLE IF NOT EXISTS tutors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    bio TEXT,
    about TEXT,
    is_first_tutor BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert the first tutor
INSERT INTO tutors (name, email, bio, about, is_first_tutor) VALUES
('Ankur Pawar', 'ankur.pawar@systango.com', 'Experienced tutor with passion for education', 'A dedicated educator with expertise in various subjects. The very first tutor on this platform.', TRUE);
