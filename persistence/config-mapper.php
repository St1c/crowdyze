<?php

//	Switch to devel mode.
$_SERVER['REMOTE_ADDR'] = '::1';

// Let bootstrap create Dependency Injection container.
$container = require __DIR__ . '/../app/bootstrap.php';

//	Rewrite dbname and user account.
$source->schema[0]->database[0] = $container->parameters['database']['dbname'];
foreach ($source->access[0] as $row) {
	if ($row['type'] == 'user') {
		$row['login'] = $container->parameters['database']['user'];
		$row['password'] = $container->parameters['database']['password'];
	}
}

return $source;
