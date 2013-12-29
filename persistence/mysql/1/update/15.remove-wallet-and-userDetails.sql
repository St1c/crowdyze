ALTER TABLE `transfer` 
DROP FOREIGN KEY `fk_to_walletid`;

ALTER TABLE `budget` 
DROP FOREIGN KEY `fk_budget_wallet`;

ALTER TABLE `user` 
CHANGE COLUMN `banned` `banned` TINYINT(1) NULL DEFAULT '0' AFTER `reset_expires`,
ADD COLUMN `wallet` DECIMAL(7,2) NOT NULL AFTER `password`,
ADD COLUMN `first_name` VARCHAR(45) NULL DEFAULT NULL AFTER `role`,
ADD COLUMN `last_name` VARCHAR(45) NULL DEFAULT NULL AFTER `first_name`,
ADD COLUMN `profile_photo` VARCHAR(255) NULL DEFAULT NULL AFTER `last_name`,
ADD COLUMN `gender` VARCHAR(6) NULL DEFAULT NULL AFTER `profile_photo`,
ADD COLUMN `city` VARCHAR(255) NULL DEFAULT NULL AFTER `gender`,
ADD COLUMN `country` VARCHAR(45) NULL DEFAULT NULL AFTER `city`,
ADD COLUMN `facebook_id` VARCHAR(16) NULL DEFAULT NULL AFTER `country`,
ADD COLUMN `google_id` VARCHAR(25) NULL DEFAULT NULL AFTER `facebook_id`;

UPDATE
    `user`, `user_details`
SET
    `user`.`first_name` = `user_details`.`first_name`,
    `user`.`last_name` = `user_details`.`last_name`,
    `user`.`profile_photo` = `user_details`.`profile_photo`,
    `user`.`gender` = `user_details`.`gender`,
    `user`.`city` = `user_details`.`city`,
    `user`.`country` = `user_details`.`country`,
    `user`.`facebook_id` = `user_details`.`facebook_id`,
    `user`.`google_id` = `user_details`.`google_id`
WHERE
    `user`.`id` = `user_details`.`user_id`;


UPDATE `user` SET wallet = 99.00;


UPDATE
    `user`, `wallet`
SET
    `user`.`wallet` = `wallet`.`balance`
WHERE
    `user`.`id` = `wallet`.`user_id`;

ALTER TABLE `budget` 
DROP COLUMN `wallet_id`,
ADD COLUMN `user_id` INT(11) NOT NULL AFTER `id`,
ADD INDEX `fk_budget_user_idx` (`user_id` ASC),
DROP INDEX `fk_budget_wallet_idx` ;


UPDATE `budget` SET `budget`.`user_id` = 101;

DROP TABLE IF EXISTS `wallet` ;

DROP TABLE IF EXISTS `user_details` ;

ALTER TABLE `transfer` 
ADD CONSTRAINT `fk_to_userid`
  FOREIGN KEY (`to`)
  REFERENCES `user` (`id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;


ALTER TABLE `budget` 
ADD CONSTRAINT `fk_budget_user`
  FOREIGN KEY (`user_id`)
  REFERENCES `user` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;