<?php 
namespace Utilities;

use Nette,
	Nette\Utils\Strings,
	Nette\Utils\Validators,
	Nette\Image,
	Nette\Http\FileUpload;
use Symfony\Component\Filesystem\Filesystem,
	Symfony\Component\Filesystem\Exception\IOException;
use Taco\Nette\Http\FileUploaded;


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
	 * @var Filesystem 
	 */
	private $filesystem;
	
	
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
		$dest = implode(DIRECTORY_SEPARATOR, $filename);
		$dir = dirname($dest);
		if (! file_exists($dir)) {
			$this->getFilesystem()->mkdir($dir, 0777, True);
			$this->getFilesystem()->chmod(dirname($dest), 0777);
		}
		$this->getFilesystem()->rename($file->temporaryFile, $dest, True);

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
		$file = implode(DIRECTORY_SEPARATOR, $filename);
		if (file_exists($file)) {
			$this->getFilesystem()->remove($file);
		}

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


	private function getFilesystem()
	{
		if (empty($this->filesystem)) {
			$this->filesystem = new Filesystem();
		}
		return $this->filesystem;
	}

}
