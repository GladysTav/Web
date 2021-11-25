-- 2.0.0 changes
ALTER TABLE `#__sellacious_cart_info`
ADD `seller_ship_quotes` TEXT NOT NULL AFTER `ship_quote_id`,
ADD `seller_ship_quote_ids` TEXT NOT NULL AFTER `seller_ship_quotes`;

ALTER TABLE `#__sellacious_orders`
ADD `seller_shipping_rules` TEXT NOT NULL AFTER `shipping_params`,
ADD `seller_shipping_rule_ids` TEXT NOT NULL AFTER `seller_shipping_rules`;
