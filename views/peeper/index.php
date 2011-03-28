<!DOCTYPE html>
<html>
	<head>
		<title>KO PEEPER</title>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
		<link REL="SHORTCUT ICON" HREF="<?php echo Route::get('peeper/media')->uri(array('file' => 'images/favicon.ico')) ?>">
		
		<script type='text/javascript' src='https://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js'></script>
		
		<?php foreach ($styles as $style => $media) echo HTML::style($style, array('media' => $media), NULL, TRUE), "\n" ?>
		<?php foreach ($scripts as $script) echo HTML::script($script, NULL, NULL, TRUE), "\n" ?>
					
		<script type='text/javascript'>
						
			$(function(){
				/**
				 * setTimeout is to prevent Chrome bug with loading indicator.
				 * @see http://stackoverflow.com/questions/2505632/preventing-browser-loading-indicator-with-chrome-gwt-rpc
				 */
				setTimeout(function(){
					Peeper
						.setMilkURL('<?php echo URL::site("peeper/suckmilk") ?>')
						.setTestQueryURL('<?php echo URL::site("peeper/testquery") ?>')
						.setLogsNumber(<?php echo Kohana::config('peeper.logs_number') ?>)
						.suckMilk();
					}, 1000);
			});
			
		</script>
	</head>
	<body>
	</body>
</html>