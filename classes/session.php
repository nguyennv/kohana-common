<?php defined('SYSPATH') or die('No direct script access.');

abstract class Session extends Kohana_Session implements IteratorAggregate,ArrayAccess,Countable{

	public function session_id($value = NULL)
	{
		return (!empty($value)) ? session_id((string) $value) : session_id();
	}

	public function session_name($value = NULL)
	{
		return (!empty($value) AND ctype_alnum($value)) ? session_name((string) $value) : session_name();
	}

	public function save_path($value = NULL)
	{
		return (!empty($value) AND is_dir($value)) ? session_save_path((string) $value) : session_save_path();
	}

	public function cookie_mode($value = NULL)
	{
		if(!empty($value))
		{
			if($value === 'none')
				ini_set('session.use_cookies','0');
			elseif($value === 'allow')
			{
				ini_set('session.use_cookies','1');
				ini_set('session.use_only_cookies','0');
			}
			elseif($value === 'only')
			{
				ini_set('session.use_cookies','1');
				ini_set('session.use_only_cookies','1');
			}
			else
				throw new Kohana_Exception('Session.cookie_mode can only be "none", "allow" or "only".');
		}
		if(ini_get('session.use_cookies') === '0')
			return 'none';
		elseif(ini_get('session.use_only_cookies') === '0')
			return 'allow';
		else
			return 'only';
	}

	public function gc_probability($value = NULL)
	{
		if(!empty($value))
		{
			$value = (int) $value;
			if($value >= 0 AND $value <= 100)
			{
				ini_set('session.gc_probability',$value);
				ini_set('session.gc_divisor','100');
			}
			else
				throw new Kohana_Exception('Session.gc_probability must be an integer between 0 and 100.');
		}
		return (int) ini_get('session.gc_probability');
	}

	public function use_trans_sid($value = NULL)
	{
		if(!empty($value))
		{
			ini_set('session.use_trans_sid',(bool) ($value) ? '1' : '0');
		}
		return ini_get('session.use_trans_sid') === '1';
	}

	public function gc_maxlifetime($value = NULL)
	{
		if(!empty($value))
		{
			ini_set('session.gc_maxlifetime',$value);
		}
		return (int) ini_get('session.gc_maxlifetime');
	}

	public function getIterator()
	{
		return new Session_Iterator;
	}

	public function count()
	{
		return count($this->_data);
	}

	public function keys(){
		return array_keys($this->_data);
	}

	public function item_at($key)
	{
		return isset($this->_data[$key]) ? $this->_data[$key] : NULL;
	}

	public function add($key,$value)
	{
		$this->_data[$key] = $value;
	}

	public function remove($key)
	{
		if(isset($this->_data[$key]))
		{
			$value = $this->_data[$key];
			unset($this->_data[$key]);
			return $value;
		}
		else
			return NULL;
	}

	public function clear()
	{
		foreach(array_keys($this->_data) as $key)
			unset($this->_data[$key]);
	}

	public function contains($key)
	{
		return isset($this->_data[$key]);
	}

	public function & to_array()
	{
		return $this->_data;
	}

	public function offsetExists($offset){
		return isset($this->_data[$offset]);
	}

	public function offsetGet($offset)
	{
		return isset($this->_data[$offset]) ? $this->_data[$offset] : NULL;
	}

	public function offsetSet($offset,$item)
	{
		$this->_data[$offset] = $item;
	}

	public function offsetUnset($offset)
	{
		unset($this->_data[$offset]);
	}
}
// End Session