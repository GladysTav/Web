ALTER TABLE `#__sellacious_sellers` ADD `store_location_address` MEDIUMTEXT NOT NULL AFTER `store_location`;
ALTER TABLE `#__sellacious_cache_products` ADD `product_location` VARCHAR(100) NOT NULL AFTER `primary_video_url`;

CREATE TABLE IF NOT EXISTS `#__sellacious_geolocation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `context` varchar(50) NOT NULL,
  `record_id` int(11) NOT NULL,
  `address` tinytext NOT NULL,
  `coordinates` varchar(100) NOT NULL,
  `zip` varchar(20) NOT NULL,
  `sublocality` varchar(250) NOT NULL,
  `locality` varchar(250) NOT NULL,
  `city` varchar(250) NOT NULL,
  `district` varchar(250) NOT NULL,
  `state` varchar(250) NOT NULL,
  `country` varchar(250) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure added if it doesn't exist during update from 1.5.3
--

CREATE TABLE IF NOT EXISTS `#__sellacious_seller_timings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `seller_uid` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `week_day` int(11) NOT NULL,
  `from_time` time NOT NULL DEFAULT '00:00:00',
  `to_time` time NOT NULL DEFAULT '00:00:00',
  `full_day` tinyint(1) DEFAULT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '1',
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `#__sellacious_seller_timings` ADD `slot_window` INT(11) NOT NULL AFTER `full_day`;
ALTER TABLE `#__sellacious_seller_timings` ADD `slot_window_unit` VARCHAR(50) NOT NULL AFTER `slot_window`;
ALTER TABLE `#__sellacious_seller_timings` ADD `today_availability` TIME NOT NULL DEFAULT '00:00:00' AFTER `slot_window_unit`;

--
-- Table structure for table `#__sellacious_order_delivery_slot`
--

CREATE TABLE IF NOT EXISTS `#__sellacious_order_delivery_slot` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_item_id` int(11) NOT NULL,
  `slot_from_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `slot_to_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `full_day` TINYINT(1) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `#__sellacious_product_seller_slot_limits`
--

CREATE TABLE IF NOT EXISTS `#__sellacious_product_seller_slot_limits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `seller_uid` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `slot_from_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `slot_to_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `full_day` TINYINT(1) NOT NULL,
  `slot_limit` int(11) NOT NULL,
  `slot_count` int(11) NOT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

