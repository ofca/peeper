<?php defined('SYSPATH') or die('No direct script access.');

return array(
	/**
	 * Controllers which will be skipped.
	 */
	'excluded_controllers' => array(		
		'peeper',	// do not remove this!
		'mediaserver' 
	),
	/**
	 * How many logs to display?
	 */
	'logs_number'	=> 50
);