<?php $id = md5($title.time()) ?>
<div class='request <?php if ($ajax_request) echo 'ajax '; if ($error) echo 'error ' ?>' id='<?php echo $id ?>'>
	<h1 class='request-title'>
		<?php if ($ajax_request): ?>
			<span class='ajax'>ajax</span>	
		<?php endif ?>
		
		<?php echo $title ?>
	</h1>
	
	<div class='request-items'>
		<?php foreach ($items as $item) echo '<div class="request-item">'.$item.'</div>' ?>
	</div>
	
</div>

<script type='text/javascript'>
	var $request = $('#<?php echo $id ?>');
	
	$('.request-title', $request).toggle(
		function(){
			$(this).next().slideDown();
		},
		function(){
			$(this).next().slideUp();
		}
	);
	
	$('.item-header', $request).click(function(){
		
		var $next = $(this).next();
		
		if ($next.css('display') == 'none'){
			$next.slideDown();
		} else {
			$next.slideUp();
		}
		
	});
	
</script>