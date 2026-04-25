SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+08:00";

CREATE DATABASE IF NOT EXISTS `laundry_db`;
USE `laundry_db`;

-- --------------------------------------------------------
-- 1. Table structure for table `user`
-- --------------------------------------------------------
CREATE TABLE `user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Manager','Employee','Customer') NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `reset_token_hash` varchar(64) DEFAULT NULL,
  `reset_token_expires_at` datetime DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `verification_token` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Trigger to automatically verify Managers and Employees
-- --------------------------------------------------------
DELIMITER $$
CREATE TRIGGER `auto_verify_staff` BEFORE INSERT ON `user`
FOR EACH ROW
BEGIN
    IF NEW.role IN ('Manager', 'Employee') THEN
        SET NEW.is_verified = 1;
    END IF;
END$$
DELIMITER ;

-- --------------------------------------------------------
-- 2. Table structure for table `shop_status`
-- --------------------------------------------------------
CREATE TABLE `shop_status` (
  `status_id` int(11) NOT NULL AUTO_INCREMENT,
  `is_shop_open` tinyint(1) NOT NULL DEFAULT 1,
  `current_closing_time` time DEFAULT NULL,
  `next_manual_open_time` datetime DEFAULT NULL,
  `next_manual_close_time` datetime DEFAULT NULL,
  `default_open_time` time DEFAULT NULL,
  `default_close_time` time DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `shop_status` 
(`status_id`, `is_shop_open`, `current_closing_time`, `next_manual_open_time`, `next_manual_close_time`, `default_open_time`, `default_close_time`) 
VALUES 
(1, 1, NULL, NULL, NULL, '08:00:00', '21:00:00');

-- --------------------------------------------------------
-- 3. Table structure for table `order`
-- --------------------------------------------------------
CREATE TABLE `order` (
  `order_id` varchar(20) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `tracking_code` int(4) NOT NULL,
  `services_requested` text NOT NULL,
  `supplies_requested` text DEFAULT NULL,
  `bag_counts` text NOT NULL,
  `customer_note` text DEFAULT NULL,
  `estimated_price` decimal(10,2) NOT NULL,
  `additional_fees` decimal(10,2) NOT NULL DEFAULT 0.00,
  `final_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_status` enum('Unpaid','Paid') NOT NULL DEFAULT 'Unpaid',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  PRIMARY KEY (`order_id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `order_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 4. Table structure for table `order_logs`
-- --------------------------------------------------------
CREATE TABLE `order_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(50) NOT NULL,
  `log_message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 5. Table structure for table `process_load`
-- --------------------------------------------------------
CREATE TABLE `process_load` (
  `load_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(20) NOT NULL,
  `load_category` enum('Colored','White','Fold Only','Other') NOT NULL,
  `bag_label` varchar(50) NOT NULL,
  `status` enum('Pending Dropoff','In Queue','Washing','Drying','Folding','Ready for Pickup','Completed','Cancelled') NOT NULL DEFAULT 'Pending Dropoff',
  PRIMARY KEY (`load_id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `process_load_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `order` (`order_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 6. Table structure for table `service_prices`
-- --------------------------------------------------------
CREATE TABLE `service_prices` (
  `service_name` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`service_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `service_prices` (`service_name`, `price`) VALUES
('Wash', 55.00),
('Dry', 60.00),
('Fold', 30.00),
('Detergent', 20.00),
('Softener', 10.00);

-- --------------------------------------------------------
-- 7. Table structure for table `order_messages`
-- --------------------------------------------------------
CREATE TABLE `order_messages` (
  `message_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(20) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message_text` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`message_id`),
  KEY `order_id` (`order_id`),
  KEY `sender_id` (`sender_id`),
  CONSTRAINT `fk_msg_order` FOREIGN KEY (`order_id`) REFERENCES `order` (`order_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_msg_user` FOREIGN KEY (`sender_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- 8.  Table structure for table `sales_report`
-- --------------------------------------------------------
CREATE TABLE `sales_report` (
  `report_id`        int(11) NOT NULL AUTO_INCREMENT,
  `report_month`     tinyint(2) NOT NULL,
  `report_year`      year(4) NOT NULL,
  `total_orders`     int(11) NOT NULL DEFAULT 0,
  `total_revenue`    decimal(10,2) NOT NULL DEFAULT 0.00,
  `detergent_count`  int(11) NOT NULL DEFAULT 0,
  `softener_count`   int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`report_id`),
  UNIQUE KEY `unique_month_year` (`report_month`, `report_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;