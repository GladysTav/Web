ALTER TABLE `#__sellacious_shoprules`
ADD `protected` TINYINT(3) NOT NULL AFTER `apply_rule_on_list_price`;

ALTER TABLE `#__sellacious_profiles`
  DROP COLUMN `bankinfo`,
  DROP COLUMN `taxinfo`;
