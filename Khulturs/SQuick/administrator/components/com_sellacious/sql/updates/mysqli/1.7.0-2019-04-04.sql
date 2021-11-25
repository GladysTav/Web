ALTER TABLE `#__sellacious_shippingrules` ADD `apply_on_all_products` TINYINT(1) NOT NULL DEFAULT '1' AFTER `amount_additional`;
ALTER TABLE `#__sellacious_shoprules` ADD `apply_rule_on_list_price` TINYINT(1) NOT NULL AFTER `apply_rule_on_price_display`;
