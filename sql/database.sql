-- Create the database
CREATE DATABASE attendance_management;

-- Use the database
USE attendance_management;

-- Table: users (to manage logins for admins and professors)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'professor') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: filieres (fields of study)
CREATE TABLE filieres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: students
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    filiere_id INT NOT NULL,
    FOREIGN KEY (filiere_id) REFERENCES filieres(id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: professors
CREATE TABLE professors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: modules
CREATE TABLE modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    professor_id INT NOT NULL,
    filiere_id INT NOT NULL,
    FOREIGN KEY (professor_id) REFERENCES professors(id) ON DELETE SET NULL,
    FOREIGN KEY (filiere_id) REFERENCES filieres(id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: attendance (tracks attendance for students in modules)
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    module_id INT NOT NULL,
    date DATE NOT NULL,
    status ENUM('present', 'absent') NOT NULL,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE,
    UNIQUE (student_id, module_id, date),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin user (example data)
INSERT INTO users (username, password, role)
VALUES ('admin', MD5('password123'), 'admin'); -- Replace 'password123' with a secure password

-- Insert example data for filieres
INSERT INTO filieres (name) VALUES ('Computer Science'), ('Mathematics'), ('Physics');

-- Insert example data for professors
INSERT INTO professors (name, email) VALUES 
('Dr. John Doe', 'john.doe@example.com'), 
('Dr. Jane Smith', 'jane.smith@example.com');

-- Insert example data for students
INSERT INTO students (name, email, filiere_id) VALUES 
('Alice Johnson', 'alice.johnson@example.com', 1), 
('Bob Brown', 'bob.brown@example.com', 2), 
('Charlie White', 'charlie.white@example.com', 3);

-- Insert example data for modules
INSERT INTO modules (name, professor_id, filiere_id) VALUES 
('Algorithms', 1, 1), 
('Linear Algebra', 2, 2), 
('Quantum Mechanics', 2, 3);

-- Insert example data for attendance
INSERT INTO attendance (student_id, module_id, date, status) VALUES 
(1, 1, '2024-12-01', 'present'), 
(2, 2, '2024-12-01', 'absent'), 
(3, 3, '2024-12-01', 'present');
