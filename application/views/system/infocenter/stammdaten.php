<div class="row">
	<div class="col-lg-6 table-responsive">
		<table class="table">
			<tr>
				<td><strong><?php echo  ucfirst($this->p->t('person','vorname')) ?></strong></td>
				<td><?php echo $stammdaten->vorname ?></td>
			</tr>
			<tr>
				<td><strong><?php echo  ucfirst($this->p->t('person','nachname')) ?></strong></td>
				<td>
					<?php echo $stammdaten->nachname ?></td>
			</tr>
			<tr>
				<td><strong><?php echo  ucfirst($this->p->t('person','geburtsdatum')) ?></strong></td>
				<td>
					<?php echo date_format(date_create($stammdaten->gebdatum), 'd.m.Y') ?></td>
			</tr>
			<tr>
				<td><strong><?php echo  ucfirst($this->p->t('person','svnr')) ?></strong></td>
				<td>
					<?php echo $stammdaten->svnr ?></td>
			</tr>
			<tr>
				<td><strong><?php echo  ucfirst($this->p->t('person','staatsbuergerschaft')) ?></strong></td>
				<td>
					<?php echo $stammdaten->staatsbuergerschaft ?></td>
			</tr>
			<tr>
				<td><strong><?php echo  ucfirst($this->p->t('person','geschlecht')) ?></strong></td>
				<td>
					<?php echo $stammdaten->geschlecht ?></td>
			</tr>
			<tr>
				<td><strong><?php echo  ucfirst($this->p->t('person','geburtsnation')) ?></strong></td>
				<td>
					<?php echo $stammdaten->geburtsnation ?></td>
			</tr>
			<tr>
				<td><strong><?php echo  ucfirst($this->p->t('person','geburtsort')) ?></strong></td>
				<td><?php echo $stammdaten->gebort ?></td>
			</tr>
		</table>
	</div>
	<div class="col-lg-6 table-responsive">
		<table class="table table-bordered">
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
			<?php foreach ($stammdaten->kontakte as $kontakt): ?>
				<tr>
				<?php if ($kontakt->kontakttyp === 'email'): ?>
					<td><?php echo  ucfirst($this->p->t('person','email')) ?></td>
				<?php elseif ($kontakt->kontakttyp === 'telefon'): ?>
					<td><?php echo  ucfirst($this->p->t('person','telefon')) ?></td>
				<?php endif; ?>
					<td>
						<?php echo '<span class="'.$kontakt->kontakttyp.'">';?>
						<?php if ($kontakt->kontakttyp === 'email'): ?>
						<a href="mailto:<?php echo $kontakt->kontakt; ?>" target="_top">
							<?php
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
						<?php echo isset($adresse) ? $adresse->strasse.', '.$adresse->plz.' '.$adresse->ort : '' ?>
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
				<form id="sendmsgform" method="post" action="<?php echo $messagelink ?>"
					  target="_blank">
					<input type="hidden" name="person_id"
						   value="<?php echo $stammdaten->person_id ?>">
					<a id="sendmsglink" href="javascript:void(0);"><i
								class="fa fa-envelope"></i>&nbsp;<?php echo  $this->p->t('ui','nachrichtSenden') ?></a>
				</form>
			</div>
			<?php if (isset($stammdaten->zugangscode)): ?>
				<div class="col-xs-6 text-right">
					<a href="<?php echo CIS_ROOT.'addons/bewerbung/cis/registration.php?code='.html_escape($stammdaten->zugangscode) ?>"
					   target='_blank'><i class="glyphicon glyphicon-new-window"></i>&nbsp;<?php echo  $this->p->t('infocenter','zugangBewerbung') ?></a>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
