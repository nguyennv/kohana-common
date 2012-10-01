<?php defined('SYSPATH') or die('No direct script access.');

class Crypto_Hash_Password{
	private static $_itoa_64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
	private static $_atoi_64 = './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
	private static $_iteration_count_log = 8;
	private static $_portable_hashes = true;
	private static $_random_state = '';

	private static function _get_bandom_bytes($count)
	{
		$output = '';
		if (($fh = @fopen('/dev/urandom', 'rb')))
		{
			$output = fread($fh, $count);
			fclose($fh);
		}

		if(empty(self::$_random_state))
		{
			self::$_random_state = microtime().(function_exists('getmypid') ? getmypid() : '').uniqid(rand(), TRUE);
		}

		if (strlen($output) < $count)
		{
			$output = '';
			for ($i = 0; $i < $count; $i += 16)
			{
				self::$_random_state = md5(microtime().self::$_random_state);
				$output .= pack('H*', md5(self::$_random_state));
			}
			$output = substr($output, 0, $count);
		}

		return $output;
	}

	private static function _encode64($input, $count)
	{
		$output = '';
		$i = 0;
		do
		{
			$value = ord($input[$i++]);
			$output .= self::$_itoa_64[$value & 0x3f];
			if ($i < $count)
				$value |= ord($input[$i]) << 8;
			$output .= self::$_itoa_64[($value >> 6) & 0x3f];
			if ($i++ >= $count)
				break;
			if ($i < $count)
				$value |= ord($input[$i]) << 16;
			$output .= self::$_itoa_64[($value >> 12) & 0x3f];
			if ($i++ >= $count)
				break;
			$output .= self::$_itoa_64[($value >> 18) & 0x3f];
		}
		while ($i < $count);

		return $output;
	}

	private static function _gensalt($input)
	{
		$output = '$P$';
		$output .= self::$_itoa_64[min(self::$_iteration_count_log + ((PHP_VERSION >= '5') ? 5 : 3), 30)];
		$output .= self::_encode64($input, 6);

		return $output;
	}

	private static function _crypt($password, $setting)
	{
		$output = '*0';
		if (substr($setting, 0, 2) == $output)
			$output = '*1';

		if (substr($setting, 0, 3) != '$P$')
			return $output;

		$countLog = strpos(self::$_itoa_64, $setting[3]);
		if ($countLog < 7 OR $countLog > 30)
			return $output;

		$count = 1 << $countLog;

		$salt = substr($setting, 4, 8);
		if (strlen($salt) != 8)
			return $output;

		if (PHP_VERSION >= '5')
		{
			$hash = md5($salt.$password, TRUE);
			do
			{
				$hash = md5($hash.$password, TRUE);
			}
			while (--$count);
		}
		else
		{
			$hash = pack('H*', md5($salt.$password));
			do
			{
				$hash = pack('H*', md5($hash.$password));
			}
			while (--$count);
		}

		$output = substr($setting, 0, 12);
		$output .= self::_encode64($hash, 16);

		return $output;
	}

	private static function _gensalt_extended($input)
	{
		$countLog = min(self::$_iteration_count_log + 8, 24);
		$count = (1 << $countLog) - 1;

		$output = '_';
		$output .= self::$_itoa_64[$count & 0x3f];
		$output .= self::$_itoa_64[($count >> 6) & 0x3f];
		$output .= self::$_itoa_64[($count >> 12) & 0x3f];
		$output .= self::$_itoa_64[($count >> 18) & 0x3f];

		$output .= self::_encode64($input, 3);

		return $output;
	}

	private static function _gensalt_blowfish($input)
	{
		$output = '$2a$';
		$output .= chr(ord('0') + self::$_iteration_count_log / 10);
		$output .= chr(ord('0') + self::$_iteration_count_log % 10);
		$output .= '$';

		$i = 0;
		do
		{
			$c1 = ord($input[$i++]);
			$output .= self::$__atoi64[$c1 >> 2];
			$c1 = ($c1 & 0x03) << 4;
			if ($i >= 16)
			{
				$output .= self::$__atoi64[$c1];
				break;
			}

			$c2 = ord($input[$i++]);
			$c1 |= $c2 >> 4;
			$output .= self::$__atoi64[$c1];
			$c1 = ($c2 & 0x0f) << 2;

			$c2 = ord($input[$i++]);
			$c1 |= $c2 >> 6;
			$output .= self::$__atoi64[$c1];
			$output .= self::$__atoi64[$c2 & 0x3f];
		}
		while (1);

		return $output;
	}

	public static function hash_password($password)
	{
		$random = '';

		if (CRYPT_BLOWFISH == 1 AND !self::$_portable_hashes)
		{
			$random = self::_get_bandom_bytes(16);
			$hash = crypt($password, self::_gensalt_blowfish($random));
			if (strlen($hash) == 60)
				return $hash;
		}

		if (CRYPT_EXT_DES == 1 AND !self::$_portable_hashes)
		{
			if (strlen($random) < 3)
				$random = self::_get_bandom_bytes(3);
			$hash = crypt($password, self::_gensalt_extended($random));
			if (strlen($hash) == 20)
				return $hash;
		}

		if (strlen($random) < 6)
			$random = self::_get_bandom_bytes(6);
		$hash = self::_crypt($password, self::_gensalt($random));
		if (strlen($hash) == 34)
			return $hash;

		return '*';
	}

	public static function verify_password($password, $stored_hash)
	{
		$hash = self::_crypt($password, $stored_hash);
		if ($hash[0] == '*')
			$hash = crypt($password, $stored_hash);

		return $hash == $stored_hash;
	}
}
// End Crypto_Hash_Password