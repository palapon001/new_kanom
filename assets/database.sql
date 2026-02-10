-- ตั้งค่าการรองรับภาษาไทย
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET TIME_ZONE = '+07:00';

-- =========================================================
-- 1. สร้างตาราง USERS
-- เก็บข้อมูลทุกบทบาทในตารางเดียว: Admin, Shop (ร้านค้า), User (ผู้ซื้อ)
-- =========================================================
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  
  -- ส่วนการเข้าสู่ระบบ (Login)
  `role` enum('admin','shop','user') NOT NULL DEFAULT 'user' COMMENT 'สิทธิ์: admin=ดูแลระบบ, shop=คนขาย, user=คนซื้อ',
  `line_id` varchar(255) DEFAULT NULL COMMENT 'เก็บ User ID จาก LINE Login',
  `email` varchar(255) DEFAULT NULL COMMENT 'Login ปกติ',
  `password` varchar(255) DEFAULT NULL COMMENT 'Login ปกติ (ถ้าผ่าน LINE อาจว่างได้)',
  
  -- ข้อมูลโปรไฟล์ทั่วไป
  `fullname` varchar(255) DEFAULT NULL COMMENT 'ชื่อผู้ซื้อ หรือ ชื่อเจ้าของร้าน',
  `phone` varchar(20) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL COMMENT 'รูปโปรไฟล์ / โลโก้ร้าน',
  `address` text COMMENT 'ที่อยู่จัดส่ง (User) หรือ ที่ตั้งร้าน (Shop)',
  
  -- ข้อมูลเฉพาะร้านค้า (เฉพาะ role='shop')
  `shop_name` varchar(255) DEFAULT NULL COMMENT 'ชื่อร้านค้า',
  `bank_name` varchar(100) DEFAULT NULL COMMENT 'ชื่อธนาคาร เช่น กสิกรไทย, ไทยพาณิชย์',
  `bank_account` varchar(50) DEFAULT NULL COMMENT 'เลขที่บัญชี',
  `bank_account_name` varchar(255) DEFAULT NULL COMMENT 'ชื่อบัญชี',
  `qrcode_image` varchar(255) DEFAULT NULL COMMENT 'รูป QR Code รับเงิน (promptpay)',
  
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `line_id` (`line_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='ตารางผู้ใช้งานและร้านค้า';

-- =========================================================
-- 2. สร้างตาราง PRODUCTS (สินค้า/ขนม)
-- มี shop_id เพื่อระบุว่า "ขนมชิ้นนี้เป็นของร้านไหน"
-- =========================================================
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_id` int(11) NOT NULL COMMENT 'เจ้าของสินค้า (Link ไปยัง users.id)',
  `name` varchar(255) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `category` enum('dessert','raw_material','snack','drink') DEFAULT 'dessert',
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','out_of_stock') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_shop_id` (`shop_id`),
  CONSTRAINT `fk_product_owner` FOREIGN KEY (`shop_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='ตารางรายการสินค้า';

-- =========================================================
-- 3. สร้างตาราง INGREDIENTS (วัตถุดิบ)
-- ใช้สำหรับระบบคำนวณสูตร (Calculator)
-- =========================================================
DROP TABLE IF EXISTS `ingredients`;
CREATE TABLE `ingredients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_id` int(11) NOT NULL COMMENT 'วัตถุดิบนี้เป็นของร้านไหน',
  `name` varchar(255) NOT NULL COMMENT 'ชื่อวัตถุดิบ เช่น ไข่เป็ด, น้ำตาลโตนด',
  `unit` varchar(50) NOT NULL COMMENT 'หน่วย เช่น ฟอง, กรัม, มิลลิลิตร',
  `cost_per_unit` decimal(10,2) DEFAULT '0.00' COMMENT 'ต้นทุนต่อหน่วย',
  `stock_qty` decimal(10,2) DEFAULT '0.00' COMMENT 'สต็อกคงเหลือ',
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_ingredient_owner` FOREIGN KEY (`shop_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- 4. สร้างตาราง RECIPES (สูตรการผลิต)
-- เชื่อม Product กับ Ingredient เข้าด้วยกัน
-- =========================================================
DROP TABLE IF EXISTS `recipes`;
CREATE TABLE `recipes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `ingredient_id` int(11) NOT NULL,
  `amount_needed` decimal(10,2) NOT NULL COMMENT 'ปริมาณที่ใช้ต่อ 1 ชิ้น/หน่วยการผลิต',
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_recipe_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_recipe_ingredient` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- 5. สร้างตาราง ORDERS (คำสั่งซื้อ)
-- =========================================================
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_no` varchar(20) NOT NULL COMMENT 'เลขที่ใบสั่งซื้อ เช่น OR-2024XXXX',
  `customer_id` int(11) NOT NULL COMMENT 'คนซื้อ (users.id)',
  `shop_id` int(11) NOT NULL COMMENT 'คนขาย (users.id)',
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','shipped','completed','cancelled') DEFAULT 'pending',
  `slip_image` varchar(255) DEFAULT NULL COMMENT 'หลักฐานการโอน',
  `shipping_address` text COMMENT 'ที่อยู่จัดส่ง (Snapshot ณ เวลาสั่ง)',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_shop` (`shop_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- 6. สร้างตาราง ORDER_ITEMS (รายละเอียดในบิล)
-- =========================================================
DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_order_items` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- DUMMY DATA: ข้อมูลตัวอย่าง
-- =========================================================

-- 1. เพิ่ม USERS
INSERT INTO `users` 
(`id`, `role`, `email`, `password`, `fullname`, `shop_name`, `address`, `phone`, `bank_name`, `bank_account`, `bank_account_name`, `qrcode_image`) 
VALUES
-- ID 1: ADMIN
(1, 'admin', 'admin@nia.or.th', '$2y$10$BpI...', 'System Admin', NULL, 'Bangkok, Thailand', '02-000-0000', NULL, NULL, NULL, NULL),

-- ID 2: SHOP ร้านที่ 1 (บ้านขนมนันทวัน)
(2, 'shop', 'shop@example.com', '$2y$10$BpI...', 'คุณนันทวัน', 'บ้านขนมนันทวัน', '99 ถ.เพชรเกษม ต.บ้านหม้อ อ.เมือง จ.เพชรบุรี 76000', '081-111-2222', 'กสิกรไทย (KBANK)', '123-4-56789-0', 'บจก.นันทวัน', 'qr_shop_2.jpg'),

-- ID 3: SHOP ร้านที่ 2 (ลุงเอนก)
(3, 'shop', 'anek@example.com', '$2y$10$BpI...', 'ลุงเอนก', 'ลุงเอนก น้ำตาลสด', 'หมู่ 5 ต.ถ้ำรงค์ อ.บ้านลาด จ.เพชรบุรี', '089-999-8888', 'กรุงไทย (KTB)', '987-6-54321-0', 'นายเอนก ใจดี', NULL),

-- ID 4: USER ผู้ซื้อ
(4, 'user', 'user@example.com', '$2y$10$BpI...', 'ลูกค้า ผู้หิวโหย', NULL, '555 คอนโดเมืองทอง กรุงเทพฯ', '090-555-5555', NULL, NULL, NULL, NULL);

-- 2. เพิ่ม PRODUCTS
INSERT INTO `products` (`shop_id`, `name`, `description`, `price`, `category`, `image`, `status`) VALUES
(2, 'ขนมหม้อแกงเผือก', 'สูตรดั้งเดิม หอมกลิ่นเผือกและหอมเจียว', 35.00, 'dessert', 'https://source.unsplash.com/400x300/?dessert', 'active'),
(2, 'ทองหยอด (กล่องเล็ก)', 'ทองหยอดไข่เป็ด หวานฉ่ำกำลังดี', 50.00, 'dessert', 'https://source.unsplash.com/400x300/?sweet', 'active'),
(3, 'น้ำตาลโตนดแท้ 100%', 'เคี่ยวจากตาลสด ไม่ผสมน้ำตาลทราย', 120.00, 'raw_material', 'https://source.unsplash.com/400x300/?sugar', 'active'),
(3, 'น้ำตาลสดพร้อมดื่ม', 'หวานหอม สดชื่น จากธรรมชาติ', 25.00, 'drink', 'https://source.unsplash.com/400x300/?juice', 'active');

-- 3. เพิ่ม INGREDIENTS (วัตถุดิบของร้าน บ้านขนมนันทวัน ID 2)
INSERT INTO `ingredients` (`shop_id`, `name`, `unit`, `cost_per_unit`, `stock_qty`) VALUES
(2, 'ไข่เป็ด', 'ฟอง', 4.50, 500.00),
(2, 'น้ำตาลโตนด', 'กรัม', 0.15, 5000.00),
(2, 'กะทิสด', 'มิลลิลิตร', 0.08, 3000.00),
(2, 'เผือกนึ่ง', 'กรัม', 0.05, 1000.00);

-- 4. เพิ่ม RECIPES (สูตรหม้อแกง ของร้าน ID 2)
INSERT INTO `recipes` (`product_id`, `ingredient_id`, `amount_needed`) VALUES
(1, 1, 5.00),   -- หม้อแกง 1 ถาด ใช้ไข่ 5 ฟอง
(1, 2, 300.00), -- ใช้น้ำตาล 300 กรัม
(1, 3, 250.00); -- ใช้กะทิ 250 มล.

SET FOREIGN_KEY_CHECKS = 1;