<?php
class Dashboard_Preset_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'dashboard.tbl_dashboard_preset';
		$this->pk = 'preset_id';
	}

	/**
	 * Get Presets of given uid.
	 * @param integer dashboard_id
	 * @param string $uid
	 * @return array
	 */
	public function getPresets($dashboard_id, $uid)
	{
		// TODO: get Funktionen for uid and load all preset for all funktionen for uid
		//return $this->loadWhere(array('dashboard_id' => $dashboard_id, 'funktion_kurzbz'=> null));
		$sql = <<<EOSQL
			SELECT 
				* 
			FROM 
				dashboard.tbl_dashboard_preset 
			WHERE 
				dashboard_id = ? 
			AND (
					funktion_kurzbz IN (
						SELECT 
							DISTINCT funktion_kurzbz 
						FROM 
							public.tbl_benutzerfunktion 
						WHERE 
							uid = ? 
						AND 
							NOW()::date 
						BETWEEN 
							COALESCE(datum_von, '1970-01-01') 
						AND 
							COALESCE(datum_bis, '2170-12-31')
					) 
					OR 
						funktion_kurzbz IS NULL
				) 
			ORDER BY 
				funktion_kurzbz DESC
EOSQL;
		
		return $this->execQuery($sql, array($dashboard_id, $uid));
	}
	
	/**
	 * Get Preset by Dashboard and Funktion
	 * @param integer dashboard_id
	 * @param string funktion_kurzbz
	 * @return array
	 */
	public function getPresetByDashboardAndFunktion($dashboard_id, $funktion_kurzbz)
	{
		return $this->loadWhere(array('dashboard_id' => $dashboard_id, 'funktion_kurzbz' => $funktion_kurzbz));
	}
}
