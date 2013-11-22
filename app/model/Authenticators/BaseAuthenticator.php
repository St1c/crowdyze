<?php
namespace Model\Authenticators;

use Nette,
	Model\Services;

class BaseAuthenticator
{

	/** @var SignService */
	protected $signService;


	public function __construct(Services\SignService $signService)
	{
		$this->signService = $signService;
	}

}
