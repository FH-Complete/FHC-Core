<?php

class Dokumentstudiengang_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_dokumentstudiengang';
		$this->pk = array('studiengang_kz', 'dokument_kurzbz');
	}
	
	public function getDokumentstudiengangByStudiengang_kz($studiengang_kz, $onlinebewerbung = null, $pflicht = null, $nachreichbar = null)
	{
		// Checks if the operation is permitted by the API caller
		if (($isEntitled = $this->isEntitled('public.tbl_dokument', 's', FHC_NORIGHT, FHC_MODEL_ERROR)) !== true)
			return $isEntitled;
		
		$this->addJoin('public.tbl_dokument', 'dokument_kurzbz');
			
		$parameterArray = array('studiengang_kz' => $studiengang_kz);
	
		if( isset($onlinebewerbung))
		{
			$parameterArray['onlinebewerbung'] = $onlinebewerbung;
		}

		if( isset($pflicht))
		{
			$parameterArray['pflicht'] = $pflicht;
		}
		
		if( isset($nachreichbar))
		{
			$parameterArray['nachreichbar'] = $nachreichbar;
		}
		
		return $this->loadWhere($parameterArray);
	}
}