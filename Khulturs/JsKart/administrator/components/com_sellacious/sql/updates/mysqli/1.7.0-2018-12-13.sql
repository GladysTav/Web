ALTER TABLE `#__sellacious_shoprules` ADD `method_name` VARCHAR(100) NOT NULL DEFAULT '*' AFTER `type`;

CREATE TABLE IF NOT EXISTS `#__sellacious_shoprule_slabs` (
  `rule_id` int(11) NOT NULL,
  `min` double NOT NULL DEFAULT '0',
  `max` double NOT NULL DEFAULT '0',
  `price` double NOT NULL DEFAULT '0',
  `unit` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
