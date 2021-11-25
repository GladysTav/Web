ALTER TABLE `#__sellacious_product_sellers`
 ADD `loc_country` VARCHAR(120) DEFAULT '' AFTER `product_location`,
 ADD `loc_state` VARCHAR(120) DEFAULT '' AFTER `loc_country`,
 ADD `loc_district` VARCHAR(120) DEFAULT '' AFTER `loc_state`,
 ADD `loc_city` VARCHAR(120) DEFAULT '' AFTER `loc_district`,
 ADD `loc_locality` VARCHAR(120) DEFAULT '' AFTER `loc_city`,
 ADD `loc_sublocality` VARCHAR(120) DEFAULT '' AFTER `loc_locality`,
 ADD `loc_zip` VARCHAR(10) DEFAULT '' AFTER `loc_sublocality`;

ALTER TABLE `#__sellacious_sellers`
 ADD `loc_country` VARCHAR(120) DEFAULT '' AFTER `store_location_address`,
 ADD `loc_state` VARCHAR(120) DEFAULT '' AFTER `loc_country`,
 ADD `loc_district` VARCHAR(120) DEFAULT '' AFTER `loc_state`,
 ADD `loc_city` VARCHAR(120) DEFAULT '' AFTER `loc_district`,
 ADD `loc_locality` VARCHAR(120) DEFAULT '' AFTER `loc_city`,
 ADD `loc_sublocality` VARCHAR(120) DEFAULT '' AFTER `loc_locality`,
 ADD `loc_zip` VARCHAR(10) DEFAULT '' AFTER `loc_sublocality`;

ALTER TABLE `#__sellacious_sellers` DROP `store_location_address`;

DROP TABLE IF EXISTS `#__sellacious_seller_hyperlocal`;

DROP TABLE IF EXISTS `#__sellacious_geolocation`;
