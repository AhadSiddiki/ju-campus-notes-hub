-- Create Database
CREATE DATABASE IF NOT EXISTS campus_notes_hub;
USE campus_notes_hub;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    faculty VARCHAR(255) NOT NULL,
    department VARCHAR(255) NOT NULL,
    batch VARCHAR(50) NOT NULL,
    mobile_number VARCHAR(20),
    profile_picture VARCHAR(255) DEFAULT NULL,
    is_verified BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_department (department)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories Table (Faculty > Department > Year/Semester > Course)
CREATE TABLE IF NOT EXISTS categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    faculty VARCHAR(255) NOT NULL,
    department VARCHAR(255) NOT NULL,
    year_semester VARCHAR(100),
    course_code VARCHAR(50),
    course_name VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_faculty (faculty),
    INDEX idx_department (department),
    INDEX idx_course (course_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Resources Table
CREATE TABLE IF NOT EXISTS resources (
    resource_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    file_size BIGINT NOT NULL,
    resource_type ENUM('notes', 'assignment', 'past_questions', 'lecture_slides', 'book', 'other') DEFAULT 'notes',
    downloads_count INT DEFAULT 0,
    views_count INT DEFAULT 0,
    is_approved BOOLEAN DEFAULT TRUE,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_category (category_id),
    INDEX idx_type (resource_type),
    INDEX idx_uploaded (uploaded_at),
    FULLTEXT INDEX idx_search (title, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Comments Table
CREATE TABLE IF NOT EXISTS comments (
    comment_id INT AUTO_INCREMENT PRIMARY KEY,
    resource_id INT NOT NULL,
    user_id INT NOT NULL,
    comment_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resource_id) REFERENCES resources(resource_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_resource (resource_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bookmarks Table
CREATE TABLE IF NOT EXISTS bookmarks (
    bookmark_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    resource_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (resource_id) REFERENCES resources(resource_id) ON DELETE CASCADE,
    UNIQUE KEY unique_bookmark (user_id, resource_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Downloads Table (for tracking)
CREATE TABLE IF NOT EXISTS downloads (
    download_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    resource_id INT NOT NULL,
    downloaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (resource_id) REFERENCES resources(resource_id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_resource (resource_id),
    INDEX idx_downloaded (downloaded_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Default Categories
INSERT INTO categories (faculty, department, year_semester, course_code, course_name) VALUES
-- Faculty of Arts and Humanities
('Faculty of Arts and Humanities', 'Department of Bangla', '1st Year', 'BNG-101', 'Introduction to Bangla Literature'),
('Faculty of Arts and Humanities', 'Department of English', '1st Year', 'ENG-101', 'Introduction to English Literature'),
('Faculty of Arts and Humanities', 'Department of History', '1st Year', 'HIS-101', 'History of Ancient Civilizations'),
('Faculty of Arts and Humanities', 'Department of Philosophy', '1st Year', 'PHI-101', 'Introduction to Philosophy'),

-- Faculty of Mathematical and Physical Sciences
('Faculty of Mathematical and Physical Sciences', 'Department of Chemistry', '1st Year', 'CHE-101', 'General Chemistry'),
('Faculty of Mathematical and Physical Sciences', 'Department of Computer Science and Engineering', '1st Year', 'CSE-101', 'Introduction to Programming'),
('Faculty of Mathematical and Physical Sciences', 'Department of Computer Science and Engineering', '2nd Year', 'CSE-201', 'Data Structures and Algorithms'),
('Faculty of Mathematical and Physical Sciences', 'Department of Computer Science and Engineering', '2nd Year', 'CSE-202', 'Database Management Systems'),
('Faculty of Mathematical and Physical Sciences', 'Department of Environmental Sciences', '1st Year', 'ENV-101', 'Environmental Science Fundamentals'),
('Faculty of Mathematical and Physical Sciences', 'Department of Geological Sciences', '1st Year', 'GEO-101', 'Introduction to Geology'),
('Faculty of Mathematical and Physical Sciences', 'Department of Mathematics', '1st Year', 'MAT-101', 'Calculus I'),
('Faculty of Mathematical and Physical Sciences', 'Department of Physics', '1st Year', 'PHY-101', 'Mechanics and Thermodynamics'),
('Faculty of Mathematical and Physical Sciences', 'Department of Statistics and Data Science', '1st Year', 'STA-101', 'Introduction to Statistics'),

-- Faculty of Biological Sciences
('Faculty of Biological Sciences', 'Department of Botany', '1st Year', 'BOT-101', 'Plant Biology'),
('Faculty of Biological Sciences', 'Department of Biochemistry and Molecular Biology', '1st Year', 'BMB-101', 'Biochemistry Fundamentals'),
('Faculty of Biological Sciences', 'Department of Zoology', '1st Year', 'ZOO-101', 'Animal Diversity'),
('Faculty of Biological Sciences', 'Department of Pharmacy', '1st Year', 'PHA-101', 'Pharmaceutical Chemistry');
