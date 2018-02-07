<table id="logtable" class="table table-bordered table-hover">
	<thead>
	<tr>
		<th>Datum</th>
		<th>Aktivit&auml;t</th>
		<th>User</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($logs as $log): ?>
		<tr data-toggle="tooltip"
			title="<?php echo isset($log->logdata->message) ? $log->logdata->message : '' ?>">
			<td><?php echo date_format(date_create($log->zeitpunkt), 'd.m.Y H:i:s') ?></td>
			<td><?php echo isset($log->logdata->name) ? $log->logdata->name : '' ?></td>
			<td><?php echo $log->insertvon ?></td>
		</tr>
	<?php endforeach ?>
	</tbody>
</table>