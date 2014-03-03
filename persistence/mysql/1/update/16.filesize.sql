ALTER TABLE `task_attachment` ADD COLUMN `size` INT(11) NOT NULL DEFAULT 0  AFTER `path` ;
ALTER TABLE `result_attachment` ADD COLUMN `size` INT(11) NOT NULL DEFAULT 0  AFTER `path` ;
