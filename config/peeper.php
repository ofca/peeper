<?php defined('SYSPATH') or die('No direct script access.');

return array(
	/**
	 * Default driver used to store logs.
	 * 
	 * Available drivers:
	 * - File
	 * - APC
	 */
	'default_driver'	=> 'apc',
	/**
	 * Controllers which will be skipped.
	 */
	'excluded_controllers' => array(		
		'peeper',	// do not remove this!
		'media' 
	),
	/**
	 * How many logs to display?
	 */
	'logs_number'	=> 50
);