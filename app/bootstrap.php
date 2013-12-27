<?php

// Load Nette Framework or autoloader generated by Composer
require __DIR__ . '/../vendor/autoload.php';


$configurator = new Nette\Configurator;

// Enable Nette Debugger for error visualisation & logging
$configurator->setDebugMode($configurator::AUTO);
// $configurator->setProductionMode();
$configurator->enableDebugger(__DIR__ . '/../log');

// Specify folder for cache
umask(0);

$configurator->setTempDirectory(__DIR__ . '/../temp');

// Enable RobotLoader - this will load all classes automatically
$configurator->createRobotLoader()
	->addDirectory(__DIR__)
	->addDirectory(__DIR__ . '/../libs')
	// ->addDirectory(__DIR__ . '/../vendor/valum/')
	//~ ->addDirectory(__DIR__ . '/../tests/')
	->register();

// Create Dependency Injection container from config.neon file
$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . '/config/config.local.neon');

$container = $configurator->createContainer();


return $container;
