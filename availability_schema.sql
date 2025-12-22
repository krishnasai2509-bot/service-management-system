-- =====================================================
-- WORKER AVAILABILITY ENHANCEMENT SCHEMA
-- Run this after improved_database.sql
-- =====================================================

USE task_manager;

-- =====================================================
-- TABLE: Worker Default Availability (Weekly Schedule)
-- Stores the regular working hours for each worker
-- =====================================================
CREATE TABLE IF NOT EXISTS worker_default_availability (
    default_availability_id INT AUTO_INCREMENT PRIMARY KEY,
    worker_id INT NOT NULL,
    day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (worker_id) REFERENCES worker(worker_id) ON DELETE CASCADE,
    INDEX idx_worker_day (worker_id, day_of_week),
    UNIQUE KEY unique_worker_day_time (worker_id, day_of_week, start_time, end_time)
) ENGINE=InnoDB;

-- =====================================================
-- TABLE: Worker Unavailability Exceptions
-- Specific dates/times when worker is NOT available
-- =====================================================
CREATE TABLE IF NOT EXISTS worker_unavailability (
    unavailability_id INT AUTO_INCREMENT PRIMARY KEY,
    worker_id INT NOT NULL,
    unavailable_date DATE NOT NULL,
    unavailable_start_time TIME NOT NULL,
    unavailable_end_time TIME NOT NULL,
    reason VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (worker_id) REFERENCES worker(worker_id) ON DELETE CASCADE,
    INDEX idx_worker_unavailable_date (worker_id, unavailable_date),
    INDEX idx_unavailable_date (unavailable_date)
) ENGINE=InnoDB;

-- =====================================================
-- STORED PROCEDURE: Check Worker Availability
-- Returns whether a worker is available at specific date/time
-- =====================================================
DELIMITER //

DROP PROCEDURE IF EXISTS check_worker_availability//

CREATE PROCEDURE check_worker_availability(
    IN p_worker_id INT,
    IN p_service_date DATE,
    IN p_service_time TIME,
    OUT p_is_available BOOLEAN
)
BEGIN
    DECLARE v_day_of_week VARCHAR(20);
    DECLARE v_default_available INT;
    DECLARE v_is_unavailable INT;
    DECLARE v_is_booked INT;

    -- Get day of week
    SET v_day_of_week = DAYNAME(p_service_date);

    -- Check if worker has default availability for this day/time
    SELECT COUNT(*) INTO v_default_available
    FROM worker_default_availability
    WHERE worker_id = p_worker_id
      AND day_of_week = v_day_of_week
      AND is_available = TRUE
      AND p_service_time BETWEEN start_time AND end_time;

    -- Check if worker marked this specific date/time as unavailable
    SELECT COUNT(*) INTO v_is_unavailable
    FROM worker_unavailability
    WHERE worker_id = p_worker_id
      AND unavailable_date = p_service_date
      AND p_service_time BETWEEN unavailable_start_time AND unavailable_end_time;

    -- Check if already booked
    SELECT COUNT(*) INTO v_is_booked
    FROM booking
    WHERE worker_id = p_worker_id
      AND service_date = p_service_date
      AND service_time = p_service_time
      AND status NOT IN ('cancelled');

    -- Worker is available if they have default availability AND not marked unavailable AND not booked
    IF v_default_available > 0 AND v_is_unavailable = 0 AND v_is_booked = 0 THEN
        SET p_is_available = TRUE;
    ELSE
        SET p_is_available = FALSE;
    END IF;
END//

DELIMITER ;

-- =====================================================
-- FUNCTION: Get Worker Availability Status
-- Returns 'available' or 'unavailable' for a worker at specific date/time
-- =====================================================
DELIMITER //

DROP FUNCTION IF EXISTS get_worker_availability_status//

CREATE FUNCTION get_worker_availability_status(
    p_worker_id INT,
    p_service_date DATE,
    p_service_time TIME
) RETURNS VARCHAR(20)
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE v_day_of_week VARCHAR(20);
    DECLARE v_default_available INT;
    DECLARE v_is_unavailable INT;
    DECLARE v_is_booked INT;

    -- Get day of week
    SET v_day_of_week = DAYNAME(p_service_date);

    -- Check if worker has default availability for this day/time
    SELECT COUNT(*) INTO v_default_available
    FROM worker_default_availability
    WHERE worker_id = p_worker_id
      AND day_of_week = v_day_of_week
      AND is_available = TRUE
      AND p_service_time BETWEEN start_time AND end_time;

    -- Check if worker marked this specific date/time as unavailable
    SELECT COUNT(*) INTO v_is_unavailable
    FROM worker_unavailability
    WHERE worker_id = p_worker_id
      AND unavailable_date = p_service_date
      AND p_service_time BETWEEN unavailable_start_time AND unavailable_end_time;

    -- Check if already booked
    SELECT COUNT(*) INTO v_is_booked
    FROM booking
    WHERE worker_id = p_worker_id
      AND service_date = p_service_date
      AND service_time = p_service_time
      AND status NOT IN ('cancelled');

    -- Determine status
    IF v_is_booked > 0 THEN
        RETURN 'booked';
    ELSEIF v_is_unavailable > 0 THEN
        RETURN 'unavailable';
    ELSEIF v_default_available > 0 THEN
        RETURN 'available';
    ELSE
        RETURN 'unavailable';
    END IF;
END//

DELIMITER ;

-- =====================================================
-- Sample Default Availability Data
-- =====================================================
-- Worker 1 (Robert Plumber): Monday-Friday 9 AM - 5 PM
INSERT INTO worker_default_availability (worker_id, day_of_week, start_time, end_time) VALUES
(1, 'Monday', '09:00:00', '17:00:00'),
(1, 'Tuesday', '09:00:00', '17:00:00'),
(1, 'Wednesday', '09:00:00', '17:00:00'),
(1, 'Thursday', '09:00:00', '17:00:00'),
(1, 'Friday', '09:00:00', '17:00:00');

-- Worker 2 (Tom Electric): Monday-Saturday 8 AM - 6 PM
INSERT INTO worker_default_availability (worker_id, day_of_week, start_time, end_time) VALUES
(2, 'Monday', '08:00:00', '18:00:00'),
(2, 'Tuesday', '08:00:00', '18:00:00'),
(2, 'Wednesday', '08:00:00', '18:00:00'),
(2, 'Thursday', '08:00:00', '18:00:00'),
(2, 'Friday', '08:00:00', '18:00:00'),
(2, 'Saturday', '08:00:00', '14:00:00');

-- Sample unavailability: Worker 1 not available on Dec 15, 2025
INSERT INTO worker_unavailability (worker_id, unavailable_date, unavailable_start_time, unavailable_end_time, reason) VALUES
(1, '2025-12-15', '09:00:00', '17:00:00', 'Personal appointment'),
(2, '2025-12-20', '08:00:00', '12:00:00', 'Training session');

-- =====================================================
-- SCHEMA ENHANCEMENT COMPLETED
-- =====================================================
SELECT 'Worker availability schema created successfully!' as Status;
