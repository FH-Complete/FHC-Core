<?php if (count($dokumente_nachgereicht) > 0): ?>
	<br/>
	<p><?php echo  ucfirst($this->p->t('infocenter','nachzureichendeDokumente')) ?></p>
	<table id="nachgdoctable" class="table table-bordered">
		<thead>
		<tr>
			<th><?php echo  ucfirst($this->p->t('global','typ')) ?></th>
			<th><?php echo  ucfirst($this->p->t('infocenter','nachzureichenAm')) ?></th>
			<th><?php echo  ucfirst($this->p->t('infocenter','ausstellungsnation')) ?></th>
			<th><?php echo  ucfirst($this->p->t('global','anmerkung')) ?></th>
		</tr>
		</thead>
		<tbody>
		<?php
		foreach ($dokumente_nachgereicht as $dokument):
			?>
			<tr>
				<td><?php echo $dokument->dokument_bezeichnung ?></td>
				<td>
					<?php echo isset($dokument->nachgereicht_am) ? date_format(date_create($dokument->nachgereicht_am), 'd.m.Y') : ''; ?>
				</td>
				<td>
					<?php echo $dokument->langtext ?>
				</td>
				<td>
					<?php echo $dokument->anmerkung; ?>
				</td>
			</tr>
		<?php endforeach ?>
		</tbody>
	</table>
<?php endif; ?>