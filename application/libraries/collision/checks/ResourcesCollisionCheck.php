<?php

class ResourcesCollisionCheck implements ICollisionCheck
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
		return 'resources';
	}

	public function check($data)
	{
		if (!isset($data->betriebsmittel_ids, $data->von, $data->bis)) return [];

		if (empty($data->betriebsmittel_ids)) return [];

		if ($this->_ci->variablelib->getVar('ignore_kollision') === 'true') return [];
		if ($this->_ci->variablelib->getVar('ignore_resources_collisions') === 'true') return [];

		$this->_ci->KalenderModel->addSelect('kalender_id, von, bis, bm.beschreibung AS betriebsmittel_beschreibung');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_betriebsmittel_kalender bk', 'bk.eindeutige_kalender_gruppen_id = tbl_kalender.eindeutige_kalender_gruppen_id');
		$this->_ci->KalenderModel->addJoin('wawi.tbl_betriebsmittel bm', 'bm.betriebsmittel_id = bk.betriebsmittel_id');

		if (!empty($data->kalender_id))
			$this->_ci->KalenderModel->db->where('tbl_kalender.kalender_id !=', $data->kalender_id);
		$this->_ci->KalenderModel->db->where('tbl_kalender.kalender_id NOT IN (SELECT vorgaenger_kalender_id FROM lehre.tbl_kalender WHERE vorgaenger_kalender_id IS NOT NULL)', null, false);
		$this->_ci->KalenderModel->db->where_not_in('tbl_kalender.status_kurzbz', ['archived', 'deleted', 'to_delete', 'to_delete_live', 'to_delete_preview']);
		$this->_ci->KalenderModel->db->where_in('bk.betriebsmittel_id', $data->betriebsmittel_ids);


		$result = $this->_ci->KalenderModel->loadWhere(array(
			'von <' => $data->bis,
			'bis >' => $data->von,
		));

		if (isError($result)) return [];
		if (!hasData($result)) return [];

		return array_map(function($row)
		{
			return [
				'errorCode' => 'resource_collision',
				'message' => $this->_ci->phraseslib->t('ui', 'resource_collision') . ': ' . $row->betriebsmittel_beschreibung . ' (' . date('d.m.Y H:i', strtotime($row->von)) . ' - ' . date('d.m.Y H:i', strtotime($row->bis)) . ')'
			];
		}, getData($result));
	}
}