<?php

class Dienstverhaeltnis_model extends DB_Model
{

    public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'hr.tbl_dienstverhaeltnis';
		$this->pk = 'dienstverhaeltnis_id';
	}

    /**
     * @return list of DV
     */
    public function getDVByPersonUID($uid, $oe_kurzbz=null, $datum=null)
    {
        $result = null;

		$qry = "
        SELECT
            dv.dienstverhaeltnis_id,
            tbl_benutzer.uid,
            tbl_mitarbeiter.personalnummer,
            tbl_mitarbeiter.kurzbz,
            tbl_mitarbeiter.lektor,
            tbl_mitarbeiter.fixangestellt,
            tbl_person.person_id,
            tbl_benutzer.alias,
            org.oe_kurzbz,
            org.bezeichnung oe_bezeichnung,
            dv.von,
            dv.bis,
	    dv.dvendegrund_kurzbz,
	    dv.dvendegrund_anmerkung,
            dv.vertragsart_kurzbz,
            dv.updateamum,
            dv.updatevon,
            dv.dvendegrund_kurzbz,
            dv.dvendegrund_anmerkung
        FROM tbl_mitarbeiter
            JOIN tbl_benutzer ON tbl_mitarbeiter.mitarbeiter_uid::text = tbl_benutzer.uid::text
            JOIN tbl_person USING (person_id)
            JOIN hr.tbl_dienstverhaeltnis dv ON(tbl_benutzer.uid::text = dv.mitarbeiter_uid::text)
            JOIN public.tbl_organisationseinheit org USING(oe_kurzbz)
        WHERE tbl_benutzer.uid=?";
		$data = array($uid);

		if(!is_null($oe_kurzbz))
		{
			$qry.=" AND oe_kurzbz=?";
			$data[] = $oe_kurzbz;
		}

		if (!is_null($datum))
		{
			$qry.=" AND ? BETWEEN dv.von AND COALESCE(dv.bis, '2999-12-31')";
			$data[] = $datum;
		}

		$qry .="
        ORDER BY dv.von desc
        ";

        return $this->execQuery($qry, $data);

    }

    public function getDVByID($dvid) {
        $this->addSelect('hr.tbl_dienstverhaeltnis.*, public.tbl_organisationseinheit.bezeichnung as unternehmen');
		$this->addJoin('public.tbl_organisationseinheit', 'hr.tbl_dienstverhaeltnis.oe_kurzbz = public.tbl_organisationseinheit.oe_kurzbz');
		$result = $this->load($dvid);

		if (hasData($result)) {
            return $result;
        }
        return error('could not fetch DV by ID');
    }


    public function getCurrentDVByPersonUID($uid, $dateAsUnixTS)
    {

        $date = DateTime::createFromFormat( 'U', $dateAsUnixTS );
        $datestring = $date->format("Y-m-d");

		$qry = "
        SELECT
            dv.dienstverhaeltnis_id,
            tbl_benutzer.uid,
            tbl_mitarbeiter.personalnummer,
            tbl_mitarbeiter.kurzbz,
            tbl_mitarbeiter.lektor,
            tbl_mitarbeiter.fixangestellt,
            tbl_person.person_id,
            tbl_benutzer.alias,
            dv.von,
            dv.bis,
            dv.vertragsart_kurzbz,
            dv.updateamum,
            dv.updatevon
        FROM tbl_mitarbeiter
            JOIN tbl_benutzer ON tbl_mitarbeiter.mitarbeiter_uid::text = tbl_benutzer.uid::text
            JOIN tbl_person USING (person_id)
            JOIN hr.tbl_dienstverhaeltnis dv ON(tbl_benutzer.uid::text = dv.mitarbeiter_uid::text)
        WHERE tbl_benutzer.uid=? and (dv.von<=? and (dv.bis is null OR dv.bis>=?))
        ORDER BY dv.von desc
        ";

        return $this->execQuery($qry, array($uid, $datestring, $datestring));
    }

	public function isOverlappingExistingDV($mitarbeiter_uid, $oe_kurzbz, $von, $bis, $dvid=null)
	{
		$params = array($mitarbeiter_uid, $oe_kurzbz, $von, $bis, $von, $bis);
		$dvidclause = '';
		if (intval($dvid) > 0)
		{
			$params = array_merge($params, array($dvid, $dvid));
			$dvidclause = <<<EODVIDC
            AND (
				SELECT
					COUNT(*) AS karenzen
				FROM
					hr.tbl_vertragsbestandteil vb
				WHERE
					vb.dienstverhaeltnis_id = ?
				AND
					vb.vertragsbestandteiltyp_kurzbz = 'karenz'
				AND
					dv.von::date >= COALESCE(vb.von, '1970-01-01'::date)
				AND
					COALESCE(dv.bis::date, '2170-12-31'::date) <= COALESCE(vb.bis, '2170-12-31')
			) = 0
			AND dv.dienstverhaeltnis_id != ?
EODVIDC;

		}

		$query = <<<EOSQL
			SELECT
				count(*) AS dvcount
			FROM
				hr.tbl_dienstverhaeltnis dv
			WHERE
				dv.mitarbeiter_uid = ?
			AND
				dv.oe_kurzbz = ?
			AND
				dv.vertragsart_kurzbz NOT IN ('werkvertrag', 'studentischehilfskr')
			AND
				?::date <= COALESCE(dv.bis, '2170-12-31'::date)
			AND
				COALESCE(?::date, '2170-12-31'::date) >= dv.von
			AND (
				SELECT
					COUNT(*) AS karenzen
				FROM
					hr.tbl_vertragsbestandteil vb
				WHERE
					vb.dienstverhaeltnis_id = dv.dienstverhaeltnis_id
				AND
					vb.vertragsbestandteiltyp_kurzbz = 'karenz'
				AND
					?::date >= COALESCE(vb.von, '1970-01-01'::date)
				AND
					COALESCE(?::date, '2170-12-31'::date) <= COALESCE(vb.bis, '2170-12-31')
			) = 0
			{$dvidclause}
EOSQL;

		$ret = $this->execReadOnlyQuery($query, $params);

		if( ($dvcount = getData($ret)) && ($dvcount[0]->dvcount > 0) ) {
			return true;
		}

		return false;
	}

	public function getDVByPersonUIDOverlapping($uid, $oe_kurzbz=null, $beginn=null, $ende=null)
    {
        $result = null;

		$qry = "
        SELECT
            dv.dienstverhaeltnis_id,
            tbl_benutzer.uid,
            tbl_mitarbeiter.personalnummer,
            tbl_mitarbeiter.kurzbz,
            tbl_mitarbeiter.lektor,
            tbl_mitarbeiter.fixangestellt,
            tbl_person.person_id,
            tbl_benutzer.alias,
            org.oe_kurzbz,
            org.bezeichnung oe_bezeichnung,
            dv.von,
            dv.bis,
            dv.vertragsart_kurzbz,
            dv.updateamum,
            dv.updatevon
        FROM tbl_mitarbeiter
            JOIN tbl_benutzer ON tbl_mitarbeiter.mitarbeiter_uid::text = tbl_benutzer.uid::text
            JOIN tbl_person USING (person_id)
            JOIN hr.tbl_dienstverhaeltnis dv ON(tbl_benutzer.uid::text = dv.mitarbeiter_uid::text)
            JOIN public.tbl_organisationseinheit org USING(oe_kurzbz)
        WHERE tbl_benutzer.uid=?";
		$data = array($uid);

		if(!is_null($oe_kurzbz))
		{
			$qry.=" AND oe_kurzbz=?";
			$data[] = $oe_kurzbz;
		}

		if (!is_null($beginn) && !is_null($ende))
		{
			$qry.=" AND (?,?) OVERLAPS (dv.von, COALESCE(dv.bis, '2999-12-31'))";
			$data[] = $beginn;
			$data[] = $ende;
		}

		$qry .="
        ORDER BY dv.von desc
        ";

        return $this->execQuery($qry, $data);

    }

    public function fetchDienstverhaeltnisse($unternehmen, $stichtag=null, $mitarbeiteruid=null) {
	$where = "oe_kurzbz = " . $this->escape($unternehmen);
	if( !is_null($stichtag) )
	{
		$where .= " AND " . $this->escape($stichtag) . " BETWEEN COALESCE(von, '1970-01-01') AND COALESCE(bis, '2070-12-31')";
	}
	if( !is_null($mitarbeiteruid) )
	{
		$where .= " AND mitarbeiter_uid = " . $this->escape($mitarbeiteruid);
	}
	$res = $this->loadWhere($where);
	$dvs = array();
	if(hasData($res) )
	{
		$dvs = getData($res);
	}
	return $dvs;
    }
}
