<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Debbuging/Profilling class
 * 
 * @package		Kohana/Peeper
 * @category	Base
 * @author		Adam Sauveur <http://github.com/adam-sauveur>
 * @copyright	(c) 2012 Adam Sauveur <sauveur@emve.org>
 */
class Kohana_Peeper {
	
	/**
	 * @var  boolean  Has [Peeper::init] been called?
	 */
	protected static $_init = FALSE;
	
	protected static $_writed = FALSE;
	
	/**
	 * @var  string  Peeper init time
	 */
	public static $start = '';
	
	/**
	 * @var  array  Array with dumped vars
	 */
	public static $debug = array();
	
	public static $cache_dir = '';
	
	/**
	 * Initialize Peeper (register shutdown handler).
	 * 
	 * @return	void
	 */
	public static function init()
	{
		if (Peeper::$_init)
		{
			return;
		}
				
		// register Peeper shutdown handler
		register_shutdown_function(array('Peeper', 'shutdown_handler'));
		
		Peeper::$_init = TRUE;
	} // eo init
			
	/**
	 * See [Debug::dump].
	 * 
	 * @param	mixed	Variable to dump
	 * @param	string	Label name for variable
	 * @param	integer	Maximum length of strings. See [Debug::dump].
	 * @return	void
	 * @uses	Debug::dump
	 */
	public static function log($var, $label = NULL, $length = 1024)
	{
		$dump = Debug::dump($var, $length);
		
		if ($label === NULL)
		{
			Peeper::$debug[] = $dump;
		}
		else
		{
			Peeper::$debug[$label] = $dump;
		}			
	} // eo log
	
	/**
	 * Check if this is ajax-request.
	 * 
	 * 		print_r( Peeper::get_ajax() );
	 * 		
	 * 		// result - if it is a ajax request
	 * 		array(
	 * 			'ajax_request'	=> TRUE,
	 * 			'ajax_response'	=> '[]'
	 * 		)
	 * 
	 * @return	array
	 */
	public static function get_ajax()
	{
		$request = Request::$initial;
		$ajax = $request->is_ajax();
		
		$output = array('ajax_request' => $ajax);
		
		if ($ajax)
		{
			$output['ajax_response'] = $request->response()->body();
			$output['ajax_response_type'] = $request->response()->headers('content-type');
		}
		
		return $output;	
	} // eo get_ajax
	
	/**
	 * Return dumped variables.
	 * 
	 * 		print_R( Peeper::get_debug() );
	 * 		
	 * 		// result
	 * 		array(
	 * 			'debug' => array(
	 * 				'var-1'	=> '...'
	 * 				1		=> '...'
	 * 				2		=> '...',
	 * 				'label'	=> '...'
	 * 			)
	 *		)
	 * 
	 * @return	array
	 */
	public static function get_debug()
	{
		return array('debug' => Peeper::$debug);
	} // eo get_debug
	
	/**
	 * Return profilers data.
	 * 
	 * 		print_r( Peeper::get_profiler() );
	 * 		
	 * 		// result
	 * 		array(
	 * 			'profiler' => array(
	 * 				'groups' => array(
	 * 					'requests' => array(
	 * 						'backend/user/setting' => array(...) // Profiler::stats()
	 * 					),
	 * 					...
	 * 				),
	 * 				'group_stats' => array(...) // Profiler::group_stats
	 * 			)
	 * 		)
	 * 
	 * @return	array
	 * @uses	Profiler::group_stats
	 * @uses	Profiler::groups
	 * @uses	Profiler::stats
	 */
	public static function get_profiler()
	{
		$output = 
			array(
				'profiler'	=> array(
					'groups'		=> array(),
					'group_stats'	=> Profiler::group_stats()
				)
			);	
		
		$groups = Profiler::groups();
		
		if ($groups)
		{
			foreach (array_keys($groups) as $group)
			{
				if (isset($groups[$group]))
				{			
					foreach ($groups[$group] as $name => $marks)
					{
						$stats = Profiler::stats($marks);
						
						if ($stats)
						{
							$stats['count'] = count($marks);
																					
							$output['profiler']['groups'][$group][$name] = $stats;
						}	
					}
				}
			}
		}
		
		return $output;
	} // eo get_profiler
	
	/**
	 * Return loaded modules.
	 * 
	 * 		print_r( Peeper::get_modules() );
	 * 		
	 * 		// result
	 * 		array(
	 * 			'modules' => array(
	 * 				'purifier'	=> '/hda3/www/rame/rameall.git/modules/shared/purifier/',
	 * 				'database'	=> '/hda3/www/rame/rameall.git/modules/shared/database/'
	 * 				...
	 * 			)
	 * 		);
	 * 
	 * @return	array
	 * @uses	Kohana::modules
	 */
	public static function get_modules()
	{
		return array(
			'modules'	=> Kohana::modules()
		);	
	}
	
	/**
	 * Return included files.
	 * 
	 * 		print_r( Peeper::get_included_files() );
	 * 		
	 * 		// result
	 * 		array(
	 * 			'included_files' => array(
	 * 				'DOCROOT/index.php',
	 * 				'APPPATH/bootstrap.php',
	 * 				'SYSPATH/classes/kohana/core.php',
	 * 				...
	 * 			)
	 * 		);
	 * 
	 * @return	array
	 * @uses	get_included_files
	 */
	public static function get_included_files()
	{
		$files = (array) get_included_files();
		
		foreach ($files as $key => $file)
		{
			$files[$key] = Debug::path($file);
		}
	
		return array('included_files' => $files);	
	} // eo get_included_files
	
	/**
	 * Return loaded extensions.
	 * 
	 * 		print_r( Peeper::get_loaded_extensions() );
	 * 		
	 * 		// result
	 * 		array(
	 * 			'loaded_extensions' => array(
	 * 				'Core',
	 * 				'date',
	 * 				'ereg',
	 * 				'libxml',
	 * 				...
	 * 			)
	 * 		)
	 * 
	 * @return	array
	 * @uses	get_loaded_extensions
	 */
	public static function get_loaded_extensions()
	{
		$extensions = (array) get_loaded_extensions();
		
		foreach ($extensions as $key => $ext)
		{
			$extensions[$key] = Debug::path($ext);
		} 
		
		return array('loaded_extensions' => $extensions);	
	} // eo get_loaded_extensions
	
	/**
	 * Return $GLOBALS.
	 * 
	 * 		print_r( Peeper::get_globals() );
	 * 		
	 * 		// result
	 * 		array('globals' => array(..));
	 * 	
	 * @return	array
	 */
	public static function get_globals()
	{
		return array('globals' => $GLOBALS);
	} // eo get_globals
	
	/**
	 * Return information about request.
	 * 
	 * @return	array
	 */
	public static function get_request()
	{
		$request = Request::$initial;
		$response = $request->response();
		
		$ajax = Request::$initial->is_ajax();
		$redirect = $response->headers('location');
		
		$array =
			 array(
				'request' => array(
					'uri'			=> Request::$initial->uri(),
					'content_type'	=> $response->headers('content-type')->value(),
					'ajax'			=> $ajax,
					'response'		=> $ajax ? $response->body() : NULL,
					'redirect'		=> $redirect !== NULL ? $redirect->value() : NULL,
					'status'		=> $response->status(),
					'error'			=> FALSE
				)
			);
				
		return $array;
	} // eo get_request
	
	/**
	 * Writes cache file with collected data.
	 * 
	 * @return	void
	 */
	public static function shutdown_handler()
	{
		if (Peeper::$_writed)
		{
			return;	
		}
		
		$config = Kohana::config('peeper');
		
		 
		// Do not execute when not active or when it is request to Peeper controller
		if ( ! Peeper::$_init OR in_array(Request::$initial->controller(), $config['excluded_controllers']))
		{	
			return;	
		}
		
		// make delay
		$delay = rand(50, 150);
		usleep($delay);
		
		// collect data
		$output = 
			Peeper::get_request() +
			Peeper::get_debug() +
			Peeper::get_profiler() +
			Peeper::get_globals() +
			Peeper::get_modules() +
			Peeper::get_included_files() +
			Peeper::get_loaded_extensions();
		
		// request start
		Peeper::$start = microtime();
		list($msec, $sec) = explode(' ', Peeper::$start);
		
		Peeper::$cache_dir = APPPATH.'cache/peeper/'.$_SERVER['REMOTE_ADDR'].'/'.$sec.'/';
		
		Peeper::create_cache_dir();
		
		try
		{
			file_put_contents(Peeper::$cache_dir.(string)(float)$msec, serialize($output), LOCK_EX);
		}
		catch (Exception $e)
		{
			
		}
		
		Peeper::$_writed = TRUE;
	} // eo shutdown
	
	public static function error($e)
	{
		if (Peeper::$_writed)
		{
			return;	
		}
		 
		// collect data
		$output =
			Peeper::get_debug() +
			Peeper::get_profiler() +
			Peeper::get_globals() +
			Peeper::get_modules() +
			Peeper::get_included_files() +
			Peeper::get_loaded_extensions();
		
		$request = Request::$initial;
		
		$output['request'] = array(
			'uri'			=> $request->uri(),
			'content_type'	=> 'text/html',
			'error'			=> TRUE,
			'ajax'			=> $request->is_ajax(),
			'redirect'		=> NULL,
			'status'		=> NULL
		);
		
		try
		{
			// Get the exception information
			$type    = get_class($e);
			$code    = $output['request']['status'] = $e->getCode();
			$message = $e->getMessage();
			$file    = $e->getFile();
			$line    = $e->getLine();

			// Get the exception backtrace
			$trace = $e->getTrace();

			if ($e instanceof ErrorException)
			{
				if (isset(Kohana_Exception::$php_errors[$code]))
				{
					// Use the human-readable error name
					$code = Kohana_Exception::$php_errors[$code];
				}

				if (version_compare(PHP_VERSION, '5.3', '<'))
				{
					// Workaround for a bug in ErrorException::getTrace() that exists in
					// all PHP 5.2 versions. @see http://bugs.php.net/bug.php?id=45895
					for ($i = count($trace) - 1; $i > 0; --$i)
					{
						if (isset($trace[$i - 1]['args']))
						{
							// Re-position the args
							$trace[$i]['args'] = $trace[$i - 1]['args'];

							// Remove the args
							unset($trace[$i - 1]['args']);
						}
					}
				}
			}

			// Start an output buffer
			ob_start();

			// Include the exception HTML
			if ($view_file = Kohana::find_file('views', 'peeper/_error'))
			{
				include $view_file;
			}
			else
			{
				throw new Kohana_Exception('Error view file does not exist: views/:file', array(
					':file' => Kohana_Exception::$error_view,
				));
			}

			// Display the contents of the output buffer
			$output['request']['response'] = ob_get_contents();
			ob_clean();
		}
		catch (Exception $e)
		{
			
		}
		
		// request start
		Peeper::$start = microtime();
		list($msec, $sec) = explode(' ', Peeper::$start);
		
		Peeper::$cache_dir = APPPATH.'cache/peeper/'.$_SERVER['REMOTE_ADDR'].'/'.$sec.'/';
		
		Peeper::create_cache_dir();
		
		try
		{
			list($msec, $sec) = explode(' ', Peeper::$start);
						
			file_put_contents(Peeper::$cache_dir.(string)(float)$msec, serialize($output), LOCK_EX);
		}
		catch (Exception $e)
		{
			
		}
		
		Peeper::$_writed = TRUE;
	}
	
	public static function create_cache_dir()
	{
		if ( ! is_dir(Peeper::$cache_dir))
		{
			// Create the cache directory
			mkdir(Peeper::$cache_dir, 0777, TRUE);

			// Set permissions (must be manually set to fix umask issues)
			chmod(Peeper::$cache_dir, 0777);
		}
	}
	
} // eo Peeper

/**
 * Shortcut function to [Peeper::log]
 * 
 * @param	mixed
 * @param	string
 * @param	integer
 * @return	void
 */
function peep($var, $label = NULL, $length = 1024)
{
	return Peeper::log($var, $label, $length);
} // eo peep
