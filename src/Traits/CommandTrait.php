<?php

/**
 * This file is part of the FzService package
 *
 * @link http://github.com/fernandozueet/service-laravel
 * @copyright 2019
 * @license MIT License
 * @author Fernando Zueet <fernandozueet@hotmail.com>
 */

namespace FzService\Traits;

trait CommandTrait {

	/**
	 * Replace values template
	 *
	 * @param array $fields
	 * @param string $template
	 * @return string
	 */
	private function replaceTemplate(array $fields = [], string $template) : string
	{
		foreach ($fields as $key => $value) {
			$template = str_replace("{{".$value["field"]."}}", $value["value"], $template);
		}

		return $template;
	}

}
