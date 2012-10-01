<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Collection_Map implements a collection that takes key-value pairs.
 *
 * You can access, add or remove an item with a key by using
 * {@link item_at}, {@link add}, and {@link remove}.
 * To get the number of the items in the map, use {@link count}.
 * Collection_Map can also be used like a regular array as follows,
 * <pre>
 * $map[$key]=$value; // add a key-value pair
 * unset($map[$key]); // remove the value with the specified key
 * if(isset($map[$key])) // if the map contains the key
 * foreach($map as $key=>$value) // traverse the items in the map
 * $n = count($map);  // returns the number of items in the map
 * </pre>
 *
 * @property boolean $read_only Whether this map is read-only or not. Defaults to false.
 * @property Collection_Map_Iterator $iterator An iterator for traversing the items in the list.
 * @property integer $count The number of items in the map.
 * @property array $keys The key list.
 */
class Collection_Map implements IteratorAggregate,ArrayAccess,Countable{
	/**
	 * @var array internal data storage
	 */
	private $_data = array();
	/**
	 * @var boolean whether this list is read-only
	 */
	private $_read_only = FALSE;

	/**
	 * Constructor.
	 * Initializes the list with an array or an iterable object.
	 * @param array the intial data. Default is NULL, meaning no initialization.
	 * @param boolean whether the list is read-only
	 * @throws Kohana_Exception If data is not NULL and neither an array nor an iterator.
	 */
	public function __construct($data = NULL, $read_only = FALSE)
	{
		if($data !== NULL)
			$this->copy_from($data);
		$this->read_only($read_only);
	}

	/**
	 * @return boolean whether this map is read-only or not. Defaults to FALSE.
	 */
	public function read_only($value = NULL)
	{
		if(!empty($value) AND is_bool($value))
		{
			$this->_read_only = (bool) $value;
		}
		return $this->_read_only;
	}

	/**
	 * Returns an iterator for traversing the items in the list.
	 * This method is required by the interface IteratorAggregate.
	 * @return Collection_Iterator_Map an iterator for traversing the items in the list.
	 */
	public function getIterator()
	{
		return new Collection_Iterator_Map($this->_data);
	}

	/**
	 * Returns the number of items in the map.
	 * This method is required by Countable interface.
	 * @return integer number of items in the map.
	 */
	public function count()
	{
		return count($this->_data);
	}

	/**
	 * @return array the key list
	 */
	public function keys()
	{
		return array_keys($this->_data);
	}

	/**
	 * Returns the item with the specified key.
	 * This method is exactly the same as {@link offsetGet}.
	 * @param mixed the key
	 * @return mixed the element at the offset, NULL if no element is found at the offset
	 */
	public function item_at($key)
	{
		if(isset($this->_data[$key]))
			return $this->_data[$key];
		else
			return NULL;
	}

	/**
	 * Adds an item into the map.
	 * Note, if the specified key already exists, the old value will be overwritten.
	 * @param mixed key
	 * @param mixed value
	 * @throws Kohana_Exception if the map is read-only
	 */
	public function add($key, $value)
	{
		if(!$this->_read_only)
		{
			if($key === NULL)
				$this->_data[] = $value;
			else
				$this->_data[$key] = $value;
		}
		else
		{
			throw new Kohana_Exception('The map is read only.');
		}
		return $this;
	}

	/**
	 * Removes an item from the map by its key.
	 * @param mixed the key of the item to be removed
	 * @return mixed the removed value, NULL if no such key exists.
	 * @throws Kohana_Exception if the map is read-only
	 */
	public function remove($key)
	{
		if(!$this->_read_only)
		{
			if(isset($this->_data[$key]))
			{
				$value = $this->_data[$key];
				unset($this->_data[$key]);
				return $value;
			}
			else
			{
				// it is possible the value is NULL, which is not detected by isset
				unset($this->_data[$key]);
				return NULL;
			}
		}
		else
		{
			throw new Kohana_Exception('The map is read only.');
		}
		return $this;
	}

	/**
	 * Removes all items in the map.
	 */
	public function clear()
	{
		foreach(array_keys($this->_data) as $key)
			$this->remove($key);
		return $this;
	}

	/**
	 * @param mixed the key
	 * @return boolean whether the map contains an item with the specified key
	 */
	public function contains($key)
	{
		return isset($this->_data[$key]) OR array_key_exists($key,$this->_data);
	}

	/**
	 * @return array the list of items in array
	 */
	public function to_array()
	{
		return $this->_data;
	}

	/**
	 * Copies iterable data into the map.
	 * Note, existing data in the map will be cleared first.
	 * @param mixed the data to be copied from, must be an array or object implementing Traversable
	 * @throws Kohana_Exception If data is neither an array nor an iterator.
	 */
	public function copy_from($data)
	{
		if(is_array($data) OR $data instanceof Traversable)
		{
			if($this->count() > 0)
				$this->clear();
			if($data instanceof Collection_Map)
				$data = $data->_data;
			foreach($data as $key => $value)
				$this->add($key, $value);
		}
		elseif($data !== NULL)
		{
			throw new Kohana_Exception('Map data must be an array or an object implementing Traversable.');
		}
		return $this;
	}

	/**
	 * Merges iterable data into the map.
	 *
	 * Existing elements in the map will be overwritten if their keys are the same as those in the source.
	 * If the merge is recursive, the following algorithm is performed:
	 * <ul>
	 * <li>the map data is saved as $a, and the source data is saved as $b;</li>
	 * <li>if $a and $b both have an array indxed at the same string key, the arrays will be merged using this algorithm;</li>
	 * <li>any integer-indexed elements in $b will be appended to $a and reindxed accordingly;</li>
	 * <li>any string-indexed elements in $b will overwrite elements in $a with the same index;</li>
	 * </ul>
	 *
	 * @param mixed the data to be merged with, must be an array or object implementing Traversable
	 * @param boolean whether the merging should be recursive.
	 *
	 * @throws Kohana_Exception If data is neither an array nor an iterator.
	 */
	public function merge_with($data, $recursive = TRUE)
	{
		if(is_array($data) OR $data instanceof Traversable)
		{
			if($data instanceof Collection_Map)
				$data = $data->_data;
			if($recursive)
			{
				if($data instanceof Traversable)
				{
					$d = array();
					foreach($data as $key => $value)
						$d[$key] = $value;
					$this->_data = self::merge_array($this->_data, $d);
				}
				else
					$this->_data = self::merge_array($this->_data, $data);
			}
			else
			{
				foreach($data as $key => $value)
					$this->add($key, $value);
			}
		}
		elseif($data !== NULL)
		{
			throw new Kohana_Exception('Map data must be an array or an object implementing Traversable.');
		}
		return $this;
	}

	/**
	 * Merges two arrays into one recursively.
	 * @param array array to be merged to
	 * @param array array to be merged from
	 * @return array the merged array (the original arrays are not changed.)
	 * @see merge_with
	 */
	public static function merge_array($a, $b)
	{
		foreach($b as $k=>$v)
		{
			if(is_integer($k))
				$a[] = $v;
			elseif(is_array($v) AND isset($a[$k]) AND is_array($a[$k]))
				$a[$k] = self::merge_array($a[$k], $v);
			else
				$a[$k] = $v;
		}
		return $a;
	}

	/**
	 * Returns whether there is an element at the specified offset.
	 * This method is required by the interface ArrayAccess.
	 * @param mixed the offset to check on
	 * @return boolean
	 */
	public function offsetExists($offset)
	{
		return $this->contains($offset);
	}

	/**
	 * Returns the element at the specified offset.
	 * This method is required by the interface ArrayAccess.
	 * @param integer the offset to retrieve element.
	 * @return mixed the element at the offset, NULL if no element is found at the offset
	 */
	public function offsetGet($offset)
	{
		return $this->item_at($offset);
	}

	/**
	 * Sets the element at the specified offset.
	 * This method is required by the interface ArrayAccess.
	 * @param integer the offset to set element
	 * @param mixed the element value
	 */
	public function offsetSet($offset, $item)
	{
		$this->add($offset, $item);
		return $this;
	}

	/**
	 * Unsets the element at the specified offset.
	 * This method is required by the interface ArrayAccess.
	 * @param mixed the offset to unset element
	 */
	public function offsetUnset($offset)
	{
		$this->remove($offset);
		return $this;
	}
}
// End Collection_Map