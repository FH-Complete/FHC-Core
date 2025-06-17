<?php
class Mitarbeiter_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_mitarbeiter';
		$this->pk = 'mitarbeiter_uid';
	}

    /**
     * Checks if the user is a Mitarbeiter.
     * @param string $uid
     * @param boolean null $fixangestellt
     * @return array
     */
    public function isMitarbeiter($uid, $fixangestellt = null)
    {
        $this->addSelect('1');

        if (is_bool($fixangestellt))
        {
            $result = $this->loadWhere(array('mitarbeiter_uid' => $uid, 'fixangestellt' => $fixangestellt));
        }
        else    // default
        {
            $result = $this->loadWhere(array('mitarbeiter_uid' => $uid));
        }

        if(hasData($result))
        {
            return success(true);
        }
        else
        {
            return success(false);
        }
    }

	/**
	 * Laedt das Personal
	 *
	 * @param $aktiv wenn true werden nur aktive geladen, wenn false dann nur inaktve, wenn null dann alle
	 * @param $fix wenn true werden nur fixangestellte geladen
	 * @param $verwendung wenn true werden alle geladen die eine BIS-Verwendung eingetragen haben
	 * @param $personaccount wenn true werden alle geladen die personalnr >= 0 haben, also "echte" Personaccounts
	 * @return array
	 */
	public function getPersonal($aktiv, $fix, $verwendung, $personaccount = null, $uids = null)
	{
		$qry = "SELECT DISTINCT ON(mitarbeiter_uid) staatsbuergerschaft, geburtsnation, sprache, anrede, titelpost, titelpre,
									nachname, vorname, vornamen, gebdatum, gebort, gebzeit, tbl_person.anmerkung AS person_anmerkung, homepage, svnr, ersatzkennzeichen, familienstand,
									geschlecht, anzahlkinder, tbl_person.insertamum AS person_insertamum, tbl_person.updateamum as person_updateamum,
									tbl_person.updatevon AS person_updatevon, kompetenzen, kurzbeschreibung, zugangscode, zugangscode_timestamp, bpk,
									tbl_benutzer.*, tbl_mitarbeiter.*, akt_funk.oe_kurzbz AS funktionale_zuordnung, akt_funk.wochenstunden
					FROM ((public.tbl_mitarbeiter JOIN public.tbl_benutzer ON(mitarbeiter_uid=uid))
					JOIN public.tbl_person USING(person_id))
			   LEFT JOIN public.tbl_benutzerfunktion USING(uid)
			   LEFT JOIN public.tbl_benutzerfunktion akt_funk ON tbl_mitarbeiter.mitarbeiter_uid = akt_funk.uid AND akt_funk.funktion_kurzbz = 'fachzuordnung'
			   													AND (akt_funk.datum_von IS NULL OR akt_funk.datum_von <= now()) AND (akt_funk.datum_bis IS NULL OR akt_funk.datum_bis >= now())
				   WHERE true";

		if ($fix === true)
			$qry .= " AND fixangestellt=true";
		elseif ($fix === false)
			$qry .= " AND fixangestellt=false";

		if ($aktiv === true)
			$qry .= " AND tbl_benutzer.aktiv=true";
		elseif ($aktiv === false)
			$qry .= " AND tbl_benutzer.aktiv=false";

		if ($verwendung === true)
		{
			$qry.=" AND EXISTS(SELECT * FROM bis.tbl_bisverwendung WHERE (ende>now() or ende is null) AND tbl_bisverwendung.mitarbeiter_uid=tbl_mitarbeiter.mitarbeiter_uid)";
		}
		elseif ($verwendung === false)
		{
			$qry.=" AND NOT EXISTS(SELECT * FROM bis.tbl_bisverwendung WHERE (ende>now() or ende is null) AND tbl_bisverwendung.mitarbeiter_uid=tbl_mitarbeiter.mitarbeiter_uid)";
		}

		if ($personaccount === true)
			$qry .= " AND tbl_mitarbeiter.personalnummer >= 0";
		elseif ($personaccount === false)
			$qry .= " AND tbl_mitarbeiter.personalnummer < 0";

		$params = array();
		if (!isEmptyArray($uids))
		{
			$qry .= " AND tbl_mitarbeiter.mitarbeiter_uid IN ?";
			$params[] = $uids;
		}

		return $this->execQuery($qry, $params);
	}

	/**
	 * Gibt ein Array mit den UIDs der Vorgesetzten zurück
	 * @return object
	 */
	public function getVorgesetzte($uid, $datum_von = null, $datum_bis = null)
	{
		$datum_von_var = isset($datum_von) ? '?' : 'now()';
		$datum_bis_var = isset($datum_bis) ? '?' : 'now()';
		$qry = "SELECT
					DISTINCT uid  as vorgesetzter
				FROM
					public.tbl_benutzerfunktion
				WHERE
					funktion_kurzbz='Leitung' AND
					(datum_von is null OR datum_von<=%s) AND
					(datum_bis is null OR datum_bis>=%s) AND
					oe_kurzbz in (SELECT oe_kurzbz
								  FROM public.tbl_benutzerfunktion
								  WHERE
									funktion_kurzbz='oezuordnung' AND uid=? AND
									(datum_von is null OR datum_von<=%s) AND
									(datum_bis is null OR datum_bis>=%s)
								  );";

		$qry = sprintf($qry, $datum_von_var, $datum_bis_var, $datum_von_var, $datum_bis_var);

		$params = array();
		if (isset($datum_von))
			$params[] = $datum_von;
		if (isset($datum_bis))
			$params[] = $datum_bis;

		$params[] = $uid;

		if (isset($datum_von))
			$params[] = $datum_von;
		if (isset($datum_bis))
			$params[] = $datum_bis;

		return $this->execQuery($qry, $params);
	}

	/**
	 * Checks if alias exists
	 * @param $kurzbz
	 */
	public function kurzbzExists($kurzbz, $uid=null)
	{
		$this->addSelect('1');
		$where = array('kurzbz' => $kurzbz);
		if ($uid != null)
		{
			$where['mitarbeiter_uid<>'] = $uid;
		}
		$result = $this->loadWhere($where);

		if (isSuccess($result))
		{
			if (hasData($result))
			{
				$result = success(array(true));
			}
			else
			{
				$result = success(array(false));
			}
		}

		return $result;
	}

	/**
	 * Generates alias for a uid.
	 * @param $uid
	 * @return array the alias if newly generated
	 */
	public function generateKurzbz($uid)
	{
		$this->addLimit(1);
		$this->addSelect('vorname, nachname');
		$this->addJoin('public.tbl_benutzer', 'tbl_mitarbeiter.mitarbeiter_uid = tbl_benutzer.uid');
		$this->addJoin('public.tbl_person', 'person_id');
		$nameresult = $this->loadWhere(array('uid' => $uid));

		if (hasData($nameresult))
		{
			$kurzbzdata = getData($nameresult);
			$genKurzbz = $this->generateKurzbzHelper($kurzbzdata[0]->vorname, $kurzbzdata[0]->nachname);

			return $genKurzbz;
		}
		return error('No Kurzbezeichnung could be generated');
	}

	public function generateKurzbzHelper($vorname, $nachname)
	{
		$nachname_clean = sanitizeProblemChars($nachname);
		$vorname_clean = sanitizeProblemChars($vorname);
		$kurzbz = '';

		for ($nn = 6, $vn = 2; $nn != 0; $nn--, $vn++)
		{
			$kurzbz = mb_substr($nachname_clean, 0, $nn);
			$kurzbz .= mb_substr($vorname_clean, 0, $vn);

			$kurzbzexists = $this->kurzbzExists($kurzbz);

			if (hasData($kurzbzexists) && !getData($kurzbzexists)[0])
				break;
		}

		$kurzbzexists = $this->kurzbzExists($kurzbz);

		if (hasData($kurzbzexists) && getData($kurzbzexists)[0])
			return error('No Kurzbezeichnung could be generated');
	
		return success($kurzbz);
	}

	/**
	 * Search function for mitarbeiter
	 * @param $filter searchstring: searches for nachname, vorname, mitarbeiter_uid
	 * $param $mode gives the resultobject in different version:
	 * 				null : "[mitarbeiter_uid], Nachname, Vorname, (mitarbeiter_uid)"
	 * 				'mitAkadGrad': "[mitarbeiter_uid], Nachname, Vorname, Titelpre, Titelpost (mitarbeiter_uid)"
	 *				'ohneMaUid' : "[mitarbeiter_uid], Nachname, Vorname, Titelpre, Titelpost"
	 * @return object in 3 versions
	 */
	public function searchMitarbeiter($filter, $mode=null)
	{
		$filter = strtoLower($filter);

		if ($mode == "mitAkadGrad")
			$returnwert = "ma.mitarbeiter_uid, CONCAT(p.nachname, ' ', p.vorname, ' ', p.titelpost, ' ', p.titelpre, ' (', ma.mitarbeiter_uid , ')') as mitarbeiter";
		elseif ($mode == "ohneMaUid")
			$returnwert = "p.person_id, CONCAT(p.nachname, ' ', p.vorname, ' ', p.titelpost, ' ', p.titelpre) as mitarbeiter";
		else
			$returnwert = "ma.mitarbeiter_uid, CONCAT(p.nachname, ' ', p.vorname, ' (', ma.mitarbeiter_uid , ')') as mitarbeiter";

		$qry = "
			SELECT " . $returnwert . "  
			FROM 
			    public.tbl_mitarbeiter ma 
			JOIN 
			    public.tbl_benutzer b on (ma.mitarbeiter_uid = b.uid)
			JOIN 
			    public.tbl_person p on (p.person_id = b.person_id)
			WHERE 
			    lower (p.nachname) LIKE '%". $this->db->escape_like_str($filter)."%'
			OR
				lower (p.vorname) LIKE '%". $this->db->escape_like_str($filter)."%'
			OR
				(ma.mitarbeiter_uid) LIKE '%". $this->db->escape_like_str($filter)."%'";

		return $this->execQuery($qry);
	}

	/**
	 * Gets Mitarbeiter for a certain Lehrveranstaltung.
	 *
	 * @param $lehrveranstaltung_id
	 * @return array with Mitarbeiter and their Lehreinheiten
	 */
	public function getMitarbeiterFromLV($lehrveranstaltung_id)
	{
		$qry = "SELECT DISTINCT
    			lehrveranstaltung_id, uid, vorname, wahlname, vornamen, nachname, titelpre, titelpost, kurzbz, mitarbeiter_uid 
			FROM 
			    lehre.tbl_lehreinheitmitarbeiter, campus.vw_mitarbeiter, lehre.tbl_lehreinheit
			WHERE 
			    lehrveranstaltung_id= ?
			AND 
				mitarbeiter_uid=uid 
			AND 
				tbl_lehreinheitmitarbeiter.lehreinheit_id=tbl_lehreinheit.lehreinheit_id;";

		$parametersArray = array($lehrveranstaltung_id);

		return $this->execQuery($qry, $parametersArray);
	}

	/**
	 * Get Lektoren by studiengang_kz
	 *
	 * @param $studiengang_kz
	 * @return array with Mitarbeiter
	 */
	public function getLektoren($studiengang_kz)
	{
		$qry = "
			SELECT DISTINCT
			    campus.vw_mitarbeiter.uid,
				campus.vw_mitarbeiter.vorname,
				campus.vw_mitarbeiter.nachname,
				studiengang_kz,
				tbl_studiengang.typ,
				tbl_studiengang.kurzbz AS stg_kurzbz
			FROM
			  campus.vw_mitarbeiter
			  JOIN public.tbl_benutzerfunktion USING (uid)
			  JOIN public.tbl_studiengang USING(oe_kurzbz)
				WHERE studiengang_kz = ?
				AND lektor is true
			ORDER BY campus.vw_mitarbeiter.nachname";

		$parametersArray = array($studiengang_kz);

		return $this->execQuery($qry, $parametersArray);
	}
}
