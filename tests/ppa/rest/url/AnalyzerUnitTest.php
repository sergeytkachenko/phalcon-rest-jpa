<?php

namespace Test\Ppa\Rest\Url;

use PPA\Rest\Url\Analyzer;

class AnalyzerUnitTest extends \UnitTestCase
{
	static $variation = array(
		'?id=1&some=1',
		'?',
		'/',
		'/?',
		'/?id=1&some=1',
		''
	);

	static $crud = array(
		'fetch' => array(
			'/ppa/s/brands/findById',
			'/api/ppa/brands',
			'/api/ppa/s/brands'
		),
		'save' => array(
			'/ppa/brands/save',
			'/api/ppa/brands/save',
		),
		'delete' => array(
			'/ppa/brands/delete',
			'/api/ppa/brands/delete'
		)
	);

	public function testIsSaving()
	{
		$operatorExpected = 'save';
		foreach (self::$variation as $suffix) {
			foreach (self::$crud as $key => $operation) {
				foreach ($operation as $url) {
					$this->assertEquals(
						Analyzer::isSaving($url . $suffix),
						$key === $operatorExpected ? true : false,
						'isSaving '. $key === $operatorExpected ? '' : 'not'  .' equal fetch url with '. $url . $suffix
					);
				}
			}
		}
	}

	public function testIsDelete()
	{
		$operatorExpected = 'delete';
		foreach (self::$variation as $suffix) {
			foreach (self::$crud as $key => $operation) {
				foreach ($operation as $url) {
					$this->assertEquals(
						Analyzer::isDeleting($url . $suffix),
						$key === $operatorExpected ? true : false,
						'isDeleting '. $key === $operatorExpected ? '' : 'not'  .' equal fetch url with '. $url . $suffix
					);
				}
			}
		}
	}

	public function testIsFetching()
	{
		$operatorExpected = 'fetch';
		foreach (self::$variation as $suffix) {
			foreach (self::$crud as $key => $operation) {
				foreach ($operation as $url) {
					$this->assertEquals(
						Analyzer::isFetching($url . $suffix),
						$key === $operatorExpected ? true : false,
						'isFetching '. $key === $operatorExpected ? '' : 'not'  .' equal fetch url with '. $url . $suffix
					);
				}
			}
		}
	}
	
	public function testGetModelName() {
		foreach (self::$variation as $suffix) {
			foreach (self::$crud as $key => $operation) {
				foreach ($operation as $url) {
					$this->assertEquals(
						Analyzer::getModelName($url . $suffix),
						'brands',
						'getModelName equal brands with '. $url . $suffix
					);
				}
			}
		}
	}
}