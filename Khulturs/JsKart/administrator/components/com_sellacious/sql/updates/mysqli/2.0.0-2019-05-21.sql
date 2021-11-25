ALTER TABLE `#__sellacious_eproduct_media`
 ADD `media_url` VARCHAR(250) NOT NULL AFTER `variant_id`,
 ADD `sample_url` VARCHAR(250) NOT NULL AFTER `media_url`,
 ADD `media_type` VARCHAR(10) NOT NULL DEFAULT 'upload' AFTER `sample_url`,
 ADD `sample_type` VARCHAR(10) NOT NULL DEFAULT 'upload' AFTER `media_type`,
 ADD `notes` VARCHAR(250) DEFAULT '' AFTER `hotlink`,
 ADD `params` text NOT NULL AFTER `modified_by`;

UPDATE `#__sellacious_eproduct_media`
 SET `media_type` = 'upload', `sample_type` = 'upload';
