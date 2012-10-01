<?php defined('SYSPATH') or die('No direct script access.');

class Helper_Utils{
	private static $_utf8_accents = array(
		'à' => 'a', 'á' => 'a', 'ả' => 'a', 'ã' => 'a', 'ạ' => 'a',
		'À' => 'A', 'Á' => 'A', 'Ả' => 'A', 'Ã' => 'A', 'Ạ' => 'A',
		'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a', 'ậ' => 'a',
		'Â' => 'A', 'Ầ' => 'A', 'Ấ' => 'A', 'Ẩ' => 'A', 'Ẫ' => 'A', 'Ậ' => 'A',
		'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a', 'ặ' => 'a',
		'Ă' => 'A', 'Ằ' => 'A', 'Ắ' => 'A', 'Ẳ' => 'A', 'Ẵ' => 'A', 'Ặ' => 'A',
		'ā' => 'a', 'ą' => 'a', 'å' => 'a', 'ä' => 'a',
		'Ā' => 'A', 'Ą' => 'A', 'Å' => 'A', 'Ä' => 'A',
		'ḃ' => 'b', 'Ḃ' => 'B',
		'ç' => 'c', 'ċ' => 'c', 'ĉ' => 'c', 'ć' => 'c', 'č' => 'c',
		'Ç' => 'C', 'Ċ' => 'C', 'Ĉ' => 'C', 'Ć' => 'C', 'Č' => 'C',
		'đ' => 'd', 'Đ' => 'D', 'ď' => 'd', 'Ď' => 'D', 'ḋ' => 'd', 'Ḋ' => 'D',
		'è' => 'e', 'é' => 'e', 'ẻ' => 'e', 'ẽ' => 'e', 'ẹ' => 'e',
		'È' => 'E', 'É' => 'E', 'Ẻ' => 'E', 'Ẽ' => 'E', 'Ẹ' => 'E',
		'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ể' => 'e', 'ễ' => 'e', 'ệ' => 'e',
		'Ê' => 'E', 'Ề' => 'E', 'Ế' => 'E', 'Ể' => 'E', 'Ễ' => 'E', 'Ệ' => 'E',
		'ë' => 'e', 'ě' => 'e', 'ė' => 'e', 'ę' => 'e', 'ē' => 'e', 'ĕ' => 'e',
		'Ë' => 'E', 'Ě' => 'E', 'Ė' => 'E', 'Ę' => 'E', 'Ē' => 'E', 'Ĕ' => 'E',
		'ḟ' => 'f', 'ƒ' => 'f', 'Ḟ' => 'F', 'Ƒ' => 'F',
		'ģ' => 'g', 'ğ' => 'g', 'ĝ' => 'g', 'ġ' => 'g',
		'Ģ' => 'G', 'Ğ' => 'G', 'Ĝ' => 'G', 'Ġ' => 'G',
		'ħ' => 'h', 'ĥ' => 'h', 'Ħ' => 'H', 'Ĥ' => 'H',
		'ì' => 'i', 'í' => 'i', 'ỉ' => 'i', 'ĩ' => 'i', 'ị' => 'i',
		'Ì' => 'I', 'Í' => 'I', 'Ỉ' => 'I', 'Ĩ' => 'I', 'Ị' => 'I',
		'î' => 'i', 'ï' => 'i', 'ī' => 'i', 'į' => 'i',
		'Î' => 'I', 'Ï' => 'I', 'Ī' => 'I', 'Į' => 'I',
		'ĵ' => 'j', 'Ĵ' => 'J',
		'ķ' => 'k', 'Ķ' => 'K',
		'ĺ' => 'l', 'ł' => 'l', 'ļ' => 'l', 'ľ' => 'l',
		'Ĺ' => 'L', 'Ł' => 'L', 'Ļ' => 'L', 'Ľ' => 'L',
		'ṁ' => 'm', 'Ṁ' => 'M', 
		'ň' => 'n', 'ņ' => 'n', 'ñ' => 'n', 'ń' => 'n',
		'Ň' => 'N', 'Ņ' => 'N', 'Ñ' => 'N', 'Ń' => 'N',
		'ò' => 'o', 'ó' => 'o', 'ỏ' => 'o', 'õ' => 'o', 'ọ' => 'o',
		'Ò' => 'O', 'Ó' => 'O', 'Ỏ' => 'O', 'Õ' => 'O', 'Ọ' => 'O',
		'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ổ' => 'o', 'ỗ' => 'o', 'ộ' => 'o',
		'Ô' => 'O', 'Ồ' => 'O', 'Ố' => 'O', 'Ổ' => 'O', 'Ỗ' => 'O', 'Ộ' => 'O', 
		'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ở' => 'o', 'ỡ' => 'o', 'ợ' => 'o',
		'Ơ' => 'O', 'Ờ' => 'O', 'Ớ' => 'O', 'Ở' => 'O', 'Ỡ' => 'O', 'Ợ' => 'O',
		'ø' => 'o', 'ö' => 'o', 'ő' => 'o', 'ō' => 'o',
		'Ø' => 'O', 'Ö' => 'O', 'Ő' => 'O', 'Ō' => 'O',
		'ṗ' => 'p', 'Ṗ' => 'P',
		'ř' => 'r', 'ŗ' => 'r', 'ŕ' => 'r',
		'Ř' => 'R', 'Ŗ' => 'R', 'Ŕ' => 'R',
		'š' => 's', 'ŝ' => 's', 'ṡ' => 's', 'ș' => 's', 'ś' => 's', 'ş' => 's',
		'Š' => 'S', 'Ŝ' => 'S', 'Ṡ' => 'S', 'Ș' => 'S', 'Ś' => 'S', 'Ş' => 'S',
		'ț' => 't', 'ŧ' => 't', 'ṫ' => 't', 'ť' => 't', 'ţ' => 't',
		'Ț' => 'T', 'Ŧ' => 'T', 'Ṫ' => 'T', 'Ť' => 'T', 'Ţ' => 'T',
		'ù' => 'u', 'ú' => 'u', 'ủ' => 'u', 'ũ' => 'u', 'ụ' => 'u',
		'Ù' => 'U', 'Ú' => 'U', 'Ủ' => 'U', 'Ũ' => 'U', 'Ụ' => 'U',
		'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ử' => 'u', 'ữ' => 'u', 'ự' => 'u',
		'Ư' => 'U', 'Ừ' => 'U', 'Ứ' => 'U', 'Ử' => 'U', 'Ữ' => 'U', 'Ự' => 'U',
		'ű' => 'u', 'ū' => 'u', 'ų' => 'u', 'ů' => 'u', 'ü' => 'u', 'ŭ' => 'u', 'û' => 'u', 'µ' => 'u',
		'Ű' => 'U', 'Ū' => 'U', 'Ų' => 'U', 'Ů' => 'U', 'Ü' => 'U', 'Ŭ' => 'U', 'Û' => 'U', 'µ' => 'U',
		'ẁ' => 'w', 'ŵ' => 'w', 'ẃ' => 'w', 'ẅ' => 'w',
		'Ẁ' => 'W', 'Ŵ' => 'W', 'Ẃ' => 'W', 'Ẅ' => 'W',
		'ỳ' => 'y', 'ý' => 'y', 'ỷ' => 'y', 'ỹ' => 'y', 'ỵ' => 'y',
		'Ỳ' => 'Y', 'Ý' => 'Y', 'Ỷ' => 'Y', 'Ỹ' => 'Y', 'Ỵ' => 'Y',
		'ŷ' => 'y', 'ÿ' => 'y', 'Ŷ' => 'Y', 'Ÿ' => 'Y',
		'ž' => 'z', 'ż' => 'z', 'ź' => 'z',
		'Ž' => 'Z', 'Ż' => 'Z', 'Ź' => 'Z',
		'ß' => 'ss', 'þ' => 'th', 'ð' => 'dh', 'æ' => 'ae',
		'Þ' => 'Th', 'Ð' => 'Dh', 'Æ' => 'Ae',
	);

	private static $_index_ascii = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

	public static function id_to_alpha($in, $pad_up = false)
	{
		$base  = strlen(self::$_index_ascii);
		if(is_numeric($pad_up))
		{
			$pad_up--;
			if ($pad_up > 0)
			{
				$in += pow($base, $pad_up);
			}
		}

		$out = "";
		for ($t = floor(log10($in) / log10($base)); $t >= 0; $t--)
		{
			$a   = floor($in / bcpow($base, $t));
			$out = $out.substr(self::$_index_ascii, $a, 1);
			$in  = $in - ($a * bcpow($base, $t));
		}
		$out = strrev($out); // reverse
		//$out = base64_encode($out);

		return $out;
	}

	public static function alpha_to_id($in, $pad_up = false)
	{
		$base  = strlen(self::$_index_ascii);
		//$in = base64_decode($in);
		$in  = strrev($in);
		$out = 0;
		$len = strlen($in) - 1;

		for ($t = 0; $t <= $len; $t++)
		{
			$bcpow = bcpow($base, $len - $t);
			$out   = $out + strpos(self::$_index_ascii, substr($in, $t, 1)) * $bcpow;
		}

		if (is_numeric($pad_up))
		{
			$pad_up--;
			if ($pad_up > 0)
			{
				$out -= pow($base, $pad_up);
			}
		}

		return $out;
	}

	public static function strip_slashes(&$data)
	{
		return is_array($data)?array_map(array('Utils','strip_slashes'),$data):stripslashes($data);
	}

	public static function add_slashes(&$data)
	{
		return is_array($data)?array_map(array('Utils','add_slashes'),$data):addslashes($data);
	}

	public static function encode_data($data)
	{
		if(is_array($data))
			return array_map(array('Utils','encode_data'),$data);
		else
			return strtr($data,array('&' => '&amp;','"' => '&quot;',"'"=>'&#039;','<' => '&lt;','>' => '&gt;'));
	}

	public static function decode_data($data)
	{
		if(is_array($data))
			return array_map(array('Utils','decode_data'),$data);
		else
			return strtr($data,array('&amp;' => '&','&quot;' => '"','&#039;'=>"'",'&lt;' => '<','&gt;' => '>'));
	}

	public static function serialize_object($object)
	{
		$v = array();
		$v[0] = $object;
		return serialize($v);
	}

	public static function unserialize_object($string)
	{
		$v = unserialize($string);
		if(!is_array($v) || count($v)!==1 || !isset($v[0]))
			throw new Kohana_Exception('Unserialize failed due to incompatible serialized data.');
		return $v[0];
	}

	public static function utf8_to_ascii($string)
	{
		return str_replace(
			array_keys(self::$_utf8_accents),
			array_values(self::$_utf8_accents),
			$string
		);
	}

	public static function url_lize($string, $separator = '-')
	{
		$title = preg_replace('![^'.preg_quote($separator).'a-z0-9\s]+!', '', strtolower(Utils::utf8_to_ascii($string)));
		$title = preg_replace('!['.preg_quote($separator).'\s]+!u', $separator, $title);
		return trim($title, $separator);
	}

	public static function clean_html($string = '')
	{
		$string = preg_replace('{(<br\s*\/?\s*>|<p\s*>)}i', "\n", $string);
		$string = preg_replace('{([\r\n])[\s]+}', '\1', $string);
		$string = preg_replace('{<script[^>]*?\s*>.*?</script>}si', '', $string);
		return preg_replace('{<\/?\w+((\s*[\w\-]+=\'.*?\'|\s*[\w\-]+=".*?")*)\s*\/?\s*>}ms', '', $string);
	}

	public static function repeat_text($string, $count = 1, $separator = '')
	{
		$text = '';
		for($i = 0; $i < $count; $i++)
		{
			if(empty($text))
			{
				$text = $string;
			}
			else
			{
				$text .= $separator.$string;
			}
		}
		return $text;
	}
}
// End Helper_Utils