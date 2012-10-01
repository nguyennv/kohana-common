<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Collection_Stack implements a stack.
 *
 * The typical stack operations are implemented, which include
 * {@link push()}, {@link pop()} and {@link peek()}. In addition,
 * {@link contains()} can be used to check if an item is contained
 * in the stack. To obtain the number of the items in the stack,
 * check the {@link getCount Count} property.
 *
 * Items in the stack may be traversed using foreach as follows,
 * <pre>
 * foreach($stack as $item) ...
 * </pre>
 *
 * @property Iterator $iterator An iterator for traversing the items in the stack.
 * @property integer $count The number of items in the stack.
 */
class Collection_Stack implements IteratorAggregate,Countable{
	/**
	 * internal data storage
	 * @var array
	 */
	private $_data = array();
	/**
	 * number of items
	 * @var integer
	 */
	private $_count = 0;

	/**
	 * Constructor.
	 * Initializes the stack with an array or an iterable object.
	 * @param array the initial data. Default is NULL, meaning no initialization.
	 * @throws Kohana_Exception If data is not NULL and neither an array nor an iterator.
	 */
	public function __construct($data = NULL)
	{
		if($data !== NULL)
			$this->copy_from($data);
	}

	/**
	 * @return array the list of items in stack
	 */
	public function to_array()
	{
		return $this->_data;
	}

	/**
	 * Copies iterable data into the stack.
	 * Note, existing data in the list will be cleared first.
	 * @param mixed the data to be copied from, must be an array or object implementing Traversable
	 * @throws Kohana_Exception If data is neither an array nor a Traversable.
	 */
	public function copy_from($data)
	{
		if(is_array($data) || ($data instanceof Traversable))
		{
			$this->clear();
			foreach($data as $item){
				$this->_data[] = $item;
				++$this->_count;
			}
		}
		elseif($data !== NULL)
		{
			throw new Kohana_Exception('Stack data must be an array or an object implementing Traversable.');
		}
		return $this;
	}

	/**
	 * Removes all items in the stack.
	 */
	public function clear()
	{
		$this->_count = 0;
		$this->_data = array();
		return $this;
	}

	/**
	 * @param mixed the item
	 * @return boolean whether the stack contains the item
	 */
	public function contains($item)
	{
		return array_search($item, $this->_data, TRUE) !== FALSE;
	}

	/**
	 * Returns the item at the top of the stack.
	 * Unlike {@link pop()}, this method does not remove the item from the stack.
	 * @return mixed item at the top of the stack or NULL
	 */
	public function peek()
	{
		if($this->_count)
			return $this->_data[$this->_count-1];
		else
			return NULL;
	}

	/**
	 * Pops up the item at the top of the stack.
	 * @return mixed the item at the top of the stack
	 * @throws Collection_Exception if the stack is empty
	 */
	public function pop()
	{
		if($this->_count)
		{
			--$this->_count;
			return array_pop($this->_data);
		}
		else
		{
			throw new Exception('The stack is empty.');
		}
	}

	/**
	 * Pushes an item into the stack.
	 * @param mixed the item to be pushed into the stack
	 */
	public function push($item)
	{
		++$this->_count;
		array_push($this->_data, $item);
		return $this;
	}

	/**
	 * Returns an iterator for traversing the items in the stack.
	 * This method is required by the interface IteratorAggregate.
	 * @return Collection_Iterator_Stack an iterator for traversing the items in the stack.
	 */
	public function getIterator()
	{
		return new Collection_Iterator_Stack($this->_data);
	}

	/**
	 * Returns the number of items in the stack.
	 * This method is required by Countable interface.
	 * @return integer number of items in the stack.
	 */
	public function count()
	{
		return $this->_count;
	}
}
// End Collection_Stack