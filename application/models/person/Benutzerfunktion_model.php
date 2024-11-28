<?php

class Benutzerfunktion_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_benutzerfunktion';
		$this->pk = 'benutzerfunktion_id';
	}

	/**
	 * Lädt alle Benutzerfunktionen zu einer UID
	 * @param type $uid UID des Mitarbeiters
	 * @param type $funktion_kurzbz OPTIONAL Kurzbezeichnung der Funktion
	 * @param type $startZeitraum OPTIONAL Start Zeitraum in dem die Funktion aktiv ist
	 * @param type $endeZeitraum OPTIONAL Ende Zeitraum in dem die Funktion aktiv ist
	 * @return boolean
	 */
	public function getBenutzerFunktionByUid($uid, $funktion_kurzbz=null, $startZeitraum=null, $endeZeitraum=null)
	{
		$params = array($uid);

		$qry = "SELECT tbl_benutzerfunktion.*, tbl_organisationseinheit.bezeichnung as organisationseinheit_bezeichnung,
					tbl_organisationseinheit.organisationseinheittyp_kurzbz
				FROM public.tbl_benutzerfunktion
				LEFT JOIN public.tbl_organisationseinheit USING(oe_kurzbz)
			    WHERE uid=?";
		if(!is_null($funktion_kurzbz))
		{
			$qry .= ' AND funktion_kurzbz = ?';
			$params[] = $funktion_kurzbz;
		}
		if(!is_null($startZeitraum))
		{
			$qry .=' AND (datum_bis IS NULL OR datum_bis >= ?)';
			$params[] = $startZeitraum;
		}
		if(!is_null($endeZeitraum))
		{
			$qry .=' AND (datum_von IS NULL OR datum_von <= ?)';
			$params[] = $endeZeitraum;
		}

		$qry .= "ORDER BY datum_bis NULLS LAST, datum_von NULLS LAST;";

		return $this->execQuery($qry, $params);
	}

	/**
	 * Get the Benutzerfunktion using the person_id
	 */
	public function getActiveFunctionsByPersonId($person_id)
	{
		$query = 'SELECT bf.*
					FROM public.tbl_benutzerfunktion bf
					JOIN public.tbl_benutzer b USING (uid)
            WHERE b.person_id = ?
              AND (bf.datum_von IS NULL OR bf.datum_von <= now())
              AND (bf.datum_bis IS NULL OR bf.datum_bis >= now())';

        return $this->execQuery($query, array($person_id));
	}

	/**
	 * Gets all Benutzer for a given OE and specified Benutzerfunktionen
	 * @param string $funktion_kurzbz String with one benutzerfunktionname or array with one or more.
	 * @param string $oe_kurzbz
	 * @param bool $activeoeonly If true, retrieve only active Organisationseinheiten.
	 * @param bool $activebenonly If true, retrieve only active Benutzer.
	 * @param bool $oerecursive If true, retrieve all oes under given oe_kurzbz recursively.
	 * @return array|null
	 */
	public function getBenutzerFunktionen($funktion_kurzbz, $oe_kurzbz = null, $activeoeonly = false, $activebenonly = false, $oerecursive = false)
	{
		$parametersArray = array();

		$query = "SELECT * FROM public.tbl_benutzerfunktion";

		if ($activeoeonly === true)
			$query .= " JOIN public.tbl_organisationseinheit USING(oe_kurzbz)";

		if ($activebenonly === true)
			$query .= " JOIN public.tbl_benutzer USING(uid)";

		$query .= " WHERE (datum_von <= NOW() OR datum_von IS NULL) AND (datum_bis >= NOW() OR datum_bis IS NULL)";

		if (is_string($funktion_kurzbz))
		{
			$query .= " AND funktion_kurzbz = '".$funktion_kurzbz."'";
		}
		elseif (is_array($funktion_kurzbz) && count($funktion_kurzbz) > 0)
		{
			$funktionstr = "'".implode("', '", $funktion_kurzbz)."'";
			$query .= " AND funktion_kurzbz IN (".$funktionstr.")";
		}

		if (is_string($oe_kurzbz))
		{
			if ($oerecursive === true)
			{
				$query .=
				" AND oe_kurzbz IN
				  (
					WITH RECURSIVE oes(oe_kurzbz, oe_parent_kurzbz, aktiv) as
					(
					  SELECT oe_kurzbz, oe_parent_kurzbz, aktiv FROM public.tbl_organisationseinheit
					  WHERE oe_kurzbz=?";

				if ($activeoeonly === true)
					$query .= " AND aktiv=true";

				$query .=
					  " UNION ALL
					  SELECT o.oe_kurzbz, o.oe_parent_kurzbz, o.aktiv FROM public.tbl_organisationseinheit o, oes
					  WHERE o.oe_parent_kurzbz=oes.oe_kurzbz
					)
					SELECT oe_kurzbz
					FROM oes";
				if ($activeoeonly === true)
				{
					$query .= " WHERE aktiv=true";
				}
				$query .= " GROUP BY oe_kurzbz)";
			}
			else
				$query .= " AND tbl_benutzerfunktion.oe_kurzbz = ?";

			$parametersArray[] = $oe_kurzbz;
		}

		if ($activebenonly === true)
		{
			$query .= " AND tbl_benutzer.aktiv";
		}

		if ($activeoeonly === true)
		{
			$query .= " AND tbl_organisationseinheit.aktiv";
		}

		$query .= " ORDER BY oe_kurzbz, funktion_kurzbz, uid";

		return $this->execQuery($query, $parametersArray);
	}

	/**
	 * Gets all Benutzer with details for a given Benutzerfunktion and optionally specified Oe and semester
	 * 
	 * @param string			$funktion_kurzbz
	 * @param string			$oe_kurzbz
	 * @param integer | null	$semester
	 * @return array|null
	 */
	public function getBenutzerFunktionenDetailed($funktion_kurzbz, $oe_kurzbz = null, $semester = null)
	{
		$this->addSelect($this->dbTable . '.funktion_kurzbz, ' . $this->dbTable . '.oe_kurzbz, ' . $this->dbTable . '.semester, ' . $this->dbTable . '.bezeichnung, f.beschreibung, b.uid, b.alias, b.aktiv, p.vorname, p.nachname, p.titelpre, p.titelpost, m.telefonklappe, k.kontakt, o.planbezeichnung');
		$this->addJoin('public.tbl_funktion f', 'funktion_kurzbz');
		$this->addJoin('public.tbl_benutzer b', 'uid');
		$this->addJoin('public.tbl_person p', 'person_id');
		$this->addJoin('public.tbl_mitarbeiter m', 'mitarbeiter_uid=uid', 'LEFT');
		$this->addJoin('public.tbl_kontakt k', 'k.standort_id=m.standort_id AND kontakttyp=\'telefon\'', 'LEFT');
		$this->addJoin('public.tbl_ort o', 'ort_kurzbz', 'LEFT');

		$this->addOrder('LOWER(uid)');

		$where = [$this->dbTable . '.funktion_kurzbz' => $funktion_kurzbz];
		if ($oe_kurzbz !== null)
			$where[$this->dbTable . '.oe_kurzbz'] = $oe_kurzbz;
		if ($semester !== null)
			$where[$this->dbTable . '.semester'] = $semester;

		$this->db->where('(' . $this->dbTable . '.datum_bis >= NOW() OR ' . $this->dbTable . '.datum_bis IS NULL)', NULL, FALSE);
		$this->db->where('(' . $this->dbTable . '.datum_von <= NOW() OR ' . $this->dbTable . '.datum_von IS NULL)', NULL, FALSE);

		return $this->loadWhere($where);
	}

    /**
     * Get active Studiengangsleitung(en) of the user by UID.
     * @param $uid
     */
	public function getSTGLByUID($uid)
    {
        $query = '
            SELECT
                uid,
                oe_kurzbz,
                studiengang_kz,
                typ,
                tbl_studiengang.bezeichnung
            FROM
                public.tbl_benutzerfunktion
                    JOIN public.tbl_studiengang USING (oe_kurzbz)
            WHERE
                funktion_kurzbz = \'Leitung\'
              AND (datum_von IS NULL OR datum_von <= now())
              AND (datum_bis IS NULL OR datum_bis >= now())
              AND uid = ?
            ORDER BY
                oe_kurzbz
        ';

        $parameters_array = array();
        if (is_string($uid))
        {
            $parameters_array[] = $uid;
        }

        return $this->execQuery($query, $parameters_array);
    }


	public function insertBenutzerfunktion($Json)
	{
		unset($Json['benutzerfunktion_id']);
        unset($Json['updateamum']);
        $Json['insertvon'] = getAuthUID();
        $Json['insertamum'] = $this->escape('NOW()');

		if ($Json['datum_bis']=='')
		{
			unset($Json['datum_bis']);
		}

        $result = $this->insert($Json);

        if (isError($result))
        {
            return error($result->msg, EXIT_ERROR);
        }

        $record = $this->load($result->retval);

        return $record;
	}

	function updateBenutzerfunktion($funktionJson)
    {
        $funktionJson['updatevon'] = getAuthUID();
        $funktionJson['updateamum'] = $this->escape('NOW()');

        $result = $this->update($funktionJson['benutzerfunktion_id'], $funktionJson);

        if (isError($result))
        {
            return error($result->msg, EXIT_ERROR);
        }

        $result = $this->load($funktionJson['benutzerfunktion_id']);

        return $result;
    }
	
	function deleteBenutzerfunktion($funktionJson)
    {
        $result = $this->delete($funktionJson);

        if (isError($result))
        {
            return error($result->msg, EXIT_ERROR);
        }

        return success($funktionJson);
    }


}
