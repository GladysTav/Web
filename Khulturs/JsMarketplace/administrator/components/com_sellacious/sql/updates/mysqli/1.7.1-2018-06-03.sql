-- 1.7.1 Changes

CREATE TABLE IF NOT EXISTS `#__sellacious_shoprule_class` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alias` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` VARCHAR(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_id` (`title`,`alias`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__sellacious_class_shoprules_xref` (
  `shoprule_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  UNIQUE KEY `shoprule_id` (`shoprule_id`,`class_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__sellacious_rule_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `context` VARCHAR(50) NOT NULL,
  `rule_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `product_id` varchar(100) NOT NULL COMMENT 'Product Id or Code',
  `assignment` TINYINT(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `#__sellacious_shoprules`
ADD `apply_on_all_products` TINYINT(1) NOT NULL AFTER `sum_method`;

ALTER TABLE `#__sellacious_coupons`
ADD `apply_on_all_products` TINYINT(1) NOT NULL AFTER `max_discount_total`;

ALTER TABLE `#__sellacious_orders`
ADD `product_shipping_taxes` DOUBLE NOT NULL AFTER `product_shipping`,
ADD `product_shipping_discounts` DOUBLE NOT NULL AFTER `product_shipping_taxes`,
ADD `product_shipping_subtotal` DOUBLE NOT NULL AFTER `product_shipping_discounts`;

ALTER TABLE `#__sellacious_orders`
ADD `shipping_shoprules` TEXT NOT NULL AFTER `shoprules`;

ALTER TABLE `#__sellacious_order_items`
ADD `shipping_taxes` DOUBLE NOT NULL AFTER `shipping_amount`,
ADD `shipping_discounts` DOUBLE NOT NULL AFTER `shipping_taxes`,
ADD `shipping_subtotal` DOUBLE NOT NULL AFTER `shipping_discounts`;

ALTER TABLE `#__sellacious_order_items`
ADD `shipping_shoprules` TEXT NOT NULL AFTER `shoprules`;
