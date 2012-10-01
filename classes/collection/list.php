<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Collection_List implements an integer-indexed collection class.
 *
 * You can access, append, insert, remove an item by using
 * {@link item_at}, {@link add}, {@link insert_at}, {@link remove}, and {@link remove_at}.
 * To get the number of the items in the list, use {@link count}.
 * Collection_List can also be used like a regular array as follows,
 * <pre>
 * $list[]=$item;  // append at the end
 * $list[$index]=$item; // $index must be between 0 and $list->Count
 * unset($list[$index]); // remove the item at $index
 * if(isset($list[$index])) // if the list has an item at $index
 * foreach($list as $index=>$item) // traverse each item in the list
 * $n=count($list); // returns the number of items in the list
 * </pre>
 *
 * To extend Collection_List by doing additional operations with each addition or removal
 * operation (e.g. performing type check), override {@link insert_at()}, and {@link remove_at()}.
 *
 * @property boolean $read_only Whether this list is read-only or not. Defaults to false.
 * @property Iterator $iterator An iterator for traversing the items in the list.
 * @property integer $count The number of items in the list.
 */
class Collection_List implements IteratorAggregate,ArrayAccess,Countable{
	/**
	 * @var array internal data storage
	 */
	private $_data = array();
	/**
	 * @var integer number of items
	 */
	private $_count = 0;
	/**
	 * @var boolean whether this list is read-only
	 */
	private $_read_only = FALSE;

	/**
	 * Constructor.
	 * Initializes the list with an array or an iterable object.
	 * @param array the initial data. Default is NULL, meaning no initialization.
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
	 * @return boolean whether this list is read-only or not. Defaults to FALSE.
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
	 * @return Collection_Iterator_List an iterator for traversing the items in the list.
	 */
	public function getIterator()
	{
		return new Collection_Iterator_List($this->_data);
	}

	/**
	 * Returns the number of items in the list.
	 * This method is required by Countable interface.
	 * @return integer number of items in the list.
	 */
	public function count()
	{
		return $this->_count;
	}

	/**
	 * Returns the item at the specified offset.
	 * This method is exactly the same as {@link offsetGet}.
	 * @param integer the index of the item
	 * @return mixed the item at the index
	 * @throws Kohana_Exception if the index is out of the range
	 */
	public function item_at($index)
	{
		if(isset($this->_data[$index]))
			return $this->_data[$index];
		elseif($index >= 0 AND $index < $this->_count) // in case the value is NULL
			return $this->_data[$index];
		else
		{
			throw new Kohana_Exception('List index ":index" is out of bound.', array(':index' => $index));
		}
	}

	/**
	 * Appends an item at the end of the list.
	 * @param mixed new item
	 * @return integer the zero-based index at which the item is added
	 */
	public function add($item)
	{
		$this->insert_at($this->_count, $item);
		return $this->_count-1;
	}

	/**
	 * Inserts an item at the specified position.
	 * Original item at the position and the next items
	 * will be moved one step towards the end.
	 * @param integer the specified position.
	 * @param mixed new item
	 * @throws Kohana_Exception If the index specified exceeds the bound or the list is read-only
	 */
	public function insert_at($index, $item)
	{
		if(!$this->_read_only)
		{
			if($index === $this->_count)
				$this->_data[$this->_count++] = $item;
			elseif($index >= 0 AND $index < $this->_count)
			{
				array_splice($this->_data, $index, 0, array($item));
				$this->_count++;
			}
			else
			{
				throw new Kohana_Exception('List index ":index" is out of bound.', array(':index' => $index));
			}
		}else
		{
			throw new Kohana_Exception('The list is read only.');
		}
	}

	/**
	 * Removes an item from the list.
	 * The list will first search for the item.
	 * The first item found will be removed from the list.
	 * @param mixed the item to be removed.
	 * @return integer the index at which the item is being removed
	 * @throws Kohana_Exception If the item does not exist
	 */
	public function remove($item)
	{
		if(($index = $this->index_of($item)) >= 0)
		{
			$this->remove_at($index);
			return $index;
		}
		else
			return FALSE;
	}

	/**
	 * Removes an item at the specified position.
	 * @param integer the index of the item to be removed.
	 * @return mixed the removed item.
	 * @throws Kohana_Exception If the index specified exceeds the bound or the list is read-only
	 */
	public function remove_at($index)
	{
		if(!$this->_read_only)
		{
			if($index >= 0 AND $index < $this->_count)
			{
				$this->_count--;
				if($index === $this->_count)
					return array_pop($this->_data);
				else
				{
					$item = $this->_data[$index];
					array_splice($this->_data, $index, 1);
					return $item;
				}
			}
			else
			{
				throw new Kohana_Exception('List index ":index" is out of bound.', array(':index' => $index));
			}
		}
		else
		{
			throw new Kohana_Exception('The list is read only.');
		}
	}

	/**
	 * Removes all items in the list.
	 */
	public function clear()
	{
		for($i = $this->_count-1; $i >= 0; --$i)
			$this->remove_at($i);
	}

	/**
	 * @param mixed the item
	 * @return boolean whether the list contains the item
	 */
	public function contains($item)
	{
		return $this->index_of($item) >= 0;
	}

	/**
	 * @param mixed the item
	 * @return integer the index of the item in the list (0 based), -1 if not found.
	 */
	public function index_of($item)
	{
		if(($index = array_search($item, $this->_data, TRUE)) !== FALSE)
			return $index;
		else
			return -1;
	}

	/**
	 * @return array the list of items in array
	 */
	public function to_array()
	{
		return $this->_data;
	}

	/**
	 * Copies iterable data into the list.
	 * Note, existing data in the list will be cleared first.
	 * @param mixed the data to be copied from, must be an array or object implementing Traversable
	 * @throws Kohana_Exception If data is neither an array nor a Traversable.
	 */
	public function copy_from($data)
	{
		if(is_array($data) OR ($data instanceof Traversable))
		{
			if($this->_count > 0)
				$this->clear();
			if($data instanceof Collection_List)
				$data = $data->_data;
			foreach($data as $item)
				$this->add($item);
		}
		elseif($data !== NULL)
		{
			throw new Kohana_Exception('List data must be an array or an object implementing Traversable.');
		}
		return $this;
	}

	/**
	 * Merges iterable data into the map.
	 * New data will be appended to the end of the existing data.
	 * @param mixed the data to be merged with, must be an array or object implementing Traversable
	 * @throws Kohana_Exception If data is neither an array nor an iterator.
	 */
	public function merge_with($data)
	{
		if(is_array($data) OR ($data instanceof Traversable))
		{
			if($data instanceof Collection_List)
				$data = $data->_data;
			foreach($data as $item)
				$this->add($item);
		}
		elseif($data!==NULL)
		{
			throw new Kohana_Exception('List data must be an array or an object implementing Traversable.');
		}
	}

	/**
	 * Returns whether there is an item at the specified offset.
	 * This method is required by the interface ArrayAccess.
	 * @param integer the offset to check on
	 * @return boolean
	 */
	public function offsetExists($offset)
	{
		return ($offset >= 0 AND $offset < $this->_count);
	}

	/**
	 * Returns the item at the specified offset.
	 * This method is required by the interface ArrayAccess.
	 * @param integer the offset to retrieve item.
	 * @return mixed the item at the offset
	 * @throws Kohana_Exception if the offset is invalid
	 */
	public function offsetGet($offset)
	{
		return $this->item_at($offset);
	}

	/**
	 * Sets the item at the specified offset.
	 * This method is required by the interface ArrayAccess.
	 * @param integer the offset to set item
	 * @param mixed the item value
	 */
	public function offsetSet($offset,$item)
	{
		if($offset === NULL OR $offset === $this->_count)
			$this->insert_at($this->_count, $item);
		else
		{
			$this->remove_at($offset);
			$this->insert_at($offset, $item);
		}
	}

	/**
	 * Unsets the item at the specified offset.
	 * This method is required by the interface ArrayAccess.
	 * @param integer the offset to unset item
	 */
	public function offsetUnset($offset)
	{
		$this->remove_at($offset);
	}
}
// End Collection_List