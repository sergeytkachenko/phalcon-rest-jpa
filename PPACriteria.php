<?
namespace Lib\Rest;
use Phalcon\Text;

/**
 * Class PPACriteria phalcon persistence api
 * @package Lib\Rest
 */
abstract class PPACriteria
{
	const AND_PATTERN = '/([a-z0-9])And([A-Z0-9])/';
	const OR_PATTERN = '/([a-z0-9])Or([A-Z0-9])/';

	/**
	 * @param $url
	 * @param $params
	 * @return array|\Phalcon\Mvc\Model\ResultsetInterface
	 */
	public static function fetch($url, $params) {
		$hasMany = self::hasMany($url);
		$criteria = self::buildCriteria($url, $params);
		if (!$hasMany) {
			$criteria->limit(1);
		}
		return $criteria->execute();
	}

	/**
	 * @param $url
	 * @param $params
	 * @return array|\Phalcon\Mvc\Model\ResultsetInterface
	 */
	public static function fetchWithRelations($url, $params) {
		$models = self::fetch($url, $params);
		if ($models === array()) {return array();}
		return $models->filter(function($model) {
			return $model->fetchRelations();
		});
	}

	/**
	 * @param $url
	 * @param $params
	 * @return \Phalcon\Mvc\Model\Criteria
	 */
	private static function buildCriteria($url, $params) {
		$model = self::parseModel($url);
		$actions = self::parseActions($url);
		$actions = self::prepareSplitActions($actions);

		/** @var \Phalcon\Mvc\Model\Criteria $criteria */
		$criteria = $model::query();
		$criteria = self::buildAnd($actions, $criteria);
		$criteria->bind(self::getParams($params, $actions));
		return $criteria;
	}

	/**
	 * Выбираем из url название искомой модели.
	 * @param $url
	 * @return string
	 */
	private static function parseModel($url) {
		$model = preg_replace('/^(.*)\/(ppa|s)\/([a-zA-Z]+).*$/', '$3', $url);
		return ucfirst($model);
	}

	/**
	 * Парсит url на наличие условия where.
	 * @param $url
	 * @return bool|mixed
	 */
	private static function parseActions($url) {
		$pattern = '/^.*\/findBy([a-zA-Z0-9]+)$/';
		if (!preg_match($pattern, $url)) {return false;}
		return preg_replace($pattern, '$1', $url);
	}

	/**
	 * @param $url
	 * @return bool
	 */
	private static function hasMany($url) {
		return (bool) preg_match('/\/s\//', $url);
	}

	/**
	 * @param $actions
	 * @return mixed
	 */
	private static function prepareSplitActions($actions) {
		$actions = preg_replace(self::AND_PATTERN, '$1|$2', $actions);
		$actions = preg_replace(self::OR_PATTERN, '$1-$2', $actions);
		return $actions;
	}

	/**
	 * @param string $actions
	 * @param \Phalcon\Mvc\Model\Criteria $criteria
	 * @return \Phalcon\Mvc\Model\Criteria
	 */
	private static function buildAND($actions, $criteria) {
		$actions = self::prepareSplitActions($actions);
		$andList = explode('|', $actions);
		$andList = array_filter($andList);
		foreach ($andList as $and) {
			$criteria->andWhere(self::buildOR($and));
		}
		return $criteria;
	}

	/**
	 * @param $actions
	 * @return string
	 */
	private static function buildOR($actions) {
		$conditions = array();
		$OrList = explode('-', $actions);
		foreach ($OrList as $field) {
			$conditions[] = Text::uncamelize($field) . ' = :' . lcfirst($field) . ':';
		}
		return implode(' OR ', $conditions);
	}

	/**
	 * @param $actions
	 * @return array
	 */
	private static function getColumns($actions) {
		$columns = preg_split('/-|\|/', $actions);
		return array_map('lcfirst', $columns);
	}

	/**
	 * @param $params
	 * @param $actions
	 * @return mixed
	 */
	private static function getParams($params, $actions) {
		$columns = self::getColumns($actions);
		foreach ($params as $key => $param) {
			if (!in_array($key, $columns)) {
				unset($params[$key]);
			}
		}
		return $params;
	}
}