<?php
/**
 * This file is part of the Taco Projects.
 *
 * Copyright (c) 2004, 2013 Martin Takáč (http://martin.takac.name)
 *
 * For the full copyright and license information, please view
 * the file LICENCE that was distributed with this source code.
 *
 * PHP version 5.3
 *
 * @author     Martin Takáč (martin@takac.name)
 */

namespace Taco\Nette\Application\Responses;


use Nette,
	Nette\Image,
	Nette\Application,
	Nette\Http\IRequest,
	Nette\Http\IResponse,
	Nette\Utils\MimeTypeDetector;


/**
 * Image response
 *
 * @author	Martin Takáč
 */
class ImageResponse extends Nette\Object implements Application\IResponse
{
	/** 
	 * @var Nette\Image|string 
	 */
	private $image;


	/**
	 * @param Nette\Image|string
	 */
	public function __construct(Image $image)
	{
		$this->image = $image;
	}



	/**
	 * @param Nette\Http\IRequest
	 * @param Nette\Http\IResponse
	 */
	public function send(IRequest $httpRequest, IResponse $httpResponse)
	{
		$this->image->send();
	}

}
