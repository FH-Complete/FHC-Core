<?php
use vertragsbestandteil\VertragsbestandteilFactory;
/**
 * Description of Vertragsbestandteil_model
 *
 * @author bambi
 */
class Vertragsbestandteil_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'hr.tbl_vertragsbestandteil';
		$this->pk = 'vertragsbestandteil_id';
	}

	protected function getVertragsbestandteilSQL()
	{
		$sql = <<<EOSQL
		SELECT
				v.*,
				bf.funktion_kurzbz, bf.uid AS mitarbeiter_uid, 
				funktion.beschreibung AS funktion_bezeichnung, 			    
				oe.oe_kurzbz, oe.bezeichnung AS oe_bezeichnung, sap.oe_kurzbz_sap,
				oet.organisationseinheittyp_kurzbz AS oe_typ_kurzbz, oet.bezeichnung AS oe_typ_bezeichnung,
				ft.freitexttyp_kurzbz, ft.titel, ft.anmerkung,
				f.benutzerfunktion_id,
				k.karenztyp_kurzbz, k.geplanter_geburtstermin, k.tatsaechlicher_geburtstermin,
				kf.arbeitgeber_frist, kf.arbeitnehmer_frist,
				s.wochenstunden, s.teilzeittyp_kurzbz,
				u.tage,
				z.zeitaufzeichnung, z.azgrelevant, z.homeoffice
			FROM
				hr.tbl_vertragsbestandteil v
			LEFT JOIN
				hr.tbl_vertragsbestandteil_freitext ft USING(vertragsbestandteil_id)
			LEFT JOIN
				hr.tbl_vertragsbestandteil_funktion f USING(vertragsbestandteil_id)
			LEFT JOIN 
				public.tbl_benutzerfunktion bf USING(benutzerfunktion_id)
			LEFT JOIN
				public.tbl_funktion funktion USING(funktion_kurzbz)
			LEFT JOIN
				public.tbl_organisationseinheit oe USING(oe_kurzbz)
			LEFT JOIN
				public.tbl_organisationseinheittyp oet USING(organisationseinheittyp_kurzbz)
			LEFT JOIN
				sync.tbl_sap_organisationsstruktur sap USING(oe_kurzbz)
			LEFT JOIN
				hr.tbl_vertragsbestandteil_karenz k USING(vertragsbestandteil_id)
			LEFT JOIN
				hr.tbl_vertragsbestandteil_kuendigungsfrist kf USING(vertragsbestandteil_id)
			LEFT JOIN
				hr.tbl_vertragsbestandteil_stunden s USING(vertragsbestandteil_id)
			LEFT JOIN
				hr.tbl_vertragsbestandteil_urlaubsanspruch u USING(vertragsbestandteil_id)
			LEFT JOIN
				hr.tbl_vertragsbestandteil_zeitaufzeichnung z USING(vertragsbestandteil_id)
EOSQL;
		return $sql;
	}

	public function getVertragsbestandteile($dienstverhaeltnis_id, $stichtag=null, $includefuture=false)
	{
		$stichtagclause = '';
		if( !is_null($stichtag) )
		{
			$date = strftime('%Y-%m-%d', strtotime($stichtag));
			$stichtagclause = 'AND (' . $this->escape($date)
				. ' BETWEEN COALESCE(v.von, \'1970-01-01\'::date)'
				. ' AND COALESCE(v.bis, \'2170-01-01\'::date)';
			if( $includefuture ) 
			{
				$stichtagclause .= ' OR COALESCE(v.von, \'1970-01-01\'::date) > ' 
					. $this->escape($date);
			}
			$stichtagclause .= ')';
		}

		$sql = <<<EOSQL
			{$this->getVertragsbestandteilSQL()}
			WHERE
				v.dienstverhaeltnis_id = {$this->escape($dienstverhaeltnis_id)}
				{$stichtagclause}
			;
EOSQL;

		// echo $sql . "\n\n";
		$query = $this->execReadOnlyQuery($sql);   // TODO add decryption
		$data = getData($query);

		if ($data == null)
		{
			return array();
		}

		$vertragsbestandteile = array();
		foreach( $data as $row ) {
			try
			{
				$vertragsbestandteile[] = VertragsbestandteilFactory::getVertragsbestandteil($row, true);
			}
			catch (Exception $ex)
			{
				echo $ex->getMessage() . "\n";
			}
		}

		$dummy = json_encode($vertragsbestandteile);
		return $vertragsbestandteile;
	}


	public function getVertragsbestandteil($id)
	{	

		$sql = <<<EOSQL
			{$this->getVertragsbestandteilSQL()}
			WHERE
				v.vertragsbestandteil_id = {$this->escape($id)}
			;
EOSQL;

		$query = $this->execReadOnlyQuery($sql);		
		
		$vertragsbestandteil = null;
		
		if( hasData($query) ) 
		{
			$data = getData($query)[0];
			try
			{
				$vertragsbestandteil = VertragsbestandteilFactory::getVertragsbestandteil($data, true);  // TODO add decryption
			}
			catch (Exception $ex)
			{
				echo $ex->getMessage() . "\n";
			}
		}
		
		return $vertragsbestandteil;
		
	}
	
	public function countOverlappingVBsOfSameType(vertragsbestandteil\Vertragsbestandteil $vb)
	{
		$notselfclause = (intval($vb->getVertragsbestandteil_id()) > 0) 
			? 'AND v.vertragsbestandteil_id <> ' . $this->escape($vb->getVertragsbestandteil_id()) 
			: '';
		$sql = <<<EOSQL
			SELECT
				count(*) AS overlappingvbs
			FROM
				hr.tbl_vertragsbestandteil v
			WHERE
				v.dienstverhaeltnis_id = ? 
			AND 
				v.vertragsbestandteiltyp_kurzbz = ? 
			AND 
				COALESCE(?::date, '2170-12-31'::date) >= COALESCE(v.von, '1970-01-01'::date) 
			AND 
				?::date <= COALESCE(v.bis, '2170-12-31')
			{$notselfclause}
EOSQL;
		$ret = $this->execReadOnlyQuery($sql, array(
			$vb->getDienstverhaeltnis_id(), 
			$vb->getVertragsbestandteiltyp_kurzbz(), 
			$vb->getBis(), 
			$vb->getVon()
		));
		
		if( null === ($vbcount = getData($ret)) ) {
			throw new Exception('failed to fetch overlappingvbs count');
		}
		
		return $vbcount[0]->overlappingvbs;
	}
}
