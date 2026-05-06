<?php
class Zeitsperre_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_zeitsperre';
		$this->pk = 'zeitsperre_id';
	}

	const BLOCKIERENDE_ZEITSPERREN = ['Krank','Urlaub','ZA','DienstV','PflegeU','DienstF','CovidSB','CovidKS'];

    /**
     * Save or update Zeitsperre.
     *
     * @param $zeitsperretyp_kurzbz
     * @param $mitarbeiter_uid
     * @param $vonDatum
     * @param $bisDatum
     * @param null $vonStunde
     * @param null $bisStunde
     * @param null $bezeichnung
     * @param null $vertretung_uid
     * @param null $erreichbarkeit_kurzbz
     * @param null $freigabeamum
     * @param null $freigabevon
     * @return array
     */
    public function save($zeitsperretyp_kurzbz, $mitarbeiter_uid, $vonDatum, $bisDatum,
                         $vonStunde = null, $bisStunde = null, $bezeichnung = null, $vertretung_uid = null,
                         $erreichbarkeit_kurzbz = null, $freigabeamum = null, $freigabevon = null)
    {
        return $this->insert(array(
            'zeitsperretyp_kurzbz' => $zeitsperretyp_kurzbz,
            'mitarbeiter_uid' => $mitarbeiter_uid,
            'vondatum' => $vonDatum,
            'bisdatum' => $bisDatum,
            'vonstunde' => $vonStunde,
            'bisstunde' => $bisStunde,
            'bezeichnung' => $bezeichnung,
            'vertretung_uid' => $vertretung_uid,
            'insertvon' => getAuthUID(),
            'insertamum' => (new DateTime())->format('Y-m-d H:i:s'),
            'erreichbarkeit_kurzbz' => $erreichbarkeit_kurzbz,
            'freigabeamum' => $freigabeamum,
            'freigabevon' => $freigabevon
            ));
    }

    /**
     * Delete Zeitsperre.
     * @return array|stdClass|null
     */
    public function deleteEntriesForCurrentDay()
    {
        $today = date('Y-m-d');
        $qry = "DELETE FROM " . $this->dbTable . " 
                WHERE vondatum = '" . $today . "';";

        return $this->execQuery($qry);
    }

	/**
	 * get Zeitsperren of a user
	 *
	 * @param $uid mitarbeiteruid
	 * @param $bisgrenze @true show only entries of actual business year (1.9.- 31.8.)
	 *
	 * @return array
	 */
	public function getZeitsperrenUser($uid, $bisgrenze = true)
	{
		$qry = "
  			SELECT
			    tbl_zeitsperre.*, tbl_zeitsperretyp.*, tbl_erreichbarkeit.farbe  AS erreichbarkeit_farbe,
  			    tbl_erreichbarkeit.beschreibung AS erreichbarkeit_beschreibung,
            	CONCAT (ps.vorname, ' ', ps.nachname) as vertretung
			FROM (campus.tbl_zeitsperre JOIN campus.tbl_zeitsperretyp USING (zeitsperretyp_kurzbz))
			LEFT JOIN campus.tbl_erreichbarkeit USING (erreichbarkeit_kurzbz)
			LEFT JOIN public.tbl_benutzer ON campus.tbl_zeitsperre.vertretung_uid = public.tbl_benutzer.uid
			LEFT JOIN public.tbl_person ps USING (person_id)
			WHERE mitarbeiter_uid= ?
			";

		if($bisgrenze)
		{
			$qry.=" 
				AND (
					(date_part('month',vondatum)>=9 AND date_part('year', vondatum)>='".(date('Y')-1)."')
					OR
					(date_part('month',vondatum)<9 AND date_part('year', vondatum)>='".(date('Y'))."')
				)";
		}

		$qry.= " ORDER BY vondatum DESC";

		return $this->execQuery($qry, array('mitarbeiter_uid' => $uid));
	}

	/**
	 * check a date for existing zeitsperre
	 *
	 * @param $uid mitarbeiteruid
	 * @param $datum datum to check
	 * @param $stunde stunde (default = null)
	 * @param bool $nurblockierend if only hr relevante zeitsperren have to be checked
	 *
	 * @return array
	 */
	public function getSperreByDate($uid, $datum, $stunde = null, $nurblockierend = false)
	{
		$parametersArray = [$datum, $datum];

		$qry = "
			SELECT
				*
			FROM
				campus.tbl_zeitsperre
			WHERE
				vondatum <= ?
				AND bisdatum>= ?";

		if($nurblockierend)
		{
			$qry .= " AND zeitsperretyp_kurzbz IN ('"
				. implode("','", self::BLOCKIERENDE_ZEITSPERREN)
				. "')";
		}

		if(!is_null($stunde))
		{
			$parametersArray = array_merge(
				$parametersArray,
				[$datum, $stunde, $datum, $datum, $stunde, $datum]
			);

			$qry.=" AND
					((vondatum= ? AND vonstunde<= ? OR vonstunde is null OR vondatum<> ?) AND
					(bisdatum= ? AND bisstunde>= ? OR bisstunde is null OR bisdatum<> ?))";
		}

		array_push($parametersArray, $uid);

		$qry .= "AND mitarbeiter_uid= ? ";

		return $this->execQuery($qry, $parametersArray);
	}

	/**
	 * check a date for existing zeitsperre
	 *
	 * @param $uid mitarbeiteruid
	 * @param $vondatum datum in Format IS0
	 * @param $bisdatum datum in Format ISO
	 *
	 * @return array
	 */
	public function existsZeitaufzeichnung($uid, $vonDay, $bisDay)
	{
		try {
			$from = new DateTime($vonDay);
			$to   = new DateTime($bisDay);
		} catch (Exception $e) {
			throw new Exception("Invalid date format");
		}

		//remove hour stamps
		$from->setTime(0, 0, 0);
		$to->setTime(0, 0, 0)->modify('+1 day');

		$fromSql = $from->format('Y-m-d');
		$toSql   = $to->format('Y-m-d');
		$params = [$uid, $fromSql, $toSql];

		$qry = "
			SELECT *
			FROM campus.tbl_zeitaufzeichnung
			WHERE uid = ?
			AND start >= ?
			AND ende < ? ";

		$result = $this->execQuery($qry, $params);

		return $result;
	}
}
