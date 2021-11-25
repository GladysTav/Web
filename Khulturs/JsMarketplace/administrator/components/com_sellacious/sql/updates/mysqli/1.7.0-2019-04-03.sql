ALTER TABLE `#__sellacious_product_sellers`
  ADD `product_address` MEDIUMTEXT NOT NULL AFTER `quantity_max`,
  ADD `product_location` VARCHAR(100) NOT NULL AFTER `product_address`;
