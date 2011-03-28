<?php
$group_cols       = array('min', 'max', 'average', 'total');
$application_cols = array('min', 'max', 'average', 'current');
?>

<div id='kohana-profiler' class='item-container'>
	<h1 class='item-header'>Profiler</h1>
	<div class='profilers item-items'>
	<?php foreach ($groups as $group => $benchmarks): ?>
		<table class="profiler">
			<tr class="group table-header">
				<th class="name"><?php echo __(ucfirst($group)) ?></th>
				<td colspan="5">
					<span class="time"><?php echo number_format($group_stats[$group]['total']['time'], 6) ?> <abbr title="seconds">s</abbr></span>
					<span class="memory"><?php echo number_format($group_stats[$group]['total']['memory'] / 1024, 4) ?> <abbr title="kilobyte">kB</abbr></span>
				</td>
			</tr>
			<tr class="headers">
				<th class="name"><?php echo __('Benchmark') ?></th>
				<?php foreach ($group_cols as $key): ?>
				<th class="<?php echo $key ?>"><?php echo __(ucfirst($key)) ?></th>
				<?php endforeach ?>
			</tr>
			<?php foreach ($benchmarks as $name => $tokens): ?>
				<tr class="mark time">				
					<th class="name" rowspan="2" scope="rowgroup">
						<?php if (strpos($group, 'database') !== FALSE AND preg_match('/^select/i', $name)): ?>
							<a class='test-query' href='javascript:void(0);'>test
								<span><?php echo urlencode($name) ?></span>	
							</a>
						<?php endif ?>
						
						<?php echo HTML::chars($name); ?>
					</th>
					<?php foreach ($group_cols as $key): ?>
					<td class="<?php echo $key ?>">
						<div>
							<div class="value"><?php echo number_format($tokens[$key]['time'], 6) ?> <abbr title="seconds">s</abbr></div>
							<?php if ($key === 'total'): ?>
								<div class="graph" style="left: <?php echo max(0, 100 - $tokens[$key]['time'] / $group_stats[$group]['max']['time'] * 100) ?>%"></div>
							<?php endif ?>
						</div>
					</td>
					<?php endforeach ?>
				</tr>
				<tr class="mark memory">
					<?php foreach ($group_cols as $key): ?>
					<td class="<?php echo $key ?>">
						<div>
							<div class="value"><?php echo number_format($tokens[$key]['memory'] / 1024, 4) ?> <abbr title="kilobyte">kB</abbr></div>
							<?php if ($key === 'total'): ?>
								<div class="graph" style="left: <?php echo max(0, 100 - $tokens[$key]['memory'] / $group_stats[$group]['max']['memory'] * 100) ?>%"></div>
							<?php endif ?>
						</div>
					</td>
					<?php endforeach ?>
				</tr>
			<?php endforeach ?>
		</table>
	<?php endforeach ?>
	</div>
	<!--<table class="profiler">
		<?php $stats = Profiler::application() ?>
		<tr class="final mark time">
			<th class="name" rowspan="2" scope="rowgroup"><?php echo __('Application Execution').' ('.$stats['count'].')' ?></th>
			<?php foreach ($application_cols as $key): ?>
			<td class="<?php echo $key ?>"><?php echo number_format($stats[$key]['time'], 6) ?> <abbr title="seconds">s</abbr></td>
			<?php endforeach ?>
		</tr>
		<tr class="final mark memory">
			<?php foreach ($application_cols as $key): ?>
			<td class="<?php echo $key ?>"><?php echo number_format($stats[$key]['memory'] / 1024, 4) ?> <abbr title="kilobyte">kB</abbr></td>
			<?php endforeach ?>
		</tr>
	</table>-->
	
</div>

