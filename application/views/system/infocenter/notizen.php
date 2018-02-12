<table id="notiztable" class="table table-bordered table-hover">
	<thead>
	<tr>
		<th>Datum</th>
		<th>Notiz</th>
		<th>User</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($notizen as $notiz): ?>
		<tr data-toggle="tooltip"
			title="<?php echo isset($notiz->text) ? strip_tags($notiz->text) : '' ?>">
			<td><?php echo date_format(date_create($notiz->insertamum), 'd.m.Y H:i:s') ?></td>
			<td><?php echo html_escape($notiz->titel) ?></td>
			<td><?php echo $notiz->verfasser_uid ?></td>
		</tr>
	<?php endforeach ?>
	</tbody>
</table>