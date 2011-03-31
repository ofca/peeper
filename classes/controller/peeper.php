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
		$view = View::factory('peeper/template');
		
		$this
			->response
			->body($view);		
	} // eo action_index
	
	public function action_core()
	{
		$file = Kohana::find_file('media', 'peeper/js/peeper-loader', 'js');
		
		ob_start();
		
		try
		{
			include $file;
		}
		catch (Exception $e)
		{
			ob_end_clean();
			
			throw $e;	
		}
		
		$content = ob_get_clean();
		
		$this
			->response
			->headers('content-type', 'application/javascript')
			->body($content);
	}
	
	/**
	 * Return logs.
	 */
	public function action_suckMilk()
	{
		$timeout = 1;
		
		while($timeout < 30)
		{			 
			$result = Peeper::instance()->suck_milk();
			
			if (is_string($result))
			{
				return 
					$this
						->response
						->body($result);
			}
			
			++$timeout;
			sleep(1);
		}		
	} // eo action_suckMilk
	
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
     		
			if (count(scandir($dir)) != 2)
			{
				$this->_remove_dir($dir);
			}
			else
			{			
				rmdir($dir);
			}
		} 
	} // eo _rrmdir
} // eo Controller_Peeper
	