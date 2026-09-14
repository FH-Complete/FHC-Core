<?php

class LectureCollisionCheck implements ICollisionCheck
{

	private $_ci;

	public function __construct()
	{
		$this->_ci =& get_instance();
		$this->_ci->load->model('ressource/Kalender_model', 'KalenderModel');
		$this->_ci->load->model('ressource/Zeitsperre_model', 'ZeitsperreModel');
		$this->_ci->load->model('education/Lehreinheitmitarbeiter_model', 'LehreinheitmitarbeiterModel');
		$this->_ci->load->library('VariableLib', array('uid' => getAuthUID()));
		$this->_ci->load->library('PhrasesLib', array('ui'));

	}

	public function getName()
	{
		return 'lecture';
	}

	public function check($data)
	{
		if (!isset($data->von, $data->bis)) return [];
		if (empty($data->kalender_id) && empty($data->lehreinheit_id) && empty($data->uids)) return [];

		if ($this->_ci->variablelib->getVar('ignore_kollision') === 'true') return [];

		if (!empty($data->kalender_id))
			$uids = $this->_getUids($data->kalender_id);
		else if (!empty($data->lehreinheit_id))
			$uids = $this->_getUidsFromLehreinheit($data->lehreinheit_id);
		else
			$uids = $data->uids;

		if (empty($uids)) return [];

		$collisions = [];

		$collisions = array_merge($collisions, $this->_checkLehreinheit($uids, $data));
		$collisions = array_merge($collisions, $this->_checkReservierung($uids, $data));
		$collisions = array_merge($collisions, $this->_checkZeitsperre($uids, $data));

		return $collisions;
	}

	public function checkAll($kalender_ids)
	{
		if (empty($kalender_ids)) return [];

		$kalender_ids = array_values(array_unique(array_map('intval', $kalender_ids)));
		$postgres_array = '{' . implode(',', $kalender_ids) . '}';
		$kollisionsfreie_user = array_values(array_filter((array) unserialize(KOLLISIONSFREIE_USER)));
		$excluded_user_condition = '';
		$params = [$postgres_array];

		if (!empty($kollisionsfreie_user))
		{
			$placeholders = implode(',', array_fill(0, count($kollisionsfreie_user), '?'));
			$excluded_user_condition = "AND current_lecturer.mitarbeiter_uid NOT IN ($placeholders)";
			$params = array_merge($params, $kollisionsfreie_user);
		}

		$sql = "
			WITH current_kalender AS MATERIALIZED (
				SELECT kalender_id, von, bis
				FROM lehre.tbl_kalender
				WHERE kalender_id = ANY(?::bigint[])
			),
			current_lecturer AS MATERIALIZED (
				SELECT
					current_kalender.kalender_id,
					current_kalender.von,
					current_kalender.bis,
					current_lehreinheit_ma.mitarbeiter_uid
				FROM current_kalender
				JOIN lehre.tbl_kalender_lehreinheit current_kalender_le
					ON current_kalender_le.kalender_id = current_kalender.kalender_id
				JOIN lehre.tbl_lehreinheit current_lehreinheit
					ON current_lehreinheit.lehreinheit_id = current_kalender_le.lehreinheit_id
				JOIN lehre.tbl_lehreinheitmitarbeiter current_lehreinheit_ma
					ON current_lehreinheit_ma.lehreinheit_id = current_lehreinheit.lehreinheit_id
			)
			SELECT current_kalender.kalender_id
			FROM current_kalender
			WHERE EXISTS (
				SELECT 1
				FROM current_lecturer
				WHERE current_lecturer.kalender_id = current_kalender.kalender_id
					$excluded_user_condition
					AND (
						EXISTS (
							SELECT 1
							FROM lehre.tbl_lehreinheitmitarbeiter other_lehreinheit_ma
							JOIN lehre.tbl_lehreinheit other_lehreinheit
								ON other_lehreinheit.lehreinheit_id = other_lehreinheit_ma.lehreinheit_id
							JOIN lehre.tbl_kalender_lehreinheit other_kalender_le
								ON other_kalender_le.lehreinheit_id = other_lehreinheit.lehreinheit_id
							JOIN lehre.tbl_kalender other_kalender
								ON other_kalender.kalender_id = other_kalender_le.kalender_id
							WHERE other_lehreinheit_ma.mitarbeiter_uid = current_lecturer.mitarbeiter_uid
								AND other_kalender.kalender_id != current_lecturer.kalender_id
								AND other_kalender.von < current_lecturer.bis
								AND other_kalender.bis > current_lecturer.von
								AND other_kalender.status_kurzbz NOT IN ('archived', 'deleted', 'to_delete', 'to_delete_live', 'to_delete_preview')
								AND NOT EXISTS (
									SELECT 1
									FROM lehre.tbl_kalender nachfolger
									WHERE nachfolger.vorgaenger_kalender_id = other_kalender.kalender_id
								)
						)
						OR EXISTS (
							SELECT 1
							FROM lehre.tbl_kalender_event_teilnehmer other_teilnehmer
							JOIN lehre.tbl_kalender_event other_event
								ON other_event.kalender_id = other_teilnehmer.kalender_id
							JOIN lehre.tbl_kalender other_kalender
								ON other_kalender.kalender_id = other_event.kalender_id
							WHERE other_teilnehmer.uid = current_lecturer.mitarbeiter_uid
								AND other_kalender.kalender_id != current_lecturer.kalender_id
								AND other_kalender.von < current_lecturer.bis
								AND other_kalender.bis > current_lecturer.von
								AND other_kalender.status_kurzbz NOT IN ('archived', 'deleted', 'to_delete', 'to_delete_live', 'to_delete_preview')
								AND NOT EXISTS (
									SELECT 1
									FROM lehre.tbl_kalender nachfolger
									WHERE nachfolger.vorgaenger_kalender_id = other_kalender.kalender_id
								)
						)
						OR EXISTS (
							SELECT 1
							FROM campus.tbl_zeitsperre zeitsperre
							LEFT JOIN lehre.tbl_stunde vonstunde_z
								ON vonstunde_z.stunde = zeitsperre.vonstunde
							LEFT JOIN lehre.tbl_stunde bisstunde_z
								ON bisstunde_z.stunde = zeitsperre.bisstunde
							WHERE zeitsperre.mitarbeiter_uid = current_lecturer.mitarbeiter_uid
								AND zeitsperre.zeitsperretyp_kurzbz != 'ZVerfueg'
								AND (zeitsperre.vondatum + COALESCE(vonstunde_z.beginn, '00:00'))::timestamp < current_lecturer.bis
								AND (zeitsperre.bisdatum + COALESCE(bisstunde_z.ende, '23:59'))::timestamp > current_lecturer.von
						)
					)
			)";

		$dbModel = new DB_Model();
		$result = $dbModel->execReadOnlyQuery($sql, $params);
		$grouped = [];

		if (!isError($result) && hasData($result))
		{
			foreach (getData($result) as $row)
				$grouped[$row->kalender_id] = [true];
		}

		log_message('error', 'LectureCollisionCheck::checkAll() 1');
		return $grouped;
	}

	private function _getUids($kalender_id)
	{
		$kollisionsfreie_user = unserialize(KOLLISIONSFREIE_USER);

		$this->_ci->KalenderModel->addDistinct('mitarbeiter_uid, tbl_kalender_event_teilnehmer.uid');
		$this->_ci->KalenderModel->addSelect('mitarbeiter_uid, tbl_kalender_event_teilnehmer.uid');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_kalender_lehreinheit', 'kalender_id', 'LEFT');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_lehreinheit', 'lehreinheit_id', 'LEFT');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_lehreinheitmitarbeiter', 'lehreinheit_id', 'LEFT');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_kalender_event', 'kalender_id', 'LEFT');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_kalender_event_teilnehmer', 'tbl_kalender_event.kalender_id = tbl_kalender_event_teilnehmer.kalender_id', 'LEFT');

		$this->_ci->KalenderModel->db->group_start();
			$this->_ci->KalenderModel->db->where_not_in('mitarbeiter_uid', $kollisionsfreie_user);
			$this->_ci->KalenderModel->db->or_where('mitarbeiter_uid IS NULL', null, false);
		$this->_ci->KalenderModel->db->group_end();

		$result = $this->_ci->KalenderModel->loadWhere(array(
			'tbl_kalender.kalender_id' => $kalender_id
		));

		if (isError($result) || !hasData($result)) return [];

		$data = getData($result);
		$mitarbeiter_uids = array_filter(array_column($data, 'mitarbeiter_uid'));
		$event_teilnehmer = array_filter(array_column($data, 'uid'));

		return array_unique(array_merge($mitarbeiter_uids, $event_teilnehmer));
	}

	private function _getUidsFromLehreinheit($lehreinheit_id)
	{
		$kollisionsfreie_user = unserialize(KOLLISIONSFREIE_USER);

		$result = $this->_ci->LehreinheitmitarbeiterModel->loadWhere(array('lehreinheit_id' => $lehreinheit_id));

		if (isError($result) || !hasData($result)) return [];

		$uids = array_column(getData($result), 'mitarbeiter_uid');
		$uids = array_diff(array_filter($uids), $kollisionsfreie_user);

		return array_values(array_unique($uids));
	}

	private function _checkLehreinheit($uids, $data)
	{
		$kollisionsfreie_user = unserialize(KOLLISIONSFREIE_USER);

		$this->_ci->KalenderModel->addDistinct('mitarbeiter_uid, tbl_kalender.von, tbl_kalender.bis');
		$this->_ci->KalenderModel->addSelect('mitarbeiter_uid, tbl_kalender.von, tbl_kalender.bis');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_kalender_lehreinheit', 'kalender_id', 'LEFT');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_lehreinheit', 'lehreinheit_id', 'LEFT');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_lehreinheitmitarbeiter', 'lehreinheit_id', 'LEFT');

		$this->_ci->KalenderModel->db->where_in('mitarbeiter_uid', $uids);
		if (!empty($data->kalender_id))
			$this->_ci->KalenderModel->db->where('tbl_kalender.kalender_id !=', $data->kalender_id);
		$this->_ci->KalenderModel->db->where_not_in('tbl_kalender.status_kurzbz', array('archived', 'deleted', 'to_delete', 'to_delete_live', 'to_delete_preview'));
		$this->_ci->KalenderModel->db->where_not_in('mitarbeiter_uid', $kollisionsfreie_user);
		$this->_ci->KalenderModel->db->where(
			'tbl_kalender.kalender_id NOT IN (SELECT vorgaenger_kalender_id FROM lehre.tbl_kalender WHERE vorgaenger_kalender_id IS NOT NULL)',
			null, false
		);

		$result = $this->_ci->KalenderModel->loadWhere(array(
			'von <' => $data->bis,
			'bis >' => $data->von,
		));

		if (isError($result) || !hasData($result)) return [];

		return array_map(function($row)
		{
			return [
				'message' => $this->_ci->phraseslib->t('ui', 'ma_le_kollision') . ': ' . $row->mitarbeiter_uid . ' (' . date('d.m.Y H:i', strtotime($row->von)) . ' - ' . date('d.m.Y H:i', strtotime($row->bis)) . ')',
				'errorCode' => 'lector_collision',
			];
		}, getData($result));
	}

	private function _checkReservierung($uids, $data)
	{
		if ($this->_ci->variablelib->getVar('ignore_reservierung') === 'true') return [];

		$kollisionsfreie_user = unserialize(KOLLISIONSFREIE_USER);


		$this->_ci->KalenderModel->addDistinct('tbl_kalender_event_teilnehmer.uid, tbl_kalender.von, tbl_kalender.bis');
		$this->_ci->KalenderModel->addSelect('tbl_kalender_event_teilnehmer.uid, tbl_kalender.von, tbl_kalender.bis');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_kalender_event', 'kalender_id', 'LEFT');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_kalender_event_teilnehmer', 'tbl_kalender_event.kalender_id = tbl_kalender_event_teilnehmer.kalender_id', 'LEFT');

		$this->_ci->KalenderModel->db->where_in('tbl_kalender_event_teilnehmer.uid', $uids);
		if (!empty($data->kalender_id))
			$this->_ci->KalenderModel->db->where('tbl_kalender.kalender_id !=', $data->kalender_id);
		$this->_ci->KalenderModel->db->where_not_in('tbl_kalender.status_kurzbz', array('archived', 'deleted', 'to_delete', 'to_delete_live', 'to_delete_preview'));
		$this->_ci->KalenderModel->db->where_not_in('uid', $kollisionsfreie_user);
		$this->_ci->KalenderModel->db->where(
			'tbl_kalender.kalender_id NOT IN (SELECT vorgaenger_kalender_id FROM lehre.tbl_kalender WHERE vorgaenger_kalender_id IS NOT NULL)',
			null, false
		);
		$result = $this->_ci->KalenderModel->loadWhere(array(
			'von <' => $data->bis,
			'bis >' => $data->von,
		));

		if (isError($result) || !hasData($result)) return [];

		return array_map(function($row)
		{
			return [
				'message' => $this->_ci->phraseslib->t('ui', 'reservierung_kollision') . ': ' . $row->uid . ' (' . date('d.m.Y H:i', strtotime($row->von)) . ' - ' . date('d.m.Y H:i', strtotime($row->bis)) . ')',
				'errorCode' => 'reservation_collision',
			];
		}, getData($result));
	}

	private function _checkZeitsperre($uids, $data)
	{
		if ($this->_ci->variablelib->getVar('ignore_zeitsperre') === 'true') return [];

		$this->_ci->ZeitsperreModel->addSelect('mitarbeiter_uid, vondatum, vonstunde_z.beginn as von_beginn, bisdatum, bisstunde_z.ende as bis_ende');
		$this->_ci->ZeitsperreModel->addJoin('lehre.tbl_stunde vonstunde_z', 'vonstunde_z.stunde = tbl_zeitsperre.vonstunde', 'LEFT');
		$this->_ci->ZeitsperreModel->addJoin('lehre.tbl_stunde bisstunde_z', 'bisstunde_z.stunde = tbl_zeitsperre.bisstunde', 'LEFT');
		$this->_ci->ZeitsperreModel->db->where('zeitsperretyp_kurzbz !=', 'ZVerfueg');
		$this->_ci->ZeitsperreModel->db->where('(tbl_zeitsperre.vondatum + COALESCE(vonstunde_z.beginn, \'00:00\'))::timestamp <', $data->bis);
		$this->_ci->ZeitsperreModel->db->where('(tbl_zeitsperre.bisdatum + COALESCE(bisstunde_z.ende, \'23:59\'))::timestamp >', $data->von);

		$this->_ci->ZeitsperreModel->db->where_in('mitarbeiter_uid', $uids);
		$result = $this->_ci->ZeitsperreModel->load();

		if (isError($result) || !hasData($result)) return [];

		return array_map(function($row)
		{
			return [
				'message' => $this->_ci->phraseslib->t('ui', 'ma_zeitsperre_kollision') . ': ' . $row->mitarbeiter_uid . ' (' . date('d.m.Y H:i', strtotime($row->vondatum . ' ' . $row->von_beginn)) . ' - ' . date('d.m.Y H:i', strtotime($row->bisdatum . ' ' . $row->bis_ende)) . ')',
				'errorCode' => 'absences_collision',
			];
		}, getData($result));
	}

}
