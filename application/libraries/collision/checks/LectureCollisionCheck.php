<?php

class LectureCollisionCheck implements ICollisionCheck
{

	private $_ci;

	public function __construct()
	{
		$this->_ci =& get_instance();
		$this->_ci->load->model('ressource/Kalender_model', 'KalenderModel');
		$this->_ci->load->model('ressource/zeitsperre_model', 'ZeitsperreModel');
		$this->_ci->load->library('VariableLib', array('uid' => getAuthUID()));
		$this->_ci->load->library('PhrasesLib', array('ui'));

	}

	public function getName()
	{
		return 'lecture';
	}

	public function check($data)
	{
		if (!isset($data->von, $data->bis, $data->kalender_id)) return [];

		if ($this->_ci->variablelib->getVar('ignore_kollision') === 'true') return [];

		$uids = $this->_getUids($data->kalender_id);

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

		$kollisionsfreie_user = unserialize(KOLLISIONSFREIE_USER);
		$grouped = [];

		$this->_ci->KalenderModel->addSelect('DISTINCT ON (tbl_kalender.kalender_id) tbl_kalender.kalender_id');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_kalender_lehreinheit current_kalender_le', 'current_kalender_le.kalender_id = tbl_kalender.kalender_id');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_lehreinheit current_lehreinheit', 'current_lehreinheit.lehreinheit_id = current_kalender_le.lehreinheit_id');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_lehreinheitmitarbeiter current_lehreinheit_ma', 'current_lehreinheit_ma.lehreinheit_id = current_lehreinheit.lehreinheit_id');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_lehreinheitmitarbeiter other_lehreinheithreinheit_ma', 'other_lehreinheithreinheit_ma.mitarbeiter_uid = current_lehreinheit_ma.mitarbeiter_uid');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_lehreinheit other_lehreinheit', 'other_lehreinheit.lehreinheit_id = other_lehreinheithreinheit_ma.lehreinheit_id');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_kalender_lehreinheit other_kalender_le', 'other_kalender_le.lehreinheit_id = other_lehreinheit.lehreinheit_id');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_kalender other_kalender', 'other_kalender.kalender_id = other_kalender_le.kalender_id');

		$this->_ci->KalenderModel->db->where('other_kalender.kalender_id != tbl_kalender.kalender_id', null, false);
		$this->_ci->KalenderModel->db->where('other_kalender.von < tbl_kalender.bis', null, false);
		$this->_ci->KalenderModel->db->where('other_kalender.bis > tbl_kalender.von', null, false);
		$this->_ci->KalenderModel->db->where_not_in('other_kalender.status_kurzbz', ['archived', 'deleted', 'to_delete']);
		$this->_ci->KalenderModel->db->where_not_in('current_lehreinheit_ma.mitarbeiter_uid', $kollisionsfreie_user);
		$this->_ci->KalenderModel->db->where_in('tbl_kalender.kalender_id', $kalender_ids);
		$this->_ci->KalenderModel->db->where(
			'other_kalender.kalender_id NOT IN (SELECT vorgaenger_kalender_id FROM lehre.tbl_kalender WHERE vorgaenger_kalender_id IS NOT NULL)',
			null, false
		);
		$result = $this->_ci->KalenderModel->load();
		if (!isError($result) && hasData($result))
			foreach (getData($result) as $row)
				$grouped[$row->kalender_id][] = true;

		$this->_ci->KalenderModel->addSelect('DISTINCT ON (tbl_kalender.kalender_id) tbl_kalender.kalender_id');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_kalender_lehreinheit current_kalender_le', 'current_kalender_le.kalender_id = tbl_kalender.kalender_id');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_lehreinheit current_lehreinheit', 'current_lehreinheit.lehreinheit_id = current_kalender_le.lehreinheit_id');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_lehreinheitmitarbeiter current_lehreinheit_ma', 'current_lehreinheit_ma.lehreinheit_id = current_lehreinheit.lehreinheit_id');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_kalender_event_teilnehmer other_t', 'other_t.uid = current_lehreinheit_ma.mitarbeiter_uid');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_kalender_event other_e', 'other_e.kalender_id = other_t.kalender_id');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_kalender other_kalender', 'other_kalender.kalender_id = other_e.kalender_id');

		$this->_ci->KalenderModel->db->where('other_kalender.kalender_id != tbl_kalender.kalender_id', null, false);
		$this->_ci->KalenderModel->db->where('other_kalender.von < tbl_kalender.bis', null, false);
		$this->_ci->KalenderModel->db->where('other_kalender.bis > tbl_kalender.von', null, false);
		$this->_ci->KalenderModel->db->where_not_in('other_kalender.status_kurzbz', ['archived', 'deleted', 'to_delete']);
		$this->_ci->KalenderModel->db->where_not_in('current_lehreinheit_ma.mitarbeiter_uid', $kollisionsfreie_user);
		$this->_ci->KalenderModel->db->where_in('tbl_kalender.kalender_id', $kalender_ids);
		$this->_ci->KalenderModel->db->where(
			'other_kalender.kalender_id NOT IN (SELECT vorgaenger_kalender_id FROM lehre.tbl_kalender WHERE vorgaenger_kalender_id IS NOT NULL)',
			null, false
		);
		$result = $this->_ci->KalenderModel->load();
		if (!isError($result) && hasData($result))
			foreach (getData($result) as $row)
				$grouped[$row->kalender_id][] = true;

		$this->_ci->KalenderModel->addSelect('DISTINCT ON (tbl_kalender.kalender_id) tbl_kalender.kalender_id');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_kalender_lehreinheit current_kalender_le', 'current_kalender_le.kalender_id = tbl_kalender.kalender_id');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_lehreinheit current_lehreinheit', 'current_lehreinheit.lehreinheit_id = current_kalender_le.lehreinheit_id');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_lehreinheitmitarbeiter current_lehreinheit_ma', 'current_lehreinheit_ma.lehreinheit_id = current_lehreinheit.lehreinheit_id');
		$this->_ci->KalenderModel->addJoin('campus.tbl_zeitsperre z',
			"z.mitarbeiter_uid = current_lehreinheit_ma.mitarbeiter_uid 
			AND z.zeitsperretyp_kurzbz != 'ZVerfueg'");
		$this->_ci->KalenderModel->addJoin('lehre.tbl_stunde vonstunde_z', 'vonstunde_z.stunde = z.vonstunde', 'LEFT');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_stunde bisstunde_z', 'bisstunde_z.stunde = z.bisstunde', 'LEFT');

		$this->_ci->KalenderModel->db->where('(z.vondatum + COALESCE(vonstunde_z.beginn, \'00:00\'))::timestamp < tbl_kalender.bis', null, false);
		$this->_ci->KalenderModel->db->where('(z.bisdatum + COALESCE(bisstunde_z.ende, \'23:59\'))::timestamp > tbl_kalender.von', null, false);
		$this->_ci->KalenderModel->db->where_not_in('current_lehreinheit_ma.mitarbeiter_uid', $kollisionsfreie_user);
		$this->_ci->KalenderModel->db->where_in('tbl_kalender.kalender_id', $kalender_ids);
		$result = $this->_ci->KalenderModel->load();


		if (!isError($result) && hasData($result))
			foreach (getData($result) as $row)
				$grouped[$row->kalender_id][] = true;


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

		$this->_ci->KalenderModel->db->where_not_in('mitarbeiter_uid', $kollisionsfreie_user);

		$result = $this->_ci->KalenderModel->loadWhere(array(
			'tbl_kalender.kalender_id' => $kalender_id
		));

		if (isError($result) || !hasData($result)) return [];

		$data = getData($result);
		$mitarbeiter_uids = array_filter(array_column($data, 'mitarbeiter_uid'));
		$event_teilnehmer = array_filter(array_column($data, 'uid'));

		return array_unique(array_merge($mitarbeiter_uids, $event_teilnehmer));
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
		$this->_ci->KalenderModel->db->where('tbl_kalender.kalender_id !=', $data->kalender_id);
		$this->_ci->KalenderModel->db->where_not_in('tbl_kalender.status_kurzbz', array('archived', 'deleted', 'to_delete'));
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

		return array_map(function($row) {
			return $this->_ci->phraseslib->t('ui', 'ma_le_kollision') . ': ' . $row->mitarbeiter_uid . ' (' . date('d.m.Y H:i', strtotime($row->von)) . ' - ' . date('d.m.Y H:i', strtotime($row->bis)) . ')';
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
		$this->_ci->KalenderModel->db->where('tbl_kalender.kalender_id !=', $data->kalender_id);
		$this->_ci->KalenderModel->db->where_not_in('tbl_kalender.status_kurzbz', array('archived', 'deleted', 'to_delete'));
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

		return array_map(function($row) {
			return $this->_ci->phraseslib->t('ui', 'reservierung_kollision') . ': ' . $row->uid . ' (' . date('d.m.Y H:i', strtotime($row->von)) . ' - ' . date('d.m.Y H:i', strtotime($row->bis)) . ')';
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

		return array_map(function($row) {
			return $this->_ci->phraseslib->t('ui', 'ma_zeitsperre_kollision') . ': ' . $row->mitarbeiter_uid . ' (' . date('d.m.Y H:i', strtotime($row->vondatum . ' ' . $row->von_beginn)) . ' - ' . date('d.m.Y H:i', strtotime($row->bisdatum . ' ' . $row->bis_ende)) . ')';		}, getData($result));
	}

}