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

	const SIZE_SMALL = 's';
	const SIZE_MEDIUM = 'm';
	const SIZE_BIG = 'b';


	/**
	 * @var string upload Folder 
	 */
	private $uploadFolders;
	

	/**
	 * @var string www Dir 
	 */
	private $wwwDir;


	/**
	 * @var Repositories\FileRepository
	 */
	private $filesystem;


	/**
	 * Rozměry náhledu.
	 * @var object | Null
	 */
	private $size;
	

	/**
	 * DI
	 */
	public function __construct(Repositories\FileRepository $repository, $uploadFolders, $wwwDir, array $size) 
	{
		$this->filesystem = $repository;
		$this->uploadFolders = $uploadFolders;
		//~ $this->wwwDir = $repository->assertAccessDir($wwwDir);
		$this->wwwDir = $wwwDir;
		foreach ($size as $n => $thumb) {
			$this->size[$n] = (object) array(
					'width' => $thumb['w'],
					'height' => $thumb['h'],
					);
		}
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
	 * @param Nette\Http\FileUpload $file
	 * 
	 * @throws Nette\InvalidStateException
	 * 
	 * @return string
	 */
	public function saveFileUpload($category, $token, FileUpload $file)
	{
		$this->assertCategory($category);
		Validators::assert($token, 'string');

		$filename = array (
				rtrim($this->wwwDir, '\\/'),
				trim($this->uploadFolders[$category], '\\/'),
				trim((string) $token, '\\/'),
				trim($file->name, '\\/'),
				);
		$this->filesystem->saveFileUpload($file, implode(DIRECTORY_SEPARATOR, $filename));

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

		}
		else {
			throw new \Exception("Error Processing Request", 1);
		}
	}



	public function testPath($path, $wwwDir = FALSE)
	{
		if (!$wwwDir) {
			return is_dir($path) ? TRUE : mkdir($path);
		}
		else {
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
	 * Důležité si uvědomit, že bez ohledu na rozměry, 
	 * náhled je vždy jeden, takže nelze vytvářet DoS.
	 * Aby se přegeneroval náhled, je nutné jej smazat.
	 * 
	 * @param string $path Source file name.
	 * @param string $size Typ velikosti.
	 * @param int $left Umístění výřezu z leva.
	 * @param int $top Umístění výřezu ze zhora.
	 * @param int $width Šířka výřezu.
	 * @param int $height Víška výřezu.
	 * 
	 * @throws Nette\InvalidStateException
	 */
	public function getImageThumbBy($path, $size, $left, $top, $width, $height)
	{
		Validators::assert($path, 'string');
		
		switch (strtolower($size)) {
			case 'big':
			case self::SIZE_BIG:
				$version = self::SIZE_BIG;
				$size = $this->size['big'];
				break;
			case 'medium':
			case self::SIZE_MEDIUM:
				$version = self::SIZE_MEDIUM;
				$size = $this->size['medium'];
				break;
			case 'small':
			case self::SIZE_SMALL:
				$version = self::SIZE_SMALL;
				$size = $this->size['small'];
				break;
				
			default:
				throw new \InvalidArgumentException("Unknow size '$size'.");
		}

		//~ Validators::assert($left, 'int');
		//~ Validators::assert($top, 'int');
		//~ Validators::assert($width, 'int');
		//~ Validators::assert($height, 'int');

		$originalPath = implode(DIRECTORY_SEPARATOR, array(
				$this->wwwDir,
				$path,
				));
		if (! file_exists($originalPath)) {
			throw new \RuntimeException("Original file '$originalPath' not found.");
		}

		$previewPath = implode(DIRECTORY_SEPARATOR, array(
				$this->wwwDir,
				$path,
				));
		$previewPath = self::decorateVersion($previewPath, $version);

		//	Máme už náhled.
		if (file_exists($previewPath)) {
			return Image::fromFile($previewPath);
		}

		$originalFile = Image::fromFile($originalPath);
		if ($width && $height) {
			$originalFile->crop($left, $top, $width, $height);
		}
		$originalFile->resize($size->width, $size->height, Image::EXACT | Image::SHRINK_ONLY);
		
		$this->filesystem->saveImage($originalFile, $previewPath);
		
		return $originalFile;
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



	private static function decorateVersion($file, $version)
	{
		return dirname($file)
				. DIRECTORY_SEPARATOR
				. ($version ? "{$version}-" : Null)
				. basename($file);
	}


}
