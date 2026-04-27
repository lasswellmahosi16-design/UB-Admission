-- ============================================
-- UB Online Admission System - Database Setup
-- Run this file once to set up the database
-- ============================================

CREATE DATABASE IF NOT EXISTS ub_admission;
USE ub_admission;

-- Students / Applicants table
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    date_of_birth DATE,
    gender ENUM('Male','Female','Other'),
    nationality VARCHAR(100),
    address TEXT,
    omang_passport VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Applications table
CREATE TABLE IF NOT EXISTS applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    programme VARCHAR(200) NOT NULL,
    status ENUM('draft','submitted','accepted','rejected','waitlisted') DEFAULT 'draft',
    submitted_at TIMESTAMP NULL,
    total_bgcse_points INT DEFAULT 0,
    submitted TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- BGCSE Results table (academic qualifications - locked after submission)
CREATE TABLE IF NOT EXISTS bgcse_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    subject VARCHAR(150) NOT NULL,
    grade ENUM('A*','A','B','C','D','E','U') NOT NULL,
    points INT NOT NULL,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE
);

-- Documents table
CREATE TABLE IF NOT EXISTS documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    doc_type ENUM('certificate','national_id','transcript','other') NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE
);

-- Admin users table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(200),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin account (password: admin123)
INSERT IGNORE INTO admins (username, password, full_name)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator');

-- Grade points lookup
-- A* = 9, A = 8, B = 7, C = 6, D = 5, E = 4, U = 0
