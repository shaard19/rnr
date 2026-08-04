-- ========================================================
-- R&R COLLECTION DATABASE BACKUP
-- Database: rnr_collection
-- Created: 2026-08-04 10:07:37
-- ========================================================

SET FOREIGN_KEY_CHECKS=0;

-- --------------------------------------------------------
-- TABLE: categories
-- --------------------------------------------------------

DROP TABLE IF EXISTS `categories`;

CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  `category_code` varchar(50) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `department_id` int(11) NOT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`id`),
  KEY `department_id` (`department_id`),
  CONSTRAINT `fk_categories_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `categories` (`id`, `category_name`, `category_code`, `description`, `department_id`, `status`) VALUES ('4', 'Braids', '', 'Abuja-Long', '3', 'Active');
INSERT INTO `categories` (`id`, `category_name`, `category_code`, `description`, `department_id`, `status`) VALUES ('5', 'Spoons', NULL, 'Silver', '4', 'Active');
INSERT INTO `categories` (`id`, `category_name`, `category_code`, `description`, `department_id`, `status`) VALUES ('6', 'Excecise Books', NULL, '120 Pages A4 Squared', '1', 'Active');
INSERT INTO `categories` (`id`, `category_name`, `category_code`, `description`, `department_id`, `status`) VALUES ('7', 'Men Trousers', NULL, 'Jeans', '2', 'Active');
INSERT INTO `categories` (`id`, `category_name`, `category_code`, `description`, `department_id`, `status`) VALUES ('8', 'Cups', NULL, 'Plastic Normal Kitchen Cups', '4', 'Active');
INSERT INTO `categories` (`id`, `category_name`, `category_code`, `description`, `department_id`, `status`) VALUES ('9', 'Enamel Plates', NULL, 'Enamel Plates', '4', 'Active');
INSERT INTO `categories` (`id`, `category_name`, `category_code`, `description`, `department_id`, `status`) VALUES ('10', 'Cooking Pot', NULL, 'Aluminium, Stainless Steel', '4', 'Active');
INSERT INTO `categories` (`id`, `category_name`, `category_code`, `description`, `department_id`, `status`) VALUES ('11', 'Pen', NULL, 'Blue Pen', '1', 'Active');
INSERT INTO `categories` (`id`, `category_name`, `category_code`, `description`, `department_id`, `status`) VALUES ('12', 'Lotion', NULL, 'nice and lovely lemon', '3', 'Active');
INSERT INTO `categories` (`id`, `category_name`, `category_code`, `description`, `department_id`, `status`) VALUES ('13', 'perfumes', NULL, 'Refill perfumes', '3', 'Active');
INSERT INTO `categories` (`id`, `category_name`, `category_code`, `description`, `department_id`, `status`) VALUES ('14', 'Perfume', NULL, 'Reffileable perfumes', '3', 'Active');
INSERT INTO `categories` (`id`, `category_name`, `category_code`, `description`, `department_id`, `status`) VALUES ('15', 'Men Trouser', NULL, 'Denim-Black', '2', 'Active');

-- --------------------------------------------------------
-- TABLE: customers
-- --------------------------------------------------------

DROP TABLE IF EXISTS `customers`;

CREATE TABLE `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_code` varchar(50) DEFAULT NULL,
  `customer_name` varchar(150) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `credit_limit` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `premium_status` enum('Regular','Premium') NOT NULL DEFAULT 'Regular',
  `sms_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `new_arrival_alerts` tinyint(1) NOT NULL DEFAULT 0,
  `credit_balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customer_code` (`customer_code`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `customers` (`id`, `customer_code`, `customer_name`, `phone`, `email`, `address`, `credit_limit`, `status`, `premium_status`, `sms_enabled`, `new_arrival_alerts`, `credit_balance`) VALUES ('1', '01', 'Shadrack Kimingich Kitele', '0736063055', 'shaard92@outlook.com', '3666-30200, Pittsburgh', '0.00', 'Active', 'Regular', '1', '0', '1070.00');
INSERT INTO `customers` (`id`, `customer_code`, `customer_name`, `phone`, `email`, `address`, `credit_limit`, `status`, `premium_status`, `sms_enabled`, `new_arrival_alerts`, `credit_balance`) VALUES ('2', '002', 'Sharon Wafula', '0711801136', 'sharonw@gmail.com', '2102-30200', '0.00', 'Active', 'Premium', '1', '1', '0.00');
INSERT INTO `customers` (`id`, `customer_code`, `customer_name`, `phone`, `email`, `address`, `credit_limit`, `status`, `premium_status`, `sms_enabled`, `new_arrival_alerts`, `credit_balance`) VALUES ('4', '1', 'Lovelace Robai Kimingich', '0710336014', 'ada@protonmail.ch', '3666-30200', '300.00', 'Active', 'Premium', '1', '1', '50.00');

-- --------------------------------------------------------
-- TABLE: departments
-- --------------------------------------------------------

DROP TABLE IF EXISTS `departments`;

CREATE TABLE `departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `department_name` varchar(100) NOT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `departments` (`id`, `department_name`, `status`) VALUES ('1', 'Student Stationery', 'Active');
INSERT INTO `departments` (`id`, `department_name`, `status`) VALUES ('2', 'Clothes', 'Active');
INSERT INTO `departments` (`id`, `department_name`, `status`) VALUES ('3', 'Beauty Products', 'Active');
INSERT INTO `departments` (`id`, `department_name`, `status`) VALUES ('4', 'Kitchen Utensils', 'Active');

-- --------------------------------------------------------
-- TABLE: notifications
-- --------------------------------------------------------

DROP TABLE IF EXISTS `notifications`;

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `notification_type` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `status` enum('Pending','Sent','Failed') NOT NULL DEFAULT 'Pending',
  `reference_id` varchar(100) DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_notification_type` (`notification_type`),
  KEY `idx_status` (`status`),
  KEY `idx_reference_id` (`reference_id`),
  CONSTRAINT `fk_notifications_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `notifications` (`id`, `customer_id`, `notification_type`, `message`, `phone`, `status`, `reference_id`, `sent_at`, `created_at`) VALUES ('1', '4', 'Credit Reminder', 'Hello Lovelace Robai Kimingich, this is a reminder from R&R Collection. Your outstanding credit balance is KSh 50.00. Kindly make payment at your earliest convenience. Thank you.', '0710336014', 'Pending', NULL, NULL, '2026-08-04 00:09:51');
INSERT INTO `notifications` (`id`, `customer_id`, `notification_type`, `message`, `phone`, `status`, `reference_id`, `sent_at`, `created_at`) VALUES ('2', '1', 'Credit Reminder', 'Hello Shadrack Kimingich Kitele, this is a reminder from R&R Collection. Your outstanding credit balance is KSh 26,070.00. Kindly make payment at your earliest convenience. Thank you.', '0736063055', 'Pending', NULL, NULL, '2026-08-04 00:09:51');

-- --------------------------------------------------------
-- TABLE: permissions
-- --------------------------------------------------------

DROP TABLE IF EXISTS `permissions`;

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `permission_name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permission_name` (`permission_name`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `permissions` (`id`, `permission_name`) VALUES ('5', 'add_category');
INSERT INTO `permissions` (`id`, `permission_name`) VALUES ('11', 'add_customer');
INSERT INTO `permissions` (`id`, `permission_name`) VALUES ('2', 'add_product');
INSERT INTO `permissions` (`id`, `permission_name`) VALUES ('8', 'add_supplier');
INSERT INTO `permissions` (`id`, `permission_name`) VALUES ('7', 'delete_category');
INSERT INTO `permissions` (`id`, `permission_name`) VALUES ('13', 'delete_customer');
INSERT INTO `permissions` (`id`, `permission_name`) VALUES ('4', 'delete_product');
INSERT INTO `permissions` (`id`, `permission_name`) VALUES ('10', 'delete_supplier');
INSERT INTO `permissions` (`id`, `permission_name`) VALUES ('6', 'edit_category');
INSERT INTO `permissions` (`id`, `permission_name`) VALUES ('12', 'edit_customer');
INSERT INTO `permissions` (`id`, `permission_name`) VALUES ('3', 'edit_product');
INSERT INTO `permissions` (`id`, `permission_name`) VALUES ('9', 'edit_supplier');
INSERT INTO `permissions` (`id`, `permission_name`) VALUES ('14', 'make_sale');
INSERT INTO `permissions` (`id`, `permission_name`) VALUES ('21', 'make_sales');
INSERT INTO `permissions` (`id`, `permission_name`) VALUES ('23', 'manage_settings');
INSERT INTO `permissions` (`id`, `permission_name`) VALUES ('17', 'manage_users');
INSERT INTO `permissions` (`id`, `permission_name`) VALUES ('18', 'view_categories');
INSERT INTO `permissions` (`id`, `permission_name`) VALUES ('20', 'view_customer');
INSERT INTO `permissions` (`id`, `permission_name`) VALUES ('1', 'view_dashboard');
INSERT INTO `permissions` (`id`, `permission_name`) VALUES ('24', 'view_notifications');
INSERT INTO `permissions` (`id`, `permission_name`) VALUES ('22', 'view_reports');
INSERT INTO `permissions` (`id`, `permission_name`) VALUES ('15', 'view_sales');
INSERT INTO `permissions` (`id`, `permission_name`) VALUES ('16', 'view_stock');
INSERT INTO `permissions` (`id`, `permission_name`) VALUES ('19', 'view_supplier');

-- --------------------------------------------------------
-- TABLE: products
-- --------------------------------------------------------

DROP TABLE IF EXISTS `products`;

CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_code` varchar(50) NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `category_id` int(11) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `buying_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `selling_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `reorder_level` int(11) NOT NULL DEFAULT 0,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_code` (`product_code`),
  KEY `category_id` (`category_id`),
  KEY `supplier_id` (`supplier_id`),
  CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_products_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `products` (`id`, `product_code`, `product_name`, `category_id`, `supplier_id`, `unit`, `buying_price`, `selling_price`, `quantity`, `reorder_level`, `status`, `image`) VALUES ('1', 'BRA-00001', 'Abuja Braids', '4', '1', '1 Box', '75.00', '100.00', '3', '5', 'Active', NULL);
INSERT INTO `products` (`id`, `product_code`, `product_name`, `category_id`, `supplier_id`, `unit`, `buying_price`, `selling_price`, `quantity`, `reorder_level`, `status`, `image`) VALUES ('2', 'SPO-00001', 'Table Spoons', '5', '2', '1 box', '125.00', '150.00', '17', '5', 'Active', NULL);
INSERT INTO `products` (`id`, `product_code`, `product_name`, `category_id`, `supplier_id`, `unit`, `buying_price`, `selling_price`, `quantity`, `reorder_level`, `status`, `image`) VALUES ('3', 'EXC-00001', 'Excecise Books', '6', '3', '1 Box', '450.00', '680.00', '42', '5', 'Active', NULL);
INSERT INTO `products` (`id`, `product_code`, `product_name`, `category_id`, `supplier_id`, `unit`, `buying_price`, `selling_price`, `quantity`, `reorder_level`, `status`, `image`) VALUES ('4', 'MEN-00001', 'Jeans Trousers', '7', NULL, '1', '200.00', '350.00', '10', '5', 'Active', NULL);
INSERT INTO `products` (`id`, `product_code`, `product_name`, `category_id`, `supplier_id`, `unit`, `buying_price`, `selling_price`, `quantity`, `reorder_level`, `status`, `image`) VALUES ('5', 'ENA-00001', 'Enamel Plate Black', '9', NULL, '1 Pc', '150.00', '275.00', '3', '5', 'Active', NULL);
INSERT INTO `products` (`id`, `product_code`, `product_name`, `category_id`, `supplier_id`, `unit`, `buying_price`, `selling_price`, `quantity`, `reorder_level`, `status`, `image`) VALUES ('6', 'CUP-00001', 'Enamel Cup', '8', NULL, '1 pc', '150.00', '200.00', '0', '5', 'Active', NULL);
INSERT INTO `products` (`id`, `product_code`, `product_name`, `category_id`, `supplier_id`, `unit`, `buying_price`, `selling_price`, `quantity`, `reorder_level`, `status`, `image`) VALUES ('8', 'PEN-00001', 'Blue Pen', '11', '3', '1 pc', '5.00', '10.00', '1', '5', 'Active', NULL);
INSERT INTO `products` (`id`, `product_code`, `product_name`, `category_id`, `supplier_id`, `unit`, `buying_price`, `selling_price`, `quantity`, `reorder_level`, `status`, `image`) VALUES ('9', 'PEN-00002', 'Blue Pen Sharp Pointed', '11', '3', '1', '10.00', '20.00', '3', '5', 'Active', NULL);
INSERT INTO `products` (`id`, `product_code`, `product_name`, `category_id`, `supplier_id`, `unit`, `buying_price`, `selling_price`, `quantity`, `reorder_level`, `status`, `image`) VALUES ('10', 'CUP-00002', 'kaulo heavy', '8', '2', '1', '80.00', '119.98', '15', '5', 'Active', NULL);
INSERT INTO `products` (`id`, `product_code`, `product_name`, `category_id`, `supplier_id`, `unit`, `buying_price`, `selling_price`, `quantity`, `reorder_level`, `status`, `image`) VALUES ('11', 'BRA-00002', 'pink chiffon', '13', '1', '20 mls', '290.00', '330.00', '5', '30', 'Active', NULL);
INSERT INTO `products` (`id`, `product_code`, `product_name`, `category_id`, `supplier_id`, `unit`, `buying_price`, `selling_price`, `quantity`, `reorder_level`, `status`, `image`) VALUES ('12', 'PER-00001', 'pink chiffon', '13', '1', '3ml', '33.00', '100.00', '7', '5', 'Inactive', NULL);

-- --------------------------------------------------------
-- TABLE: role_permissions
-- --------------------------------------------------------

DROP TABLE IF EXISTS `role_permissions`;

CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role` enum('Admin','Cashier') NOT NULL,
  `permission_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `fk_role_permissions_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `role_permissions` (`id`, `role`, `permission_id`) VALUES ('1', 'Admin', '1');
INSERT INTO `role_permissions` (`id`, `role`, `permission_id`) VALUES ('2', 'Admin', '2');
INSERT INTO `role_permissions` (`id`, `role`, `permission_id`) VALUES ('3', 'Admin', '3');
INSERT INTO `role_permissions` (`id`, `role`, `permission_id`) VALUES ('4', 'Admin', '4');
INSERT INTO `role_permissions` (`id`, `role`, `permission_id`) VALUES ('5', 'Admin', '5');
INSERT INTO `role_permissions` (`id`, `role`, `permission_id`) VALUES ('6', 'Admin', '6');
INSERT INTO `role_permissions` (`id`, `role`, `permission_id`) VALUES ('7', 'Admin', '7');
INSERT INTO `role_permissions` (`id`, `role`, `permission_id`) VALUES ('8', 'Admin', '8');
INSERT INTO `role_permissions` (`id`, `role`, `permission_id`) VALUES ('9', 'Admin', '9');
INSERT INTO `role_permissions` (`id`, `role`, `permission_id`) VALUES ('10', 'Admin', '10');
INSERT INTO `role_permissions` (`id`, `role`, `permission_id`) VALUES ('11', 'Admin', '11');
INSERT INTO `role_permissions` (`id`, `role`, `permission_id`) VALUES ('12', 'Admin', '12');
INSERT INTO `role_permissions` (`id`, `role`, `permission_id`) VALUES ('13', 'Admin', '13');
INSERT INTO `role_permissions` (`id`, `role`, `permission_id`) VALUES ('14', 'Admin', '14');
INSERT INTO `role_permissions` (`id`, `role`, `permission_id`) VALUES ('15', 'Admin', '15');
INSERT INTO `role_permissions` (`id`, `role`, `permission_id`) VALUES ('16', 'Admin', '16');
INSERT INTO `role_permissions` (`id`, `role`, `permission_id`) VALUES ('17', 'Admin', '17');
INSERT INTO `role_permissions` (`id`, `role`, `permission_id`) VALUES ('18', 'Admin', '18');
INSERT INTO `role_permissions` (`id`, `role`, `permission_id`) VALUES ('19', 'Admin', '19');
INSERT INTO `role_permissions` (`id`, `role`, `permission_id`) VALUES ('20', 'Admin', '20');
INSERT INTO `role_permissions` (`id`, `role`, `permission_id`) VALUES ('21', 'Admin', '21');
INSERT INTO `role_permissions` (`id`, `role`, `permission_id`) VALUES ('22', 'Admin', '22');
INSERT INTO `role_permissions` (`id`, `role`, `permission_id`) VALUES ('23', 'Cashier', '14');
INSERT INTO `role_permissions` (`id`, `role`, `permission_id`) VALUES ('24', 'Cashier', '1');
INSERT INTO `role_permissions` (`id`, `role`, `permission_id`) VALUES ('25', 'Cashier', '15');
INSERT INTO `role_permissions` (`id`, `role`, `permission_id`) VALUES ('26', 'Cashier', '16');
INSERT INTO `role_permissions` (`id`, `role`, `permission_id`) VALUES ('30', 'Admin', '23');
INSERT INTO `role_permissions` (`id`, `role`, `permission_id`) VALUES ('31', 'Admin', '24');

-- --------------------------------------------------------
-- TABLE: sale_items
-- --------------------------------------------------------

DROP TABLE IF EXISTS `sale_items`;

CREATE TABLE `sale_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sale_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sale_id` (`sale_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `fk_sale_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_sale_items_sale` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('1', '1', '1', '1', '100.00', '100.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('2', '2', '3', '1', '680.00', '680.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('3', '3', '1', '1', '100.00', '100.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('4', '4', '3', '1', '680.00', '680.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('5', '5', '9', '1', '20.00', '20.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('6', '5', '8', '1', '10.00', '10.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('7', '6', '1', '1', '100.00', '100.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('8', '6', '3', '1', '680.00', '680.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('9', '7', '3', '2', '680.00', '1360.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('10', '8', '6', '2', '200.00', '400.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('11', '9', '4', '1', '25000.00', '25000.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('12', '10', '10', '2', '119.98', '239.96');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('13', '11', '12', '1', '100.00', '100.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('14', '12', '1', '2', '100.00', '200.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('15', '12', '3', '1', '680.00', '680.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('16', '12', '12', '1', '100.00', '100.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('17', '13', '2', '1', '150.00', '150.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('18', '14', '8', '1', '10.00', '10.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('19', '15', '8', '1', '10.00', '10.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('20', '16', '10', '1', '119.98', '119.98');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('21', '17', '6', '1', '200.00', '200.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('22', '18', '8', '2', '10.00', '20.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('23', '19', '8', '1', '10.00', '10.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('24', '20', '10', '1', '119.98', '119.98');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('25', '21', '8', '1', '10.00', '10.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('26', '22', '12', '1', '100.00', '100.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('27', '23', '8', '4', '10.00', '40.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('28', '24', '8', '1', '10.00', '10.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('29', '25', '4', '1', '25000.00', '25000.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('30', '26', '2', '1', '150.00', '150.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('31', '27', '10', '1', '119.98', '119.98');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('32', '28', '5', '1', '275.00', '275.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('33', '29', '8', '1', '10.00', '10.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('34', '30', '8', '1', '10.00', '10.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('35', '30', '2', '1', '150.00', '150.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('36', '31', '8', '1', '10.00', '10.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('37', '31', '11', '1', '330.00', '330.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('38', '32', '8', '1', '10.00', '10.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('39', '32', '11', '1', '330.00', '330.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('40', '33', '8', '1', '10.00', '10.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('41', '33', '2', '1', '150.00', '150.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('42', '34', '8', '1', '10.00', '10.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('43', '34', '2', '1', '150.00', '150.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('44', '35', '8', '1', '10.00', '10.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('45', '36', '11', '1', '330.00', '330.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('46', '37', '11', '1', '330.00', '330.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('47', '38', '8', '1', '10.00', '10.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('48', '39', '8', '1', '10.00', '10.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('49', '39', '2', '1', '150.00', '150.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('50', '40', '8', '1', '10.00', '10.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('51', '41', '8', '1', '10.00', '10.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('52', '42', '8', '1', '10.00', '10.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('53', '43', '1', '1', '100.00', '100.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('54', '44', '11', '1', '330.00', '330.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('55', '45', '1', '1', '100.00', '100.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('56', '46', '1', '1', '100.00', '100.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('57', '47', '11', '1', '330.00', '330.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('58', '48', '1', '1', '100.00', '100.00');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES ('59', '49', '2', '1', '150.00', '150.00');

-- --------------------------------------------------------
-- TABLE: sales
-- --------------------------------------------------------

DROP TABLE IF EXISTS `sales`;

CREATE TABLE `sales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_no` varchar(50) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `payment_method` enum('Cash','Lipa na M-Pesa') NOT NULL DEFAULT 'Cash',
  `mpesa_phone` varchar(20) DEFAULT NULL,
  `mpesa_transaction_code` varchar(50) DEFAULT NULL,
  `mpesa_payment_mode` enum('STK_PUSH','DIRECT_TILL') DEFAULT NULL,
  `payment_status` enum('Paid','Partial','Credit') NOT NULL DEFAULT 'Paid',
  `amount_paid` decimal(12,2) NOT NULL DEFAULT 0.00,
  `balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `sale_date` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_no` (`invoice_no`),
  KEY `customer_id` (`customer_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_sales_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_sales_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('1', 'INV-20260730024142-199', NULL, '1', '100.00', 'Cash', NULL, NULL, NULL, 'Paid', '200.00', '0.00', '2026-07-30 03:41:42');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('2', 'INV-20260730024239-358', NULL, '1', '680.00', 'Cash', NULL, NULL, NULL, 'Paid', '700.00', '0.00', '2026-07-30 03:42:39');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('3', 'INV-20260730024657-502', NULL, '1', '100.00', 'Cash', NULL, NULL, NULL, 'Paid', '200.00', '0.00', '2026-07-30 03:46:57');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('4', 'INV-20260730024715-991', NULL, '1', '680.00', 'Cash', NULL, NULL, NULL, 'Paid', '1000.00', '0.00', '2026-07-30 03:47:15');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('5', 'INV-20260730082808-222', NULL, '2', '30.00', 'Cash', NULL, NULL, NULL, 'Paid', '50.00', '0.00', '2026-07-30 09:28:08');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('6', 'INV-20260730082935-203', NULL, '3', '780.00', 'Cash', NULL, NULL, NULL, 'Paid', '800.00', '0.00', '2026-07-30 09:29:35');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('7', 'INV-20260730150639-877', NULL, '1', '1360.00', 'Cash', NULL, NULL, NULL, 'Paid', '1500.00', '0.00', '2026-07-30 16:06:39');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('8', 'INV-20260730150821-148', NULL, '1', '400.00', 'Cash', NULL, NULL, NULL, 'Paid', '500.00', '0.00', '2026-07-30 16:08:21');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('9', 'INV-20260730150852-788', NULL, '1', '25000.00', 'Cash', NULL, NULL, NULL, 'Paid', '25000.00', '0.00', '2026-07-30 16:08:52');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('10', 'INV-20260730151747-905', NULL, '1', '239.96', 'Cash', NULL, NULL, NULL, 'Paid', '500.00', '0.00', '2026-07-30 16:17:47');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('11', 'INV-20260730152234-399', NULL, '1', '100.00', 'Cash', NULL, NULL, NULL, 'Paid', '200.00', '0.00', '2026-07-30 16:22:34');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('12', 'INV-20260730152529-804', NULL, '3', '980.00', 'Cash', NULL, NULL, NULL, 'Paid', '1000.00', '0.00', '2026-07-30 16:25:29');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('13', 'INV-20260730154744-171', NULL, '1', '150.00', 'Cash', NULL, NULL, NULL, 'Paid', '200.00', '0.00', '2026-07-30 16:47:44');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('14', 'INV-20260730192104-143', NULL, '2', '10.00', 'Cash', NULL, NULL, NULL, 'Paid', '40.00', '0.00', '2026-07-30 20:21:04');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('15', 'INV-20260730195402-DF4F0D', NULL, '2', '10.00', 'Cash', NULL, NULL, NULL, 'Credit', '0.00', '10.00', '2026-07-30 20:54:02');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('16', 'INV-20260730195426-91BC39', NULL, '2', '119.98', 'Cash', NULL, NULL, NULL, 'Paid', '150.00', '0.00', '2026-07-30 20:54:26');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('17', 'INV-20260730195545-6F457D', NULL, '2', '200.00', 'Cash', NULL, NULL, NULL, 'Paid', '500.00', '0.00', '2026-07-30 20:55:45');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('18', 'INV-20260730195726-A2EB88', NULL, '2', '20.00', 'Cash', NULL, NULL, NULL, 'Paid', '20.00', '0.00', '2026-07-30 20:57:26');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('19', 'INV-20260730200544-2409D3', NULL, '2', '10.00', 'Cash', NULL, NULL, NULL, 'Paid', '20.00', '0.00', '2026-07-30 21:05:44');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('20', 'INV-20260730200707-351744', NULL, '2', '119.98', 'Cash', NULL, NULL, NULL, 'Paid', '120.00', '0.00', '2026-07-30 21:07:07');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('21', 'INV-20260730201231-F3C7F6', NULL, '2', '10.00', 'Cash', NULL, NULL, NULL, 'Paid', '20.00', '0.00', '2026-07-30 21:12:31');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('22', 'INV-20260730201754-63CF80', NULL, '2', '100.00', 'Cash', NULL, NULL, NULL, 'Paid', '100.00', '0.00', '2026-07-30 21:17:54');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('23', 'INV-20260730201845-725B05', NULL, '2', '40.00', 'Cash', NULL, NULL, NULL, 'Paid', '50.00', '0.00', '2026-07-30 21:18:45');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('24', 'INV-20260730202110-6DBA2D', NULL, '3', '10.00', 'Cash', NULL, NULL, NULL, 'Paid', '20.00', '0.00', '2026-07-30 21:21:10');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('25', 'INV-20260730202201-84B798', '1', '3', '25000.00', 'Cash', NULL, NULL, NULL, 'Credit', '0.00', '25000.00', '2026-07-30 21:22:01');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('26', 'INV-20260730203355-870182', NULL, '3', '150.00', 'Cash', NULL, NULL, NULL, 'Paid', '150.00', '0.00', '2026-07-30 21:33:55');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('27', 'INV-20260730203912-DA04EF', NULL, '3', '119.98', 'Cash', NULL, NULL, NULL, 'Credit', '0.00', '119.98', '2026-07-30 21:39:12');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('28', 'INV-20260730203958-5EF966', NULL, '3', '275.00', 'Lipa na M-Pesa', NULL, NULL, NULL, 'Paid', '300.00', '0.00', '2026-07-30 21:39:58');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('29', 'INV-20260730205353-DA8A3B', '1', '3', '10.00', 'Cash', NULL, NULL, NULL, 'Credit', '0.00', '10.00', '2026-07-30 21:53:53');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('30', 'INV-20260730205447-BABD1A', '1', '3', '160.00', 'Cash', NULL, NULL, NULL, 'Credit', '0.00', '160.00', '2026-07-30 21:54:47');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('31', 'INV-20260730210042-FE36BF', '1', '3', '340.00', 'Cash', NULL, NULL, NULL, 'Credit', '0.00', '340.00', '2026-07-30 22:00:42');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('32', 'INV-20260730210128-F46970', '1', '3', '340.00', 'Cash', NULL, NULL, NULL, 'Credit', '0.00', '340.00', '2026-07-30 22:01:28');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('33', 'INV-20260730210259-2D6B7C', '1', '3', '160.00', 'Cash', NULL, NULL, NULL, 'Credit', '0.00', '160.00', '2026-07-30 22:02:59');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('34', 'INV-20260730210648-409395', '1', '3', '160.00', 'Cash', NULL, NULL, NULL, 'Credit', '100.00', '60.00', '2026-07-30 22:06:48');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('35', 'INV-20260731090231-AD708B', NULL, '1', '10.00', 'Cash', NULL, NULL, NULL, 'Paid', '20.00', '0.00', '2026-07-31 10:02:31');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('36', 'INV-20260731090509-2F612E', NULL, '1', '330.00', 'Cash', NULL, NULL, NULL, 'Paid', '400.00', '0.00', '2026-07-31 10:05:09');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('37', 'INV-20260731090530-567C68', NULL, '1', '330.00', 'Cash', NULL, NULL, NULL, 'Paid', '400.00', '0.00', '2026-07-31 10:05:30');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('38', 'INV-20260731103539-7F99C2', NULL, '1', '10.00', 'Cash', NULL, NULL, NULL, 'Paid', '10.00', '0.00', '2026-07-31 11:35:39');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('39', 'INV-20260731132854-EE6A35', NULL, '3', '160.00', 'Cash', NULL, NULL, NULL, 'Paid', '250.00', '0.00', '2026-07-31 14:28:54');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('40', 'INV-20260731163627-08C35A', NULL, '3', '10.00', 'Lipa na M-Pesa', NULL, NULL, NULL, 'Paid', '50.00', '0.00', '2026-07-31 17:36:27');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('41', 'INV-20260731185725-2D4EA5', NULL, '3', '10.00', 'Cash', NULL, NULL, NULL, 'Paid', '20.00', '0.00', '2026-07-31 19:57:25');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('42', 'INV-20260801084502-F1D4A5', '2', '1', '10.00', 'Cash', NULL, NULL, NULL, 'Paid', '20.00', '0.00', '2026-08-01 09:45:02');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('43', 'INV-20260803205229-BAA9CA', '4', '4', '100.00', 'Cash', NULL, NULL, NULL, 'Credit', '50.00', '50.00', '2026-08-03 21:52:29');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('44', 'INV-20260803234015-4B1B62', '1', '1', '330.00', 'Cash', NULL, NULL, NULL, 'Paid', '350.00', '0.00', '2026-08-04 00:40:15');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('45', 'INV-20260804001211-9E8EB5', '1', '4', '100.00', 'Lipa na M-Pesa', NULL, 'DES32SXDGT', 'DIRECT_TILL', '', '100.00', '0.00', '2026-08-04 01:12:11');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('46', 'INV-20260804001254-D30104', '1', '4', '100.00', 'Lipa na M-Pesa', '254141844072', NULL, 'STK_PUSH', '', '100.00', '0.00', '2026-08-04 01:12:54');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('47', 'INV-20260804091745-082430', '2', '4', '330.00', 'Lipa na M-Pesa', NULL, 'AD7478FRV45D', 'DIRECT_TILL', '', '330.00', '0.00', '2026-08-04 10:17:45');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('48', 'INV-20260804094759-E7612F', NULL, '4', '100.00', 'Lipa na M-Pesa', NULL, 'DFR45RETFG', 'DIRECT_TILL', '', '100.00', '0.00', '2026-08-04 10:47:59');
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `user_id`, `total`, `payment_method`, `mpesa_phone`, `mpesa_transaction_code`, `mpesa_payment_mode`, `payment_status`, `amount_paid`, `balance`, `sale_date`) VALUES ('49', 'INV-20260804100041-6C50B3', NULL, '4', '150.00', 'Cash', NULL, NULL, NULL, '', '100.00', '50.00', '2026-08-04 11:00:41');

-- --------------------------------------------------------
-- TABLE: settings
-- --------------------------------------------------------

DROP TABLE IF EXISTS `settings`;

CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `business_name` varchar(150) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `currency` varchar(10) DEFAULT 'KES',
  `tax_rate` decimal(5,2) DEFAULT 16.00,
  `logo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `settings` (`id`, `business_name`, `phone`, `email`, `address`, `currency`, `tax_rate`, `logo`) VALUES ('1', 'RnR Collection', '0736063055', 'rnr@gmail.com', '0120120', 'KES', '16.00', NULL);

-- --------------------------------------------------------
-- TABLE: stock_movements
-- --------------------------------------------------------

DROP TABLE IF EXISTS `stock_movements`;

CREATE TABLE `stock_movements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `movement_type` enum('IN','OUT') NOT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `movement_date` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_stock_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_stock_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `stock_movements` (`id`, `product_id`, `quantity`, `movement_type`, `reference`, `user_id`, `movement_date`) VALUES ('1', '8', '1', 'OUT', 'INV-20260730195402-DF4F0D', '2', '2026-07-30 20:54:02');
INSERT INTO `stock_movements` (`id`, `product_id`, `quantity`, `movement_type`, `reference`, `user_id`, `movement_date`) VALUES ('2', '10', '1', 'OUT', 'INV-20260730195426-91BC39', '2', '2026-07-30 20:54:26');
INSERT INTO `stock_movements` (`id`, `product_id`, `quantity`, `movement_type`, `reference`, `user_id`, `movement_date`) VALUES ('3', '6', '1', 'OUT', 'INV-20260730195545-6F457D', '2', '2026-07-30 20:55:45');
INSERT INTO `stock_movements` (`id`, `product_id`, `quantity`, `movement_type`, `reference`, `user_id`, `movement_date`) VALUES ('4', '8', '2', 'OUT', 'INV-20260730195726-A2EB88', '2', '2026-07-30 20:57:26');
INSERT INTO `stock_movements` (`id`, `product_id`, `quantity`, `movement_type`, `reference`, `user_id`, `movement_date`) VALUES ('5', '8', '1', 'OUT', 'INV-20260730200544-2409D3', '2', '2026-07-30 21:05:44');
INSERT INTO `stock_movements` (`id`, `product_id`, `quantity`, `movement_type`, `reference`, `user_id`, `movement_date`) VALUES ('6', '10', '1', 'OUT', 'INV-20260730200707-351744', '2', '2026-07-30 21:07:07');
INSERT INTO `stock_movements` (`id`, `product_id`, `quantity`, `movement_type`, `reference`, `user_id`, `movement_date`) VALUES ('7', '8', '1', 'OUT', 'INV-20260730201231-F3C7F6', '2', '2026-07-30 21:12:31');
INSERT INTO `stock_movements` (`id`, `product_id`, `quantity`, `movement_type`, `reference`, `user_id`, `movement_date`) VALUES ('8', '12', '1', 'OUT', 'INV-20260730201754-63CF80', '2', '2026-07-30 21:17:54');
INSERT INTO `stock_movements` (`id`, `product_id`, `quantity`, `movement_type`, `reference`, `user_id`, `movement_date`) VALUES ('9', '8', '4', 'OUT', 'INV-20260730201845-725B05', '2', '2026-07-30 21:18:45');
INSERT INTO `stock_movements` (`id`, `product_id`, `quantity`, `movement_type`, `reference`, `user_id`, `movement_date`) VALUES ('10', '8', '1', 'OUT', 'INV-20260730202110-6DBA2D', '3', '2026-07-30 21:21:10');
INSERT INTO `stock_movements` (`id`, `product_id`, `quantity`, `movement_type`, `reference`, `user_id`, `movement_date`) VALUES ('11', '4', '1', 'OUT', 'INV-20260730202201-84B798', '3', '2026-07-30 21:22:01');
INSERT INTO `stock_movements` (`id`, `product_id`, `quantity`, `movement_type`, `reference`, `user_id`, `movement_date`) VALUES ('12', '2', '1', 'OUT', 'INV-20260730203355-870182', '3', '2026-07-30 21:33:55');
INSERT INTO `stock_movements` (`id`, `product_id`, `quantity`, `movement_type`, `reference`, `user_id`, `movement_date`) VALUES ('13', '10', '1', 'OUT', 'INV-20260730203912-DA04EF', '3', '2026-07-30 21:39:12');
INSERT INTO `stock_movements` (`id`, `product_id`, `quantity`, `movement_type`, `reference`, `user_id`, `movement_date`) VALUES ('14', '5', '1', 'OUT', 'INV-20260730203958-5EF966', '3', '2026-07-30 21:39:58');
INSERT INTO `stock_movements` (`id`, `product_id`, `quantity`, `movement_type`, `reference`, `user_id`, `movement_date`) VALUES ('15', '8', '1', 'OUT', 'INV-20260730205353-DA8A3B', '3', '2026-07-30 21:53:53');
INSERT INTO `stock_movements` (`id`, `product_id`, `quantity`, `movement_type`, `reference`, `user_id`, `movement_date`) VALUES ('16', '8', '1', 'OUT', 'INV-20260730205447-BABD1A', '3', '2026-07-30 21:54:47');
INSERT INTO `stock_movements` (`id`, `product_id`, `quantity`, `movement_type`, `reference`, `user_id`, `movement_date`) VALUES ('17', '2', '1', 'OUT', 'INV-20260730205447-BABD1A', '3', '2026-07-30 21:54:47');
INSERT INTO `stock_movements` (`id`, `product_id`, `quantity`, `movement_type`, `reference`, `user_id`, `movement_date`) VALUES ('18', '8', '1', 'OUT', 'INV-20260730210042-FE36BF', '3', '2026-07-30 22:00:42');
INSERT INTO `stock_movements` (`id`, `product_id`, `quantity`, `movement_type`, `reference`, `user_id`, `movement_date`) VALUES ('19', '11', '1', 'OUT', 'INV-20260730210042-FE36BF', '3', '2026-07-30 22:00:42');
INSERT INTO `stock_movements` (`id`, `product_id`, `quantity`, `movement_type`, `reference`, `user_id`, `movement_date`) VALUES ('20', '8', '1', 'OUT', 'INV-20260730210128-F46970', '3', '2026-07-30 22:01:28');
INSERT INTO `stock_movements` (`id`, `product_id`, `quantity`, `movement_type`, `reference`, `user_id`, `movement_date`) VALUES ('21', '11', '1', 'OUT', 'INV-20260730210128-F46970', '3', '2026-07-30 22:01:28');
INSERT INTO `stock_movements` (`id`, `product_id`, `quantity`, `movement_type`, `reference`, `user_id`, `movement_date`) VALUES ('22', '8', '1', 'OUT', 'INV-20260730210259-2D6B7C', '3', '2026-07-30 22:02:59');
INSERT INTO `stock_movements` (`id`, `product_id`, `quantity`, `movement_type`, `reference`, `user_id`, `movement_date`) VALUES ('23', '2', '1', 'OUT', 'INV-20260730210259-2D6B7C', '3', '2026-07-30 22:02:59');
INSERT INTO `stock_movements` (`id`, `product_id`, `quantity`, `movement_type`, `reference`, `user_id`, `movement_date`) VALUES ('24', '8', '1', 'OUT', 'INV-20260730210648-409395', '3', '2026-07-30 22:06:48');
INSERT INTO `stock_movements` (`id`, `product_id`, `quantity`, `movement_type`, `reference`, `user_id`, `movement_date`) VALUES ('25', '2', '1', 'OUT', 'INV-20260730210648-409395', '3', '2026-07-30 22:06:48');
INSERT INTO `stock_movements` (`id`, `product_id`, `quantity`, `movement_type`, `reference`, `user_id`, `movement_date`) VALUES ('26', '8', '1', 'OUT', 'INV-20260731090231-AD708B', '1', '2026-07-31 10:02:31');
INSERT INTO `stock_movements` (`id`, `product_id`, `quantity`, `movement_type`, `reference`, `user_id`, `movement_date`) VALUES ('27', '11', '1', 'OUT', 'INV-20260731090509-2F612E', '1', '2026-07-31 10:05:09');
INSERT INTO `stock_movements` (`id`, `product_id`, `quantity`, `movement_type`, `reference`, `user_id`, `movement_date`) VALUES ('28', '11', '1', 'OUT', 'INV-20260731090530-567C68', '1', '2026-07-31 10:05:30');
INSERT INTO `stock_movements` (`id`, `product_id`, `quantity`, `movement_type`, `reference`, `user_id`, `movement_date`) VALUES ('29', '8', '1', 'OUT', 'INV-20260731103539-7F99C2', '1', '2026-07-31 11:35:39');
INSERT INTO `stock_movements` (`id`, `product_id`, `quantity`, `movement_type`, `reference`, `user_id`, `movement_date`) VALUES ('30', '8', '1', 'OUT', 'INV-20260731132854-EE6A35', '3', '2026-07-31 14:28:54');
INSERT INTO `stock_movements` (`id`, `product_id`, `quantity`, `movement_type`, `reference`, `user_id`, `movement_date`) VALUES ('31', '2', '1', 'OUT', 'INV-20260731132854-EE6A35', '3', '2026-07-31 14:28:54');
INSERT INTO `stock_movements` (`id`, `product_id`, `quantity`, `movement_type`, `reference`, `user_id`, `movement_date`) VALUES ('32', '8', '1', 'OUT', 'INV-20260731163627-08C35A', '3', '2026-07-31 17:36:27');
INSERT INTO `stock_movements` (`id`, `product_id`, `quantity`, `movement_type`, `reference`, `user_id`, `movement_date`) VALUES ('33', '8', '1', 'OUT', 'INV-20260731185725-2D4EA5', '3', '2026-07-31 19:57:25');
INSERT INTO `stock_movements` (`id`, `product_id`, `quantity`, `movement_type`, `reference`, `user_id`, `movement_date`) VALUES ('34', '8', '1', 'OUT', 'INV-20260801084502-F1D4A5', '1', '2026-08-01 09:45:02');
INSERT INTO `stock_movements` (`id`, `product_id`, `quantity`, `movement_type`, `reference`, `user_id`, `movement_date`) VALUES ('35', '1', '1', 'OUT', 'INV-20260803205229-BAA9CA', '4', '2026-08-03 21:52:29');
INSERT INTO `stock_movements` (`id`, `product_id`, `quantity`, `movement_type`, `reference`, `user_id`, `movement_date`) VALUES ('36', '11', '1', 'OUT', 'INV-20260803234015-4B1B62', '1', '2026-08-04 00:40:15');
INSERT INTO `stock_movements` (`id`, `product_id`, `quantity`, `movement_type`, `reference`, `user_id`, `movement_date`) VALUES ('37', '1', '1', 'OUT', 'INV-20260804001211-9E8EB5', '4', '2026-08-04 01:12:11');
INSERT INTO `stock_movements` (`id`, `product_id`, `quantity`, `movement_type`, `reference`, `user_id`, `movement_date`) VALUES ('38', '1', '1', 'OUT', 'INV-20260804001254-D30104', '4', '2026-08-04 01:12:54');
INSERT INTO `stock_movements` (`id`, `product_id`, `quantity`, `movement_type`, `reference`, `user_id`, `movement_date`) VALUES ('39', '11', '1', 'OUT', 'INV-20260804091745-082430', '4', '2026-08-04 10:17:45');
INSERT INTO `stock_movements` (`id`, `product_id`, `quantity`, `movement_type`, `reference`, `user_id`, `movement_date`) VALUES ('40', '1', '1', 'OUT', 'INV-20260804094759-E7612F', '4', '2026-08-04 10:47:59');
INSERT INTO `stock_movements` (`id`, `product_id`, `quantity`, `movement_type`, `reference`, `user_id`, `movement_date`) VALUES ('41', '2', '1', 'OUT', 'INV-20260804100041-6C50B3', '4', '2026-08-04 11:00:41');

-- --------------------------------------------------------
-- TABLE: suppliers
-- --------------------------------------------------------

DROP TABLE IF EXISTS `suppliers`;

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_name` varchar(150) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `suppliers` (`id`, `supplier_name`, `phone`, `email`, `address`, `status`, `created_at`) VALUES ('1', 'Best Lady Cosmetics', '0710336014', 'bestlady@yahoo.com', '3666-30200, Kitale', 'Active', '2026-07-30 00:02:05');
INSERT INTO `suppliers` (`id`, `supplier_name`, `phone`, `email`, `address`, `status`, `created_at`) VALUES ('2', 'Jalaram Drappers Limited', '0124154211', 'jdrappers@gmail.com', '320, Kitale', 'Active', '2026-07-30 02:31:43');
INSERT INTO `suppliers` (`id`, `supplier_name`, `phone`, `email`, `address`, `status`, `created_at`) VALUES ('3', 'Lisa Paper Works', '0710336014', 'lisa@gmail.com', '253, Kitale', 'Active', '2026-07-30 02:33:36');
INSERT INTO `suppliers` (`id`, `supplier_name`, `phone`, `email`, `address`, `status`, `created_at`) VALUES ('5', 'Waki Supplies', '0745126325', 'wakis@gmail.com', '2102-Kitale', 'Active', '2026-08-04 01:16:21');

-- --------------------------------------------------------
-- TABLE: users
-- --------------------------------------------------------

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fullname` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Cashier') NOT NULL DEFAULT 'Cashier',
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `last_login` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`id`, `fullname`, `username`, `password`, `role`, `status`, `last_login`) VALUES ('1', 'Administrator', 'admin', '$2y$10$ifbb8vTVrqE/6v5Skp9A7Obp0SpesAUOE/woDNr1GfM4rFMX1OACu', 'Admin', 'Active', '2026-08-04 02:08:07');
INSERT INTO `users` (`id`, `fullname`, `username`, `password`, `role`, `status`, `last_login`) VALUES ('2', 'Shadrack Kimingich Kitele', 'root', '$2y$10$9OiLd7w3SIjeiSbla36i4OCK7bLkRHWOuYGRgPZDC5un0z1GR0756', 'Admin', 'Active', '2026-07-31 19:58:20');
INSERT INTO `users` (`id`, `fullname`, `username`, `password`, `role`, `status`, `last_login`) VALUES ('3', 'Maureen Simiyu Kimingich', 'maureen', '$2y$10$S6S81AaFH2Fr9yQ1iUKMQ.0cehHUlj.p7oHhePEUfiMoKQuXo9uIG', 'Cashier', 'Active', '2026-07-31 19:56:50');
INSERT INTO `users` (`id`, `fullname`, `username`, `password`, `role`, `status`, `last_login`) VALUES ('4', 'Claiton Andaye', 'Andaye', '$2y$10$WAcRZD6cbzXNY5BdvaKrzO9okCOLRqTXdhnViy2nBrZ4Fv2mKGBy2', 'Admin', 'Active', '2026-08-04 10:43:24');

SET FOREIGN_KEY_CHECKS=1;
