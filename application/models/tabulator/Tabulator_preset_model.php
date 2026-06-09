<?php

class Tabulator_preset_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_tabulator_presets';
		$this->pk = ['preset_id'];
		$this->hasSequence = true;


		$this->load->model('person/Benutzer_model', 'BenutzerModel');
	}

	public function getTabulatorPreset($presetId)
	{
		$query = "
			SELECT *
			FROM public.tbl_tabulator_presets
			WHERE preset_id = ?
			LIMIT 1
		";
		return $this->execQuery($query, array($presetId));
	}

	public function getTabulatorPresetsByUserAndTable($uid, $tableName)
	{
		$query = '
			SELECT *
			FROM public.tbl_tabulator_presets
			WHERE benutzer_uid = ?
			AND table_name = ?
			ORDER BY preset_id ASC
		';
		return $this->execQuery($query, array($uid, $tableName));
	}

	public function createTabulatorPreset($uid, $tableName, $presetName, $preset)
	{
		$this->db->trans_start(false);

		$result = $this->insert([
			"benutzer_uid" => $uid,
			"table_name" => $tableName,
			"preset_name" => $presetName,
			"preset_json" => json_encode($preset),
		]);

		$this->db->trans_complete();
		
		if ($this->db->trans_status() === false)
		{
			$this->db->trans_rollback();
			return error('Something went wrong with tabulator preset creation.');
		}
		
		return success($result->retval);
	}

	public function deleteTabulatorPreset($presetId)
	{
		$presetDeletionReseult = $this->delete(["preset_id" => $presetId]);
		
		if (isError($presetDeletionReseult))
        {
            return error('Something went wrong during tabulator preset deletion.');
        }

        return success($presetDeletionReseult->retval);
	}

}
