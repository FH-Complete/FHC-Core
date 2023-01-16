<?php
$includesArray = array(
	'customJSModules' => ['public/js/apps/Cis/MyLv/Student.js'],
	'customCSSs' => [
		'public/css/components/dashboard.css'
	]
);

$this->load->view('templates/CISHTML-Header', $includesArray);
?>

<div id="content">
	<h2>MyLv</h2>
	<hr>
	<mylv-student></mylv-student>
	<?php $studiengaenge = [];foreach ($studiengaenge as $studiengang) { ?>
		<h3><?= $studiengang['bezeichnung'] . ' - ' . $studiengang['studiengang_kz']; ?></h3>
		<?php foreach ($studiengang['semester'] as $semester) { ?>
			<h4><?= implode(",", array_keys($semester['studiensemester_kurzbz'])); ?>, <?= $semester['semester']; ?>.Semester</h4>
			<?php foreach ($semester['lvs'] as $lv) { ?>
				<!-- lehrveranstaltung_id: <?= $lv->lehrveranstaltung_id; ?><br>
				kurzbz: <?= $lv->kurzbz; ?><br> -->
				bezeichnung: <?= $lv->bezeichnung; ?><br>
				<!-- studiengang_kz: <?= $lv->studiengang_kz; ?><br>
				semester: <?= $lv->semester; ?><br>
				sprache: <?= $lv->sprache; ?><br>
				ects: <?= $lv->ects; ?><br>
				semesterstunden: <?= $lv->semesterstunden; ?><br>
				anmerkung: <?= $lv->anmerkung; ?><br>
				lehre: <?= $lv->lehre; ?><br>
				lehreverzeichnis: <?= $lv->lehreverzeichnis; ?><br>
				aktiv: <?= $lv->aktiv; ?><br>
				planfaktor: <?= $lv->planfaktor; ?><br>
				planlektoren: <?= $lv->planlektoren; ?><br>
				planpersonalkosten: <?= $lv->planpersonalkosten; ?><br>
				plankostenprolektor: <?= $lv->plankostenprolektor; ?><br>
				ext_id: <?= $lv->ext_id; ?><br>
				sort: <?= $lv->sort; ?><br>
				zeugnis: <?= $lv->zeugnis; ?><br>
				koordinator: <?= $lv->koordinator; ?><br>
				projektarbeit: <?= $lv->projektarbeit; ?><br>
				lehrform_kurzbz: <?= $lv->lehrform_kurzbz; ?><br>
				bezeichnung_english: <?= $lv->bezeichnung_english; ?><br>
				orgform_kurzbz: <?= $lv->orgform_kurzbz; ?><br>
				incoming: <?= $lv->incoming; ?><br>
				lehrtyp_kurzbz: <?= $lv->lehrtyp_kurzbz; ?><br>
				oe_kurzbz: <?= $lv->oe_kurzbz; ?><br>
				raumtyp_kurzbz: <?= $lv->raumtyp_kurzbz; ?><br>
				anzahlsemester: <?= $lv->anzahlsemester; ?><br>
				semesterwochen: <?= $lv->semesterwochen; ?><br>
				lvnr: <?= $lv->lvnr; ?><br>
				semester_alternativ: <?= $lv->semester_alternativ; ?><br>
				farbe: <?= $lv->farbe; ?><br>
				old_lehrfach_id: <?= $lv->old_lehrfach_id; ?><br>
				sws: <?= $lv->sws; ?><br>
				lvs: <?= $lv->lvs; ?><br>
				alvs: <?= $lv->alvs; ?><br>
				lvps: <?= $lv->lvps; ?><br>
				las: <?= $lv->las; ?><br>
				benotung: <?= $lv->benotung; ?><br>
				lvinfo: <?= $lv->lvinfo; ?><br>
				lehrauftrag: <?= $lv->lehrauftrag; ?><br>
				lehrmodus_kurzbz: <?= $lv->lehrmodus_kurzbz; ?><br>
				lehrveranstaltung_template_id: <?= $lv->lehrveranstaltung_template_id; ?><br>
				uid: <?= $lv->uid; ?><br>
				lehreinheit_id: <?= $lv->lehreinheit_id; ?><br> -->
				studiensemester_kurzbz: <?= $lv->studiensemester_kurzbz; ?><br>
				<!-- lehrfach_id: <?= $lv->lehrfach_id; ?><br>
				stundenblockung: <?= $lv->stundenblockung; ?><br>
				wochenrythmus: <?= $lv->wochenrythmus; ?><br>
				start_kw: <?= $lv->start_kw; ?><br>
				raumtyp: <?= $lv->raumtyp; ?><br>
				raumtypalternativ: <?= $lv->raumtypalternativ; ?><br>
				lv_lehrform_kurzbz: <?= $lv->lv_lehrform_kurzbz; ?><br> -->
			<?php } ?>
			<hr>
		<?php } ?>
		<hr>
	<?php } ?>
</div>

<?php $this->load->view('templates/CISHTML-Footer', $includesArray); ?>
