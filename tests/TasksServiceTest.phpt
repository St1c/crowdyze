<?php

namespace Test;

use Nette,
	Tester,
	Tester\Assert;

$container = require __DIR__ . '/bootstrap.php';

// var_dump($container);

class TasksServiceTest extends Tester\TestCase
{
	private $container;
	private $taskService;

	function __construct(Nette\DI\Container $container )
	{
		$this->container = $container;
		$this->taskService = $this->container->getByType('Model\Services\TaskService');
	}


	function setUp()
	{
	}

	function testGetTask()
	{
		Assert::equal( 5, $this->taskService->getTask(5)->id);
	}

	function testSomething()
	{
		Assert::true( true );
	}

}


id(new TasksServiceTest($container))->run();
