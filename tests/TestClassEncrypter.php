<?php

namespace onOffice\tests;


use onOffice\WPlugin\Utility\SymmetricEncryptionDefault;
use WP_UnitTestCase;


class TestClassEncrypter extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testEncrypt()
	{
		$plainText = '12345';
		$key = 'test-key';
		$encrypter = new SymmetricEncryptionDefault();
		$cipherText = $encrypter->encrypt($plainText, $key);
		$this->assertSame($plainText, $encrypter->decrypt($cipherText, $key));
	}
}