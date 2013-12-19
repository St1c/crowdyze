ALTER TABLE `result` 
DROP COLUMN `attachments`,
CHANGE COLUMN `description` `result` TEXT NOT NULL;

ALTER TABLE `accepted_task` 
DROP COLUMN `payment`,
DROP COLUMN `satisfied`;
