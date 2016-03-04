<?php

namespace Test\Ppa\Rest\Request;

use PPA\Rest\Request\Params;

class ParamsUnitTest extends \UnitTestCase
{
	static $crud = array(
		'/ppa/s/targetGroup/findById' => array('id' => 1),
		'/ppa/s/targetGroup/search' => array('search' => 'searchValue'),
		'/ppa/s/targetGroup/findByTitleContaining' => array('title' => 'titleValue'),
		'/ppa/s/targetGroup/findByTitleLike' => array('title' => 'titleValue'),
		'/ppa/s/targetGroup/findByTitleStartingWith' => array('title' => 'titleValue'),
	);

	static $expected = array(
		'/ppa/s/targetGroup/findById' => array('id' => 1),
		'/ppa/s/targetGroup/search' => array('search' => '%searchValue%'),
		'/ppa/s/targetGroup/findByTitleContaining' => array('title' => '%titleValue%'),
		'/ppa/s/targetGroup/findByTitleLike' => array('title' => 'titleValue'),
		'/ppa/s/targetGroup/findByTitleStartingWith' => array('title' => 'titleValue%')
	);

	public function testConstructor() {
		foreach (self::$crud as $url => $params) {
			$paramsObj = new Params($url, $params);
			$prepareParams = $paramsObj->getPrepareParams();
			$this->assertEquals(self::$expected[$url], $prepareParams, 'getPrepareParams return expected value');
		}
	}

}