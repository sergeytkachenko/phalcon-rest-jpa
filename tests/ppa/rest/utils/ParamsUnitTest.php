<?php
namespace Test\Ppa\Rest\Url;

use Phalcon\Http\Request;
use PPA\Rest\Utils\Params;

class ParamsUnitTest extends \UnitTestCase
{
	public $post = array(
		'model' => 'model',
		'relations' => array(
			'R1' => array(
				'model_column' => 1,
				'relation_column' => 2
			)
		),
		'all' => 'allValue'
	);

	public $put = array(
		'put' => array(),
		'all' => null
	);

	public $get = array(
		'id' => 1,
		'all' => 0
	);

	public $jsonRawBody = array(
		'entity' => array()
	);

	public function testMockRequest() {
		$request = $this
			->getMockBuilder(Request::class)
			->getMock();
		$request
			->method('getPost')
			->willReturn($this->post);
		$request
			->method('getPut')
			->willReturn($this->put);
		$request
			->method('get')
			->willReturn($this->get);
		$request
			->method('getJsonRawBody')
			->willReturn($this->jsonRawBody);
		return $request;
	}

	/**
	 * @depends testMockRequest
	 * @param $request
	 */
	public function testGetParams($request) {
		$params = Params::getParams($request);
		$this->assertNotEmpty($params, 'params not empty');
		$this->assertArrayHasKey('id', $params, 'params has id key');
		$this->assertArrayHasKey('model', $params, 'params has model key');
		$this->assertArrayHasKey('entity', $params, 'params has entity key');
		$this->assertArrayHasKey('put', $params, 'params has put key');
		$this->assertEquals($params['all'], null, 'param with key all equals getPut value');
	}

	/**
	 * @depends testMockRequest
	 * @param $request
	 */
	public function testGetRelations($request) {
		$relations = Params::getRelations($request);
		$this->assertEquals($relations, $this->post['relations'], 'relations is equals');

		$request = $this
			->getMockBuilder(Request::class)
			->getMock();
		$request
			->method('getPost')
			->willReturn(array(
				'relations' => 1
			));
		$relations = Params::getRelations($request);
		$this->assertEquals($relations, array(), 'relations is empty array');

		$request = $this
			->getMockBuilder(Request::class)
			->getMock();
		$request
			->method('getPost')
			->willReturn(array());
		$relations = Params::getRelations($request);
		$this->assertEquals($relations, array(), 'relations is empty array');
	}

	/**
	 * @depends testMockRequest
	 * @param $request
	 */
	public function testGetRelation($request) {
		$relation = Params::getRelation($request, 'R1');
		$this->assertEquals($relation, $this->post['relations']['R1'],
			'relation Ri expected value');
		$request = $this
			->getMockBuilder(Request::class)
			->getMock();
		$request
			->method('getPost')
			->willReturn(array());
		$relation = Params::getRelation($request, 'R1');
		$this->assertEquals($relation, null, 'relation is null');

		$request = $this
			->getMockBuilder(Request::class)
			->getMock();
		$request
			->method('getPost')
			->willReturn(array(
				'relations' => array(
					'R1' => array()
				)
			));
		$relation = Params::getRelation($request, 'R1');
		$this->assertEquals($relation, null, 'relation is null');
	}
}