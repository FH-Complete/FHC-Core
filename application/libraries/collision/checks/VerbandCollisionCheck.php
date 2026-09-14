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
		if (!isset($data->von, $data->bis)) return [];
		if (!isset($data->kalender_id) && !isset($data->lehreinheit_id)) return [];

		if ($this->_ci->variablelib->getVar('ignore_kollision') === 'true') return [];

		$kollision_student = $this->_ci->variablelib->getVar('kollision_student') === 'false';
		$kollision_reservierung = $this->_ci->variablelib->getVar('ignore_reservierung') === 'false';

		if (!isset($data->kalender_id)) $kollision_reservierung = false;

		if (!$kollision_student && !$kollision_reservierung) return [];

		$dbModel = new DB_Model();
		$collisions = [];

		$group_matching_qry = "
			(
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
		";

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

			$other_lehreinheitgruppe_subquery = "
				SELECT tbl_lehreinheitgruppe.studiengang_kz, tbl_lehreinheitgruppe.semester, tbl_lehreinheitgruppe.verband, tbl_lehreinheitgruppe.gruppe,
					tbl_lehreinheitgruppe.gruppe_kurzbz, tbl_kalender_lehreinheit.kalender_id
				FROM lehre.tbl_kalender_lehreinheit
				JOIN lehre.tbl_lehreinheit ON tbl_lehreinheit.lehreinheit_id = tbl_kalender_lehreinheit.lehreinheit_id
				JOIN lehre.tbl_lehreinheitgruppe ON tbl_lehreinheitgruppe.lehreinheit_id = tbl_lehreinheit.lehreinheit_id
            ". $union_event ."
        ";

			if (isset($data->kalender_id))
			{
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
						". $other_lehreinheitgruppe_subquery ."
					) current_lehreinheitguppe ON current_lehreinheitguppe.kalender_id = current_kalender.kalender_id
	
					LEFT JOIN public.tbl_gruppe current_gruppe
						ON current_gruppe.gruppe_kurzbz = current_lehreinheitguppe.gruppe_kurzbz
	
					JOIN lehre.tbl_kalender other_kalender
						ON other_kalender.kalender_id != current_kalender.kalender_id
						AND other_kalender.von < ?
						AND other_kalender.bis > ?
	
					JOIN (
						". $other_lehreinheitgruppe_subquery ."
					) other_lehreinheitguppe ON other_lehreinheitguppe.kalender_id = other_kalender.kalender_id
	
					LEFT JOIN public.tbl_gruppe other_gruppe
						ON other_gruppe.gruppe_kurzbz = other_lehreinheitguppe.gruppe_kurzbz
	
					LEFT JOIN public.tbl_studiengang stg
						ON stg.studiengang_kz = other_lehreinheitguppe.studiengang_kz
	
					WHERE current_kalender.kalender_id = ?
					AND other_kalender.status_kurzbz NOT IN ('archived', 'deleted', 'to_delete', 'to_delete_live', 'to_delete_preview')
					AND current_lehreinheitguppe.studiengang_kz = other_lehreinheitguppe.studiengang_kz
					AND current_lehreinheitguppe.semester = other_lehreinheitguppe.semester
					AND ". $group_matching_qry ."
					AND other_kalender.kalender_id NOT IN (
						SELECT vorgaenger_kalender_id
						FROM lehre.tbl_kalender
						WHERE vorgaenger_kalender_id IS NOT NULL
					)
            ";

				$params = [$data->bis, $data->von, $data->kalender_id];
			}
			else
			{
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
					FROM lehre.tbl_lehreinheitgruppe current_lehreinheitguppe
					LEFT JOIN public.tbl_gruppe current_gruppe
						ON current_gruppe.gruppe_kurzbz = current_lehreinheitguppe.gruppe_kurzbz
	
					JOIN lehre.tbl_kalender other_kalender
						ON other_kalender.von < ?
						AND other_kalender.bis > ?
	
					JOIN (
						". $other_lehreinheitgruppe_subquery ."
					) other_lehreinheitguppe ON other_lehreinheitguppe.kalender_id = other_kalender.kalender_id
	
					LEFT JOIN public.tbl_gruppe other_gruppe
						ON other_gruppe.gruppe_kurzbz = other_lehreinheitguppe.gruppe_kurzbz
	
					LEFT JOIN public.tbl_studiengang stg
						ON stg.studiengang_kz = other_lehreinheitguppe.studiengang_kz
	
					WHERE current_lehreinheitguppe.lehreinheit_id = ?
					AND other_kalender.status_kurzbz NOT IN ('archived', 'deleted', 'to_delete', 'to_delete_live', 'to_delete_preview')
					AND current_lehreinheitguppe.studiengang_kz = other_lehreinheitguppe.studiengang_kz
					AND current_lehreinheitguppe.semester = other_lehreinheitguppe.semester
					AND ". $group_matching_qry ."
					AND other_kalender.kalender_id NOT IN (
						SELECT vorgaenger_kalender_id
						FROM lehre.tbl_kalender
						WHERE vorgaenger_kalender_id IS NOT NULL
					)
				";

				$params = [$data->bis, $data->von, $data->lehreinheit_id];
			}

			$result = $dbModel->execReadOnlyQuery($sql_gruppen, $params);

			if (!isError($result) && hasData($result))
			{
				foreach (getData($result) as $row)
				{
					$collisions[] = [
						'message' => $this->_ci->phraseslib->t('ui', 'verband_kollision') . ': ' . $row->gruppenname . ' (' . date('d.m.Y H:i', strtotime($row->von)) . ' - ' . date('d.m.Y H:i', strtotime($row->bis)) . ')',
						'errorCode' => 'verband_collision',
					];
				}
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
				AND other_kalender.status_kurzbz NOT IN ('archived', 'deleted', 'to_delete', 'to_delete_live', 'to_delete_preview')
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
				{
					$collisions[] = [
						'errorCode' => 'reservation_collision',
						'message' => $this->_ci->phraseslib->t('ui', 'reservierung_kollision') . ': ' . $row->gruppenname . ' (' . date('d.m.Y H:i', strtotime($row->von)) . ' - ' . date('d.m.Y H:i', strtotime($row->bis)) . ')'
					];
				}
			}
		}

		return $collisions;
	}

	public function checkAll($kalender_ids)
	{
		if (empty($kalender_ids)) return [];

		$kalender_ids = array_values(array_unique(array_map('intval', $kalender_ids)));
		$postgres_array = '{' . implode(',', $kalender_ids) . '}';
		$dbModel = new DB_Model();
		$lehreinheit_group_match = $this->_buildGroupMatchCondition(
			'current_group_assignment',
			'other_lehreinheitgruppe',
			'current_group_assignment.direktinskription',
			'other_gruppe.direktinskription'
		);
		$event_group_match = $this->_buildGroupMatchCondition(
			'current_group_assignment',
			'other_event_teilnehmer',
			'current_group_assignment.direktinskription',
			'other_gruppe.direktinskription'
		);

		$sql = "
			WITH current_kalender AS MATERIALIZED (
				SELECT kalender_id, von, bis
				FROM lehre.tbl_kalender
				WHERE kalender_id = ANY(?::bigint[])
			),
			current_group_assignment AS MATERIALIZED (
				SELECT
					current_kalender.kalender_id,
					current_lehreinheitgruppe.studiengang_kz,
					current_lehreinheitgruppe.semester,
					current_lehreinheitgruppe.verband,
					current_lehreinheitgruppe.gruppe,
					current_lehreinheitgruppe.gruppe_kurzbz,
					current_gruppe.direktinskription
				FROM current_kalender
				JOIN lehre.tbl_kalender_lehreinheit current_kalender_le
					ON current_kalender_le.kalender_id = current_kalender.kalender_id
				JOIN lehre.tbl_lehreinheit current_lehreinheit
					ON current_lehreinheit.lehreinheit_id = current_kalender_le.lehreinheit_id
				JOIN lehre.tbl_lehreinheitgruppe current_lehreinheitgruppe
					ON current_lehreinheitgruppe.lehreinheit_id = current_lehreinheit.lehreinheit_id
				LEFT JOIN public.tbl_gruppe current_gruppe
					ON current_gruppe.gruppe_kurzbz = current_lehreinheitgruppe.gruppe_kurzbz

				UNION ALL

				SELECT
					current_kalender.kalender_id,
					current_event_teilnehmer.studiengang_kz,
					current_event_teilnehmer.semester,
					current_event_teilnehmer.verband,
					current_event_teilnehmer.gruppe,
					current_event_teilnehmer.gruppe_kurzbz,
					current_gruppe.direktinskription
				FROM current_kalender
				JOIN lehre.tbl_kalender_event_teilnehmer current_event_teilnehmer
					ON current_event_teilnehmer.kalender_id = current_kalender.kalender_id
				LEFT JOIN public.tbl_gruppe current_gruppe
					ON current_gruppe.gruppe_kurzbz = current_event_teilnehmer.gruppe_kurzbz
			)
			SELECT current_kalender.kalender_id
			FROM current_kalender
			WHERE EXISTS (
				SELECT 1
				FROM current_group_assignment
				WHERE current_group_assignment.kalender_id = current_kalender.kalender_id
					AND EXISTS (
						SELECT 1
						FROM lehre.tbl_kalender other_kalender
						WHERE other_kalender.kalender_id != current_kalender.kalender_id
							AND other_kalender.von < current_kalender.bis
							AND other_kalender.bis > current_kalender.von
							AND other_kalender.status_kurzbz NOT IN ('archived', 'deleted', 'to_delete', 'to_delete_live', 'to_delete_preview')
							AND NOT EXISTS (
								SELECT 1
								FROM lehre.tbl_kalender nachfolger
								WHERE nachfolger.vorgaenger_kalender_id = other_kalender.kalender_id
							)
							AND (
								EXISTS (
									SELECT 1
									FROM lehre.tbl_kalender_lehreinheit other_kalender_le
									JOIN lehre.tbl_lehreinheit other_lehreinheit
										ON other_lehreinheit.lehreinheit_id = other_kalender_le.lehreinheit_id
									JOIN lehre.tbl_lehreinheitgruppe other_lehreinheitgruppe
										ON other_lehreinheitgruppe.lehreinheit_id = other_lehreinheit.lehreinheit_id
									LEFT JOIN public.tbl_gruppe other_gruppe
										ON other_gruppe.gruppe_kurzbz = other_lehreinheitgruppe.gruppe_kurzbz
									WHERE other_kalender_le.kalender_id = other_kalender.kalender_id
										AND $lehreinheit_group_match
								)
								OR EXISTS (
									SELECT 1
									FROM lehre.tbl_kalender_event_teilnehmer other_event_teilnehmer
									LEFT JOIN public.tbl_gruppe other_gruppe
										ON other_gruppe.gruppe_kurzbz = other_event_teilnehmer.gruppe_kurzbz
									WHERE other_event_teilnehmer.kalender_id = other_kalender.kalender_id
										AND $event_group_match
								)
							)
					)
			)";

		$result = $dbModel->execReadOnlyQuery($sql, [$postgres_array]);
		if (isError($result) || !hasData($result)) return [];

		$grouped = [];
		foreach (getData($result) as $row)
			$grouped[$row->kalender_id] = [true];

		log_message('error', 'VerbandCollisionCheck::checkAll() 1');
		return $grouped;
	}

	private function _buildGroupMatchCondition($current_alias, $other_alias, $current_direct, $other_direct)
	{
		return "
			$current_alias.studiengang_kz = $other_alias.studiengang_kz
			AND $current_alias.semester = $other_alias.semester
			AND (
				(
					$current_alias.gruppe_kurzbz IS NULL
					AND $other_alias.gruppe_kurzbz IS NULL
					AND (
						$current_alias.verband IS NULL
						OR (
							$current_alias.verband = $other_alias.verband
							AND (
								$current_alias.gruppe IS NULL
								OR $other_alias.gruppe IS NULL
								OR $current_alias.gruppe = $other_alias.gruppe
							)
						)
					)
				)
				OR (
					$current_alias.gruppe_kurzbz IS NOT NULL
					AND $other_alias.gruppe_kurzbz IS NOT NULL
					AND $current_direct IS NOT TRUE
					AND $other_direct IS NOT TRUE
				)
				OR (
					$current_alias.gruppe_kurzbz IS NULL
					AND $other_alias.gruppe_kurzbz IS NOT NULL
					AND $other_direct IS NOT TRUE
				)
				OR (
					$current_alias.gruppe_kurzbz IS NOT NULL
					AND $other_alias.gruppe_kurzbz IS NULL
					AND $current_direct IS NOT TRUE
				)
			)";
	}

}
