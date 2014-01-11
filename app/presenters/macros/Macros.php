<?php
/**
 * This file is part of the Taco Projects.
 *
 * Copyright (c) 2004, 2013 Martin Takáč (http://martin.takac.name)
 *
 * For the full copyright and license information, please view
 * the file LICENCE that was distributed with this source code.
 *
 * PHP version 5.3
 *
 * @author     Martin Takáč (martin@takac.name)
 */


namespace App\Nette\Latte;


use Nette,
	Nette\Latte\MacroNode,
	Nette\Latte\PhpWriter;


/**
 * Macros for Nette\Forms.
 *
 * - {errors name}
 *
 * @author		Martin Takáč <taco@taco-beru.name>
 * @credits 	David Grudl
 */
class MediaMacros extends Nette\Latte\Macros\MacroSet
{

	public static function install(Nette\Latte\Compiler $compiler)
	{
		$set = new static($compiler);
		$set->addMacro('thumbnail', array($set, 'macroThumbnail'));
	}



	/**
	 * {thumbnail <string> (small|medium|big)}
	 */
	public function macroThumbnail(MacroNode $node, PhpWriter $writer)
	{
		$args = 'array ('
			. '"type" => %node.args,'
			. '"path" => %node.word,'
			//~ . '"x" => %node.word->thumbLeftPosition,'
			//~ . '"y" => %node.word->thumbTopPosition,'
			//~ . '"w" => %node.word->thumbWidth,'
			//~ . '"h" => %node.word->thumbHeight,'
			. ')';
		$res = $writer->write('echo %escape(%modify(' 
				. '$_presenter' 
				. "->link(':File:image', {$args})))");
		return $res;
	}



}
