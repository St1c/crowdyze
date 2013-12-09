ALTER TABLE `attachment_type` 
	ADD COLUMN `mime` VARCHAR(128) NULL COMMENT 'Example application/json'  AFTER `type`,
	ADD UNIQUE INDEX `mime_UNIQUE` (`mime` ASC) ;
