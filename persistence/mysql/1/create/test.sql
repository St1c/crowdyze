-- 
-- Merge schema from Crowdyze.mwb
-- 


-- -----------------------------------------------------
-- Table `user`
-- -----------------------------------------------------
CREATE TABLE `user` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `username` VARCHAR(16) NULL ,
  `email` VARCHAR(255) NULL ,
  `password` VARCHAR(255) NULL ,
  `active` TINYINT(1) NULL DEFAULT '1' COMMENT 'enable, disable' ,
  `role` TINYTEXT NULL ,
  `banned` TINYINT(1) NULL DEFAULT '0' ,
  `reset_key` VARCHAR(255) NULL ,
  `reset_expires` TIMESTAMP NULL ,
  `registered` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `id_UNIQUE` (`id` ASC) ,
  UNIQUE INDEX `username_UNIQUE` (`username` ASC) ,
  UNIQUE INDEX `email_UNIQUE` (`email` ASC) )
ENGINE = InnoDB
COMMENT = 'Table containing basic user details';



-- -----------------------------------------------------
-- Table `user_details`
-- -----------------------------------------------------
CREATE TABLE `user_details` (
  `user_id` INT(11) NOT NULL ,
  `first_name` VARCHAR(45) NULL ,
  `last_name` VARCHAR(45) NULL ,
  `profile_photo` VARCHAR(255) NULL ,
  `gender` VARCHAR(6) NULL ,
  `city` VARCHAR(255) NULL ,
  `country` VARCHAR(45) NULL ,
  `facebook_id` VARCHAR(16) NULL ,
  `google_id` VARCHAR(25) NULL ,
  `updated` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
  PRIMARY KEY (`user_id`) ,
  UNIQUE INDEX `facebook_id_UNIQUE` (`facebook_id` ASC) ,
  UNIQUE INDEX `user_id_UNIQUE` (`user_id` ASC) ,
  UNIQUE INDEX `google_id_UNIQUE` (`google_id` ASC) ,
  CONSTRAINT `user_id`
    FOREIGN KEY (`user_id` )
    REFERENCES `user` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;



-- -----------------------------------------------------
-- Table `accepted_task`
-- -----------------------------------------------------
CREATE TABLE `accepted_task` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `user_id` INT(11) NOT NULL ,
  `task_id` BIGINT NOT NULL ,
  `status` INT NOT NULL ,
  `satisfied` TINYINT(1) NULL ,
  `payment` DECIMAL(5,2) NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_acceptedtasks_users_idx` (`user_id` ASC) ,
  INDEX `fk_acceptedtasks_tasks_idx` (`task_id` ASC) ,
  INDEX `fk_acceptedtasks_tasksstatus_idx` (`status` ASC) ,
  UNIQUE INDEX `id_UNIQUE` (`id` ASC) ,
  CONSTRAINT `fk_acceptedtasks_users`
    FOREIGN KEY (`user_id` )
    REFERENCES `user` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_acceptedtasks_tasks`
    FOREIGN KEY (`task_id` )
    REFERENCES `task` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_acceptedtasks_tasksstatus`
    FOREIGN KEY (`status` )
    REFERENCES `task_status` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;



-- -----------------------------------------------------
-- Table `budget_type`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `budget_type` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `budget_type` TINYTEXT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;



-- -----------------------------------------------------
-- Table `task`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `task` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `token` VARCHAR(8) NOT NULL ,
  `owner` INT(11) NOT NULL ,
  `title` VARCHAR(254) NOT NULL ,
  `description` TEXT NULL COMMENT '	' ,
  `salary` DECIMAL(5,2) NOT NULL ,
  `budget` DECIMAL(5,2) NOT NULL DEFAULT '1.00' COMMENT 'default 1.00 $, maximum 999.99 $' ,
  `budget_type` INT NOT NULL ,
  `workers` INT NULL ,
  `deadline` TIMESTAMP NULL ,
  `promotion` INT NULL ,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `id_UNIQUE` (`id` ASC) ,
  INDEX `users_id_idx` (`owner` ASC) ,
  INDEX `budget_type_fk_idx` (`budget_type` ASC) ,
  INDEX `fk_tasks_promotions_idx` (`promotion` ASC) ,
  UNIQUE INDEX `token_UNIQUE` (`token` ASC) ,
  CONSTRAINT `fk_tasks_promotions`
    FOREIGN KEY (`promotion` )
    REFERENCES `promotion` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_tasks_tasksbudgettypes`
    FOREIGN KEY (`budget_type` )
    REFERENCES `budget_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_tasks_users`
    FOREIGN KEY (`owner` )
    REFERENCES `user` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;




-- -----------------------------------------------------
-- Table `attachment_type`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `attachment_type` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `type` TINYTEXT NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `task_attachment`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `task_attachment` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `task_id` BIGINT NOT NULL ,
  `type_id` INT NULL ,
  `path` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `tasks_id_attachments_fk_idx` (`task_id` ASC) ,
  INDEX `fk_tasks_attachments_Attachments_type1_idx` (`type_id` ASC) ,
  CONSTRAINT `fk_tasksattach_tasks`
    FOREIGN KEY (`task_id` )
    REFERENCES `task` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_tasks_attachments_Attachments_type1`
    FOREIGN KEY (`type_id` )
    REFERENCES `attachment_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `tag`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `tag` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `tag` TINYTEXT NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;




-- -----------------------------------------------------
-- Table `wallet`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `wallet` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `user_id` INT(11) NOT NULL ,
  `balance` DECIMAL(7,2) NOT NULL ,
  `reserved` DECIMAL(7,2) NULL ,
  `bank_accounts` VARCHAR(45) NULL ,
  `int_transfer` VARCHAR(45) NULL ,
  `ext_transfer` VARCHAR(45) NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_wallets_users_idx` (`user_id` ASC) ,
  UNIQUE INDEX `id_UNIQUE` (`id` ASC) ,
  CONSTRAINT `fk_wallets_users`
    FOREIGN KEY (`user_id` )
    REFERENCES `user` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;



-- -----------------------------------------------------
-- Table `task_status`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `task_status` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `status` TINYTEXT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;



-- -----------------------------------------------------
-- Table `accepted_task`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `accepted_task` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `user_id` INT(11) NOT NULL ,
  `task_id` BIGINT NOT NULL ,
  `status` INT NOT NULL ,
  `satisfied` TINYINT(1) NULL ,
  `payment` DECIMAL(5,2) NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_acceptedtasks_users_idx` (`user_id` ASC) ,
  INDEX `fk_acceptedtasks_tasks_idx` (`task_id` ASC) ,
  INDEX `fk_acceptedtasks_tasksstatus_idx` (`status` ASC) ,
  UNIQUE INDEX `id_UNIQUE` (`id` ASC) ,
  CONSTRAINT `fk_acceptedtasks_users`
    FOREIGN KEY (`user_id` )
    REFERENCES `user` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_acceptedtasks_tasks`
    FOREIGN KEY (`task_id` )
    REFERENCES `task` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_acceptedtasks_tasksstatus`
    FOREIGN KEY (`status` )
    REFERENCES `task_status` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;




-- -----------------------------------------------------
-- Table `task_has_tag`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `task_has_tag` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `task_id` BIGINT NOT NULL ,
  `tag_id` INT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_tasks_has_tasks_tags_tasks_tags1_idx` (`tag_id` ASC) ,
  INDEX `fk_tasks_has_tasks_tags_tasks1_idx` (`task_id` ASC) ,
  CONSTRAINT `fk_tasks_has_tasks_tags_tasks1`
    FOREIGN KEY (`task_id` )
    REFERENCES `task` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_tasks_has_tasks_tags_tasks_tags1`
    FOREIGN KEY (`tag_id` )
    REFERENCES `tag` (`id` )
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
CREATE  TABLE IF NOT EXISTS `email` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `email` VARCHAR(255) NOT NULL ,
  `subscribe` TINYINT(1) NULL DEFAULT TRUE ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `email_UNIQUE` (`email` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `poll`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `poll` (
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
CREATE  TABLE IF NOT EXISTS `question` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `tag` VARCHAR(45) NULL ,
  `question` VARCHAR(255) NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `answer`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `answer` (
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
