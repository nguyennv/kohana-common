<?php defined('SYSPATH') or die('No direct script access.');

class Tree_Node{
	/**
	 * Parent node
	 * @var    object
	 */
	protected $_parent = NULL;

	/**
	 * Array of children
	 *
	 * @var    array
	 */
	protected $_children = array();

	/**
	 * Constructor
	 */
	public function __construct()
	{
	}

	/**
	 * Add child to this node
	 *
	 * If the child already has a parent, the link is unset
	 *
	 * @param   Tree_Node  $child  The child to be added
	 *
	 * @return  void
	 */
	public function add_child(Tree_Node $child)
	{
		if ($child instanceof Tree_Node)
		{
			$child->set_parent($this);
		}
		return $this;
	}

	/**
	 * Set the parent of a this node
	 *
	 * If the node already has a parent, the link is unset
	 *
	 * @param   mixed  $parent  The Tree_Node for parent to be set or null
	 *
	 * @return  void
	 */
	public function set_parent(Tree_Node $parent)
	{
		if ($parent instanceof Tree_Node || is_null($parent))
		{
			$hash = spl_object_hash($this);
			if (!is_null($this->_parent))
			{
				unset($this->_parent->children[$hash]);
			}
			if (!is_null($parent))
			{
				$parent->_children[$hash] = $this;
			}
			$this->_parent = $parent;
		}
		return $this;
	}

	/**
	 * Get the children of this node
	 *
	 * @return  array    The children
	 */
	public function get_children()
	{
		return $this->_children;
	}

	/**
	 * Get the parent of this node
	 *
	 * @return  mixed   Tree_Node object with the parent or null for no parent
	 */
	public function get_parent()
	{
		return $this->_parent;
	}

	/**
	 * Test if this node has children
	 *
	 * @return   boolean  True if there are children
	 */
	public function has_children()
	{
		return (bool) count($this->_children);
	}

	/**
	 * Test if this node has a parent
	 *
	 * @return  boolean  True if there is a parent
	 */
	public function has_parent()
	{
		return $this->_parent !== NULL;
	}
} // End Tree_Node