<?php

require_once(dirname(__FILE__).'/basis_db.class.php');

class stundensatzzyp extends basis_db
{
	public $result = array();
	
	public $stundensatztyp;
	public $bezeichnung;
	public $aktiv;

	public function __construct($stundensatztyp=null)
	{
		parent::__construct();
		
		if($stundensatztyp!=null)
			$this->load($stundensatztyp);
	}

	public function load($stundensatzzyp)
	{
		$qry = "SELECT *
				FROM hr.tbl_stundensatztyp
				WHERE stundensatztyp=". $this->db_add_param($stundensatzzyp);
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->stundensatztyp = $row->stundensatztyp;
				$this->bezeichnung = $row->bezeichnung;
				$this->aktiv = $this->db_parse_bool($row->aktiv);
			}
		}
		else
		{
			$this->errormsg ="Fehler bei der Abfrage aufgetreten";
			return false;
		}
	}
	
	public function getAll($onlyAktiv = true, $order = 'aktiv DESC, bezeichnung')
	{
		$qry = "SELECT *
				FROM hr.tbl_stundensatztyp";
		
		if ($onlyAktiv)
		{
			$qry .= " WHERE aktiv";
		}
		
		if($order !== '')
			$qry .= " ORDER BY ".$order;
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new stundensatzzyp();
				
				$obj->stundensatztyp = $row->stundensatztyp;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->aktiv = $this->db_parse_bool($row->aktiv);
				
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
}
?>
