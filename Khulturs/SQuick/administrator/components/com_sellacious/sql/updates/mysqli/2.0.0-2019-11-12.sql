ALTER TABLE `#__sellacious_order_status`
ADD `seller_uid` INT(11) NOT NULL AFTER `order_id`;

UPDATE `#__sellacious_statuses` SET `stock` = 'R' WHERE `#__sellacious_statuses`.`type` = 'approved' AND `#__sellacious_statuses`.`context` = 'order';
UPDATE `#__sellacious_statuses` SET `stock` = 'A' WHERE `#__sellacious_statuses`.`type` = 'delivered' AND `#__sellacious_statuses`.`context` = 'order';
UPDATE `#__sellacious_statuses` SET `stock` = 'O' WHERE `#__sellacious_statuses`.`type` = 'shipped' AND `#__sellacious_statuses`.`context` = 'order';
