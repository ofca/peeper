<div class='module-debug item-container'>
	<h1 class='item-header'><?php echo __('Debug') ?> (<strong><?php echo count($debug) ?></strong>)</h1>
	<div class='modules item-items'>
		<table class='stripes'>
			<?php foreach ($debug as $label => $dump): ?>
				<tr>
					<th><?php echo is_int($label) ? NULL : $label ?></th>
					<td><pre class='debug'><?php echo $dump ?></pre></td>
				</tr>
			<?php endforeach ?>
		</table>
	</div>	
</div>