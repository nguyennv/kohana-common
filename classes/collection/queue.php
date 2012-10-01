<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Collection_Queue implements a queue.
 *
 * The typical queue operations are implemented, which include
 * {@link enqueue()}, {@link dequeue()} and {@link peek()}. In addition,
 * {@link contains()} can be used to check if an item is contained
 * in the queue. To obtain the number of the items in the queue,
 * check the {@link getCount Count} property.
 *
 * Items in the queue may be traversed using foreach as follows,
 * <pre>
 * foreach($queue as $item) ...
 * </pre>
 *
 * @property Iterator $iterator An iterator for traversing the items in the queue.
 * @property integer $count The number of items in the queue.
 */
class Collection_Queue implements IteratorAggregate,Countable{
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
	 * Initializes the queue with an array or an iterable object.
	 * @param array the intial data. Default is NULL, meaning no initialization.
	 * @throws Kohana_Exception If data is not NULL and neither an array nor an iterator.
	 */
	public function __construct($data = NULL)
	{
		if($data !== NULL)
			$this->copy_from($data);
	}

	/**
	 * @return array the list of items in queue
	 */
	public function to_array()
	{
		return $this->_data;
	}

	/**
	 * Copies iterable data into the queue.
	 * Note, existing data in the list will be cleared first.
	 * @param mixed the data to be copied from, must be an array or object implementing Traversable
	 * @throws Kohana_Exception If data is neither an array nor a Traversable.
	 */
	public function copy_from($data)
	{
		if(is_array($data) || ($data instanceof Traversable))
		{
			$this->clear();
			foreach($data as $item)
			{
				$this->_data[] = $item;
				++$this->_count;
			}
		}
		elseif($data !== NULL)
		{
			throw new Kohana_Exception('Queue data must be an array or an object implementing Traversable.');
		}
		return $this;
	}

	/**
	 * Removes all items in the queue.
	 */
	public function clear()
	{
		$this->_count = 0;
		$this->_data = array();
		return $this;
	}

	/**
	 * @param mixed the item
	 * @return boolean whether the queue contains the item
	 */
	public function contains($item)
	{
		return array_search($item, $this->_data, TRUE)!==FALSE;
	}

	/**
	 * Returns the item at the top of the queue.
	 * @return mixed item at the top of the queue or NULL
	 */
	public function peek()
	{
		if($this->_count === 0){
			return NULL;
		}else
			return $this->_data[$this->_count-1];
	}

	/**
	 * Removes and returns the object at the beginning of the queue.
	 * @return mixed the item at the beginning of the queue
	 * @throws Kohana_Exception if the queue is empty
	 */
	public function dequeue()
	{
		if($this->_count === 0)
		{
			throw new Kohana_Exception('The queue is empty.');
		}
		else
		{
			--$this->_count;
			return array_shift($this->_data);
		}
	}

	/**
	 * Adds an object to the end of the queue.
	 * @param mixed the item to be appended into the queue
	 */
	public function enqueue($item)
	{
		++$this->_count;
		array_push($this->_data, $item);
		return $this;
	}

	/**
	 * Returns an iterator for traversing the items in the queue.
	 * This method is required by the interface IteratorAggregate.
	 * @return Collection_Iterator_Queue an iterator for traversing the items in the queue.
	 */
	public function getIterator()
	{
		return new Collection_Iterator_Queue($this->_data);
	}

	/**
	 * Returns the number of items in the queue.
	 * This method is required by Countable interface.
	 * @return integer number of items in the queue.
	 */
	public function count()
	{
		return $this->_count;
	}
}
// End Collection_Queue