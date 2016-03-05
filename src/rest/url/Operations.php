<?php

namespace PPA\Rest\Url;

use PPA\Rest\Column\Portion;
use PPA\Rest\Utils\Text;

abstract class Operations
{
	/**
	 * … where x.firstname like ?1
	 */
	const Like = '/^(.*[a-z0-9])Like$/';

	/**
	 * … where x.firstname not like ?1
	 */
	const NotLike = '/^(.*[a-z0-9])NotLike/';

	/**
	 * where x.firstname like ?1 (parameter bound wrapped in %)
	 */
	const Containing = '/^(.*[a-z0-9])Containing/';

	/**
	 * … where x.firstname like ?1 (parameter bound with appended %)
	 */
	const StartingWith = '/^(.*[a-z0-9])StartingWith/';

	/**
	 * … where x.firstname like ?1 (parameter bound with prepended %)
	 */
	const EndingWith = '/^(.*[a-z0-9])EndingWith/';

	/**
	 * … where x.startDate between 1? and ?2
	 */
	const Between = '/^(.*[a-z0-9])Between/';

	/**
	 * … where x.age < ?1
	 */
	const LessThan = '/^(.*[a-z0-9])LessThan/';

	/**
	 * … where x.age ⇐ ?1
	 */
	const LessThanEqual = '/^(.*[a-z0-9])LessThanEqual/';

	/**
	 * … where x.age > ?1
	 */
	const GreaterThan = '/^(.*[a-z0-9])GreaterThan/';

	/**
	 * … where x.age >= ?1
	 */
	const GreaterThanEqual = '/^(.*[a-z0-9])GreaterThanEqual/';

	/**
	 * … where x.startDate > ?1
	 */
	const After = '/^(.*[a-z0-9])After/';

	/**
	 * … where x.startDate < ?1
	 */
	const Before = '/^(.*[a-z0-9])Before/';

	/**
	 * … where x.age is null
	 */
	const IsNull = '/^(.*[a-z0-9])IsNull/';

	/**
	 * … where x.age not null
	 */
	const IsNotNull = '/^(.*[a-z0-9])IsNotNull/';

	/**
	 * findByAgeOrderByLastnameDesc
	 * … where x.age = ?1 order by x.lastname desc
	 */
	const OrderBy = '/^(.*[a-z0-9])OrderBy([a-z0-9]+)/';

	/**
	 * … where x.age in ?1
	 */
	const In = '/^(.*[a-z0-9])In/';

	/**
	 * … where x.lastname <> ?1
	 */
	const Not = '/^(.*[a-z0-9])Not/';

	/**
	 * … where x.active = true
	 */
	const True = '/^(.*[a-z0-9])True/';

	/**
	 *
	 * … where x.active = false
	 */
	const False = '/^(.*[a-z0-9])False/';

	/**
	 *
	 * … where x.active = false
	 */
	const Search = '/^search$/';

	const _Default = '/^([a-zA-Z0-9]+)/';

	public static function getOperations() {
		return array(
			'Like' => Operations::Like,
			'NotLike' => Operations::NotLike,
			'Containing' => Operations::Containing,
			'StartingWith' => Operations::StartingWith,
			'EndingWith' => Operations::EndingWith,
			'Between' => Operations::Between,
			'LessThan' => Operations::LessThan,
			'LessThanEqual' => Operations::LessThanEqual,
			'GreaterThan' => Operations::GreaterThan,
			'GreaterThanEqual' => Operations::GreaterThanEqual,
			'After' => Operations::After,
			'Before' => Operations::Before,
			'IsNull' => Operations::IsNull,
			'IsNotNull' => Operations::IsNotNull,
			'OrderBy' => Operations::OrderBy,
			'In' => Operations::In,
			'Not' => Operations::Not,
			'True' => Operations::True,
			'False' => Operations::False,
			'Search' => Operations::Search,
			'Default' => Operations::_Default
		);
	}

	/**
	 * @param \PPA\Rest\Column\Portion $portion Portion of condition column.
	 * @return string|bool
	 */
	public static function getTextSqlOperation(Portion $portion) {
		$operationKey = self::getSearchOperationKey($portion);
		if (!$operationKey) {return false;}
		$methodName = 'get' . $operationKey;
		return self::$methodName($portion);
	}

	/**
	 * @param Portion $portion
	 * @param $value
	 * @return mixed
	 */
	public static function getPrepareParam(Portion $portion, $value) {
		$operationKey = self::getSearchOperationKey($portion);
		if (!$operationKey) {return $value;}
		$methodName = 'get' . $operationKey . 'Param';
		return self::$methodName($value);
	}

	/**
	 * @param Portion $portion
	 * @return mixed
	 */
	public static function truncateOperation(Portion $portion) {
		$operationKey = self::getSearchOperationKey($portion);
		if (!$operationKey) {
			return $portion->getPortion();
		}
		$operations = self::getOperations();
		$columnNameWithoutOperation = preg_replace($operations[$operationKey], "$1", $portion->getPortion());
		return Text::getColumnLowerCase($columnNameWithoutOperation);
	}

	/**
	 * @param Portion $portion
	 * @return bool|string
	 */
	public static function getSearchOperationKey(Portion $portion) {
		$operations = self::getOperations();
		foreach($operations as $key => $pattern) {
			if (preg_match($pattern, $portion->getPortion())) {
				return $key;
			}
		}
		return false;
	}

	/**
	 * @param Portion $portion
	 * @return string
	 */
	public static function getLike(Portion $portion) {
		$columnName = self::getColumnName($portion);
		$columnValue = self::getColumnValue($portion);
		return $columnName . ' LIKE :' . $columnValue . ':';
	}

	/**
	 * @param string $value
	 * @return string
	 */
	public static function getLikeParam($value) {
		return self::getDefaultParam($value);
	}

	/**
	 * @param Portion $portion
	 * @return string
	 */
	public static function getContaining(Portion $portion) {
		return self::getLike($portion);
	}

	/**
	 * @param string $value
	 * @return string
	 */
	public static function getContainingParam($value) {
		return '%' . $value . '%';
	}

	/**
	 * @param Portion $portion
	 * @param string $defaultColumnSearch
	 * @return string
	 */
	public static function getSearch(Portion $portion) {
		return '{columns likes}';
	}

	/**
	 * @param string $value
	 * @return string
	 */
	public static function getSearchParam($value) {
		return '%' . $value . '%';
	}

	/**
	 * @param Portion $portion
	 * @return string
	 */
	public static function getStartingWith(Portion $portion) {
		return self::getLike($portion);
	}

	/**
	 * @param string $value
	 * @return string
	 */
	public static function getStartingWithParam($value) {
		return $value . '%';
	}

	/**
	 * @param Portion $portion
	 * @return string
	 */
	public static function getDefault(Portion $portion) {
		$columnName = self::getColumnName($portion);
		$columnValue = self::getColumnValue($portion);
		return $columnName . ' = :' . $columnValue . ':';
	}

	/**
	 * @param $value
	 * @return string
	 */
	public static function getDefaultParam($value) {
		return $value;
	}

	private static function getColumnName(Portion $portion) {
		$columnName = $portion->getColumnName();
		return Text::uncamelize($columnName);
	}

	private static function getColumnValue(Portion $portion) {
		return $portion->getColumnName();
	}
}