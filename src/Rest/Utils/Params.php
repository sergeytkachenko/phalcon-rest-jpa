<?php
namespace PPA\Rest\Utils;

use Moment\Moment;
use Moment\MomentException;
use Phalcon\Di;
use Phalcon\Http\Request;

abstract class Params
{
	/**
	 * @example Params::getRelations($request, 'entityName') => array(array('entity' => 1, 'relation' => 1))
	 * @param \Phalcon\Http\Request $request
	 * @return array All relations, that send to request.
	 */
	public static function getRelations(Request $request) {
		$params = self::getMergeParams($request);
		if (isset($params['relations']) and is_array($params['relations'])) {
			return $params['relations'];
		}
		return array();
	}

	/**
	 * Returns relation array by model from request.
	 * @param Request $request
	 * @param $relationName Relation name from 'relations' section.
	 * @return array Relation array for model.
	 */
	public static function getRelation(Request $request, $relationName) {
		$relations = self::getRelations($request);
		if (array_key_exists($relationName, $relations)) {
			$relationValue = $relations[$relationName];
			return !empty($relationValue) ? $relationValue : null;
		}
		return null;
	}

	/**
	 * @param \Phalcon\Http\Request $request
	 * @return array All request params (GET, POST, PUT, RawJsonBody)
	 */
	public static function getMergeParams(Request $request) {
		$jsonRawBody = (array)$request->getJsonRawBody(true);
		$params = array_merge((array)$request->get(), (array)$request->getPost(), (array)$request->getPut(), $jsonRawBody);;
		return self::convertDates($params, $request->getDI());
	}
	
	/**
	 * @param array $params
	 * @return mixed|null
	 */
	public static function getColumns(array $params) {
		return key_exists('columns', $params) ? $params['columns'] : null;
	}

	/**
	 * @param array $params
	 * @return mixed|null
	 */
	public static function getExcludeColumns(array $params) {
		return key_exists('excluded', $params) ? $params['excluded'] : null;
	}
	
	/**
	 * @param array $params
	 * @param Di $di
	 * @return array
	 */
	public static function convertDates(array $params, Di $di) {
		foreach ($params as $key => $value) {
			$params[$key] = self::convertDate($value, $di);
		}
		return $params;
	}

	/**
	 * @param $date
	 * @param Di $di
	 * @return string
	 */
	public static function convertDate($date, Di $di) {
		if (!is_string($date) or !self::isDate($date) or !strtotime($date)) {
			return $date;
		}
		try {
			$timezoneOffset = self::getTimezoneOffset($di);
			$moment = new Moment($date, 'CET');
			if ($timezoneOffset > 0) {
				$moment->subtractMinutes(abs($timezoneOffset));
			}
			if ($timezoneOffset < 0) {
				$moment->addMinutes(abs($timezoneOffset));
			}
			return $moment->format('Y-m-d H:i:s');
		} catch (MomentException $exception) {
			return $date;
		}
	}
	
	/**
	 * @param $dateString
	 * @return bool
	 */
	public static function isDate($dateString) {
		$pattern = '/^2[0-9]{3}-[0-9]{2}-[0-9]{2}/';
		if (preg_match($pattern, $dateString, $matches)) {
			return true;
		};
		return false;
	}
	
	/**
	 * @param Di $di
	 * @return int
	 */
	public static function getTimezoneOffset(Di $di) {
		/**
		 * @var \Phalcon\Http\Request $request
		 */
		$request = $di->get('request');
		$timezoneOffset = $request->getHeader('TimezoneOffset');
		return (int) $timezoneOffset;
	}
}