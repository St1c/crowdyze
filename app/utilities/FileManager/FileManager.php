<?php 
namespace Utilities;

use Nette,
	Nette\Utils\Strings,
	Nette\Image,
	Nette\Http\FileUpload;

/** 
 * Basic operation on database tables
 */

class FileManager extends Nette\Object
{

	/** @var string upload Folder */
	public $uploadFolders;
	/** @var string www Dir */
	public $wwwDir;

	public function saveFile($type, $uuid, FileUpload $upload)
	{
		$savePath = $this->uploadFolders[$type] . (string) $uuid;

		if ($this->testPath( $this->wwwDir . $savePath )) {
			
			$upload->move( $this->wwwDir . $savePath . '/' . $upload->getSanitizedName() );
			
			return $savePath . '/' . $upload->getSanitizedName();

		} else {
			throw new \Exception("Error Processing Request", 1);
		}
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

	private function testPath($path)
	{
		return is_dir($path) ? TRUE : mkdir($path);
	}

}