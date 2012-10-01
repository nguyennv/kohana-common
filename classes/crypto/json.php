<?php defined('SYSPATH') or die('No direct script access.');

class Crypto_JSON {
	const JSON_SLICE = 1;
	const JSON_IN_STR = 2;
	const JSON_IN_ARR = 4;
	const JSON_IN_OBJ = 8;
	const JSON_IN_CMT = 16;
	const JSON_LOOSE_TYPE = 10;
	const JSON_STRICT_TYPE = 11;
	
	private $_use;

    public function __construct($use = self::JSON_STRICT_TYPE)
	{
        $this->_use = $use;
    }

    public function encode($var)
	{
        switch (gettype($var))
		{
            case 'boolean':
                return $var ? 'true' : 'false';
            case 'NULL':
                return 'null';
            case 'integer':
                return (int) $var;
            case 'double':
            case 'float':
                return (float) $var;
            case 'string':
				//$var = iconv($enc, 'UTF-8', $var);
                // STRINGS ARE EXPECTED TO BE IN ASCII OR UTF-8 FORMAT
                $ascii = '';
                $strlen_var = strlen($var);

               /*
                * Iterate over every character in the string,
                * escaping with a slash or encoding to UTF-8 where necessary
                */
                for ($c = 0; $c < $strlen_var; ++$c)
				{
                    $ord_var_c = ord($var{$c});

                    switch (true)
					{
                        case $ord_var_c == 0x08:
                            $ascii .= '\b';
                            break;
                        case $ord_var_c == 0x09:
                            $ascii .= '\t';
                            break;
                        case $ord_var_c == 0x0A:
                            $ascii .= '\n';
                            break;
                        case $ord_var_c == 0x0C:
                            $ascii .= '\f';
                            break;
                        case $ord_var_c == 0x0D:
                            $ascii .= '\r';
                            break;

                        case $ord_var_c == 0x22:
                        case $ord_var_c == 0x2F:
                        case $ord_var_c == 0x5C:
                            // double quote, slash, slosh
                            $ascii .= '\\'.$var{$c};
                            break;

                        case (($ord_var_c >= 0x20) && ($ord_var_c <= 0x7F)):
                            // characters U-00000000 - U-0000007F (same as ASCII)
                            $ascii .= $var{$c};
                            break;

                        case (($ord_var_c & 0xE0) == 0xC0):
                            // characters U-00000080 - U-000007FF, mask 110XXXXX
                            // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                            $char = pack('C*', $ord_var_c, ord($var{$c+1}));
                            $c+=1;
                            $utf16 =  $this->utf8_to_utf16be($char);
                            $ascii .= sprintf('\u%04s', bin2hex($utf16));
                            break;

                        case (($ord_var_c & 0xF0) == 0xE0):
                            // characters U-00000800 - U-0000FFFF, mask 1110XXXX
                            // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                            $char = pack('C*', $ord_var_c, ord($var{$c+1}), ord($var{$c+2}));
                            $c+=2;
                            $utf16 = $this->utf8_to_utf16be($char);
                            $ascii .= sprintf('\u%04s', bin2hex($utf16));
                            break;

                        case (($ord_var_c & 0xF8) == 0xF0):
                            // characters U-00010000 - U-001FFFFF, mask 11110XXX
                            // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                            $char = pack('C*', $ord_var_c,
                                         ord($var{$c+1}),
                                         ord($var{$c+2}),
                                         ord($var{$c+3}));
                            $c+=3;
                            $utf16 = $this->utf8_to_utf16be($char);
                            $ascii .= sprintf('\u%04s', bin2hex($utf16));
                            break;

                        case (($ord_var_c & 0xFC) == 0xF8):
                            // characters U-00200000 - U-03FFFFFF, mask 111110XX
                            // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                            $char = pack('C*', $ord_var_c,
                                         ord($var{$c+1}),
                                         ord($var{$c+2}),
                                         ord($var{$c+3}),
                                         ord($var{$c+4}));
                            $c+=4;
                            $utf16 = $this->utf8_to_utf16be($char);
                            $ascii .= sprintf('\u%04s', bin2hex($utf16));
                            break;

                        case (($ord_var_c & 0xFE) == 0xFC):
                            // characters U-04000000 - U-7FFFFFFF, mask 1111110X
                            // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                            $char = pack('C*', $ord_var_c,
                                         ord($var{$c+1}),
                                         ord($var{$c+2}),
                                         ord($var{$c+3}),
                                         ord($var{$c+4}),
                                         ord($var{$c+5}));
                            $c+=5;
                            $utf16 = $this->utf8_to_utf16be($char);
                            $ascii .= sprintf('\u%04s', bin2hex($utf16));
                            break;
                    }
                }
                return '"'.$ascii.'"';

            case 'array':
                // treat as a JSON object
                if (is_array($var) && count($var) && (array_keys($var) !== range(0, sizeof($var) - 1)))
				{
                    return '{' .
                           join(',', array_map(array($this, 'name_value'),
                                               array_keys($var),
                                               array_values($var)))
                          .'}';
                }

                // treat it like a regular array
                return '['.join(',', array_map(array($this, 'encode'), $var)).']';

            case 'object':
                $vars = get_object_vars($var);
                return '{' .
                       join(',', array_map(array($this, 'name_value'),
                                           array_keys($vars),
                                           array_values($vars)))
                      .'}';

            default:
                return '';
        }
    }

    public function enc($var)
	{
        return $this->encode($var);
    }

    protected function name_value($name, $value)
	{
        return $this->encode(strval($name)).':'.$this->encode($value);
    }

    protected function reduce_string($str)
	{
        $str = preg_replace(array(
                // eliminate single line comments in '// ...' form
                '#^\s*//(.+)$#m',
                // eliminate multi-line comments in '/* ... */' form, at start of string
                '#^\s*/\*(.+)\*/#Us',
                // eliminate multi-line comments in '/* ... */' form, at end of string
                '#/\*(.+)\*/\s*$#Us'
            ), '', $str);

        // eliminate extraneous space
        return trim($str);
    }

    public function decode($str){
        $str = $this->reduce_string($str);
        switch (strtolower($str))
		{
            case 'true':
                return true;

            case 'false':
                return false;

            case 'null':
                return null;

            default:
                if (is_numeric($str))
				{
                    // Return float or int, as appropriate
                    return ((float)$str == (integer)$str)
                        ? (integer)$str
                        : (float)$str;

                }
				elseif (preg_match('/^("|\').+(\1)$/s', $str, $m) && $m[1] == $m[2])
				{
                    // STRINGS RETURNED IN UTF-8 FORMAT
                    $delim = substr($str, 0, 1);
                    $chrs = substr($str, 1, -1);
                    $utf8 = '';
                    $strlen_chrs = strlen($chrs);

                    for ($c = 0; $c < $strlen_chrs; ++$c)
					{
                        $substr_chrs_c_2 = substr($chrs, $c, 2);
                        $ord_chrs_c = ord($chrs{$c});

                        switch (true)
						{
                            case $substr_chrs_c_2 == '\b':
                                $utf8 .= chr(0x08);
                                ++$c;
                                break;
                            case $substr_chrs_c_2 == '\t':
                                $utf8 .= chr(0x09);
                                ++$c;
                                break;
                            case $substr_chrs_c_2 == '\n':
                                $utf8 .= chr(0x0A);
                                ++$c;
                                break;
                            case $substr_chrs_c_2 == '\f':
                                $utf8 .= chr(0x0C);
                                ++$c;
                                break;
                            case $substr_chrs_c_2 == '\r':
                                $utf8 .= chr(0x0D);
                                ++$c;
                                break;

                            case $substr_chrs_c_2 == '\\"':
                            case $substr_chrs_c_2 == '\\\'':
                            case $substr_chrs_c_2 == '\\\\':
                            case $substr_chrs_c_2 == '\\/':
                                if (($delim == '"' && $substr_chrs_c_2 != '\\\'') ||
                                   ($delim == "'" && $substr_chrs_c_2 != '\\"'))
								{
									$utf8 .= $chrs{++$c};
                                }
                                break;

                            case preg_match('/\\\u[0-9A-F]{4}/i', substr($chrs, $c, 6)):
                                // single, escaped unicode character
                                $utf16 = chr(hexdec(substr($chrs, ($c+2), 2)))
                                      .chr(hexdec(substr($chrs, ($c+4), 2)));
                                $utf8 .= $this->utf16be_to_utf8($utf16);
                                $c+=5;
                                break;

                            case ($ord_chrs_c >= 0x20) && ($ord_chrs_c <= 0x7F):
                                $utf8 .= $chrs{$c};
                                break;

                            case ($ord_chrs_c & 0xE0) == 0xC0:
                                // characters U-00000080 - U-000007FF, mask 110XXXXX
                                //see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                $utf8 .= substr($chrs, $c, 2);
                                ++$c;
                                break;

                            case ($ord_chrs_c & 0xF0) == 0xE0:
                                // characters U-00000800 - U-0000FFFF, mask 1110XXXX
                                // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                $utf8 .= substr($chrs, $c, 3);
                                $c += 2;
                                break;

                            case ($ord_chrs_c & 0xF8) == 0xF0:
                                // characters U-00010000 - U-001FFFFF, mask 11110XXX
                                // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                $utf8 .= substr($chrs, $c, 4);
                                $c += 3;
                                break;

                            case ($ord_chrs_c & 0xFC) == 0xF8:
                                // characters U-00200000 - U-03FFFFFF, mask 111110XX
                                // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                $utf8 .= substr($chrs, $c, 5);
                                $c += 4;
                                break;

                            case ($ord_chrs_c & 0xFE) == 0xFC:
                                // characters U-04000000 - U-7FFFFFFF, mask 1111110X
                                // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                $utf8 .= substr($chrs, $c, 6);
                                $c += 5;
                                break;

                        }
                    }
                    return $utf8;
                }
				elseif (preg_match('/^\[.*\]$/s', $str) || preg_match('/^\{.*\}$/s', $str))
				{
                    // array, or object notation
                    if ($str{0} == '[')
					{
                        $stk = array(self::JSON_IN_ARR);
                        $arr = array();
                    }
					else
					{
                        if ($this->_use == self::JSON_LOOSE_TYPE)
						{
                            $stk = array(self::JSON_IN_OBJ);
                            $obj = array();
                        }
						else
						{
                            $stk = array(self::JSON_IN_OBJ);
                            $obj = new stdClass();
                        }
                    }

                    array_push($stk, array('what'  => self::JSON_SLICE,
                                           'where' => 0,
                                           'delim' => false));

                    $chrs = substr($str, 1, -1);
                    $chrs = $this->reduce_string($chrs);

                    if ($chrs == '')
					{
                        if (reset($stk) == self::JSON_IN_ARR)
						{
                            return $arr;
                        }
						else
						{
                            return $obj;
                        }
                    }

                    //print("\nparsing {$chrs}\n");

                    $strlen_chrs = strlen($chrs);

                    for ($c = 0; $c <= $strlen_chrs; ++$c)
					{
                        $top = end($stk);
                        $substr_chrs_c_2 = substr($chrs, $c, 2);

                        if (($c == $strlen_chrs) || (($chrs{$c} == ',') && ($top['what'] == self::JSON_SLICE)))
						{
                            // found a comma that is not inside a string, array, etc.,
                            // OR we've reached the end of the character list
                            $slice = substr($chrs, $top['where'], ($c - $top['where']));
                            array_push($stk, array('what' => self::JSON_SLICE, 'where' => ($c + 1), 'delim' => false));
                            //print("Found split at {$c}: ".substr($chrs, $top['where'], (1 + $c - $top['where']))."\n");

                            if (reset($stk) == self::JSON_IN_ARR)
							{
                                // we are in an array, so just push an element onto the stack
                                array_push($arr, $this->decode($slice));
                            }
							elseif (reset($stk) == self::JSON_IN_OBJ)
							{
                                // we are in an object, so figure
                                // out the property name and set an
                                // element in an associative array,
                                // for now
                                if (preg_match('/^\s*(["\'].*[^\\\]["\'])\s*:\s*(\S.*),?$/Uis', $slice, $parts))
								{
                                    // "name":value pair
                                    $key = $this->decode($parts[1]);
                                    $val = $this->decode($parts[2]);

                                    if ($this->_use == self::JSON_LOOSE_TYPE)
									{
                                        $obj[$key] = $val;
                                    }
									else
									{
                                        $obj->$key = $val;
                                    }
                                }
								elseif (preg_match('/^\s*(\w+)\s*:\s*(\S.*),?$/Uis', $slice, $parts))
								{
                                    // name:value pair, where name is unquoted
                                    $key = $parts[1];
                                    $val = $this->decode($parts[2]);

                                    if ($this->_use == self::JSON_LOOSE_TYPE)
									{
                                        $obj[$key] = $val;
                                    }
									else
									{
                                        $obj->$key = $val;
                                    }
                                }
                            }
                        }
						elseif ((($chrs{$c} == '"') || ($chrs{$c} == "'")) && ($top['what'] != self::JSON_IN_STR))
						{
                            // found a quote, and we are not inside a string
                            array_push($stk, array('what' => self::JSON_IN_STR, 'where' => $c, 'delim' => $chrs{$c}));
                            //print("Found start of string at {$c}\n");
                        }
						elseif (($chrs{$c} == $top['delim']) &&
                                ($top['what'] == self::JSON_IN_STR) &&
                                (($chrs{$c - 1} != "\\") ||
                                ($chrs{$c - 1} == "\\" && $chrs{$c - 2} == "\\")))
						{
                            // found a quote, we're in a string, and it's not escaped
                            array_pop($stk);
                            //print("Found end of string at {$c}: ".substr($chrs, $top['where'], (1 + 1 + $c - $top['where']))."\n");
                        }
						elseif (($chrs{$c} == '[') &&
                                in_array($top['what'], array(self::JSON_SLICE, self::JSON_IN_ARR, self::JSON_IN_OBJ)))
						{
                            // found a left-bracket, and we are in an array, object, or slice
                            array_push($stk, array('what' => self::JSON_IN_ARR, 'where' => $c, 'delim' => false));
                            //print("Found start of array at {$c}\n");
                        }
						elseif (($chrs{$c} == ']') && ($top['what'] == self::JSON_IN_ARR))
						{
                            // found a right-bracket, and we're in an array
                            array_pop($stk);
                            //print("Found end of array at {$c}: ".substr($chrs, $top['where'], (1 + $c - $top['where']))."\n");
                        }
						elseif (($chrs{$c} == '{') &&
                                 in_array($top['what'], array(self::JSON_SLICE, self::JSON_IN_ARR, self::JSON_IN_OBJ)))
						{
                            // found a left-brace, and we are in an array, object, or slice
                            array_push($stk, array('what' => self::JSON_IN_OBJ, 'where' => $c, 'delim' => false));
                            //print("Found start of object at {$c}\n");
                        }
						elseif (($chrs{$c} == '}') && ($top['what'] == self::JSON_IN_OBJ))
						{
                            // found a right-brace, and we're in an object
                            array_pop($stk);
                            //print("Found end of object at {$c}: ".substr($chrs, $top['where'], (1 + $c - $top['where']))."\n");
                        }
						elseif (($substr_chrs_c_2 == '/*') &&
                                 in_array($top['what'], array(self::JSON_SLICE, self::JSON_IN_ARR, self::JSON_IN_OBJ)))
						{
                            // found a comment start, and we are in an array, object, or slice
                            array_push($stk, array('what' => self::JSON_IN_CMT, 'where' => $c, 'delim' => false));
                            $c++;
                            //print("Found start of comment at {$c}\n");
                        }
						elseif (($substr_chrs_c_2 == '*/') && ($top['what'] == self::JSON_IN_CMT))
						{
                            // found a comment end, and we're in one now
                            array_pop($stk);
                            $c++;

                            for ($i = $top['where']; $i <= $c; ++$i)
                                $chrs = substr_replace($chrs, ' ', $i, 1);
                            //print("Found end of comment at {$c}: ".substr($chrs, $top['where'], (1 + $c - $top['where']))."\n");
                        }
                    }

                    if (reset($stk) == self::JSON_IN_ARR)
					{
                        return $arr;
                    }
					elseif (reset($stk) == self::JSON_IN_OBJ)
					{
                        return $obj;
                    }
                }
        }
    }

    public function dec($var)
	{
        return $this->decode($var);
    }

	protected function utf8_to_unicode( &$str )
	{
		$unicode = array();
		$values = array();
		$looking_for = 1;

		for ($i = 0; $i < strlen( $str ); $i++ )
		{
			$this_value = ord( $str[ $i ] );
			if ( $this_value < 128 )
			{
				$unicode[] = $this_value;
			}
			else
			{
				if ( count( $values ) == 0 )
				{
					$looking_for = ( $this_value < 224 ) ? 2 : 3;
				}
				$values[] = $this_value;
				if ( count( $values ) == $looking_for )
				{
					$number = ( $looking_for == 3 ) ?
						( ( $values[0] % 16 ) * 4096 ) + ( ( $values[1] % 64 ) * 64 ) + ( $values[2] % 64 ):
						( ( $values[0] % 32 ) * 64 ) + ( $values[1] % 64 );
					$unicode[] = $number;
					$values = array();
					$looking_for = 1;
				}
			}
		}
		return $unicode;
	}

	protected function unicode_to_utf8( & $str)
	{
		$utf8 = '';
		foreach( $str as $unicode )
		{
			if ( $unicode < 128 )
			{
				$utf8.= chr( $unicode );
			}
			elseif ( $unicode < 2048 )
			{
				$utf8.= chr( 192 +  ( ( $unicode - ( $unicode % 64 ) ) / 64 ) );
				$utf8.= chr( 128 + ( $unicode % 64 ) );
			}
			else
			{
				$utf8.= chr( 224 + ( ( $unicode - ( $unicode % 4096 ) ) / 4096 ) );
				$utf8.= chr( 128 + ( ( ( $unicode % 4096 ) - ( $unicode % 64 ) ) / 64 ) );
				$utf8.= chr( 128 + ( $unicode % 64 ) );
			}
		}
		return $utf8;
	}

	protected function utf8_to_utf16be( & $str, $bom = false)
	{
		$out = $bom ? "\xFE\xFF" : '';
		if(function_exists('mb_convert_encoding'))
		{
			return $out.mb_convert_encoding($str,'UTF-16BE','UTF-8');
		}

		$uni = $this->utf8_to_unicode($str);
		foreach($uni as $cp)
		{
			$out .= pack('n',$cp);
		}
		return $out;
	}

	protected function utf16be_to_utf8( & $str)
	{
		$uni = unpack('n*',$str);
		return $this->unicode_to_utf8($uni);
	}
}
// End Crypto_JSON