-- Task Manager Database Schema
-- Create database
CREATE DATABASE IF NOT EXISTS task_manager;
USE task_manager;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    user_type ENUM('customer', 'worker', 'admin') NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    assigned_to INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert sample users (password is stored as plain text 'password123')
INSERT INTO users (username, email, password, full_name, user_type, phone, address) VALUES
('admin', 'admin@taskmanager.com', 'password123', 'Admin User', 'admin', '1234567890', '123 Admin Street'),
('worker1', 'worker@taskmanager.com', 'password123', 'John Worker', 'worker', '9876543210', '456 Worker Lane'),
('customer1', 'customer@taskmanager.com', 'password123', 'Jane Customer', 'customer', '5555555555', '789 Customer Road');

-- Insert sample tasks
INSERT INTO tasks (user_id, title, description, status, priority, assigned_to) VALUES
(3, 'Complete project documentation', 'Write comprehensive documentation for the task manager project', 'in_progress', 'high', 2),
(3, 'Review code', 'Perform code review for the latest features', 'pending', 'medium', 2),
(1, 'Fix responsive design', 'Ensure the application works well on mobile devices', 'pending', 'high', 2),
(1, 'Setup backup system', 'Implement automated database backup', 'completed', 'low', NULL);
