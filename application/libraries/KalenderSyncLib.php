<?php

if (! defined("BASEPATH")) exit("No direct script access allowed");

class KalenderSyncLib
{
	private $_ci;

	private $status_map = array(
		'preview' => array('planning', 'sync_preview'),
		'live' => array('planning', 'sync_preview', 'preview', 'sync_live')
	);

	public function __construct()
	{
		$this->_ci =& get_instance();

		$this->_ci->load->model('ressource/Kalender_model', 'KalenderModel');
		$this->_ci->load->model('ressource/Kalendersyncstatus_model', 'KalendersyncstatusModel');
		$this->_ci->load->model('organisation/Studiengang_model', 'StudiengangModel');
		$this->_ci->load->model('organisation/Organisationseinheit_model', 'OrganisationseinheitModel');
	}

	public function run($studiensemester_kurzbz)
	{
		$this->_ci->KalendersyncstatusModel->db->where('studiensemester_kurzbz', $studiensemester_kurzbz);

		$configs = $this->_ci->KalendersyncstatusModel->load();

		if (isError($configs))
			return $configs;

		if (!hasData($configs))
			return success([]);

		$results = [];
		$mail_infos_gesamt = [];

		foreach (getData($configs) as $config)
		{

			$ziel_status = isEmptyString($config->sync_status_kurzbz) ? 'live' : $config->sync_status_kurzbz;

			$relevante_stati = array_merge($this->status_map[$ziel_status], array('todelete'));

			$kalender_ids_result = $this->getKalenderIds(
				$config->oe_kurzbz,
				$config->studiensemester_kurzbz,
				$relevante_stati,
				$config->ausbildungssemester,
				$config->studienplan_id,
				$config->datum_bis
			);

			if (isError($kalender_ids_result))
				return $kalender_ids_result;

			$kalender_ids = getData($kalender_ids_result);

			$sync_result = $this->_syncKalenderIds($kalender_ids, $ziel_status);

			if (isError($sync_result))
				return $sync_result;

			$sync_data = getData($sync_result);

			if ((bool) $config->mail)
				$mail_infos_gesamt = array_merge($mail_infos_gesamt, $sync_data['mail_infos']);

			$results[] = [
				'kalender_syncstatus_id' => $config->kalender_syncstatus_id,
				'oe_kurzbz' => $config->oe_kurzbz,
				'ausbildungssemester' => $config->ausbildungssemester,
				'studienplan_id' => $config->studienplan_id,
				'ziel_status' => $ziel_status,
				'mail' => (bool) $config->mail,
				'synced' => $sync_data['synced']
			];
		}

		if (!empty($mail_infos_gesamt))
		{
			$this->_ci->load->library('KalenderNotificationLib');
			$this->_ci->kalendernotificationlib->sendMails($mail_infos_gesamt);
		}

		return success($results);
	}

	public function runManual($oe_kurzbz, $studiensemester_kurzbz, $ausbildungssemester = null, $studienplan_id = null, $mail = true, $datum_bis = null, $ziel_status = 'live')
	{
		$relevante_stati = array_merge($this->status_map[$ziel_status], array('todelete'));

		$kalender_ids_result = $this->getKalenderIds(
			$oe_kurzbz,
			$studiensemester_kurzbz,
			$relevante_stati,
			$ausbildungssemester,
			$studienplan_id,
			$datum_bis
		);

		if (isError($kalender_ids_result))
			return $kalender_ids_result;

		$kalender_ids = getData($kalender_ids_result);

		$sync_result = $this->_syncKalenderIds($kalender_ids, $ziel_status);

		if (isError($sync_result))
			return $sync_result;

		$sync_data = getData($sync_result);

		if ($mail && !empty($sync_data['mail_infos']))
		{
			$this->_ci->load->library('KalenderNotificationLib');
			$this->_ci->kalendernotificationlib->sendMails($sync_data['mail_infos']);
		}

		return success(['synced' => $sync_data['synced']]);
	}

	public function sync()
	{
		$this->_ci->KalenderModel->addSelect('tbl_kalender.*, tbl_kalender_ort.ort_kurzbz, tbl_kalender_ort.location');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_kalender_ort', 'tbl_kalender.kalender_id = tbl_kalender_ort.kalender_id', 'LEFT');
		$this->_ci->KalenderModel->db->where_in('status_kurzbz', array('sync_live', 'sync_preview', 'todelete'));
		$this->_ci->KalenderModel->addOrder('tbl_kalender.kalender_id', 'DESC');
		$to_update = $this->_ci->KalenderModel->load();

		if (isError($to_update))
			return $to_update;

		if (!hasData($to_update))
			return success(['synced' => 0]);

		$mail_infos = [];
		$synced = 0;

		foreach (getData($to_update) as $entry)
		{
			if ($entry->status_kurzbz === 'todelete')
			{
				$this->_ci->KalenderModel->update(array('kalender_id' => $entry->kalender_id), array('status_kurzbz' => 'deleted'));
				$mail_infos[] = array('entry' => $entry, 'new_status' => 'deleted', 'notify' => array('lektor', 'student'));
				$synced++;
			}

			if ($entry->status_kurzbz === 'sync_preview')
			{
				$this->_ci->KalenderModel->update(array('kalender_id' => $entry->kalender_id), array('status_kurzbz' => 'preview'));
				$this->_archiveVorgaenger($entry->vorgaenger_kalender_id, true);
				$mail_infos[] = array('entry' => $entry, 'new_status' => 'preview', 'notify' => array('lektor'));
				$synced++;
			}

			if ($entry->status_kurzbz === 'sync_live')
			{
				$this->_ci->KalenderModel->update(array('kalender_id' => $entry->kalender_id), array('status_kurzbz' => 'live'));
				$this->_archiveVorgaenger($entry->vorgaenger_kalender_id, false);
				$mail_infos[] = array('entry' => $entry, 'new_status' => 'live', 'notify' => array('lektor', 'student'));
				$synced++;
			}
		}

		$this->_ci->load->library('KalenderNotificationLib');
		$this->_ci->kalendernotificationlib->sendMails($mail_infos);

		return success(['synced' => $synced]);
	}

	public function getKalenderIds($oe_kurzbz, $studiensemester_kurzbz, $status_kurzbz_list, $ausbildungssemester = null, $studienplan_id = null, $datum_bis = null)
	{
		if (empty($status_kurzbz_list))
			return success([]);

		$studiengang_result = $this->_getStudiengang($oe_kurzbz);

		if (isError($studiengang_result))
			return $studiengang_result;

		$studiengang_kz = getData($studiengang_result);

		if (!hasData($studiengang_result))
		{
			$organisationseinheiten = $this->_ci->OrganisationseinheitModel->getChilds($oe_kurzbz);
			$oe_kurzbz = hasData($organisationseinheiten) ? array_column(getData($organisationseinheiten), 'oe_kurzbz') : [];
		}

		$this->_ci->KalenderModel->addDistinct('tbl_kalender.kalender_id');
		$this->_ci->KalenderModel->addSelect('tbl_kalender.kalender_id');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_kalender_lehreinheit', 'tbl_kalender.kalender_id = tbl_kalender_lehreinheit.kalender_id');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_lehreinheit', 'tbl_kalender_lehreinheit.lehreinheit_id = tbl_lehreinheit.lehreinheit_id');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_lehrveranstaltung', 'tbl_lehreinheit.lehrveranstaltung_id = tbl_lehrveranstaltung.lehrveranstaltung_id');

		if (!is_null($studiengang_kz))
		{
			$this->_ci->KalenderModel->db->where('tbl_lehrveranstaltung.studiengang_kz', $studiengang_kz);
		}
		else
		{
			$this->_ci->KalenderModel->db->where_in('tbl_lehrveranstaltung.oe_kurzbz', $oe_kurzbz);
		}

		$this->_ci->KalenderModel->db->where('tbl_lehreinheit.studiensemester_kurzbz', $studiensemester_kurzbz);
		$this->_ci->KalenderModel->db->where_in('tbl_kalender.status_kurzbz', $status_kurzbz_list);

		$this->_ci->KalenderModel->db->where('NOT EXISTS (
				SELECT 1 FROM lehre.tbl_kalender nachfolger
				WHERE nachfolger.vorgaenger_kalender_id = tbl_kalender.kalender_id)', null, false);

		if (!is_null($studienplan_id))
		{
			$this->_ci->KalenderModel->addJoin('lehre.tbl_studienplan_lehrveranstaltung', 'tbl_lehrveranstaltung.lehrveranstaltung_id = tbl_studienplan_lehrveranstaltung.lehrveranstaltung_id');
			$this->_ci->KalenderModel->db->where('tbl_studienplan_lehrveranstaltung.studienplan_id', $studienplan_id);
		}

		if (!is_null($ausbildungssemester))
			$this->_ci->KalenderModel->db->where('tbl_lehrveranstaltung.semester', $ausbildungssemester);

		if (!is_null($datum_bis))
		{
			$end_date = date('Y-m-d', strtotime($datum_bis . ' +1 day'));
			$this->_ci->KalenderModel->db->where('tbl_kalender.von <', $end_date);
		}

		$result = $this->_ci->KalenderModel->load();

		if (isError($result))
			return $result;

		if (!hasData($result))
			return success([]);

		return success(array_column(getData($result), 'kalender_id'));
	}


	private function _archiveVorgaenger($vorgaenger_kalender_id, $stop_at_live = false)
	{
		if (!$vorgaenger_kalender_id) return;

		$current_id = $vorgaenger_kalender_id;

		while ($current_id)
		{
			$vorgaenger = $this->_ci->KalenderModel->load(array('kalender_id' => $current_id));
			if (!hasData($vorgaenger)) return;

			$vorgaenger = getData($vorgaenger)[0];

			if ($stop_at_live && $vorgaenger->status_kurzbz === 'live')
				return;

			if (in_array($vorgaenger->status_kurzbz, array('preview', 'live')))
			{
				$this->_ci->KalenderModel->update(
					array('kalender_id' => $current_id),
					array('status_kurzbz' => 'archived')
				);
			}

			$current_id = $vorgaenger->vorgaenger_kalender_id;
		}
	}

	private function _syncKalenderIds($kalender_ids, $ziel_status)
	{
		if (empty($kalender_ids))
			return success(['synced' => 0, 'mail_infos' => []]);

		$this->_ci->KalenderModel->addSelect('tbl_kalender.*, tbl_kalender_ort.ort_kurzbz, tbl_kalender_ort.location');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_kalender_ort', 'tbl_kalender.kalender_id = tbl_kalender_ort.kalender_id', 'LEFT');
		$this->_ci->KalenderModel->db->where_in('tbl_kalender.kalender_id', $kalender_ids);
		$this->_ci->KalenderModel->addOrder('tbl_kalender.kalender_id', 'DESC');
		$to_update = $this->_ci->KalenderModel->load();

		if (isError($to_update))
			return $to_update;

		if (!hasData($to_update))
			return success(['synced' => 0, 'mail_infos' => []]);

		$mail_infos = [];
		$synced = 0;

		foreach (getData($to_update) as $entry)
		{
			if ($entry->status_kurzbz === 'todelete')
			{
				$this->_ci->KalenderModel->update(array('kalender_id' => $entry->kalender_id), array('status_kurzbz' => 'deleted'));
				$mail_infos[] = array('entry' => $entry, 'new_status' => 'deleted', 'notify' => array('lektor', 'student'));
				$synced++;
				continue;
			}

			if ($ziel_status === 'preview' && in_array($entry->status_kurzbz, array('planning', 'sync_preview')))
			{
				$this->_ci->KalenderModel->update(array('kalender_id' => $entry->kalender_id), array('status_kurzbz' => 'preview'));
				$this->_archiveVorgaenger($entry->vorgaenger_kalender_id, true);
				$mail_infos[] = array('entry' => $entry, 'new_status' => 'preview', 'notify' => array('lektor'));
				$synced++;
				continue;
			}

			if ($ziel_status === 'live' && in_array($entry->status_kurzbz, array('planning', 'sync_preview', 'sync_live')))
			{
				$this->_ci->KalenderModel->update(array('kalender_id' => $entry->kalender_id), array('status_kurzbz' => 'live'));
				$this->_archiveVorgaenger($entry->vorgaenger_kalender_id, false);
				$mail_infos[] = array('entry' => $entry, 'new_status' => 'live', 'notify' => array('lektor', 'student'));
				$synced++;
				continue;
			}

			if ($ziel_status === 'live' && $entry->status_kurzbz === 'preview')
			{
				$current = getData($this->_ci->KalenderModel->load(array('kalender_id' => $entry->kalender_id)))[0];
				if ($current->status_kurzbz === 'archived')
					continue;

				$this->_ci->KalenderModel->update(array('kalender_id' => $entry->kalender_id), array('status_kurzbz' => 'live'));
				$this->_archiveVorgaenger($entry->vorgaenger_kalender_id, false);
				$mail_infos[] = array('entry' => $entry, 'new_status' => 'live', 'notify' => array('lektor', 'student'));
				$synced++;
			}
		}

		return success(['synced' => $synced, 'mail_infos' => $mail_infos]);
	}


	private function _getStudiengang($oe_kurzbz)
	{
		$this->_ci->StudiengangModel->addJoin('tbl_organisationseinheit', 'oe_kurzbz');
		$result = $this->_ci->StudiengangModel->loadWhere(array('oe_kurzbz' => $oe_kurzbz));

		if (isError($result))
			return $result;

		if (!hasData($result))
			return success(null);

		return success(getData($result)[0]->studiengang_kz);
	}
}