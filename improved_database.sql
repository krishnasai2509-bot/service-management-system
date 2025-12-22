-- =====================================================
-- IMPROVED DATABASE SCHEMA BASED ON ER DIAGRAM
-- Service Booking System (Task Manager)
-- =====================================================

-- Drop database if exists and create fresh
DROP DATABASE IF EXISTS task_manager;
CREATE DATABASE task_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE task_manager;

-- =====================================================
-- TABLE: Admin
-- =====================================================
CREATE TABLE admin (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_admin_email (email)
) ENGINE=InnoDB;

-- =====================================================
-- TABLE: Service_category
-- =====================================================
CREATE TABLE service_category (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    managed_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (managed_by) REFERENCES admin(admin_id) ON DELETE SET NULL,
    INDEX idx_category_name (category_name)
) ENGINE=InnoDB;

-- =====================================================
-- TABLE: Customer
-- =====================================================
CREATE TABLE customer (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    street VARCHAR(255),
    city VARCHAR(100),
    pincode VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_customer_email (email),
    INDEX idx_customer_phone (phone),
    INDEX idx_customer_city (city)
) ENGINE=InnoDB;

-- =====================================================
-- TABLE: Worker
-- =====================================================
CREATE TABLE worker (
    worker_id INT AUTO_INCREMENT PRIMARY KEY,
    worker_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone_no VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    skill_type VARCHAR(100),
    experience_years DECIMAL(4,1) DEFAULT 0,
    availability_status ENUM('available', 'busy', 'offline') DEFAULT 'available',
    rating DECIMAL(3,2) DEFAULT 0.00,
    street VARCHAR(255),
    city VARCHAR(100),
    pincode VARCHAR(10),
    category_id INT,
    assigned_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES service_category(category_id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_by) REFERENCES admin(admin_id) ON DELETE SET NULL,
    INDEX idx_worker_email (email),
    INDEX idx_worker_category (category_id),
    INDEX idx_worker_status (availability_status),
    INDEX idx_worker_rating (rating),
    CHECK (rating >= 0 AND rating <= 5),
    CHECK (experience_years >= 0)
) ENGINE=InnoDB;

-- =====================================================
-- TABLE: Availability
-- =====================================================
CREATE TABLE availability (
    slot_id INT AUTO_INCREMENT PRIMARY KEY,
    worker_id INT NOT NULL,
    available_date DATE NOT NULL,
    available_time TIME NOT NULL,
    status ENUM('available', 'booked', 'unavailable') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (worker_id) REFERENCES worker(worker_id) ON DELETE CASCADE,
    INDEX idx_availability_worker (worker_id),
    INDEX idx_availability_date (available_date),
    INDEX idx_availability_status (status),
    UNIQUE KEY unique_worker_slot (worker_id, available_date, available_time)
) ENGINE=InnoDB;

-- =====================================================
-- TABLE: Booking
-- =====================================================
CREATE TABLE booking (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    worker_id INT,
    category_id INT,
    slot_id INT,
    service_description TEXT,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    service_date DATE,
    service_time TIME,
    status ENUM('pending', 'confirmed', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    total_amount DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customer(customer_id) ON DELETE CASCADE,
    FOREIGN KEY (worker_id) REFERENCES worker(worker_id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES service_category(category_id) ON DELETE SET NULL,
    FOREIGN KEY (slot_id) REFERENCES availability(slot_id) ON DELETE SET NULL,
    INDEX idx_booking_customer (customer_id),
    INDEX idx_booking_worker (worker_id),
    INDEX idx_booking_category (category_id),
    INDEX idx_booking_status (status),
    INDEX idx_booking_service_date (service_date)
) ENGINE=InnoDB;

-- =====================================================
-- TABLE: Payment
-- =====================================================
CREATE TABLE payment (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL UNIQUE,
    payment_method ENUM('cash', 'credit_card', 'debit_card', 'upi', 'net_banking') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    transaction_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES booking(booking_id) ON DELETE CASCADE,
    INDEX idx_payment_booking (booking_id),
    INDEX idx_payment_status (status),
    INDEX idx_payment_date (payment_date),
    CHECK (amount >= 0)
) ENGINE=InnoDB;

-- =====================================================
-- TABLE: Feedback
-- =====================================================
CREATE TABLE feedback (
    feedback_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL UNIQUE,
    rating DECIMAL(3,2) NOT NULL,
    comments TEXT,
    feedback_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES booking(booking_id) ON DELETE CASCADE,
    INDEX idx_feedback_booking (booking_id),
    INDEX idx_feedback_rating (rating),
    INDEX idx_feedback_date (feedback_date),
    CHECK (rating >= 0 AND rating <= 5)
) ENGINE=InnoDB;

-- =====================================================
-- SAMPLE DATA
-- =====================================================

-- Insert Admin users
INSERT INTO admin (name, email, password, phone) VALUES
('Super Admin', 'admin@taskmanager.com', 'password123', '1234567890'),
('Manager One', 'manager1@taskmanager.com', 'password123', '1234567891'),
('Manager Two', 'manager2@taskmanager.com', 'password123', '1234567892');

-- Insert Service Categories
INSERT INTO service_category (category_name, description, managed_by) VALUES
('Plumbing', 'All plumbing related services including pipe repair, installation, and maintenance', 1),
('Electrical', 'Electrical services including wiring, repair, and installation', 1),
('Carpentry', 'Woodwork, furniture repair, and custom carpentry services', 2),
('Cleaning', 'Home and office cleaning services', 2),
('Painting', 'Interior and exterior painting services', 1),
('HVAC', 'Heating, ventilation, and air conditioning services', 3);

-- Insert Customers
INSERT INTO customer (name, email, phone, password, street, city, pincode) VALUES
('John Doe', 'john.doe@email.com', '9876543210', 'password123', '123 Main Street', 'New York', '10001'),
('Jane Smith', 'jane.smith@email.com', '9876543211', 'password123', '456 Oak Avenue', 'Los Angeles', '90001'),
('Mike Johnson', 'mike.j@email.com', '9876543212', 'password123', '789 Pine Road', 'Chicago', '60601'),
('Sarah Williams', 'sarah.w@email.com', '9876543213', 'password123', '321 Elm Street', 'Houston', '77001'),
('David Brown', 'david.b@email.com', '9876543214', 'password123', '654 Maple Drive', 'Phoenix', '85001');

-- Insert Workers
INSERT INTO worker (worker_name, email, phone_no, password, skill_type, experience_years, availability_status, rating, street, city, pincode, category_id, assigned_by) VALUES
('Robert Plumber', 'robert.p@worker.com', '8888888881', 'password123', 'Licensed Plumber', 5.0, 'available', 4.50, '111 Worker Lane', 'New York', '10002', 1, 1),
('Tom Electric', 'tom.e@worker.com', '8888888882', 'password123', 'Certified Electrician', 7.5, 'available', 4.80, '222 Service Road', 'New York', '10003', 2, 1),
('Chris Carpenter', 'chris.c@worker.com', '8888888883', 'password123', 'Master Carpenter', 10.0, 'busy', 4.90, '333 Craft Street', 'Los Angeles', '90002', 3, 2),
('Lisa Cleaner', 'lisa.c@worker.com', '8888888884', 'password123', 'Professional Cleaner', 3.0, 'available', 4.60, '444 Clean Ave', 'Chicago', '60602', 4, 2),
('Paul Painter', 'paul.p@worker.com', '8888888885', 'password123', 'Professional Painter', 6.0, 'available', 4.40, '555 Color Street', 'Houston', '77002', 5, 1),
('Mark HVAC', 'mark.h@worker.com', '8888888886', 'password123', 'HVAC Technician', 8.0, 'offline', 4.70, '666 Climate Road', 'Phoenix', '85002', 6, 3);

-- Insert Availability Slots
INSERT INTO availability (worker_id, available_date, available_time, status) VALUES
(1, '2025-12-01', '09:00:00', 'available'),
(1, '2025-12-01', '14:00:00', 'available'),
(1, '2025-12-02', '10:00:00', 'available'),
(2, '2025-12-01', '08:00:00', 'available'),
(2, '2025-12-01', '15:00:00', 'booked'),
(2, '2025-12-02', '09:00:00', 'available'),
(3, '2025-12-01', '10:00:00', 'unavailable'),
(4, '2025-12-01', '11:00:00', 'available'),
(4, '2025-12-02', '14:00:00', 'available'),
(5, '2025-12-01', '13:00:00', 'available'),
(5, '2025-12-03', '10:00:00', 'available'),
(6, '2025-12-01', '09:00:00', 'available');

-- Insert Bookings
INSERT INTO booking (customer_id, worker_id, slot_id, service_description, service_date, service_time, status, total_amount) VALUES
(1, 1, 1, 'Fix leaking pipe in bathroom', '2025-12-01', '09:00:00', 'confirmed', 150.00),
(2, 2, 5, 'Install ceiling fan in bedroom', '2025-12-01', '15:00:00', 'in_progress', 200.00),
(3, 4, 8, 'Deep cleaning of 2BHK apartment', '2025-12-01', '11:00:00', 'pending', 350.00),
(4, 5, 10, 'Paint living room walls', '2025-12-01', '13:00:00', 'confirmed', 500.00),
(1, 2, 6, 'Repair electrical outlet', '2025-12-02', '09:00:00', 'pending', 100.00),
(5, 1, 3, 'Install new water heater', '2025-12-02', '10:00:00', 'completed', 800.00);

-- Insert Payments
INSERT INTO payment (booking_id, payment_method, amount, status, transaction_id) VALUES
(1, 'upi', 150.00, 'completed', 'TXN001234567890'),
(2, 'credit_card', 200.00, 'completed', 'TXN001234567891'),
(3, 'cash', 350.00, 'pending', NULL),
(4, 'net_banking', 500.00, 'completed', 'TXN001234567892'),
(5, 'debit_card', 100.00, 'pending', NULL),
(6, 'upi', 800.00, 'completed', 'TXN001234567893');

-- Insert Feedback
INSERT INTO feedback (booking_id, rating, comments) VALUES
(1, 4.50, 'Great service! Fixed the leak quickly and professionally.'),
(2, 4.80, 'Excellent work. Very skilled electrician.'),
(4, 4.00, 'Good job overall. Arrived on time and completed the work as expected.'),
(6, 5.00, 'Outstanding service! Very professional and the installation was perfect.');

-- =====================================================
-- VIEWS FOR COMMON QUERIES
-- =====================================================

-- View: Active Workers with Category Info
CREATE VIEW active_workers_view AS
SELECT
    w.worker_id,
    w.worker_name,
    w.email,
    w.phone_no,
    w.skill_type,
    w.experience_years,
    w.availability_status,
    w.rating,
    sc.category_name,
    sc.description as category_description,
    CONCAT(w.street, ', ', w.city, ', ', w.pincode) as full_address
FROM worker w
LEFT JOIN service_category sc ON w.category_id = sc.category_id
WHERE w.availability_status != 'offline';

-- View: Booking Details
CREATE VIEW booking_details_view AS
SELECT
    b.booking_id,
    c.name as customer_name,
    c.email as customer_email,
    c.phone as customer_phone,
    w.worker_name,
    w.phone_no as worker_phone,
    sc.category_name,
    b.service_description,
    b.service_date,
    b.service_time,
    b.status as booking_status,
    b.total_amount,
    p.payment_method,
    p.status as payment_status,
    f.rating as feedback_rating,
    f.comments as feedback_comments
FROM booking b
JOIN customer c ON b.customer_id = c.customer_id
JOIN worker w ON b.worker_id = w.worker_id
LEFT JOIN service_category sc ON w.category_id = sc.category_id
LEFT JOIN payment p ON b.booking_id = p.booking_id
LEFT JOIN feedback f ON b.booking_id = f.feedback_id;

-- View: Worker Performance
CREATE VIEW worker_performance_view AS
SELECT
    w.worker_id,
    w.worker_name,
    w.email,
    sc.category_name,
    w.rating as worker_rating,
    COUNT(DISTINCT b.booking_id) as total_bookings,
    COUNT(DISTINCT CASE WHEN b.status = 'completed' THEN b.booking_id END) as completed_bookings,
    AVG(f.rating) as average_feedback_rating,
    SUM(b.total_amount) as total_revenue
FROM worker w
LEFT JOIN service_category sc ON w.category_id = sc.category_id
LEFT JOIN booking b ON w.worker_id = b.worker_id
LEFT JOIN feedback f ON b.booking_id = f.booking_id
GROUP BY w.worker_id, w.worker_name, w.email, sc.category_name, w.rating;

-- =====================================================
-- STORED PROCEDURES
-- =====================================================

DELIMITER //

-- Procedure to update worker rating based on feedback
CREATE PROCEDURE update_worker_rating(IN p_worker_id INT)
BEGIN
    DECLARE avg_rating DECIMAL(3,2);

    SELECT AVG(f.rating) INTO avg_rating
    FROM feedback f
    JOIN booking b ON f.booking_id = b.booking_id
    WHERE b.worker_id = p_worker_id;

    IF avg_rating IS NOT NULL THEN
        UPDATE worker
        SET rating = avg_rating
        WHERE worker_id = p_worker_id;
    END IF;
END//

-- Procedure to complete a booking
CREATE PROCEDURE complete_booking(IN p_booking_id INT)
BEGIN
    DECLARE v_worker_id INT;

    UPDATE booking
    SET status = 'completed'
    WHERE booking_id = p_booking_id;

    SELECT worker_id INTO v_worker_id
    FROM booking
    WHERE booking_id = p_booking_id;

    IF v_worker_id IS NOT NULL THEN
        CALL update_worker_rating(v_worker_id);
    END IF;
END//

DELIMITER ;

-- =====================================================
-- TRIGGERS
-- =====================================================

DELIMITER //

-- Trigger to update slot status when booking is created
CREATE TRIGGER after_booking_insert
AFTER INSERT ON booking
FOR EACH ROW
BEGIN
    IF NEW.slot_id IS NOT NULL THEN
        UPDATE availability
        SET status = 'booked'
        WHERE slot_id = NEW.slot_id;
    END IF;
END//

-- Trigger to update slot status when booking is cancelled
CREATE TRIGGER after_booking_cancel
AFTER UPDATE ON booking
FOR EACH ROW
BEGIN
    IF NEW.status = 'cancelled' AND OLD.status != 'cancelled' AND NEW.slot_id IS NOT NULL THEN
        UPDATE availability
        SET status = 'available'
        WHERE slot_id = NEW.slot_id;
    END IF;
END//

-- Trigger to update worker rating after feedback insert
CREATE TRIGGER after_feedback_insert
AFTER INSERT ON feedback
FOR EACH ROW
BEGIN
    DECLARE v_worker_id INT;

    SELECT worker_id INTO v_worker_id
    FROM booking
    WHERE booking_id = NEW.booking_id;

    IF v_worker_id IS NOT NULL THEN
        CALL update_worker_rating(v_worker_id);
    END IF;
END//

DELIMITER ;

-- =====================================================
-- INDEXES FOR PERFORMANCE OPTIMIZATION
-- =====================================================
-- Additional composite indexes for common queries
CREATE INDEX idx_worker_category_status ON worker(category_id, availability_status);
CREATE INDEX idx_booking_customer_status ON booking(customer_id, status);
CREATE INDEX idx_booking_worker_status ON booking(worker_id, status);
CREATE INDEX idx_availability_date_status ON availability(available_date, status);

-- =====================================================
-- GRANT PERMISSIONS (Optional - uncomment if needed)
-- =====================================================
-- GRANT ALL PRIVILEGES ON task_manager.* TO 'root'@'localhost';
-- FLUSH PRIVILEGES;

-- =====================================================
-- DATABASE CREATION COMPLETED
-- =====================================================
SELECT 'Database created successfully!' as Status;
