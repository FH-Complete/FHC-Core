<?php
class Konto_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'wawi.tbl_konto';
		$this->pk = 'konto_id';
	}

	/**
	 * Gets all Konten connected for a Kostenstelle
	 * @param $kostenstelle_id
	 * @return array
	 */
	public function getKontenForKostenstelle($kostenstelle_id)
	{
		$this->addJoin('wawi.tbl_konto_kostenstelle', 'konto_id');
		$konten = $this->loadWhere(array('kostenstelle_id' => $kostenstelle_id));

		if ($konten->error) return $konten;

		return $konten;
	}
}
