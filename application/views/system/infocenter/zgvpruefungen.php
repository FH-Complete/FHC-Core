<div class="panel-group">
	<?php
	foreach ($zgvpruefungen as $zgvpruefung):
		$infoonly = $zgvpruefung->infoonly;
		//set bootstrap columns for zgv form
		$columns = array(4, 3, 2, 3);
		$headercolumns = array(7, 5);
		if (!$infoonly && isset($zgvpruefung->prestudentstatus->bewerbungsnachfrist) && isset($zgvpruefung->prestudentstatus->bewerbungstermin))
		{
			$headercolumns[0] = 5;
			$headercolumns[1] = 7;
		}
		?>
		<br/>
		<div class="panel panel-default">
			<div class="panel-heading">
				<div class="row">
					<div class="col-xs-<?php echo $headercolumns[0]; ?>">
						<h4 class="panel-title">
							<a data-toggle="collapse"
							   href="#collapse<?php echo $zgvpruefung->prestudent_id ?>"><?php echo $zgvpruefung->studiengang.' - '.$zgvpruefung->studiengangbezeichnung.' | '.(isset($zgvpruefung->prestudentstatus->status_kurzbz) ? $zgvpruefung->prestudentstatus->status_kurzbz : '');
								?></a>
						</h4>
					</div>
					<?php if (isset($zgvpruefung->prestudentstatus->status_kurzbz) && $zgvpruefung->prestudentstatus->status_kurzbz === 'Interessent'/* && !$infoonly*/): ?>
						<?php if ($infoonly): ?>
							<?php if (isset($zgvpruefung->prestudentstatus->bestaetigtam)): ?>
							<div class="col-xs-<?php echo $headercolumns[1]; ?> text-right">
								<i class="fa fa-check" style="color: green"></i>
								An Studiengang freigegeben
							</div>
							<?php endif; ?>
						<?php else: ?>
						<div class="col-xs-<?php echo $headercolumns[1]; ?> text-right">
							<?php echo 'Bewerbung abgeschickt: '.(isset($zgvpruefung->prestudentstatus->bewerbung_abgeschicktamum) ? '<i class="fa fa-check" style="color:green"></i>' : '<i class="fa fa-times" style="color:red"></i>'); ?>
							<?php echo (isset($zgvpruefung->prestudentstatus->bewerbungsnachfrist) ? ' | Nachfrist: '. date_format(date_create($zgvpruefung->prestudentstatus->bewerbungsnachfrist), 'd.m.Y') : ''); ?>
							<?php echo (isset($zgvpruefung->prestudentstatus->bewerbungstermin) ? ' | Bewerbungsfrist: '. date_format(date_create($zgvpruefung->prestudentstatus->bewerbungstermin), 'd.m.Y') : ''); ?>
						</div>
						<?php endif; ?>
					<?php endif; ?>
				</div>
			</div>
			<div id="collapse<?php echo $zgvpruefung->prestudent_id ?>"
				 class="panel-collapse collapse<?php echo $infoonly ? '' : ' in' ?>">
				<div class="panel-body">
					<form method="post"
						  action="#" class="zgvform">
						<input type="hidden" name="prestudentid" value="<?php echo $zgvpruefung->prestudent_id  ?>">
						<div class="row">
							<div class="col-lg-<?php echo $columns[0] ?>">
								<div class="form-group">
									<label>Letzter Status: </label>
									<?php
									if (isset($zgvpruefung->prestudentstatus->status_kurzbz))
									{
										echo $zgvpruefung->prestudentstatus->status_kurzbz.(isset($zgvpruefung->prestudentstatus->bezeichnung_statusgrund[0]) ? ' ('.$zgvpruefung->prestudentstatus->bezeichnung_statusgrund[0].')' : '');
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
									<div class="row">
										<?php if ($infoonly): ?>
										<div class="col-xs-8">
										<label>ZGV:</label>
											<?php echo $zgvpruefung->zgv_bez; ?>
										</div>
										<?php else: ?>
										<div class="col-xs-3">
											<label>ZGV:</label>
										</div>
										<?php endif;
											$zgvinfocolumns = $infoonly ? 4 : 9;
										?>
										<div class="col-xs-<?php echo $zgvinfocolumns; ?> text-right zgvinfo" id="zgvinfo_<?php echo $zgvpruefung->prestudent_id ?>">
											<a href="javascript:void(0)"><i class="fa fa-info-circle"></i> ZGV <?php echo $zgvpruefung->studiengang; ?></a>
										</div>
									</div>
									<?php if (!$infoonly)
										echo $this->widgetlib->widget(
											'Zgv_widget',
											array(DropdownWidget::SELECTED_ELEMENT => $zgvpruefung->zgv_code),
											array('name' => 'zgv', 'id' => 'zgv_'.$zgvpruefung->prestudent_id)
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
											   name="zgvort" id="zgvort_<?php echo $zgvpruefung->prestudent_id ?>">
									<?php endif; ?>
								</div>
							</div>
							<div class="col-lg-<?php echo $columns[2] ?>">
								<div class="form-group">
									<label>ZGV Datum: </label>
									<?php
									$zgvdatum = empty($zgvpruefung->zgvdatum) ? "" : date_format(date_create($zgvpruefung->zgvdatum), 'd.m.Y');
									if ($infoonly):
										echo $zgvdatum;
									else:
										?>
										<input type="text"
											   class="dateinput form-control"
											   value="<?php echo $zgvdatum ?>"
											   name="zgvdatum" id="zgvdatum_<?php echo $zgvpruefung->prestudent_id ?>">
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
											array('name' => 'zgvnation', 'id' => 'zgvnation_'.$zgvpruefung->prestudent_id)
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
										<?php
										$zgvmadatum = empty($zgvpruefung->zgvmadatum) ? "" : date_format(date_create($zgvpruefung->zgvmadatum), 'd.m.Y');
										if ($infoonly):
											echo $zgvmadatum;
										else:
											?>
											<input type="text"
												   class="dateinput form-control"
												   value="<?php echo $zgvmadatum ?>"
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
								<div class="col-xs-6 text-left">
									<button type="button" class="btn btn-default zgvUebernehmen" id="zgvUebernehmen_<?php echo $zgvpruefung->prestudent_id ?>">
										Letzte ZGV &uuml;bernehmen
									</button>
								</div>
								<div class="col-xs-6 text-right">
									<button type="submit" class="btn btn-default saveZgv" id="zgvSpeichern_<?php echo $zgvpruefung->prestudent_id ?>">
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
										<div class="input-group" id="statusgrselect_<?php echo $zgvpruefung->prestudent_id ?>">
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
														data-target="#absageModal_<?php echo $zgvpruefung->prestudent_id ?>">
													Absage
												</button>
											</span>
										</div>
										<div class="modal fade absageModal" id="absageModal_<?php echo $zgvpruefung->prestudent_id ?>"
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
								<?php  
								$disabled = $disabledTxt = '';
								if (empty($zgvpruefung->prestudentstatus->bewerbung_abgeschicktamum))
								{
									$disabled = 'disabled';
									$disabledTxt = 'Die Bewerbung muss erst abgeschickt worden sein.';
								} 
								
								if ($zgvpruefung->studiengangtyp !== 'b')
								{
									$disabled = 'disabled';
									$disabledTxt = 'Nur Bachelorstudiengänge können freigegeben werden.';;
								}
								?>
								<div>
									<button type="button" class="btn btn-default" <?php echo $disabled ?>			
											data-toggle="modal"
											data-target="#freigabeModal_<?php echo $zgvpruefung->prestudent_id ?>"
											data-toggle="tooltip" title="<?php echo $disabledTxt ?>">
										Freigabe an Studiengang
									</button>
								</div>
							</div>
							<div class="modal fade" id="freigabeModal_<?php echo $zgvpruefung->prestudent_id ?>" tabindex="-1"
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
											Infocenter nicht mehr bearbeitet
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