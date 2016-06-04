<?php

namespace PPA\Rest\Utils;

class Text extends \Phalcon\Text
{
	/**
	 * CamelCasing and converts the first character of the string to lowercase.
	 * @param string $column
	 * @return string
	 */
	public static function getColumnLowerCase($column) {
		$column = Text::uncamelize($column . "");
		$column = Text::camelize($column);
		return lcfirst($column);
	}
}