<?php defined('SYSPATH') or die('No direct script access.');

class Crypto_Hash_Simple{
	private static $_rand_seed = 'simple';
	private static $_rand_seed_last_update = 0;
	private static $_dss_seeded = false;

	private static $_itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

	public static function compute_hash($password)
	{
		$random_state = self::unique_id();
		$random = '';
		$count = 6;

		if (($fh = @fopen('/dev/urandom', 'rb')))
		{
			$random = fread($fh, $count);
			fclose($fh);
		}

		if (strlen($random) < $count)
		{
			$random = '';

			for ($i = 0; $i < $count; $i += 16)
			{
				$random_state = md5(self::unique_id().$random_state);
				$random .= pack('H*', md5($random_state));
			}
			$random = substr($random, 0, $count);
		}

		$hash = self::_hash_crypt($password, self::_hash_gen_salt($random));

		if (strlen($hash) == 34)
		{
			return $hash;
		}

		return md5($password);
	}

	public static function verify_hash($password, $hash)
	{
		if (strlen($hash) == 34)
		{
			return (self::_hash_crypt($password, $hash) === $hash) ? true : false;
		}

		return (md5($password) === $hash) ? true : false;
	}

	public static function unique_id($extra = 'unique')
	{
		$val = self::$_rand_seed.microtime();
		$val = md5($val);
		self::$_rand_seed = md5(self::$_rand_seed.$val.$extra);

		if (self::$_dss_seeded !== true AND (self::$_rand_seed_last_update < time() - rand(1,10)))
		{
			self::$_rand_seed_last_update = time();
			self::$_dss_seeded = true;
		}

		return substr($val, 4, 16);
	}

	private static function _hash_crypt($password, $setting)
	{
		$output = '*';

		// Check for correct hash
		if (substr($setting, 0, 3) != '$H$')
		{
			return $output;
		}

		$count_log2 = strpos(self::$_itoa64, $setting[3]);

		if ($count_log2 < 7 OR $count_log2 > 30)
		{
			return $output;
		}

		$count = 1 << $count_log2;
		$salt = substr($setting, 4, 8);

		if (strlen($salt) != 8)
		{
			return $output;
		}

		if (PHP_VERSION >= 5)
		{
			$hash = md5($salt.$password, true);
			do
			{
				$hash = md5($hash.$password, true);
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
		$output .= self::_hash_encode_64($hash, 16, self::$_itoa64);

		return $output;
	}

	private static function _hash_gen_salt($input, $iteration_count_log2 = 6)
	{
		if ($iteration_count_log2 < 4 OR $iteration_count_log2 > 31)
		{
			$iteration_count_log2 = 8;
		}

		$output = '$H$';
		$output .= self::$_itoa64[min($iteration_count_log2 + ((PHP_VERSION >= 5) ? 5 : 3), 30)];
		$output .= self::_hash_encode_64($input, 6);

		return $output;
	}

	private static function _hash_encode_64($input, $count)
	{
		$output = '';
		$i = 0;

		do
		{
			$value = ord($input[$i++]);
			$output .= self::$_itoa64[$value & 0x3f];

			if ($i < $count)
			{
				$value |= ord($input[$i]) << 8;
			}

			$output .= self::$_itoa64[($value >> 6) & 0x3f];

			if ($i++ >= $count)
			{
				break;
			}

			if ($i < $count)
			{
				$value |= ord($input[$i]) << 16;
			}

			$output .= self::$_itoa64[($value >> 12) & 0x3f];

			if ($i++ >= $count)
			{
				break;
			}

			$output .= self::$_itoa64[($value >> 18) & 0x3f];
		}
		while ($i < $count);

		return $output;
	}
}
// End Crypto_Hash_Simple