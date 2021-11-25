SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Table structure for table `#__sellacious_seller_hyperlocal`
--

CREATE TABLE IF NOT EXISTS `#__sellacious_seller_hyperlocal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `seller_uid` int(11) NOT NULL,
  `shipping_location_type` tinyint(1) NOT NULL,
  `shipping_distance` text NOT NULL,
  `params` TEXT NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `seller_uid` (`seller_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `#__sellacious_seller_timings`
--

CREATE TABLE IF NOT EXISTS `#__sellacious_seller_timings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `seller_uid` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `week_day` int(11) NOT NULL,
  `from_time` time NOT NULL DEFAULT '00:00:00',
  `to_time` time NOT NULL DEFAULT '00:00:00',
  `full_day` tinyint(1),
  `slot_window` INT(11) NOT NULL,
  `slot_window_unit` VARCHAR(50) NOT NULL,
  `today_availability` TIME NOT NULL DEFAULT '00:00:00',
  `state` tinyint(1) NOT NULL DEFAULT '1',
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `#__sellacious_cache_distances`
--

CREATE TABLE IF NOT EXISTS `#__sellacious_cache_distances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `seller_uid` int(11) NOT NULL,
  `hash` varchar(100) NOT NULL,
  `seller_shipping_distance` text NOT NULL,
  `shipping_filter_lat` float NOT NULL,
  `shipping_filter_long` float NOT NULL,
  `store_location_lat` float NOT NULL,
  `store_location_long` float NOT NULL,
  `distance` float NOT NULL,
  `cache_created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
