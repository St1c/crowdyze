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
		
		if (!$ret = $this->getTable()
				->where('mime', $contentType)
				->fetch()) { // ?: self::UNKNOW_MIME_TYPE;
			list($type, $mime) = explode('/', $contentType, 2);
			$res = $this->getTable()->insert(array(
					'type' => $type,
					'mime' => $mime,
					));
			return $res->id;
		}

		return $ret;
	}

}
