<?php
	$this->load->view(
		'templates/FHC-Header',
		array(
			'title' => 'Info Center',
			'jquery' => true,
			'jqueryui' => true,
			'bootstrap' => true,
			'fontawesome' => true,
			'sbadmintemplate' => true,
			'tablesorter' => true,
			'ajaxlib' => true,
			'filterwidget' => true,
			'navigationwidget' => true,
			'customCSSs' => 'public/css/sbadmin2/tablesort_bootstrap.css',
			'customJSs' => array('public/js/bootstrapper.js', 'public/js/infocenter/infocenterPersonDataset.js')
		)
	);
?>

<body>
	<div id="wrapper">

		<?php echo $this->widgetlib->widget('NavigationWidget'); ?>

		<div id="page-wrapper">
			<div class="container-fluid">
				<div class="row">
					<div class="col-lg-12">
						<h3 class="page-header">Freigegebene Interessenten</h3>
					</div>
				</div>
				<div>
					<?php

						$APP = 'infocenter';
						$NOTBEFORE = '2018-03-01 18:00:00';
						$filterWidgetArray = array(
							'query' => '
							SELECT
									p.person_id AS "PersonId",
									p.vorname AS "Vorname",
									p.nachname AS "Nachname",
									p.gebdatum AS "Gebdatum",
									p.staatsbuergerschaft AS "Nation",
									(
										SELECT zeitpunkt
										FROM system.tbl_log
										WHERE taetigkeit_kurzbz IN(\'bewerbung\',\'kommunikation\')
										AND logdata->>\'name\' NOT IN (\'Login with code\', \'New application\')
										AND person_id = p.person_id
										ORDER BY zeitpunkt DESC
										LIMIT 1
									) AS "LastAction",
									(
										SELECT insertvon
										FROM system.tbl_log
										WHERE taetigkeit_kurzbz IN(\'bewerbung\',\'kommunikation\')
										AND logdata->>\'name\' NOT IN (\'Login with code\', \'New application\')
										AND person_id = p.person_id
										ORDER BY zeitpunkt DESC
										LIMIT 1
									) AS "User/Operator",
									(
										SELECT
											pss.studiensemester_kurzbz
										FROM
											public.tbl_prestudentstatus pss
											INNER JOIN public.tbl_prestudent ps USING(prestudent_id)
											JOIN public.tbl_studiengang USING(studiengang_kz)
										WHERE pss.status_kurzbz = \'Interessent\'
										AND pss.bestaetigtam IS NULL
										AND ps.person_id = p.person_id
										AND tbl_studiengang.typ in(\'b\')
										AND studiensemester_kurzbz IN (
											SELECT studiensemester_kurzbz
											FROM public.tbl_studiensemester
											WHERE ende >= NOW()
										)
										ORDER BY pss.datum DESC, pss.insertamum DESC, pss.ext_id DESC
										LIMIT 1
									) AS "Studiensemester",
									(
										SELECT pss.bewerbung_abgeschicktamum
										FROM
											public.tbl_prestudentstatus pss
											INNER JOIN public.tbl_prestudent ps USING(prestudent_id)
											JOIN public.tbl_studiengang USING(studiengang_kz)
										WHERE pss.status_kurzbz = \'Interessent\'
											AND (pss.bewerbung_abgeschicktamum IS NOT NULL AND pss.bewerbung_abgeschicktamum>=\''.$NOTBEFORE.'\')
											AND pss.bestaetigtam IS NULL
											AND ps.person_id = p.person_id
											AND tbl_studiengang.typ in(\'b\')
											AND studiensemester_kurzbz IN (
												SELECT studiensemester_kurzbz
												FROM public.tbl_studiensemester
												WHERE ende >= NOW()
											)
										ORDER BY pss.datum DESC, pss.insertamum DESC, pss.ext_id DESC
										LIMIT 1
									) AS "SendDate",
									(
										SELECT count(*)
										FROM
											public.tbl_prestudentstatus pss
											INNER JOIN public.tbl_prestudent ps USING(prestudent_id)
											JOIN public.tbl_studiengang USING(studiengang_kz)
										WHERE pss.status_kurzbz = \'Interessent\'
											AND (pss.bewerbung_abgeschicktamum IS NOT NULL AND pss.bewerbung_abgeschicktamum>=\''.$NOTBEFORE.'\')
											AND pss.bestaetigtam IS NULL
											AND ps.person_id = p.person_id
											AND tbl_studiengang.typ in(\'b\')
											AND studiensemester_kurzbz IN (
												SELECT studiensemester_kurzbz
												FROM public.tbl_studiensemester
												WHERE ende >= NOW()
											)
										LIMIT 1
									) AS "AnzahlAbgeschickt",
									array_to_string(
										(
										SELECT array_agg(distinct UPPER(tbl_studiengang.typ || tbl_studiengang.kurzbz))
										FROM
											public.tbl_prestudentstatus pss
											INNER JOIN public.tbl_prestudent ps USING(prestudent_id)
											JOIN public.tbl_studiengang USING(studiengang_kz)
										WHERE pss.status_kurzbz = \'Interessent\'
											AND (pss.bewerbung_abgeschicktamum IS NOT NULL AND pss.bewerbung_abgeschicktamum>=\''.$NOTBEFORE.'\')
											AND pss.bestaetigtam IS NULL
											AND ps.person_id = p.person_id
											AND tbl_studiengang.typ in(\'b\')
											AND studiensemester_kurzbz IN (
												SELECT studiensemester_kurzbz
												FROM public.tbl_studiensemester
												WHERE ende >= NOW()
											)
										LIMIT 1
										),\', \'
									) AS "StgAbgeschickt",
									pl.zeitpunkt AS "LockDate",
									pl.lockuser as "LockUser"
								FROM public.tbl_person p
							LEFT JOIN (SELECT person_id, zeitpunkt, uid as lockuser FROM system.tbl_person_lock WHERE app = \''.$APP.'\') pl USING(person_id)
								WHERE
									EXISTS(
										SELECT 1
										FROM
											public.tbl_prestudent
											JOIN public.tbl_studiengang USING(studiengang_kz)
										WHERE
											person_id=p.person_id
											AND tbl_studiengang.typ in(\'b\')
											AND \'Interessent\' = (SELECT status_kurzbz FROM public.tbl_prestudentstatus
																	WHERE prestudent_id=tbl_prestudent.prestudent_id
																	ORDER BY datum DESC, insertamum DESC, ext_id DESC
																	LIMIT 1
																	)
											AND EXISTS (
												SELECT
													1
												FROM
													public.tbl_prestudentstatus
												WHERE
													prestudent_id = tbl_prestudent.prestudent_id
													AND status_kurzbz = \'Interessent\'
													AND (bestaetigtam IS NOT NULL AND (bewerbung_abgeschicktamum is null OR bewerbung_abgeschicktamum>=\''.$NOTBEFORE.'\'))
													AND studiensemester_kurzbz IN (
														SELECT studiensemester_kurzbz
														FROM public.tbl_studiensemester
														WHERE ende >= NOW()
												)
										)
									)
								ORDER BY "LastAction" ASC
							',
							'checkboxes' => 'PersonId',
							'additionalColumns' => array('Details'),
							'columnsAliases' => array('PersonID','Vorname','Nachname','GebDatum','Nation','Letzte Aktion','Letzter Bearbeiter',
								'StSem','GesendetAm','NumAbgeschickt','Studiengänge','Sperrdatum','GesperrtVon'),
							'formatRaw' => function($datasetRaw) {

								$datasetRaw->{'Details'} = sprintf(
									'<a href="%s/%s?fhc_controller_id=%s">Details</a>',
									site_url('system/infocenter/InfoCenter/showDetails'),
									$datasetRaw->{'PersonId'},
									(isset($_GET['fhc_controller_id'])?$_GET['fhc_controller_id']:'')
								);

								if ($datasetRaw->{'SendDate'} == null)
								{
									$datasetRaw->{'SendDate'} = 'Not sent';
								}
								else
								{
									$datasetRaw->{'SendDate'} = date_format(date_create($datasetRaw->{'SendDate'}),'Y-m-d H:i');
								}

								if ($datasetRaw->{'LastAction'} == null)
								{
									$datasetRaw->{'LastAction'} = '-';
								}
								else
								{
									$datasetRaw->{'LastAction'} = date_format(date_create($datasetRaw->{'LastAction'}),'Y-m-d H:i');
								}

								if ($datasetRaw->{'User/Operator'} == '')
								{
									$datasetRaw->{'User/Operator'} = 'NA';
								}

								if ($datasetRaw->{'LockDate'} == null)
								{
									$datasetRaw->{'LockDate'} = '-';
								}

								if ($datasetRaw->{'LockUser'} == null)
								{
									$datasetRaw->{'LockUser'} = '-';
								}

								if ($datasetRaw->{'StgAbgeschickt'} == null)
								{
									$datasetRaw->{'StgAbgeschickt'} = 'N/A';
								}

								if ($datasetRaw->{'Nation'} == null)
								{
									$datasetRaw->{'Nation'} = '-';
								}

								return $datasetRaw;
							},
							'markRow' => function($datasetRaw) {

								if ($datasetRaw->LockDate != null)
								{
									return FilterWidget::DEFAULT_MARK_ROW_CLASS;
								}
							}
						);


						$filterId = isset($_GET[InfoCenter::FILTER_ID]) ? $_GET[InfoCenter::FILTER_ID] : null;

						if (isset($filterId) && is_numeric($filterId))
						{
							$filterWidgetArray[InfoCenter::FILTER_ID] = $filterId;
						}
						else
						{
							$filterWidgetArray['app'] = $APP;
							$filterWidgetArray['datasetName'] = 'PersonActions';
							$filterWidgetArray['filterKurzbz'] = 'InfoCenterNotSentApplicationAll';
						}

						echo $this->widgetlib->widget('FilterWidget', $filterWidgetArray);
					?>

				</div>
			</div>
		</div>
	</div>
</body>

<?php $this->load->view('templates/FHC-Footer'); ?>
