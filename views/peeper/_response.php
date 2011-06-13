<div class='ajax-response item-container'>
	<h1 class='item-header'><?php echo __('Response') ?></h1>
	<div class='modules item-items'>
		<table>
			<tr>
				<?php if ($error): ?>
					<td class='<?php echo str_replace('/', '-', $content_type) ?>'>
						<?php echo $response ?>
					</td>
				<?php else: ?>
					<td class='<?php echo str_replace('/', '-', $content_type) ?>'>						
						<p class='debug'><?php echo HTML::chars($response) ?></p>						
						
						<?php if ($content_type == 'text/html' OR $content_type == 'text/xml'): ?>
							<div class='text-html' style='display: none'><?php echo $response ?></div>
						<?php endif ?>
					</td>
					<td></td>
				<?php endif ?>
			</tr>
		</table>
	</div>	
</div>