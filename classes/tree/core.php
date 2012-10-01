<?php defined('SYSPATH') or die('No direct script access.');

class Tree_Core{
	/**
	 * Root node
	 *
	 * @var    object
	 */
	protected $_root = NULL;

	/**
	 * Current working node
	 *
	 * @var    object
	 */
	protected $_current = NULL;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->_root = new Tree_Node('ROOT');
		$this->_current = $this->_root;
	}

	/**
	 * Method to add a child
	 *
	 * @param   array    $node         The node to process
	 * @param   boolean  $set_current  True to set as current working node
	 *
	 * @return  mixed
	 */
	public function add_child(Tree_Node $node, $set_current = FALSE)
	{
		$this->_current->add_child($node);
		if ($set_current)
		{
			$this->_current = $node;
		}
		return $this;
	}

	/**
	 * Method to get the parent
	 *
	 * @return  this
	 */
	public function get_parent()
	{
		return $this->current($this->current()->get_parent());
	}

	/**
	 * Method to get the root
	 *
	 * @return  this
	 */
	public function reset()
	{
		$this->_current = $this->_root;
		return $this;
	}
	
	public function current(Tree_Node $current = NULL)
	{
		if($current === NULL)
		{
			return $this->_current;
		}
		$this->_current = $current;
		return $this;
	}
	
} // End Tree_Core