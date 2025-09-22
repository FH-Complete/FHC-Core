<?php

class VertragsbestandteilFreitext_model extends DB_Model
{

	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'hr.tbl_vertragsbestandteil_freitext';
		$this->pk = 'vertragsbestandteil_id';
	}
	
	public function countOverlappingVBFreitextsOfSameType(vertragsbestandteil\VertragsbestandteilFreitext $vbft)
	{
		$notselfclause = (intval($vbft->getVertragsbestandteil_id()) > 0) 
			? 'AND v.vertragsbestandteil_id <> ' . $this->escape($vbft->getVertragsbestandteil_id()) 
			: '';
		$sql = <<<EOSQL
			SELECT
				count(*) AS overlappingvbs
			FROM
				hr.tbl_vertragsbestandteil v
			JOIN
				hr.tbl_vertragsbestandteil_freitext vbft USING(vertragsbestandteil_id)
			WHERE
				v.dienstverhaeltnis_id = ? 
			AND 
				v.vertragsbestandteiltyp_kurzbz = ? 
			AND
				vbft.freitexttyp_kurzbz = ?
			AND 
				COALESCE(?::date, '2170-12-31'::date) >= COALESCE(v.von, '1970-01-01'::date) 
			AND 
				?::date <= COALESCE(v.bis, '2170-12-31')
			{$notselfclause}
EOSQL;
		$ret = $this->execReadOnlyQuery($sql, array(
			$vbft->getDienstverhaeltnis_id(), 
			$vbft->getVertragsbestandteiltyp_kurzbz(), 
			$vbft->getFreitexttypKurzbz(),
			$vbft->getBis(), 
			$vbft->getVon()
		));
		
		if( null === ($vbcount = getData($ret)) ) {
			throw new Exception('failed to fetch overlappingvbs count');
		}
		
		return $vbcount[0]->overlappingvbs;
	}
}
