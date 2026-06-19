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

	public function getSchedulableEntriesByDatetimeInterval($from, $to)
	{
		$qry = "SELECT
					*
				FROM
					wawi.tbl_betriebsmittel
				WHERE
					verplanen=true
					AND 
				NOT EXISTS(
					SELECT 1 FROM lehre.tbl_betriebsmittel_kalender
					JOIN lehre.tbl_kalender ON tbl_kalender.eindeutige_gruppen_id = tbl_betriebsmittel_kalender.eindeutige_kalender_gruppen_id
					WHERE
						betriebsmittel_id=tbl_betriebsmittel.betriebsmittel_id
					AND tbl_kalender.von <= ?
					AND tbl_kalender.bis >= ?
				)";


		return $this->execQuery($qry, array($from, $to));
	}
}
