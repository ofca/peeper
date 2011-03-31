<?php defined('SYSPATH') or die('No direct script access.');

Peeper::instance()->init();

// Static file serving (CSS, JS, images)
Route::set('peeper/media', 'peeper/media(/<file>)', array('file' => '.+'))
	->defaults(array(
		'controller' => 'peeper',
		'action'     => 'media',
		'file'       => NULL,
	));