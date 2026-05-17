-- ================================================================
-- Online Medicine Shop - Group 8
-- Database: online_medicine_shop
-- Import this file into phpMyAdmin ONCE before running the app.
-- ================================================================

CREATE DATABASE IF NOT EXISTS online_medicine_shop
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE online_medicine_shop;

-- ---------------------------------------------------------------
-- users  (both admin and customer)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100)  NOT NULL,
    email           VARCHAR(150)  NOT NULL UNIQUE,
    password_hash   VARCHAR(255)  NOT NULL,
    role            ENUM('admin','customer') NOT NULL DEFAULT 'customer',
    phone           VARCHAR(20)   DEFAULT NULL,
    address         TEXT          DEFAULT NULL,
    profile_picture VARCHAR(255)  DEFAULT NULL,
    created_at      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- categories  (liquid / solid)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100) NOT NULL,
    category_type ENUM('liquid','solid') NOT NULL,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- medicines
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS medicines (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(200)   NOT NULL,
    category_id  INT            NOT NULL,
    vendor_name  VARCHAR(150)   NOT NULL,
    price        DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    availability INT            NOT NULL DEFAULT 0,
    description  TEXT           DEFAULT NULL,
    image_path   VARCHAR(255)   DEFAULT NULL,
    created_at   DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- cart
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cart (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    medicine_id INT NOT NULL,
    quantity    INT NOT NULL DEFAULT 1,
    added_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)     REFERENCES users(id)     ON DELETE CASCADE,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE CASCADE,
    UNIQUE KEY uq_user_medicine (user_id, medicine_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- orders
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS orders (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    user_id          INT            NOT NULL,
    total_amount     DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    shipping_address TEXT           NOT NULL,
    status           ENUM('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
    payment_method   VARCHAR(50)    NOT NULL,
    order_date       DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- order_items
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS order_items (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    order_id    INT            NOT NULL,
    medicine_id INT            NOT NULL,
    quantity    INT            NOT NULL,
    unit_price  DECIMAL(10,2)  NOT NULL,
    FOREIGN KEY (order_id)    REFERENCES orders(id)    ON DELETE CASCADE,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- payments
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS payments (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    order_id       INT            NOT NULL,
    amount         DECIMAL(10,2)  NOT NULL,
    payment_method VARCHAR(50)    NOT NULL,
    transaction_id VARCHAR(100)   DEFAULT NULL,
    payment_date   DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- NOTE: The default admin account is auto-created by config.php
-- on first page load.  Do NOT manually insert an admin row here.
-- Credentials: admin@medicine.com / admin123
-- ---------------------------------------------------------------
-- If you previously imported an older version of this file that
-- contained a wrong hash, this line removes it so config.php
-- can insert the correct one on next page load:
DELETE FROM users WHERE email = 'admin@medicine.com';

-- ---------------------------------------------------------------
-- Seed: sample categories
-- ---------------------------------------------------------------
INSERT IGNORE INTO categories (id, name, category_type) VALUES
(1, 'Painkiller',      'solid'),
(2, 'Antibiotic',      'solid'),
(3, 'Antacid Syrup',   'liquid'),
(4, 'Cough Syrup',     'liquid'),
(5, 'Vitamin',         'solid'),
(6, 'Antiseptic',      'liquid');

-- ---------------------------------------------------------------
-- Seed: sample medicines
-- ---------------------------------------------------------------
INSERT IGNORE INTO medicines (id, name, category_id, vendor_name, price, availability, description) VALUES
(1, 'Napa Extra 500mg',   1, 'Beximco Pharma',  12.00, 200, 'Effective painkiller and fever reducer.'),
(2, 'Azithromycin 250mg', 2, 'Square Pharma',   45.00, 100, 'Broad-spectrum antibiotic.'),
(3, 'Antacid Plus Syrup', 3, 'Opsonin Pharma',  55.00,  80, 'Relieves acidity and heartburn.'),
(4, 'Tussikof Syrup',     4, 'Renata Ltd',      70.00,  60, 'Cough suppressant syrup.'),
(5, 'Vitamin C 500mg',    5, 'ACI Limited',     20.00, 300, 'Immunity booster vitamin supplement.'),
(6, 'Savlon Antiseptic',  6, 'Reckitt',         85.00,  50, 'Antiseptic liquid for wound cleaning.');
