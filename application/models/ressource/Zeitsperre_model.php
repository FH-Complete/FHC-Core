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

	/**
	 * getZeitsperren of Mitarbeiter for the next days
	 * @ days: count of the intervall of the next days
	 * @return array
	 */
	public function getZeitsperrenForNextDays($days, $type=null)
	{
		//TODO(Manu) von und bis im controller berechnen und hier übergeben
		$von = date('Y-m-d');
		$bis = date('Y-m-d', strtotime("+{$days} days"));

		//version with campus.vw_mitarbeiter
		$paramsArray = [$von, $bis, $bis, $von];
		$qry = "select
					nachname,
					vorname,
					uid,
					vondatum,
					bisdatum,
					vertretung_uid,
					erreichbarkeit_kurzbz
					--lektor,
					-- fixangestellt
				from
					campus.vw_mitarbeiter
				join campus.tbl_zeitsperre on
					(uid = mitarbeiter_uid)
				where
					((? <= bisdatum
						and ? >= bisdatum)
					or (?>= vondatum
						and ? <= vondatum))
				";

		//fix, lektor etc just with all ma
		if($type=="fix") {
			$qry.= "AND fixangestellt = true";
		}
		if($type=="lector") {
			$qry.= "AND lektor = true";
		}

		//wenn lektor= WAHR und fixangestellt = Falsch: -->  externer Lektor

		$qry .= " order by nachname";



		//version with hr.dienstverhaeltnis
/*			$paramsArray = [$bis, $von];
		$qry = "
			SELECT
				ps.nachname,
				ps.vorname,
				zs.mitarbeiter_uid,
				zs.vondatum,
				zs.bisdatum,
				zs.vertretung_uid,
				zs.erreichbarkeit_kurzbz
			FROM campus.tbl_zeitsperre zs
			JOIN public.tbl_benutzer bn
				ON bn.uid = zs.mitarbeiter_uid
			JOIN public.tbl_person ps
				ON ps.person_id = bn.person_id ";

		if ($type != null) {
			$qry.= " JOIN hr.tbl_dienstverhaeltnis dv on bn.uid = dv.mitarbeiter_uid ";
		}

		$qry.="
			WHERE
				zs.vondatum <= ?
				AND zs.bisdatum >= ?";

		if($type=="fix") {
			$qry.= " and dv.vertragsart_kurzbz = 'echterdv'";
		}
		if($type=="lector") {
			$qry.= " and dv.vertragsart_kurzbz in ('externerlehrender', 'gastlektor')";
		}

		$qry .= "			
			ORDER BY
				ps.nachname,
				ps.vorname,
				zs.vondatum;";*/

		$result = $this->execQuery($qry, $paramsArray);

		return $result;
	}

	public function getMitarbeiterWithZeitsperren($days, $fix=false, $lector=false, $oe=null, $ass=false, $stg=false)
	{
		//TODO(Manu) von und bis im controller berechnen und hier übergeben
		$von = date('Y-m-d');
		$bis = date('Y-m-d', strtotime("+{$days} days"));

		//version with campus.vw_mitarbeiter
		$paramsArray = [$von, $bis, $bis, $von];

		if($oe)
		{
			$paramsArray[] = $oe;
		}

		if($stg)
		{
			$paramsArray[] = $stg;
		}

		$qry = "
			SELECT
				m.nachname,
				m.vorname,
				m.uid,
				z.vondatum,
				z.bisdatum,
				z.vertretung_uid,
				z.erreichbarkeit_kurzbz,
				m.lektor,
				m.fixangestellt,
				mv.kurzbz
			FROM campus.vw_mitarbeiter m
			join public.tbl_benutzer bn on bn.uid = m.uid";

		if($oe || $ass || $stg)
		{
			$qry .= " JOIN public.tbl_benutzerfunktion bf on bf.uid = bn.uid";
		}

		if($stg)
		{
			$qry .= " JOIN public.tbl_studiengang stg using (oe_kurzbz)";
		}

		$qry.= "
			LEFT JOIN campus.tbl_zeitsperre z
				ON m.uid = z.mitarbeiter_uid
			   AND (
					(? <= z.bisdatum AND ? >= z.bisdatum)
					OR
					(? >= z.vondatum AND ? <= z.vondatum)
			   )
			left join public.tbl_mitarbeiter mv on z.vertretung_uid = mv.mitarbeiter_uid
			where bn.aktiv = true
				";


		if($fix) {
			$qry.= " AND m.fixangestellt = true";
		}
		if($lector) {
			$qry.= " AND m.lektor = true";
		}

		if ($oe)
		{
			$qry.= " 
				AND bf.funktion_kurzbz ='oezuordnung'
				AND bf.oe_kurzbz= ?
				AND (bf.datum_von is null or bf.datum_von <=now()) 
				AND (bf.datum_bis is null or bf.datum_bis >= now())
				";
		}

		if ($ass)
		{
			$qry.= " 
				AND bf.funktion_kurzbz ='ass'
				AND (bf.datum_von is null or bf.datum_von <=now()) 
				AND (bf.datum_bis is null or bf.datum_bis >= now())
				";
		}

		if ($stg)
		{
			$qry.= " 
				AND stg.studiengang_kz = ?
				";
		}

		$qry .= " order by nachname, vorname, uid";


		$result = $this->execQuery($qry, $paramsArray);

		return $result;

	}
}
