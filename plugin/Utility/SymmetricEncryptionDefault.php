<?php


namespace onOffice\WPlugin\Utility;


use http\Exception\RuntimeException;

class SymmetricEncryptionDefault implements SymmetricEncryption
{
	const OPEN_SSL_ERROR_MESSAGE = 'OpenSSL is not installed';
	const IV_ERROR_MESSAGE = 'iv is false';
	const DECRYPTION_ERROR_MESSAGE = 'Decryption error';

	/**
	 * @param string $plainText
	 * @param string $key
	 * @param string $cipher
	 * @return string
	 */
	public function encrypt(string $plainText, string $key, string $cipher = 'AES-128-CBC'): string
	{
		if (!extension_loaded('openssl')) {
			throw new \RuntimeException(self::OPEN_SSL_ERROR_MESSAGE);
		}

		$ivlen = openssl_cipher_iv_length($cipher);
		$iv = openssl_random_pseudo_bytes($ivlen);
		if ($iv === false) {
			throw new \RuntimeException(self::IV_ERROR_MESSAGE);
		}
		$cipherText = openssl_encrypt($plainText, $cipher, $key, OPENSSL_RAW_DATA, $iv);
		$hmac = hash_hmac('sha256', $iv.$cipherText, $key, true);
		return base64_encode($iv . $hmac . $cipherText);
	}

	/**
	 * @param string $cipherText
	 * @param string $key
	 * @param string $cipher
	 * @return string
	 */
	public function decrypt(string $cipherText, string $key, string $cipher = 'AES-128-CBC'): string
	{
		$hmacSha256Len = 32;
		if (!extension_loaded('openssl')) {
			throw new \RuntimeException(self::OPEN_SSL_ERROR_MESSAGE);
		}
		$decodeText = base64_decode($cipherText);
		$ivlen = openssl_cipher_iv_length($cipher);
		$iv = substr($decodeText, 0, $ivlen);
		$hmac = substr($decodeText, $ivlen, $hmacSha256Len);
		$cipherTextRaw = substr($decodeText, $ivlen + $hmacSha256Len);
		$plainText = openssl_decrypt($cipherTextRaw, $cipher, $key, OPENSSL_RAW_DATA, $iv);
		if ($plainText === false)
		{
			throw new \RuntimeException(self::DECRYPTION_ERROR_MESSAGE);
		}

		$calcmac = hash_hmac('sha256',$iv. $cipherTextRaw, $key,  true);
		if (!hash_equals($hmac, $calcmac)) {
			throw new \RuntimeException(self::DECRYPTION_ERROR_MESSAGE);
		}
		return $plainText;
	}
}