<?php defined('SYSPATH') or die('No direct script access.');

class Exception_Handler{
    public static function handle(Exception $e)
    {
        if (Kohana::$environment === Kohana::DEVELOPMENT)
        {
            parent::handler($e);
        }
		else
		{
			switch (get_class($e))
			{
				case 'HTTP_Exception_404':
					$response = new Response;
					$response->status(404);
					$view = new View('error/404');
					$view->message = $e->getMessage();
					$view->title = 'File Not Found';
					echo $response->body($view)->send_headers()->body();
					return TRUE;
					break;
				default:
					return Kohana_Exception::handler($e);
					break;
			}
		}
    }
}
// End Exception_Handler