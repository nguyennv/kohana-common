<?php defined('SYSPATH') or die('No direct script access.');

class Helper_Loader{

	private static $_packages = array();

	private static $_aliases = array(
		'system' => SYSPATH,
		'app' => APPPATH,
		'modules' => MODPATH,
	);

	public static function application_load()
	{
		self::import('app.vendor.*');
	}

	public static function load_class($class, $path = '')
	{
		if(is_string($class) === TRUE)
		{
			class_exists($class) === TRUE OR require(empty($path) ? "$class.php" : $path.DIRECTORY_SEPARATOR."$class.php");
		}
		elseif(is_array($class) === TRUE)
		{
			foreach ($class as $one)
			{
				class_exists($one) === TRUE OR require(empty($path) ? "$class.php" : $path.DIRECTORY_SEPARATOR."$one.php");
			}
		}
	}

	public static function import($package, $check_class_existence = TRUE)
	{
		if(isset(self::$_packages[$package]) OR class_exists($package, FALSE))
			return TRUE;
		// a class name
		if(($pos = strrpos($package,'.')) === FALSE)
		{
			try
			{
				require($package.EXT);
			}
			catch(Exception $e)
			{
				if($check_class_existence AND !class_exists($package, FALSE))
					throw new Kohana_Exception(__('Unknown class :package.', array(':package' => $package)));
				else
					throw $e;
			}
		}
		elseif(($path = self::path_of_package($package,EXT))!==NULL)
		{
			$className = substr($package, $pos+1);
			if($className==='*')
			{
				// a directory
				self::$_packages[$package] = $path;
				set_include_path($path.PATH_SEPARATOR.get_include_path());
			
			}
			else
			{
				// a file
				self::$_packages[$package] = $path;
				if(!$check_class_existence OR !class_exists($className, FALSE)){
					try
					{
						require($path);
					}
					catch(Exception $e)
					{
						if($check_class_existence AND !class_exists($className, FALSE))
							throw new Kohana_Exception(__('Unknown class :package.', array(':package' => $package)));
						else
							throw $e;
					}
				}
			}
		}else{
			throw new Kohana_Exception(__('":package" is not a valid package to be used. Make sure ".*" is appended if you want to use a package referring to a directory.', array(':package' => $package)));
		}
	}

	public static function path_of_package($package, $ext='')
	{
		if(isset(self::$_packages[$package]))
			return self::$_packages[$package];
		elseif(isset(self::$_aliases[$package]))
			return self::$_aliases[$package];
		else
		{
			$segs = explode('.',$package);
			$alias = array_shift($segs);
			if(($file = array_pop($segs)) !== NULL AND ($root = self::path_of_alias($alias)) !== NULL)
				return rtrim($root.implode(DIRECTORY_SEPARATOR ,$segs),'/\\').(($file==='*')?'':DIRECTORY_SEPARATOR.$file.$ext);
			else
				return NULL;
		}
	}

	public static function path_of_alias($alias, $path = null)
	{
		return isset(self::$_aliases[$alias]) ? self::$_aliases[$alias] : NULL;
	}

	public static function set_path_of_alias($alias, $path)
	{
		if(empty($path))
			unset(self::$_aliases[$alias]);
		else
			self::$_aliases[$alias] = rtrim($path,'\\/');
	}
}
// End Helper_Loader