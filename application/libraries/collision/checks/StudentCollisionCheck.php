<?php

class StudentCollisionCheck implements ICollisionCheck
{
	private $_ci;

	public function __construct()
	{
		$this->_ci =& get_instance();
		$this->_ci->load->model('ressource/Kalender_model', 'KalenderModel');
		$this->_ci->load->library('VariableLib', array('uid' => getAuthUID()));
		$this->_ci->load->library('PhrasesLib', array('ui'));
	}

	public function getName()
	{
		return 'student';
	}

	public function check($data)
	{
		if (!isset($data->von, $data->bis, $data->kalender_id)) return [];

		if ($this->_ci->variablelib->getVar('ignore_kollision') === 'true') return [];
		if ($this->_ci->variablelib->getVar('kollision_student') !== 'true') return [];

		$kollisionsfreie_user = unserialize(KOLLISIONSFREIE_USER);
		$placeholders = implode(',', array_fill(0, count($kollisionsfreie_user), '?'));


		$dbModel = new DB_Model();

		$qry1 = "
			SELECT DISTINCT tbl_benutzergruppe.uid
			FROM lehre.tbl_kalender  
			JOIN lehre.tbl_kalender_lehreinheit USING(kalender_id)
			JOIN lehre.tbl_lehreinheit ON tbl_lehreinheit.lehreinheit_id = tbl_kalender_lehreinheit.lehreinheit_id
			JOIN lehre.tbl_lehreinheitgruppe ON tbl_lehreinheitgruppe.lehreinheit_id = tbl_lehreinheit.lehreinheit_id
			JOIN public.tbl_gruppe
				ON tbl_gruppe.studiengang_kz = tbl_lehreinheitgruppe.studiengang_kz
				AND tbl_gruppe.semester = tbl_lehreinheitgruppe.semester
				AND tbl_gruppe.gruppe_kurzbz = tbl_lehreinheitgruppe.gruppe_kurzbz
			JOIN public.tbl_benutzergruppe ON tbl_benutzergruppe.gruppe_kurzbz = tbl_gruppe.gruppe_kurzbz
				AND tbl_benutzergruppe.studiensemester_kurzbz = tbl_lehreinheit.studiensemester_kurzbz

			WHERE tbl_kalender.kalender_id = ?
				AND tbl_benutzergruppe.uid NOT IN ($placeholders)
			UNION ALL

			SELECT DISTINCT tbl_studentlehrverband.student_uid AS uid
			FROM lehre.tbl_kalender  
			JOIN lehre.tbl_kalender_lehreinheit USING(kalender_id)
			JOIN lehre.tbl_lehreinheit ON tbl_lehreinheit.lehreinheit_id = tbl_kalender_lehreinheit.lehreinheit_id
			JOIN lehre.tbl_lehreinheitgruppe ON tbl_lehreinheitgruppe.lehreinheit_id = tbl_lehreinheit.lehreinheit_id
			JOIN public.tbl_studentlehrverband
				ON tbl_studentlehrverband.studiengang_kz = tbl_lehreinheitgruppe.studiengang_kz
				AND tbl_studentlehrverband.semester = tbl_lehreinheitgruppe.semester
				AND tbl_studentlehrverband.studiensemester_kurzbz = tbl_lehreinheit.studiensemester_kurzbz
				AND (tbl_lehreinheitgruppe.verband = tbl_studentlehrverband.verband OR tbl_lehreinheitgruppe.verband IS NULL OR btrim(tbl_lehreinheitgruppe.verband::text) = '' OR tbl_studentlehrverband.verband IS NULL)
				AND (tbl_lehreinheitgruppe.gruppe  = tbl_studentlehrverband.gruppe  OR tbl_lehreinheitgruppe.gruppe  IS NULL OR btrim(tbl_lehreinheitgruppe.gruppe::text)  = '' OR tbl_studentlehrverband.gruppe  IS NULL)
			WHERE tbl_kalender.kalender_id = ?
				AND tbl_studentlehrverband.student_uid NOT IN ($placeholders)

		";

		$result1 = $dbModel->execReadOnlyQuery($qry1, array_merge(
			[$data->kalender_id],
			$kollisionsfreie_user,
			[$data->kalender_id],
			$kollisionsfreie_user
		));

		if (isError($result1) || !hasData($result1)) return [];

		$curUids = array_flip(array_column(getData($result1), 'uid'));

		$qry2 = "
			SELECT DISTINCT tbl_kalender.kalender_id, tbl_kalender.von, tbl_kalender.bis, tbl_benutzergruppe.uid
			FROM lehre.tbl_kalender
			JOIN lehre.tbl_kalender_lehreinheit ON tbl_kalender_lehreinheit.kalender_id = tbl_kalender.kalender_id
			JOIN lehre.tbl_lehreinheit ON tbl_lehreinheit.lehreinheit_id = tbl_kalender_lehreinheit.lehreinheit_id
			JOIN lehre.tbl_lehreinheitgruppe ON tbl_lehreinheitgruppe.lehreinheit_id = tbl_lehreinheit.lehreinheit_id
			JOIN public.tbl_gruppe
				ON tbl_gruppe.studiengang_kz = tbl_lehreinheitgruppe.studiengang_kz
				AND tbl_gruppe.semester = tbl_lehreinheitgruppe.semester
				AND tbl_gruppe.gruppe_kurzbz = tbl_lehreinheitgruppe.gruppe_kurzbz
			JOIN public.tbl_benutzergruppe ON tbl_benutzergruppe.gruppe_kurzbz = tbl_gruppe.gruppe_kurzbz
				AND tbl_benutzergruppe.studiensemester_kurzbz = tbl_lehreinheit.studiensemester_kurzbz
			WHERE tbl_kalender.von < ?
			  AND tbl_kalender.bis > ?
			  AND tbl_kalender.kalender_id != ?
			  AND tbl_kalender.status_kurzbz NOT IN ('archived', 'deleted', 'to_delete')
			  AND tbl_benutzergruppe.uid NOT IN ($placeholders)
			  AND NOT EXISTS (
				SELECT 1 FROM lehre.tbl_kalender vorgaenger
				WHERE vorgaenger.vorgaenger_kalender_id = tbl_kalender.kalender_id
			  )

			UNION ALL
			
			SELECT DISTINCT tbl_kalender.kalender_id, tbl_kalender.von, tbl_kalender.bis, tbl_studentlehrverband.student_uid AS uid
			FROM lehre.tbl_kalender
			JOIN lehre.tbl_kalender_lehreinheit ON tbl_kalender_lehreinheit.kalender_id = tbl_kalender.kalender_id
			JOIN lehre.tbl_lehreinheit ON tbl_lehreinheit.lehreinheit_id = tbl_kalender_lehreinheit.lehreinheit_id
			JOIN lehre.tbl_lehreinheitgruppe ON tbl_lehreinheitgruppe.lehreinheit_id = tbl_lehreinheit.lehreinheit_id
			JOIN public.tbl_studentlehrverband
				ON tbl_studentlehrverband.studiengang_kz = tbl_lehreinheitgruppe.studiengang_kz
				AND tbl_studentlehrverband.semester = tbl_lehreinheitgruppe.semester
				AND tbl_studentlehrverband.studiensemester_kurzbz = tbl_lehreinheit.studiensemester_kurzbz
				AND (tbl_lehreinheitgruppe.verband = tbl_studentlehrverband.verband OR tbl_lehreinheitgruppe.verband IS NULL OR btrim(tbl_lehreinheitgruppe.verband::text) = '' OR tbl_studentlehrverband.verband IS NULL)
				AND (tbl_lehreinheitgruppe.gruppe  = tbl_studentlehrverband.gruppe  OR tbl_lehreinheitgruppe.gruppe  IS NULL OR btrim(tbl_lehreinheitgruppe.gruppe::text)  = '' OR tbl_studentlehrverband.gruppe  IS NULL)
			WHERE tbl_kalender.von < ?
			  AND tbl_kalender.bis > ?
			  AND tbl_kalender.kalender_id != ?
			  AND tbl_kalender.status_kurzbz NOT IN ('archived', 'deleted', 'to_delete')
			  AND tbl_studentlehrverband.student_uid NOT IN ($placeholders)
			  AND NOT EXISTS (
				  SELECT 1 FROM lehre.tbl_kalender vorgaenger
				  WHERE vorgaenger.vorgaenger_kalender_id = tbl_kalender.kalender_id
			  )
		";

		$result2 = $dbModel->execReadOnlyQuery($qry2, array_merge(
			[$data->bis, $data->von, $data->kalender_id],
			$kollisionsfreie_user,
			[$data->bis, $data->von, $data->kalender_id],
			$kollisionsfreie_user
		));

		if (isError($result2) || !hasData($result2)) return [];

		$conflicts = [];
		foreach (getData($result2) as $row) {
			if (isset($curUids[$row->uid])) {
				$conflicts[] = $this->_ci->phraseslib->t('ui', 'student_kollision')
					. ': ' . $row->uid
					. ' (' . date('d.m.Y H:i', strtotime($row->von))
					. ' - ' . date('d.m.Y H:i', strtotime($row->bis)) . ')';
			}
		}

		return $conflicts;
	}

	public function checkAll($kalender_ids)
	{

		if (empty($kalender_ids)) return [];

		if ($this->_ci->variablelib->getVar('kollision_student') !== 'true') return [];

		$dbModel = new DB_Model();
		$placeholders = implode(',', array_fill(0, count($kalender_ids), '?'));

		$sql = "
			SELECT DISTINCT current_kalender.kalender_id, current_benutzergruppe.uid
			FROM lehre.tbl_kalender current_kalender
			JOIN lehre.tbl_kalender_lehreinheit current_kalender_le ON current_kalender_le.kalender_id = current_kalender.kalender_id
			JOIN lehre.tbl_lehreinheit current_lehreinheit ON current_lehreinheit.lehreinheit_id = current_kalender_le.lehreinheit_id
			JOIN lehre.tbl_lehreinheitgruppe current_lehreinheitgruppe ON current_lehreinheitgruppe.lehreinheit_id = current_lehreinheit.lehreinheit_id
			JOIN public.tbl_gruppe current_gruppe
				ON current_gruppe.studiengang_kz = current_lehreinheitgruppe.studiengang_kz
				AND current_gruppe.semester = current_lehreinheitgruppe.semester
				AND current_gruppe.gruppe_kurzbz = current_lehreinheitgruppe.gruppe_kurzbz
			JOIN public.tbl_benutzergruppe current_benutzergruppe ON current_benutzergruppe.gruppe_kurzbz = current_gruppe.gruppe_kurzbz
					AND current_benutzergruppe.studiensemester_kurzbz = current_lehreinheit.studiensemester_kurzbz
			JOIN lehre.tbl_kalender other_kalender
				ON other_kalender.kalender_id != current_kalender.kalender_id
				AND other_kalender.von < current_kalender.bis
				AND other_kalender.bis > current_kalender.von
				AND other_kalender.status_kurzbz NOT IN ('archived', 'deleted', 'to_delete')
				AND NOT EXISTS (
					SELECT 1 FROM lehre.tbl_kalender vorgaenger
					WHERE vorgaenger.vorgaenger_kalender_id = other_kalender.kalender_id
				)
			JOIN lehre.tbl_kalender_lehreinheit other_kalender_le ON other_kalender_le.kalender_id = other_kalender.kalender_id
			JOIN lehre.tbl_lehreinheit other_lehreinheit ON other_lehreinheit.lehreinheit_id = other_kalender_le.lehreinheit_id
			JOIN lehre.tbl_lehreinheitgruppe other_lehreinheitgruppe ON other_lehreinheitgruppe.lehreinheit_id = other_lehreinheit.lehreinheit_id
			JOIN public.tbl_gruppe other_gruppe
				ON other_gruppe.studiengang_kz = other_lehreinheitgruppe.studiengang_kz
				AND other_gruppe.semester = other_lehreinheitgruppe.semester
				AND other_gruppe.gruppe_kurzbz = other_lehreinheitgruppe.gruppe_kurzbz
			JOIN public.tbl_benutzergruppe other_benutzergruppe
				ON other_benutzergruppe.gruppe_kurzbz = other_gruppe.gruppe_kurzbz
				AND other_benutzergruppe.uid = current_benutzergruppe.uid
				AND other_benutzergruppe.studiensemester_kurzbz = other_lehreinheit.studiensemester_kurzbz


			WHERE current_kalender.kalender_id IN ($placeholders)

			UNION ALL

			SELECT DISTINCT current_kalender.kalender_id, current_studentlehrverband.student_uid AS uid
			FROM lehre.tbl_kalender current_kalender
			JOIN lehre.tbl_kalender_lehreinheit current_kalender_le ON current_kalender_le.kalender_id = current_kalender.kalender_id
			JOIN lehre.tbl_lehreinheit current_lehreinheit ON current_lehreinheit.lehreinheit_id = current_kalender_le.lehreinheit_id
			JOIN lehre.tbl_lehreinheitgruppe current_lehreinheitgruppe ON current_lehreinheitgruppe.lehreinheit_id = current_lehreinheit.lehreinheit_id
			JOIN public.tbl_studentlehrverband current_studentlehrverband
				ON current_studentlehrverband.studiengang_kz = current_lehreinheitgruppe.studiengang_kz
				AND current_studentlehrverband.semester = current_lehreinheitgruppe.semester
				AND current_studentlehrverband.studiensemester_kurzbz = current_lehreinheit.studiensemester_kurzbz
				AND (current_lehreinheitgruppe.verband = current_studentlehrverband.verband OR current_lehreinheitgruppe.verband IS NULL OR btrim(current_lehreinheitgruppe.verband::text) = '' OR current_studentlehrverband.verband IS NULL)
				AND (current_lehreinheitgruppe.gruppe  = current_studentlehrverband.gruppe  OR current_lehreinheitgruppe.gruppe  IS NULL OR btrim(current_lehreinheitgruppe.gruppe::text)  = '' OR current_studentlehrverband.gruppe  IS NULL)

			JOIN lehre.tbl_kalender other_kalender
				ON other_kalender.kalender_id != current_kalender.kalender_id
				AND other_kalender.von < current_kalender.bis
				AND other_kalender.bis > current_kalender.von
				AND other_kalender.status_kurzbz NOT IN ('archived', 'deleted', 'to_delete')
				AND NOT EXISTS (
					SELECT 1 FROM lehre.tbl_kalender vorgaenger
					WHERE vorgaenger.vorgaenger_kalender_id = other_kalender.kalender_id
				)
			JOIN lehre.tbl_kalender_lehreinheit other_kalender_le ON other_kalender_le.kalender_id = other_kalender.kalender_id
			JOIN lehre.tbl_lehreinheit other_lehreinheit ON other_lehreinheit.lehreinheit_id = other_kalender_le.lehreinheit_id
			JOIN lehre.tbl_lehreinheitgruppe other_lehreinheitgruppe ON other_lehreinheitgruppe.lehreinheit_id = other_lehreinheit.lehreinheit_id
			JOIN public.tbl_studentlehrverband other_slv
				ON other_slv.studiengang_kz = other_lehreinheitgruppe.studiengang_kz
				AND other_slv.semester = other_lehreinheitgruppe.semester
				AND other_slv.studiensemester_kurzbz = other_lehreinheit.studiensemester_kurzbz
				AND other_slv.student_uid = current_studentlehrverband.student_uid
				AND (other_lehreinheitgruppe.verband = other_slv.verband OR other_lehreinheitgruppe.verband IS NULL OR btrim(other_lehreinheitgruppe.verband::text) = '' OR other_slv.verband IS NULL)
				AND (other_lehreinheitgruppe.gruppe  = other_slv.gruppe  OR other_lehreinheitgruppe.gruppe  IS NULL OR btrim(other_lehreinheitgruppe.gruppe::text)  = '' OR other_slv.gruppe  IS NULL)

			WHERE current_kalender.kalender_id IN ($placeholders)
		";

		$result = $dbModel->execReadOnlyQuery($sql, array_merge($kalender_ids, $kalender_ids));

		if (isError($result) || !hasData($result)) return [];

		$grouped = [];
		foreach (getData($result) as $row)
			$grouped[$row->kalender_id][] = true;

		return $grouped;
	}
}