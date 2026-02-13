-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 13, 2026 at 06:09 AM
-- Server version: 5.7.24
-- PHP Version: 8.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kanom_muangphet_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `central_ingredients`
--

CREATE TABLE `central_ingredients` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL COMMENT 'ชื่อมาตรฐาน เช่น ไข่ไก่, น้ำตาลทราย',
  `unit` varchar(50) NOT NULL COMMENT 'หน่วยมาตรฐาน เช่น ฟอง, กก.',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `central_ingredients`
--

INSERT INTO `central_ingredients` (`id`, `name`, `unit`, `created_at`) VALUES
(1, 'ไข่ไก่ (เบอร์ 2)', 'ฟอง', '2026-02-12 02:07:41'),
(2, 'น้ำตาลทรายขาว', 'กก.', '2026-02-12 02:07:41'),
(3, 'แป้งสาลีเอนกประสงค์', 'กก.', '2026-02-12 02:07:41'),
(4, 'เนยจืด', 'ก้อน (227g)', '2026-02-12 02:07:41'),
(5, 'นมข้นจืด', 'กระป๋อง', '2026-02-12 02:07:41'),
(6, 'ไข่เป็ด', 'ฟอง', '2026-02-13 05:41:23');

-- --------------------------------------------------------

--
-- Table structure for table `ingredients`
--

CREATE TABLE `ingredients` (
  `id` int(11) NOT NULL,
  `shop_id` int(11) NOT NULL COMMENT 'วัตถุดิบนี้เป็นของร้านไหน',
  `name` varchar(255) NOT NULL COMMENT 'ชื่อวัตถุดิบ เช่น ไข่เป็ด, น้ำตาลโตนด',
  `unit` varchar(50) NOT NULL COMMENT 'หน่วย เช่น ฟอง, กรัม, มิลลิลิตร',
  `cost_per_unit` decimal(10,2) DEFAULT '0.00' COMMENT 'ต้นทุนต่อหน่วย',
  `stock_qty` decimal(10,2) DEFAULT '0.00' COMMENT 'สต็อกคงเหลือ'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `ingredients`
--

INSERT INTO `ingredients` (`id`, `shop_id`, `name`, `unit`, `cost_per_unit`, `stock_qty`) VALUES
(1, 2, 'ไข่เป็ด', 'ฟอง', '4.50', '500.00'),
(2, 2, 'น้ำตาลโตนด', 'กรัม', '0.15', '5000.00'),
(3, 2, 'กะทิสด', 'มิลลิลิตร', '0.08', '3000.00'),
(4, 2, 'เผือกนึ่ง', 'กรัม', '0.05', '1000.00');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_no` varchar(20) NOT NULL COMMENT 'เลขที่ใบสั่งซื้อ เช่น OR-2024XXXX',
  `customer_id` int(11) NOT NULL COMMENT 'คนซื้อ (users.id)',
  `shop_id` int(11) NOT NULL COMMENT 'คนขาย (users.id)',
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','shipped','completed','cancelled') DEFAULT 'pending',
  `tracking_no` varchar(100) DEFAULT NULL,
  `slip_image` varchar(255) DEFAULT NULL COMMENT 'หลักฐานการโอน',
  `shipping_address` text COMMENT 'ที่อยู่จัดส่ง (Snapshot ณ เวลาสั่ง)',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_no`, `customer_id`, `shop_id`, `total_amount`, `status`, `tracking_no`, `slip_image`, `shipping_address`, `created_at`) VALUES
(1001, 'INV-TEST-001', 101, 102, '170.00', 'pending', 'EM064129067T1', NULL, '99/9 หมู่บ้านสุขสันต์ กทม. โทร 081-111-2222', '2026-02-06 15:23:32'),
(1002, 'INV-20260211-3123', 4, 3, '170.00', 'pending', NULL, NULL, '555 คอนโดเมืองทอง กรุงเทพฯ', '2026-02-10 19:19:20'),
(1003, 'INV-20260211-4992', 4, 102, '35.00', 'shipped', NULL, NULL, '555 คอนโดเมืองทอง กรุงเทพฯ', '2026-02-10 19:19:20'),
(1004, 'OR-20269441', 4, 102, '85.00', 'shipped', '', 'slip_698b85d8ced46_1770751448.png', 'ชื่อผู้รับ: ลูกค้า ผู้หิวโหย\nเบอร์โทร: 090-555-5555\nที่อยู่: 555 คอนโดเมืองทอง กรุงเทพฯ', '2026-02-10 19:24:08');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `price`, `quantity`) VALUES
(1, 1001, 501, 'ขนมหม้อแกงเผือก', '85.00', 2),
(2, 1002, 3, 'น้ำตาลโตนดแท้ 100%', '120.00', 1),
(3, 1002, 4, 'น้ำตาลสดพร้อมดื่ม', '25.00', 2),
(4, 1003, 502, 'ขนมหม้อแกง', '8.00', 3),
(5, 1003, 510, 'ลอดช่อง', '11.00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `shop_id` int(11) NOT NULL COMMENT 'เจ้าของสินค้า (Link ไปยัง users.id)',
  `name` varchar(255) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `stock_qty` int(11) DEFAULT '100',
  `category` varchar(256) DEFAULT 'dessert',
  `image` varchar(255) DEFAULT NULL,
  `status` varchar(256) DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `central_id` int(11) DEFAULT NULL COMMENT 'อ้างอิง central_ingredients.id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='ตารางรายการสินค้า';

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `shop_id`, `name`, `description`, `price`, `stock_qty`, `category`, `image`, `status`, `created_at`, `central_id`) VALUES
(1, 2, 'ขนมหม้อแกงเผือก', 'สูตรดั้งเดิม หอมกลิ่นเผือกและหอมเจียว', '35.00', 100, 'dessert', 'https://source.unsplash.com/400x300/?dessert', 'active', '2026-02-06 12:33:30', NULL),
(2, 2, 'ทองหยอด (กล่องเล็ก)', 'ทองหยอดไข่เป็ด หวานฉ่ำกำลังดี', '50.00', 100, 'dessert', 'https://source.unsplash.com/400x300/?sweet', 'active', '2026-02-06 12:33:30', NULL),
(3, 3, 'น้ำตาลโตนดแท้ 100%', 'เคี่ยวจากตาลสด ไม่ผสมน้ำตาลทราย', '120.00', 100, 'raw_material', 'https://source.unsplash.com/400x300/?sugar', 'active', '2026-02-06 12:33:30', NULL),
(4, 3, 'น้ำตาลสดพร้อมดื่ม', 'หวานหอม สดชื่น จากธรรมชาติ', '25.00', 100, 'material', 'https://source.unsplash.com/400x300/?juice', 'active', '2026-02-06 12:33:30', NULL),
(501, 102, 'ขนมหม้อแกงเผือก', 'สูตรดั้งเดิม หวานน้อย', '85.00', 50, 'dessert', 'https://placehold.co/300x300?text=Morkang', 'active', '2026-02-06 15:23:25', NULL),
(504, 102, 'ขนมหม้อแกง', 'สูตรเด็ด เจ็ดย่านน้ำ', '8.00', 10, 'dessert', 'default_kanom.png', 'active', '2026-02-06 10:36:14', NULL),
(505, 102, 'ขนมหม้อแกง', 'สูตรเด็ด เจ็ดย่านน้ำ', '8.00', 10, 'dessert', '698b721481fc3_1770746388.png', 'active', '2026-02-10 17:59:48', NULL),
(510, 102, 'ลอดช่อง', '11', '11.00', 1, 'dessert', '698b7221a5bec_1770746401.png', 'active', '2026-02-10 18:00:01', NULL),
(511, 102, 'ลอดช่อง', '', '8.00', 10, 'dessert', '698b788559e4d_1770748037.png', 'active', '2026-02-10 18:24:50', NULL),
(512, 102, 'น้ำตาลทราย', '', '30.00', 10, 'material', 'default_kanom.png', 'active', '2026-02-12 02:14:43', 2),
(513, 102, 'นมข้นจืด', '', '20.00', 10, 'material', 'default_kanom.png', 'active', '2026-02-13 05:02:49', 5);

-- --------------------------------------------------------

--
-- Table structure for table `product_ingredients`
--

CREATE TABLE `product_ingredients` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `ingredient_name` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `unit` varchar(50) NOT NULL,
  `linked_product_id` int(11) DEFAULT NULL COMMENT 'ID สินค้าต้นทาง (ถ้ามี)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `product_ingredients`
--

INSERT INTO `product_ingredients` (`id`, `product_id`, `ingredient_name`, `amount`, `unit`, `linked_product_id`) VALUES
(3, 0, 'แป้ง', '10.00', '1', NULL),
(4, 0, 'แป้ง', '1.00', 'กรัม', NULL),
(5, 0, 'แป้ง มัน', '2.00', 'ถ้วย', NULL),
(6, 0, 'b1', '1.00', 'bbb', NULL),
(7, 0, 'a1', '1.00', 'aaa', NULL),
(46, 510, 'กะทิ', '2.00', 'ลิตร', NULL),
(47, 510, 'มะพร้าว', '2.00', 'ลูก', NULL),
(57, 511, 'นมข้นจืด', '1.00', '1', 513),
(58, 511, 'น้ำตาลทราย', '1.00', '1', 512),
(59, 511, 'เยี่ยม', '1.00', '1', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_packages`
--

CREATE TABLE `product_packages` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `package_name` varchar(255) NOT NULL,
  `qty_per_pack` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `product_packages`
--

INSERT INTO `product_packages` (`id`, `product_id`, `package_name`, `qty_per_pack`, `price`) VALUES
(2, 0, 'กล่องเล็ก', 1, '99.00'),
(3, 0, 'ccc', 1, '11.00'),
(23, 510, '100 กล่อง', 400, '20.00');

-- --------------------------------------------------------

--
-- Table structure for table `recipes`
--

CREATE TABLE `recipes` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `ingredient_id` int(11) NOT NULL,
  `amount_needed` decimal(10,2) NOT NULL COMMENT 'ปริมาณที่ใช้ต่อ 1 ชิ้น/หน่วยการผลิต'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `recipes`
--

INSERT INTO `recipes` (`id`, `product_id`, `ingredient_id`, `amount_needed`) VALUES
(1, 1, 1, '5.00'),
(2, 1, 2, '300.00'),
(3, 1, 3, '250.00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `role` enum('user','shop','admin') DEFAULT 'user',
  `line_id` varchar(255) DEFAULT NULL COMMENT 'เก็บ User ID จาก LINE Login',
  `email` varchar(255) DEFAULT NULL COMMENT 'Login ปกติ',
  `password` varchar(255) DEFAULT NULL COMMENT 'Login ปกติ (ถ้าผ่าน LINE อาจว่างได้)',
  `fullname` varchar(255) DEFAULT NULL COMMENT 'ชื่อผู้ซื้อ หรือ ชื่อเจ้าของร้าน',
  `phone` varchar(20) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL COMMENT 'รูปโปรไฟล์ / โลโก้ร้าน',
  `address` text COMMENT 'ที่อยู่จัดส่ง (User) หรือ ที่ตั้งร้าน (Shop)',
  `shop_name` varchar(255) DEFAULT NULL COMMENT 'ชื่อร้านค้า',
  `bank_name` varchar(100) DEFAULT NULL COMMENT 'ชื่อธนาคาร เช่น กสิกรไทย, ไทยพาณิชย์',
  `bank_account` varchar(50) DEFAULT NULL COMMENT 'เลขที่บัญชี',
  `bank_account_name` varchar(255) DEFAULT NULL COMMENT 'ชื่อบัญชี',
  `qrcode_image` varchar(255) DEFAULT NULL COMMENT 'รูป QR Code รับเงิน (promptpay)',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `latitude` double DEFAULT NULL COMMENT 'ละติจูด',
  `longitude` double DEFAULT NULL COMMENT 'ลองจิจูด'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='ตารางผู้ใช้งานและร้านค้า';

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role`, `line_id`, `email`, `password`, `fullname`, `phone`, `profile_image`, `address`, `shop_name`, `bank_name`, `bank_account`, `bank_account_name`, `qrcode_image`, `created_at`, `updated_at`, `latitude`, `longitude`) VALUES
(1, 'admin', NULL, 'admin@nia.or.th', 'e10adc3949ba59abbe56e057f20f883e', 'System Admin', '02-000-0000', NULL, 'Bangkok, Thailand', NULL, NULL, NULL, NULL, NULL, '2026-02-06 14:33:00', '2026-02-06 14:57:00', NULL, NULL),
(2, 'shop', NULL, 'shop@example.com', 'e10adc3949ba59abbe56e057f20f883e', 'คุณนันทวัน', '081-111-2222', NULL, '99 ถ.เพชรเกษม ต.บ้านหม้อ อ.เมือง จ.เพชรบุรี 76000', 'บ้านขนมนันทวัน', 'กสิกรไทย (KBANK)', '123-4-56789-0', 'บจก.นันทวัน', 'qr_shop_2.jpg', '2026-02-06 12:33:30', '2026-02-10 18:55:09', NULL, NULL),
(3, 'shop', NULL, 'anek@example.com', 'e10adc3949ba59abbe56e057f20f883e', 'ลุงเอนก', '089-999-8888', NULL, 'หมู่ 5 ต.ถ้ำรงค์ อ.บ้านลาด จ.เพชรบุรี', 'ลุงเอนก น้ำตาลสด', 'กรุงไทย (KTB)', '987-6-54321-0', 'นายเอนก ใจดี', NULL, '2026-02-06 12:33:30', '2026-02-10 18:55:15', NULL, NULL),
(4, 'user', NULL, 'user@example.com', 'e10adc3949ba59abbe56e057f20f883e', 'ลูกค้า ผู้หิวโหย', '090-555-5555', NULL, '555 คอนโดเมืองทอง กรุงเทพฯ', NULL, NULL, NULL, NULL, NULL, '2026-02-06 12:33:30', '2026-02-10 18:55:18', NULL, NULL),
(101, 'user', NULL, 'customer@test.com', 'e10adc3949ba59abbe56e057f20f883e', 'นายสมชาย ใจดี (ลูกค้า)', '081-111-2222', NULL, '99/9 หมู่บ้านสุขสันต์ กทม.', NULL, NULL, NULL, NULL, NULL, '2026-02-06 15:23:17', '2026-02-06 15:23:17', NULL, NULL),
(102, 'shop', NULL, 'shop@test.com', 'e10adc3949ba59abbe56e057f20f883e', 'ป้าแจ่ม (เจ้าของร้าน)', '089-555-6666', '20260206_170125_1918.png', '123 ตลาดเมืองเพชร จ.เพชรบุรี', 'ร้านป้าแจ่ม ของฝากเมืองเพชร', '', '', '', 'qrcode.png', '2026-02-06 15:23:17', '2026-02-13 02:56:18', 14.048054465147908, 100.49750338039782),
(104, 'admin', NULL, 'admin@dev.com', '130714308cc6fe6e883615871d96a595', 'Palapon Thitithanaporn', '0996317186', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-13 05:40:38', '2026-02-13 05:40:38', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `central_ingredients`
--
ALTER TABLE `central_ingredients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ingredient_owner` (`shop_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_customer` (`customer_id`),
  ADD KEY `idx_shop` (`shop_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_order_items` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_shop_id` (`shop_id`);

--
-- Indexes for table `product_ingredients`
--
ALTER TABLE `product_ingredients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_packages`
--
ALTER TABLE `product_packages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `recipes`
--
ALTER TABLE `recipes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_recipe_product` (`product_id`),
  ADD KEY `fk_recipe_ingredient` (`ingredient_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `line_id` (`line_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `central_ingredients`
--
ALTER TABLE `central_ingredients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `ingredients`
--
ALTER TABLE `ingredients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1005;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=514;

--
-- AUTO_INCREMENT for table `product_ingredients`
--
ALTER TABLE `product_ingredients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `product_packages`
--
ALTER TABLE `product_packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `recipes`
--
ALTER TABLE `recipes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD CONSTRAINT `fk_ingredient_owner` FOREIGN KEY (`shop_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_product_owner` FOREIGN KEY (`shop_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `recipes`
--
ALTER TABLE `recipes`
  ADD CONSTRAINT `fk_recipe_ingredient` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_recipe_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
