<?php

namespace PPA\Rest\Utils;

use InvalidArgumentException;
use Phalcon\Mvc\Model\Criteria;

class Macros extends Text
{
	/**
	 * @param string $string String with macros.
	 * @param array $params Params for replace.
	 * @param string $modelName Model name.
	 * @return string Replacement string.
	 */
	public static function replace($string, $params, $modelName) {
		foreach (self::getMacrosMethods() as $macros => $method) {
			if (!preg_match('/'. $macros . '/', $string)) {continue;}
			$replacement = self::$method($string, $params, $modelName);
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
	 * @param $modelName Model name.
	 * @return string
	 */
	private static function replaceColumns($string, array $params, $modelName) {
		$columns = isset($params['columns']) ? $params['columns'] : self::getModelColumns($modelName);
		$columns = implode(',', $columns);
		return preg_replace('/{columns}/', $columns, $string);
	}

	/**
	 * @param string $string
	 * @param array $params
	 * @param $modelName Model name.
	 * @return string
	 */
	private static function replaceColumnsLikes($string, array $params, $modelName) {
		$columns = isset($params['columns']) ? $params['columns'] : self::getModelColumns($modelName);
		$columns = array_map(function($column) {
			return $column . ' LIKE :search:';
		}, $columns);
		$columns = implode(' OR ', $columns);
		return preg_replace('/{columns likes}/', $columns, $string);
	}

	/**
	 * @param $modelName
	 * @return array
	 */
	private static function getModelColumns($modelName) {
		/**
		 * @var \Phalcon\Mvc\Model $model
		 */
		$model = new $modelName();
		return $model->getModelsMetaData()->getAttributes($model);
	}
}