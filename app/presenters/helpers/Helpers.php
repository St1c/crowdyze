<?php

namespace App;


use Nette;


/**
 * Common helpers.
 */
class Helpers extends Nette\Object
{

	private $translator;



	function __construct($translator)
	{
		$this->translator = $translator;
	}



    public function loader($helper)
    {
        if (method_exists($this, $helper)) {
            return array($this, $helper);
        }
    }



	/**
	 * Translate mime type to class name.
	 * @param int $type
	 * @return string
	 */
    public function promotionClass($type)
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
	public function mediaType($type)
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
	public function daysLeft(\DateTime $deadline = Null)
	{
		if (!$deadline) {
			return '';
		}

		$deadline = $deadline->format('Y-m-d H:i:s');
		
		//Calculate difference
		$seconds = strtotime($deadline) - time(); 	//time returns current time in seconds
		if ($seconds < 0 ) {
			return 'a';
		}

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
	 * Human text from date.
	 * @return string
	 */
	public function daysAgo(\DateTime $date = Null)
	{
		if (empty($date)) {
			return Null;
		}

		$diff = $date->diff(new \DateTime());

		if ($diff->y) {
			return strtr($this->translator->translate('helpers.daysAgo.years', $diff->y), array(
					'%{val}' => $diff->y,
					));
		}
		
		if ($diff->m) {
			return strtr($this->translator->translate('helpers.daysAgo.months', $diff->m), array(
					'%{val}' => $diff->m,
					));
		}
		
		if ($diff->d) {
			return strtr($this->translator->translate('helpers.daysAgo.days', $diff->d), array(
					'%{val}' => $diff->d,
					));
		}
		
		if ($diff->h) {
			return strtr($this->translator->translate('helpers.daysAgo.hours', $diff->h), array(
					'%{val}' => $diff->h,
					));
		}

		if ($diff->i) {
			return strtr($this->translator->translate('helpers.daysAgo.minutes', $diff->i), array(
					'%{val}' => $diff->i,
					));
		}

		if ($diff->s) {
			return strtr($this->translator->translate('helpers.daysAgo.seconds', $diff->s), array(
					'%{val}' => $diff->s,
					));
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
