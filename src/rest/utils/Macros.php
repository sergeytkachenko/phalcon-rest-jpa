<?php

namespace PPA\Rest\Utils;

use InvalidArgumentException;
use Phalcon\Mvc\Model\Criteria;

class Macros extends Text
{
	/**
	 * @param string $string String with macros.
	 * @param array $params Params for replace.
	 * @return string Replacement string.
	 */
	public static function replace($string, $params) {
		foreach (self::getMacrosMethods() as $macros => $method) {
			if (!preg_match('/'. $macros . '/', $string)) {continue;}
			$replacement = self::$method($string, $params);
			return $replacement;
		}
		return $string;
	}

	/**
	 * @return array
	 */
	private static function getMacrosMethods() {
		return array(
			'{columns}' => 'replaceColumns',
			'{columns likes}' => 'replaceColumnsLikes'
		);
	}

	/**
	 * @param string $string
	 * @param array $params
	 * @return string
	 */
	private static function replaceColumns($string, array $params) {
		if (!isset($params['columns'])) {
			throw new InvalidArgumentException('param "columns" mast be required');
		}
		$columns = $params['columns'];
		$columns = implode(',', $columns);
		return preg_replace('/{columns}/', $columns, $string);
	}

	/**
	 * @param string $string
	 * @param array $params
	 * @return string
	 */
	private static function replaceColumnsLikes($string, array $params) {
		if (!isset($params['columns'])) {
			throw new InvalidArgumentException('param "columns" mast be required');
		}
		$columns = array_map(function($column) {
			return $column . ' LIKE :search:';
		}, $params['columns']);
		$columns = implode(' OR ', $columns);
		return preg_replace('/{columns likes}/', $columns, $string);
	}
}