<?php

namespace onOffice\tests;

use onOffice\WPlugin\Utility\HTTPHeadersGeneric;

class TestClassHTTPHeadersGeneric extends \WP_UnitTestCase
{
	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testAddHeader()
	{
		$pInstance = new HTTPHeadersGeneric;
		$this->assertEmpty(xdebug_get_headers());
		$pInstance->addHeader('Content-Type: text/plain');
		$this->assertEquals(['Content-type: text/plain;charset=UTF-8'], xdebug_get_headers());
		$pInstance->addHeader('Content-Type: text/html');
		$this->assertEquals(['Content-type: text/html;charset=UTF-8'], xdebug_get_headers());
		$pInstance->addHeader('Content-Type: text/plain', false);
		$this->assertEquals([
			'Content-type: text/html;charset=UTF-8',
			'Content-type: text/plain;charset=UTF-8',
		], xdebug_get_headers());
		$pInstance->addHeader('Content-Type: text/html', true, 404);
		$this->assertEquals(['Content-type: text/html;charset=UTF-8'], xdebug_get_headers());
	}
}