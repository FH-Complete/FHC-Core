<div class="row">
	<div class="col-lg-6 table-responsive stammdaten_form">
		<table class="table">
			<tr>
				<td><strong><?php echo  ucfirst($this->p->t('person','titelpre')) ?></strong></td>
				<td><input type="text" id="titelpre" readonly value="<?php echo $stammdaten->titelpre ?>"></td>
			</tr>

			<tr>
				<td><strong><?php echo  ucfirst($this->p->t('person','vorname')) ?></strong></td>
				<td><input type="text" id="vorname" readonly value="<?php echo $stammdaten->vorname ?>"></td>
			</tr>
			<tr>
				<td><strong><?php echo  ucfirst($this->p->t('person','nachname')) ?></strong></td>
				<td>
					<input type="text" id="nachname" readonly value="<?php echo $stammdaten->nachname ?>"></td>
			</tr>
			<tr>
				<td><strong><?php echo  ucfirst($this->p->t('person','titelpost')) ?></strong></td>
				<td><input type="text"  id="titelpost" readonly value="<?php echo $stammdaten->titelpost ?>"></td>
			</tr>
			<tr>
				<td><strong><?php echo  ucfirst($this->p->t('person','geburtsdatum')) ?></strong></td>
				<td>
					<input type="text"  id="gebdatum" readonly value="<?php echo date_format(date_create($stammdaten->gebdatum), 'd.m.Y') ?>" placeholder="DD.MM.YYYY"></td>
			</tr>
			<tr>
				<td><strong><?php echo  ucfirst($this->p->t('person','svnr')) ?></strong></td>
				<td>
					<input type="text"  id="svnr" readonly value="<?php echo $stammdaten->svnr ?>"></td>
			</tr>
			<tr>
				<td><strong><?php echo  ucfirst($this->p->t('person','staatsbuergerschaft')) ?></strong></td>
				<td>
					<select id="buergerschaft" readonly>
						<?php
						foreach ($all_nations as $nation)
						{
							$selected = '';
							if ($nation->nation_code === $stammdaten->staatsbuergerschaft_code)
								$selected = 'selected';
							echo "<option value='". $nation->nation_code ."' " . $selected . ">". $nation->langtext . "</option>";
						}
						?>
					</select>
			</tr>
			<tr>
				<td><strong><?php echo  ucfirst($this->p->t('person','geschlecht')) ?></strong></td>
				<td>
					<?php
						$language = getUserLanguage() == 'German' ? 0 : 1;
					?>
					<select id="geschlecht" readonly>
						<?php
						foreach ($all_genders as $gender)
						{
							$selected = '';
							if ($gender->geschlecht === $stammdaten->geschlecht)
								$selected = 'selected';
							echo "<option value='". $gender->geschlecht ."' " . $selected . ">". ucfirst($gender->bezeichnung_mehrsprachig[$language]) . "</option>";
						}
						?>
					</select>
				</td>

			</tr>
			<tr>
				<td><strong><?php echo  ucfirst($this->p->t('person','geburtsnation')) ?></strong></td>
				<td>
					<select id="gebnation" readonly>

					<?php
						foreach ($all_nations as $nation)
						{
							$selected = '';
							if ($nation->nation_code === $stammdaten->geburtsnation_code)
								$selected = 'selected';
							echo "<option value='". $nation->nation_code ."' " . $selected . ">". $nation->langtext . "</option>";
						}
					?>
					</select>
			</tr>
			<tr>
				<td><strong><?php echo  ucfirst($this->p->t('person','geburtsort')) ?></strong></td>
				<td><input type="text" id="gebort" readonly value="<?php echo $stammdaten->gebort ?>"></td>
			</tr>
		</table>
	</div>
	<div class="col-lg-6 table-responsive">
		<table class="table table-bordered stammdaten_form">
			<thead>
			<tr>
				<th colspan="4" class="text-center"><?php echo  ucfirst($this->p->t('global','kontakt')) ?></th>
			</tr>
			<tr>
				<th class="text-center"><?php echo  ucfirst($this->p->t('global','typ')) ?></th>
				<th class="text-center"><?php echo  ucfirst($this->p->t('global','kontakt')) ?></th>
				<th class="text-center"><?php echo  ucfirst($this->p->t('global','anmerkung')) ?></th>
			</tr>
			</thead>
			<tbody>
			<?php
			$lastMailAdress = '';
			foreach ($stammdaten->kontakte as $kontakt): ?>
				<tr>
				<?php if ($kontakt->kontakttyp === 'email'): ?>
					<td><?php echo ucfirst($this->p->t('person', 'email')) ?></td>
				<?php elseif ($kontakt->kontakttyp === 'telefon' || $kontakt->kontakttyp === 'mobil'): ?>
					<td><?php echo ucfirst($this->p->t('person', 'telefon')) ?></td>
				<?php else: ?>
					<td><?php echo ucfirst($kontakt->kontakttyp) ?></td>
				<?php endif; ?>
					<td>
						<?php echo '<span class="'.$kontakt->kontakttyp.'">';?>
						<?php if ($kontakt->kontakttyp === 'email'): ?>
							<a href="mailto:<?php echo $kontakt->kontakt; ?>" target="_top">
							<?php $lastMailAdress = $kontakt->kontakt;
							endif;
							if (($kontakt->kontakttyp === 'telefon' || $kontakt->kontakttyp === 'mobil'))
								echo '<input type="text" data-value="'. $kontakt->kontakt_id .'" class="kontakt_nummer" readonly value="'. $kontakt->kontakt . '">';
							else
								echo $kontakt->kontakt;
							if ($kontakt->kontakttyp === 'email'):
							?>
							</a>
							<?php endif; ?>
					<?php echo '</span>'?>
					</td>
					<td><?php echo $kontakt->anmerkung; ?></td>
				</tr>
			<?php endforeach; ?>
			<?php foreach ($stammdaten->adressen as $adresse): ?>
				<tr>
					<td>
						<?php echo  ucfirst($this->p->t('person','adresse')) ?>
					</td>
					<td>
						<?php echo isset($adresse) ? $adresse->strasse.', '.$adresse->plz.' '.$adresse->ort : '' ?>
						<?php echo isset($adresse->nationkurztext) ? '<br />'.$adresse->nationkurztext : '' ?>
					</td>
					<td>
						<?php echo ($adresse->heimatadresse === true ? 'Heimatadresse' : '').
							($adresse->heimatadresse === true && $adresse->rechnungsadresse === true ? ', ' : '').
							($adresse->rechnungsadresse === true ? 'Rechnungsadresse' : ''); ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<div class="row">
			<div class="col-xs-6">
				<form id="sendmsgform" method="post" action="<?php echo site_url('/system/messages/Messages/writeTemplate'); ?>" target="_blank">
					<input type="hidden" name="person_id" value="<?php echo $stammdaten->person_id ?>">
					<a id="sendmsglink" href="javascript:void(0);">
						<i class="fa fa-envelope"></i>&nbsp;<?php echo $this->p->t('ui','nachrichtSenden'); ?>
					</a>
				</form>
			</div>
			<?php if (isset($stammdaten->zugangscode)): ?>
				<div class="col-xs-6 text-right">
					<a href="<?php echo CIS_ROOT.'addons/bewerbung/cis/registration.php?code='.html_escape($stammdaten->zugangscode).'&emailAdresse='.$lastMailAdress ?>"
					   target='_blank'><i class="glyphicon glyphicon-new-window"></i>&nbsp;<?php echo  $this->p->t('infocenter','zugangBewerbung') ?></a>
				</div>
			<?php endif; ?>
			<div class="col-xs-6">
				<a class="editStammdaten">
					<i class="fa fa-edit"></i>&nbsp;<?php echo $this->p->t('ui','bearbeiten'); ?></a>
				<div class="editActionStammdaten" style="display:none">
					<a class="cancelStammdaten">
						<i class="fa fa-trash"></i>&nbsp;<?php echo $this->p->t('ui','abbrechen');?></a>
					<a class="saveStammdaten">
						<i class="fa fa-save"></i>&nbsp;<?php echo $this->p->t('ui','speichern'); ?></a>
				</div>
			</div>
		</div>
	</div>
</div>
