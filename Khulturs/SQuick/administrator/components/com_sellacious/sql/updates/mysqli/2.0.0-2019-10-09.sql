ALTER TABLE `#__sellacious_product_sellers`
  ADD `pricing_type` VARCHAR(50) NOT NULL DEFAULT '' AFTER `stock_sold`;

ALTER TABLE `#__sellacious_orders`
  ADD `order_source` VARCHAR(100) NOT NULL AFTER `order_number`;
