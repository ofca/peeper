<?php $id = md5($uri.time()) ?>
<div class='request <?php if ($ajax) echo 'ajax '; if ($error) echo 'error ' ?>' id='<?php echo $id ?>'>
	<h1 class='request-title'>
		<?php if ($ajax): ?>
			<span class='ajax'>ajax</span>	
		<?php endif ?>
		
		<?php echo '<span class="method">'.$globals['_SERVER']['REQUEST_METHOD'].'</span> <span class="status">['.$status.']</span> '.$uri ?>
		<?php if ($redirect !== NULL): ?>
			<span class="redirect">--(<?php echo $status ?>)--> <?php echo $redirect ?></span>
		<?php endif ?>
		
		<?php if ($ajax): ?>
			<a class='test-request' href='javascript:void(0);' title='Test request'>test</a>
		<?php endif; ?>
	</h1>
	
	<div class='request-items'>
		<?php foreach ($items as $item) echo '<div class="request-item">'.$item.'</div>' ?>
	</div>
	
</div>

<script type='text/javascript'>	
	Peeper.addRequest('<?php echo $id ?>', {
		url: '<?php echo $uri ?>',
		ajax: <?php echo $ajax ? 'true' : 'false' ?>,
		post: <?php echo json_encode($globals['_POST']) ?>,
		get: <?php echo json_encode($globals['_GET']) ?>,
	});
</script>