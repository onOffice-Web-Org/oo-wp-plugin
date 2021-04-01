<?php


namespace onOffice\WPlugin\Utility;


interface SymmetricEncryption
{
	public function encrypt(string $plainText, string $key, string $cipher): string;
	public function decrypt(string $plainText, string $key, string $cipher): string;
}