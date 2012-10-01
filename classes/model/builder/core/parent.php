<?php defined('SYSPATH') or die('No direct script access.');

abstract class Model_Builder_Core_Parent extends Model_Builder_Core{

	public function by_parent($parent = NULL)
	{
		$parent_id = ($parent instanceof Jelly_Model) ? (int) $parent->id : (int) $parent;
		return $this->where('parent_id', '=', $parent_id);
	}

	public function have_children($parent = NULL, $db = NULL)
	{
		$db = $this->_db($db);
		$parent_id = ($parent instanceof Jelly_Model) ? (int) $parent->id : (int) $parent;
		return $this->where('parent_id', '=', $parent_id)->count($db) > 0;
	}

	public function delete_by_parent($parent = NULL, $db = NULL)
	{
		$db = $this->_db($db);
		$parent_id = ($parent instanceof Jelly_Model) ? (int) $parent->id : (int) $parent;
		return $this->_build(Database::DELETE)->where('parent_id', '=', $parent_id)->execute($db);
	}

	public function delete_all_children($parent = NULL, $db = NULL)
    {
		$db = $this->_db($db);
		$arr_id = array();
		if($parent instanceof Jelly_Model)
		{
			$arr_id = array((int) $parent->id);
		}
		else if(is_array($parent) OR ($parent instanceof Traversable))
		{
			foreach($parent as $id)
				$arr_id[] = (int) $id;
		}
		else
		{
			$arr_id = array((int) $parent);
		}
		$models = $this->reset()->where('parent_id', 'IN', $arr_id)->select_all();
		$arr_child_id = array();
		foreach($models as $model)
		{
			if($model->have_children())
			{
				$arr_child_id[] = $model->id;
			}
		}
		if(count($arr_child_id) > 0)
		{
			$this->delete_all_children($arr_child_id);
		}
		return $this->reset()->_build(Database::DELETE)->where('parent_id', 'IN', $arr_id)->execute($db);
    }
} // End Model_Builder_Core_Parent