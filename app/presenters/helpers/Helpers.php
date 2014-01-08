<?php

namespace App;


use Nette;


/**
 * Homepage presenter.
 */
class Helpers extends Nette\Object
{

    public static function loader($helper)
    {
        if (method_exists(__CLASS__, $helper)) {
            return array(__CLASS__, $helper);
        }
    }


    public static function promotionClass($s)
    {
		switch ($s) {
			case 2:
				return 'promo-max';
			case 1:
				return 'promo-medium';
			default:
				return Null;
		}
    }




	/**
	 * 
	 */
	public static function daysLeft($deadline)
	{
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


}
