-- 1.7.3 Changes

CREATE TABLE IF NOT EXISTS `#__sellacious_user_gst` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `user_email` VARCHAR(100),
  `gst_num` varchar(50) NOT NULL,
  `pan_num` varchar(50) NOT NULL,
  `params` TEXT NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ;

CREATE TABLE IF NOT EXISTS `#__sellacious_product_gst` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `hsn_sac` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
