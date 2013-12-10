-- 
-- Merge schema from Crowdyze.mwb
-- 


-- -----------------------------------------------------
-- Table `user`
-- -----------------------------------------------------
CREATE TABLE `user` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(16) NULL,
  `email` VARCHAR(255) NULL,
  `password` VARCHAR(255) NULL,
  `active` TINYINT(1) NULL DEFAULT '1' COMMENT '  ',
  `role` TINYTEXT NULL,
  `banned` TINYINT(1) NULL DEFAULT '0',
  `reset_key` VARCHAR(255) NULL,
  `reset_expires` TIMESTAMP NULL,
  `registered` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  UNIQUE INDEX `username_UNIQUE` (`username` ASC),
  UNIQUE INDEX `email_UNIQUE` (`email` ASC))
ENGINE = InnoDB
COMMENT = 'Table containing basic user details';


-- -----------------------------------------------------
-- Table `user_details`
-- -----------------------------------------------------
CREATE TABLE `user_details` (
  `user_id` INT(11) NOT NULL,
  `first_name` VARCHAR(45) NULL,
  `last_name` VARCHAR(45) NULL,
  `profile_photo` VARCHAR(255) NULL,
  `gender` VARCHAR(6) NULL,
  `city` VARCHAR(255) NULL,
  `country` VARCHAR(45) NULL,
  `facebook_id` VARCHAR(16) NULL,
  `google_id` VARCHAR(25) NULL,
  `updated` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE INDEX `facebook_id_UNIQUE` (`facebook_id` ASC),
  UNIQUE INDEX `user_id_UNIQUE` (`user_id` ASC),
  UNIQUE INDEX `google_id_UNIQUE` (`google_id` ASC),
  CONSTRAINT `user_id`
    FOREIGN KEY (`user_id`)
    REFERENCES `user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;



-- -----------------------------------------------------
-- Table `promotion`
-- -----------------------------------------------------
CREATE TABLE `promotion` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `type` TINYTEXT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `budget_type`
-- -----------------------------------------------------
CREATE TABLE `budget_type` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `budget_type` TINYTEXT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `task_status`
-- -----------------------------------------------------
CREATE TABLE `task_status` (
  `id` TINYINT NOT NULL,
  `status` VARCHAR(45) NOT NULL,
  `description` VARCHAR(254) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `task`
-- -----------------------------------------------------
CREATE TABLE `task` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `token` VARCHAR(8) NOT NULL,
  `owner` INT(11) NOT NULL,
  `title` VARCHAR(254) NOT NULL,
  `description` TEXT NULL COMMENT ' ',
  `salary` DECIMAL(5,2) NOT NULL,
  `budget` DECIMAL(5,2) NOT NULL DEFAULT '1.00' COMMENT 'default 1.00 $, maximum 999.99 $',
  `budget_type` INT NOT NULL,
  `workers` INT NULL,
  `deadline` TIMESTAMP NULL,
  `promotion` INT NULL,
  `status` TINYINT NOT NULL DEFAULT 0,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  INDEX `users_id_idx` (`owner` ASC),
  INDEX `budget_type_fk_idx` (`budget_type` ASC),
  INDEX `fk_tasks_promotions_idx` (`promotion` ASC),
  UNIQUE INDEX `token_UNIQUE` (`token` ASC),
  INDEX `fk_task_status_idx` (`status` ASC),
  CONSTRAINT `fk_tasks_promotions`
    FOREIGN KEY (`promotion`)
    REFERENCES `promotion` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_tasks_tasksbudgettypes`
    FOREIGN KEY (`budget_type`)
    REFERENCES `budget_type` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_tasks_users`
    FOREIGN KEY (`owner`)
    REFERENCES `user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_task_status`
    FOREIGN KEY (`status`)
    REFERENCES `task_status` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `attachment_type`
-- -----------------------------------------------------
CREATE TABLE `attachment_type` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `type` TINYTEXT NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `task_attachment`
-- -----------------------------------------------------
CREATE TABLE `task_attachment` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `task_id` BIGINT NOT NULL,
  `type_id` INT NULL,
  `path` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `tasks_id_attachments_fk_idx` (`task_id` ASC),
  INDEX `fk_tasks_attachments_Attachments_type1_idx` (`type_id` ASC),
  CONSTRAINT `fk_tasksattach_tasks`
    FOREIGN KEY (`task_id`)
    REFERENCES `task` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_tasks_attachments_Attachments_type1`
    FOREIGN KEY (`type_id`)
    REFERENCES `attachment_type` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `tag`
-- -----------------------------------------------------
CREATE TABLE `tag` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `tag` TINYTEXT NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `result`
-- -----------------------------------------------------
CREATE TABLE `result` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `task_id` BIGINT NOT NULL,
  `user_id` INT(11) NOT NULL,
  `description` TEXT NOT NULL,
  `attachments` TINYINT(1) NOT NULL DEFAULT 0,
  `created` TIMESTAMP NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `users_id_results_fk_idx` (`user_id` ASC),
  INDEX `fk_results__tasks_idx` (`task_id` ASC),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  CONSTRAINT `fk_results_tasks`
    FOREIGN KEY (`task_id`)
    REFERENCES `task` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_results_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `result_attachment`
-- -----------------------------------------------------
CREATE TABLE `result_attachment` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `result_id` BIGINT NOT NULL,
  `type_id` INT NULL,
  `path` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `results_id_res_attachments_idx` (`result_id` ASC),
  INDEX `fk_results_attachments_Attachments_type1_idx` (`type_id` ASC),
  CONSTRAINT `fk_resultsattach_results`
    FOREIGN KEY (`result_id`)
    REFERENCES `result` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_results_attachments_Attachments_type1`
    FOREIGN KEY (`type_id`)
    REFERENCES `attachment_type` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `comment`
-- -----------------------------------------------------
CREATE TABLE `comment` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `task_id` BIGINT NOT NULL,
  `user_id` INT(11) NOT NULL,
  `body` TEXT NULL,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `fk_comments_tasks1_idx` (`task_id` ASC),
  INDEX `fk_comments_users1_idx` (`user_id` ASC),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  CONSTRAINT `fk_comments_tasks`
    FOREIGN KEY (`task_id`)
    REFERENCES `task` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_comments_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wallet`
-- -----------------------------------------------------
CREATE TABLE `wallet` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `balance` DECIMAL(7,2) NOT NULL,
  `bank_accounts` VARCHAR(45) NULL,
  `int_transfer` VARCHAR(45) NULL,
  `ext_transfer` VARCHAR(45) NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_wallets_users_idx` (`user_id` ASC),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  CONSTRAINT `fk_wallets_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `internal_transfer`
-- -----------------------------------------------------
CREATE TABLE `internal_transfer` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `from` INT(11) NOT NULL,
  `to` INT(11) NOT NULL,
  `result_id` BIGINT NOT NULL,
  `amount` DECIMAL(5,2) NOT NULL,
  `type` VARCHAR(45) NOT NULL,
  `transfer_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `fk_internaltransfer_results_idx` (`result_id` ASC),
  INDEX `fk_from_walletid_idx` (`from` ASC),
  INDEX `fk_to_walletid_idx` (`to` ASC),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  CONSTRAINT `fk_internaltransfer_results`
    FOREIGN KEY (`result_id`)
    REFERENCES `result` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
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
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `message`
-- -----------------------------------------------------
CREATE TABLE `message` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `sender` INT(11) NOT NULL,
  `recipient` INT(11) NOT NULL,
  `subject` VARCHAR(255) NULL,
  `sent` TIMESTAMP NULL,
  `message` TEXT NULL,
  `read` TINYINT(1) NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `fk_messagesrecipient_users_idx` (`recipient` ASC),
  INDEX `fk_messagessender_users_idx` (`sender` ASC),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  CONSTRAINT `fk_messagessender_users`
    FOREIGN KEY (`sender`)
    REFERENCES `user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_messagesrecipient_users`
    FOREIGN KEY (`recipient`)
    REFERENCES `user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `department_type`
-- -----------------------------------------------------
CREATE TABLE `department_type` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `type` TINYTEXT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `department_name`
-- -----------------------------------------------------
CREATE TABLE `department_name` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` TINYTEXT NOT NULL,
  `owner` INT(11) NOT NULL,
  `type` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_depnames_deptypes_idx` (`type` ASC),
  INDEX `fk_depnames_users_idx` (`owner` ASC),
  CONSTRAINT `fk_depnames_deptypes`
    FOREIGN KEY (`type`)
    REFERENCES `department_type` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_depnames_users`
    FOREIGN KEY (`owner`)
    REFERENCES `user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `department_member`
-- -----------------------------------------------------
CREATE TABLE `department_member` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `department_id` INT NULL,
  `member` INT(11) NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_departments_depnames_idx` (`department_id` ASC),
  INDEX `fk_departments_users_idx` (`member` ASC),
  CONSTRAINT `fk_departments_depnames`
    FOREIGN KEY (`department_id`)
    REFERENCES `department_name` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_departments_users`
    FOREIGN KEY (`member`)
    REFERENCES `user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `task_department`
-- -----------------------------------------------------
CREATE TABLE `task_department` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `task_id` BIGINT NOT NULL,
  `department_id` INT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_tasksid_tasks_idx` (`task_id` ASC),
  INDEX `fk_tasksdep_depnames_idx` (`department_id` ASC),
  CONSTRAINT `fk_tasksid_tasks`
    FOREIGN KEY (`task_id`)
    REFERENCES `task` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_tasksdep_depnames`
    FOREIGN KEY (`department_id`)
    REFERENCES `department_name` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `accepted_status`
-- -----------------------------------------------------
CREATE TABLE `accepted_status` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `status` TINYTEXT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `accepted_task`
-- -----------------------------------------------------
CREATE TABLE `accepted_task` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `task_id` BIGINT NOT NULL,
  `status` INT NOT NULL,
  `satisfied` TINYINT(1) NULL,
  `payment` DECIMAL(5,2) NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_acceptedtasks_users_idx` (`user_id` ASC),
  INDEX `fk_acceptedtasks_tasks_idx` (`task_id` ASC),
  INDEX `fk_acceptedtasks_tasksstatus_idx` (`status` ASC),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  CONSTRAINT `fk_acceptedtasks_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_acceptedtasks_tasks`
    FOREIGN KEY (`task_id`)
    REFERENCES `task` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_acceptedtasks_tasksstatus`
    FOREIGN KEY (`status`)
    REFERENCES `accepted_status` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `task_has_tag`
-- -----------------------------------------------------
CREATE TABLE `task_has_tag` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `task_id` BIGINT NOT NULL,
  `tag_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_tasks_has_tasks_tags_tasks_tags1_idx` (`tag_id` ASC),
  INDEX `fk_tasks_has_tasks_tags_tasks1_idx` (`task_id` ASC),
  CONSTRAINT `fk_tasks_has_tasks_tags_tasks1`
    FOREIGN KEY (`task_id`)
    REFERENCES `task` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_tasks_has_tasks_tags_tasks_tags1`
    FOREIGN KEY (`tag_id`)
    REFERENCES `tag` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `bank_account`
-- -----------------------------------------------------
CREATE TABLE `bank_account` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `wallet_id` INT NOT NULL,
  `account_name` VARCHAR(45) NULL,
  `pre_account_code` INT(6) NULL,
  `account_number` INT NULL,
  `bank_code` INT NULL,
  `BIC` VARCHAR(255) NULL,
  `SWIFT` VARCHAR(255) NULL,
  `verified` TINYINT(1) NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_account_wallets_idx` (`wallet_id` ASC),
  INDEX `fk_account_users_idx` (`user_id` ASC),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  CONSTRAINT `fk_account_wallets`
    FOREIGN KEY (`wallet_id`)
    REFERENCES `wallet` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_account_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `transfer_status`
-- -----------------------------------------------------
CREATE TABLE `transfer_status` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `status` TINYTEXT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `external_transfer`
-- -----------------------------------------------------
CREATE TABLE `external_transfer` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `wallet_id` INT NOT NULL,
  `account_id` BIGINT NOT NULL,
  `amount` DECIMAL(5,2) NOT NULL,
  `transfer_date` TIMESTAMP NOT NULL,
  `status` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_exttransfer_bankaccount_idx` (`account_id` ASC),
  INDEX `fk_exttransfer_wallets_idx` (`wallet_id` ASC),
  INDEX `fk_exttransfer_transstatus_idx` (`status` ASC),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  CONSTRAINT `fk_exttransfer_bankaccount`
    FOREIGN KEY (`account_id`)
    REFERENCES `bank_account` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_exttransfer_wallets`
    FOREIGN KEY (`wallet_id`)
    REFERENCES `wallet` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_exttransfer_transstatus`
    FOREIGN KEY (`status`)
    REFERENCES `transfer_status` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `reserve`
-- -----------------------------------------------------
CREATE TABLE `reserve` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `wallet_id` INT NOT NULL,
  `task_id` BIGINT NOT NULL,
  `amount` DECIMAL(7,2) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_reserve_wallet_idx` (`wallet_id` ASC),
  INDEX `fk_reserve_task_idx` (`task_id` ASC),
  CONSTRAINT `fk_reserve_wallet`
    FOREIGN KEY (`wallet_id`)
    REFERENCES `wallet` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_reserve_task`
    FOREIGN KEY (`task_id`)
    REFERENCES `task` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- -----------------------------------------------------
-- Sekce pro odesílání newsletteru.
-- -----------------------------------------------------
-- -----------------------------------------------------



-- -----------------------------------------------------
-- Table `email`
-- -----------------------------------------------------
CREATE TABLE `email` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `email` VARCHAR(255) NOT NULL ,
  `subscribe` TINYINT(1) NULL DEFAULT TRUE ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `email_UNIQUE` (`email` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `poll`
-- -----------------------------------------------------
CREATE TABLE `poll` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `uuid` VARCHAR(13) NULL ,
  `email_id` INT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_poll_email_idx` (`email_id` ASC) ,
  UNIQUE INDEX `uuid_UNIQUE` (`uuid` ASC) ,
  CONSTRAINT `fk_poll_email`
    FOREIGN KEY (`email_id` )
    REFERENCES `email` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `question`
-- -----------------------------------------------------
CREATE TABLE `question` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `tag` VARCHAR(45) NULL ,
  `question` VARCHAR(255) NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `answer`
-- -----------------------------------------------------
CREATE TABLE `answer` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `poll_id` INT NULL ,
  `question_id` INT NULL ,
  `answer` VARCHAR(255) NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_answer_poll_idx` (`poll_id` ASC) ,
  INDEX `fk-answer_question_idx` (`question_id` ASC) ,
  CONSTRAINT `fk_answer_poll`
    FOREIGN KEY (`poll_id` )
    REFERENCES `poll` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_answer_question`
    FOREIGN KEY (`question_id` )
    REFERENCES `question` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- EOL
