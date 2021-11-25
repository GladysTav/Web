-- 2.0.0 changes
CREATE TABLE IF NOT EXISTS `#__sellacious_user_favorites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `context` varchar(255) CHARACTER SET utf8 NOT NULL,
  `author_id` int(11) NOT NULL,
  `record_id` int(11) NOT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
