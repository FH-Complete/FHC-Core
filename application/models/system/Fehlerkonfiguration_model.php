<?php
class Fehlerkonfiguration_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'system.tbl_fehler_konfiguration';
		$this->pk = array('konfigurationstyp_kurzbz', 'fehlercode');
		$this->hasSequence = false;
	}

	/**
	 * Retrieve all set configuration parameters, optionally filtered by app.
	 * @param string $apps
	 * @return object success or error
	 */
	public function getKonfiguration($apps = null)
	{
		if (is_string($apps)) $apps = [$apps];
		$fehlerkonfiguration = array();

		$this->addDistinct();
		$this->addSelect('fehler.fehlercode, konftyp.konfigurationstyp_kurzbz, tbl_fehler_konfiguration.konfiguration, fehler.fehler_kurzbz');
		$this->addJoin('system.tbl_fehler_konfigurationstyp konftyp', 'konfigurationstyp_kurzbz');
		$this->addJoin('system.tbl_fehler fehler', 'fehlercode');
		$this->addJoin('system.tbl_fehler_app fe_app', 'fehlercode');
		if (isset($apps) && !isEmptyArray($apps)) $this->db->where_in('fe_app.app', $apps);
		$fehlerkonfigurationRes = $this->load();

		if (isError($fehlerkonfigurationRes)) return $fehlerkonfigurationRes;

		if (hasData($fehlerkonfigurationRes))
		{
			$fehlerkonfigurationData = getData($fehlerkonfigurationRes);
			foreach ($fehlerkonfigurationData as $fk)
			{
				$konf = json_decode($fk->konfiguration);
				if (is_array($konf))
				{
					$fk->konfiguration = $konf;
					$fehlerkonfiguration[] = $fk;
				}
			}
		}

		return success($fehlerkonfiguration);
	}
}
