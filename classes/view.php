<?php defined('SYSPATH') or die('No direct script access.');

class View extends Kohana_View{
	// Content blocks
	protected static $_blocks = array();

	public static function render_partial($file, array $data = NULL)
	{
		return View::factory($file, $data)->render();
	}

	public static function block($name, $closure = null)
	{
		if(!isset(self::$_blocks[$name]))
		{
			self::$_blocks[$name] = new View_Block($name, $closure);
		}
		return self::$_blocks[$name];
	}
}
// End View