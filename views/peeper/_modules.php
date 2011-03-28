<div id='kohana-modules' class='item-container'>
	<h1 class='item-header'>Modules (<strong><?php echo count($modules) ?></strong>)</h1>
	<div class='modules item-items' style='display: none'>
		<table class='stripes'>
			<?php foreach ($modules as $name => $path): ?>
				<tr>
					<th><?php echo $name ?></th>
					<td><?php echo $path ?></td>
				</tr>
			<?php endforeach ?>
		</table>
	</div>	
</div>