<?php

header("Content-type: application/xhtml+xml");
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/basis_db.class.php');

$db = new basis_db();

if (isset($_REQUEST["xmlformat"]) && $_REQUEST["xmlformat"] == "xml")
{

	if(isset($_GET['id'])) {
		$id = $_GET['id'];

		$where = " WHERE studierendenantrag_id = " . $db->db_add_param($id) . "
					AND a.typ = 'Abmeldung' AND campus.get_status_studierendenantrag(a.studierendenantrag_id) = 'Genehmigt';";
		$not_found_error = 'Studierendenantrag not found'. $id;
	} elseif(isset($_GET['uid']) && isset($_GET['prestudent_id'])) {
		$uid = $_GET['uid'];
		$uid = explode(';', $uid);
		$uid  = (array_filter($uid, 'strlen'));

		$prestudent_id = $_GET['prestudent_id'];
		$prestudent_id = explode(';', $prestudent_id);
		$prestudent_id  = (array_filter($prestudent_id, 'strlen'));

		$where = " WHERE  a.prestudent_id in (" . $db->db_implode4SQL($prestudent_id) . ")
					AND a.typ = 'Abmeldung' AND campus.get_status_studierendenantrag(a.studierendenantrag_id) = 'Genehmigt';";
		$not_found_error = 'Studierendenantrag not found for: ' . implode(',', $uid);
	} else
		die('<error>wrong parameters</error>');
}
else
	die('<error>Format not supported</error>');


$query = "
	SELECT stg.bezeichnung, bezeichnung_mehrsprachig[(SELECT index FROM public.tbl_sprache WHERE sprache=" . $db->db_add_param(getSprache(), FHC_STRING) . ")], studierendenantrag_id, matrikelnr, studienjahr_kurzbz, a.studiensemester_kurzbz, vorname, nachname, studiengang_kz, pss.ausbildungssemester AS semester, pss.datum, a.grund
	FROM
	campus.tbl_studierendenantrag a
	JOIN public.tbl_student USING (prestudent_id)
	JOIN public.tbl_benutzer ON tbl_student.student_uid=uid
	JOIN public.tbl_person USING (person_id)
	JOIN public.tbl_studiengang stg USING (studiengang_kz)
	JOIN public.tbl_studiensemester USING (studiensemester_kurzbz)
	LEFT JOIN public.tbl_prestudentstatus pss ON (pss.prestudent_id = a.prestudent_id AND pss.studiensemester_kurzbz=a.studiensemester_kurzbz AND pss.status_kurzbz=get_rolle_prestudent(a.prestudent_id, a.studiensemester_kurzbz))
	LEFT JOIN lehre.tbl_studienplan plan USING (studienplan_id)
	JOIN bis.tbl_orgform ON (tbl_orgform.orgform_kurzbz = COALESCE(plan.orgform_kurzbz, pss.orgform_kurzbz, stg.orgform_kurzbz))" . $where;


if (!$db->db_query($query) || !$db->db_num_rows())
	die('<error>' . $not_found_error . '</error>');

?>
<?xml version='1.0' encoding='UTF-8' standalone='yes'?>
<antraege>
	<?php while($row = $db->db_fetch_object()) { ?>
		<?php $abmeldedatum = new DateTime($row->datum); ?>
		<antrag>
			<name><![CDATA[<?= trim($row->vorname . ' ' . $row->nachname); ?>]]></name>
			<studiengang><![CDATA[<?= $row->bezeichnung; ?>]]></studiengang>
			<organisationsform><![CDATA[<?= $row->bezeichnung_mehrsprachig; ?>]]></organisationsform>
			<personenkz><![CDATA[<?= $row->matrikelnr; ?>]]></personenkz>
			<studienjahr><![CDATA[<?= $row->studienjahr_kurzbz; ?>]]></studienjahr>
			<studiensemester><![CDATA[<?= $row->studiensemester_kurzbz; ?>]]></studiensemester>
			<semester><![CDATA[<?= $row->semester; ?>]]></semester>
			<abmeldedatum><![CDATA[<?= $abmeldedatum->format('d.m.Y'); ?>]]></abmeldedatum>
			<grund><![CDATA[<?= $row->grund; ?>]]></grund>
		</antrag>
	<?php } ?>
</antraege>

