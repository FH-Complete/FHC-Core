<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

use phpseclib\Crypt\Rijndael;

/**
 * Collection of different encryption/hashing algorithms
 */
class CryptLib
{
	/**
	 * Encrypt using the Rijndael algorithm with a key length of 256 bits and the ECB mode
	 * It is possible to disable the padding, enabled by default
	 */
	public static function RIJNDAEL_256_ECB($value, $key, $paddingDisabled = false)
	{
		if (isEmptyString($key) || strlen($value) % 32 != 0) return null;

		$cipher = new Rijndael(Rijndael::MODE_ECB);
		$cipher->setBlockLength(256);
		$cipher->setKey($key);

		if ($paddingDisabled === true) $cipher->disablePadding();

		return $cipher->encrypt($value);
	}
}
