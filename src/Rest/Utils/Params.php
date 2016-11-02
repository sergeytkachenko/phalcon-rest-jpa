<?php
namespace PPA\Rest\Utils;

use Moment\Moment;
use Moment\MomentException;
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
		return self::convertDate($params);
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
	 */
	public static function convertDate(array $params) {
		foreach ($params as $key => $value) {
			if (!is_string($value) or !strtotime($value)) {
				continue;
			}
			try {
				$moment = new Moment($value, 'CET');
				$params[$key] = $moment->format('Y-m-d H:i:s');
			} catch (MomentException $exception) {
				
			}
		}
	}
}