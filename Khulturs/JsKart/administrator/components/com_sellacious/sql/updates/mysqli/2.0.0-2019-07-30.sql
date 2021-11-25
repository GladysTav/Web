-- 2.0.0

ALTER TABLE `#__sellacious_shoprule_slabs`
ADD `country` INT(11) NOT NULL DEFAULT '0' AFTER `max`,
ADD `state` INT(11) NOT NULL DEFAULT '0' AFTER `country`,
ADD `zip` VARCHAR(10) NOT NULL AFTER `state`;
