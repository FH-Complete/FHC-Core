<div class="panel-group">
	<?php
	$unique_studsemester = array();
	$first = true;
	$fit_programme_studiengaenge = array(10021, 10027);
	foreach ($zgvpruefungen as $zgvpruefung):
		$infoonly = $zgvpruefung->infoonly;
		$studiengangkurzbz = $studiengangbezeichnung = $studiengangtyp = '';

		$studiensemester = isset($zgvpruefung->prestudentstatus->studiensemester_kurzbz) ? $zgvpruefung->prestudentstatus->studiensemester_kurzbz : '';
		$studiengang_kz = isset($zgvpruefung->studiengang_kz) ? $zgvpruefung->studiengang_kz : '';
		$studiengangtyp = isset($zgvpruefung->studiengangtyp) ? $zgvpruefung->studiengangtyp : '';

		if (empty($zgvpruefung->prestudentstatus->studiengangkurzbzlang))
		{
			if (!empty($zgvpruefung->studiengang))
				$studiengangkurzbz = $zgvpruefung->studiengang;
		}
		else
		{
			$studiengangkurzbz = $zgvpruefung->prestudentstatus->studiengangkurzbzlang;
		}

		if (empty($zgvpruefung->prestudentstatus->studiengangbezeichnung))
		{
			if (!empty($zgvpruefung->studiengangbezeichnung))
				$studiengangbezeichnung = $zgvpruefung->studiengangbezeichnung;
		}
		else
		{
			$studiengangbezeichnung = $zgvpruefung->prestudentstatus->studiengangbezeichnung;
		}

		//set bootstrap columns for zgv form
		$columns = array(3, 3, 3, 3);

		if (!$infoonly && isset($zgvpruefung->prestudentstatus->bewerbungsnachfrist) && isset($zgvpruefung->prestudentstatus->bewerbungstermin))
		{
            $headercolumns = $zgvpruefung->hasBewerber === true ? array(3, 5, 4) : array(4, 8);
		}
		else
        {
		    $headercolumns = $zgvpruefung->hasBewerber === true ? array(4, 4, 4) : array(7, 5);
        }

		if (!$first)
			echo '<br />';

		if (!in_array($studiensemester, $unique_studsemester)):
			$unique_studsemester[] = $studiensemester;

			if (!$first)
				echo '<br/>';

			if (isEmptyString($studiensemester)):
		?>
				<h4 class="headercolorbg text-center"><?php echo $this->p->t('global', 'ohne').' '.$this->p->t('lehre', 'studiensemester') ?></h4>
			<?php else: ?>
				<h4 class="headercolorbg text-center"><?php echo $studiensemester; ?></h4>
			<?php endif; ?>
		<?php endif; ?>
		<div class="panel panel-default">
			<div class="panel-heading">
				<div class="row">
					<div class="col-xs-<?php echo $headercolumns[0]; ?>">
						<h4 class="panel-title">
							<a data-toggle="collapse"
							   href="#collapse<?php echo $zgvpruefung->prestudent_id ?>">
								<?php echo $studiengangkurzbz.' - '.$studiengangbezeichnung.' | '.(isset($zgvpruefung->prestudentstatus->status_kurzbz) ? $zgvpruefung->prestudentstatus->status_kurzbz : '');
								?></a>
						</h4>
					</div>
                    <?php if($zgvpruefung->hasBewerber === true): ?>
                    <div class="col-xs-<?php echo $headercolumns[2]; ?> text-center text-danger">
                        <?php echo $this->p->t('infocenter', 'rtErgebnisExistiert') ?>
                    </div>
                    <?php endif; ?>
					<?php
					$changeup = isset($zgvpruefung->changeup) && $zgvpruefung->changeup === true;
					$changedown = isset($zgvpruefung->changedown) && $zgvpruefung->changedown === true;
					?>
					<div class="col-xs-<?php echo $headercolumns[1]; ?> text-right">
					<?php if (isset($zgvpruefung->prestudentstatus->status_kurzbz) && $zgvpruefung->prestudentstatus->status_kurzbz === 'Interessent'): ?>
						<span<?php echo $changeup || $changedown ? ' class="zgvheaderbeforeprio"' : ''?>>
						<?php if ($infoonly): ?>
							<?php if (isset($zgvpruefung->prestudentstatus->bestaetigtam)): ?>
								<i class="fa fa-check text-success"></i>
								<?php if (isset($zgvpruefung->prestudentstatus->statusgrund_id))
										echo  $this->p->t('global', 'anStudiengangFreigegeben').(isset($zgvpruefung->prestudentstatus->bezeichnung_statusgrund[0]) ? ' ('.$zgvpruefung->prestudentstatus->bezeichnung_statusgrund[0].')' : '');
									else
										echo  $this->p->t('global', 'zumReihungstestFreigegeben');
									?>
							<?php endif; ?>
						<?php else: ?>
							<?php echo ucfirst($this->p->t('infocenter', 'bewerbung')) . ' ' . $this->p->t('global', 'abgeschickt') . ':&nbsp;'.(isset($zgvpruefung->prestudentstatus->bewerbung_abgeschicktamum) ? '<i class="fa fa-check text-success"></i>' : '<i class="fa fa-times text-danger"></i>'); ?>
							<?php echo (isset($zgvpruefung->prestudentstatus->bewerbungsnachfrist) ? ' | ' . $this->p->t('infocenter', 'nachfrist') . ':&nbsp;' . date_format(date_create($zgvpruefung->prestudentstatus->bewerbungsnachfrist), 'd.m.Y') : ''); ?>
							<?php echo (isset($zgvpruefung->prestudentstatus->bewerbungstermin) ? ' | ' . $this->p->t('infocenter', 'bewerbungsfrist') . ':&nbsp;' . date_format(date_create($zgvpruefung->prestudentstatus->bewerbungstermin), 'd.m.Y') : ''); ?>
						<?php endif; ?>
						<?php
							echo ' | ' . ucfirst($this->p->t('infocenter', 'priorisierung')) . ': ';
							echo isset($zgvpruefung->priorisierung) ? $zgvpruefung->priorisierung : $this->p->t('global', 'nichtvorhanden'); ?>
						</span>
						<?php
						if ($changeup || $changedown):
							$topup = $changeup && $changedown ? 'priotogetherup' : 'prioalone';
							$topdown = $changeup && $changedown ? 'priotogetherdown' : 'prioalone';
						?>
						<span class="zgvheaderrightprio">
							<?php if ($changeup): ?>
								<button id="prioup_<?php echo $zgvpruefung->prestudent_id ?>" class="prio prioup <?php echo $topup ?>">
									<span class="fa fa-caret-up prioarrow"></span>
								</button>
									<?php endif; ?>
							<?php if ($changedown): ?>
								<button id="priodown_<?php echo $zgvpruefung->prestudent_id ?>" class="prio priodown <?php echo $topdown ?>">
									<span class="fa fa-caret-down prioarrow" id="priodown_<?php echo $zgvpruefung->prestudent_id ?>"></span>
								</button>
							<?php endif; ?>
						<?php endif; ?>
						</span>
					<?php endif; ?>
					</div>
				</div>
			</div>
			<div id="collapse<?php echo $zgvpruefung->prestudent_id ?>"
				 class="panel-collapse collapse<?php echo $infoonly ? '' : ' in' ?>">
				<div class="panel-body">
					<form action="javascript:void(0);" class="zgvform" id="zgvform_<?php echo $zgvpruefung->prestudent_id ?>">
						<input type="hidden" name="prestudentid" value="<?php echo $zgvpruefung->prestudent_id ?>" class="prestudentidinput">
						<div class="row">
							<div class="col-lg-<?php echo $columns[0] ?>">
								<div class="form-group">
									<label><?php echo  ucfirst($this->p->t('global', 'letzterStatus')) . ':' ?></label>
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
									<label><?php echo  ucfirst($this->p->t('lehre', 'studiensemester')) . ':' ?></label>
									<?php echo $studiensemester ?>
								</div>
							</div>
							<div class="col-lg-<?php echo $columns[2] ?>">
								<div class="form-group form-inline">
									<label><?php echo  ucfirst($this->p->t('lehre', 'ausbildungssemester')) . ':' ?></label>
									<?php if (isset($zgvpruefung->prestudentstatus->ausbildungssemester)): ?>
										<?php if ($infoonly): ?>
											<input id="ausbildungssem_<?php echo $zgvpruefung->prestudent_id ?>" value="<?php echo $zgvpruefung->prestudentstatus->ausbildungssemester?>" type="hidden">
											<?php echo $zgvpruefung->prestudentstatus->ausbildungssemester?>
										<?php else:?>
											<select class="ausbildungssemselect" name="ausbildungssemester" id="ausbildungssem_<?php echo $zgvpruefung->prestudent_id ?>">
												<?php $maxsemester = isset($zgvpruefung->prestudentstatus->regelstudiendauer) ? intval($zgvpruefung->prestudentstatus->regelstudiendauer) : 10 ?>
													<?php for ($i = 1; $i <= $maxsemester; $i++): ?>
														<option value="<?php echo $i ?>"<?php echo $i === intval($zgvpruefung->prestudentstatus->ausbildungssemester) ? ' selected' : '' ?>><?php echo $i ?></option>
													<?php endfor; ?>
											</select>
										<?php endif; ?>
									<?php endif; ?>
								</div>
							</div>
							<div class="col-lg-<?php echo $columns[3] ?>">
								<div class="form-group">
									<label><?php echo  ucfirst($this->p->t('lehre', 'organisationsform')) . ':' ?></label>
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
										<label><?php echo  $this->p->t('infocenter', 'zgv') . ':' ?></label>
											<?php echo $zgvpruefung->zgv_bez; ?>
										</div>
										<?php else: ?>
										<div class="col-xs-3">
											<label><?php echo  $this->p->t('infocenter', 'zgv') . ':' ?></label>
										</div>
										<?php endif;
											$zgvinfocolumns = $infoonly ? 4 : 9;
										?>
										<div class="col-xs-<?php echo $zgvinfocolumns; ?> text-right zgvinfo" id="zgvinfo_<?php echo $zgvpruefung->prestudent_id ?>">
											<a href="javascript:void(0)"><i class="fa fa-info-circle"></i> <?php echo  $this->p->t('infocenter', 'zgv') ?> <?php echo $studiengangkurzbz ?></a>
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
									<label><?php echo  $this->p->t('infocenter', 'zgv') . ' ' . $this->p->t('person', 'ort') . ':'?></label>
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
									<label><?php echo  $this->p->t('infocenter', 'zgv') . ' ' . $this->p->t('global', 'datum') . ':'?></label>
									<?php
									$zgvdatum = isEmptyString($zgvpruefung->zgvdatum) ? "" : date_format(date_create($zgvpruefung->zgvdatum), 'd.m.Y');
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
									<label><?php echo  $this->p->t('infocenter', 'zgv') . ' ' . $this->p->t('person', 'nation') . ':'?></label>
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
						<?php if ($studiengangtyp === 'm') : ?>
							<div class="row">
								<div class="col-lg-<?php echo $columns[0] ?>">
									<div class="form-group"><label><?php echo  $this->p->t('infocenter', 'zgv') . ' ' . $this->p->t('lehre','master') . ':'?></label>
										<?php
										if ($infoonly)
											echo $zgvpruefung->zgvmas_bez;
										else
											echo $this->widgetlib->widget(
												'Zgvmaster_widget',
												array(DropdownWidget::SELECTED_ELEMENT => $zgvpruefung->zgvmas_code),
												array('name' => 'zgvmas', 'id' => 'zgvmas_'.$zgvpruefung->prestudent_id)
											); ?>
									</div>
								</div>
								<div class="col-lg-<?php echo $columns[1] ?>">
									<div class="form-group">
										<label><?php echo  $this->p->t('infocenter', 'zgv') . ' ' . $this->p->t('lehre', 'master') . ' ' . $this->p->t('person', 'ort') . ':'?></label>
										<?php if ($infoonly):
											echo $zgvpruefung->zgvmaort;
										else:
											?>
											<input type="text" class="form-control"
												   value="<?php echo $zgvpruefung->zgvmaort ?>"
												   name="zgvmaort"
												   id="zgvmaort_<?php echo $zgvpruefung->prestudent_id ?>">
										<?php endif; ?>
									</div>
								</div>
								<div class="col-lg-<?php echo $columns[2] ?>">
									<div class="form-group">
										<label><?php echo  $this->p->t('infocenter', 'zgv') . ' ' . $this->p->t('lehre', 'master') . ' ' . $this->p->t('global', 'datum') . ':'?></label>
										<?php
										$zgvmadatum = isEmptyString($zgvpruefung->zgvmadatum) ? "" : date_format(date_create($zgvpruefung->zgvmadatum), 'd.m.Y');
										if ($infoonly):
											echo $zgvmadatum;
										else:
											?>
											<input type="text"
												   class="dateinput form-control"
												   value="<?php echo $zgvmadatum ?>"
												   name="zgvmadatum"
												   id="zgvmadatum_<?php echo $zgvpruefung->prestudent_id ?>">
										<?php endif; ?>
									</div>
								</div>
								<div class="col-lg-<?php echo $columns[3] ?>">
									<div class="form-group"><label><?php echo $this->p->t('infocenter', 'zgv') . ' ' . $this->p->t('lehre', 'master') . ' ' . $this->p->t('person', 'nation') . ':'?></label>
										<?php
										if ($infoonly)
											echo $zgvpruefung->zgvmanation_bez;
										else
											echo $this->widgetlib->widget(
												'Nation_widget',
												array(DropdownWidget::SELECTED_ELEMENT => $zgvpruefung->zgvmanation_code),
												array('name' => 'zgvmanation', 'id' => 'zgvmanation_'.$zgvpruefung->prestudent_id)
											); ?>
									</div>
								</div>
							</div>
							<!-- show only master zgv if master studiengang - end -->
						<?php endif; ?>
					</form>
					<?php if (!$infoonly): ?>
						<div class="row">
							<div class="col-xs-6 text-left">
								<button type="button" class="btn btn-default zgvUebernehmen" id="zgvUebernehmen_<?php echo $zgvpruefung->prestudent_id ?>">
									<?php echo $this->p->t('infocenter', 'letzteZgvUebernehmen') ?>
								</button>
							</div>
							<div class="col-xs-6 text-right">
								<button type="submit" class="btn btn-default saveZgv" id="zgvSpeichern_<?php echo $zgvpruefung->prestudent_id ?>">
									<?php echo  $this->p->t('ui', 'speichern') ?>
								</button>
							</div>
						</div>
					<?php endif; ?>
					<br />
					<?php
					if (isset($zgvpruefung->prestudentUdfs))
					{
						echo $this->udflib->UDFWidget(
							array(
								UDFLib::UDF_UNIQUE_ID => 'infocenterPrestudentUDFs_'.$zgvpruefung->prestudent_id,
								UDFLib::REQUIRED_PERMISSIONS_PARAMETER => 'infocenter',
								UDFLib::SCHEMA_ARG_NAME => 'public',
								UDFLib::TABLE_ARG_NAME => 'tbl_prestudent',
								UDFLib::PRIMARY_KEY_NAME => 'prestudent_id',
								UDFLib::PRIMARY_KEY_VALUE => $zgvpruefung->prestudent_id,
								UDFLib::UDFS_ARG_NAME => $zgvpruefung->prestudentUdfs
							)
						);
					}
					?>
				</div>

				<?php
				//Prestudenten cannot be abgewiesen or freigegeben if already done
				if (!$infoonly) :
					?>
					<div class="panel-footer solidtop">
						<div class="row">
							<div class="col-lg-4 text-left">
								<div class="form-inline">
									<div class="input-group" id="absgstatusgrselect_<?php echo $zgvpruefung->prestudent_id ?>">
										<select name="absgstatusgrund"
												class="d-inline float-right"
												required>
											<option value="null"
													selected="selected"><?php echo ucfirst($this->p->t('infocenter', 'absagegrund')) . '...' ?>
											</option>
											<?php foreach ($abwstatusgruende as $statusgrund): ?>
												<option value="<?php echo $statusgrund->statusgrund_id ?>"><?php echo $statusgrund->bezeichnung_mehrsprachig[0] ?></option>
											<?php endforeach ?>
										</select>
										<span class="input-group-btn">
											<button type="button"
													class="btn btn-default absageBtn" id="absagebtn_<?php echo $zgvpruefung->prestudent_id ?>">
												<?php echo  $this->p->t('ui', 'absagen') ?>
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
														id="absageModalLabel"><?php echo  $this->p->t('infocenter', 'absageBestaetigen') ?></h4>
												</div>
												<div class="modal-body">
													<?php echo  $this->p->t('infocenter', 'absageBestaetigenTxt') ?>
												</div>
												<div class="modal-footer">
													<button type="button"
															class="btn btn-default"
															data-dismiss="modal">
														<?php echo  $this->p->t('ui', 'abbrechen') ?>
													</button>
													<button class="btn btn-primary saveAbsage" id="saveAbsage_<?php echo $zgvpruefung->prestudent_id ?>">
														<?php echo  $this->p->t('infocenter', 'interessentAbweisen') ?>
													</button>
												</div>
											</div>
											<!-- /.modal-content -->
										</div>
										<!-- /.modal-dialog -->
									</div>
								</div>
							</div><!-- /.column-absage -->
								<?php
								$disabled = $disabledStg = $disabledTxt = $disabledStgTxt = '';
								if (isEmptyString($zgvpruefung->prestudentstatus->bewerbung_abgeschicktamum))
								{
									$disabled = $disabledStg = 'disabled';
									$disabledTxt = $disabledStgTxt = $this->p->t('infocenter', 'bewerbungMussAbgeschickt');
								}

								if ($studiengangtyp !== 'b')
								{
									$disabled = 'disabled';
									$disabledTxt = $this->p->t('infocenter', 'nurBachelorFreigeben');

									// FIT-Lehrgänge: exceptions, can be freigegeben in Infocenter
									if (!in_array($studiengang_kz, $fit_programme_studiengaenge))
									{
										$disabledStg = 'disabled';
										$disabledStgTxt = $this->p->t('infocenter', 'nurBachelorFreigeben');
									}
								}
								?>
							<div class="col-lg-8 text-right">
								<div class="form-inline">
									<div class="input-group frgstatusgrselect" id="frgstatusgrselect_<?php echo $zgvpruefung->prestudent_id ?>">
										<select name="frgstatusgrund"
												class="d-inline float-right"
												<?php echo $disabledStg ?>
												required>
											<option value="null"
													selected="selected"><?php echo ucfirst($this->p->t('ui', 'freigabeart')) . '...' ?>
											</option>
											<?php foreach ($intstatusgruende as $statusgrund): ?>
												<option value="<?php echo $statusgrund->statusgrund_id ?>"><?php echo $statusgrund->bezeichnung_mehrsprachig[0] ?></option>
											<?php endforeach ?>
										</select>
										<span class="input-group-btn">
											<button class="btn btn-default freigabebtnstg" <?php echo $disabledStg ?> id="freigabebtnstg_<?php echo $zgvpruefung->prestudent_id ?>"
													data-toggle="tooltip" title="<?php echo $disabledStgTxt ?>">
												<?php echo  $this->p->t('ui', 'freigabeAnStudiengang') ?>
											</button>
										</span>
									</div>
									<div class="input-group" id="igrfrgbtn">
										<button type="button" id="freigabebtn_<?php echo $zgvpruefung->prestudent_id ?>" class="btn btn-default freigabebtn" <?php echo $disabled ?>
												data-toggle="tooltip" title="<?php echo $disabledTxt ?>">
											<?php echo  $this->p->t('ui', 'freigabeZumReihungstest') ?>
										</button>
									</div>
								</div>
							</div>
							<div class="modal fade freigabeModal" id="freigabeModal_<?php echo $zgvpruefung->prestudent_id ?>" tabindex="-1"
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
												<?php echo $this->p->t('infocenter', 'freigabeBestaetigen') ?>
												<span id="freigabeModalStgr_<?php echo $zgvpruefung->prestudent_id ?>"></span>
											</h4>
										</div>
										<div class="modal-body">
											<?php echo $this->p->t('infocenter', 'interessentFreigebenTxt') ?>
										</div>
										<div class="modal-footer">
											<button type="button"
													class="btn btn-default"
													data-dismiss="modal"><?php echo  $this->p->t('ui', 'abbrechen') ?>
											</button>
											<button type="button"
													class="btn btn-primary saveFreigabe" id="saveFreigabe_<?php echo $zgvpruefung->prestudent_id ?>">
												<?php echo $this->p->t('infocenter', 'interessentFreigeben') ?>
											</button>
											<button type="button"
													class="btn btn-primary saveStgFreigabe" id="saveStgFreigabe_<?php echo $zgvpruefung->prestudent_id ?>">
												<?php echo $this->p->t('infocenter', 'interessentFreigeben') ?>
											</button>
										</div>
									</div><!-- /.modal-content -->
								</div><!-- /.modal-dialog -->
							</div><!-- /.modal-fade -->
						</div><!-- /.row -->
					</div><!-- /.panel-footer -->
				<?php elseif (isset($zgvpruefung->prestudentstatus->status_kurzbz) && $zgvpruefung->prestudentstatus->status_kurzbz === 'Interessent'): ?>
					<div class="panel-footer solidtop">
						<div class="row">
							<div class="col-lg-12 text-left">
								<?php echo isset($zgvpruefung->prestudentstatus->bestaetigtam) ? '<i class="fa fa-check text-success"></i>' : '<i class="fa fa-check text-danger"></i>' ?>
								<label>
									<?php if (isset($zgvpruefung->prestudentstatus->statusgrund_id))
											echo  $this->p->t('global', 'anStudiengangFreigegeben').(isset($zgvpruefung->prestudentstatus->bezeichnung_statusgrund[0]) ? ' ('.$zgvpruefung->prestudentstatus->bezeichnung_statusgrund[0].')' : '');
										else
											echo  $this->p->t('global', 'zumReihungstestFreigegeben');
									?>
								</label>
							</div>
						</div><!-- /.row -->
					</div><!-- /.panel-footer -->
				<?php endif; //end if infoonly
				?>
			</div><!-- /.div collapse -->
		</div><!-- /.panel -->
	<?php
		$first = false;
		endforeach; // end foreach zgvpruefungen
	?>
</div><!-- /.panel-group -->
