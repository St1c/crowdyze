<?php
namespace Model\Repositories;

use Nette\Database\Connection,
	Nette\Database\Table\ActiveRow;

class Attachment_typeRepository extends BaseRepository
{

	/**
	 * @param string $contentType
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function findContentType($contentType)
	{
		return $this->getTable()
				->where('mime', $contentType)
				->fetch();
	}

}
