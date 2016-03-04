<?php

namespace Test\Ppa\Rest\Url;

use PPA\Rest\Column\Portion;
use PPA\Rest\Url\Operations;

class OperationsUnitTest extends \UnitTestCase
{
	static $crud = array(
		'like' => array(
			'title' => 'TitleLike',
			'lastName' => 'LastNameLike',
			'longColumnTest' => 'LongColumnTestLike'
		),
		'containing' => array(
			'title' => 'TitleContaining',
			'lastName' => 'LastNameContaining',
			'longColumnTest' => 'LongColumnTestContaining'
		)
	);

	public function testGetTextSqlOperationLike() {
		$operatorExpected = 'like';
		foreach (self::$crud as $key => $operation) {
			if ($operatorExpected !== $key) {continue;}
			foreach ($operation as $param => $conditionPortion) {
				$sql = Operations::getTextSqlOperation(new Portion($conditionPortion));
				$this->assertEquals(
					$sql,
					$param . ' LIKE :'. $param . ':',
					'getTextSqlOperation create expected sql text'
				);
			}
		}
	}

	public function testGetTextSqlOperationContaining() {
		$operatorExpected = 'containing';
		foreach (self::$crud as $key => $operation) {
			if ($operatorExpected !== $key) {continue;}
			foreach ($operation as $param => $conditionPortion) {
				$sql = Operations::getTextSqlOperation(new Portion($conditionPortion));
				$this->assertEquals(
					$sql,
					$param . ' LIKE :'. $param . ':',
					'getTextSqlOperation create expected sql text'
				);
			}
		}
	}

}