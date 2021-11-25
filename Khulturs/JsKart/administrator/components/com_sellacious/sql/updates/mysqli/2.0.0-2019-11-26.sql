ALTER TABLE `#__sellacious_mailqueue`
  ADD `type` VARCHAR(20) NOT NULL DEFAULT 'email' AFTER `context`;
