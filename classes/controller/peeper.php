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
		
		// 30 seconds
		while($timeout < 30) 
		{
			// there're no any logs
			if ( ! is_dir($dir))
			{
				goto next;
			}
			
			$array = array();
			// archive logs
			$old = array();
			
			// clear file status cache
			clearstatcache();
			
			// get all directories
			if ($files = scandir($dir))
			{	
				foreach ($files as $file)
				{ 
					if ($file == '.' OR $file == '..')
					{
						continue;
					}
					
					(int) $file;
					
					// all directories older then 10 minut, will be deleted
					if (time() - 600 > $file)
					{
						$old[] = $dir.$file;	
					}
					else
					{
						$array[] = $file;
					}
				}
			}
			
			// remove old dirs
			if ($old)
			{
				array_map(array($this, '_remove_dir'), $old);
			}
			
			if ($array)
			{
				// sort directories 
				arsort($array, SORT_NUMERIC);
				
				// first directory will be the oldest
				$directory = $array[0];
				$path = $dir.$directory.'/';
				// clear array
				$array = array();
				
				// in the directory may show up new logs
				if ($directory == time())
				{
					sleep(1);	
				}
				
				// get all logs
				if ($files = scandir($path))
				{	
					foreach ($files as $file)
					{
						if ($file == '.' OR $file == '..')
						{
							continue;
						}
						
						$array[] = (float) $file;
					}
				}
				
				if ($array)
				{
					arsort($array, SORT_NUMERIC);
					
					foreach ($array as $file)
					{
						$files_content[] = unserialize(file_get_contents($path.$file));
						
						// delete this file
						unlink($path.$file);
					}
					
					// remove directory
					rmdir($dir.$directory);	
					
					return $this->render($files_content);
				}
				else
				{	
					rmdir($dir.$directory);	
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
	
	/**
	 * Delete directory recursively.
	 * 
	 * @author	holger1 at NOSPAMzentralplan dot de
	 * @see		http://www.php.net/manual/en/function.rmdir.php#98622
	 */
	protected function _remove_dir($dir)
	{
		if (is_dir($dir)) 
		{ 
			$objects = scandir($dir); 
			
			foreach ($objects as $object) 
			{ 
				if ($object != "." && $object != "..") 
				{ 
					if (filetype($dir."/".$object) == "dir")
					{
						$this->_remove_dir($dir."/".$object); 
					}
					else
					{
						 unlink($dir."/".$object);
					} 
				} 
			} 
     
			reset($objects); 
			rmdir($dir); 
		} 
	} // eo _rrmdir
} // eo Controller_Peeper
	