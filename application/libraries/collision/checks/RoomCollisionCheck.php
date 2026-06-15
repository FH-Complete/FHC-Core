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
		if (!isset($data->ort_kurzbz, $data->von, $data->bis, $data->kalender_id)) return [];

		if ($this->_ci->variablelib->getVar('ignore_kollision') === 'true') return [];

		$this->_ci->KalenderModel->addSelect('kalender_id, ort_kurzbz, von, bis');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_kalender_ort', 'kalender_id');

		$this->_ci->KalenderModel->db->where('tbl_kalender.kalender_id !=', $data->kalender_id);
		$this->_ci->KalenderModel->db->where('tbl_kalender.kalender_id NOT IN (SELECT vorgaenger_kalender_id FROM lehre.tbl_kalender WHERE vorgaenger_kalender_id IS NOT NULL)', null, false);
		$this->_ci->KalenderModel->db->where_not_in('tbl_kalender.status_kurzbz', ['archived', 'deleted', 'to_delete']);

		$result = $this->_ci->KalenderModel->loadWhere(array(
			'von <' => $data->bis,
			'bis >' => $data->von,
			'ort_kurzbz' => $data->ort_kurzbz,
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

		$this->_ci->KalenderModel->addSelect('DISTINCT ON (tbl_kalender.kalender_id) tbl_kalender.kalender_id');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_kalender_ort current_ort', 'current_ort.kalender_id = tbl_kalender.kalender_id');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_kalender_ort other_ort', 'other_ort.ort_kurzbz = current_ort.ort_kurzbz');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_kalender other_kalender', 'other_kalender.kalender_id = other_ort.kalender_id');

		$this->_ci->KalenderModel->db->where('other_kalender.kalender_id != tbl_kalender.kalender_id', null, false);
		$this->_ci->KalenderModel->db->where('other_kalender.von < tbl_kalender.bis', null, false);
		$this->_ci->KalenderModel->db->where('other_kalender.bis > tbl_kalender.von', null, false);
		$this->_ci->KalenderModel->db->where_not_in('other_kalender.status_kurzbz', ['archived', 'deleted', 'to_delete']);
		$this->_ci->KalenderModel->db->where_in('tbl_kalender.kalender_id', $kalender_ids);
		$this->_ci->KalenderModel->db->where('other_kalender.kalender_id NOT IN (SELECT vorgaenger_kalender_id FROM lehre.tbl_kalender WHERE vorgaenger_kalender_id IS NOT NULL)', null, false);

		$result = $this->_ci->KalenderModel->load();

		if (isError($result) || !hasData($result)) return [];

		$grouped = [];
		foreach (getData($result) as $row)
		{
			$grouped[$row->kalender_id][] = true;
		}

		return $grouped;
	}
}