<?php
$sitesettings = array(
	'title' => 'Pruefungsprotokoll',
	'jquery3' => true,
	'jqueryui1' => true,
	'bootstrap3' => true,
	'fontawesome4' => true,
	'dialoglib' => true,
	'ajaxlib' => true,
	'sbadmintemplate3' => true,
	'phrases' => array(
		'abschlusspruefung' => array(
			'freigegebenAm',
			'pruefungGespeichert',
			'pruefungSpeichernFehler',
			'abschlussbeurteilungLeer',
			'beginnzeitLeer',
			'beginnzeitFormatError',
			'endezeitLeer',
			'endezeitFormatError',
			'endezeitBeforeError',
			'verfNotice'
		),
		'ui' => array(
			'stunde',
			'minute'
		)
	),
	'customCSSs' => array(
		'public/css/sbadmin2/admintemplate_contentonly.css',
		'vendor/fgelinas/timepicker/jquery.ui.timepicker.css',
		'public/css/lehre/pruefungsprotokoll.css'
	),
	'customJSs' => array(
		'vendor/fgelinas/timepicker/jquery.ui.timepicker.js',
		'public/js/lehre/pruefungsprotokoll.js'
	)
);

$this->load->view(
	'templates/FHC-Header',
	$sitesettings
);
?>
<div id="wrapper">
	<div id="page-wrapper">
		<div class="container-fluid">
            <?php if (isset($abschlusspruefung)):
                $studiengangstyp_name = $abschlusspruefung->studiengangstyp == 'Bachelor' ? 'Bachelor' : 'Master';
                $pruefung_name = $abschlusspruefung->studiengangstyp == 'Bachelor' ? $this->p->t('abschlusspruefung', 'pruefungBachelor') : $this->p->t('abschlusspruefung', 'pruefungMaster');
                $arbeit_name = $abschlusspruefung->studiengangstyp == 'Bachelor' ? $this->p->t('abschlusspruefung', 'arbeitBachelor') : $this->p->t('abschlusspruefung', 'arbeitMaster');
                $protokolltextvorlage = $abschlusspruefung->studiengangstyp == 'Bachelor' ? $this->p->t('abschlusspruefung', 'pruefungsnotizenBachelor') : $this->p->t('abschlusspruefung', 'pruefungsnotizenMaster');
                $protokolltext = isset($abschlusspruefung->protokoll) ? $abschlusspruefung->protokoll : $protokolltextvorlage;
                ?>
			<div class="row">
				<div class="col-lg-12">
					<h3 class="page-header">
						<?php echo $this->p->t('abschlusspruefung', 'protokoll') ?>&nbsp;<?php echo $pruefung_name ?>
					</h3>
                    <p>
                        <?php echo $abschlusspruefung->studiengangstyp == 'Bachelor' ? $this->p->t('abschlusspruefung', 'abgehaltenAmBachelor') : $this->p->t('abschlusspruefung', 'abgehaltenAmMaster'); ?>
						<?php echo $language == 'German' ? $abschlusspruefung->studiengangbezeichnung : $abschlusspruefung->studiengangbezeichnung_englisch ?>,&nbsp;<?php echo $this->p->t('abschlusspruefung', 'studiengangskennzahl') ?>&nbsp;
						<?php echo $abschlusspruefung->studiengang_kz ?>
                    </p>
				</div>
			</div>
            <div class="row">
                <div class="col-lg-12">
                    <h4>
                    <?php echo $abschlusspruefung->titelpre_student . ' ' . $abschlusspruefung->vorname_student . ' ' . $abschlusspruefung->nachname_student . ' ' . $abschlusspruefung->titelpost_student?>
                    </h4>
                    <p><?php echo $this->p->t('abschlusspruefung', 'personenkennzeichen') ?>: <?php echo $abschlusspruefung->matrikelnr ?></p>
                    <br />
                    <input type="hidden" name="abschlusspruefung_id" value="<?php echo $abschlusspruefung->abschlusspruefung_id;?>" id="abschlusspruefung_id">
                    <form id="protocolform">
                        <table class="table-condensed table-bordered table-responsive" id="protocoltbl">
                            <tr>
                                <td colspan="6">

                                    <?php echo $this->p->t('abschlusspruefung', 'pruefungssenat') ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <?php echo $this->p->t('abschlusspruefung', 'vorsitz') ?>
                                </td>
                                <td colspan="5">
                                    <?php echo $abschlusspruefung->titelpre_vorsitz . ' ' . $abschlusspruefung->vorname_vorsitz . ' ' . $abschlusspruefung->nachname_vorsitz . ' ' . $abschlusspruefung->titelpost_vorsitz ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <?php echo $this->p->t('abschlusspruefung', 'erstpruefer') ?>
                                </td>
                                <td colspan="5">
                                    <?php echo $abschlusspruefung->titelpre_erstpruefer . ' ' . $abschlusspruefung->vorname_erstpruefer . ' ' . $abschlusspruefung->nachname_erstpruefer . ' ' . $abschlusspruefung->titelpost_erstpruefer ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <?php echo $this->p->t('abschlusspruefung', 'zweitpruefer') ?>
                                </td>
                                <td colspan="5">
                                    <?php echo $abschlusspruefung->titelpre_zweitpruefer . ' ' . $abschlusspruefung->vorname_zweitpruefer . ' ' . $abschlusspruefung->nachname_zweitpruefer . ' ' . $abschlusspruefung->titelpost_zweitpruefer ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="namecellwidth">
                                    <?php echo $this->p->t('abschlusspruefung', 'pruefungsdatum') ?>
                                </td>
                                <td class="datevalcellwidth">
                                    <?php echo date_format(date_create($abschlusspruefung->datum), 'd.m.Y'); ?>
                                </td>
                                <td class="cellbg namecellwidth">
									<?php echo $this->p->t('abschlusspruefung', 'pruefungsbeginn') ?>
                                </td>
                                <td class="timecellwidth">
                                    <input class="timepicker form-control" name="pruefungsbeginn" id="pruefungsbeginn" value="<?php echo isEmptyString($abschlusspruefung->pruefungsbeginn) ? '' : date_format(date_create($abschlusspruefung->pruefungsbeginn), 'H:i') ?>">
                                </td>
                                <td class="cellbg namecellwidth">
									<?php echo $this->p->t('abschlusspruefung', 'pruefungsende') ?>
                                </td>
                                <td class="timecellwidth">
                                    <input class="timepicker form-control" name="pruefungsende" id="pruefungsende" value="<?php echo isEmptyString($abschlusspruefung->pruefungsbeginn) ? '' : date_format(date_create($abschlusspruefung->pruefungsende), 'H:i') ?>">
                                </td>
                            </tr>
                            <tr>
                                <td>
									<?php echo $this->p->t('abschlusspruefung', 'pruefungsantritt') ?>
                                </td>
                                <td colspan="5">
										<?php echo $language == 'German' ? $abschlusspruefung->pruefungsantritt_bezeichnung : $abschlusspruefung->pruefungsantritt_bezeichnung_english; ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <?php echo $this->p->t('abschlusspruefung', 'einverstaendniserklaerungName') ?>
                                </td>
                                <td colspan="5">
                                    <input type="checkbox" id="verfCheck">
                                    <?php echo $this->p->t('abschlusspruefung', 'einverstaendniserklaerungText') ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <?php echo $this->p->t('abschlusspruefung', 'themaBeurteilung') ?>&nbsp;<?php echo $arbeit_name ?>
                                </td>
                                <td colspan="4">
                                    <?php echo isset($abschlusspruefung->abschlussarbeit_titel) ? $abschlusspruefung->abschlussarbeit_titel : '' ?>
                                </td>
                                <td>
                                    <?php echo $this->p->t('lehre', 'note') ?>: <?php echo isset($abschlusspruefung->abschlussarbeit_note) ? $abschlusspruefung->abschlussarbeit_note : '' ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <?php echo $this->p->t('abschlusspruefung', 'pruefungsgegenstand') ?>
                                </td>
                                <td colspan="5">
                                    <?php  echo ($abschlusspruefung->studiengangstyp == 'Bachelor' ? $this->p->t('abschlusspruefung', 'pruefungsgegenstandBachelor') : $this->p->t('abschlusspruefung', 'pruefungsgegenstandMaster')) ?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="6">
                                    <?php echo ucfirst($this->p->t('global', 'notizen')); ?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="6">

                                    <textarea id="protokoll" name="protokoll" class="form-control" rows="15" cols="107"><?php echo $protokolltext ?></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="6">
                                    <?php  echo $abschlusspruefung->studiengangstyp == 'Bachelor' ? $this->p->t('abschlusspruefung', 'beurteilungKriterienBachelor') : $this->p->t('abschlusspruefung', 'beurteilungKriterienMaster') ?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="6">
                                    <?php echo $abschlusspruefung->studiengangstyp == 'Bachelor' ? $this->p->t('abschlusspruefung', 'beurteilungBachelor') : $this->p->t('abschlusspruefung', 'beurteilungMaster') ?>:
                                    <select name="abschlussbeurteilung_kurzbz" id="abschlussbeurteilung_kurzbz" class="form-control">
                                        <option value="">-- <?php echo $this->p->t('ui', 'bitteWaehlen'); ?> --</option>
                                        <?php foreach ($abschlussbeurteilung as $beurteilung):
                                            $selected = $beurteilung->abschlussbeurteilung_kurzbz == $abschlusspruefung->abschlussbeurteilung_kurzbz ? " selected" : "" ?>
                                            <option value="<?php echo $beurteilung->abschlussbeurteilung_kurzbz; ?>"<?php echo $selected ?>><?php echo $language == 'German' ? $beurteilung->bezeichnung : $beurteilung->bezeichnung_english; ?> </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span id="verfNotice"></span>
                                </td>
                            </tr>
                        </table>
                    </form>
                    <br />
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12 text-right">
                    <p>
                        <?php $freigegeben = isset($abschlusspruefung->freigabedatum); ?>
                        <button id="saveProtocolBtn" class="btn btn-default"<?php echo $freigegeben ? " disabled" : "" ?>><?php echo $this->p->t('ui', 'speichern') ?></button>
                    </p>
                </div>
            </div>
            <hr id="hrbottom">
            <div class="row">
                <div class="col-lg-12">
                    <span id="freigegebenText">
                            <?php
							if ($freigegeben)
								echo '&nbsp;&nbsp;' . $this->p->t('abschlusspruefung', 'freigegebenAm') . ' ' . date_format(date_create($abschlusspruefung->freigabedatum), 'd.m.Y')
							?>
                    </span>
                </div>
            </div>
            <br />
            <div class="row">
                <div class="col-lg-8">
                    <div class="input-group">
                        <input id="username" type="hidden" value=""><!-- this is to prevent Chrome autofilling a random input field with the username-->
                        <input id="password" type="password" autocomplete="new-password" class="form-control" placeholder="CIS-<?php echo ucfirst($this->p->t('password', 'password')); ?>">
                        <span class="input-group-btn">
                            <button id="freigebenProtocolBtn" class="btn btn-default"><?php echo $this->p->t('abschlusspruefung', 'ueberpruefenFreigeben') ?></button>
                        </span>
                    </div>
                </div>
            </div>
            <br />
            <br />
			<?php endif; ?>
		</div>
	</div>
</div>
<?php
$this->load->view(
	'templates/FHC-Footer',
	$sitesettings
);
