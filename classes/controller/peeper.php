<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Peeper controller
 * 
 * @package		Kohana/Peeper
 * @category	Controller
 * @author		Adam Sauveur <http://github.com/adam-sauveur>
 * @copyright	(c) 2012 Adam Sauveur <adam.sauveur@gmail.com>
 */
class Controller_Peeper extends Controller {
	
	/**
	 * Index.
	 */
	public function action_index()
	{	
		$view = View::factory('peeper/index');
		
		$media = Route::get('peeper/media');
		
		$view->styles = array(
			
			$media->uri(array('file' => 'css/redmond/jquery-ui-1.8.11.custom.css')) => 'screen',
			$media->uri(array('file' => 'css/peeper.css')) => 'screen'
		);
		
		$view->scripts = array(
			$media->uri(array('file' => 'js/jquery-ui-1.8.11.custom.min.js')),
			$media->uri(array('file' => 'js/class.js')),
			$media->uri(array('file' => 'js/peeper.js'))
		);
		
		$this
			->response
			->body($view);
		
	} // eo action_index
	
	/**
	 * Return logs.
	 */
	public function action_suckMilk()
	{ 
		$timeout = 1;
		$files_content = array();
		$result = array();
		
		$dir = APPPATH.'cache/peeper/'.$_SERVER['REMOTE_ADDR'].'/';
		
		// 10 seconds
		while($timeout < 30) 
		{
			/*if (connection_aborted())
			{
				exit;
			}*/
			clearstatcache();
			
			$files = scandir($dir);
			$array = array();

			foreach ($files as $file)
			{ 
				if ($file == '.' OR $file == '..' OR $file[0] == '.' OR $file[0] == '_')
				{
					continue;
				}
				
				$array[] = $file;
			}

			if ( ! empty($array))
			{
				arsort($array, SORT_NUMERIC);
				
				$directory = $array[0];
				$path = $dir.$directory.'/';
				$array = array();
				
				if ($directory == time())
				{
					sleep(1);	
				}
				
				$files = scandir($path);
				
				foreach ($files as $file)
				{
					if ($file == '.' OR $file == '..')
					{
						continue;
					}
					
					$array[] = ltrim($file, '.');
				}
				
				if ( ! empty($array))
				{
					arsort($array, SORT_NUMERIC);
					
					foreach ($array as $file)
					{
						$files_content[] = unserialize(file_get_contents($path.'.'.$file));
					}
					
					rename($dir.$directory, $dir.'_'.$directory);	
					return $this->render($files_content);
				}
				else
				{
					rename($dir.$directory, $dir.'_'.$directory);	
				}
			}
			
			next:
				$timeout++;
				
				// wait 1 second
				sleep(1);
		}
		
	} // eo action_suckMilk
	
	/**
	 * Render html.
	 * 
	 * @param	array
	 * @return	void
	 */
	public function render(array $result)
	{
		$output = '';
		
		foreach ($result as $item)
		{
			$error = FALSE;
			$title = NULL;
			
			$ajax = $item['ajax_request'];
			
			if (isset($item['profiler']) AND isset($item['profiler']['groups']) AND isset($item['profiler']['groups']['requests']))
			{	
				reset($item['profiler']['groups']['requests']);
				$title = trim(key($item['profiler']['groups']['requests']), '"');
			}
			
			if (isset($item['error']))
			{
				$error = TRUE;
			}
			
			$view = View::factory('peeper/request', array('title' => $title, 'ajax_request' => $ajax, 'error' => $error, 'globals' => $item['globals']));
			
			$view->items = array();
			
			if ( ! empty($item['debug']))
			{
				$view->items['debug'] = View::factory('peeper/_debug', array('debug' => $item['debug']));
			}
						
			if ($ajax AND isset($item['ajax_response']))
			{
				$view->items['ajax_response'] = View::factory('peeper/_ajax_response', array('error' => $error, 'response' => $item['ajax_response'], 'content_type' => $item['ajax_response_type']));
			}
			
			if (isset($item['profiler']))
			{
				$view->items['profiler'] = View::factory('peeper/_profiler', $item['profiler']);
			}
			
			if (isset($item['globals']))
			{
				$view->items['globals'] = View::factory('peeper/_globals', array('vars' => $item['globals']));	
			}
			
			if (isset($item['modules']))
			{
				$view->items['modules'] = View::factory('peeper/_modules', array('modules' => $item['modules']));
			}
			
			if (isset($item['included_files']))
			{
				$view->items['included_files'] = View::factory('peeper/_included_files', array('files' => $item['included_files']));
			}
			
			if (isset($item['loaded_extensions']))
			{
				$view->items['loaded_extensions'] = View::factory('peeper/_loaded_extensions', array('files' => $item['loaded_extensions']));
			}
			
			$output .= $view;
		}
		
		$this
			->response
			->body($output);
	} // eo render
	
	/**
	 * Database query tester.
	 * 
	 * Request (POST):
	 * Param			| Type			| Desc
	 * -----------------|---------------|----------------
	 * query			| string		| query to test
	 * 
	 * @requestMethod	POST
	 * @responseFormat	JSON
	 */
	public function action_testQuery()
	{
		$query = Arr::get($_POST, 'query', NULL);
		
		if (empty($query))
		{
			return;
		}
		
		$query = urldecode($query);
		
		$result = 
			DB::query(DATABASE::SELECT, $query)
				->execute()
				->as_array();
				
		$this
			->response
			->headers('content-type', 'application/json')
			->body(json_encode($result));
	} // eo action_testQuery
	
	public function action_media()
	{
		// Get the file path from the request
		$file = $this->request->param('file');

		// Find the file extension
		$ext = pathinfo($file, PATHINFO_EXTENSION);

		// Remove the extension from the filename
		$file = substr($file, 0, -(strlen($ext) + 1));

		if ($file = Kohana::find_file('media/peeper', $file, $ext))
		{ 			
			// Send the file content as the response
			$this->response->body(file_get_contents($file));

			// Set the proper headers to allow caching
			$this->response->headers('content-type',  File::mime_by_ext($ext));
			$this->response->headers('last-modified', date('r', filemtime($file)));
		}
		else
		{
			// Return a 404 status
			$this->response->status(404);
		}
	}
} // eo Controller_Peeper
	