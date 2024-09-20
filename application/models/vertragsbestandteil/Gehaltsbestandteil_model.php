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
				DB_Model::CRYPT_PASSWORD_NAME => 'ENCRYPTIONKEYGEHALT'
			),
			'betrag_valorisiert' => array(
				DB_Model::CRYPT_CAST => 'numeric',
				DB_Model::CRYPT_PASSWORD_NAME => 'ENCRYPTIONKEYGEHALT'
			)
		);
    }

    public function getCurrentGBTByDV($dienstverhaeltnis_id, $dateAsUnixTS)
    {
		$date = DateTime::createFromFormat( 'U', $dateAsUnixTS );
        $datestring = $date->format("Y-m-d");

		$qry = "
        SELECT
            gehaltsbestandteil_id,
            gbt.von,
            gbt.bis,
            gbt.anmerkung,
            gbt.dienstverhaeltnis_id,
            gehaltstyp_kurzbz,
            valorisierungssperre,
            gbt.valorisierung,
            grundbetrag as grund_betrag_decrypted,
            betrag_valorisiert as betrag_val_decrypted,
            gt.bezeichnung as gehaltstyp_bezeichnung,
			vb.vertragsbestandteiltyp_kurzbz,
			bf.funktion_kurzbz,
			bf.oe_kurzbz,
			fkt.beschreibung as fkt_beschreibung,
			fb.bezeichnung as fb_bezeichnung,
			org.bezeichnung as org_bezeichnung,
			freitext.freitexttyp_kurzbz,
			freitext.titel as freitext_titel
        FROM hr.tbl_gehaltsbestandteil gbt LEFT JOIN hr.tbl_gehaltstyp gt using(gehaltstyp_kurzbz)
			LEFT JOIN hr.tbl_vertragsbestandteil vb using(vertragsbestandteil_id)
			LEFT JOIN hr.tbl_vertragsbestandteil_funktion vbf using(vertragsbestandteil_id)
			LEFT JOIN public.tbl_benutzerfunktion bf using(benutzerfunktion_id)
			LEFT JOIN public.tbl_funktion fkt using(funktion_kurzbz)
			LEFT JOIN public.tbl_fachbereich fb using(fachbereich_kurzbz)
			LEFT JOIN public.tbl_organisationseinheit org on (bf.oe_kurzbz=org.oe_kurzbz)
			LEFT JOIN hr.tbl_vertragsbestandteil_freitext freitext on(vb.vertragsbestandteil_id=freitext.vertragsbestandteil_id)
        WHERE gbt.dienstverhaeltnis_id=? AND
			(gbt.von<=? and (gbt.bis is null OR gbt.bis>=?))
        ORDER BY gt.sort
        ";

        return $this->execQuery($qry,
			array($dienstverhaeltnis_id, $datestring, $datestring),
			$this->getEncryptedColumns());
    }

	public function getGBTChartDataByDV_old($dienstverhaeltnis_id)
    {		

		$qry = "
        WITH gbt as
			(select von,bis,grundbetrag as grund_betrag_decrypted  from hr.tbl_gehaltsbestandteil where dienstverhaeltnis_id=?)
			select von,bis, (select sum(gbt.grund_betrag_decrypted) as sum_betrag
			from gbt where gbt.von<=gbtmeta.von and (gbt.bis is null or gbt.bis>=gbtmeta.von)
			) as summe from gbt as gbtmeta order by von,bis
        ";

        return $this->execQuery($qry,
			array($dienstverhaeltnis_id),
			$this->getEncryptedColumns());
    }

	
	public function getGehaltsbestandteile($dienstverhaeltnis_id, $stichtag=null, $includefuture=false)
	{
		$stichtagclause = '';
		if( !is_null($stichtag) )
		{
			$date = strftime('%Y-%m-%d', strtotime($stichtag));
			$stichtagclause = 'AND (' . $this->escape($date)
				. ' BETWEEN COALESCE(von, \'1970-01-01\'::date)'
				. ' AND COALESCE(bis, \'2170-01-01\'::date)';
			if( $includefuture ) 
			{
				$stichtagclause .= ' OR COALESCE(von, \'1970-01-01\'::date) > ' 
					. $this->escape($date);
			}
			$stichtagclause .= ')';
		}

		$this->addSelect('*');
		$where = <<<EOSQL
				dienstverhaeltnis_id = {$this->escape($dienstverhaeltnis_id)} 
				{$stichtagclause}
EOSQL;

		$query = $this->loadWhere(
			$where,
			$this->getEncryptedColumns()
		);

		$gehaltsbestandteile = array(); 
		if( null !== ($rows = getData($query)) )
		{
			foreach( $rows as $row ) {
				$tmpgb = new Gehaltsbestandteil();
				$tmpgb->hydrateByStdClass($row, true);
				$gehaltsbestandteile[] = $tmpgb;
			}
		}

		return $gehaltsbestandteile;
	}


	public function getGehaltsbestandteil($id)
	{	
		$this->addSelect('*');
		$query = $this->load($id, $this->getEncryptedColumns());
		$gehaltsbestandteil = null;
		
		if( null !== ($row = getData($query)) )
		{
			$gehaltsbestandteil = new Gehaltsbestandteil();
			$gehaltsbestandteil->hydrateByStdClass($row[0], true);
		}

		return $gehaltsbestandteil;
	}
}
