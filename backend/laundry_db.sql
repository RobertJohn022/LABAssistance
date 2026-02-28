SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE DATABASE IF NOT EXISTS `laundry_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `laundry_db`;

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
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `order` (`order_id`, `customer_id`, `customer_name`, `tracking_code`, `services_requested`, `supplies_requested`, `bag_counts`, `customer_note`, `estimated_price`, `additional_fees`, `final_price`, `payment_status`, `created_at`) VALUES
('02252026001', 1, 'admin', 5752, 'Wash', 'Detergent', 'Colored: 1, White: 1', '', 140.00, 0.00, 140.00, 'Unpaid', '2026-02-25 13:16:29'),
('02252026002', 2, 'rob', 2229, 'Wash, Dry', 'Detergent', 'Colored: 1, White: 2', 'Fuck off', 390.00, 0.00, 390.00, 'Unpaid', '2026-02-25 13:19:57'),
('02252026003', 2, 'rob', 2460, 'Wash, Dry', 'Detergent, Fabric Softener', 'Colored: 1, White: 1', 'Test', 280.00, 0.00, 280.00, 'Unpaid', '2026-02-25 18:52:49'),
('02282026001', 3, 'john', 2987, 'Wash, Dry', 'Detergent, Fabric Softener', 'Colored: 1, White: 1', 'Note', 280.00, 0.00, 280.00, 'Unpaid', '2026-02-28 11:43:13');

CREATE TABLE `process_load` (
  `load_id` int(11) NOT NULL,
  `order_id` varchar(20) NOT NULL,
  `load_category` enum('Colored','White','Fold Only','Other') NOT NULL,
  `bag_label` varchar(50) NOT NULL,
  `status` enum('Pending Dropoff','In Queue','Washing','Wash Complete','Drying','Drying Complete','Folding','Folding Complete','Awaiting Pickup','Completed') NOT NULL DEFAULT 'Pending Dropoff',
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `timer_paused` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `process_load` (`load_id`, `order_id`, `load_category`, `bag_label`, `status`, `start_time`, `end_time`, `timer_paused`) VALUES
(6, '02252026003', 'Colored', 'Colored #1', 'Washing', NULL, NULL, NULL),
(7, '02252026003', 'White', 'White #1', 'Wash Complete', NULL, NULL, NULL),
(8, '02282026001', 'Colored', 'Colored #1', 'Drying', NULL, NULL, NULL),
(9, '02282026001', 'White', 'White #1', 'Washing', NULL, NULL, NULL);

CREATE TABLE `shop_status` (
  `status_id` int(11) NOT NULL,
  `is_shop_open` tinyint(1) NOT NULL DEFAULT 1,
  `current_closing_time` time DEFAULT NULL,
  `next_manual_open_time` datetime DEFAULT NULL,
  `default_open_time` time DEFAULT NULL,
  `default_close_time` time DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `shop_status` (`status_id`, `is_shop_open`, `current_closing_time`, `next_manual_open_time`, `default_open_time`, `default_close_time`, `updated_at`) VALUES
(1, 1, '20:00:00', '2026-02-23 08:00:00', '08:00:00', '21:00:00', '2026-02-28 11:41:39');

CREATE TABLE `system_log` (
  `log_id` int(11) NOT NULL,
  `load_id` int(11) NOT NULL,
  `status_event` varchar(50) NOT NULL,
  `employee_name` varchar(255) NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `system_log` (`log_id`, `load_id`, `status_event`, `employee_name`, `timestamp`) VALUES
(5, 6, 'Pending Dropoff', 'admin', '2026-02-26 13:28:36'),
(6, 6, 'Pending Dropoff', 'admin', '2026-02-26 13:29:10'),
(7, 6, 'Pending Dropoff', 'admin', '2026-02-27 20:44:46'),
(8, 6, 'Washing', 'admin', '2026-02-27 20:56:06'),
(9, 6, 'Washing', 'admin', '2026-02-27 21:48:23'),
(10, 7, 'Wash Complete', 'admin', '2026-02-27 23:38:30'),
(11, 8, 'In Queue', 'admin', '2026-02-28 11:43:36'),
(12, 9, 'Pending Dropoff', 'admin', '2026-02-28 18:34:23'),
(13, 9, 'In Queue', 'admin', '2026-02-28 18:34:27'),
(14, 9, 'Washing', 'admin', '2026-02-28 18:34:31'),
(15, 8, 'Washing', 'admin', '2026-02-28 19:07:41'),
(16, 8, 'Wash Complete', 'admin', '2026-02-28 19:07:45'),
(17, 8, 'Drying', 'admin', '2026-02-28 19:27:12');

CREATE TABLE `timer_settings` (
  `id` int(1) NOT NULL,
  `end_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `timer_settings` (`id`, `end_time`) VALUES
(1, '2026-02-25 18:41:48');

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Manager','Employee','Customer') NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `user` (`user_id`, `email`, `password`, `role`, `full_name`, `created_at`) VALUES
(1, 'admin@gmail.com', '$2y$10$gSpmybSwQlS3AdaeCMDSz.aFkD6OmyZwwMpcu1yM3eSMRFQPec6J6', 'Employee', 'admin', '2026-02-25 13:15:52'),
(2, 'rob@gmail.com', '$2y$10$a3QdK7W23YDy5wCFlqVv9eAIxbDkIQttbn7x.swy6Jv7mfG8tSPi2', 'Customer', 'rob', '2026-02-25 13:19:29'),
(3, 'john@gmail.com', '$2y$10$KmyGEuYsgRfSOGJl5RFXF.3gIkTXB6ozetRswIYeKnjrfysU2ooz6', 'Customer', 'john', '2026-02-28 11:42:23');


ALTER TABLE `order`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `customer_id` (`customer_id`);

ALTER TABLE `process_load`
  ADD PRIMARY KEY (`load_id`),
  ADD KEY `order_id` (`order_id`);

ALTER TABLE `shop_status`
  ADD PRIMARY KEY (`status_id`);

ALTER TABLE `system_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `load_id` (`load_id`);

ALTER TABLE `timer_settings`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);


ALTER TABLE `process_load`
  MODIFY `load_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

ALTER TABLE `system_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;


ALTER TABLE `order`
  ADD CONSTRAINT `order_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

ALTER TABLE `process_load`
  ADD CONSTRAINT `process_load_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `order` (`order_id`) ON DELETE CASCADE;

ALTER TABLE `system_log`
  ADD CONSTRAINT `system_log_ibfk_1` FOREIGN KEY (`load_id`) REFERENCES `process_load` (`load_id`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
