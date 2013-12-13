<?php
namespace Model\Repositories;

use Nette\Database\Connection,
	Nette\Database\Table\ActiveRow,
	Nette\Utils\Validators;

class Attachment_typeRepository extends BaseRepository
{

	const UNKNOW_MIME_TYPE = 1;


	/**
	 * @param string $contentType
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function findContentType($contentType)
	{
		Validators::assert($contentType, 'string');
		
		return $this->getTable()
				->where('mime', $contentType)
				->fetch() ?: self::UNKNOW_MIME_TYPE;
	}

}
