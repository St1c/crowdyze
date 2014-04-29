<?php

namespace App;

use Nette,
	Nette\Application\Routers\RouteList,
	Nette\Application\Routers\Route,
	Nette\Application\Routers\SimpleRouter;


/**
 * Router factory.
 */
class RouterFactory
{

	/**
	 * @return \Nette\Application\IRouter
	 */
	public function createRouter()
	{
		$router = new RouteList();
		$router[] = new Route('article/<id>', 'Article:view');
		$router[] = new Route('search/<q>', 'Search:default');
		$router[] = new Route('content/<category>/<token>/<path>', array(
			'presenter' => 'File',
			'action' 	=> 'file',
			'category'	=> NULL,
			'token'		=> NULL,
			'path'		=> NULL,
		));
		$router[] = new Route('<presenter>/<action>[/<token>]', array(
			'presenter' => 'Homepage',
			'action' 	=> 'default',
			'token'		=> NULL
		));
		return $router;
	}

}
