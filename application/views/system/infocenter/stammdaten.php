<div class="row">
	<div class="col-lg-6 table-responsive">
		<table class="table">
			<tr>
				<td><strong>Vorname</strong></td>
				<td><?php echo $stammdaten->vorname ?></td>
			</tr>
			<tr>
				<td><strong>Nachname</strong></td>
				<td>
					<?php echo $stammdaten->nachname ?></td>
			</tr>
			<tr>
				<td><strong>Geburtsdatum</strong></td>
				<td>
					<?php echo date_format(date_create($stammdaten->gebdatum), 'd.m.Y') ?></td>
			</tr>
			<tr>
				<td><strong>Sozialversicherungsnr</strong></td>
				<td>
					<?php echo $stammdaten->svnr ?></td>
			</tr>
			<tr>
				<td><strong>Staatsb&uuml;rgerschaft</strong></td>
				<td>
					<?php echo $stammdaten->staatsbuergerschaft ?></td>
			</tr>
			<tr>
				<td><strong>Geschlecht</strong></td>
				<td>
					<?php echo $stammdaten->geschlecht ?></td>
			</tr>
			<tr>
				<td><strong>Geburtsnation</strong></td>
				<td>
					<?php echo $stammdaten->geburtsnation ?></td>
			</tr>
			<tr>
				<td><strong>Geburtsort</strong></td>
				<td><?php echo $stammdaten->gebort ?></td>
			</tr>
		</table>
	</div>
	<div class="col-lg-6 table-responsive">
		<table class="table table-bordered">
			<thead>
			<tr>
				<th colspan="4" class="text-center">Kontakte</th>
			</tr>
			<tr>
				<th class="text-center">Typ</th>
				<th class="text-center">Kontakt</th>
				<th class="text-center">Anmerkung</th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($stammdaten->kontakte as $kontakt): ?>
				<tr>
					<td><?php echo ucfirst($kontakt->kontakttyp); ?></td>
					<td>
						<?php if ($kontakt->kontakttyp === 'email'): ?>
						<a href="mailto:<?php echo $kontakt->kontakt; ?>" target="_top">
							<?php
							endif;
							echo $kontakt->kontakt;
							if ($kontakt->kontakttyp === 'email'):
							?>
						</a>
					<?php endif; ?>
					</td>
					<td><?php echo $kontakt->anmerkung; ?></td>
				</tr>
			<?php endforeach; ?>
			<?php foreach ($stammdaten->adressen as $adresse): ?>
				<tr>
					<td>
						Adresse
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
			<div class="col-lg-6">
				<form id="sendmsgform" method="post" action="<?php echo $messagelink ?>"
					  target="_blank">
					<input type="hidden" name="person_id"
						   value="<?php echo $stammdaten->person_id ?>">
					<a id="sendmsglink" href="javascript:void(0);"><i
								class="fa fa-envelope"></i>&nbsp;Nachricht senden</a>
				</form>
			</div>
			<?php if (isset($stammdaten->zugangscode)): ?>
				<div class="col-lg-6 text-right">
					<a href="<?php echo base_url('addons/bewerbung/cis/registration.php?code='.html_escape($stammdaten->zugangscode)) ?>"
					   target='_blank'><i class="glyphicon glyphicon-new-window"></i>&nbsp;Zugang Bewerbung</a>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>