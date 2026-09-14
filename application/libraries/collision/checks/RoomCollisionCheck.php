<?php

class RoomCollisionCheck implements ICollisionCheck
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
		return 'room';
	}

	public function check($data)
	{
		if (!isset($data->ort_kurzbz, $data->von, $data->bis) || isEmptyArray($data->ort_kurzbz)) return [];

		if ($this->_ci->variablelib->getVar('ignore_kollision') === 'true') return [];

		$this->_ci->KalenderModel->addSelect('kalender_id, ort_kurzbz, von, bis');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_kalender_ort', 'kalender_id');

		if (!empty($data->kalender_id))
			$this->_ci->KalenderModel->db->where('tbl_kalender.kalender_id !=', $data->kalender_id);
		$this->_ci->KalenderModel->db->where('tbl_kalender.kalender_id NOT IN (SELECT vorgaenger_kalender_id FROM lehre.tbl_kalender WHERE vorgaenger_kalender_id IS NOT NULL)', null, false);
		$this->_ci->KalenderModel->db->where_not_in('tbl_kalender.status_kurzbz', array('archived', 'deleted', 'to_delete', 'to_delete_live', 'to_delete_preview'));

		$this->_ci->KalenderModel->db->where_in('ort_kurzbz', $data->ort_kurzbz);
		$result = $this->_ci->KalenderModel->loadWhere(array(
			'von <' => $data->bis,
			'bis >' => $data->von,
		));

		if (isError($result)) return [];
		if (!hasData($result)) return [];

		return array_map(function($row)
		{
			return [
				'errorCode' => 'room_collision',
				'message' => $this->_ci->phraseslib->t('ui', 'raum_kollision') . ': ' . $row->ort_kurzbz . ' (' . date('d.m.Y H:i', strtotime($row->von)) . ' - ' . date('d.m.Y H:i', strtotime($row->bis)) . ')'
			];
		}, getData($result));
	}

	public function checkAll($kalender_ids)
	{
		if (empty($kalender_ids)) return [];

		$kalender_ids = array_values(array_unique(array_map('intval', $kalender_ids)));
		$postgres_array = '{' . implode(',', $kalender_ids) . '}';
		$dbModel = new DB_Model();
		$sql = "
			WITH current_kalender AS MATERIALIZED (
				SELECT kalender_id, von, bis
				FROM lehre.tbl_kalender
				WHERE kalender_id = ANY(?::bigint[])
			)
			SELECT current_kalender.kalender_id
			FROM current_kalender
			WHERE EXISTS (
				SELECT 1
				FROM lehre.tbl_kalender_ort current_ort
				JOIN lehre.tbl_kalender_ort other_ort
					ON other_ort.ort_kurzbz = current_ort.ort_kurzbz
				JOIN lehre.tbl_kalender other_kalender
					ON other_kalender.kalender_id = other_ort.kalender_id
				WHERE current_ort.kalender_id = current_kalender.kalender_id
					AND other_kalender.kalender_id != current_kalender.kalender_id
					AND other_kalender.von < current_kalender.bis
					AND other_kalender.bis > current_kalender.von
					AND other_kalender.status_kurzbz NOT IN ('archived', 'deleted', 'to_delete', 'to_delete_live', 'to_delete_preview')
					AND NOT EXISTS (
						SELECT 1
						FROM lehre.tbl_kalender nachfolger
						WHERE nachfolger.vorgaenger_kalender_id = other_kalender.kalender_id
					)
			)";

		$result = $dbModel->execReadOnlyQuery($sql, [$postgres_array]);

		log_message('error', 'RoomCollisionCheck::checkAll() 1');
		if (isError($result) || !hasData($result)) return [];

		$grouped = [];
		foreach (getData($result) as $row)
		{
			$grouped[$row->kalender_id][] = true;
		}

		return $grouped;
	}
}
