<?php defined('SYSPATH') or die('No direct script access.');
/**
 * HTML helper class. Provides generic methods for generating various HTML
 * tags and making output HTML safe.
 *
 * @package    Application core
 * @category   Helpers
 * @author     Nguyen Van Nguyen
 * @copyright  (c) 2011-2012 Nguyen Van Nguyen
 * @license    http://kohanaframework.org/license
 */
class HTML extends Kohana_HTML {
	private static $_json;

	public static $doctypes = null;
	public static $html5 = false;

	public static function action_anchor($title, $action_name, $controller_name = '', array $params = NULL, array $attributes = NULL)
	{
		$url = URL::action_url($action_name, $controller_name, $params);
		return self::anchor($url, $title, $attributes);
	}

	public static function route_anchor($title, $route_name, array $params = NULL, array $attributes = NULL)
	{
		$url = URL::route_url($route_name, $params);
		return self::anchor($url, $title, $attributes);
	}

	public static function render_action($action_name, $controller_name = '', array $params = NULL)
	{
		$url = URL::action_url($action_name, $controller_name, $params);
		return Request::factory($url)->execute()->send_headers()->body();
	}

	public static function render_view($view_name, array $data = NULL)
	{
		$request = Request::current();
		$controller = $request->controller();
		$directory = $request->directory();
		if(empty($directory))
		{
			$view_file = $controller.DIRECTORY_SEPARATOR.$view_name;
		}
		else
		{
			$view_file = $directory.DIRECTORY_SEPARATOR.$controller.DIRECTORY_SEPARATOR.$view_name;
		}
		return View::render_partial($view_file, $data);
	}

	public static function anti_forgery_token($new = FALSE)
	{
		$session = Session::instance();
		$config = Kohana::$config->load('security');
		$token_name = $config->get('csrf_token_name', 'request-verification-token');
		$csrf_token = $session->get($token_name);
		if ($new === TRUE OR ! $csrf_token)
		{
			$csrf_key = $config->get('csrf_key', Security::token(TRUE));
			$csrf_token = Crypto_Hash_Simple::compute_hash($csrf_key);
			$session->set($token_name, $csrf_token);
		}
		return Form::hidden($token_name, $csrf_token, array('id' => $token_name));
	}

	/**
	 * Generates a html meta tag
	 *
	 * @param	string|array	multiple inputs or name/http-equiv value
	 * @param	string			content value
	 * @param	string			name or http-equiv
	 * @return	string
	 */
	public static function meta($name = '', $content = '', $type = 'name')
	{
		if( ! is_array($name))
		{
			$result = '<meta'.HTML::attributes(array($type => $name, 'content' => $content)).' />';
		}
		elseif(is_array($name))
		{
			$result = "";
			foreach($name as $array)
			{
				$meta = $array;
				$result .= "\n".'<meta'.HTML::attributes($meta).' />';
			}
		}
		return $result;
	}

	/**
	 * Generates a html doctype tag
	 *
	 * @param	string	doctype declaration key from doctypes config
	 * @return	string
	 */
	public static function doctype($type = 'xhtml1-trans')
	{
		if(self::$doctypes === null)
		{
			self::$doctypes = Kohana::$config->load('doctypes')->as_array();
		}

		if(is_array(self::$doctypes) and isset(self::$doctypes[$type]))
		{
			if($type == "html5")
			{
				self::$html5 = true;
			}
			return self::$doctypes[$type];
		}
		else
		{
			return false;
		}
	}

	/**
	 * Generates a html un-ordered list tag
	 *
	 * @param	array			list items, may be nested
	 * @param	array|string	outer list attributes
	 * @return	string
	 */
	public static function ul(array $list = array(), $attr = false)
	{
		return self::build_list('ul', $list, $attr);
	}

	/**
	 * Generates a html ordered list tag
	 *
	 * @param	array			list items, may be nested
	 * @param	array|string	outer list attributes
	 * @return	string
	 */
	public static function ol(array $list = array(), $attr = false)
	{
		return self::build_list('ol', $list, $attr);
	}

	/**
	 * Generates the html for the list methods
	 *
	 * @param	string	list type (ol or ul)
	 * @param	array	list items, may be nested
	 * @param	array	tag attributes
	 * @param	string	indentation
	 * @return	string
	 */
	protected static function build_list($type = 'ul', array $list = array(), $attr = false, $indent = '')
	{
		if ( ! is_array($list))
		{
			$result = false;
		}

		$out = '';
		foreach ($list as $key => $val)
		{
			if ( ! is_array($val))
			{
				$out .= $indent."\t".'<li>'.$val.'</li>'.PHP_EOL;
			}
			else
			{
				$out .= $indent."\t".'<li>'.$key.PHP_EOL.self::build_list($type, $val, '', $indent."\t\t").$indent."\t".'</li>'.PHP_EOL;
			}
		}
		return '<'.$type.HTML::attributes($attr).'>'.PHP_EOL.$out.$indent.'</'.$type.'>';
	}

	public static function raw($html = NULL)
	{
		return htmlspecialchars_decode($html);
	}

	public static function script_blocks(array $scripts = array())
	{
		if(count($scripts))
			return "<script type=\"text/javascript\">\n/*<![CDATA[*/\n".implode("\n", $scripts)."\n/*]]>*/\n</script>\n";
		else
			return '';
	}

	public static function script_block($script)
	{
		return "<script type=\"text/javascript\">\n/*<![CDATA[*/\n{$script}\n/*]]>*/\n</script>\n";
	}

	public static function style_blocks(array $styles = array())
	{
		if(count($styles))
			return "<style type=\"text/css\">\n".implode("\n", $styles)."\n</style>\n";
		else
			return '';
	}

	public static function quote_string($js, $for_url = FALSE)
	{
		if($for_url)
			return strtr($js,array('%'=>'%25',"\t"=>'\t',"\n"=>'\n',"\r"=>'\r','"'=>'\"','\''=>'\\\'','\\'=>'\\\\'));
		else
			return strtr($js,array("\t"=>'\t',"\n"=>'\n',"\r"=>'\r','"'=>'\"','\''=>'\\\'','\\'=>'\\\\'));
	}

	public static function quote_function($js)
	{
		if(self::is_js_function($js))
			return $js;
		else
			return 'javascript:'.$js;
	}

	public static function is_js_function($js)
	{
		return preg_match('/^\s*javascript:/i', $js);
	}

	public static function encode_js($value, $to_map = TRUE, $encode_empty_strings = FALSE)
	{
		if(is_string($value))
		{
			if(($n = strlen($value)) > 2)
			{
				$first = $value[0];
				$last = $value[$n-1];
				if(($first === '[' AND $last===']') OR ($first === '{' AND $last === '}'))
					return $value;
			}

			if(self::is_js_function($value))
			{
				return preg_replace('/^\s*javascript:/', '', $value);
			}
			else
			{
				return "'".self::quote_string($value)."'";
			}
		}
		elseif(is_bool($value))
		{
			return $value ? 'true' : 'false';
		}
		elseif(is_array($value))
		{
			$results = '';
			if(($n = count($value)) > 0 AND array_keys($value) !== range(0, $n-1))
			{
				foreach($value as $k => $v)
				{
					if($v !== '' OR $encode_empty_strings)
					{
						if($results !== '') $results .= ',';
						$results .= "'$k':".self::encode_js($v, $to_map, $encode_empty_strings);
					}
				}
				return '{'.$results.'}';
			}
			else
			{
				foreach($value as $v)
				{
					if($v !== '' OR $encode_empty_strings)
					{
						if($results !== '') $results .= ',';
						$results .= self::encode_js($v, $to_map, $encode_empty_strings);
					}
				}
				return '['.$results .']';
			}
		}
		elseif(is_integer($value))
		{
			return "$value";
		}
		elseif(is_float($value))
		{
			if($value === -INF)
				return 'Number.NEGATIVE_INFINITY';
			elseif($value === INF)
				return 'Number.POSITIVE_INFINITY';
			else
				return "$value";
		}
		elseif(is_object($value))
			return self::encode_js(get_object_vars($value), $to_map);
		elseif($value === NULL)
			return 'null';
		else
			return '';
	}

	public static function json_encode($value = NULL)
	{
		if(self::$_json === NULL)
		{
			self::$_json = new Crypto_JSON;
		}
		return self::$_json->encode($value);
	}

	public static function json_decode($value)
	{
		if(self::$_json === NULL)
		{
			self::$_json = new Crypto_JSON;
		}
		return self::$_json->decode($value);
	}

	public static function compression_output($content)
	{
		$zlib_on = ini_get('zlib.output_compression') OR (ini_set('zlib.output_compression', 0) === FALSE);
		if(Request::accept_encoding('deflate') AND !$zlib_on AND function_exists('gzdeflate'))
		{
			header('Content-Encoding: deflate');
			return gzdeflate($content, 9);
		}
		elseif(Request::accept_encoding('gzip') AND !$zlib_on AND function_exists('gzencode'))
		{
			header('Content-Encoding: gzip');
			return gzencode($content, 9);
		}
		else
		{
			return $content;
		}
	}
}
// End HTML
