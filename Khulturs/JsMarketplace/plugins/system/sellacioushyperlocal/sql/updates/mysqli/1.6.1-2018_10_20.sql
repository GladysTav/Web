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
