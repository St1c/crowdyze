<?php

namespace App;


use Nette;


/**
 * Common helpers.
 */
class Helpers extends Nette\Object
{

    public static function loader($helper)
    {
        if (method_exists(__CLASS__, $helper)) {
            return array(__CLASS__, $helper);
        }
    }



	/**
	 * Translate mime type to class name.
	 * @param int $type
	 * @return string
	 */
    public static function promotionClass($type)
    {
		switch ($type) {
			case 3:
				return 'promo-max';
			case 2:
				return 'promo-medium';
			default:
				return Null;
		}
    }



	/**
	 * Translate mime type to class name.
	 * @param string $type
	 * @return string
	 */
	public static function mediaType($type)
	{
		switch ($type) {
			case 'video':
				return 'file-avi';
			case 'music':
				return 'file-mp3';
			case 'image':
				return 'file-img';
			default:
				return 'file-doc';
		}
    }



	/**
	 * Human text from date.
	 * @return string
	 */
	public static function daysLeft(\DateTime $deadline = Null)
	{
		//~ dump($deadline);
		//~ exit;
		if (!$deadline) {
			return '';
		}
		
		//Calculate difference
		$seconds = strtotime($deadline) - time(); 	//time returns current time in seconds
		if ($seconds < 0 ) return '';

		$days 		= floor($seconds / 86400);
		$seconds 	%= 86400;

		$hours 		= floor($seconds / 3600);
		$seconds 	%= 3600;

		$minutes 	= floor($seconds / 60);
		$seconds 	%= 60;

		if ($days >= 1) {
			return "$days days left";
		}
		
		if ($hours >= 1) {
			return "$hours hours left";
		}
		else {
			return "$minutes minutes left";
		}
	}


	/**
	 * Translate result status to class.
	 * @param int $type
	 * @return string
	 */
    public static function resultClass($type)
    {
		switch ($type) {
			case 4:
				return 'not-satisfied';
			case 3:
				return 'satisfied';
			case 2:
				return 'pending';
			default:
				return 'accepted';
		}
    }

}
