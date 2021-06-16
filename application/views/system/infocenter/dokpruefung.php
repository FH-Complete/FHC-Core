<div class="table-responsive">
	<table id="doctable" class="table table-bordered">
		<thead>
		<tr>
			<th><?php echo  ucfirst($this->p->t('global','name')) ?></th>
			<th><?php echo  ucfirst($this->p->t('global','typ')) ?></th>
			<th><?php echo  ucfirst($this->p->t('global','uploaddatum')) ?></th>
			<th><?php echo  ucfirst($this->p->t('infocenter','ausstellungsnation')) ?></th>
			<?php
				if (!isset($formalReadonly))
					echo "<th>" . ucfirst($this->p->t('infocenter','formalGeprueft')) . "</th>"
			?>
		</tr>
		</thead>
		<tbody>
		<?php
		foreach ($dokumente as $dokument):
			$geprueft = isset($dokument->formal_geprueft_amum) ? "checked" : "";
			?>
			<tr>
				<td>
					<a href="outputAkteContent/<?php echo $dokument->akte_id ?>"><?php echo isEmptyString($dokument->titel) ? $dokument->bezeichnung : $dokument->titel ?></a>
				</td>
				<td>
					<select class="aktenid" id="aktenid_<?php echo $dokument->akte_id?>" <?php echo (isset($formalReadonly) ? 'disabled' : '') ?>>
						<?php
						foreach($dokumententypen as $dokumenttyp)
							echo "<option " . ($dokumenttyp->bezeichnung === $dokument->dokument_bezeichnung ? 'selected' : '') . " value = " . $dokumenttyp->dokument_kurzbz . ">" . $dokumenttyp->bezeichnung . "</option>"
						?>
					</select>
				</td>
				<td><?php echo date_format(date_create($dokument->erstelltam), 'd.m.Y') ?></td>
				<td><?php echo $dokument->langtext ?></td>
				<?php
				if (!isset($formalReadonly)) :
				?>
					<td>
						<input type="checkbox" class="form-check-input prchbox"
							   id="prchkbx_<?php echo $dokument->akte_id ?>" <?php echo $geprueft ?>>
						<span id="formalgeprueftam_<?php echo $dokument->akte_id ?>">
						<?php echo isset($dokument->formal_geprueft_amum) ? date_format(date_create($dokument->formal_geprueft_amum), 'd.m.Y') : ''; ?>
						</span>
					</td>
				<?php endif ?>
			</tr>
		<?php endforeach ?>
		</tbody>
	</table>
</div>
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
