<?php defined('SYSPATH') or die('No direct script access.');

abstract class Model_Builder_Core extends Jelly_Builder{

	public function delete_all(array $arr_id = array(), $db = NULL)
    {
		$db = $this->_db($db);
		return $this->_build(Database::DELETE)->where('id', 'IN', $arr_id)->execute($db);
    }

	public function not_in(array $arr_id = array())
	{
		return $this->where('id', 'NOT IN', $arr_id);
	}
	
	public function filter_all($filter = '', array $fields = array())
	{
		$model = $this->_meta->model();
		if(!empty($filter) AND count($fields) > 0)
		{
			$this->where_open();
			foreach ($fields as $field)
			{
				if($this->_meta->field($field) instanceof Jelly_Field) $this->or_where($field, 'like', '%' . $filter . '%');
			}
			$this->where_close();
		}
		return $this;
	}

	public function by_language($language = NULL)
	{
		$language_id = ($language instanceof Jelly_Model) ? (int) $language->id : (int) $language;
		if($language_id > 0) $this->where('language_id', '=', $language_id);
		return $this;
	}

	public function list_by_language($language = NULL)
	{
		$language_id = ($language instanceof Jelly_Model) ? (int) $language->id : (int) $language;
		return $this->where('language_id', '=', $language_id);
	}

	public function paging($offset, $items_per_page = NULL)
	{
		if($items_per_page === NULL)
		{
			$items_per_page = Kohana::$config->load('pagination.default.items_per_page');
		}
		return $this->offset($offset)->limit((int) $items_per_page);
	}

	public function select_rand()
	{
		return $this->order_by(DB::expr('RAND()'));
	}
	
	public function sort_order($order = 'asc')
	{
		return $this->order_by('sort_order', ($order != 'asc' AND $order != 'desc') ? 'asc' : $order);
	}

	public function sort_order_by(array $fields = array())
	{
		if(count($fields) > 0)
		{
			foreach ($fields as $key => $value)
			{
				if($this->_meta->field($key) instanceof Jelly_Field)
				{
					$order = ($value != 'asc' AND $value != 'desc') ? 'asc' : $value;
					$this->order_by($key, $order);
				}
				else
				{
					$order = 'asc';
					$this->order_by($value, $order);
				}
			}
		}
		return $this;
	}

	public function sort_date_created($order = 'desc')
	{
		return $this->order_by('date_created', $order);
	}

	public function select($db = NULL)
	{
		/*$db = $this->_db($db);
		$cache_key = sha1($this->compile(Database::instance($db)));
		if(!$result = Kohana::cache($cache_key))
		{
			$result = parent::select($db);
			if(Kohana::$caching)
			{
				Kohana::cache($cache_key, $result);
			}
		}
		return $result;*/
		return parent::select($db);
	}

	public function select_all($db = NULL)
	{
		/*$db = $this->_db($db);
		$cache_key = sha1($this->compile(Database::instance($db)));
		if(!$result = Kohana::cache($cache_key))
		{
			$result = parent::select_all($db);
			if(Kohana::$caching)
			{
				Kohana::cache($cache_key, $result);
			}
		}
		return $result;*/
		return parent::select_all($db);
	}
}
// End Model_Builder_Core