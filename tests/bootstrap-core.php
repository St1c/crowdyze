<?php

// The Nette Tester command-line runner can be
// invoked through the command: ../vendor/bin/tester .

if ( ! include __DIR__ . '/../vendor/autoload.php') {
	exit(1);
}


// configure environment
Tester\Environment::setup();
date_default_timezone_set('Europe/Prague');


// create temporary directory
define('TEMP_DIR', __DIR__ . '/../temp/ntest/' . getmypid());
@mkdir(dirname(TEMP_DIR)); // @ - directory may already exist
Tester\Helpers::purge(TEMP_DIR);


function before(\Closure $function = NULL)
{
	static $val;
	if (!func_num_args()) {
		return ($val ? $val() : NULL);
	}
	$val = $function;
}


function test(\Closure $function)
{
	before();
	$function();
}
