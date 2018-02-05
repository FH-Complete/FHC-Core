<?php
$this->load->view(
	'templates/FHC-Header',
	array(
		'title' => 'InfocenterDetails',
		'jquery' => true,
		'bootstrap' => true,
		'fontawesome' => true,
		'jqueryui' => true,
		'tablesorter' => true,
		'sbadmintemplate' => true,
		'customCSSs' => array('skin/admintemplate.css', 'skin/tablesort_bootstrap.css')
	)
);
?>
<body>
<div id="wrapper">
	<?php
	echo $this->widgetlib->widget(
		'NavigationWidget',
		array(
			'navigationHeader' => $navigationHeaderArray,
			'navigationMenu' => $navigationMenuArray
		)
	);
	?>
	<div id="page-wrapper">
		<div class="container-fluid">
			<div class="row">
				<div class="col-lg-12">
					<h3 class="page-header">Infocenter
						Details: <?php echo $stammdaten->vorname.' '.$stammdaten->nachname ?>
					</h3>
				</div>
			</div>
			<section>
				<div class="row">
					<div class="col-lg-12">
						<div class="panel panel-primary">
							<div class="panel-heading text-center"><h4>Stammdaten</h4></div>
							<div class="panel-body">
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
														<?php echo ($adresse->heimatadresse === true ? 'Heimatadresse' : '').($adresse->heimatadresse === true && $adresse->rechnungsadresse === true ? ', ' : '').($adresse->rechnungsadresse === true ? 'Rechnungsadresse' : ''); ?>
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
													<a
															href="<?php echo base_url('addons/bewerbung/cis/registration.php?code='.html_escape($stammdaten->zugangscode)) ?>"
															target='_blank'><i
																class="glyphicon glyphicon-new-window"></i>&nbsp;Zugang
														Bewerbung</a>
												</div>
											<?php endif; ?>
										</div>
									</div> <!-- ./column -->
								</div> <!-- ./row -->
							</div> <!-- ./panel-body -->
						</div> <!-- ./panel -->
					</div> <!-- ./main column -->
				</div> <!-- ./main row -->
			</section>
			<section>
				<div class="row">
					<div class="col-lg-12">
						<div class="panel panel-primary">
							<a name="DokPruef"></a><!-- anchor for jumping to the section -->
							<div class="panel-heading text-center"><h4>Dokumentenpr&uuml;fung</h4></div>
							<div class="panel-body">
								<div class="table-responsive">
									<table id="doctable" class="table table-bordered">
										<thead>
										<th>Name</th>
										<th>Typ</th>
										<th>Uploaddatum</th>
										<th>Ausstellungsnation</th>
										<th>Formal gepr&uuml;ft</th>
										</thead>
										<tbody>
										<?php
										foreach ($dokumente as $dokument):
											$geprueft = isset($dokument->formal_geprueft_amum) ? "checked" : "";
											?>
											<tr>
												<td>
													<a href="../outputAkteContent/<?php echo $dokument->akte_id ?>"><?php echo empty($dokument->titel) ? $dokument->bezeichnung : $dokument->titel ?></a>
												</td>
												<td><?php echo $dokument->dokument_bezeichnung ?></td>
												<td><?php echo date_format(date_create($dokument->erstelltam), 'd.m.Y') ?></td>
												<td><?php echo $dokument->langtext ?></td>
												<td>
													<input type="checkbox" class="form-check-input"
														   id="prchkbx<?php echo $dokument->akte_id ?>" <?php echo $geprueft ?> />
													<?php echo isset($dokument->formal_geprueft_amum) ? date_format(date_create($dokument->formal_geprueft_amum), 'd.m.Y') : ''; ?>
												</td>
											</tr>
										<?php endforeach ?>
										</tbody>
									</table>
								</div>
								<?php if (count($dokumente_nachgereicht) > 0): ?>
									<br/>
									<p>Nachzureichende Dokumente:</p>
									<table id="nachgdoctable" class="table table-bordered">
										<thead>
										<th>Typ</th>
										<th>Nachzureichen am</th>
										<th>Ausstellungsnation</th>
										<th>Anmerkung</th>
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
							</div> <!-- ./panel-body -->
						</div> <!-- ./panel -->
					</div> <!-- ./column -->
				</div> <!-- ./row -->
			</section>
			<section>
				<div class="row">
					<div class="col-md-12">
						<div class="panel panel-primary">
							<div class="panel-heading text-center">
								<a name="ZgvPruef"></a>
								<h4>ZGV-Pr&uuml;fung</h4>
							</div>
							<div class="panel-body">
								<div class="panel-group">
									<?php
									foreach ($zgvpruefungen as $zgvpruefung):
										$infoonly = $zgvpruefung->infoonly;
										//set bootstrap columns
										$columns = array(4, 3, 2, 3);
										?>
										<br/>
										<div class="panel panel-default">
											<div class="panel-heading">
												<div class="row">
													<div class="col-lg-8">
														<h4 class="panel-title">
															<a data-toggle="collapse"
															   href="#collapse<?php echo $zgvpruefung->prestudent_id ?>"><?php echo $zgvpruefung->studiengang.' - '.$zgvpruefung->studiengangbezeichnung.' | '.(isset($zgvpruefung->prestudentstatus->status_kurzbz) ? $zgvpruefung->prestudentstatus->status_kurzbz : '');
																?></a>
														</h4>
													</div>
													<?php if (isset($zgvpruefung->prestudentstatus->status_kurzbz) && $zgvpruefung->prestudentstatus->status_kurzbz === 'Interessent' && !$infoonly): ?>
														<div class="col-lg-4 text-right">
															<?php echo 'Bewerbung abgeschickt: '.(isset($zgvpruefung->prestudentstatus->bewerbung_abgeschicktamum) ? '<i class="fa fa-check" style="color:green"></i>' : '<i class="fa fa-times" style="color:red"></i>'); ?>
														</div>
													<?php endif ?>
												</div>
											</div>
											<div id="collapse<?php echo $zgvpruefung->prestudent_id ?>"
												 class="panel-collapse collapse<?php echo $infoonly ? '' : ' in' ?>">
												<div class="panel-body">
													<form method="post"
														  action="../saveZgvPruefung/<?php echo $zgvpruefung->prestudent_id ?>">
														<div class="row">
															<div class="col-lg-<?php echo $columns[0] ?>">
																<div class="form-group">
																	<label>Letzter Status: </label>
																	<?php
																	if (isset($zgvpruefung->prestudentstatus->status_kurzbz))
																	{
																		echo $zgvpruefung->prestudentstatus->status_kurzbz.(isset($zgvpruefung->prestudentstatus->bezeichnung_statusgrund[0]) && $zgvpruefung->prestudentstatus->status_kurzbz === 'Abgewiesener' ? ' ('.$zgvpruefung->prestudentstatus->bezeichnung_statusgrund[0].')' : '');
																	}
																	?>
																</div>
															</div>
															<div class="col-lg-<?php echo $columns[1] ?>">
																<div class="form-group">
																	<label>Studiensemester: </label>
																	<?php echo isset($zgvpruefung->prestudentstatus->studiensemester_kurzbz) ? $zgvpruefung->prestudentstatus->studiensemester_kurzbz : '' ?>
																</div>
															</div>
															<div class="col-lg-<?php echo $columns[2] ?>">
																<div class="form-group">
																	<label><span style="display: inline-block">Ausbildungs</span><span
																				style="display: inline-block">semester: </span></label>
																	<?php echo isset($zgvpruefung->prestudentstatus->ausbildungssemester) ? $zgvpruefung->prestudentstatus->ausbildungssemester : '' ?>
																</div>
															</div>
															<div class="col-lg-<?php echo $columns[3] ?>">
																<div class="form-group">
																	<label>Orgform: </label>
																	<span style="display: inline-block">
																	<?php
																	$separator = (isset($zgvpruefung->prestudentstatus->orgform)) ? ', ' : '';
																	echo (isset($zgvpruefung->prestudentstatus->orgform) ? $zgvpruefung->prestudentstatus->orgform : '')
																		.(isset($zgvpruefung->prestudentstatus->sprachedetails->bezeichnung) ? $separator.$zgvpruefung->prestudentstatus->sprachedetails->bezeichnung[0] : '')
																		.(isset($zgvpruefung->prestudentstatus->alternative) ? ' ('.$zgvpruefung->prestudentstatus->alternative.')' : '') ?>
																	</span>
																</div>
															</div>
														</div>
														<div class="row">
															<div class="col-lg-<?php echo $columns[0] ?>">
																<div class="form-group">
																	<label>ZGV: </label>
																	<?php if ($infoonly)
																		echo $zgvpruefung->zgv_bez;
																	else
																		echo $this->widgetlib->widget(
																			'Zgv_widget',
																			array(DropdownWidget::SELECTED_ELEMENT => $zgvpruefung->zgv_code),
																			array('name' => 'zgv', 'id' => 'zgv')
																		); ?>
																</div>
															</div>
															<div class="col-lg-<?php echo $columns[1] ?>">
																<div class="form-group">
																	<label>ZGV Ort: </label>
																	<?php if ($infoonly):
																		echo html_escape($zgvpruefung->zgvort);
																	else:
																		?>
																		<input type="text" class="form-control"
																			   value="<?php echo $zgvpruefung->zgvort ?>"
																			   name="zgvort">
																	<?php endif; ?>
																</div>
															</div>
															<div class="col-lg-<?php echo $columns[2] ?>">
																<div class="form-group">
																	<label>ZGV Datum: </label>
																	<?php if ($infoonly):
																		echo date_format(date_create($zgvpruefung->zgvdatum), 'd.m.Y');
																	else:
																		?>
																		<input type="text"
																			   class="dateinput form-control"
																			   value="<?php echo empty($zgvpruefung->zgvdatum) ? "" : date_format(date_create($zgvpruefung->zgvdatum), 'd.m.Y') ?>"
																			   name="zgvdatum">
																	<?php endif; ?>
																</div>
															</div>
															<div class="col-lg-<?php echo $columns[3] ?>">
																<div class="form-group">
																	<label>ZGV Nation: </label>
																	<?php if ($infoonly)
																		echo $zgvpruefung->zgvnation_bez;
																	else
																		echo $this->widgetlib->widget(
																			'Nation_widget',
																			array(DropdownWidget::SELECTED_ELEMENT => $zgvpruefung->zgvnation_code),
																			array('name' => 'zgvnation', 'id' => 'zgvnation')
																		); ?>
																</div>
															</div>
														</div>
														<!-- show only master zgv if master studiengang - start -->
														<?php if ($zgvpruefung->studiengangtyp === 'm') : ?>
															<div class="row">
																<div class="col-lg-<?php echo $columns[0] ?>">
																	<div class="form-group"><label>ZGV Master: </label>
																		<?php
																		if ($infoonly)
																			echo $zgvpruefung->zgvmas_bez;
																		else
																			echo $this->widgetlib->widget(
																				'Zgvmaster_widget',
																				array(DropdownWidget::SELECTED_ELEMENT => $zgvpruefung->zgvmas_code),
																				array('name' => 'zgvmas', 'id' => 'zgvmas')
																			); ?>
																	</div>
																</div>
																<div class="col-lg-<?php echo $columns[1] ?>">
																	<div class="form-group">
																		<label>ZGV Master Ort: </label>
																		<?php if ($infoonly):
																			echo $zgvpruefung->zgvmaort;
																		else:
																			?>
																			<input type="text" class="form-control"
																				   value="<?php echo $zgvpruefung->zgvmaort ?>"
																				   name="zgvmaort">
																		<?php endif; ?>
																	</div>
																</div>
																<div class="col-lg-<?php echo $columns[2] ?>">
																	<div class="form-group">
																		<label>ZGV Master Datum: </label>
																		<?php if ($infoonly):
																			echo date_format(date_create($zgvpruefung->zgvmadatum), 'd.m.Y');
																		else:
																			?>
																			<input type="text"
																				   class="dateinput form-control"
																				   value="<?php echo empty($zgvpruefung->zgvmadatum) ? "" : date_format(date_create($zgvpruefung->zgvmadatum), 'd.m.Y') ?>"
																				   name="zgvmadatum">
																		<?php endif; ?>
																	</div>
																</div>
																<div class="col-lg-<?php echo $columns[3] ?>">
																	<div class="form-group"><label>ZGV Master
																			Nation: </label>
																		<?php
																		if ($infoonly)
																			echo $zgvpruefung->zgvmanation_bez;
																		else
																			echo $this->widgetlib->widget(
																				'Nation_widget',
																				array(DropdownWidget::SELECTED_ELEMENT => $zgvpruefung->zgvmanation_code),
																				array('name' => 'zgvmanation', 'id' => 'zgvmanation')
																			); ?>
																	</div>
																</div>
															</div>
															<!-- show only master zgv if master studiengang - end -->
														<?php endif; ?>
														<?php if (!$infoonly): ?>
															<div class="row">
																<div class="col-lg-12 text-right">
																	<button type="submit" class="btn btn-default">
																		Speichern
																	</button>
																</div>
															</div>
														<?php endif; ?>
													</form>
												</div>

												<?php
												//Prestudenten cannot be abgewiesen or freigegeben if already done
												if (!$infoonly) :
													?>
													<div class="panel-footer" style="border-top: 1px solid #ddd">
														<div class="row">
															<div class="col-lg-6 text-left">
																<div class="form-inline">
																	<form method="post"
																		  action="../saveAbsage/<?php echo $zgvpruefung->prestudent_id ?>">
																		<div class="input-group" id="statusgrselect">
																			<select name="statusgrund"
																					class="d-inline float-right"
																					required>
																				<option value="null"
																						selected="selected">Absagegrund
																					w&auml;hlen...
																				</option>
																				<?php foreach ($statusgruende as $statusgrund): ?>
																					<option value="<?php echo $statusgrund->statusgrund_id ?>"><?php echo $statusgrund->bezeichnung_mehrsprachig[0] ?></option>
																				<?php endforeach ?>
																			</select>
																			<span class="input-group-btn">
																				<button id="absageBtn" type="button"
																						class="btn btn-default"
																						data-toggle="modal"
																						data-target="#absageModal">
																					Absage
																				</button>
																			</span>
																		</div>
																		<div class="modal fade" id="absageModal"
																			 tabindex="-1"
																			 role="dialog"
																			 aria-labelledby="absageModalLabel"
																			 aria-hidden="true">
																			<div class="modal-dialog">
																				<div class="modal-content">
																					<div class="modal-header">
																						<button type="button"
																								class="close"
																								data-dismiss="modal"
																								aria-hidden="true">
																							&times;
																						</button>
																						<h4 class="modal-title"
																							id="absageModalLabel">Absage
																							best&auml;tigen</h4>
																					</div>
																					<div class="modal-body">
																						Bei Absage von InteressentInnen
																						erhalten
																						diese den Status "Abgewiesener"
																						und<br/>deren
																						Zgvdaten können
																						im Infocenter nicht mehr
																						bearbeitet
																						oder
																						freigegeben werden.
																						<br/>Alle nicht gespeicherten
																						Zgvdaten
																						gehen
																						verloren. Fortfahren?
																					</div>
																					<div class="modal-footer">
																						<button type="button"
																								class="btn btn-default"
																								data-dismiss="modal">
																							Abbrechen
																						</button>
																						<button type="submit"
																								class="btn btn-primary">
																							InteressentIn abweisen
																						</button>
																					</div>
																				</div>
																				<!-- /.modal-content -->
																			</div>
																			<!-- /.modal-dialog -->
																		</div>
																	</form>
																</div>
															</div><!-- /.column-absage -->
															<div class="col-lg-6 text-right">
																<div>
																	<button type="button" class="btn btn-default"
																			data-toggle="modal"
																			data-target="#freigabeModal">
																		Freigabe an Studiengang
																	</button>
																</div>
															</div>
															<div class="modal fade" id="freigabeModal" tabindex="-1"
																 role="dialog"
																 aria-labelledby="freigabeModalLabel"
																 aria-hidden="true">
																<div class="modal-dialog">
																	<div class="modal-content">
																		<div class="modal-header">
																			<button type="button" class="close"
																					data-dismiss="modal"
																					aria-hidden="true">&times;
																			</button>
																			<h4 class="modal-title"
																				id="freigabeModalLabel">
																				Freigabe
																				best&auml;tigen</h4>
																		</div>
																		<div class="modal-body">
																			Bei Freigabe von InteressentInnen wird deren
																			Interessentenstatus bestätigt und<br/>deren
																			Zgvdaten
																			können im
																			Infocenter nicht mehr bearbeitet oder
																			freigegeben
																			werden.
																			<br/>Alle nicht gespeicherten Zgvdaten gehen
																			verloren.
																			Fortfahren?
																		</div>
																		<div class="modal-footer">
																			<button type="button"
																					class="btn btn-default"
																					data-dismiss="modal">Abbrechen
																			</button>
																			<a href="../saveFreigabe/<?php echo $zgvpruefung->prestudent_id ?>">
																				<button type="button"
																						class="btn btn-primary">
																					InteressentIn freigeben
																				</button>
																			</a>
																		</div>
																	</div><!-- /.modal-content -->
																</div><!-- /.modal-dialog -->
															</div><!-- /.modal-fade -->
														</div><!-- /.row -->
													</div><!-- /.panel-footer -->
												<?php elseif (isset($zgvpruefung->prestudentstatus->status_kurzbz) && $zgvpruefung->prestudentstatus->status_kurzbz === 'Interessent'): ?>
													<div class="panel-footer" style="border-top: 1px solid #ddd">
														<div class="row">
															<div class="col-lg-12 text-left">
																<?php echo isset($zgvpruefung->prestudentstatus->bestaetigtam) ? '<i class="fa fa-check" style="color: green"></i>' : '<i class="fa fa-check" style="color: red"></i>' ?>
																<label>An Studiengang freigegeben</label>
															</div>
														</div><!-- /.row -->
													</div><!-- /.panel-footer -->
												<?php endif; //end if infoonly
												?>
											</div><!-- /.div collapse -->
										</div><!-- /.panel -->
									<?php endforeach; // end foreach zgvpruefungen?>
								</div><!-- /.panel-group -->
							</div><!-- /.main panel body -->
						</div> <!-- /.main panel-->
					</div> <!-- /.column freigabe-->
				</div> <!-- /.row freigabe-->
			</section>
			<section>
				<div class="row">
					<div class="col-lg-12">
						<div class="panel panel-primary">
							<div class="panel-heading text-center">
								<a name="NotizAkt"></a>
								<h4 class="text-center">Notizen &amp; Aktivit&auml;ten</h4>
							</div>
							<div class="panel-body">
								<div class="row">
									<div class="col-lg-6">
										<form method="post" action="../saveNotiz/<?php echo $stammdaten->person_id ?>">
											<div class="form-group">
												<div class="text-center">
													<label>Notiz hinzuf&uuml;gen</label>
												</div>
												<div class="form-group">
													<label>Titel: </label><input type="text" class="form-control"
																				 name="notiztitel"/>
												</div>
												<div class="form-group">
													<label>Text: </label><textarea name="notiz" class="form-control"
																				   rows="10"
																				   cols="32"></textarea>
												</div>
												<div class="text-right">
													<button type="submit" class="btn btn-default">Speichern</button>
												</div>
											</div>
										</form>
										<table id="notiztable" class="table table-bordered table-hover">
											<thead>
											<th>Datum</th>
											<th>Notiz</th>
											<th>User</th>
											</thead>
											<tbody>

											<?php foreach ($notizen as $notiz): ?>
												<tr data-toggle="tooltip"
													title="<?php echo isset($notiz->text) ? $notiz->text : '' ?>">
													<td><?php echo date_format(date_create($notiz->insertamum), 'd.m.Y H:i:s') ?></td>
													<td><?php echo html_escape($notiz->titel) ?></td>
													<td><?php echo $notiz->verfasser_uid ?></td>
												</tr>
											<?php endforeach ?>
											</tbody>
										</table>
									</div>
									<div class="col-lg-6">
										<table id="logtable" class="table table-bordered table-hover">
											<thead>
											<th>Datum</th>
											<th>Aktivit&auml;t</th>
											<th>User</th>
											</thead>
											<tbody>
											<?php foreach ($logs as $log): ?>
												<tr data-toggle="tooltip"
													title="<?php echo isset($log->logdata->message) ? $log->logdata->message : '' ?>">
													<td><?php echo date_format(date_create($log->zeitpunkt), 'd.m.Y H:i:s') ?></td>
													<td><?php echo isset($log->logdata->name) ? $log->logdata->name : '' ?></td>
													<td><?php echo $log->insertvon ?></td>
												</tr>
											<?php endforeach ?>
											</tbody>
										</table>
									</div> <!-- ./column -->
								</div> <!-- ./row -->
							</div> <!-- ./panel-body -->
						</div> <!-- ./panel -->
					</div> <!-- ./main column -->
				</div> <!-- ./main row -->
			</section>
		</div> <!-- ./container-fluid-->
	</div> <!-- ./page-wrapper-->
</div> <!-- ./wrapper -->

<script>

	$(document).ready(function ()
		{
			//initialise table sorter
			addTablesorter("doctable", [[2, 1], [1, 0]], ["zebra"]);
			addTablesorter("nachgdoctable", [[2, 0], [1, 1]], ["zebra"]);
			addTablesorter("logtable", [[0, 1]], ["filter"]);
			addTablesorter("notiztable", [[0, 1]], ["filter"]);

			//add pager
			togglePager(23, "logtable", "logpager");
			togglePager(10, "notiztable", "notizpager");

			function addTablesorter(tableid, sortList, widgets)
			{
				$("#" + tableid).tablesorter(
					{
						theme: "default",
						dateFormat: "ddmmyyyy",
						sortList: sortList,
						widgets: widgets
					}
				);

				//hide filters if less than 2 datarows (+ 2 for headings and filter row itself)
				if ($("#" + tableid + " tr").length < 4)
				{
					$("#" + tableid + " tr.tablesorter-filter-row").hide();
				}
			}

			function togglePager(size, tableid, pagerid)
			{
				var html =
					'<div id="' + pagerid + '" class="pager"> ' +
					'<form class="form-inline">' +
					'<i class="fa fa-step-backward first"></i>&nbsp;' +
					'<i class="fa fa-backward prev"></i>' +
					'<span class="pagedisplay"></span>' +
					'<i class="fa fa-forward next"></i>&nbsp;' +
					'<i class="fa fa-step-forward last"></i>' +
					'</form>' +
					'</div>';

				var rowcount = $("#" + tableid + " tr").length;

				//not show pager if on first table page
				if (rowcount > size)
				{
					var table = $("#" + tableid);
					table.after(html);

					table.tablesorterPager(
						{
							container: $("#" + pagerid),
							size: size,
							cssDisabled: 'disabled',
							savePages: false,
							output: '{startRow} – {endRow} / {totalRows} Zeilen'
						}
					);
				}
			}

			//initialise datepicker
			$.datepicker.setDefaults($.datepicker.regional['de']);
			$(".dateinput").datepicker({
				"dateFormat": "dd.mm.yy"
			});

			//javascript bootstrap hack - not nice!
			$("select").addClass('form-control');
			$("table").addClass('table-condensed');
			$("#logtable, #notiztable").addClass('table-hover');

			//add submit event to message send link
			$("#sendmsglink").click(
				function()
				{
					$("#sendmsgform").submit();
				}
			);

			//add click events to "formal geprüft" checkboxes
			<?php foreach($dokumente as $dokument): ?>

			if ($("#prchkbx<?php echo $dokument->akte_id; ?>"))
			{
				$("#prchkbx<?php echo $dokument->akte_id; ?>").click(function ()
				{
					window.location = "../saveFormalGeprueft?akte_id=<?php echo $dokument->akte_id; ?>&formal_geprueft=" + this.checked + "&person_id=<?php echo $stammdaten->person_id ?>";
				});
			}
			<?php endforeach ?>

			//prevent opening modal when Statusgrund not chosen
			$("#absageModal").on('show.bs.modal', function (e)
				{
					if ($("[name=statusgrund]").val() === "null")
					{
						$("#statusgrselect").addClass("has-error");
						return e.preventDefault();
					}
				}
			);

			$("[name=statusgrund]").change(function ()
				{
					$("#statusgrselect").removeClass("has-error");
				}
			);
		}
	);
</script>
</body>

<?php $this->load->view('templates/FHC-Footer'); ?>
