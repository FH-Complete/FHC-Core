<?php

require_once(dirname(__FILE__).'/basis_db.class.php');

class stundensatz extends basis_db
{
	public $new;
	public $result = array();
	
	//Tabellenspalten
	public $stundensatz_id;	// serial
	public $uid;					// varchar(32)
	public $stundensatztyp;		// varchar(32)
	public $stundensatz;			// numeric
	public $oe_kurzbz;			// varchar(32)
	public $gueltig_von;			// date
	public $gueltig_bis;			// date
	public $insertamum;				// timestamp
	public $insertvon;				// varchar(16)
	public $updateamum;				// timestamp
	public $updatevon;				// varchar(16)
	

	
	public function __construct($stundensatz_id = null)
	{
		parent::__construct();

		if (!is_null($stundensatz_id))
			$this->load($stundensatz_id);
	}
	
	public function getStundensatzDatum($uid, $beginn, $ende = null, $typ = null)
	{
		
		$qry = "SELECT
					*
				FROM
					hr.tbl_stundensatz
				WHERE
					uid = ". $this->db_add_param($uid) ."
					AND (gueltig_bis >= ". $this->db_add_param($beginn) ." OR gueltig_bis is null)";

		if (!is_null($ende))
		{
			$qry .=  " AND (gueltig_von <= ". $this->db_add_param($ende) .")";
		}
		
		if (!is_null($typ))
		{
			$qry .=  " AND stundensatztyp = ". $this->db_add_param($typ);
		}
		
		$qry .= " ORDER BY gueltig_bis DESC NULLS FIRST, gueltig_von DESC NULLS LAST LIMIT 1;";
		
		if ($this->db_query($qry))
		{
			if ($row = $this->db_fetch_object())
			{
				$this->stundensatz_id = $row->stundensatz_id;
				$this->uid = $row->uid;
				$this->stundensatztyp = $row->stundensatztyp;
				$this->stundensatz = $row->stundensatz;
				$this->oe_kurzbz = $row->oe_kurzbz;
				$this->gueltig_von = $row->gueltig_von;
				$this->gueltig_bis = $row->gueltig_bis;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
			}
			
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
}
?>
