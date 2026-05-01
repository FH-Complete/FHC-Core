<?php
class Betriebsmittel_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'wawi.tbl_betriebsmittel';
		$this->pk = 'betriebsmittel_id';
	}

	/**
	 * load Liste Inventarnummern
	 */
	public function loadInventarliste($filter)
	{
		$filter = urldecode(strtoLower($filter));

		$qry = "
			SELECT 
			    bm.inventarnummer, bm.betriebsmitteltyp, bm.betriebsmittel_id, CONCAT(bm.inventarnummer, ' ', bm.beschreibung) as dropdowntext
			FROM 
			    wawi.tbl_betriebsmittel bm
			WHERE 
			    upper(bm.inventarnummer) LIKE '%" .$this->db->escape_like_str($filter)."%'
			OR    
			    lower(bm.inventarnummer) LIKE '%" .$this->db->escape_like_str($filter)."%'";

		return $this->execQuery($qry);
	}
}
