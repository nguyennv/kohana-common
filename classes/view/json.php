<?php defined('SYSPATH') or die('No direct script access.');

class View_Json extends View{
	protected $_json_data;

	public function __construct($data = NULL)
	{
		$this->_json_data = $data;
	}

	public function render($data = NULL)
	{
		$request = Request::initial();
		$request->response()->headers('Content-Type', 'application/json');
		if($data !== NULL)
		{
			$this->_json_data = $data;
		}
		return HTML::json_encode($this->_json_data);
	}
}
// End View_Json