<?php 
namespace Model\Services;

use Nette,
	Nette\Utils\Strings,
	Nette\Utils\Validators,
	Nette\Image,
	Nette\Http\FileUpload;
use Taco\Nette\Http\FileUploaded;
use	Model\Repositories;


/** 
 * Basic operation on filesystem.
 */
class FileManager extends Nette\Object
{

	/**
	 * @var string upload Folder 
	 */
	public $uploadFolders;
	

	/**
	 * @var string www Dir 
	 */
	public $wwwDir;


	/**
	 * @var Repositories\FileRepository
	 */
	private $filesystem;
	
	

	/**
	 * DI
	 */
	public function __construct(Repositories\FileRepository $repository) 
	{
		$this->filesystem = $repository;
	}



	/**
	 * @param enum $category tasks | users
	 * @param string $token Next level of directory.
	 * @param FileUpload $file
	 * 
	 * @throws Nette\InvalidStateException
	 * 
	 * @return string
	 */
	public function saveFile($category, $token, FileUploaded $file)
	{
		$this->assertCategory($category);
		Validators::assert($token, 'string');

		$filename = array (
				rtrim($this->wwwDir, '\\/'),
				trim($this->uploadFolders[$category], '\\/'),
				trim((string) $token, '\\/'),
				trim($file->name, '\\/'),
				);
		$this->filesystem->saveFile($file->temporaryFile, implode(DIRECTORY_SEPARATOR, $filename));

		return implode(DIRECTORY_SEPARATOR, array_slice($filename, 1));
	}



	/**
	 * @param enum $category tasks | users
	 * @param string $token Next level of directory.
	 * @param FileUpload $file
	 * 
	 * @throws Nette\InvalidStateException
	 * 
	 * @return string
	 */
	public function removeFile($category, $token, FileUploaded $file)
	{
		$this->assertCategory($category);
		Validators::assert($token, 'string');

		$filename = array (
				rtrim($this->wwwDir, '\\/'),
				trim($this->uploadFolders[$category], '\\/'),
				trim((string) $token, '\\/'),
				trim($file->getName(), '\\/'),
				);

		$this->filesystem->removeFile(implode(DIRECTORY_SEPARATOR, $filename));

		return implode(DIRECTORY_SEPARATOR, array_slice($filename, 1));
	}



	public function saveProfilePhoto($type, $id, $url)
	{
		$image = Image::fromFile($url);

		$savePath = $this->uploadFolders[$type] . (string) $id ;
		if ($this->testPath( $this->wwwDir . $savePath )) {
			
			// Name the file according the $url (google), if it contains the original name
			strpos(basename($url), '.') ? $name = basename($url) : $name = (string) $id . '.jpg';

			$image->save( $this->wwwDir . $savePath . '/' . $name);
			
			return $savePath . '/' . $name;

		} else {
			throw new \Exception("Error Processing Request", 1);
		}
	}



	public function testPath($path, $wwwDir = FALSE)
	{
		if (!$wwwDir) {
			return is_dir($path) ? TRUE : mkdir($path);
		} else {
			return is_dir($this->wwwDir . $path) ? TRUE : mkdir($this->wwwDir . $path);
		}
	}



 	/** 
 	 * Create new transaction
 	 */
	public function beginTransaction()
	{
		$this->filesystem->beginTransaction();
	}



 	/** 
 	 * Commit transaction
 	 */
	public function commitTransaction()
	{
		$this->filesystem->commitTransaction();
	}



 	/** 
 	 * Rollback transaction
 	 */
	public function rollbackTransaction()
	{
		$this->filesystem->rollbackTransaction();
	}



	/**
	 * Validate of enums.
	 */
	private function assertCategory($category, $label = 'Unknow enum value: "%{category}". Choise from %{enums}')
	{
		if (! isset($this->uploadFolders[$category])) {
			throw new \LogicException(strtr($label, array(
					'%{category}' => $category,
					'%{enums}' => implode('|', array_keys($this->uploadFolders)),
					)));
		}
		
		return $this->uploadFolders[$category];
	}


}
