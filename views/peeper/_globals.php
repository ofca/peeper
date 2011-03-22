<div id='globals' class='item-container'>
	<h1 class='item-header'><?php echo __('Globals') ?></h1>
	<div class='modules item-items' style='display: none'>
		<?php foreach (array('_SESSION', '_GET', '_POST', '_FILES', '_COOKIE', '_SERVER') as $var): ?>			
			<?php if (empty($vars[$var]) OR ! is_array($vars[$var])) continue ?>	
			
			<table class='stripes'>
					<tr class='table-header'>
						<th colspan='2'><?php echo $var ?></th>
					</tr>
				<?php foreach ($vars[$var] as $name => $value): ?>
					<tr>
						<th><?php echo HTML::chars($name) ?></th>
						<td><p><?php echo Debug::dump($value) ?></p></td>
					</tr>
				<?php endforeach ?>
			</table>
		<?php endforeach ?>
	</div>	
</div>