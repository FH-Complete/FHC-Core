<?php
require_once __DIR__ . '/IEncryption.php';

use vertragsbestandteil\Gehaltsbestandteil;

class Gehaltsbestandteil_model extends DB_Model implements IEncryption
{

    public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'hr.tbl_gehaltsbestandteil';
		$this->pk = 'gehaltsbestandteil_id';
	}

    public function getEncryptedColumns(): array
    {
		return array(
			'grundbetrag' => array(
				DB_Model::CRYPT_CAST => 'numeric',
				DB_Model::CRYPT_PASSWORD_NAME => 'ENCRYPTIONKEY'
			),
			'betrag_valorisiert' => array(
				DB_Model::CRYPT_CAST => 'numeric',
				DB_Model::CRYPT_PASSWORD_NAME => 'ENCRYPTIONKEY'
			)
		);
    }

    public function getCurrentGBTByDV($dienstverhaeltnis_id)
    {
		$qry = "
        SELECT
            gehaltsbestandteil_id,
            von,
            bis,
            anmerkung,
            dienstverhaeltnis_id,
            gehaltstyp_kurzbz,
            valorisierungssperre,
            valorisieren,
            grundbetrag,
            betrag_valorisiert,
            gt.bezeichnung as gehaltstyp_bezeichnung
        FROM hr.tbl_gehaltsbestandteil gbt JOIN hr.tbl_gehaltstyp gt using(gehaltstyp_kurzbz)
        WHERE gbt.dienstverhaeltnis_id=? AND
            (gbt.von<=CURRENT_DATE::text::date and (gbt.bis is null OR gbt.bis>=CURRENT_DATE::text::date))
        ORDER BY gt.sort
        ";

        return $this->execQuery($qry, array($dienstverhaeltnis_id), $this->getEncryptedColumns());
    }
	
	public function getGehaltsbestandteile($dienstverhaeltnis_id=1, $stichtag=null)
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
				g.*
			FROM
				hr.tbl_gehaltsbestandteil g
			WHERE
				g.dienstverhaeltnis_id = ? 
				{$stichtagclause}
			;
EOSQL;

		$query = $this->execReadOnlyQuery(
			$query,
			array($dienstverhaeltnis_id),
			$this->getEncryptedColumns()
		);

		$gehaltsbestandteile = array(); 
		if( null !== ($rows = getData($query)) )
		{
			foreach( $rows as $row ) {
				$tmpgb = new Gehaltsbestandteil();
				$tmpgb->hydrateByStdClass($row);
				$gehaltsbestandteile[] = $tmpgb;
			}
		}

		return $gehaltsbestandteile;
	}


	public function getGehaltsbestandteil($id)
	{	
		$query = $this->load($id, $this->getEncryptedColumns());
		$gehaltsbestandteil = null;
		
		if( null !== ($row = getData($query)) )
		{
			$gehaltsbestandteil = new Gehaltsbestandteil();
			$gehaltsbestandteil->hydrateByStdClass($row[0]);
		}

		return $gehaltsbestandteil;
	}
}
