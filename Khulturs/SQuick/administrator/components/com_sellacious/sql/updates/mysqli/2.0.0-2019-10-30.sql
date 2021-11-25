ALTER TABLE `#__sellacious_addresses` ADD `first_name` VARCHAR(100) NOT NULL AFTER `name`;
ALTER TABLE `#__sellacious_addresses` ADD `middle_name` VARCHAR(100) NOT NULL AFTER `first_name`;
ALTER TABLE `#__sellacious_addresses` ADD `last_name` VARCHAR(100) NOT NULL AFTER `middle_name`;
ALTER TABLE `#__sellacious_addresses` ADD `address_line_1` VARCHAR(200) NULL AFTER `address`;
ALTER TABLE `#__sellacious_addresses` ADD `address_line_2` VARCHAR(200) NULL AFTER `address_line_1`;
ALTER TABLE `#__sellacious_addresses` ADD `address_line_3` VARCHAR(200) NULL AFTER `address_line_2`;
