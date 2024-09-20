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
	 * @param string $app
	 * @return object success or error
	 */
	public function getKonfiguration($app = null)
	{
		$fehlerkonfiguration = array();

		$this->addSelect('fehlercode, konfigurationstyp_kurzbz, konfiguration, fehler_kurzbz');
		$this->addJoin('system.tbl_fehler_konfigurationstyp konftyp', 'konfigurationstyp_kurzbz');
		$this->addJoin('system.tbl_fehler fehler', 'fehlercode');
		$fehlerkonfigurationRes = isset($app) ? $this->loadWhere(array('fehler.app' => $app)) : $this->load();

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
