<?php
namespace Test\Ppa\Rest\Url;

use PPA\Rest\Utils\Text;

class TextUnitTest extends \UnitTestCase
{
	static $strings = array(
		'lowerCase' => array(
			'aaaBbb' => 'aaa_bbb',
			'aaaBbbCcc' => 'Aaa_bbb_ccc',
			'aaaBbbCcc' => 'AaaBbbCcc',
		)
	);

	public function testGetColumnLowerCase() {
		$expectedKey = 'lowerCase';
		foreach (self::$strings as $key => $expectedArray) {
			if ($key !== $expectedKey) {continue;}
			foreach ($expectedArray as $expectedValue => $string) {
				$result = Text::getColumnLowerCase($string);
				$this->assertEquals($result, $expectedValue, 'getColumnLowerCase returns expected value');
			}
		}
	}
}