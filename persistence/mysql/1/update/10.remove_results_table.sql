ALTER TABLE `result_attachment` 
DROP FOREIGN KEY `fk_resultsattach_results`;

ALTER TABLE `result_attachment` 
ADD INDEX `fk_resultsattach_accepted_idx` (`result_id` ASC),
DROP INDEX `results_id_res_attachments_idx` ;

ALTER TABLE `accepted_task` 
ADD COLUMN `result` TEXT NULL DEFAULT NULL AFTER `status`,
ADD COLUMN `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `result`;

DROP TABLE IF EXISTS `result` ;

ALTER TABLE `result_attachment` 
ADD CONSTRAINT `fk_resultsattach_accepted`
  FOREIGN KEY (`result_id`)
  REFERENCES `accepted_task` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;