<?php defined('SYSPATH') or die('No direct script access.');
/**
 * URL helper class.
 *
 * @package    Application core
 * @category   Helpers
 * @author     Nguyen Van Nguyen
 * @copyright  (c) 2011-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class URL extends Kohana_URL {

	/**
	 * Fetches an absolute site URL based on a action of controller.
	 *
	 *     echo URL::action_site('foo', 'bar', array('id' => 1));
	 *
	 * @param   string  $action_name       Action name
	 * @param   mixed   $controller_name   Controller name
	 * @param   array   $params	   route parameters
	 * @return  string
	 * @uses    URL::site
	 * @uses    URL::action_url
	 */
	public static function action_site($action_name, $controller_name = '', array $params = NULL)
	{
		return URL::site(URL::action_url($action_name, $controller_name, $params));
	}

	/**
	 * Fetches a url based on a action of controller.
	 *
	 *     echo URL::action_url('foo', 'bar', array('id' => 1));
	 *
	 * @param   string  $action_name       Action name
	 * @param   mixed   $controller_name   Controller name
	 * @param   array   $params	   route parameters
	 * @return  string
	 * @uses    Request::initial
	 * @uses    Route::all
	 * @uses    Route::uri
	 * @uses    Route::matches
	 */
	public static function action_url($action_name, $controller_name = '', array $params = NULL)
	{
		$request = Request::current();
		$controller = !empty($controller_name) ? $controller_name : $request->controller();
		$directory = $request->directory();
		if(is_null($params))
		{
			$params = array(
				'directory' => $directory,
				'controller' => $controller,
				'action' => $action_name,
			);
		}
		else
		{
			$params = array_merge($params, array(
				'controller' => $controller,
				'action' => $action_name,
			));
			if(!isset($params['directory']))
			{
				$params['directory'] = $directory;
			}
			if(empty($params['directory']))
			{
				unset($params['directory']);
			}
		}

		$cache_key = 'URL::action_url::';
		foreach($params as $key => $value)
		{
			$cache_key .= $key.$value;
		}

		if(!$url = Kohana::cache($cache_key))
		{
			$select_route = Route::get('default');
			$routes = Route::all();
			foreach($routes as $route_name => $route)
			{
				$match_url = $route->uri($params);
				$match_params = $route->matches($match_url);
				if($match_params == $params)
				{
					$select_route = $route;
					$url = $match_url;
					break;
				}
			}
			if(empty($url))
			{
				$url = $select_route->uri($params);
			}
			$paths = explode('/', $url);
			while(array_pop($paths))
			{
				$match_url = implode('/', $paths);
				$match_params = $route->matches($match_url);
				if($match_params == $params)
				{
					$url = $match_url;
				}
				else
				{
					break;
				}
			}

			if (Kohana::$caching === TRUE)
			{
				Kohana::cache($cache_key, $url);
			}
		}
		
		return $url;
	}

	/**
	 * Fetches an absolute site url based on a route.
	 *
	 *     echo URL::route_site('foo', array('id' => 1));
	 *
	 * @param   string  $route_name       Route name
	 * @param   array   $params	   route parameters
	 * @return  string
	 * @uses    URL::site
	 * @uses    URL::route_url
	 */
	public static function route_site($route_name, array $params = NULL)
	{
		return URL::site(URL::route_url($route_name, $params));
	}

	/**
	 * Fetches a url based on a route.
	 *
	 *     echo URL::route_url('foo', array('id' => 1));
	 *
	 * @param   string  $route_name       Route name
	 * @param   array   $params	   route parameters
	 * @return  string
	 * @uses    Route::get
	 * @uses    Route::uri
	 */
	public static function route_url($route_name, array $params = NULL)
	{
		$route = Route::get($route_name);
		$url = $route->uri($params);
		$paths = explode('/', $url);
		while(array_pop($paths))
		{
			$match_url = implode('/', $paths);
			$match_params = $route->matches($match_url);
			if($match_params == $params)
			{
				$url = $match_url;
			}
			else
			{
				break;
			}
		}
		return $url;
	}
	
}
// End URL