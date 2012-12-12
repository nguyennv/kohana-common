<?php defined('SYSPATH') or die('No direct script access.');

/**
* View content block class to create and hold content for blocks
*/
class View_Block{
	protected $_name;
	protected $_default;

	protected $_closures = array();

    /**
	 * Create new named content block
	 */
	public function __construct($name, $closure = NULL)
	{
		$this->_name = $name;
		if(NULL !== $closure)
		{
			$this->_ensure_closure($closure);
			$this->_default = $closure;
		}
	}
	
	public function content($closure = NULL)
	{
		if(NULL !== $closure)
		{
			$this->_ensure_closure($closure);
			$this->_closures = array($closure);
		}
		$content = "";
		if($closures = $this->_closures)
		{
			// Execute all closure callbacks
			ob_start();
			foreach($closures as $closure)
			{
				echo $closure();
			}
			$content = ob_get_clean();
		}
		else if($this->_default)
		{
			$default = $this->_default;
			ob_start();
			echo $default();
			$content = ob_get_clean();
		}
		
		// Return content
		return $content;
	}

	public function __toString()
	{
		try
		{
			return $this->content();
		}
		catch (Exception $e)
		{
			// Display the exception message
			Kohana_Exception::handler($e);

			return '';
		}
	}

	private function _ensure_closure($closure)
	{
		if(!is_callable($closure))
		{
			throw new InvalidArgumentException("Block content expected a closure, given (" . gettype($closure) . ")");
		}
	}
}
// End View_Block