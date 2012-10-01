<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Collection_Attribute implements a collection for storing attribute names and values.
 *
 * Besides all functionalities provided by {@link Collection_Map}, Collection_Attribute
 * allows you to get and set attribute values like getting and setting
 * properties. For example, the following usages are all valid for a
 * Collection_Attribute object:
 * <pre>
 * $collection->text='text'; // same as:  $collection->add('text','text');
 * echo $collection->text;   // same as:  echo $collection->itemAt('text');
 * </pre>
 *
 * The case sensitivity of attribute names can be toggled by setting the
 * {@link caseSensitive} property of the collection.
 */
class Collection_Attribute extends Collection_Map{
	/**
	 * @var boolean whether the keys are case-sensitive. Defaults to FALSE.
	 */
	public $case_sensitive = FALSE;

	/**
	 * Returns a property value or an event handler list by property or event name.
	 * This method overrides the parent implementation by returning
	 * a key value if the key exists in the collection.
	 * @param string $name the property name or the event name
	 * @return mixed the property value or the event handler list
	 * @throws Kohana_Exception if the property/event is not defined.
	 */
	public function __get($name)
	{
		if($this->contains($name))
			return $this->item_at($name);
		else
			return parent::item_at($name);
	}

	/**
	 * Sets value of a component property.
	 * This method overrides the parent implementation by adding a new key value
	 * to the collection.
	 * @param string $name the property name or event name
	 * @param mixed $value the property value or event handler
	 * @throws Kohana_Exception If the property is not defined or read-only.
	 */
	public function __set($name, $value)
	{
		$this->add($name, $value);
	}

	/**
	 * Checks if a property value is NULL.
	 * This method overrides the parent implementation by checking
	 * if the key exists in the collection and contains a non-NULL value.
	 * @param string $name the property name or the event name
	 * @return boolean whether the property value is NULL
	 * @since 1.0.1
	 */
	public function __isset($name)
	{
		if($this->contains($name))
			return $this->item_at($name) !== NULL;
		else
			return parent::contains($name);
	}

	/**
	 * Sets a component property to be NULL.
	 * This method overrides the parent implementation by clearing
	 * the specified key value.
	 * @param string $name the property name or the event name
	 * @since 1.0.1
	 */
	public function __unset($name)
	{
		$this->remove($name);
	}

	/**
	 * Returns the item with the specified key.
	 * This overrides the parent implementation by converting the key to lower case first if {@link case_sensitive} is FALSE.
	 * @param mixed $key the key
	 * @return mixed the element at the offset, NULL if no element is found at the offset
	 */
	public function item_at($key)
	{
		if($this->case_sensitive)
			return parent::item_at($key);
		else
			return parent::item_at(strtolower($key));
	}

	/**
	 * Adds an item into the map.
	 * This overrides the parent implementation by converting the key to lower case first if {@link case_sensitive} is FALSE.
	 * @param mixed $key key
	 * @param mixed $value value
	 */
	public function add($key, $value)
	{
		if($this->case_sensitive)
			parent::add($key, $value);
		else
			parent::add(strtolower($key), $value);
	}

	/**
	 * Removes an item from the map by its key.
	 * This overrides the parent implementation by converting the key to lower case first if {@link case_sensitive} is FALSE.
	 * @param mixed $key the key of the item to be removed
	 * @return mixed the removed value, NULL if no such key exists.
	 */
	public function remove($key)
	{
		if($this->case_sensitive)
			return parent::remove($key);
		else
			return parent::remove(strtolower($key));
	}

	/**
	 * Returns whether the specified is in the map.
	 * This overrides the parent implementation by converting the key to lower case first if {@link case_sensitive} is FALSE.
	 * @param mixed $key the key
	 * @return boolean whether the map contains an item with the specified key
	 */
	public function contains($key)
	{
		if($this->case_sensitive)
			return parent::contains($key);
		else
			return parent::contains(strtolower($key));
	}

	/**
	 * Determines whether a property is defined.
	 * This method overrides parent implementation by returning TRUE
	 * if the collection contains the named key.
	 * @param string $name the property name
	 * @return boolean whether the property is defined
	 */
	public function has_property($name)
	{
		return $this->contains($name) OR parent::contains($name);
	}

	/**
	 * Determines whether a property can be read.
	 * This method overrides parent implementation by returning TRUE
	 * if the collection contains the named key.
	 * @param string $name the property name
	 * @return boolean whether the property can be read
	 */
	public function can_get_property($name)
	{
		return $this->contains($name) OR parent::contains($name);
	}

	/**
	 * Determines whether a property can be set.
	 * This method overrides parent implementation by always returning TRUE
	 * because you can always add a new value to the collection.
	 * @param string $name the property name
	 * @return boolean TRUE
	 */
	public function can_set_property($name)
	{
		return TRUE;
	}
}
// End Collection_Attribute