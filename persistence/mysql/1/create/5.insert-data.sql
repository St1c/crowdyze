-- 
-- Merge schema from Crowdyze.mwb
-- 



-- -----------------------------------------------------
-- Data for table `budget_type`
-- -----------------------------------------------------
INSERT INTO `budget_type` (`id`, `budget_type`) VALUES (1, 'Pay only the best one');
INSERT INTO `budget_type` (`id`, `budget_type`) VALUES (2, 'Pay best 10 users');
INSERT INTO `budget_type` (`id`, `budget_type`) VALUES (3, 'Pay all');



-- -----------------------------------------------------
-- Data for table `crowdyze`.`task_status`
-- -----------------------------------------------------
INSERT INTO `task_status` (`id`, `status`, `description`) VALUES (0, 'Not approved', 'New task, before admin approval');
INSERT INTO `task_status` (`id`, `status`, `description`) VALUES (1, 'Approved', 'Active task, no workers yet, editing possible');
INSERT INTO `task_status` (`id`, `status`, `description`) VALUES (2, 'Paused', 'Paused task, workers assigned, editing possible');
INSERT INTO `task_status` (`id`, `status`, `description`) VALUES (3, 'Locked', 'Active task, workers assigned, no editing');
INSERT INTO `task_status` (`id`, `status`, `description`) VALUES (4, 'Finished', 'Finished taks');
INSERT INTO `task_status` (`id`, `status`, `description`) VALUES (5, 'Cancelled', 'Cancelled task');
INSERT INTO `task_status` (`id`, `status`, `description`) VALUES (6, 'Banned', 'Disapproved task by admin');



-- -----------------------------------------------------
-- Data for table `attachment_type`
-- -----------------------------------------------------
INSERT INTO `attachment_type` (`id`, `type`) VALUES (1, 'image');
INSERT INTO `attachment_type` (`id`, `type`) VALUES (2, 'doc');
INSERT INTO `attachment_type` (`id`, `type`) VALUES (3, 'file');



-- -----------------------------------------------------
-- Data for table `accepted_status`
-- -----------------------------------------------------
INSERT INTO `accepted_status` (`id`, `status`) VALUES (1, 'accepted');
INSERT INTO `accepted_status` (`id`, `status`) VALUES (2, 'pending');
INSERT INTO `accepted_status` (`id`, `status`) VALUES (3, 'satisfied');
INSERT INTO `accepted_status` (`id`, `status`) VALUES (4, 'unsatisfied');



-- -----------------------------------------------------
-- Data for table `transfer_status`
-- -----------------------------------------------------
INSERT INTO `transfer_status` (`id`, `status`) VALUES (1, 'successfull');
INSERT INTO `transfer_status` (`id`, `status`) VALUES (2, 'failed');
INSERT INTO `transfer_status` (`id`, `status`) VALUES (3, 'not-finished');



-- -----------------------------------------------------
-- Data for table `question`
-- -----------------------------------------------------
INSERT INTO `question` (`id`, `tag`, `question`) VALUES (1, 'question1', 'Ak by ste mali moznost zadavat ponuky prace pre externych pracovnikov prostrednictvom online sluzby, vyuzili by ste takuto sluzbu?');
INSERT INTO `question` (`id`, `tag`, `question`) VALUES (2, 'question2', 'Vyuzili by ste moznost budovania komunity externych spolupracovnikov, ktori pre Vas budu pracovat?');
INSERT INTO `question` (`id`, `tag`, `question`) VALUES (3, 'question3', 'Ste ochotni zadavat pracu aj externým pracovníkov z krajín EÚ?');
INSERT INTO `question` (`id`, `tag`, `question`) VALUES (4, 'question4', 'Pri akej uspore nakladov by ste uprednostnili zadanie prace pre online spolupracovnikov pred sposobom aky vyuzivate teraz?');
INSERT INTO `question` (`id`, `tag`, `question`) VALUES (5, 'question5', 'V akej oblasti v rámci Vašej firmy by ste takúto spoluprácu najviac privítali?');
INSERT INTO `question` (`id`, `tag`, `question`) VALUES (6, 'question6', 'Aký parameter je pre zadávanie práce externým spolupracovníkom najdôležitejší?');



-- EOL
