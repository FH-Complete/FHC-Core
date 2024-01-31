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

	public $oe_bezeichnung;
	public $stundensatztyp_bezeichnung;

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
	
	public function getAllStundensaetze($uid)
	{
		$qry = "SELECT
					tbl_stundensatz.*,
					tbl_organisationseinheit.bezeichnung AS oe_bezeichnung,
					hr.tbl_stundensatztyp.bezeichnung AS stundensatztyp_bezeichnung,
					tbl_organisationseinheit.oe_kurzbz
				FROM
					hr.tbl_stundensatz
					LEFT JOIN
						public.tbl_organisationseinheit ON tbl_stundensatz.oe_kurzbz = tbl_organisationseinheit.oe_kurzbz
					JOIN hr.tbl_stundensatztyp USING(stundensatztyp)
				WHERE
					uid = ". $this->db_add_param($uid);

		if ($result = $this->db_query($qry))
		{
			while ($row = $this->db_fetch_object($result))
			{
				$obj = new stundensatz();
				$obj->stundensatz_id = $row->stundensatz_id;
				$obj->uid = $row->uid;
				$obj->stundensatztyp = $row->stundensatztyp;
				$obj->stundensatz = $row->stundensatz;
				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->gueltig_von = $row->gueltig_von;
				$obj->gueltig_bis = $row->gueltig_bis;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->oe_bezeichnung = $row->oe_bezeichnung;
				$obj->stundensatztyp_bezeichnung = $row->stundensatztyp_bezeichnung;

				$this->result[] = $obj;
			}
			
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
	
	public function load($stundensatz_id)
	{
		$qry = "SELECT
					tbl_stundensatz.*,
					tbl_organisationseinheit.bezeichnung AS oe_bezeichnung,
					hr.tbl_stundensatztyp.bezeichnung AS stundensatztyp_bezeichnung,
					tbl_organisationseinheit.oe_kurzbz
				FROM
					hr.tbl_stundensatz
					LEFT JOIN public.tbl_organisationseinheit ON tbl_stundensatz.oe_kurzbz = tbl_organisationseinheit.oe_kurzbz
					JOIN hr.tbl_stundensatztyp USING(stundensatztyp)
				WHERE
					stundensatz_id = ".$this->db_add_param($stundensatz_id).";";
		
		if($this->db_query($qry))
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
				$this->oe_bezeichnung = $row->oe_bezeichnung;
				$this->stundensatztyp_bezeichnung = $row->stundensatztyp_bezeichnung;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}
	}
	
	public function save($new = null)
	{
		if (!$this->validate())
			return false;
		
		if(is_null($new))
			$new = $this->new;
		
		if ($new)
		{
			
			$qry = "BEGIN;INSERT INTO hr.tbl_stundensatz
				(
					uid,
					stundensatz,
					gueltig_von,
					gueltig_bis,
					oe_kurzbz,
					stundensatztyp,
					insertamum,
					insertvon
				)
				VALUES
				(" .
					$this->db_add_param($this->uid) . "," .
					$this->db_add_param($this->stundensatz, FHC_INTEGER) . "," .
					$this->db_add_param($this->gueltig_von) . "," .
					$this->db_add_param($this->gueltig_bis) . "," .
					$this->db_add_param($this->oe_kurzbz) . "," .
					$this->db_add_param($this->stundensatztyp) . "," .
					$this->db_add_param($this->insertamum) . "," .
					$this->db_add_param($this->insertvon) .
				");";
		}
		else
		{
			$qry = "UPDATE hr.tbl_stundensatz SET " .
				"stundensatz = " . $this->db_add_param($this->stundensatz, FHC_INTEGER) .",".
				"oe_kurzbz = " . $this->db_add_param($this->oe_kurzbz) .",".
				"stundensatztyp = " . $this->db_add_param($this->stundensatztyp) .",".
				"gueltig_von = " . $this->db_add_param($this->gueltig_von) .",".
				"gueltig_bis = " . $this->db_add_param($this->gueltig_bis) .",".
				"updatevon = " . $this->db_add_param($this->updatevon) .",".
				"updateamum = " . $this->db_add_param($this->updateamum).
				" WHERE stundensatz_id = " . $this->db_add_param($this->stundensatz_id, FHC_INTEGER).';';
		}
		
		if($this->db_query($qry))
		{
			if ($new)
			{
				$qry = "SELECT currval('hr.tbl_stundensatz_stundensatz_id_seq') as id;";
				if ($this->db_query($qry))
				{
					if ($row = $this->db_fetch_object())
					{
						$this->stundensatz_id = $row->id;
						$this->db_query('COMMIT');
						return true;
					} else
					{
						$this->db_query('ROLLBACK');
						$this->errormsg = "Fehler beim Auslesen der Sequence";
						return false;
					}
				} else
				{
					$this->db_query('ROLLBACK');
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					return false;
				}
			}
			else
			{
				return true;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Zuteilen des Eintrages';
			return false;
		}
	}
	
	public function delete($stundensatz_id)
	{
		$qry = "DELETE FROM hr.tbl_stundensatz WHERE stundensatz_id = ".$this->db_add_param($stundensatz_id, FHC_INTEGER);
		
		if($this->db_query($qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim Löschen des Stundensatzes';
			return false;
		}
	}
	
	public function validate()
	{
		if (!is_numeric($this->stundensatz))
		{
			$this->errormsg = "Stundensatz ungueltig";
			return false;
		}
		
		if (is_null($this->oe_kurzbz))
		{
			$this->errormsg = "Unternehmen ungueltig";
			return false;
		}
		
		if ($this->insertamum === '')
		{
			$this->errormsg = "Datum ungueltig";
			return false;
		}
		return true;
	}
}
?>
