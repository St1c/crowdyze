/*
-- Query: 
-- Date: 2013-12-05 18:16
*/
INSERT INTO `task_status` (`id`,`status`,`description`) VALUES (0,'Not approved','New task, before admin approval');
INSERT INTO `task_status` (`id`,`status`,`description`) VALUES (1,'Approved','Active task, no workers yet, editing possible');
INSERT INTO `task_status` (`id`,`status`,`description`) VALUES (2,'Paused','Paused task, workers assigned, editing possible');
INSERT INTO `task_status` (`id`,`status`,`description`) VALUES (3,'Locked','Active task, workers assigned, no editing');
INSERT INTO `task_status` (`id`,`status`,`description`) VALUES (4,'Finished','Finished taks');
INSERT INTO `task_status` (`id`,`status`,`description`) VALUES (5,'Cancelled','Cancelled task');
INSERT INTO `task_status` (`id`,`status`,`description`) VALUES (6,'Banned','Disapproved task by admin');
