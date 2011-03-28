<div id='included-files' class='item-container'>
	<h1 class='item-header'><?php echo __('Included files') ?> (<strong><?php echo count($files) ?></strong>)</h1>
	<div class='modules item-items' style='display: none'>
		<table class='stripes'>
			<?php foreach ($files as $file): ?>
				<tr>
					<td><?php echo $file ?></td>
				</tr>
			<?php endforeach ?>
		</table>
	</div>	
</div>