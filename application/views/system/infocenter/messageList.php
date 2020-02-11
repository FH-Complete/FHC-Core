<?php
$msgExists = count($messages) > 0;
$widthColumn = $msgExists === true ? 8 : 12;
?>
<div class="col-lg-<?php echo $widthColumn ?>">
	<table id="msgtable" class="table table-bordered table-condensed tablesort-hover tablesort-active">
		<thead>
		<tr>
			<th><?php echo  ucfirst($this->p->t('global','gesendetAm')) ?></th>
			<th><?php echo  ucfirst($this->p->t('global','sender')) ?></th>
			<th><?php echo  ucfirst($this->p->t('global','empfaenger')) ?></th>
			<th><?php echo  ucfirst($this->p->t('global','betreff')) ?></th>
			<th><?php echo  ucfirst($this->p->t('global','gelesenAm')) ?></th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($messages as $message): ?>
			<tr id="<?php echo $message->message_id.'_'.$message->repersonid ?>" style="cursor: pointer">
				<td><?php echo isset($message->insertamum) ? date_format(date_create($message->insertamum), 'd.m.Y H:i:s') : '' ?></td>
				<td><?php echo $message->sevorname.' '.$message->senachname ?></td>
				<td><?php echo $message->revorname.' '.$message->renachname ?></td>
				<td><?php echo $message->subject ?></td>
				<td><?php echo isset($message->statusamum) ? date_format(date_create($message->statusamum), 'd.m.Y H:i:s') : '' ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>
<?php if ($msgExists === true): ?>
<div class="col-lg-4">
	<br>
	<div class="text-center"><label for="msgbody" id="msgsubject"></label></div>
	<div>
		<textarea id="msgbody"></textarea>
	</div>
</div>
<?php endif; ?>
