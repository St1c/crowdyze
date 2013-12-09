ALTER TABLE `task_attachment` 
	ADD UNIQUE INDEX `path_task_id_UNIQUE` (`path` ASC, `task_id` ASC);
