<?php if (count($notizenbewerbung) > 0): ?>
	<br>
	<div class="row">
		<div class="col-lg-12">
			<table class="table-bordered" align="center" width="100%">
				<thead>
				<tr>
					<th colspan="2" class="text-center"><?= ucfirst($this->p->t('infocenter','anmerkungenZurBewerbung')) ?></th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($notizenbewerbung as $notiz): ?>
					<tr>
						<td class="text-center">
							<?php echo date_format(date_create($notiz->insertamum), 'd.m.Y H:i:s') ?>
						</td>
						<td>
							<?php echo nl2br($notiz->text) ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
<?php endif; ?>