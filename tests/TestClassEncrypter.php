<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 3/31/2021
 * Time: 11:10 AM
 */

namespace onOffice\tests;


use onOffice\WPlugin\Utility\SymmetricEncryption;
use WP_UnitTestCase;


class TestClassEncrypter extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testEncrypt()
	{
		$plainText = '12345';
		$cipherText = SymmetricEncryption::encrypt($plainText);
		$this->assertSame($plainText, SymmetricEncryption::decrypt($cipherText));
	}
}