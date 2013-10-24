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
		$router[] = new Route('[<locale [a-z]{2}>/]<presenter>/<action>[/<id>]', array(
			'locale' 		=> 'en',
			'presenter' => 'Homepage',
			'action' 	=> 'default',
			'id'		=> NULL
		));
		return $router;
	}

}
