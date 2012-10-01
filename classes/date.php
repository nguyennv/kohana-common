<?php defined('SYSPATH') or die('No direct script access.');

class Date extends Kohana_Date{

	public static function invert_date_format($date_string)
	{
		if(strlen($date_string) == 0 OR is_null($date_string))
		{
			return '';
		}
		if(strpos($date_string,"-") <= 0 AND strpos($date_string,"/") <= 0)
		{
			return '';
		}
		if(strpos($date_string,"-") > 0)
		{ 
			$del="-";
		}
		if(strpos($date_string,"/") > 0)
		{
			$del="/";
		}
		$date_array=explode($del,$date_string);
		if(count($date_array) != 3)
			return null;	
		else
			return $date_array[2]."-".$date_array[1]."-".$date_array[0];
	}

	public static function to_us_date($date_string)
	{
		if(strlen($date_string) == 0 OR is_null($date_string))
		{
			return '';
		}
		if(strpos($date_string,"-") <= 0 AND strpos($date_string,"/") <= 0)
		{
			return '';
		}
		if(strpos($date_string,"-") > 0)
		{ 
			$del="-";
		}
		if(strpos($date_string,"/") > 0)
		{
			$del="/";
		}
		$date_array = explode($del,$date_string);
		if(count($date_array) != 3)
		{
			return null;	
		}
		else
		{
			return $date_array[1]."-".$date_array[0]."-".$date_array[2];
		}
	}

	public static function get_day_of_week($year, $month, $day)
	{
		/*
		Pope Gregory removed 10 days - October 5 to October 14 - from the year 1582 and
		proclaimed that from that time onwards 3 days would be dropped from the calendar
		every 400 years.

		Thursday, October 4, 1582 (Julian) was followed immediately by Friday, October 15, 1582 (Gregorian).
		*/
		if ($year <= 1582)
		{
			if ($year < 1582 OR
				($year == 1582 AND ($month < 10 OR ($month == 10 AND $day < 15))))
			{
				$greg_correction = 3;
			}
			else
			{
				$greg_correction = 0;
			}
		}
		else
		{
			$greg_correction = 0;
		}

		if($month > 2)
		    $month -= 2;
		else
		{
		    $month += 10;
		    $year--;
		}

		$day =  floor((13 * $month - 1) / 5) +
		        $day + ($year % 100) +
		        floor(($year % 100) / 4) +
		        floor(($year / 100) / 4) - 2 *
		        floor($year / 100) + 77 + $greg_correction;

		return $day - 7 * floor($day / 7);
	}

	public static function is_leap_year($year)
	{
		$year = self::digit_check($year);
		if ($year % 4 != 0)
			return FALSE;

		if ($year % 400 == 0)
			return TRUE;
		// if gregorian calendar (>1582), century not-divisible by 400 is not leap
		elseif ($year > 1582 AND $year % 100 == 0 )
			return FALSE;
		return TRUE;
	}

	public static function get_4digit_year($y)
	{
		return self::digit_check($y);
	}

	public static function get_gmt_diff()
	{
		static $tz;
		if (isset($tz)) return $tz;

		$tz = mktime(0, 0, 0, 1, 2, 1970) - gmmktime(0, 0, 0, 1, 2, 1970);
		return $tz;
	}

	public static function get_date($d = FALSE, $fast = FALSE, $gmt = FALSE)
	{
		if($d === FALSE)
			$d = time();
		if($gmt)
		{
			$tz = date_default_timezone_get();
			date_default_timezone_set('GMT');
			$result = getdate($d);
			date_default_timezone_set($tz);
		}
		else
		{
			$result = getdate($d);
		}
		return $result;
	}

	public static function is_valid_date($y, $m, $d)
	{
		return checkdate($m, $d, $y);
	}

	public static function is_valid_time($h, $m, $s, $hs24 = TRUE)
	{
		if($hs24 AND ($h < 0 OR $h > 23) OR !$hs24 AND ($h < 1 OR $h > 12)) return FALSE;
		if($m > 59 OR $m < 0) return FALSE;
		if($s > 59 OR $s < 0) return FALSE;
		return TRUE;
	}

	public static function format_date($fmt, $d = FALSE, $is_gmt = FALSE)
	{
		if ($d === FALSE)
			return ($is_gmt)? @gmdate($fmt): @date($fmt);

		// check if number in 32-bit signed range
		if ((abs($d) <= 0x7FFFFFFF))
		{
			// if windows, must be +ve integer
			if ($d >= 0)
				return ($is_gmt)? @gmdate($fmt,$d): @date($fmt,$d);
		}

		$_day_power = 86400;

		$arr = self::get_date($d, TRUE, $is_gmt);

		$year = $arr['year'];
		$month = $arr['mon'];
		$day = $arr['mday'];
		$hour = $arr['hours'];
		$min = $arr['minutes'];
		$secs = $arr['seconds'];

		$max = strlen($fmt);
		$dates = '';

		/*
			at this point, we have the following integer vars to manipulate:
			$year, $month, $day, $hour, $min, $secs
		*/
		for ($i=0; $i < $max; $i++)
		{
			switch($fmt[$i])
			{
				case 'T': $dates .= date('T');break;
				// YEAR
				case 'L': $dates .= $arr['leap'] ? '1' : '0'; break;
				case 'r': // Thu, 21 Dec 2000 16:01:07 +0200

					// 4.3.11 uses '04 Jun 2004'
					// 4.3.8 uses  ' 4 Jun 2004'
					$dates .= gmdate('D', $_day_power * (3 + self::get_day_of_week($year, $month, $day))).', '
						.($day < 10 ? '0'.$day : $day).' '.date('M', mktime(0, 0, 0, $month, 2, 1971)).' '.$year.' ';

					if ($hour < 10) $dates .= '0'.$hour; else $dates .= $hour;

					if ($min < 10) $dates .= ':0'.$min; else $dates .= ':'.$min;

					if ($secs < 10) $dates .= ':0'.$secs; else $dates .= ':'.$secs;

					$gmt = self::get_gmt_diff();
					$dates .= sprintf(' %s%04d', ($gmt<=0) ? '+' : '-', abs($gmt)/36);
					break;

				case 'Y': $dates .= $year; break;
				case 'y': $dates .= substr($year, strlen($year) - 2, 2); break;
				// MONTH
				case 'm': if ($month<10) $dates .= '0'.$month; else $dates .= $month; break;
				case 'Q': $dates .= ($month + 3) >> 2; break;
				case 'n': $dates .= $month; break;
				case 'M': $dates .= date('M', mktime(0, 0, 0, $month, 2, 1971)); break;
				case 'F': $dates .= date('F', mktime(0, 0, 0, $month, 2, 1971)); break;
				// DAY
				case 't': $dates .= $arr['ndays']; break;
				case 'z': $dates .= $arr['yday']; break;
				case 'w': $dates .= self::get_day_of_week($year, $month, $day); break;
				case 'l': $dates .= gmdate('l', $_day_power * (3 + self::get_day_of_week($year, $month, $day))); break;
				case 'D': $dates .= gmdate('D', $_day_power * (3 + self::get_day_of_week($year, $month, $day))); break;
				case 'j': $dates .= $day; break;
				case 'd': if ($day < 10) $dates .= '0'.$day; else $dates .= $day; break;
				case 'S':
					$d10 = $day % 10;
					if ($d10 == 1) $dates .= 'st';
					elseif ($d10 == 2 AND $day != 12) $dates .= 'nd';
					elseif ($d10 == 3) $dates .= 'rd';
					else $dates .= 'th';
					break;

				// HOUR
				case 'Z':
					$dates .= ($is_gmt) ? 0 : -self::get_gmt_diff(); break;
				case 'O':
					$gmt = ($is_gmt) ? 0 : self::get_gmt_diff();

					$dates .= sprintf('%s%04d',($gmt <= 0) ? '+' : '-', abs($gmt)/36);
					break;

				case 'H':
					if ($hour < 10) $dates .= '0'.$hour;
					else $dates .= $hour;
					break;
				case 'h':
					if ($hour > 12) $hh = $hour - 12;
					else {
						if ($hour == 0) $hh = '12';
						else $hh = $hour;
					}

					if ($hh < 10) $dates .= '0'.$hh;
					else $dates .= $hh;
					break;

				case 'G':
					$dates .= $hour;
					break;

				case 'g':
					if ($hour > 12) $hh = $hour - 12;
					else {
						if ($hour == 0) $hh = '12';
						else $hh = $hour;
					}
					$dates .= $hh;
					break;
				// MINUTES
				case 'i': if ($min < 10) $dates .= '0'.$min; else $dates .= $min; break;
				// SECONDS
				case 'U': $dates .= $d; break;
				case 's': if ($secs < 10) $dates .= '0'.$secs; else $dates .= $secs; break;
				// AM/PM
				// Note 00:00 to 11:59 is AM, while 12:00 to 23:59 is PM
				case 'a':
					if ($hour >= 12) $dates .= 'pm';
					else $dates .= 'am';
					break;
				case 'A':
					if ($hour >= 12) $dates .= 'PM';
					else $dates .= 'AM';
					break;
				default:
					$dates .= $fmt[$i]; break;
				// ESCAPE
				case "\\":
					$i++;
					if ($i < $max) $dates .= $fmt[$i];
					break;
			}
		}
		return $dates;
	}

	public static function get_timestamp($hr, $min, $sec, $mon = FALSE, $day = FALSE, $year = FALSE, $is_gmt = FALSE)
	{
		if ($mon === FALSE)
			return $is_gmt? @gmmktime($hr, $min, $sec): @mktime($hr, $min, $sec);
		return $is_gmt ? @gmmktime($hr, $min, $sec, $mon, $day, $year) : @mktime($hr, $min, $sec, $mon, $day, $year);
	}

	public static function parse($value, $pattern='yyyy/MM/dd', $defaults=array())
	{
		$tokens = self::tokenize($pattern);
		$i = 0;
		$n = strlen($value);
		foreach($tokens as $token)
		{
			switch($token)
			{
				case 'yyyy':
				{
					if(($year = self::parse_integer($value, $i, 4, 4)) === FALSE)
						return FALSE;
					$i+=4;
					break;
				}
				case 'yy':
				{
					if(($year = self::parse_integer($value, $i, 1, 2)) === FALSE)
						return FALSE;
					$i += strlen($year);
					break;
				}
				case 'MM':
				{
					if(($month = self::parse_integer($value, $i ,2 ,2)) === FALSE)
						return FALSE;
					$i += 2;
					break;
				}
				case 'M':
				{
					if(($month = self::parse_integer($value, $i, 1, 2)) === FALSE)
						return FALSE;
					$i += strlen($month);
					break;
				}
				case 'dd':
				{
					if(($day = self::parse_integer($value, $i, 2, 2)) === FALSE)
						return FALSE;
					$i += 2;
					break;
				}
				case 'd':
				{
					if(($day = self::parse_integer($value, $i, 1, 2)) === FALSE)
						return FALSE;
					$i += strlen($day);
					break;
				}
				case 'h':
				case 'H':
				{
					if(($hour = self::parse_integer($value, $i, 1, 2)) === FALSE)
						return FALSE;
					$i += strlen($hour);
					break;
				}
				case 'hh':
				case 'HH':
				{
					if(($hour = self::parse_integer($value,$i,2,2)) === FALSE)
						return FALSE;
					$i += 2;
					break;
				}
				case 'm':
				{
					if(($minute = self::parse_integer($value, $i, 1, 2)) === FALSE)
						return FALSE;
					$i += strlen($minute);
					break;
				}
				case 'mm':
				{
					if(($minute = self::parse_integer($value, $i, 2, 2)) === FALSE)
						return FALSE;
					$i += 2;
					break;
				}
				case 's':
				{
					if(($second = self::parse_integer($value, $i, 1, 2)) === FALSE)
						return FALSE;
					$i += strlen($second);
					break;
				}
				case 'ss':
				{
					if(($second = self::parse_integer($value, $i, 2, 2)) === FALSE)
						return FALSE;
					$i += 2;
					break;
				}
				case 'a':
				{
				    if(($ampm = self::parse_am_pm($value, $i)) === FALSE)
				        return FALSE;
				    if(isset($hour))
				    {
				    	if($hour == 12 AND $ampm === 'am')
				    		$hour=0;
				    	elseif($hour < 12 AND $ampm === 'pm')
				    		$hour += 12;
				    }
					$i+=2;
					break;
				}
				default:
				{
					$tn = strlen($token);
					if($i >= $n || substr($value, $i, $tn) !== $token)
						return FALSE;
					$i += $tn;
					break;
				}
			}
		}
		if($i < $n)
			return FALSE;

		if(!isset($year))
			$year = isset($defaults['year']) ? $defaults['year'] : date('Y');
		if(!isset($month))
			$month = isset($defaults['month']) ? $defaults['month'] : date('n');
		if(!isset($day))
			$day = isset($defaults['day']) ? $defaults['day'] : date('j');

		if(strlen($year)===2)
		{
			if($year >= 70)
				$year += 1900;
			else
				$year += 2000;
		}
		$year = (int)$year;
		$month = (int)$month;
		$day = (int)$day;

		if(
			!isset($hour) AND !isset($minute) AND !isset($second)
			&& !isset($defaults['hour']) AND !isset($defaults['minute']) AND !isset($defaults['second'])
		)
			$hour = $minute = $second = 0;
		else
		{
			if(!isset($hour))
				$hour = isset($defaults['hour']) ? $defaults['hour'] : date('H');
			if(!isset($minute))
				$minute = isset($defaults['minute']) ? $defaults['minute'] : date('i');
			if(!isset($second))
				$second = isset($defaults['second']) ? $defaults['second'] : date('s');
			$hour = (int)$hour;
			$minute =( int)$minute;
			$second = (int)$second;
		}

		if(self::is_valid_date($year, $month, $day) AND self::is_valid_time($hour, $minute, $second))
			return self::get_timestamp($hour, $minute, $second, $month, $day, $year);
		else
			return FALSE;
	}

	private static function tokenize($pattern)
	{
		if(!($n = strlen($pattern)))
			return array();
		$tokens = array();
		for($c0 = $pattern[0], $start=0, $i=1; $i<$n; ++$i)
		{
			if(($c = $pattern[$i]) !== $c0)
			{
				$tokens[] = substr($pattern, $start, $i - $start);
				$c0 = $c;
				$start = $i;
			}
		}
		$tokens[] = substr($pattern, $start, $n-$start);
		return $tokens;
	}

	protected static function parse_integer($value, $offset, $min_length, $max_length)
	{
		for($len = $max_length; $len >= $min_length; --$len)
		{
			$v = substr($value, $offset, $len);
			if(ctype_digit($v) AND strlen($v) >= $min_length)
				return $v;
		}
		return FALSE;
	}

	protected static function parse_am_pm($value, $offset)
	{
		$v = strtolower(substr($value, $offset, 2));
		return $v==='am' || $v==='pm' ? $v : FALSE;
	}
	
	protected static function digit_check($y)
	{
		if ($y < 100){
			$yr = (integer) date("Y");
			$century = (integer) ($yr /100);

			if ($yr%100 > 50) {
				$c1 = $century + 1;
				$c0 = $century;
			} else {
				$c1 = $century;
				$c0 = $century - 1;
			}
			$c1 *= 100;
			// if 2-digit year is less than 30 years in future, set it to this century
			// otherwise if more than 30 years in future, then we set 2-digit year to the prev century.
			if (($y + $c1) < $yr+30) $y = $y + $c1;
			else $y = $y + $c0*100;
		}
		return $y;
	}
}
// End Date