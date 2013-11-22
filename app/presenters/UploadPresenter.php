<?php

namespace App;

use Nette,
	Model,
	Nette\Application\Responses\JsonResponse;


/**
 * Error presenter.
 */
class UploadPresenter extends BasePresenter
{

	/** @var Model\Services\TaskService @inject */
	public $taskService;
	/** @var Utilities\FileManager @inject */
	public $fileManager;

	public function actionTemp($id, $qqfile)
	{
		// $token = $this->taskService->generateTaskToken();
		
		$allowedExtensions = array('jpg','JPG','png','jpeg');
		// max file size in bytes
		$sizeLimit = 10 * 1024 * 1024;

		$uploader = new \qqFileUploader($allowedExtensions, $sizeLimit);		

		// Test if folder for the given task exists
		$this->fileManager->testPAth('/uploads_temp/'. $id . '/', true);
		// Save file to the folder
		$result = $uploader->handleUpload('uploads_temp/' . $id . '/');

		if($result['success']) {
			$this->sendResponse(new JsonResponse(array(
				'response'	=> 'true',
				'success'	=> true,
				'file' 		=> $uploader->getName(),
				'ext'		=> pathinfo('uploads_temp/' . $id . '/' . $uploader->getName(), PATHINFO_EXTENSION),
				'location' 	=> 'uploads_temp/' . $id . '/'
			)));
		}
	}

}
