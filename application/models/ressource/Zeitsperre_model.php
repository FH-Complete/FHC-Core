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
}
