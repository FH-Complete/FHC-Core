<?php

require_once(dirname(__FILE__) . '/basis_db.class.php');

class adressentyp extends basis_db {

	public $result = array();
	public $adressentyp;
	public $bezeichnung;

	public function __construct()
	{
		parent::__construct();
	}

	public function getAll()
	{
		$qry = "SELECT * FROM public.tbl_adressentyp ORDER BY sort";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new adressentyp();

				$obj->adressentyp = $row->adressentyp_kurzbz;
				$obj->bezeichnung = $row->bezeichnung;

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
