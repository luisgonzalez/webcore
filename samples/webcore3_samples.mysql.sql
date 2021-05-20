-- MySQL Administrator dump 1.4
--
-- ------------------------------------------------------
-- Server version	5.1.35-community


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


--
-- Create schema webcore3_samples
--

CREATE DATABASE IF NOT EXISTS webcore3_samples;
USE webcore3_samples;

--
-- Definition of table `addresses`
--

DROP TABLE IF EXISTS `addresses`;
CREATE TABLE `addresses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `line1` varchar(255) NOT NULL,
  `line2` varchar(255) NOT NULL DEFAULT '',
  `line3` varchar(255) NOT NULL DEFAULT '',
  `state_id` int(10) unsigned NOT NULL,
  `postal_code` varchar(10) NOT NULL,
  `directions` varchar(255) NOT NULL DEFAULT '',
  `fisrt_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `company_name` varchar(255) NOT NULL DEFAULT '',
  `phone_primary` varchar(45) NOT NULL,
  `phone_office` varchar(45) NOT NULL DEFAULT '',
  `phone_home` varchar(45) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `FK_addresses_states` (`state_id`),
  CONSTRAINT `FK_addresses_states` FOREIGN KEY (`state_id`) REFERENCES `states` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `addresses`
--

/*!40000 ALTER TABLE `addresses` DISABLE KEYS */;
INSERT INTO `addresses` (`id`,`line1`,`line2`,`line3`,`state_id`,`postal_code`,`directions`,`fisrt_name`,`last_name`,`company_name`,`phone_primary`,`phone_office`,`phone_home`) VALUES 
 (1,'Americas #1600','Piso 4','Col. Country Club',15,'44637','','Mario','Di Vece','Unosquare S.A. de C.V.','523336789139','523336789139','523336789139'),
 (2,'1001 SW Fifth Avenue','Suite 1100','',76,'97204','','Michael','Barrett','Unosquare, Inc.','5035358084','5035358084','5035358084'),
 (3,'100 Spooner','','',80,'78920','','Peter','Griffin','','5555555555','5555555555','5555555555'),
 (4,'567 Evergreen Terrace','','',76,'28839','','Homer','Simpson','','5555555555','5555555555','5555555555'),
 (5,'120 Platt','','',40,'29938','','Liane','Cartman','','5555555555','5555555555','5555555555');
/*!40000 ALTER TABLE `addresses` ENABLE KEYS */;


--
-- Definition of table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `categories`
--

/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` (`id`,`name`) VALUES 
 (1,'Specialty Candles'),
 (2,'Home Accessories'),
 (3,'Gifts');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;


--
-- Definition of table `countries`
--

DROP TABLE IF EXISTS `countries`;
CREATE TABLE `countries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `countries`
--

/*!40000 ALTER TABLE `countries` DISABLE KEYS */;
INSERT INTO `countries` (`id`,`name`) VALUES 
 (1,'México'),
 (2,'United States');
/*!40000 ALTER TABLE `countries` ENABLE KEYS */;


--
-- Definition of table `images`
--

DROP TABLE IF EXISTS `images`;
CREATE TABLE `images` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `mimetype` varchar(255) NOT NULL,
  `data` longblob NOT NULL,
  `data_length` int(10) unsigned NOT NULL,
  `thumb` longblob NOT NULL,
  `thumb_length` int(10) unsigned NOT NULL,
  `sys_created_date` datetime NOT NULL,
  `sys_created_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_images_users` (`sys_created_id`),
  CONSTRAINT `FK_images_users` FOREIGN KEY (`sys_created_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `images`
--

/*!40000 ALTER TABLE `images` DISABLE KEYS */;
/*!40000 ALTER TABLE `images` ENABLE KEYS */;


--
-- Definition of table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
CREATE TABLE `invoices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_code` varchar(45) NOT NULL,
  `contributor_name` varchar(255) NOT NULL,
  `contributor_code` varchar(255) NOT NULL,
  `contributor_address_id` int(10) unsigned NOT NULL,
  `invoice_date` datetime NOT NULL,
  `order_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_invoices_orders` (`order_id`),
  CONSTRAINT `FK_invoices_orders` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `invoices`
--

/*!40000 ALTER TABLE `invoices` DISABLE KEYS */;
/*!40000 ALTER TABLE `invoices` ENABLE KEYS */;


--
-- Definition of table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `address_shipping_id` int(10) unsigned NOT NULL,
  `address_billing_id` int(10) unsigned NOT NULL,
  `order_date` datetime NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `tax` decimal(10,2) NOT NULL,
  `shipping` decimal(10,2) NOT NULL,
  `handling` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `status_code` enum('Ordered','Charged','Rejected','Shipped','Delivered') NOT NULL DEFAULT 'Ordered',
  PRIMARY KEY (`id`),
  KEY `FK_orders_users` (`user_id`),
  KEY `FK_orders_addresses_shipping` (`address_shipping_id`),
  KEY `FK_orders_addresses_billing` (`address_billing_id`),
  CONSTRAINT `FK_orders_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `FK_orders_addresses_shipping` FOREIGN KEY (`address_shipping_id`) REFERENCES `addresses` (`id`),
  CONSTRAINT `FK_orders_addresses_billing` FOREIGN KEY (`address_billing_id`) REFERENCES `addresses` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `orders`
--

/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` (`id`,`user_id`,`address_shipping_id`,`address_billing_id`,`order_date`,`subtotal`,`tax`,`shipping`,`handling`,`total`,`status_code`) VALUES 
 (1,20,4,4,'2009-01-01 00:00:00','101.00','12.12','0.00','2.00','115.12','Rejected'),
 (2,20,4,4,'2009-01-15 00:00:00','29.00','3.48','0.00','1.00','33.48','Delivered'),
 (3,20,4,4,'2009-02-01 00:00:00','13.00','1.56','0.00','1.00','15.56','Shipped'),
 (4,21,3,3,'2009-02-15 00:00:00','52.00','6.24','0.00','1.00','59.24','Rejected'),
 (5,21,3,3,'2009-03-01 00:00:00','34.00','4.08','0.00','1.00','39.08','Shipped'),
 (6,21,3,3,'2009-03-15 00:00:00','149.00','17.88','0.00','2.00','168.88','Rejected'),
 (7,21,3,3,'2009-04-15 00:00:00','65.00','7.80','0.00','1.00','73.80','Delivered'),
 (8,22,3,3,'2009-05-01 00:00:00','209.00','25.08','0.00','4.00','238.08','Shipped'),
 (9,23,5,5,'2009-05-15 00:00:00','106.00','12.72','0.00','2.00','120.72','Rejected'),
 (10,23,5,5,'2009-05-01 00:00:00','281.00','33.72','0.00','4.00','318.72','Shipped'),
 (11,24,5,5,'2009-06-15 00:00:00','37.00','4.44','0.00','1.00','42.44','Shipped'),
 (12,25,2,1,'2009-07-01 00:00:00','5.00','0.60','0.00','1.00','6.60','Rejected'),
 (13,25,1,2,'2009-07-15 00:00:00','58.00','6.96','20.00','1.00','85.96','Delivered');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;


--
-- Definition of table `orders_products`
--

DROP TABLE IF EXISTS `orders_products`;
CREATE TABLE `orders_products` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `quantity` int(10) unsigned NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_orders_products_orders` (`order_id`),
  KEY `FK_orders_products_products` (`product_id`),
  CONSTRAINT `FK_orders_products_orders` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  CONSTRAINT `FK_orders_products_products` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `orders_products`
--

/*!40000 ALTER TABLE `orders_products` DISABLE KEYS */;
INSERT INTO `orders_products` (`id`,`order_id`,`product_id`,`quantity`,`unit_price`,`subtotal`) VALUES 
 (1,1,1,1,'1.00','1.00'),
 (2,2,2,2,'2.00','4.00'),
 (3,3,3,3,'3.00','9.00'),
 (4,4,4,4,'4.00','16.00'),
 (5,5,5,5,'5.00','25.00'),
 (6,6,6,6,'6.00','36.00'),
 (7,7,7,7,'7.00','49.00'),
 (8,8,8,8,'8.00','64.00'),
 (9,9,9,9,'9.00','81.00'),
 (10,10,10,10,'10.00','100.00'),
 (11,11,1,1,'1.00','1.00'),
 (12,12,2,2,'2.00','4.00'),
 (13,13,3,3,'3.00','9.00'),
 (14,2,5,5,'5.00','25.00'),
 (15,4,6,6,'6.00','36.00'),
 (16,6,7,7,'7.00','49.00'),
 (17,8,8,8,'8.00','64.00'),
 (18,10,9,9,'9.00','81.00'),
 (19,1,10,10,'10.00','100.00'),
 (20,12,1,1,'1.00','1.00'),
 (21,3,2,2,'2.00','4.00'),
 (22,5,3,3,'3.00','9.00'),
 (23,7,4,4,'4.00','16.00'),
 (24,9,5,5,'5.00','25.00'),
 (25,11,6,6,'6.00','36.00'),
 (26,13,7,7,'7.00','49.00'),
 (27,6,8,8,'8.00','64.00'),
 (28,8,9,9,'9.00','81.00'),
 (29,10,10,10,'10.00','100.00');
/*!40000 ALTER TABLE `orders_products` ENABLE KEYS */;


--
-- Definition of table `orders_tracking`
--

DROP TABLE IF EXISTS `orders_tracking`;
CREATE TABLE `orders_tracking` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `status_code` enum('Ordered','Charged','Rejected','Shipped','Delivered') NOT NULL,
  `status_note` text NOT NULL,
  `status_date` datetime NOT NULL,
  `status_user_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_orders_tracking_orders` (`order_id`),
  KEY `FK_orders_tracking_users` (`status_user_id`),
  CONSTRAINT `FK_orders_tracking_users` FOREIGN KEY (`status_user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `FK_orders_tracking_orders` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `orders_tracking`
--

/*!40000 ALTER TABLE `orders_tracking` DISABLE KEYS */;
INSERT INTO `orders_tracking` (`id`,`order_id`,`status_code`,`status_note`,`status_date`,`status_user_id`) VALUES 
 (1,1,'Ordered','Order was placed, proceeding to check out','2009-07-17 19:27:44',20),
 (2,1,'Rejected','Transaction Rejected','2009-07-17 20:27:44',20),
 (3,2,'Ordered','Order was placed, proceeding to check out','2009-07-17 21:27:44',20),
 (4,2,'Charged','Transaction OK','2009-07-17 22:27:44',20),
 (5,3,'Ordered','Order was placed, proceeding to check out','2009-07-17 23:27:44',20),
 (6,3,'Charged','Transaction OK','2009-07-18 00:27:44',20),
 (7,4,'Ordered','Order was placed, proceeding to check out','2009-07-18 01:27:44',21),
 (8,4,'Rejected','Transaction Rejected','2009-07-18 02:27:44',21),
 (9,5,'Ordered','Order was placed, proceeding to check out','2009-07-18 03:27:44',21),
 (10,5,'Charged','Transaction OK','2009-07-18 04:27:44',21),
 (11,6,'Ordered','Order was placed, proceeding to check out','2009-07-18 05:27:44',21),
 (12,6,'Rejected','Transaction Rejected','2009-07-18 06:27:44',21),
 (13,7,'Ordered','Order was placed, proceeding to check out','2009-07-18 07:27:44',21),
 (14,7,'Charged','Transaction OK','2009-07-18 08:27:44',21),
 (15,8,'Ordered','Order was placed, proceeding to check out','2009-07-18 09:27:44',22),
 (16,8,'Charged','Transaction OK','2009-07-18 10:27:44',22),
 (17,9,'Ordered','Order was placed, proceeding to check out','2009-07-18 11:27:44',23),
 (18,9,'Rejected','Transaction Rejected','2009-07-18 12:27:44',23),
 (19,10,'Ordered','Order was placed, proceeding to check out','2009-07-18 13:27:44',23),
 (20,10,'Charged','Transaction OK','2009-07-18 14:27:44',23),
 (21,11,'Ordered','Order was placed, proceeding to check out','2009-07-18 15:27:44',24),
 (22,11,'Charged','Transaction OK','2009-07-18 16:27:44',24),
 (23,12,'Ordered','Order was placed, proceeding to check out','2009-07-18 17:27:44',25),
 (24,12,'Rejected','Order was placed, proceeding to check out','2009-07-18 18:27:44',25),
 (25,13,'Ordered','Order was placed, proceeding to check out','2009-07-18 19:27:44',25),
 (26,13,'Charged','Transaction OK','2009-07-18 20:27:44',25),
 (27,2,'Shipped','Sent via FedEx 392039499','2009-07-18 21:27:44',20),
 (28,7,'Shipped','Sent via FedEx 392039499','2009-07-18 22:27:44',21),
 (29,13,'Shipped','Sent via FedEx 392039499','2009-07-18 23:27:44',25),
 (30,2,'Delivered','Delivered','2009-07-19 00:27:44',20),
 (31,7,'Delivered','Delivered','2009-07-19 01:27:44',21),
 (32,13,'Delivered','Delivered','2009-07-19 02:27:44',25),
 (33,3,'Shipped','Sent via UPS 3274823388','2009-07-19 03:27:44',20),
 (34,5,'Shipped','Send via UPS 92349239934','2009-07-19 04:27:44',21),
 (35,8,'Shipped','Sent via UPS 4345734858','2009-07-19 05:27:44',22),
 (36,10,'Shipped','Sent via UPS 238749239','2009-07-19 06:27:44',23),
 (37,11,'Shipped','Sent via UPS 437238488458','2009-07-19 07:27:44',24);
/*!40000 ALTER TABLE `orders_tracking` ENABLE KEYS */;


--
-- Definition of table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(45) NOT NULL,
  `title` varchar(255) NOT NULL,
  `name` varchar(45) NOT NULL,
  `highlights` longtext NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `category_id` int(10) unsigned NOT NULL,
  `stock` int(10) unsigned NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UQ_Codes` (`code`),
  KEY `FK_products_categories` (`category_id`),
  CONSTRAINT `FK_products_categories` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `products`
--

/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` (`id`,`code`,`title`,`name`,`highlights`,`price`,`category_id`,`stock`,`enabled`) VALUES 
 (1,'C-AC-PCH-003-005','Aroma Candle','Peach, 3x5','-- This product is pending a description','1.00',1,99,1),
 (2,'C-AC-CIN-003-005','Aroma Candle','Cinnamon, 3x5','-- This product is pending a description','2.00',1,96,1),
 (3,'C-AC-VAN-003-005','Aroma Candle','Vanilla, 3x5','-- This product is pending a description','3.00',1,91,1),
 (4,'C-AC-CHO-003-005','Aroma Candle','Chocolate, 3x5','-- This product is pending a description','4.00',1,96,1),
 (5,'C-AC-CAR-003-005','Aroma Candle','Caramel, 3x5','-- This product is pending a description','5.00',1,90,1),
 (6,'H-AC-P00-SML-000','Colonial Difuser','Peach, Small','-- This product is pending a description','6.00',2,94,1),
 (7,'H-CD-S00-LRG-000','Colonial Difuser','Strawberry, Large','-- This product is pending a description','7.00',2,86,1),
 (8,'H-CD-C00-MED-000','Colonial Difuser','Caramel, Medium','-- This product is pending a description','8.00',2,84,1),
 (9,'H-CD-CI0-003-005','Colonial Difuser','Cinnamon, 3x5','-- This product is pending a description','9.00',2,82,1),
 (10,'H-CD-V00-004-004','Colonial Difuser','Vanilla, 4x4','-- This product is pending a description','10.00',2,80,1);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;


--
-- Definition of table `products_images`
--

DROP TABLE IF EXISTS `products_images`;
CREATE TABLE `products_images` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `image_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_products_images_products` (`product_id`),
  KEY `FK_products_images_images` (`image_id`),
  CONSTRAINT `FK_products_images_products` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `FK_products_images_images` FOREIGN KEY (`image_id`) REFERENCES `images` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `products_images`
--

/*!40000 ALTER TABLE `products_images` DISABLE KEYS */;
/*!40000 ALTER TABLE `products_images` ENABLE KEYS */;


--
-- Definition of table `products_stock_tracking`
--

DROP TABLE IF EXISTS `products_stock_tracking`;
CREATE TABLE `products_stock_tracking` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `change_stock` int(11) NOT NULL,
  `change_date` datetime NOT NULL,
  `change_user_id` int(10) unsigned NOT NULL,
  `new_stock` int(11) NOT NULL,
  `user_comment` longtext NOT NULL,
  `order_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_products_stock_track_products` (`product_id`),
  KEY `FK_products_stock_track_users` (`change_user_id`),
  KEY `FK_products_stock_track_orders` (`order_id`),
  CONSTRAINT `FK_products_stock_track_orders` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  CONSTRAINT `FK_products_stock_track_products` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `FK_products_stock_track_users` FOREIGN KEY (`change_user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `products_stock_tracking`
--

/*!40000 ALTER TABLE `products_stock_tracking` DISABLE KEYS */;
INSERT INTO `products_stock_tracking` (`id`,`product_id`,`change_stock`,`change_date`,`change_user_id`,`new_stock`,`user_comment`,`order_id`) VALUES 
 (1,1,100,'2009-07-14 19:15:44',3,100,'Received by UPS tracking number 2372348298798374',NULL),
 (2,2,100,'2009-07-14 19:15:44',3,100,'Received by UPS tracking number 2372348298798374',NULL),
 (3,3,100,'2009-07-14 19:15:44',3,100,'Received by UPS tracking number 2372348298798374',NULL),
 (4,4,100,'2009-07-14 19:15:44',3,100,'Received by UPS tracking number 2372348298798374',NULL),
 (5,5,100,'2009-07-14 19:15:44',3,100,'Received by UPS tracking number 2372348298798374',NULL),
 (6,6,100,'2009-07-14 19:15:44',3,100,'Received by UPS tracking number 2372348298798374',NULL),
 (7,7,100,'2009-07-14 19:15:44',3,100,'Received by UPS tracking number 2372348298798374',NULL),
 (8,8,100,'2009-07-14 19:15:44',3,100,'Received by UPS tracking number 2372348298798374',NULL),
 (9,9,100,'2009-07-14 19:15:44',3,100,'Received by UPS tracking number 2372348298798374',NULL),
 (10,10,100,'2009-07-14 19:15:44',3,100,'Received by UPS tracking number 2372348298798374',NULL),
 (42,1,-1,'2009-06-15 00:00:00',24,99,'Automatically updated.',11),
 (43,2,-2,'2009-02-01 00:00:00',20,98,'Automatically updated.',3),
 (44,2,-2,'2009-01-15 00:00:00',20,96,'Automatically updated.',2),
 (45,3,-3,'2009-07-15 00:00:00',25,97,'Automatically updated.',13),
 (46,3,-3,'2009-03-01 00:00:00',21,94,'Automatically updated.',5),
 (47,3,-3,'2009-02-01 00:00:00',20,91,'Automatically updated.',3),
 (48,4,-4,'2009-04-15 00:00:00',21,96,'Automatically updated.',7),
 (49,5,-5,'2009-03-01 00:00:00',21,95,'Automatically updated.',5),
 (50,5,-5,'2009-01-15 00:00:00',20,90,'Automatically updated.',2),
 (51,6,-6,'2009-06-15 00:00:00',24,94,'Automatically updated.',11),
 (52,7,-7,'2009-07-15 00:00:00',25,93,'Automatically updated.',13),
 (53,7,-7,'2009-04-15 00:00:00',21,86,'Automatically updated.',7),
 (54,8,-8,'2009-05-01 00:00:00',22,92,'Automatically updated.',8),
 (55,8,-8,'2009-05-01 00:00:00',22,84,'Automatically updated.',8),
 (56,9,-9,'2009-05-01 00:00:00',23,91,'Automatically updated.',10),
 (57,9,-9,'2009-05-01 00:00:00',22,82,'Automatically updated.',8),
 (58,10,-10,'2009-05-01 00:00:00',23,90,'Automatically updated.',10),
 (59,10,-10,'2009-05-01 00:00:00',23,80,'Automatically updated.',10);
/*!40000 ALTER TABLE `products_stock_tracking` ENABLE KEYS */;


--
-- Definition of table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `roles`
--

/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` (`id`,`name`,`description`) VALUES 
 (1,'System Administrator','Manages users passwords and catalogs'),
 (2,'Store Administrator','Manages Products, categories and orders'),
 (3,'Customer','Allows a user to place orders in the store');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;


--
-- Definition of table `states`
--

DROP TABLE IF EXISTS `states`;
CREATE TABLE `states` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `country_id` int(10) unsigned NOT NULL,
  `abbreviation` varchar(5) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_states_countries` (`country_id`),
  CONSTRAINT `FK_states_countries` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=92 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `states`
--

/*!40000 ALTER TABLE `states` DISABLE KEYS */;
INSERT INTO `states` (`id`,`country_id`,`abbreviation`,`name`) VALUES 
 (1,1,'AGS','Aguascalientes'),
 (2,1,'BC','Baja California'),
 (3,1,'BCS','Baja California Sur'),
 (4,1,'CAM','Campeche'),
 (5,1,'CHP','Chiapas'),
 (6,1,'CIH','Chihuahua'),
 (7,1,'COA','Coahuila'),
 (8,1,'COL','Colima'),
 (9,1,'DF','Distrito Federal'),
 (10,1,'DUR','Durango'),
 (11,1,'MEX','Estado de México'),
 (12,1,'GUA','Guanajuato'),
 (13,1,'GUE','Guerrero'),
 (14,1,'HGO','Hidalgo'),
 (15,1,'JAL','Jalisco'),
 (16,1,'MIH','Michoacán'),
 (17,1,'MOR','Morelos'),
 (18,1,'NAY','Nayarit'),
 (19,1,'NL','Nuevo León'),
 (20,1,'OAX','Oaxaca'),
 (21,1,'PUE','Puebla'),
 (22,1,'QRO','Querétaro'),
 (23,1,'QUI','Quintana Roo'),
 (24,1,'SLP','San Luis Potosí'),
 (25,1,'SIN','Sinaloa'),
 (26,1,'SON','Sonora'),
 (27,1,'TAB','Tabasco'),
 (28,1,'TMP','Tamaulipas'),
 (29,1,'TLX','Tlaxcala'),
 (30,1,'VER','Veracruz'),
 (31,1,'YUC','Yucatán'),
 (32,1,'ZAC','Zacatecas'),
 (33,2,'WY','Wyoming'),
 (34,2,'AL','Alabama'),
 (35,2,'AK','Alaska'),
 (36,2,'AS','American Samoa'),
 (37,2,'AZ','Arizona '),
 (38,2,'AR','Arkansas'),
 (39,2,'CA','California '),
 (40,2,'CO','Colorado '),
 (41,2,'CT','Connecticut'),
 (42,2,'DE','Delaware'),
 (43,2,'DC','District Of Columbia'),
 (44,2,'FM','Federated States Of Micronesia'),
 (45,2,'FL','Florida'),
 (46,2,'GA','Georgia'),
 (47,2,'GU','Guam '),
 (48,2,'HI','Hawaii'),
 (49,2,'ID','Idaho'),
 (50,2,'IL','Illinois'),
 (51,2,'IN','Indiana'),
 (52,2,'IA','Iowa'),
 (53,2,'KS','Kansas'),
 (54,2,'KY','Kentucky'),
 (55,2,'LA','Louisiana'),
 (56,2,'ME','Maine'),
 (57,2,'MH','Marshall Islands'),
 (58,2,'MD','Maryland'),
 (59,2,'MA','Massachusetts'),
 (60,2,'MI','Michigan'),
 (61,2,'MN','Minnesota'),
 (62,2,'MS','Mississippi'),
 (63,2,'MO','Missouri'),
 (64,2,'MT','Montana'),
 (65,2,'NE','Nebraska'),
 (66,2,'NV','Nevada'),
 (67,2,'NH','New Hampshire'),
 (68,2,'NJ','New Jersey'),
 (69,2,'NM','New Mexico'),
 (70,2,'NY','New York'),
 (71,2,'NC','North Carolina'),
 (72,2,'ND','North Dakota'),
 (73,2,'MP','Northern Mariana Islands'),
 (74,2,'OH','Ohio'),
 (75,2,'OK','Oklahoma'),
 (76,2,'OR','Oregon'),
 (77,2,'PW','Palau'),
 (78,2,'PA','Pennsylvania'),
 (79,2,'PR','Puerto Rico'),
 (80,2,'RI','Rhode Island'),
 (81,2,'SC','South Carolina'),
 (82,2,'SD','South Dakota'),
 (83,2,'TN','Tennessee'),
 (84,2,'TX','Texas'),
 (85,2,'UT','Utah'),
 (86,2,'VT','Vermont'),
 (87,2,'VI','Virgin Islands'),
 (88,2,'VA','Virginia '),
 (89,2,'WA','Washington'),
 (90,2,'WV','West Virginia'),
 (91,2,'WI','Wisconsin');
/*!40000 ALTER TABLE `states` ENABLE KEYS */;


--
-- Definition of table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
CREATE TABLE `transactions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `trans_date` datetime NOT NULL,
  `trans_amount` decimal(10,2) NOT NULL,
  `trans_code` varchar(45) NOT NULL,
  `trans_result` varchar(45) NOT NULL,
  `cc_number` varchar(45) NOT NULL,
  `cc_expdate` varchar(45) NOT NULL,
  `cc_name` varchar(45) NOT NULL,
  `cc_type` varchar(45) NOT NULL,
  `cc_ccv2` varchar(45) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_transactions_orders` (`order_id`),
  CONSTRAINT `FK_transactions_orders` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `transactions`
--

/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
INSERT INTO `transactions` (`id`,`order_id`,`trans_date`,`trans_amount`,`trans_code`,`trans_result`,`cc_number`,`cc_expdate`,`cc_name`,`cc_type`,`cc_ccv2`) VALUES 
 (1,1,'2009-01-01 00:00:00','115.12','39821349189199929','Failure','4111111111111111','2013-06-02','Individual M. Testvisa','Visa','111'),
 (2,2,'2009-01-15 00:00:00','33.48','42309483294823099','Success','4111111111111111','2013-06-02','Individual M. Testvisa','Visa','111'),
 (3,3,'2009-02-01 00:00:00','15.56','23842348230948858','Success','4111111111111111','2013-06-02','Individual M. Testvisa','Visa','111'),
 (4,4,'2009-02-15 00:00:00','59.24','23948230498243908','Failure','4111111111111111','2013-06-02','Individual M. Testvisa','Visa','111'),
 (5,5,'2009-03-01 00:00:00','39.08','23654020034980093','Success','4111111111111111','2013-06-02','Individual M. Testvisa','Visa','111'),
 (6,6,'2009-03-15 00:00:00','168.88','23940920702384857','Failure','4111111111111111','2013-06-02','Individual M. Testvisa','Visa','111'),
 (7,7,'2009-04-15 00:00:00','73.80','23948021023948540','Success','4111111111111111','2013-06-02','Individual M. Testvisa','Visa','111'),
 (8,8,'2009-05-01 00:00:00','238.08','98793248723988374','Success','4111111111111111','2013-06-02','Individual M. Testvisa','Visa','111'),
 (9,9,'2009-05-15 00:00:00','120.72','23984916324734658','Failure','4111111111111111','2013-06-02','Individual M. Testvisa','Visa','111'),
 (10,10,'2009-05-01 00:00:00','318.72','42983723478747777','Success','4111111111111111','2013-06-02','Individual M. Testvisa','Visa','111'),
 (11,11,'2009-06-15 00:00:00','42.44','28748272565645552','Success','4111111111111111','2013-06-02','Individual M. Testvisa','Visa','111'),
 (12,12,'2009-07-01 00:00:00','6.60','23984563466366465','Failure','4111111111111111','2013-06-02','Individual M. Testvisa','Visa','111'),
 (13,13,'2009-07-15 00:00:00','85.96','32428320293848588','Success','4111111111111111','2013-06-02','Individual M. Testvisa','Visa','111');
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;


--
-- Definition of table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name_first` varchar(255) NOT NULL,
  `name_last` varchar(255) NOT NULL,
  `birthdate` datetime NOT NULL,
  `sys_created_date` datetime NOT NULL,
  `sys_created_id` int(10) unsigned NOT NULL DEFAULT '0',
  `sys_updated_date` datetime NOT NULL,
  `sys_updated_id` int(10) unsigned NOT NULL DEFAULT '0',
  `image_id` int(10) unsigned DEFAULT NULL,
  `sys_enabled` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `UQ_Emails` (`email`),
  KEY `FK_users_images` (`image_id`),
  CONSTRAINT `FK_users_images` FOREIGN KEY (`image_id`) REFERENCES `images` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=latin1 COMMENT='Contains a list of users';

--
-- Dumping data for table `users`
--

/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`id`,`email`,`password`,`name_first`,`name_last`,`birthdate`,`sys_created_date`,`sys_created_id`,`sys_updated_date`,`sys_updated_id`,`image_id`,`sys_enabled`) VALUES 
 (1,'admin@unosquare.com','test','System','Administrator','1982-10-09 00:00:00','2009-07-14 19:15:44',0,'2009-07-14 19:15:44',0,NULL,1),
 (2,'customer@unosquare.com','test','Joe','Doe','1984-06-19 00:00:00','2009-07-14 19:15:44',0,'2009-07-14 19:15:44',0,NULL,1),
 (3,'store@unosquare.com','test','Store','Administrator','1981-07-12 00:00:00','2009-07-14 19:15:44',0,'2009-07-14 19:15:44',0,NULL,1),
 (20,'bart@thesimpsons.com','test','Bartolomeo','Simpson','1980-07-12 00:00:00','2009-07-14 19:15:44',0,'2009-07-14 19:15:44',0,NULL,1),
 (21,'peter@familyguy.com','test','Peter','Griffin','1979-06-11 00:00:00','2009-07-14 19:15:44',0,'2009-07-14 19:15:44',0,NULL,1),
 (22,'tom@familyguy.com','test','Thomas','Tucker','1981-07-12 00:00:00','2009-07-14 19:15:44',0,'2009-07-14 19:15:44',0,NULL,1),
 (23,'cartman@southparkstudios.com','test','Eric','Cartman','1979-06-11 00:00:00','2009-07-14 19:15:44',0,'2009-07-14 19:15:44',0,NULL,1),
 (24,'kyle@southparkstudios.com','test','Kyle','Rafalowsky','1974-01-13 00:00:00','2009-07-14 19:15:44',0,'2009-07-14 19:15:44',0,NULL,1),
 (25,'stan@southparkstudios.com','test','Stanley','Marsh','1976-12-12 00:00:00','2009-07-14 19:15:44',0,'2009-07-14 19:15:44',0,NULL,1),
 (26,'kenny@southparkstudios.com','test','Kenny','McCormick','1979-12-20 00:00:00','2009-07-14 19:15:44',0,'2009-07-14 19:15:44',0,NULL,1);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;


--
-- Definition of table `users_addresses`
--

DROP TABLE IF EXISTS `users_addresses`;
CREATE TABLE `users_addresses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `address_id` int(10) unsigned NOT NULL,
  `address_type` enum('billing','shipping') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_users_addresses_users` (`user_id`),
  KEY `FK_users_addresses_addresses` (`address_id`),
  CONSTRAINT `FK_users_addresses_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `FK_users_addresses_addresses` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users_addresses`
--

/*!40000 ALTER TABLE `users_addresses` DISABLE KEYS */;
INSERT INTO `users_addresses` (`id`,`user_id`,`address_id`,`address_type`) VALUES 
 (1,1,1,'billing');
/*!40000 ALTER TABLE `users_addresses` ENABLE KEYS */;


--
-- Definition of table `users_roles`
--

DROP TABLE IF EXISTS `users_roles`;
CREATE TABLE `users_roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UQ_user_role` (`user_id`,`role_id`),
  KEY `FK_users_roles_roles` (`role_id`),
  CONSTRAINT `FK_users_roles_roles` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  CONSTRAINT `FK_users_roles_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users_roles`
--

/*!40000 ALTER TABLE `users_roles` DISABLE KEYS */;
INSERT INTO `users_roles` (`id`,`user_id`,`role_id`) VALUES 
 (1,1,1),
 (5,2,3),
 (6,3,2),
 (7,20,3),
 (8,21,3),
 (9,22,3),
 (10,23,3),
 (11,24,3),
 (12,25,3),
 (13,26,3);
/*!40000 ALTER TABLE `users_roles` ENABLE KEYS */;




/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
