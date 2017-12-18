<?php
$this->load->view('templates/header', array('title' => 'InfocenterDetails', 'datepicker' => true, 'datepickerclass' => 'dateinput'/*, 'tablesort' => true, 'tableid' => 't1'*/));
?>
<body>
<h2>Infocenter - Person Details</h2>

<fieldset>
	<legend>Stammdaten</legend>
	<table>
		<tr>
			<td>Vorname: <?php echo $person->vorname ?></td>
		</tr>
		<tr>
			<td>Nachname: <?php echo $person->nachname ?></td>
		</tr>

		<tr>
			<td>Geburtsdatum: <?php echo date_format(date_create($person->gebdatum), 'd.m.Y') ?></td>
		</tr>
		<tr>
			<td>Sozialversicherungsnr: <?php echo $person->svnr ?></td>
		</tr>
		<tr>
			<td>Staatsb&uuml;rgerschaft: <?php echo $staatsbuergerschaft->kurztext ?></td>
		</tr>
		<tr>
			<td>Geschlecht: <?php echo $person->geschlecht ?></td>
		</tr>
		<tr>
			<td>Geburtsnation: <?php echo $geburtsnation->kurztext ?></td>
		</tr>
		<tr>
			<td>Geburtsort: <?php echo $person->gebort ?></td>
		</tr>
		<tr>
			<td align="center">Kontaktdaten</td>
		</tr>
		<?php
		foreach ($kontakte as $kontakt):
			?>
			<tr>
				<td><?php
					echo ucfirst($kontakt->kontakttyp).': ';
					if ($kontakt->kontakttyp == 'email'):
					?>
					 <a href="mailto:<?php echo $kontakt->kontakt; ?>" target="_top">
					<?php
					endif;
					echo $kontakt->kontakt;
					if ($kontakt->kontakttyp == 'email'):
					?>
					 </a>
					<?php endif; ?>
				</td>
			</tr>
			<?php
		endforeach
		?>
		<tr>
			<td>
				Adresse: <?php echo isset($adresse) ? $adresse->strasse.", ".$adresse->plz." ".$adresse->ort : "" ?>
			</td>
		</tr>
	</table>
</fieldset>
<fieldset>
	<legend>Dokumentenprüfung</legend>
	<table border="1"><!-- id="t1" class = "tablesorter"-->
		<thead>
		<th>Name</th>
		<th>Typ</th>
		<th>Uploaddatum</th>
		<th>formal geprüft</th>
		</thead>
		<tbody>
		<?php
		foreach ($dokumente as $dokument):
			$geprueft = (isset($dokument->formal_geprueft_amum)) ? "checked" : "";
			?>
			<tr>
				<td><a href="../outputAkteContent/<?php echo $dokument->akte_id ?>"><?php echo $dokument->titel ?></a>
				</td>
				<td><?php echo $dokument->dokument_kurzbz ?></td>
				<td><?php echo date_format(date_create($dokument->erstelltam), 'd.m.Y') ?></td>
				<td>
					<input type="checkbox" <?php echo $geprueft ?>
						   onclick="onFormalGeprueftChange(this.checked, <?php echo $dokument->akte_id ?>, <?php echo $person->person_id ?>)"/>
					<?php
					echo $dokument->formal_geprueft_amum;
					?>
				</td>
			</tr>
		<?php endforeach ?>
		</tbody>
	</table>
</fieldset>
<?php foreach ($zgvpruefungen as $zgvpruefung): ?>
	<form method="post" action="../saveZgvPruefung/<?php echo $zgvpruefung->prestudent_id ?>">
		<fieldset>
			<legend>ZGV Prüfung - Studiengang <?php echo $zgvpruefung->studiengang ?></legend>
			<table>
				<tr>
					<td>
						<label>Letzter Status: </label>
						<?php echo $zgvpruefung->prestudentstatus->status_kurzbz?>
					</td>
					<td>
						<label>Freigegeben an Studiengang: </label>
						<?php echo isset($zgvpruefung->prestudentstatus->bestaetigtam) ? "ja" : "nein" ?>
					</td>
					<td>
						<label>Studiensemester: </label>
						<?php echo $this->widgetlib->widget(
							'Studiensemester_widget',
							array(DropdownWidget::SELECTED_ELEMENT => $zgvpruefung->prestudentstatus->studiensemester_kurzbz),
							array('name' => 'studiensemester', 'id' => 'studiensemester')
						); ?>
					</td>
					<td>
						<label>Ausbildungssemester: </label>
						<input type="text" name="ausbildungssemester" value="<?php echo $zgvpruefung->prestudentstatus->ausbildungssemester ?>"
						<?php echo $zgvpruefung->prestudentstatus->ausbildungssemester ?>
					</td>
				</tr>
				<tr>
					<td>
						<label>ZGV: </label>
						<?php echo $this->widgetlib->widget(
							'Zgv_widget',
							array(DropdownWidget::SELECTED_ELEMENT => $zgvpruefung->zgv_code),
							array('name' => 'zgv', 'id' => 'zgv')
						); ?>
					</td>
					<td>
						<label>ZGV Ort: </label>
						<input type="text" value="<?php echo $zgvpruefung->zgvort ?>" name="zgvort">
					</td>
					<td>
						<label>ZGV Datum: </label>
						<input type="text" class="dateinput"
							   value="<?php echo empty($zgvpruefung->zgvdatum) ? "" : date_format(date_create($zgvpruefung->zgvdatum), 'd.m.Y') ?>"
							   name="zgvdatum">
					</td>
					<td>
						<label>ZGV Nation: </label>
						<?php echo $this->widgetlib->widget(
							'Nation_widget',
							array(DropdownWidget::SELECTED_ELEMENT => $zgvpruefung->zgvnation_code),
							array('name' => 'zgvnation', 'id' => 'zgvnation')
						); ?>
					</td>
				</tr>
				<?php if($zgvpruefung->studiengangtyp === 'm') :?>
				<tr>
					<td>
						<label>ZGV Master: </label>
						<?php echo $this->widgetlib->widget(
							'Zgvmaster_widget',
							array(DropdownWidget::SELECTED_ELEMENT => $zgvpruefung->zgvmas_code),
							array('name' => 'zgvmas', 'id' => 'zgvmas')
						); ?>
					</td>
					<td>
						<label>ZGV Master Ort: </label>
						<input type="text" value="<?php echo $zgvpruefung->zgvmaort ?>" name="zgvmaort">
					</td>
					<td>
						<label>ZGV Master Datum: </label>
						<input type="text" class="dateinput"
							   value="<?php echo empty($zgvpruefung->zgvmadatum) ? "" : date_format(date_create($zgvpruefung->zgvmadatum), 'd.m.Y') ?>"
							   name="zgvmadatum">
					</td>
					<td>
						<label>ZGV Master Nation: </label>
						<?php echo $this->widgetlib->widget(
							'Nation_widget',
							array(DropdownWidget::SELECTED_ELEMENT => $zgvpruefung->zgvmanation_code),
							array('name' => 'zgvmanation', 'id' => 'zgvmanation')
						); ?>
					</td>
				</tr>
				<?php endif; ?>
				<tr>
					<td>
						<input type="submit" value="Speichern"/>
					</td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
				<tr>
					<td></td>
				</tr>
			</table>
		</fieldset>
	</form>

	<?php /*echo $this->widgetlib->widget(
		'Statusgrund_widget',
		array(),
		array('name' => 'absage', 'id' => 'absage')
	); */
	//Prestudenten cannot be abgewiesen or freigegeben if already done
	if(!isset($zgvpruefung->prestudentstatus->bestaetigtam) && $zgvpruefung->prestudentstatus->status_kurzbz != 'Abgewiesener') :
	?>
	<form method="post" action="../saveAbsage/<?php echo $zgvpruefung->prestudent_id ?>">
		<label>Absagegrund:</label>
		<select name="statusgrund">
			<?php foreach ($statusgruende as $statusgrund): ?>
				<option value="<?php echo $statusgrund->statusgrund_id ?>"><?php echo $statusgrund->bezeichnung_mehrsprachig[0] ?></option>
			<?php endforeach ?>
		</select>
		<button type="submit">Absage</button>
	</form>
	<a href="../saveFreigabe/<?php echo $zgvpruefung->prestudent_id ?>">
		<button>Freigabe an Studiengang</button>
	</a>
	<?php endif; ?>
<?php endforeach ?>
<table border="1">
	<thead>
	<th>Datum</th>
	<th>Aktivität</th>
	<th>User</th>
	</thead>
	<tbody>
	<?php foreach ($logs as $log): ?>
		<tr>
			<td><?php echo date_format(date_create($log->zeitpunkt), 'd.m.Y H:i:s') ?></td>
			<td><?php echo $log->logdata->name ?></td>
			<td><?php echo $log->insertvon ?></td>
		</tr>
	<?php endforeach ?>
	</tbody>
</table>

<table border="1">
	<thead>
	<th>Datum</th>
	<th>Notiz</th>
	<th>User</th>
	</thead>
	<tbody>

	<?php foreach ($notizen as $notiz): ?>
		<tr>
			<td><?php echo date_format(date_create($notiz->insertamum), 'd.m.Y H:i:s') ?></td>
			<td><?php echo $notiz->titel ?></td>
			<td><?php echo $notiz->verfasser_uid ?></td>
		</tr>
	<?php endforeach ?>
	</tbody>
</table>
<form method="post" action="../saveNotiz/<?php echo $person->person_id ?>">
	<label>Notiz:</label>
	<br />
	Titel: <input type="text" name="notiztitel" />
	<br />
	Text: <textarea name="notiz" rows="10" cols="32"></textarea>
	<button type="submit">Speichern</button>
</form>

</body>
<script>
	function onFormalGeprueftChange(formal_geprueft, akte_id, person_id)
	{
		window.location = "../saveFormalGeprueft?akte_id=" + akte_id + "&formal_geprueft=" + formal_geprueft + "&person_id=" + person_id;
	}
</script>
