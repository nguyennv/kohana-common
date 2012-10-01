<?php defined('SYSPATH') or die('No direct script access.');

class View_Jsonp extends View{
	protected $_json_data;
	protected $_callback;

	public function __construct($data = NULL, $callback = 'jsoncallback')
	{
		$this->_json_data = $data;
		$this->_callback = empty($callback) ? 'jsoncallback' : $callback;
	}

	public function render($data = NULL)
	{
		$request = Request::initial();
		$request->response()->headers('Content-Type', 'application/javascript');
		if($data !== NULL)
		{
			$this->_json_data = $data;
		}
		return $this->_callback . '(' . HTML::encode_js($this->_json_data) . ');';
	}
}
// End View_Jsonp