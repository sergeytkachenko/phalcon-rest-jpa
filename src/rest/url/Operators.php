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
		$prepareUrl = self::getPrepareUrlOperators($fullUrl);
		$whereSql = self::buildWhere($prepareUrl);
		$whereSqlReplacement = Macros::replace($whereSql, $params, $modelName);
		$builder->where($whereSqlReplacement);
		$builder = self::setLimit($builder, $params);
		$params = new Params($fullUrl, $params);
		$params = $params->getPrepareParams();
		$query = $builder->getQuery();
		$query->setBindParams($params, true);

		return $query;
	}

	/**
	 * Добавляет ->limit в $builder.
	 * @param \Phalcon\Mvc\Model\Query\Builder $builder
	 * @param array $params
	 * @return \Phalcon\Mvc\Model\Query\Builder
	 */
	private static function setLimit(Builder $builder, array $params) {
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