<?php

namespace Test;

use Nette,
	Tester,
	Tester\Assert;

$container = require __DIR__ . '/bootstrap.php';


class UserPresenterTest extends Tester\TestCase
{
	public function __construct(\Nette\DI\Container $container) {
		$this->tester = new \Test\Presenter($container);
		$this->container = $container;
	}

	public function setUp() {
		// $this->tester->init('User');

		$presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
       	$this->presenter = $presenterFactory->createPresenter('Task');
       	$this->presenter->autoCanonicalize = FALSE;

		$admin = new Nette\Security\Identity(104, 'user', array('username' => 'user'));
		$user = $this->container->getByType('Nette\Security\User');
		$user->login($admin);
	}

	public function testRenderDefault() {


		$request = new \Nette\Application\Request('User', 'GET', array('action' => 'default'));
        $response = $this->presenter->run($request);
                
		\Tester\Assert::true($response instanceof \Nette\Application\Responses\TextResponse);
        \Tester\Assert::true($response->getSource() instanceof \Nette\Templating\ITemplate);

        $html = (string)$response->getSource();

        $dom = \Tester\DomQuery::fromHtml($html);
        \Tester\Assert::true($dom->has('title'));

			// $this->tester->testAction('default');
	}

}


id(new UserPresenterTest($container))->run();
