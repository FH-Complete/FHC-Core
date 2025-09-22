<?php

class GehaltsTyp_model extends DB_Model
{

	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'hr.tbl_gehaltstyp';
		$this->pk = 'gehaltstyp_kurzbz';
	}

	/**
	 * Gets Gehaltstyp for a Gehaltsbestandteil.
	 * @param int gehaltsbestandteil_id
	 * @return object the typ
	 */
	public function getGehaltstypByGehaltsbestandteil($gehaltsbestandteil_id)
	{
		$gehaltsTyp = null;
		$this->addJoin('hr.tbl_gehaltsbestandteil', 'gehaltstyp_kurzbz');
		$result = $this->loadWhere(['gehaltsbestandteil_id' => $gehaltsbestandteil_id]);

		if (hasData($result)) $gehaltsTyp = getData($result)[0];

		return $gehaltsTyp;
	}
}
