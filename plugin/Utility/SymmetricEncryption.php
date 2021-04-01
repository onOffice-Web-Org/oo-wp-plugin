<?php


namespace onOffice\WPlugin\Utility;


class SymmetricEncryption
{
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
			return $plainText;
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
		if (!extension_loaded('openssl')) {
			return $cipherText;
		}
		$decodeText = base64_decode($cipherText);
		$ivlen = openssl_cipher_iv_length($cipher);
		$iv = substr($decodeText, 0, $ivlen);
		$hmac = substr($decodeText, $ivlen, $sha2len = 32);
		$cipherTextRaw = substr($decodeText, $ivlen + $sha2len);
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