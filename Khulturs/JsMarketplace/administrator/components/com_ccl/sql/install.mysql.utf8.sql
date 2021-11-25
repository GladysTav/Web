CREATE TABLE IF NOT EXISTS `#__ccl_user_details` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`user_id` INT(11)  NOT NULL ,
`social_plugin` VARCHAR(100)  NOT NULL ,
`social_identifier` VARCHAR(50)  NOT NULL ,
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `#__ccl_user_details` ADD UNIQUE( `user_id`, `social_plugin`, `social_identifier`);