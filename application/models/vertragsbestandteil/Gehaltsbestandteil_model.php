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
with gbt as (
	select 
	    gb.gehaltsbestandteil_id,
	    gb.von,
	    gb.bis,
	    gb.anmerkung,
	    gb.dienstverhaeltnis_id,
	    gb.gehaltstyp_kurzbz,
	    gb.valorisierungssperre,
	    gb.valorisierung,
	    gb.grundbetrag as grund_betrag_decrypted,
	    coalesce(vh.betrag_valorisiert, gb.grundbetrag) as betrag_val_decrypted,
	    gb.vertragsbestandteil_id
	from 
		hr.tbl_gehaltsbestandteil gb
	LEFT JOIN 
		hr.tbl_valorisierung_historie vh ON vh.gehaltsbestandteil_id = gb.gehaltsbestandteil_id AND vh.valorisierungsdatum = (
	      	SELECT 
	          	vi.valorisierungsdatum 
	          FROM 
	          	hr.tbl_valorisierung_instanz vi
	          JOIN
	            hr.tbl_dienstverhaeltnis d ON d.dienstverhaeltnis_id = ? 
				AND d.oe_kurzbz = vi.oe_kurzbz
	          WHERE 
	          	? >= valorisierungsdatum 
	          ORDER BY 
	          	valorisierungsdatum DESC 
	          LIMIT 1
	        )
	where
		dienstverhaeltnis_id = ?
		and (
			? BETWEEN COALESCE(von, '1970-01-01'::date) AND COALESCE(bis, '2170-01-01'::date) 
		)
)
select
    gbt.gehaltsbestandteil_id,
    gbt.von,
    gbt.bis,
    gbt.anmerkung,
    gbt.dienstverhaeltnis_id,
    gbt.gehaltstyp_kurzbz,
    gbt.valorisierungssperre,
    gbt.valorisierung,
    gbt.grund_betrag_decrypted,
    gbt.betrag_val_decrypted,
	gt.bezeichnung as gehaltstyp_bezeichnung,
	vb.vertragsbestandteiltyp_kurzbz,
	bf.funktion_kurzbz,
	bf.oe_kurzbz,
	fkt.beschreibung as fkt_beschreibung,
	fb.bezeichnung as fb_bezeichnung,
	org.bezeichnung as org_bezeichnung,
	freitext.freitexttyp_kurzbz,
	freitext.titel as freitext_titel
from
	gbt
LEFT JOIN 
	hr.tbl_gehaltstyp gt using(gehaltstyp_kurzbz)
LEFT JOIN 
	hr.tbl_vertragsbestandteil vb using(vertragsbestandteil_id)
LEFT JOIN 
	hr.tbl_vertragsbestandteil_funktion vbf using(vertragsbestandteil_id)
LEFT JOIN 
	public.tbl_benutzerfunktion bf using(benutzerfunktion_id)
LEFT JOIN 
	public.tbl_funktion fkt using(funktion_kurzbz)
LEFT JOIN 
	public.tbl_fachbereich fb using(fachbereich_kurzbz)
LEFT JOIN 
	public.tbl_organisationseinheit org on (bf.oe_kurzbz=org.oe_kurzbz)
LEFT JOIN 
	hr.tbl_vertragsbestandteil_freitext freitext on(vb.vertragsbestandteil_id=freitext.vertragsbestandteil_id)
        ";

        return $this->execQuery($qry,
			array($dienstverhaeltnis_id, $datestring, $dienstverhaeltnis_id, $datestring),
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
	
	public function getGehaltsbestandteile($dienstverhaeltnis_id, $stichtag=null, 
		$includefuture=false, $withvalorisationhistory=true)
	{
		if( !is_null($stichtag) && (time() > strtotime($stichtag)) 
			&& $withvalorisationhistory !== false ) 
		{
			$query = $this->getGehaltsbestandteileMitValorisierungsHistorie(
				$dienstverhaeltnis_id, $stichtag, $includefuture
			);
		}
		else
		{
			$query = $this->getGehaltsbestandteileOhneValorisierungsHistorie(
				$dienstverhaeltnis_id, $stichtag, $includefuture
			);
		}
		
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

	protected function getGehaltsbestandteileOhneValorisierungsHistorie($dienstverhaeltnis_id, $stichtag=null, $includefuture=false)
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

		$result = $this->loadWhere(
			$where,
			$this->getEncryptedColumns()
		);
		return $result;
	}
	
	protected function getGehaltsbestandteileMitValorisierungsHistorie($dienstverhaeltnis_id, $stichtag, $includefuture=false)
	{
		$date = strftime('%Y-%m-%d', strtotime($stichtag));
		$includefuture_clause = ($includefuture) 
			? ' OR COALESCE(von, \'1970-01-01\'::date) > ' . $this->escape($date) 
			: '';
		$sql = <<<EOSQL
SELECT    
	g.gehaltsbestandteil_id, 
	g.dienstverhaeltnis_id, 
	g.vertragsbestandteil_id, 
	g.gehaltstyp_kurzbz, 
	g.von, 
	g.bis, 
	g.anmerkung, 
	g.grundbetrag AS grundbetrag, 
    COALESCE(vh.betrag_valorisiert, g.grundbetrag) AS betrag_valorisiert, 
	g.valorisierungssperre, 
	g.insertamum, 
	g.insertvon, 
	g.updateamum, 
	g.updatevon, 
	g.valorisierung, 
	g.auszahlungen 
FROM 
	hr.tbl_gehaltsbestandteil g 
LEFT JOIN 
	hr.tbl_valorisierung_historie vh ON vh.gehaltsbestandteil_id = g.gehaltsbestandteil_id AND vh.valorisierungsdatum = (
          SELECT 
          	vi.valorisierungsdatum 
          FROM 
          	hr.tbl_valorisierung_instanz vi
          JOIN
            hr.tbl_dienstverhaeltnis d ON d.dienstverhaeltnis_id = {$this->escape($dienstverhaeltnis_id)} 
			AND d.oe_kurzbz = vi.oe_kurzbz
          WHERE 
          	{$this->escape($date)} >= valorisierungsdatum 
          ORDER BY 
          	valorisierungsdatum DESC 
          LIMIT 1
        )
WHERE 
	g.dienstverhaeltnis_id = {$this->escape($dienstverhaeltnis_id)} 
	AND (
		{$this->escape($date)} BETWEEN COALESCE(von, '1970-01-01'::date) AND COALESCE(bis, '2170-01-01'::date)
		{$includefuture_clause}
	)
EOSQL;
	
		$result = $this->execReadOnlyQuery($sql, array(), $this->getEncryptedColumns());
		return $result;
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
