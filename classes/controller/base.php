<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Abstract controller class for automatic view.
 *
 * @package    Application core
 * @category   Controller
 * @author     Nguyen Van Nguyen
 * @copyright  (c) 2011-2012 Nguyen Van Nguyen
 * @license    http://kohanaframework.org/license
 */
abstract class Controller_Base extends Controller{
	/**
	 * @var  string  page layout
	 */
	protected $_layout = 'layout';

	/**
	 * @var  View  content view
	 */
	protected $_view = NULL;

	/**
	 * @var  Session  controller session
	 */
	protected $_session;

	/**
	 * Creates a new controller instance. Each controller must be constructed
	 * with the request object that created it.
	 *
	 * @param   Request   $request  Request that created the controller
	 * @param   Response  $response The request's response
	 * @return  void
	 */
	public function __construct(Request $request, Response $response)
	{
		parent::__construct($request, $response);
		$this->_session = Session::instance();
	}

	/**
	 * Assigns the content [View] as the request response.
	 */
	public function after()
	{
		if($this->_view instanceof View)
		{
			$this->response->body($this->_view->render());				
		}
		parent::after();
	}

	/**
	 * This method returns the named parameter requested, or all of them
	 * if no parameter is given.
	 *
	 * @param string $param The name of the parameter
	 * @param mixed $default Default value
	 * @return mixed
	 */
	public function param($param = NULL, $default = NULL)
	{
		return $this->request->param($param, $default);
	}

	/**
	 * This method returns all of the named parameters.
	 *
	 * @return array
	 */
	public function params()
	{
		return $this->request->param();
	}

	/**
	 * Sets and gets layout view file.
	 *
	 * @param   string $layout
	 * @return  mixed
	 */
	protected function layout($layout = NULL)
	{
		if($layout === NULL)
		{
			return $this->_layout;
		}
		$this->_layout = (string) $layout;
		return $this;
	}

	/**
	 * Factory content view and set content view file.
	 *
	 * @param   array $data
	 * @param   string $view_file
	 * @return  mixed
	 */
	protected function view(array $data = NULL, $view_file = NULL, $layout = NULL)
	{
		if(empty($view_file))
		{
			$directory = $this->request->directory();
			if(empty($directory))
			{
				$view_file = $this->request->controller().DIRECTORY_SEPARATOR.$this->request->action();
			}
			else
			{
				$view_file = $directory.DIRECTORY_SEPARATOR.$this->request->controller().DIRECTORY_SEPARATOR.$this->request->action();
			}
		}
		if(!empty($layout))
		{
			$this->_layout = $layout;
		}
		$content_view = View::factory($view_file, $data)->render();
		$this->_view = View::factory($this->_layout, array('content' => $content_view));
		return $this->_view;
	}

	/**
	 * Factory content view and set content view file
	 * and set auto render is false.
	 *
	 * @param   array $data
	 * @param   string $view_file
	 * @return  mixed
	 */
	protected function partial_view(array $data = NULL, $view_file = NULL)
	{
		if(empty($view_file))
		{
			$directory = $this->request->directory();
			if(empty($directory))
			{
				$view_file = $this->request->controller().DIRECTORY_SEPARATOR.$this->request->action();
			}
			else
			{
				$view_file = $directory.DIRECTORY_SEPARATOR.$this->request->controller().DIRECTORY_SEPARATOR.$this->request->action();
			}
		}
		$this->_view = View::factory($view_file, $data);
		return $this->_view;
	}

	/**
	 * Factory json view.
	 *
	 * @param   array $data
	 * @return  mixed
	 */
	protected function json($data = NULL)
	{
		$this->_view = new View_Json($data);
		return $this->_view;
	}

	/**
	 * Redirects as the request response. If the URL does not include a
	 * protocol, it will be converted into a complete URL.
	 *
	 *     $request->redirect($url);
	 *
	 * [!!] No further processing can be done after this method is called!
	 *
	 * @param   string   $url   Redirect location
	 * @return  void
	 * @uses    Request::redirect
	 */
	protected function redirect($url)
	{
		$this->request->redirect($url);
	}

	/**
	 * Redirect to action of controller.
	 *
	 * @param   string   $action_name   action name
	 * @param   string   $controller_name   controller name
	 * @param   array    $params   route parameters
	 * @return  void
	 * @uses    URL::action_url
	 */
	protected function redirect_to_action($action_name, $controller_name = '', array $params = NULL)
	{
		$this->redirect(URL::action_url($action_name, $controller_name, $params));
	}

	/**
	 * Redirect to a route.
	 *
	 * @param   string   $route_name   route name
	 * @param   array    $params   route parameters
	 * @return  void
	 * @uses    URL::route_url
	 */
	protected function redirect_to_route($route_name, array $params = NULL)
	{
		$this->redirect(URL::route_url($route_name, $params));
	}

	/**
	 * Set responce no cache headers.
	 *
	 * @return  void
	 * @uses    Response::headers
	 */
	protected function no_cache()
	{
		$this->response->headers('Cache-Control', 'no-cache');
		$this->response->headers('Expires', '-1');
		$this->response->headers('Pragma', 'no-cache');
	}

	protected function validate_anti_forgery_token()
	{
		$config = Kohana::$config->load('security');
		$token_name = $config->get('csrf_token_name', 'request-verification-token');
		$csrf_key = $config->get('csrf_key', Security::token());
		if($this->request->is_ajax())
		{
			$csrf_token = $this->request->headers($token_name);
		}
		else
		{
			$csrf_token = $this->request->post($token_name);
		}
		return Crypto_Hash_Simple::verify_hash($csrf_key, $csrf_token);
	}

	protected function upload_file($file_upload = 'file', $directory = NULL)
	{
		if ($this->valid_upload($file_upload))
		{
			$file_path = Upload::save(
							$_FILES[$file_upload],
							NULL, $directory,
							0777
						);
			$file_name = basename($file_path);
			Kohana::$log->add(Log::INFO, 'Upload successfully - File '.$file_name.' has been uploaded');
			return $file_name;
		}else{
			return FALSE;
		}
	}

	protected function upload_image($file_upload = 'file', $directory = NULL)
	{
		if($this->valid_upload($file_upload))
		{
			$file_path = Upload::save(
							$_FILES[$file_upload],
							NULL, $directory,
							0777
						);
			$file_name = basename($file_path);
			$config = Kohana::$config->load('upload');
			$resize_dimension = $config->get('resize_dimension');
			$resize_type = (int) $config->get('resize_type');

			Image::factory($file_path)
					->resize(
						isset($resize_dimension[0]) ? (int) $resize_dimension[0] : 100,
						isset($resize_dimension[1]) ? (int) $resize_dimension[1] : 100,
						$resize_type
					)
					->save($directory.'/thumbs/'.$file_name, 0777);

			Kohana::$log->add(Log::INFO, 'Upload successfully - File '.$file_name.' has been uploaded');
			return $file_name;
		}else{
			return FALSE;
		}
	}

	protected function valid_upload($file_upload = 'file'){
		$config = Kohana::$config->load('upload');
		$allowed_types = $config->get('allowed_types');
		$allowed_size = $config->get('allowed_size');

		$files = Validation::factory($_FILES)
					->rule($file_upload, 'Upload::valid')
					->rule($file_upload, 'Upload::not_empty')
					->rule($file_upload, 'Upload::type', array(':value', $allowed_types))
					->rule($file_upload, 'Upload::size', array(':value', $allowed_size));

		if($files->check())
		{
			return TRUE;
		}
		else
		{
			Kohana::$log->add(Log::INFO, 'Upload failed - Not valid file upload');
			return FALSE;
		}
	}
}
// End Controller_Base