<?php

namespace PPA\Rest\Request;

use InvalidArgumentException;
use PPA\Rest\Column\Portion;
use PPA\Rest\Url\Operations;
use PPA\Rest\Url\Operators;
use PPA\Rest\Utils\Text;

class Params
{
	/**
	 * @var string $fullUrl
	 */
	private $fullUrl;

	/**
	 * @var array[string]mixed
	 */
	private $params;

	/**
	 * @var array[string]\PPA\Rest\Column\Portion $paramsPortion
	 */
	private $paramsPortion = array();


	/**
	 * Params constructor.
	 * @param string $fullUrl
	 * @param array $params
	 */
	public function __construct($fullUrl, array $params)
	{
		$this->fullUrl = $fullUrl;
		$this->params = $params;
		$this->initParamsPortion($params);
	}

	/**
	 * @param array $params
	 */
	private function initParamsPortion(array $params) {
		$preparedUrl = Operators::getPrepareUrlOperators($this->fullUrl);
		$andList = array_filter(explode('|', $preparedUrl));
		foreach ($andList as $and) {
			$orList  = array_filter(explode('-', $and));
			foreach ($orList as $or) {
				$paramKey = $this->findKeyParamByPortionString($params, $or);
				if ($paramKey === false) {
					continue;
					//throw new InvalidArgumentException('for portion ' . $or . ' is not set param');
				}
				$this->paramsPortion[$paramKey] = new Portion($or);
			}
		}
	}

	/**
	 * @param array $params
	 * @param $portionString
	 * @return bool|int|string
	 */
	private function findKeyParamByPortionString(array $params, $portionString) {
		foreach ($params as $key => $param) {
			$key = Text::getColumnLowerCase($key);
			$portionString = Text::getColumnLowerCase($portionString);
			if (preg_match('/^' . $key . '/', $portionString)) {
				return $key;
			}
		}
		return false;
	}

	public function getPrepareParams() {
		foreach ($this->paramsPortion as $key => $portion) {
			$value = Operations::getPrepareParam($portion, $this->params[$key]);
			$this->params[$key] = $value;
		}
		return $this->params;
	}
}