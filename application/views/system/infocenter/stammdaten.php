<div class="row">
	<div class="col-lg-6 table-responsive stammdaten_form">
		<table class="table">
			<tr>
				<td><strong><?php echo  ucfirst($this->p->t('person','titelpre')) ?></strong></td>
				<td>
					<div class='stammdaten' id="titelpre"><?php echo $stammdaten->titelpre ?></div>
				</td>
			</tr>

			<tr>
				<td><strong><?php echo  ucfirst($this->p->t('person','vorname')) ?></strong></td>
				<td>
					<div class='stammdaten' id="vorname"><?php echo $stammdaten->vorname ?></div>
				</td>
			</tr>
			<tr>
				<td><strong><?php echo  ucfirst($this->p->t('person','nachname')) ?></strong></td>
				<td>
					<div class='stammdaten' id="nachname"><?php echo $stammdaten->nachname ?></div>
				</td>
			</tr>
			<tr>
				<td><strong><?php echo  ucfirst($this->p->t('person','titelpost')) ?></strong></td>
				<td>
					<div class='stammdaten' id="titelpost"><?php echo $stammdaten->titelpost ?></div>
				</td>
			</tr>
			<tr>
				<td><strong><?php echo  ucfirst($this->p->t('person','geburtsdatum')) ?></strong></td>
				<td>
					<div class='stammdaten' id="gebdatum"><?php echo date_format(date_create($stammdaten->gebdatum), 'd.m.Y') ?></div>
				</td>
			</tr>
			<tr>
				<td><strong><?php echo  ucfirst($this->p->t('person','svnr')) ?></strong></td>
				<td>
					<div class='stammdaten' id="svnr"><?php echo $stammdaten->svnr ?></div>
				</td>
			</tr>
			<tr>
				<td><strong><?php echo  ucfirst($this->p->t('person','staatsbuergerschaft')) ?></strong></td>
				<td>
					<select id="buergerschaft" disabled>
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
					<select id="geschlecht" disabled>
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
					<select id="gebnation" disabled>

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
				<td>
					<div class='stammdaten' id="gebort"><?php echo $stammdaten->gebort ?></div>
				</td>
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
						<?php echo '<span class="kontakt '.$kontakt->kontakttyp.'" data-id="'. $kontakt->kontakt_id .'" data-value="' . $kontakt->kontakt .'">';?>
						<?php if ($kontakt->kontakttyp === 'email'): ?>
							<a href="mailto:<?php echo $kontakt->kontakt; ?>" target="_top">
							<?php $lastMailAdress = $kontakt->kontakt;
							endif;
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
						<?php if (isset($adresse)): ?>
							<div class="row adresse col-sm-12" data-id="<?php echo $adresse->adresse_id ?>">
								<div class="float-left" data-value="<?php echo $adresse->strasse ?>" data-type="strasse"><?php echo $adresse->strasse ?></div>
								
								<div class="float-left" data-value="<?php echo $adresse->plz ?>" data-type="plz"><?php echo $adresse->plz ?></div>
								
								<div class="float-left" data-value="<?php echo $adresse->ort ?>" data-type="ort"><?php echo $adresse->ort ?></div>

							<?php if (isset($adresse->nationkurztext)): ?>
								<select id="nation_<?php echo $adresse->adresse_id ?>" disabled>
									<?php
									foreach ($all_nations as $nation)
									{
										$selected = '';
										if ($nation->nation_code === $adresse->nation)
											$selected = 'selected';
										echo "<option value='". $nation->nation_code ."' " . $selected . ">". $nation->langtext . "</option>";
									}
									?>
								</select>
							</div>
							<br />
							<?php endif; ?>
						<?php endif; ?>
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
