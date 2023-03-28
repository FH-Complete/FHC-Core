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

	public function getVertragsbestandteile($dienstverhaeltnis_id=1, $stichtag=null)
	{
		$stichtagclause = '';
		if( !is_null($stichtag) )
		{
			$date = strftime('%Y-%m-%d', strtotime($stichtag));
			$stichtagclause = 'AND ' . $this->escape($date)
				. ' BETWEEN COALESCE(v.von, \'1970-01-01\'::date)'
				. ' AND COALESCE(v.bis, \'2170-01-01\'::date)';
		}

		$sql = <<<EOSQL
			SELECT
				v.*, 
				
				s.wochenstunden, s.karenz, 
				
				f.benutzerfunktion_id, f.anmerkung, f. kuendigungsrelevant,
				
				g.von as gehalt_von, g.bis as gehalt_bis, g.dienstverhaeltnis_id as gehalt_dienstverhaeltnis_id, g.grundbetrag,
				g.betrag_valorisiert,g.valorisieren,gehaltstyp_kurzbz,valorisierungssperre
			FROM
				hr.tbl_vertragsbestandteil v
			LEFT JOIN
				hr.tbl_vertragsbestandteil_stunden s USING(vertragsbestandteil_id)
			LEFT JOIN
				hr.tbl_vertragsbestandteil_funktion f USING(vertragsbestandteil_id)
			LEFT JOIN
				hr.tbl_gehaltsbestandteil g USING(vertragsbestandteil_id)
			WHERE
				v.dienstverhaeltnis_id = {$this->escape($dienstverhaeltnis_id)}
				{$stichtagclause}
			;
EOSQL;

		// echo $sql . "\n\n";
		$query = $this->db->query($sql);   // TODO add decryption

		$vertragsbestandteile = array();
		foreach( $query->result() as $row ) {
			try
			{
				$vertragsbestandteile[] = VertragsbestandteilFactory::getVertragsbestandteil($row);
			}
			catch (Exception $ex)
			{
				echo $ex->getMessage() . "\n";
			}
		}

		return $vertragsbestandteile;
	}


	public function getVertragsbestandteil($id)
	{	

		$sql = <<<EOSQL
			SELECT
				v.*, 
				
				s.wochenstunden, s.karenz, 
				
				f.benutzerfunktion_id, f.anmerkung, f. kuendigungsrelevant,
				
				g.von as gehalt_von, g.bis as gehalt_bis, g.dienstverhaeltnis_id as gehalt_dienstverhaeltnis_id, g.grundbetrag,
				g.betrag_valorisiert,g.valorisieren,gehaltstyp_kurzbz,valorisierungssperre
			FROM
				hr.tbl_vertragsbestandteil v
			LEFT JOIN
				hr.tbl_vertragsbestandteil_stunden s USING(vertragsbestandteil_id)
			LEFT JOIN
				hr.tbl_vertragsbestandteil_funktion f USING(vertragsbestandteil_id)
			LEFT JOIN
				hr.tbl_gehaltsbestandteil g USING(vertragsbestandteil_id)
			WHERE
				v.vertragsbestandteil_id = {$this->escape($id)}
			;
EOSQL;

		// echo $sql . "\n\n";
		$query = $this->db->query($sql);

		$vertragsbestandteil = array();
		try
		{
			$vertragsbestandteile = VertragsbestandteilFactory::getVertragsbestandteil($row);  // TODO add decryption
		}
		catch (Exception $ex)
		{
			echo $ex->getMessage() . "\n";
		}

		return $vertragsbestandteil;
		
	}
}
