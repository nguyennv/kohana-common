<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Collection_Typed_Map represents a map whose items are of the certain type.
 *
 * Collection_Typed_Map extends {@link Collection_Map} by making sure that the elements to be
 * added to the list is of certain class type.
 */
class Collection_Typed_Map extends Collection_Map{
	private $_type;

	/**
	 * Constructor.
	 * @param string class type
	 */
	public function __construct($type)
	{
		$this->_type = $type;
	}

	/**
	 * Adds an item into the map.
	 * This method overrides the parent implementation by
	 * checking the item to be inserted is of certain type.
	 * @param integer $index the specified position.
	 * @param mixed $item new item
	 * @throws Kohana_Exception If the index specified exceeds the bound,
	 * the map is read-only or the element is not of the expected type.
	 */
	public function add($index, $item)
	{
		if($item instanceof $this->_type)
		{
			parent::add($index, $item);
		}
		else
		{
			throw new Kohana_Exception('Collection_Typed_Map<:type> can only hold objects of :type class.', array(':type' => $this->_type));
		}
	}
}
// End Collection_Typed_Map