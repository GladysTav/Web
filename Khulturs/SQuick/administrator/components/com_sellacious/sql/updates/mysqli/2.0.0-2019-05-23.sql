ALTER TABLE `#__sellacious_eproduct_media`
 ADD `files_group` VARCHAR(100) NOT NULL AFTER `tags`;

ALTER TABLE `#__sellacious_eproduct_downloads`
 ADD `ep_media_id` int(11) NOT NULL AFTER `delivery_id`,
 ADD `media_ctx` varchar(10) NOT NULL AFTER `ep_media_id`;

ALTER TABLE `#__sellacious_media`
 ADD `on_cloud` int(1) NOT NULL DEFAULT '0' AFTER `state`;

UPDATE `#__sellacious_eproduct_media`
 SET `files_group` = 'files';
