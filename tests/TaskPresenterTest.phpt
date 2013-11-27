<?php

namespace Test;

use Nette,
	Tester,
	Tester\Assert;

$container = require __DIR__ . '/bootstrap.php';
require __DIR__ . '/Presenter.php';


class TaskPresenterTest extends Tester\TestCase
{
	public function __construct(\Nette\DI\Container $container) {
			// $this->tester = new \Test\Presenter($container);
			$this->container = $container;
	}

	public function setUp() {
		$presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
       	$this->presenter = $presenterFactory->createPresenter('Task');
       	$this->presenter->autoCanonicalize = FALSE;

   		$admin = new Nette\Security\Identity(1, 'admin', array('nick' => 'admin'));
		$user = $this->container->getByType('Nette\Security\User');
		$user->login($admin);
	}

	// public function testRenderDefault() {
	// 	$request = new \Nette\Application\Request('Task', 'GET', array('action' => 'detail'));
 //        $response = $this->presenter->run($request);

	// 	\Tester\Assert::true($response instanceof \Nette\Application\Responses\TextResponse);
 //        \Tester\Assert::true($response->getSource() instanceof \Nette\Templating\ITemplate);

 //        $html = (string)$response->getSource();
 //        $dom = \Tester\DomQuery::fromHtml($html);
 //        \Tester\Assert::true($dom->has('title'));
	// }


    public function dataId()
    {
        return array(
        	array('f5n8qy2e'),
        	array('e3zk0x32'),
        	array('6r2zp5er'),
        	array('do5rmpt3'),
        	array('do5rmpt4')
        );
    }

    /**
     * @dataProvider dataId
     */
	public function testActionTask($id)
	{

		$request = new \Nette\Application\Request('Task', 'GET', array('action' => 'detail', 'id' => $id));
        $response = $this->presenter->run($request);
                
		if ($id == 'do5rmpt4') {
			\Tester\Assert::true($response instanceof \Nette\Application\Responses\RedirectResponse);
			echo '1';
		} else {
			\Tester\Assert::true($response instanceof \Nette\Application\Responses\TextResponse);
            \Tester\Assert::true($response->getSource() instanceof \Nette\Templating\ITemplate);

            $html = (string)$response->getSource();
            $dom = \Tester\DomQuery::fromHtml($html);
            echo '2';
            \Tester\Assert::true($dom->has('title'));
		}
	}

}


id(new TaskPresenterTest($container))->run();
