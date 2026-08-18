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
	public function getZeitsperrenForNextDays($days, $uid=null)
	{
		$von = date('Y-m-d');
		$bis = date('Y-m-d', strtotime("+{$days} days"));

		$paramsArray = [$von, $bis, $bis, $von];

		if($uid)
			$paramsArray[] = $uid;

		$qry = "select
					nachname,
					vorname,
					uid,
					vondatum,
					bisdatum,
					vertretung_uid,
					erreichbarkeit_kurzbz,
					mv.kurzbz
				from
					campus.vw_mitarbeiter
				join campus.tbl_zeitsperre on
					(uid = mitarbeiter_uid)
				left join public.tbl_mitarbeiter mv on campus.tbl_zeitsperre.vertretung_uid = mv.mitarbeiter_uid
				where
					((? <= bisdatum
						and ? >= bisdatum)
					or (?>= vondatum
						and ? <= vondatum))";

		if($uid)
			$qry .= " and uid = ? ";

		$qry .= "
				order by nachname
				";

		$result = $this->execQuery($qry, $paramsArray);

		return $result;
	}

	public function getMitarbeiterWithZeitsperren($von, $bis, $fix=false, $lector=false, $oe=null, $ass=false, $stg=false)
	{
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

	public function checkIfZeitsperreExists($uid, $datum, $stunde)
	{
		$this->db->select("*");

		$this->db->where('mitarbeiter_uid', $uid);
		$this->db->where('zeitsperretyp_kurzbz !=', 'ZVerfueg');

		$this->db->group_start();

			$this->db->where('vondatum <', $datum);
			$this->db->or_group_start();
				$this->db->where('vondatum', $datum);
				$this->db->group_start();
					$this->db->where('vonstunde <=', $stunde);
					$this->db->or_where('vonstunde IS NULL', null, false);
				$this->db->group_end();
			$this->db->group_end();
		$this->db->group_end();


		$this->db->group_start();
			$this->db->where('bisdatum >', $datum);
			$this->db->or_group_start();
				$this->db->where('bisdatum', $datum);
				$this->db->group_start();
					$this->db->where('bisstunde >=', $stunde);
					$this->db->or_where('bisstunde IS NULL', null, false);
				$this->db->group_end();

			$this->db->group_end();
		$this->db->group_end();

		return $this->load();

	}
}
