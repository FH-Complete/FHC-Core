<table id="notiztable" class="table table-bordered table-hover">
	<thead>
	<tr>
		<th><?php echo  ucfirst($this->p->t('global', 'datum')) ?></th>
		<th><?php echo  ucfirst($this->p->t('global', 'notiz')) ?></th>
		<th>User</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($notizen as $notiz): ?>	
		<tr data-toggle="tooltip"
			title="<?php echo isset($notiz->text) ? html_escape($notiz->text) : '' ?>" style="cursor: pointer">
			<td><?php echo date_format(date_create($notiz->insertamum), 'd.m.Y H:i:s') ?></td>
			<td><?php echo html_escape($notiz->titel) ?></td>
			<td><?php echo $notiz->verfasser_uid ?></td>
			<td style="display: none" class="hiddennotizid"><?php echo $notiz->notiz_id ?></td>
		</tr>
	<?php endforeach ?>
	</tbody>
</table>