-- ALTER TABLE `transfer` 
-- ADD INDEX `fk_from_walletid_idx` (`from` ASC),
-- DROP INDEX `fk_from_walletid_idx` ;

-- ALTER TABLE `transfer` 
-- ADD CONSTRAINT `fk_from_walletid`
--   FOREIGN KEY (`from`)
--   REFERENCES `budget` (`id`)
--   ON DELETE NO ACTION
--   ON UPDATE NO ACTION;
