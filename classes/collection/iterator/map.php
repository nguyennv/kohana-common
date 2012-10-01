<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Collection_Iterator_Map implements an interator for {@link Collection_Map}.
 *
 * It allows Collection_Map to return a new iterator for traversing the items in the map.
 */
class Collection_Iterator_Map implements Iterator{
	/**
	 * @var array the data to be iterated through
	 */
	private $_data;
	/**
	 * @var array list of keys in the map
	 */
	private $_keys;
	/**
	 * @var mixed current key
	 */
	private $_key;

	/**
	 * Constructor.
	 * @param array the data to be iterated through
	 */
	public function __construct(&$data)
	{
		$this->_data = &$data;
		$this->_keys = array_keys($data);
	}

	/**
	 * Rewinds internal array pointer.
	 * This method is required by the interface Iterator.
	 */
	public function rewind()
	{
		$this->_key = reset($this->_keys);
		return $this;
	}

	/**
	 * Returns the key of the current array element.
	 * This method is required by the interface Iterator.
	 * @return mixed the key of the current array element
	 */
	public function key()
	{
		return $this->_key;
	}

	/**
	 * Returns the current array element.
	 * This method is required by the interface Iterator.
	 * @return mixed the current array element
	 */
	public function current()
	{
		return $this->_data[$this->_key];
	}

	/**
	 * Moves the internal pointer to the next array element.
	 * This method is required by the interface Iterator.
	 */
	public function next()
	{
		$this->_key = next($this->_keys);
		return $this;
	}

	/**
	 * Returns whether there is an element at current position.
	 * This method is required by the interface Iterator.
	 * @return boolean
	 */
	public function valid()
	{
		return $this->_key !== FALSE;
	}
}
// End Collection_Iterator_Map