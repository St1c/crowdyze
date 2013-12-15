ALTER TABLE `task` 
DROP COLUMN `budget`,
CHANGE COLUMN `description` `description` TEXT NULL DEFAULT NULL COMMENT '  ' ;

ALTER TABLE `wallet` 
DROP COLUMN `ext_transfer`,
DROP COLUMN `int_transfer`,
DROP COLUMN `bank_accounts`;


DROP TABLE IF EXISTS `reserve`;

-- -----------------------------------------------------
-- Table `budget`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `budget` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `wallet_id` INT NOT NULL,
  `task_id` BIGINT NOT NULL,
  `budget` DECIMAL(7,2) NOT NULL,
  `fee` DECIMAL(7,2) NOT NULL,
  `commission` DECIMAL(7,2) NOT NULL,
  `promotion_fee` DECIMAL(7,2) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_budget_wallet_idx` (`wallet_id` ASC),
  INDEX `fk_budget_task_idx` (`task_id` ASC),
  CONSTRAINT `fk_budget_wallet`
    FOREIGN KEY (`wallet_id`)
    REFERENCES `wallet` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_budget_task`
    FOREIGN KEY (`task_id`)
    REFERENCES `task` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;

