<?php

namespace PPA\Rest\Url;

use Phalcon\Di;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Mvc\Model\Query\Builder;
use PPA\Rest\Column\Portion;
use PPA\Rest\Request\Params;
use PPA\Rest\Utils\Macros;
use PPA\Rest\Utils\Text;

abstract class Operators
{
	const AND_PATTERN = '/([a-z0-9])And([A-Z0-9])/';
	const OR_PATTERN = '/([a-z0-9])Or([A-Z0-9])/';

	/**
	 * @param string $url Full url.
	 * @return string
	 */
	public static function getPrepareUrlOperators($url) {
		$url = self::deleteExcessPrefix($url);
		$url = preg_replace(self::AND_PATTERN, '$1|$2', $url);
		$url = preg_replace(self::OR_PATTERN, '$1-$2', $url);
		return $url;
	}

	/**
	 * Delete excess symbols.
	 * @param $url Full url.
	 * @return string Truncated url, whithout 'findBy' prefix.
	 */
	private static function deleteExcessPrefix($url) {
		$pattern = '/^.*ppa(\/s)?\/[a-zA-Z]+\/?/';
		$url = preg_replace($pattern, '', $url);
		if (preg_match('/^(save|delete)/', $url)) {
			return "";
		}
		$url = preg_replace('/^findBy/', '', $url);
		$pattern = '/^([a-zA-Z0-9]+)$/';
		if (!preg_match($pattern, $url)) {return "";}
		return $url;
	}

	/**
	 * @param string $preparedUrl
	 * @return null|string
	 */
	private static function buildWhere($preparedUrl) {
		$criteria = new Criteria();
		$andList = explode('|', $preparedUrl);
		$andList = array_filter($andList);
		foreach ($andList as $condition) {
			$criteria->andWhere(self::buildOR($condition));
		}
		return $criteria->getWhere();
	}

	/**
	 * @param string $fullUrl Full url.
	 * @param array $params
	 * @return \Phalcon\Mvc\Model\Query
	 */
	public static function buildQuery($fullUrl, array $params = array()) {
		/**
		 * @var \Phalcon\Di $di
		 */
		global $di;
		/**
		 * @var \Phalcon\Mvc\Model $modelName
		 */
		$modelName = Text::camelize(Text::uncamelize(Analyzer::getModelName($fullUrl)));
		$builder = new Builder();
		$builder->setDI($di);
		$builder->from($modelName);
		self::setColumns($builder, $params);
		$prepareUrl = self::getPrepareUrlOperators($fullUrl);
		$whereSql = self::buildWhere($prepareUrl);
		$whereSqlReplacement = Macros::replace($whereSql, $params, $modelName);
		$builder->where($whereSqlReplacement);
		$builder = self::limit($builder, $params);
		$builder = self::orderBy($builder, $params);
		$params = new Params($fullUrl, $params);
		$params = $params->getPrepareParams();
		$query = $builder->getQuery();
		$query->setBindParams($params, true);

		return $query;
	}

	/**
	 * @param \Phalcon\Mvc\Model\Query\Builder $builder
	 * @param $params
	 */
	private static function setColumns($builder, $params) {
		$columns = \PPA\Rest\Utils\Params::getColumns($params);
		$excludeColumns = \PPA\Rest\Utils\Params::getExcludeColumns($params);
		if ($columns) {
			$builder->columns($columns);
		}
		if ($excludeColumns) {
			self::setExcludeColumns($builder, $excludeColumns);
		}
	}

	/**
	 * @param \Phalcon\Mvc\Model\Query\Builder $builder
	 * @param array $excludeColumns
	 */
	private static function setExcludeColumns($builder, $excludeColumns) {
		$modelName = $builder->getFrom();
		/**
		 * @var \Phalcon\Mvc\Model $model
		 */
		$model = new $modelName();
		$columns = $model->getModelsMetaData()->getAttributes($model);
		$columns = array_diff($columns, $excludeColumns);
		foreach ($columns as $key => $column) {
			foreach ($excludeColumns as $excludeColumn) {
				$excludeColumn = Text::lower($excludeColumn);
				if (!Text::endsWith($excludeColumn, '*')) {
					break;
				}
				$excludeColumn = str_replace('*', '', $excludeColumn);
				$column = Text::lower($column);
				$isStartWith = Text::startsWith($column, $excludeColumn);
				if ($isStartWith) {
					unset($columns[$key]);
					break;
				}
			}
		}
		$builder->columns($columns);
	}

	/**
	 * Добавляет ->orderBy в $builder.
	 * @param \Phalcon\Mvc\Model\Query\Builder $builder
	 * @param array $params
	 * @return \Phalcon\Mvc\Model\Query\Builder
	 */
	private static function orderBy(Builder $builder, array $params) {
		$model = $builder->getFrom();
		if (empty($params['orderBy'])) {return $builder;}
		$orderBy = (array)$params['orderBy'];
		foreach ($orderBy as $key => $item) {
			$order =  explode('|', $item);
			$order =  implode(' ', $order);
			if (!preg_match('/\./', $item)) {
				$order = $model . '.' . $order;
			}
			$orderBy[$key] = $order;
		}
		return $builder->orderBy($orderBy);
	}

	/**
	 * Добавляет ->limit в $builder.
	 * @param \Phalcon\Mvc\Model\Query\Builder $builder
	 * @param array $params
	 * @return \Phalcon\Mvc\Model\Query\Builder
	 */
	private static function limit(Builder $builder, array $params) {
		if (empty($params['limit'])) {return $builder;}
		$limit = (int)$params['limit'];
		$builder->limit($limit);
		if (empty($params['offset'])) {return $builder;}
		$offset = (int)$params['offset'];
		return $builder->offset($offset);
	}

	/**
	 * @param string $preparedUrlWithoutAnd
	 * @return string
	 *
	 */
	private static function buildOR($preparedUrlWithoutAnd) {
		$conditions = array();
		$orList = explode('-', $preparedUrlWithoutAnd);
		foreach ($orList as $condition) {
			$sql = Operations::getTextSqlOperation(new Portion($condition));
			if ($sql === false) {continue;}
			$conditions[] = $sql;
		}
		return implode(' OR ', $conditions);
	}

}