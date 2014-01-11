<?php

namespace App;


use Nette\Application\UI\Presenter;
use Model\Services\FileManager;
use Taco\Nette\Application\Responses\ImageResponse;


class FilePresenter extends BasePresenter
{


    /**
     * @var Model\Services\FileManager 
     */
    private $filesystemModel;


	public function injectFilesystemModel(FileManager $model)
	{
		$this->filesystemModel = $model;
		return $this;
	}



	public function renderImage($path, $type, $x, $y, $w, $h)
	{
		$entry = $this->filesystemModel->getImageThumbBy($path, $type, $x, $y, $w, $h);
		$this->sendResponse(new ImageResponse($entry));
	}


}
