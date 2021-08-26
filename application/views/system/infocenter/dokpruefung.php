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

					<div class="row">
						<button class="nachreichungInfos hidden" id="nachreichungInfos_<?php echo $dokument->akte_id?>"><?php echo  ucfirst($this->p->t('infocenter','dokumentWirdNachgereicht')) ?></button>
					</div>

					<div class="nachreichungInputs hidden" id="nachreichungInputs_<?php echo $dokument->akte_id?>">
						<div class="row">
							<div class="col-sm-8">
								<div class="input-group">
									<input type="text" class="form-control nachreichungAnmerkung" id="nachreichungAnmerkung_<?php echo $dokument->akte_id?>" maxlength="128" placeholder="Institution des Ausstellers (zB: TGM Wien)">
									<span class="input-group-addon" style="color: grey;">128</span>

								</div>
							</div>
							<div class="col-sm-4">
								<input type="text" class="form-control nachreichungAm" id="nachreichungAm_<?php echo $dokument->akte_id?>" autofocus="autofocus" placeholder="tt.mm.jjjj">
							</div>
						</div>
						<div class="row">
							<div class="col-sm-12">
								<div class="btn-group pull-right">
									<input type="button" value="OK" class="btn btn-primary nachreichungSpeichern" id="nachreichungSpeichern_<?php echo $dokument->akte_id?>">
									<input type="button" value="Abbrechen" class="btn btn-default nachreichungAbbrechen" id="nachreichungAbbrechen_<?php echo $dokument->akte_id?>">
								</div>
							</div>
						</div>

					</div>


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