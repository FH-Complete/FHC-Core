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
		if (!isset($data->von, $data->bis)) return [];
		if (!isset($data->kalender_id) && !isset($data->lehreinheit_id)) return [];

		if ($this->_ci->variablelib->getVar('ignore_kollision') === 'true') return [];
		if ($this->_ci->variablelib->getVar('kollision_student') !== 'true') return [];

		$kollisionsfreie_user = unserialize(KOLLISIONSFREIE_USER);
		$placeholders = implode(',', array_fill(0, count($kollisionsfreie_user), '?'));


		$dbModel = new DB_Model();

		if (isset($data->kalender_id))
		{
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
				AND (tbl_lehreinheitgruppe.gruppe = tbl_studentlehrverband.gruppe OR tbl_lehreinheitgruppe.gruppe IS NULL OR btrim(tbl_lehreinheitgruppe.gruppe::text) = '' OR tbl_studentlehrverband.gruppe IS NULL)
			WHERE tbl_kalender.kalender_id = ?
				AND tbl_studentlehrverband.student_uid NOT IN ($placeholders)
	";

			$qry_1_param = $data->kalender_id;
		}
		else
		{
			$qry1 = "
				SELECT DISTINCT tbl_benutzergruppe.uid
				FROM lehre.tbl_lehreinheit
				JOIN lehre.tbl_lehreinheitgruppe ON tbl_lehreinheitgruppe.lehreinheit_id = tbl_lehreinheit.lehreinheit_id
				JOIN public.tbl_gruppe
					ON tbl_gruppe.studiengang_kz = tbl_lehreinheitgruppe.studiengang_kz
					AND tbl_gruppe.semester = tbl_lehreinheitgruppe.semester
					AND tbl_gruppe.gruppe_kurzbz = tbl_lehreinheitgruppe.gruppe_kurzbz
				JOIN public.tbl_benutzergruppe ON tbl_benutzergruppe.gruppe_kurzbz = tbl_gruppe.gruppe_kurzbz
					AND tbl_benutzergruppe.studiensemester_kurzbz = tbl_lehreinheit.studiensemester_kurzbz
	
				WHERE tbl_lehreinheit.lehreinheit_id = ?
					AND tbl_benutzergruppe.uid NOT IN ($placeholders)

				UNION ALL

				SELECT DISTINCT tbl_studentlehrverband.student_uid AS uid
				FROM lehre.tbl_lehreinheit
				JOIN lehre.tbl_lehreinheitgruppe ON tbl_lehreinheitgruppe.lehreinheit_id = tbl_lehreinheit.lehreinheit_id
				JOIN public.tbl_studentlehrverband
					ON tbl_studentlehrverband.studiengang_kz = tbl_lehreinheitgruppe.studiengang_kz
					AND tbl_studentlehrverband.semester = tbl_lehreinheitgruppe.semester
					AND tbl_studentlehrverband.studiensemester_kurzbz = tbl_lehreinheit.studiensemester_kurzbz
					AND (tbl_lehreinheitgruppe.verband = tbl_studentlehrverband.verband OR tbl_lehreinheitgruppe.verband IS NULL OR btrim(tbl_lehreinheitgruppe.verband::text) = '' OR tbl_studentlehrverband.verband IS NULL)
					AND (tbl_lehreinheitgruppe.gruppe = tbl_studentlehrverband.gruppe OR tbl_lehreinheitgruppe.gruppe IS NULL OR btrim(tbl_lehreinheitgruppe.gruppe::text) = '' OR tbl_studentlehrverband.gruppe IS NULL)
				WHERE tbl_lehreinheit.lehreinheit_id = ?
					AND tbl_studentlehrverband.student_uid NOT IN ($placeholders)
        ";

			$qry_1_param = $data->lehreinheit_id;
		}

		$result1 = $dbModel->execReadOnlyQuery($qry1, array_merge(
			[$qry_1_param],
			$kollisionsfreie_user,
			[$qry_1_param],
			$kollisionsfreie_user
		));

		if (isError($result1) || !hasData($result1)) return [];

		$curUids = array_flip(array_column(getData($result1), 'uid'));

		$exclude_kalender = isset($data->kalender_id) ? "AND tbl_kalender.kalender_id != ?" : "";
		$exclude_kalender_param = isset($data->kalender_id) ? [$data->kalender_id] : [];

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
				$exclude_kalender
				AND tbl_kalender.status_kurzbz NOT IN ('archived', 'deleted', 'to_delete', 'to_delete_live', 'to_delete_preview')
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
				AND (tbl_lehreinheitgruppe.gruppe = tbl_studentlehrverband.gruppe OR tbl_lehreinheitgruppe.gruppe IS NULL OR btrim(tbl_lehreinheitgruppe.gruppe::text) = '' OR tbl_studentlehrverband.gruppe IS NULL)
			WHERE tbl_kalender.von < ?
				AND tbl_kalender.bis > ?
				$exclude_kalender
				AND tbl_kalender.status_kurzbz NOT IN ('archived', 'deleted', 'to_delete', 'to_delete_live', 'to_delete_preview')
				AND tbl_studentlehrverband.student_uid NOT IN ($placeholders)
				AND NOT EXISTS (
					SELECT 1 FROM lehre.tbl_kalender vorgaenger
					WHERE vorgaenger.vorgaenger_kalender_id = tbl_kalender.kalender_id
				)
		";

		$result2 = $dbModel->execReadOnlyQuery($qry2, array_merge(
			[$data->bis, $data->von], $exclude_kalender_param, $kollisionsfreie_user,
			[$data->bis, $data->von], $exclude_kalender_param, $kollisionsfreie_user
		));

		if (isError($result2) || !hasData($result2)) return [];

		$conflicts = [];
		foreach (getData($result2) as $row)
		{
			if (isset($curUids[$row->uid]))
			{
				$conflicts[] = [
					'message' =>
						$this->_ci->phraseslib->t('ui', 'student_kollision')
						. ': ' . $row->uid
						. ' (' . date('d.m.Y H:i', strtotime($row->von))
						. ' - ' . date('d.m.Y H:i', strtotime($row->bis)) . ')',
					'errorCode' => 'student_collision'
				];

			}
		}

		return $conflicts;
	}
}
