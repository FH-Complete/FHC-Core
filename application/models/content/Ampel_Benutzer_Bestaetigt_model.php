<?php
class Ampel_Benutzer_Bestaetigt_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_ampel_benutzer_bestaetigt';
		$this->pk = 'ampel_benutzer_bestaetigt_id';
	}

}
