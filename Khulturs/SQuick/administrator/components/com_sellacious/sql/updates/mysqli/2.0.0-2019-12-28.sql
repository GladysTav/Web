ALTER TABLE `#__sellacious_mailqueue`
CHANGE `lock_token` `lock_token` VARCHAR(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '';

ALTER TABLE `#__sellacious_emailtemplates`
ADD `message_type` VARCHAR(30) NOT NULL AFTER `send_attachment`;

UPDATE `#__sellacious_emailtemplates` SET message_type = 'email' WHERE message_type = '';
