<?php

namespace Test\Ppa\Rest\Url;

use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Di;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Model\Manager as ModelsManager;
use PPA\Rest\Url\Operators;

class OperatorsUnitTest extends \UnitTestCase
{
	static $urls = array(
		'findByTitleAndPrimaryColumn' => 'Title|PrimaryColumn',
		'findByLastName' => 'LastName',
		'findByTitleAndPrimaryColumnOrName' => 'Title|PrimaryColumn-Name',
		'/ppa/s/testModel/findByTitleAndPrimaryColumnOrName' => 'Title|PrimaryColumn-Name',
		'/api/ppa/s/testModel/search' => 'search'
	);

	static $whereUrls = array(
		/**
		 * Default
		 */
		'/api/ppa/s/testModel/findByTitleAndLastName' => '(title = :title:) AND (last_name = :lastName:)',
		'/api/ppa/testModel/findByLastName' => 'last_name = :lastName:',
		'/api/ppa/s/testModel/findByTitleAndPrimaryColumnOrName'
			=> '(title = :title:) AND (primary_column = :primaryColumn: OR name = :name:)',
		'/ppa/s/testModel/findByTitleAndPrimaryColumnOrName'
			=> '(title = :title:) AND (primary_column = :primaryColumn: OR name = :name:)',

		/**
		 * Like
		 */
		'/api/ppa/testModel/findByNameLike' => 'name LIKE :name:',
		'/api/ppa/testModel/findByNameLikeOrTitleLike' => 'name LIKE :name: OR title LIKE :title:',
		'/api/ppa/testModel/findByNameLikeAndTitleLike' => '(name LIKE :name:) AND (title LIKE :title:)',

		/**
		 * StartingWith
		 */
		'/api/ppa/testModel/findByTitleStartingWithAndTitleStartingWith' => '(title LIKE :title:) AND (title LIKE :title:)',

		/**
		 * Search
		 */
		'/api/ppa/s/testModel/search' => 'MATCH(`title`,`last_name`) AGAINST (:search:)'
	);

	static $expectedUrls = array(
		'/api/ppa/s/testModel/findByTitleAndLastName' => array(
			'params' => array(
				'title' => 'title',
				'lastName' => 'lastName'
			),
			'prepareParams' => array(
				'title' => 'title',
				'lastName' => 'lastName'
			),
			'sql' => "SELECT `test_model`.`id`, `test_model`.`title`, `test_model`.`last_name` FROM `test_model` WHERE (`test_model`.`title` = :title) AND (`test_model`.`last_name` = :lastName)"
		),
		'/api/ppa/s/testModel/findByTitleLike' => array(
			'params' => array(
				'title' => 'title'
			),
			'prepareParams' => array(
				'title' => 'title'
			),
			'sql' => "SELECT `test_model`.`id`, `test_model`.`title`, `test_model`.`last_name` FROM `test_model` WHERE `test_model`.`title` LIKE :title"
		),
		'/api/ppa/s/testModel/findByTitleStartingWith' => array(
			'params' => array(
				'title' => 'title',
				'extraParam' => 'value'
			),
			'prepareParams' => array(
				'title' => 'title%'
			),
			'sql' => "SELECT `test_model`.`id`, `test_model`.`title`, `test_model`.`last_name` FROM `test_model` WHERE `test_model`.`title` LIKE :title"
		),
		'/api/ppa/s/testModel/search' => array(
			'params' => array(
				'search' => 'searchValue',
				'columns' => array('title', 'lastName')
			),
			'prepareParams' => array(
				'search' => '%searchValue%'
			),
			'sql' => "SELECT `test_model`.`id`, `test_model`.`title`, `test_model`.`last_name` FROM `test_model` WHERE `test_model`.`title` LIKE :search OR `test_model`.`last_name` LIKE :search"
		),
		'/api/ppa/s/testModel/findByIdIsNull' => array(
			'params' => array(),
			'prepareParams' => array(),
			'sql' => "SELECT `test_model`.`id`, `test_model`.`title`, `test_model`.`last_name` FROM `test_model` WHERE `test_model`.`id`  IS NULL"
		)
	);

	public function testGetPrepareUrlOperators() {
		foreach (self::$urls as $url => $expected) {
			$result = Operators::getPrepareUrlOperators($url);
			$this->assertEquals($result, $expected, 'getPrepareOperators returns expected value');
		}
	}

	public function testBuild() {
		foreach (self::$expectedUrls as $url => $expected) {
			$query = Operators::buildQuery($url, $expected['params']);
			$query->execute();
			$this->assertEquals($query->getSql()['sql'], $expected['sql'], '$query->getSql() returns expected value');
			$this->assertEquals($query->getBindParams(), $expected['prepareParams'], '$query->getBindParams() returns expected value');
		}
	}
}