<?php defined('SYSPATH') or die('No direct script access.');

class View_Head{
	protected $_title;
	protected $_scripts = array();
	protected $_script_vars = array();
	protected $_script_files = array();
	protected $_styles = array();
	protected $_style_files = array();

	public function title($title = NULL)
	{
		if(NULL === $title)
		{
			return $this->_title;
		}
		$this->_title = $title;
		return $this;
	}

	public function scripts(array $scripts = array())
	{
		if(count($scripts))
		{
			$this->_scripts = $scripts;
			return $this;
		}
		else
		{
			return HTML::script_blocks($scripts);
		}
	}

	public function script_vars(array $script_vars = array())
	{
		if(count($script_vars))
		{
			$this->_script_vars = $script_vars;
			return $this;
		}
		else
		{
			$js_vars = '';
			foreach($this->_script_vars as $var => $value )
			{
				if(!empty($var))
				{
					$js_vars .= "var {$var} = '{$value}';\n";
				}
			}
			return HTML::script_block($js_vars);
		}
	}

	public function script_files(array $script_files = array())
	{
		if(count($script_files))
		{
			$this->_script_files = $script_files;
			return $this;
		}
		else
		{
			$html = '';
			foreach($this->_script_files as $file)
			{
				$html .= HTML::script($file);
			}
			return $html;
		}
	}

	public function style_files(array $style_files = array())
	{
		if(count($style_files))
		{
			$this->_style_files = $style_files;
			return $this;
		}
		else
		{
			$html = '';
			foreach($this->_style_files as $file)
			{
				$html .= HTML::style($file);
			}
			return $html;
		}
	}

	public function styles(array $styles = array())
	{
		if(count($styles))
		{
			$this->_styles = $styles;
			return $this;
		}
		else
		{
			return HTML::style_blocks($styles);
		}
	}
}
// End View_Head