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
class Kohana_Peeper_Apc extends Peeper {
	
	/**
	 * Saves information about request to APC cache.
	 * 
	 * @param	Exception	
	 * @return	null
	 */
	public function shutdown_handler(Exception $e = NULL)
	{
		if ($e === NULL)
		{
			// Get information about request
			$array = parent::shutdown_handler();
		}
		else
		{
			// Get information about request and exception
			$array = $this->error($e);
		}
		
		if ( ! is_array($array))
		{
			return;
		}
		
		extract($array);
		
		// Make delay
		$delay = rand(50, 150);
		usleep($delay);
		
		// Save data to APC cache
		apc_store('peeper-'.md5($delay.$msec.$id), $output, 3600);
	} // eo shutdown_handler
	
	/**
	 * Returns [Peeper] logs.
	 * 
	 * @return	bool	FALSE if there are no logs
	 * @return	string	Rendered html
	 */
	public function suck_milk()
	{		
		$result = array();			
		
		// cache list
		$list = apc_cache_info('user');
		
		array_reverse($list);
		
		foreach ($list['cache_list'] as $cache)
		{	
			if (strpos($cache['info'], 'peeper-') !== FALSE)
			{
				// Delete logs older then 10 minutes
				if ($cache['creation_time'] < time() - 600)
				{
					apc_delete($cache['info']);
					continue;	
				}
				
				$result[] = apc_fetch($cache['info']);
				apc_delete($cache['info']);
			}
		}
		
		if ($result)
		{
			return $this->render($result);
		}
		
		return FALSE;
	} // eo suck_milk
	
} // eo Kohana_Peeper_Apc
	