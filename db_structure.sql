-- =====================================================
--  Coastal Villa and Room Booking System
--  File: db_structure.sql
--  How to use: open phpMyAdmin (http://localhost/phpmyadmin)
-- =====================================================

-- 1. Create the database and start using it
CREATE DATABASE IF NOT EXISTS coastal_villa;
USE coastal_villa;

-- 2. USERS table: stores guests and admins
--    "email" is UNIQUE so two people cannot register with the same email.
--    "password" stores a HASH (scrambled text), never the real password.
CREATE TABLE IF NOT EXISTS users (
    user_id        INT AUTO_INCREMENT PRIMARY KEY,
    name           VARCHAR(100) NOT NULL,
    email          VARCHAR(100) NOT NULL UNIQUE,
    contact_number VARCHAR(20)  NOT NULL,
    password       VARCHAR(255) NOT NULL,
    role           ENUM('guest', 'admin') NOT NULL DEFAULT 'guest'
);

-- 3. ROOMS table: the rooms the villa offers
--    "room_status" lets the admin hide a room without deleting it.
CREATE TABLE IF NOT EXISTS rooms (
    room_id         INT AUTO_INCREMENT PRIMARY KEY,
    room_name       VARCHAR(100) NOT NULL,
    room_type       VARCHAR(50)  NOT NULL,
    description     TEXT         NOT NULL,
    price_per_night DECIMAL(10,2) NOT NULL,
    max_guests      INT          NOT NULL,
    room_status     ENUM('available', 'unavailable') NOT NULL DEFAULT 'available'
);

-- 4. BOOKINGS table: one row per booking
--    user_id and room_id are FOREIGN KEYS (they link to the other tables).
CREATE TABLE IF NOT EXISTS bookings (
    booking_id     INT AUTO_INCREMENT PRIMARY KEY,
    user_id        INT NOT NULL,
    room_id        INT NOT NULL,
    check_in       DATE NOT NULL,
    check_out      DATE NOT NULL,
    total_amount   DECIMAL(10,2) NOT NULL,
    booking_status ENUM('Pending', 'Confirmed', 'Cancelled') NOT NULL DEFAULT 'Pending',
    booking_date   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (room_id) REFERENCES rooms(room_id)
);

-- =====================================================
--  SAMPLE DATA
-- =====================================================

-- Two accounts. The passwords below are already hashed with PHP's password_hash().
--   Admin login: admin@coastalvilla.lk  /  admin123
--   Guest login: guest@example.com      /  guest123
INSERT INTO users (name, email, contact_number, password, role) VALUES
('Villa Admin', 'admin@coastalvilla.lk', '0771234567',
 '$2y$10$ScDvMsIRyLSz1xNiGa6UHuCSDI8ciOge3FaTVLNH/4XgDKbz/KoGa', 'admin'),
('Nimal Perera', 'guest@example.com', '0712345678',
 '$2y$10$ZPziBsuBqcOD.XdIIPgM1exO0D9jG9m0hjelWpDk8LpV6yiX6Jk3a', 'guest');

-- Four sample rooms (prices are in Sri Lankan Rupees, LKR)
INSERT INTO rooms (room_name, room_type, description, price_per_night, max_guests, room_status) VALUES
('Ocean View Deluxe', 'Deluxe',
 'Spacious air-conditioned room with a private balcony facing the Mirissa beach. Facilities: king bed, AC, Wi-Fi, hot water, mini fridge.',
 25000.00, 2, 'available'),
('Garden Family Suite', 'Family',
 'Two-bedroom suite opening to a tropical garden. Facilities: 2 double beds, AC, Wi-Fi, living area, kettle and tea set.',
 32000.00, 4, 'available'),
('Beachside Cabana', 'Cabana',
 'Cosy wooden cabana just 20 steps from the sand. Facilities: double bed, fan, Wi-Fi, outdoor shower, hammock.',
 18000.00, 2, 'available'),
('Budget Twin Room', 'Standard',
 'Simple and clean room for budget travellers. Facilities: 2 single beds, fan, Wi-Fi, shared sitting area.',
 12000.00, 2, 'available');
