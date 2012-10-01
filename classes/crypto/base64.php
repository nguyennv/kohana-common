<?php defined('SYSPATH') or die('No direct script access.');

class Crypto_Base64{
	const END_OF_INPUT = -1;

	private static $_base_64_chars = array(
		'A','B','C','D','E','F','G','H',
		'I','J','K','L','M','N','O','P',
		'Q','R','S','T','U','V','W','X',
		'Y','Z','a','b','c','d','e','f',
		'g','h','i','j','k','l','m','n',
		'o','p','q','r','s','t','u','v',
		'w','x','y','z','0','1','2','3',
		'4','5','6','7','8','9','+','/',
	);

	private static $_reverse_base_64_chars = array();

	private static $_base_64_string = '';
	private static $_base64_count = 0;

	private static function _set_base64_string($string)
	{
		self::$_base_64_string = $string;
		self::$_base64_count = 0;
	}
	
	private static function _read_base64()
	{	
		if (empty(self::$_base_64_string)) return self::END_OF_INPUT;
		if (self::$_base64_count >= strlen(self::$_base_64_string)) return self::END_OF_INPUT;
		$c = ord(self::$_base_64_string[self::$_base64_count]) & 0xff;
		self::$_base64_count++;
		return $c;
	}

	public static function encode($string)
	{
		self::_set_base64_string($string);
		$result = '';
		$in_buffer = array();
		$line_count = 0;
		$done = false;
		while (!$done AND ($in_buffer[0] = self::_read_base64()) != self::END_OF_INPUT)
		{
			$in_buffer[1] = self::_read_base64();
			$in_buffer[2] = self::_read_base64();
			$result .= self::$_base_64_chars[$in_buffer[0] >> 2];
			if ($in_buffer[1] != self::END_OF_INPUT)
			{
				$result .= (self::$_base_64_chars [(( $in_buffer[0] << 4 ) & 0x30) | ($in_buffer[1] >> 4) ]);
				if ($in_buffer[2] != self::END_OF_INPUT)
				{
					$result .= (self::$_base_64_chars [(($in_buffer[1] << 2) & 0x3c) | ($in_buffer[2] >> 6) ]);
					$result .= (self::$_base_64_chars [$in_buffer[2] & 0x3F]);
				}
				else
				{
					$result .= (self::$_base_64_chars [(($in_buffer[1] << 2) & 0x3c)]);
					$result .= '=';
					$done = true;
				}
			}
			else
			{
				$result .= (self::$_base_64_chars [(($in_buffer[0] << 4 ) & 0x30)]);
				$result .= '=';
				$result .= '=';
				$done = true;
			}
			$line_count += 4;
			if ($line_count >= 76)
			{
				//$result .= "\n";
				$line_count = 0;
			}
		}
		return $result;
	}

	public static function decode($input)
	{
		self::$_reverse_base_64_chars = array_flip(self::$_base_64_chars);
		self::$_reverse_base_64_chars['='] = 64;
		$output = '';
	    $i = 0;

		// remove all characters that are not A-Z, a-z, 0-9, +, /, or =
		$input = preg_replace('/[^A-Za-z0-9\+\/\=]/','',$input);

		do
		{
			$enc1 = self::$_reverse_base_64_chars[$input[$i++]];
			$enc2 = self::$_reverse_base_64_chars[$input[$i++]];
			$enc3 = self::$_reverse_base_64_chars[$input[$i++]];
			$enc4 = self::$_reverse_base_64_chars[$input[$i++]];

			$chr1 = ($enc1 << 2) | ($enc2 >> 4);
			$chr2 = (($enc2 & 15) << 4) | ($enc3 >> 2);
			$chr3 = (($enc3 & 3) << 6) | $enc4;

			$output = $output.chr($chr1);

			if ($enc3 != 64)
			{
				$output = $output.chr($chr2);
			}
			if ($enc4 != 64)
			{
				$output = $output.chr($chr3);
			}
		} while ($i < strlen($input));

		return $output;
	}
}
//End Crypto_Base64