
DROP TABLE IF EXISTS `internal_transfer` ;

DROP TABLE IF EXISTS `external_transfer` ;

DROP TABLE IF EXISTS `bank_account` ;

DROP TABLE IF EXISTS `transfer_status` ;



CREATE TABLE IF NOT EXISTS `transfer` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `from` INT(11) NOT NULL,
  `to` INT(11) NOT NULL,
  `amount` DECIMAL(5,2) NOT NULL,
  `type` VARCHAR(45) NOT NULL,
  `transfer_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `fk_from_walletid_idx` (`from` ASC),
  INDEX `fk_to_walletid_idx` (`to` ASC),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  CONSTRAINT `fk_from_walletid`
    FOREIGN KEY (`from`)
    REFERENCES `wallet` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_to_walletid`
    FOREIGN KEY (`to`)
    REFERENCES `wallet` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

ALTER TABLE `budget` 
CHANGE COLUMN `fee` `fee` DECIMAL(7,2) NOT NULL AFTER `task_id`;

CREATE TABLE IF NOT EXISTS `income_type` (
  `id` TINYINT(4) NOT NULL AUTO_INCREMENT,
  `type` VARCHAR(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `ind_UNIQUE` (`id` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `income` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `from` BIGINT(20) NOT NULL,
  `type` TINYINT(4) NOT NULL,
  `amount` DECIMAL(7,2) NOT NULL,
  `note` TEXT NULL DEFAULT NULL,
  `time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  INDEX `fk_income_type_idx` (`type` ASC),
  INDEX `fk_income_budget_idx` (`from` ASC),
  CONSTRAINT `fk_income_budget`
    FOREIGN KEY (`from`)
    REFERENCES `budget` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_income_type`
    FOREIGN KEY (`type`)
    REFERENCES `income_type` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

