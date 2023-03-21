<?php
class Projektbetreuer_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_projektbetreuer';
		$this->pk = array('betreuerart_kurzbz', 'projektarbeit_id', 'person_id');
	}

    /**
     * Checks if Projektauftrag has a contract.
     * @param $person_id
     * @param $projektarbeit_id
     * @return array|bool|int       Returns vertrag_id if contract exists. False if doesnt exist. On error array.
     */
    public function hasVertrag($person_id, $projektarbeit_id)
    {
        if (is_numeric($person_id) && is_numeric($projektarbeit_id))
        {
            $result = $this->load(array(
                'person_id' => $person_id,
                'projektarbeit_id' => $projektarbeit_id
            ));

            if (hasData($result))
            {
                return (is_null($result->retval[0]->vertrag_id)) ? false : intval($result->retval[0]->vertrag_id);
            }
            else
            {
                return error($result->msg, EXIT_ERROR);
            }
        }
        else
        {
            return error ('Incorrect parameter type');
        }
    }

	/**
	 * Gets Betreuer of a certain Type of a Projektarbeit.
	 * Returns one row for each person.
	 * @param int $projektarbeit_id
	 * @param string $betreuerart_kurzbz
	 * @return array success with number of Betreuer or error
	 */
	public function getBetreuerOfProjektarbeit($projektarbeit_id, $betreuerart_kurzbz)
	{
		$qry = "SELECT DISTINCT ON (pers.person_id) pers.person_id, betreuerart_kurzbz, vorname, nachname,
				trim(COALESCE(titelpre,'')||' '||COALESCE(vorname,'')||' '||COALESCE(nachname,'')||' '||COALESCE(titelpost,'')) as voller_name,
				anrede, titelpre, titelpost, gebdatum, geschlecht, pa.projekttyp_kurzbz,
				ben.uid, ben.alias, ma.personalnummer, mitarbeiter_uid, student_uid,
				(
					SELECT kontakt
					FROM public.tbl_kontakt
					WHERE kontakttyp = 'email'
					AND person_id = pers.person_id
					ORDER BY
						CASE WHEN zustellung THEN 0 ELSE 1 END,
						insertamum DESC NULLS LAST
					LIMIT 1
				) AS private_email
				FROM lehre.tbl_projektarbeit pa
				JOIN lehre.tbl_projektbetreuer USING (projektarbeit_id)
				JOIN public.tbl_person pers USING (person_id)
				LEFT JOIN public.tbl_benutzer ben USING (person_id)
				LEFT JOIN public.tbl_mitarbeiter ma ON ben.uid = ma.mitarbeiter_uid
				WHERE (ben.aktiv OR ben.aktiv IS NULL)
				AND projektarbeit_id = ?
				AND betreuerart_kurzbz = ?
				ORDER BY pers.person_id, CASE WHEN ma.mitarbeiter_uid IS NULL THEN 1 ELSE 0 END, /*Mitarbeiter account first*/
						CASE WHEN ben.uid IS NULL THEN 1 ELSE 0 END, /*user with account first*/
						ben.insertamum";

		return $this->execQuery($qry, array($projektarbeit_id, $betreuerart_kurzbz));
	}

	/**
	 * Get Projektbetreuer data by authentification token
	 * @param $zugangstoken
	 * @return object
	 */
    public function getBetreuerByToken($zugangstoken)
	{
		$qry = "
			SELECT tbl_projektbetreuer.person_id, tbl_projektbetreuer.projektarbeit_id, student_uid
			FROM lehre.tbl_projektbetreuer
			JOIN lehre.tbl_projektarbeit USING (projektarbeit_id)
			WHERE zugangstoken = ? AND zugangstoken_gueltigbis >= NOW()
			ORDER BY tbl_projektbetreuer.insertamum DESC, projektarbeit_id DESC
			LIMIT 1
		";

		return $this->execQuery($qry, array($zugangstoken));
	}

	/**
	 * Holt Zweitbegutachter einer Projektarbeit mit Mail.
	 * @param $erstbegutachter_person_id int person_id des Erstbegutachters
	 * @param $projektarbeit_id int
	 * @param $student_uid string uid des Studenten der Arbeit abgibt
	 * @return object | bool
	 */
	public function getZweitbegutachterWithToken($erstbegutachter_person_id, $projektarbeit_id, $student_uid, $zweitbegutachter_person_id = null)
	{
		$params = array($erstbegutachter_person_id, $erstbegutachter_person_id, $projektarbeit_id, $student_uid);

		$qry_betr = "SELECT betr.person_id, betr.projektarbeit_id, pers.anrede, betr.zugangstoken, betr.zugangstoken_gueltigbis, tbl_benutzer.uid,
					trim(COALESCE(titelpre,'')||' '||COALESCE(vorname,'')||' '||COALESCE(nachname,'')||' '||COALESCE(titelpost,'')) as voller_name,
					CASE WHEN tbl_benutzer.uid IS NULL THEN kontakt ELSE tbl_benutzer.uid || '@".DOMAIN."' END AS email, kontakt,
					abg.abgabedatum, betr.betreuerart_kurzbz
					FROM lehre.tbl_projektbetreuer betr
					JOIN lehre.tbl_projektarbeit parb ON betr.projektarbeit_id = parb.projektarbeit_id
					JOIN public.tbl_person pers ON betr.person_id = pers.person_id
					LEFT JOIN public.tbl_kontakt ON pers.person_id = tbl_kontakt.person_id AND kontakttyp = 'email' AND zustellung = true
					LEFT JOIN public.tbl_benutzer ON pers.person_id = tbl_benutzer.person_id
					LEFT JOIN campus.tbl_paabgabe abg ON betr.projektarbeit_id = abg.projektarbeit_id AND abg.paabgabetyp_kurzbz = 'end'
					WHERE
					(
						(
							betr.betreuerart_kurzbz  = 'Zweitbegutachter'
							AND EXISTS (
								SELECT 1 FROM lehre.tbl_projektbetreuer
								WHERE person_id = ?
								AND betreuerart_kurzbz = 'Erstbegutachter'
								AND projektarbeit_id = betr.projektarbeit_id
							)
						)
						OR /* either Zweitbegutachter of masterarbeit, or Kommissionsprüfer if Kommission */
						(
							betr.betreuerart_kurzbz  = 'Senatsmitglied'
							AND EXISTS (
								SELECT 1 FROM lehre.tbl_projektbetreuer
								WHERE person_id = ?
								AND betreuerart_kurzbz = 'Senatsvorsitz'
								AND projektarbeit_id = betr.projektarbeit_id
							)
						)
					)
					AND betr.projektarbeit_id = ?
					AND parb.student_uid = ?
					AND (tbl_benutzer.aktiv OR tbl_benutzer.aktiv IS NULL)";

					if (isset($zweitbegutachter_person_id))
					{
						$qry_betr .= " AND betr.person_id = ?";
						$params[] = $zweitbegutachter_person_id;
					}

					$qry_betr .= " ORDER BY betr.person_id DESC,
					(CASE WHEN EXISTS ( /* if multiple accounts, prioritize mitarbeiter */
						SELECT 1 FROM public.tbl_mitarbeiter ma
						WHERE ma.mitarbeiter_uid = tbl_benutzer.uid
					) THEN 0 ELSE 1 END), betr.insertamum DESC
					LIMIT 1";

		return $this->execQuery($qry_betr, $params);
	}

	/**
	 * Generiert neuen Token für externen Zweitbetreuer.
	 * @param int $zweitbegutachter_person_id
	 * @param int $projektarbeit_id
	 * @return array
	 */
	public function generateZweitbegutachterToken($zweitbegutachter_person_id, $projektarbeit_id)
	{
		$betreuerUidQry = "SELECT uid, zugangstoken, zugangstoken_gueltigbis, tbl_projektbetreuer.person_id, betreuerart_kurzbz
							FROM lehre.tbl_projektbetreuer
							JOIN public.tbl_person USING(person_id)
							LEFT JOIN public.tbl_benutzer USING(person_id)
							WHERE projektarbeit_id = ?
							AND tbl_projektbetreuer.person_id = ?
							AND betreuerart_kurzbz IN ('Zweitbegutachter', 'Senatsmitglied')
							LIMIT 1";

		$betreueruidRes = $this->execQuery($betreuerUidQry, array($projektarbeit_id, $zweitbegutachter_person_id));

		if (!hasData($betreueruidRes))
			return error('Zweitbegutachter nicht gefunden');

		$zweitbetreuer = getData($betreueruidRes)[0];

		if (!isset($zweitbetreuer->uid))
		{
			do {
				$token = generateToken(16);
				$qry_tokencheck = $this->load(array('zugangstoken' => $token));
			} while(hasData($qry_tokencheck));

			$result = $this->update(
				array('projektarbeit_id' => $projektarbeit_id,
					'person_id' => $zweitbetreuer->person_id,
					'betreuerart_kurzbz' => $zweitbetreuer->betreuerart_kurzbz),
				array('zugangstoken' => $token,
					'zugangstoken_gueltigbis' => date('Y-m-d', strtotime('+1 year')))
			);

			return $result;
		}
		else
			return success("Account vorhanden, kein Token benötigt");
	}

	/**
	 * Gets betreuerart of a Betreuer for a Projektarbeit.
	 * Main Betreuer are prioritized (normally one Betreuer should be assigned to a Projektarbeit another time with a different Betreuerart).
	 * @param int projektarbeit_id
	 * @param int betreuer_person_id
	 * @return object success or error
	 */
	public function getBetreuerart($projektarbeit_id, $betreuer_person_id)
	{
		$qry = "SELECT betreuerart_kurzbz
				FROM lehre.tbl_projektbetreuer
				WHERE projektarbeit_id = ?
				AND person_id = ?
				ORDER BY CASE WHEN betreuerart_kurzbz = 'Senatsvorsitz' THEN 1 /*Senatsvorsitz has priority*/
					WHEN betreuerart_kurzbz = 'Begutachter' THEN 2
					WHEN betreuerart_kurzbz = 'Erstbegutachter' THEN 3
					WHEN betreuerart_kurzbz = 'Zweitbegutachter' THEN 4
					WHEN betreuerart_kurzbz = 'Senatsmitglied' THEN 5
					ELSE 5
				END, insertamum DESC
				LIMIT 1";

		return $this->execQuery($qry, array($projektarbeit_id, $betreuer_person_id));
	}
}
