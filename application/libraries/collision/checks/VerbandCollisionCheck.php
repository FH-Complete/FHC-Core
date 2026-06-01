<?php

class VerbandCollisionCheck implements ICollisionCheck
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
		return 'verband';
	}

	public function check($data)
	{
		if (!isset($data->von, $data->bis, $data->kalender_id)) return [];

		if ($this->_ci->variablelib->getVar('ignore_kollision') === 'true') return [];

		$kollision_student      = $this->_ci->variablelib->getVar('kollision_student') === 'false';
		$kollision_reservierung = $this->_ci->variablelib->getVar('ignore_reservierung') === 'false';

		if (!$kollision_student && !$kollision_reservierung) return [];

		$dbModel    = new DB_Model();
		$collisions = [];

		if ($kollision_student)
		{
			$union_event = "";

			if ($kollision_reservierung)
			{
				$union_event = "
					UNION
					SELECT tbl_kalender_event_teilnehmer.studiengang_kz, tbl_kalender_event_teilnehmer.semester, tbl_kalender_event_teilnehmer.verband, tbl_kalender_event_teilnehmer.gruppe, tbl_kalender_event_teilnehmer.gruppe_kurzbz, tbl_kalender_event_teilnehmer.kalender_id
					FROM lehre.tbl_kalender_event_teilnehmer
					WHERE tbl_kalender_event_teilnehmer.rolle_kurzbz = 'teilnehmer'
				";
			}

			$sql_gruppen = "
				SELECT
					other_kalender.von,
					other_kalender.bis,
					COALESCE(
						other_lehreinheitguppe.gruppe_kurzbz,
						UPPER(stg.typ::text || stg.kurzbz::text) || '-' || other_lehreinheitguppe.semester ||
						COALESCE(other_lehreinheitguppe.verband::text, '') ||
						COALESCE(other_lehreinheitguppe.gruppe::text, '')
					) AS gruppenname
				FROM lehre.tbl_kalender current_kalender

				JOIN (
					SELECT tbl_lehreinheitgruppe.studiengang_kz, tbl_lehreinheitgruppe.semester, tbl_lehreinheitgruppe.verband, tbl_lehreinheitgruppe.gruppe,
						   tbl_lehreinheitgruppe.gruppe_kurzbz, tbl_kalender_lehreinheit.kalender_id
					FROM lehre.tbl_kalender_lehreinheit
					JOIN lehre.tbl_lehreinheit ON tbl_lehreinheit.lehreinheit_id = tbl_kalender_lehreinheit.lehreinheit_id
					JOIN lehre.tbl_lehreinheitgruppe ON tbl_lehreinheitgruppe.lehreinheit_id = tbl_lehreinheit.lehreinheit_id
					". $union_event ."
				) current_lehreinheitguppe ON current_lehreinheitguppe.kalender_id = current_kalender.kalender_id

				LEFT JOIN public.tbl_gruppe current_gruppe
					ON current_gruppe.gruppe_kurzbz = current_lehreinheitguppe.gruppe_kurzbz

				JOIN lehre.tbl_kalender other_kalender
					ON other_kalender.kalender_id != current_kalender.kalender_id
					AND other_kalender.von < ?
					AND other_kalender.bis > ?

				JOIN (
					SELECT tbl_lehreinheitgruppe.studiengang_kz, tbl_lehreinheitgruppe.semester, tbl_lehreinheitgruppe.verband, tbl_lehreinheitgruppe.gruppe,
						   tbl_lehreinheitgruppe.gruppe_kurzbz, tbl_kalender_lehreinheit.kalender_id
					FROM lehre.tbl_kalender_lehreinheit
					JOIN lehre.tbl_lehreinheit ON tbl_lehreinheit.lehreinheit_id = tbl_kalender_lehreinheit.lehreinheit_id
					JOIN lehre.tbl_lehreinheitgruppe ON tbl_lehreinheitgruppe.lehreinheit_id = tbl_lehreinheit.lehreinheit_id
					". $union_event ."
				) other_lehreinheitguppe ON other_lehreinheitguppe.kalender_id = other_kalender.kalender_id

				LEFT JOIN public.tbl_gruppe other_gruppe
					ON other_gruppe.gruppe_kurzbz = other_lehreinheitguppe.gruppe_kurzbz

				LEFT JOIN public.tbl_studiengang stg
					ON stg.studiengang_kz = other_lehreinheitguppe.studiengang_kz

				WHERE current_kalender.kalender_id = ?
				AND other_kalender.status_kurzbz NOT IN ('archived', 'deleted', 'to_delete')
				AND current_lehreinheitguppe.studiengang_kz = other_lehreinheitguppe.studiengang_kz
				AND current_lehreinheitguppe.semester = other_lehreinheitguppe.semester
				AND (
					(
						current_lehreinheitguppe.gruppe_kurzbz IS NULL
						AND other_lehreinheitguppe.gruppe_kurzbz IS NULL
						AND (
							current_lehreinheitguppe.verband IS NULL
							OR (
								current_lehreinheitguppe.verband = other_lehreinheitguppe.verband
								AND (current_lehreinheitguppe.gruppe IS NULL OR other_lehreinheitguppe.gruppe IS NULL OR current_lehreinheitguppe.gruppe = other_lehreinheitguppe.gruppe)
							)
						)
					)
					OR
					(
						current_lehreinheitguppe.gruppe_kurzbz IS NOT NULL
						AND other_lehreinheitguppe.gruppe_kurzbz IS NOT NULL
						AND current_gruppe.direktinskription IS NOT TRUE
						AND other_gruppe.direktinskription IS NOT TRUE
					)
					OR
					(
						(
							current_lehreinheitguppe.gruppe_kurzbz IS NULL
							AND other_lehreinheitguppe.gruppe_kurzbz IS NOT NULL
							AND other_gruppe.direktinskription IS NOT TRUE
						)
						OR
						(
							current_lehreinheitguppe.gruppe_kurzbz IS NOT NULL
							AND other_lehreinheitguppe.gruppe_kurzbz IS NULL
							AND current_gruppe.direktinskription IS NOT TRUE
						)
					)
				)
				AND other_kalender.kalender_id NOT IN (
					SELECT vorgaenger_kalender_id
					FROM lehre.tbl_kalender
					WHERE vorgaenger_kalender_id IS NOT NULL
				)
			";

			$result = $dbModel->execReadOnlyQuery($sql_gruppen, [
				$data->bis,
				$data->von,
				$data->kalender_id,
			]);

			if (!isError($result) && hasData($result))
			{
				foreach (getData($result) as $row)
					$collisions[] = $this->_ci->phraseslib->t('ui', 'verband_kollision') . ': ' . $row->gruppenname . ' (' . date('d.m.Y H:i', strtotime($row->von)) . ' - ' . date('d.m.Y H:i', strtotime($row->bis)) . ')';
			}
		}

		if ($kollision_reservierung && !$kollision_student)
		{
			$sql_reservierung = "
				SELECT
					other_kalender.von,
					other_kalender.bis,
					COALESCE(
						other_event_teilnehmer.gruppe_kurzbz,
						UPPER(stg.typ::text || stg.kurzbz::text) || '-' || other_event_teilnehmer.semester ||
						COALESCE(other_event_teilnehmer.verband::text, '') ||
						COALESCE(other_event_teilnehmer.gruppe::text, '')
					) AS gruppenname
				FROM lehre.tbl_kalender_event_teilnehmer current_event_teilnehmer
				LEFT JOIN public.tbl_gruppe current_gruppe
					ON current_gruppe.gruppe_kurzbz = current_event_teilnehmer.gruppe_kurzbz

				JOIN lehre.tbl_kalender other_kalender
					ON other_kalender.kalender_id != ?
					AND other_kalender.von < ?
					AND other_kalender.bis > ?

				JOIN lehre.tbl_kalender_event_teilnehmer other_event_teilnehmer
					ON other_event_teilnehmer.kalender_id = other_kalender.kalender_id
					AND other_event_teilnehmer.rolle_kurzbz = 'teilnehmer'

				LEFT JOIN public.tbl_gruppe other_gruppe
					ON other_gruppe.gruppe_kurzbz = other_event_teilnehmer.gruppe_kurzbz

				LEFT JOIN public.tbl_studiengang stg
					ON stg.studiengang_kz = other_event_teilnehmer.studiengang_kz

				WHERE current_event_teilnehmer.kalender_id = ?
				AND current_event_teilnehmer.rolle_kurzbz = 'teilnehmer'
				AND other_kalender.status_kurzbz NOT IN ('archived', 'deleted', 'to_delete')
				AND current_event_teilnehmer.studiengang_kz = other_event_teilnehmer.studiengang_kz
				AND current_event_teilnehmer.semester = other_event_teilnehmer.semester
				AND (
					(
						current_event_teilnehmer.gruppe_kurzbz IS NULL
						AND other_event_teilnehmer.gruppe_kurzbz IS NULL
						AND (
							current_event_teilnehmer.verband IS NULL
							OR (
								current_event_teilnehmer.verband = other_event_teilnehmer.verband
								AND (current_event_teilnehmer.gruppe IS NULL OR other_event_teilnehmer.gruppe IS NULL OR current_event_teilnehmer.gruppe = other_event_teilnehmer.gruppe)
							)
						)
					)
					OR
					(
						current_event_teilnehmer.gruppe_kurzbz IS NOT NULL
						AND other_event_teilnehmer.gruppe_kurzbz IS NOT NULL
						AND current_gruppe.direktinskription IS NOT TRUE
						AND other_gruppe.direktinskription IS NOT TRUE
					)
					OR
					(
						(
							current_event_teilnehmer.gruppe_kurzbz IS NULL
							AND other_event_teilnehmer.gruppe_kurzbz IS NOT NULL
							AND other_gruppe.direktinskription IS NOT TRUE
						)
						OR
						(
							current_event_teilnehmer.gruppe_kurzbz IS NOT NULL
							AND other_event_teilnehmer.gruppe_kurzbz IS NULL
							AND current_gruppe.direktinskription IS NOT TRUE
						)
					)
				)
				AND other_kalender.kalender_id NOT IN (
					SELECT vorgaenger_kalender_id
					FROM lehre.tbl_kalender
					WHERE vorgaenger_kalender_id IS NOT NULL
				)
			";

			$result = $dbModel->execReadOnlyQuery($sql_reservierung, [
				$data->kalender_id,
				$data->bis,
				$data->von,
				$data->kalender_id,
			]);

			if (!isError($result) && hasData($result))
			{
				foreach (getData($result) as $row)
					$collisions[] = $this->_ci->phraseslib->t('ui', 'reservierung_kollision') . ': ' . $row->gruppenname . ' (' . date('d.m.Y H:i', strtotime($row->von)) . ' - ' . date('d.m.Y H:i', strtotime($row->bis)) . ')';
			}
		}

		return $collisions;
	}

	public function checkAll($kalender_ids)
	{
		if (empty($kalender_ids)) return [];

		$dbModel = new DB_Model();

		$placeholders = implode(',', array_fill(0, count($kalender_ids), '?'));

		$sql = "
			SELECT DISTINCT ON (current_kalender.kalender_id) current_kalender.kalender_id
			FROM lehre.tbl_kalender current_kalender
	
			JOIN (
				SELECT tbl_lehreinheitgruppe.studiengang_kz, tbl_lehreinheitgruppe.semester, tbl_lehreinheitgruppe.verband, tbl_lehreinheitgruppe.gruppe,
					   tbl_lehreinheitgruppe.gruppe_kurzbz, tbl_kalender_lehreinheit.kalender_id
				FROM lehre.tbl_kalender_lehreinheit
				JOIN lehre.tbl_lehreinheit ON tbl_lehreinheit.lehreinheit_id = tbl_kalender_lehreinheit.lehreinheit_id
				JOIN lehre.tbl_lehreinheitgruppe ON tbl_lehreinheitgruppe.lehreinheit_id = tbl_lehreinheit.lehreinheit_id
				UNION
				SELECT tbl_kalender_event_teilnehmer.studiengang_kz, tbl_kalender_event_teilnehmer.semester, tbl_kalender_event_teilnehmer.verband, tbl_kalender_event_teilnehmer.gruppe,
					tbl_kalender_event_teilnehmer.gruppe_kurzbz, tbl_kalender_event_teilnehmer.kalender_id
				FROM lehre.tbl_kalender_event_teilnehmer
			) current_lehreinheitguppe ON current_lehreinheitguppe.kalender_id = current_kalender.kalender_id
	
			LEFT JOIN public.tbl_gruppe current_gruppe
				ON current_gruppe.gruppe_kurzbz = current_lehreinheitguppe.gruppe_kurzbz
	
			JOIN lehre.tbl_kalender other_kalender
				ON other_kalender.kalender_id != current_kalender.kalender_id
				AND other_kalender.von < current_kalender.bis
				AND other_kalender.bis > current_kalender.von
	
			JOIN (
				SELECT tbl_lehreinheitgruppe.studiengang_kz, tbl_lehreinheitgruppe.semester, tbl_lehreinheitgruppe.verband, tbl_lehreinheitgruppe.gruppe,
					   tbl_lehreinheitgruppe.gruppe_kurzbz, tbl_kalender_lehreinheit.kalender_id
				FROM lehre.tbl_kalender_lehreinheit
				JOIN lehre.tbl_lehreinheit ON tbl_lehreinheit.lehreinheit_id = tbl_kalender_lehreinheit.lehreinheit_id
				JOIN lehre.tbl_lehreinheitgruppe ON tbl_lehreinheitgruppe.lehreinheit_id = tbl_lehreinheit.lehreinheit_id
				UNION
				SELECT tbl_kalender_event_teilnehmer.studiengang_kz, tbl_kalender_event_teilnehmer.semester, tbl_kalender_event_teilnehmer.verband, tbl_kalender_event_teilnehmer.gruppe,
					tbl_kalender_event_teilnehmer.gruppe_kurzbz, tbl_kalender_event_teilnehmer.kalender_id
				FROM lehre.tbl_kalender_event_teilnehmer
			) other_lehreinheitguppe ON other_lehreinheitguppe.kalender_id = other_kalender.kalender_id
	
			LEFT JOIN public.tbl_gruppe other_gruppe
				ON other_gruppe.gruppe_kurzbz = other_lehreinheitguppe.gruppe_kurzbz
	
			WHERE current_kalender.kalender_id IN ({$placeholders})
			AND other_kalender.status_kurzbz NOT IN ('archived', 'deleted', 'to_delete')
			AND current_lehreinheitguppe.studiengang_kz = other_lehreinheitguppe.studiengang_kz
			AND current_lehreinheitguppe.semester = other_lehreinheitguppe.semester
			AND (
				(
					current_lehreinheitguppe.gruppe_kurzbz IS NULL
					AND other_lehreinheitguppe.gruppe_kurzbz IS NULL
					AND (
						current_lehreinheitguppe.verband IS NULL
						OR (
							current_lehreinheitguppe.verband = other_lehreinheitguppe.verband
							AND (current_lehreinheitguppe.gruppe IS NULL OR other_lehreinheitguppe.gruppe IS NULL OR current_lehreinheitguppe.gruppe = other_lehreinheitguppe.gruppe)
						)
					)
				)
				OR
				(
					current_lehreinheitguppe.gruppe_kurzbz IS NOT NULL
					AND other_lehreinheitguppe.gruppe_kurzbz IS NOT NULL
					AND current_gruppe.direktinskription IS NOT TRUE
					AND other_gruppe.direktinskription IS NOT TRUE
				)
				OR
				(
					(
						current_lehreinheitguppe.gruppe_kurzbz IS NULL
						AND other_lehreinheitguppe.gruppe_kurzbz IS NOT NULL
						AND other_gruppe.direktinskription IS NOT TRUE
					)
					OR
					(
						current_lehreinheitguppe.gruppe_kurzbz IS NOT NULL
						AND other_lehreinheitguppe.gruppe_kurzbz IS NULL
						AND current_gruppe.direktinskription IS NOT TRUE
					)
				)
			)
			AND other_kalender.kalender_id NOT IN (
				SELECT vorgaenger_kalender_id
				FROM lehre.tbl_kalender
				WHERE vorgaenger_kalender_id IS NOT NULL
			)
			
		";

		$result = $dbModel->execReadOnlyQuery($sql, $kalender_ids);

		if (isError($result) || !hasData($result)) return [];

		$grouped = [];
		foreach (getData($result) as $row)
			$grouped[$row->kalender_id][] = true;

		return $grouped;
	}

}