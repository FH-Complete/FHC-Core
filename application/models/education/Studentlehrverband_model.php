<?php
class Studentlehrverband_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_studentlehrverband';
		$this->pk = array('studiensemester_kurzbz', 'student_uid');
		$this->hasSequence = false;

		$this->load->model('crm/prestudentstatus_model', 'PrestudentstatusModel');
	}

	public function getAnzahl($studiengang_kz, $semester, $verband, $gruppe, $gruppe_kurzbz, $studiensemester_kurzbz, $lehreinheit_id, $mitschwund)
	{

		if ($semester === '')
			return null;

		$params = array();

		if (is_null($gruppe_kurzbz))
		{
			$qry = "SELECT COUNT(*) as anzahl 
					FROM public.tbl_studentlehrverband
					WHERE studiensemester_kurzbz = ?
						AND studiengang_kz = ?
						AND semester = ?
			";
			$params = array($studiensemester_kurzbz, $studiengang_kz, $semester);

			if (trim($verband) !== '')
			{
				$qry .= " AND trim(verband) = trim(?)";
				$params[] = $verband;
			}

			if (trim($gruppe) !== '')
			{
				$qry .= " AND trim(gruppe) = trim(?)";
				$params[] = $gruppe;
			}

			if ($mitschwund)
			{
				$qry .= " AND NOT EXISTS (
							SELECT 1 FROM lehre.tbl_zeugnisnote
							WHERE student_uid = tbl_studentlehrverband.student_uid
								AND lehrveranstaltung_id = (
									SELECT lehrveranstaltung_id
									FROM lehre.tbl_lehreinheit
									WHERE lehreinheit_id = ?
								)
							AND studiensemester_kurzbz = ?
							AND note IN (" . $this->_getNichtzugelasseneNoten() . "))";
				$params[] = $lehreinheit_id;
				$params[] = $studiensemester_kurzbz;

				$qry .= " AND get_rolle_prestudent(
							(SELECT prestudent_id FROM public.tbl_student WHERE student_uid = tbl_studentlehrverband.student_uid), null) NOT IN ('Abbrecher', 'Unterbrecher')";
			}
		}
		else
		{
			$qry = "SELECT COUNT(*) as anzahl
					FROM public.tbl_benutzergruppe
					WHERE studiensemester_kurzbz = ?
						AND gruppe_kurzbz = ?";

			$params = array($studiensemester_kurzbz, $gruppe_kurzbz);

			if ($mitschwund)
			{
				$qry .= " AND NOT EXISTS(
							SELECT 1 FROM lehre.tbl_zeugnisnote
							WHERE student_uid = tbl_benutzergruppe.uid
							AND lehrveranstaltung_id = (
								SELECT lehrveranstaltung_id FROM lehre.tbl_lehreinheit
								WHERE lehreinheit_id = ?
							)
							AND studiensemester_kurzbz = ?
							AND note IN (" . $this->_getNichtzugelasseneNoten() . "))";

				$params[] = $lehreinheit_id;
				$params[] = $studiensemester_kurzbz;

				$qry .= " AND get_rolle_prestudent(
							(SELECT prestudent_id FROM public.tbl_student WHERE student_uid = tbl_benutzergruppe.uid), null) NOT IN ('Abbrecher', 'Unterbrecher')";
			}
		}

		$result = $this->execReadOnlyQuery($qry, $params);

		if (isError($result))
			return error($result);

		return hasData($result) ? getData($result)[0]->anzahl : 0;
	}

	private function _getNichtzugelasseneNoten()
	{
		if (defined('NICHT_ZUGELASSENE') && NICHT_ZUGELASSENE !== null)
		{
			$notenArray = unserialize(NICHT_ZUGELASSENE);
			return implode(", ", array_map('intval', $notenArray));
		}
		return '6';
	}

}
