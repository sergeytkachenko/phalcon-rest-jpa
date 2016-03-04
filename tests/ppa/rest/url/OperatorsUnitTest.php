<?php

namespace Test\Ppa\Rest\Url;

use PPA\Rest\Url\Operators;

class OperatorsUnitTest extends \UnitTestCase
{
	static $urls = array(
		'findByTitleAndPrimaryColumn' => 'Title|PrimaryColumn',
		'findByLastName' => 'LastName',
		'findByTitleAndPrimaryColumnOrName' => 'Title|PrimaryColumn-Name',
		'/ppa/s/model/findByTitleAndPrimaryColumnOrName' => 'Title|PrimaryColumn-Name',
		'/api/ppa/s/model/search' => 'search'
	);

	static $whereUrls = array(
		/**
		 * Default
		 */
		'/api/ppa/s/model/findByTitleAndPrimaryColumn' => '(title = :title:) AND (primaryColumn = :primaryColumn:)',
		'/api/ppa/model/findByLastName' => 'lastName = :lastName:',
		'/api/ppa/s/model/findByTitleAndPrimaryColumnOrName'
			=> '(title = :title:) AND (primaryColumn = :primaryColumn: OR name = :name:)',
		'/ppa/s/model/findByTitleAndPrimaryColumnOrName'
			=> '(title = :title:) AND (primaryColumn = :primaryColumn: OR name = :name:)',

		/**
		 * Like
		 */
		'/api/ppa/model/findByNameLike' => 'name LIKE :name:',
		'/api/ppa/model/findByNameLikeOrTitleLike' => 'name LIKE :name: OR title LIKE :title:',
		'/api/ppa/model/findByNameLikeAndTitleLike' => '(name LIKE :name:) AND (title LIKE :title:)',

		/**
		 * StartingWith
		 */
		'/api/ppa/model/findByNameStartingWithAndTitleStartingWith' => '(name LIKE :name:) AND (title LIKE :title:)',

		/**
		 * Search
		 */
		'/api/ppa/s/model/search' => 'MATCH(`a`,`b`) AGAINST (:search:)'
	);

	public function testGetPrepareUrlOperators() {
		foreach (self::$urls as $url => $expected) {
			$result = Operators::getPrepareUrlOperators($url);
			$this->assertEquals($result, $expected, 'getPrepareOperators returns expected value');
		}
	}

	public function testBuild() {
		foreach (self::$whereUrls as $url => $expected) {
			$builder = Operators::buildQuery($url, array('columns' => array('a', 'b')));
			$this->assertEquals($builder->getWhere(), $expected, '$builder->getWhere() returns expected value');
			$this->assertEquals($builder->getFrom(), 'Model', '$builder->getFrom() returns expected value');
		}
	}
}