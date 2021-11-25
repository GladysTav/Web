ALTER TABLE `#__sellacious_shippingrule_slabs`
 ADD `origin_country` INT(11) NOT NULL AFTER `max`,
 ADD `origin_state` INT(11) NOT NULL AFTER `origin_country`,
 ADD `origin_zip` VARCHAR(10) NOT NULL AFTER `origin_state`;

ALTER TABLE `#__sellacious_shoprule_slabs`
 ADD `origin_country` INT(11) NOT NULL AFTER `max`,
 ADD `origin_state` INT(11) NOT NULL AFTER `origin_country`,
 ADD `origin_zip` VARCHAR(10) NOT NULL AFTER `origin_state`;
