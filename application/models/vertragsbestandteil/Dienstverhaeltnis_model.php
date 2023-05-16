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
    public function getDVByPersonUID($uid)
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
        WHERE tbl_benutzer.uid=?
        ORDER BY dv.von desc
        ";

        return $this->execQuery($qry, array($uid));
		
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

	public function isOverlappingExistingDV($mitarbeiter_uid, $oe_kurzbz, $von, $bis)
	{
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
				?::date <= COALESCE(dv.bis, '2170-12-31'::date)
			AND 
				COALESCE(?::date, '2170-12-31'::date) >= dv.von
EOSQL;
		
		$ret = $this->execReadOnlyQuery($query, 
			array($mitarbeiter_uid, $oe_kurzbz, $von, $bis));
		
		if( ($dvcount = getData($ret)) && ($dvcount[0]->dvcount > 0) ) {
			return true;
		}
		
		return false;	
	}
}