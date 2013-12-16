ALTER TABLE `budget` 
CHANGE COLUMN `commission` `commission` DECIMAL(8,3) NOT NULL ,
CHANGE COLUMN `promotion_fee` `promotion_fee` DECIMAL(8,3) NULL DEFAULT NULL;

ALTER TABLE `income` 
CHANGE COLUMN `amount` `amount` DECIMAL(8,3) NOT NULL;

ALTER TABLE `income_type` 
CHANGE COLUMN `type` `type` VARCHAR(254) NOT NULL;

INSERT INTO `income_type` (`id`, `type`) VALUES (1, 'fee for creating a job');
INSERT INTO `income_type` (`id`, `type`) VALUES (2, 'commisssion');
INSERT INTO `income_type` (`id`, `type`) VALUES (3, 'promotion fee');
INSERT INTO `income_type` (`id`, `type`) VALUES (4, 'other');