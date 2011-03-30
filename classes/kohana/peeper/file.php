<?php defined('SYSPATH') or die('No direct script access.');
/**
 * [Peeper] APC driver. Provides an opcode based
 * driver for Peeper library.
 * 
 * @package		Kohana/Peeper
 * @category	Peeper
 * @author		Adam Sauveur <http://github.com/adam-sauveur>
 * @copyright	(c) 2012 Adam Sauveur <sauveur@emve.org>
 */
class Kohana_Peeper_File extends Peeper {
	
	public function shutdown_handler(Exception $e = NULL)
	{
		if ($e === NULL)
		{
			$array = parent::shutdown_handler();
		}
		else
		{ 
			$array = $this->error($e);
		}
		
		if ( ! is_array($array))
		{
			return;	
		}
		
		extract($array);
		
		// make delay
		$delay = rand(50, 150);
		usleep($delay);
		
		$cache_dir = APPPATH.'cache/peeper/'.$user.'/'.$sec.'/';
		
		if ( ! is_dir($cache_dir))
		{
			// Create the cache directory
			mkdir($cache_dir, 0777, TRUE);

			// Set permissions (must be manually set to fix umask issues)
			chmod($cache_dir, 0777);
		}
		
		try
		{
			file_put_contents($cache_dir.(string)(float)$msec, serialize($output), LOCK_EX);
		}
		catch (Exception $e)
		{
			
		}
		
		$this->_writed = TRUE;	
	} // eo shutdown_handler
	
} // eo Kohana_Peeper_Apc
