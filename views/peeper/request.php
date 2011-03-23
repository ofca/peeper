<?php $id = md5($title.time()) ?>
<div class='request <?php if ($ajax_request) echo 'ajax '; if ($error) echo 'error ' ?>' id='<?php echo $id ?>'>
	<h1 class='request-title'>
		<?php if ($ajax_request): ?>
			<span class='ajax'>ajax</span>	
		<?php endif ?>
		
		<?php echo $title ?>
		
		<?php if ($ajax_request AND ! empty($title)): ?>
			<a class='test-request' href='javascript:void(0);' title='Test request'>test</a>
		<?php endif; ?>
	</h1>
	
	<div class='request-items'>
		<?php foreach ($items as $item) echo '<div class="request-item">'.$item.'</div>' ?>
	</div>
	
</div>

<script type='text/javascript'>	
	Peeper.addRequest('<?php echo $id ?>', {
		url: '<?php echo $title ?>',
		ajax: <?php echo $ajax_request ? 'true' : 'false' ?>,
		post: <?php echo json_encode($globals['_POST']) ?>,
		get: <?php echo json_encode($globals['_GET']) ?>,
	});
</script>