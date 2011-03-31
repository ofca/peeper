<?php defined('SYSPATH') or die('No direct script access.');
/**
 * [Peeper] File driver. Provides an file based
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
	
	public function suck_milk()
	{
		$files_content = array();
		$result = array();
		
		$dir = APPPATH.'cache/peeper/'.$_SERVER['REMOTE_ADDR'].'/';
		
		// there're no any logs
		if ( ! is_dir($dir))
		{
			return FALSE;
		}
		
		$array = array();
		// archive logs
		$delete = array();
		
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
					$delete[] = $dir.$file;	
				}
				else
				{
					$array[] = $file;
				}
			}
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
			if ($directory == time() OR ! file_exists($path))
			{
				return FALSE;
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
					if ( ! file_exists($path.$file))
					{
						continue;
					}
					
					$files_content[] = unserialize(file_get_contents($path.$file));
				}
				
				// remove directory
				$delete[] = $dir.$directory;
			}
			else
			{
				$delete[] = $dir.$directory;
			}
		}
		
		// remove old dirs
		if ($delete)
		{ 
			array_map(array($this, '_remove_dir'), $delete);
		}
		
		if ($files_content)
		{
			return $this->render($files_content);	
		}
		
		return FALSE;
	}
} // eo Kohana_Peeper_Apc
