<?php
$this->load->view(
	'templates/FHC-Header',
	array(
		'title' => 'Pruefungsprotokoll',
		'jquery' => true,
		'bootstrap' => true,
		'fontawesome' => true,
		'dialoglib' => true,
		'ajaxlib' => true,
		'sbadmintemplate' => true,
		'customCSSs' => array(
			'public/css/sbadmin2/admintemplate_contentonly.css',
			'public/css/lehre/pruefungsprotokoll.css'
		),
		'customJSs' => array(
			'public/js/bootstrapper.js'
		)
	)
);
?>
<body>
<div id="wrapper">
	<div id="page-wrapper">
		<div class="container-fluid">
            <?php if (isset($abschlusspruefung)):
                $studiengangstyp_name = $abschlusspruefung->studiengangstyp == 'b' ? 'Bachelor' : 'Master';
                $pruefung_name = $abschlusspruefung->studiengangstyp == 'b' ? $this->p->t('abschlusspruefung', 'PruefungBachelor') : $this->p->t('abschlusspruefung', 'PruefungMaster');
                $arbeit_name = $abschlusspruefung->studiengangstyp == 'b' ? $this->p->t('abschlusspruefung', 'ArbeitBachelor') : $this->p->t('abschlusspruefung', 'ArbeitMaster');
                ?>
			<div class="row">
				<div class="col-lg-12">
					<h3 class="page-header">
						<?php echo $this->p->t('abschlusspruefung', 'Protokoll') ?>&nbsp;<?php echo $pruefung_name ?>
					</h3>
                    <p>
                        <?php echo $abschlusspruefung->studiengangstyp == 'b' ? $this->p->t('abschlusspruefung', 'AbgehaltenAmBachelor') : $this->p->t('abschlusspruefung', 'AbgehaltenAmMaster'); ?>
						<?php echo $abschlusspruefung->studiengangbezeichnung?>,&nbsp;<?php echo $this->p->t('abschlusspruefung', 'Studiengangskennzahl') ?>&nbsp;
						<?php echo $abschlusspruefung->studiengang_kz ?>
                    </p>
				</div>
			</div>
            <div class="row">
                <div class="col-lg-12">
                    <h4>
                    <?php echo $abschlusspruefung->titelpre_student . ' ' . $abschlusspruefung->vorname_student . ' ' . $abschlusspruefung->nachname_student . ' ' . $abschlusspruefung->titelpost_student?>
                    </h4>
                    <p><?php echo $this->p->t('abschlusspruefung', 'Personenkennzeichen') ?>: <?php echo $abschlusspruefung->matrikelnr ?></p>
                    <br />
                    <form>
                        <table class="table-condensed table-bordered table-responsive" id="protocoltbl">
                            <tr>
                                <td colspan="3">

                                    <?php echo $this->p->t('abschlusspruefung', 'Pruefungssenat') ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <?php echo $this->p->t('abschlusspruefung', 'Vorsitz') ?>
                                </td>
                                <td colspan="2">
                                    <?php echo $abschlusspruefung->titelpre_vorsitz . ' ' . $abschlusspruefung->vorname_vorsitz . ' ' . $abschlusspruefung->nachname_vorsitz . ' ' . $abschlusspruefung->titelpost_vorsitz ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
									<?php echo $this->p->t('abschlusspruefung', 'Erstpruefer') ?>
                                </td>
                                <td colspan="2">
                                    <?php echo $abschlusspruefung->titelpre_erstpruefer . ' ' . $abschlusspruefung->vorname_erstpruefer . ' ' . $abschlusspruefung->nachname_erstpruefer . ' ' . $abschlusspruefung->titelpost_erstpruefer ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
									<?php echo $this->p->t('abschlusspruefung', 'Zweitpruefer') ?>
                                </td>
                                <td colspan="2">
                                    <?php echo $abschlusspruefung->titelpre_zweitpruefer . ' ' . $abschlusspruefung->vorname_zweitpruefer . ' ' . $abschlusspruefung->nachname_zweitpruefer . ' ' . $abschlusspruefung->titelpost_zweitpruefer ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
									<?php echo $this->p->t('abschlusspruefung', 'Pruefungsdatum') ?>
                                </td>
                                <td colspan="2">
                                    <?php echo date_format(date_create($abschlusspruefung->datum), 'd.m.Y'); ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
									<?php echo $this->p->t('abschlusspruefung', 'EinverstaendniserklaerungName') ?>
                                </td>
                                <td colspan="2">
                                    <input type="checkbox" checked>
                                    <?php echo $this->p->t('abschlusspruefung', 'EinverstaendniserklaerungText') ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
									<?php echo $this->p->t('abschlusspruefung', 'ThemaBeurteilung') ?>&nbsp;<?php echo $arbeit_name ?>
                                </td>
                                <td>
                                    <?php echo isset($abschlusspruefung->abschlussarbeit_titel) ? $abschlusspruefung->abschlussarbeit_titel : '' ?>
                                </td>
                                <td>
									<?php echo $this->p->t('abschlusspruefung', 'Note') ?>: <?php echo isset($abschlusspruefung->abschlussarbeit_note) ? $abschlusspruefung->abschlussarbeit_note : '' ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
									<?php echo $this->p->t('abschlusspruefung', 'Pruefungsgegenstand') ?>
                                </td>
                                <td colspan="2">
                                    <?php  echo ($abschlusspruefung->studiengangstyp == 'b' ? $this->p->t('abschlusspruefung', 'PruefungsgegenstandBachelor') : $this->p->t('abschlusspruefung', 'PruefungsgegenstandMaster')) ?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3">
									<?php echo $this->p->t('abschlusspruefung', 'Notizen') ?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3">
                                    <textarea rows="15" cols="107"><?php echo $abschlusspruefung->studiengangstyp == 'b' ? $this->p->t('abschlusspruefung', 'PruefungsnotizenBachelor') : $this->p->t('abschlusspruefung', 'PruefungsnotizenMaster'); ?></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3">
									<?php  echo ($abschlusspruefung->studiengangstyp == 'b' ? $this->p->t('abschlusspruefung', 'BeurteilungKriterienBachelor') : $this->p->t('abschlusspruefung', 'BeurteilungKriterienMaster')) ?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3">
									<?php echo $this->p->t('abschlusspruefung', 'Beurteilung') ?>:
                                    <select>
                                        <?php foreach ($abschlussbeurteilung as $beurteilung):
                                            $selected = $beurteilung->abschlussbeurteilung_kurzbz == $abschlusspruefung->abschlussbeurteilung_kurzbz ? " selected" : "" ?>
                                            <option value="<?php echo $beurteilung->abschlussbeurteilung_kurzbz; ?>"<?php echo $selected ?>><?php echo $beurteilung->bezeichnung; ?> </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </form>
                    <br />
                </div>
            </div>
            <?php endif; ?>
		</div>
	</div>
</div>