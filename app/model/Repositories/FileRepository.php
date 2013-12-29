<?php 
namespace Model\Repositories;

use Nette,
	Nette\Utils\Strings,
	Nette\Utils\Validators;
use Symfony\Component\Filesystem\Filesystem,
	Symfony\Component\Filesystem\Exception\IOException;


/** 
 * Basic operation on filesystem.
 */
class FileRepository extends Nette\Object
{


	/**
	 * @var Filesystem 
	 */
	private $filesystem;
	
	
	/**
	 * List of action in transaction if any.
	 * @var array | Null
	 */
	private $transactions = Null;
	
	
	
 	/** 
 	 * Create new transaction
 	 */
	public function isTransaction()
	{
		return ($this->transactions !== Null);
	}



 	/** 
 	 * Create new transaction
 	 */
	public function beginTransaction()
	{
		if ($this->isTransaction()) {
			throw new \RuntimeException('Is any not-commited transaction.');
		}

		$this->transactions = array();
		return $this;
	}



 	/** 
 	 * Commit transaction
 	 */
	public function commitTransaction()
	{
		if (! $this->isTransaction()) {
			throw new \RuntimeException('Transaction not found.');
		}

		//	Fire transction
		foreach ($this->transactions as $action) {
			$action();
		}

		return $this;
	}



 	/** 
 	 * Rollback transaction
 	 */
	public function rollbackTransaction()
	{
		if (! $this->isTransaction()) {
			throw new \RuntimeException('Transaction not found.');
		}

		$this->transactions = Null;
		return $this;
	}



	/**
	 * @param string $src Source file name.
	 * @param string $desc Destination file name.
	 * 
	 * @throws Nette\InvalidStateException
	 */
	public function saveFile($src, $desc)
	{
		Validators::assert($src, 'string');
		Validators::assert($desc, 'string');

		if (! $this->isTransaction()) {
			$this->fetchSaveFile($src, $desc);
		}
		else {
			$this->transactions[] = function() use ($src, $desc) {
				$this->fetchSaveFile($src, $desc);
			};
		}
	}



	/**
	 * @param string $file Source file name.
	 * 
	 * @throws Nette\InvalidStateException
	 */
	public function removeFile($file)
	{
		Validators::assert($file, 'string');

		if (! $this->isTransaction()) {
			$this->fetchRemoveFile($file);
		}
		else {
			$this->transactions[] = function() use ($file) {
				$this->fetchRemoveFile($file);
			};
		}
	}



	/**
	 * @param string $src Source file name.
	 * @param string $desc Destination file name.
	 * 
	 * @throws Nette\InvalidStateException
	 * 
	 * @return string
	 */
	private function fetchSaveFile($src, $dest)
	{
		$dir = dirname($dest);
		if (! file_exists($dir)) {
			$this->getFilesystem()->mkdir($dir, 0777, True);
			$this->getFilesystem()->chmod(dirname($dest), 0777);
		}
		$this->getFilesystem()->rename($src, $dest, True);
	}



	/**
	 * @param string $file
	 * 
	 * @throws Nette\InvalidStateException
	 * 
	 * @return string
	 */
	private function fetchRemoveFile($file)
	{
		Validators::assert($file, 'string');

		if (file_exists($file)) {
			$this->getFilesystem()->remove($file);
		}
	}



	private function getFilesystem()
	{
		if (empty($this->filesystem)) {
			$this->filesystem = new Filesystem();
		}
		return $this->filesystem;
	}

}
