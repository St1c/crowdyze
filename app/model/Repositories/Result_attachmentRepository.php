<?php
namespace Model\Repositories;

use Nette\Database\Connection,
	Nette\Database\Table\ActiveRow,
	Nette\Utils\Validators
	;

class Result_attachmentRepository extends BaseRepository
{


	/**
	 * Integrity constraint violation: 1062 Duplicate entry...
	 */
	const ERROR_DUPLICATE_ENTRY = 23000;


	/**
	 * @param Result
	 * @param string $path
	 * @param int $contentType
	 */
	public function saveAttachment($result, $path, $contentType)
	{
		Validators::assert($path, 'string');
		//~ Validators::assert($contentType, 'int');

		try {
			$result->related('result_attachment')
					->insert(array(
							'path' => $path,
							'type_id' => $contentType,
							));
		}
		catch (\PDOException $e) {
			if (! self::ERROR_DUPLICATE_ENTRY == $e->getCode()) {
				throw $e;
			}

			$result->related('result_attachment')
					->select('`result_attachment`.id')
					->where('`result_attachment`.path', $path)
					->update(array(
							'type_id' => $contentType,
							));
		}
	}



	/**
	 * @param Result
	 * @param string $path
	 */
	public function removeAttachment($result, $path)
	{
		Validators::assert($path, 'string');

		$result->related('result_attachment')
				->where('path', $path)
				->delete();
	}



}
