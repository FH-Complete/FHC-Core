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
				ben.uid, ben.alias, ma.personalnummer, mitarbeiter_uid, student_uid
			FROM lehre.tbl_projektarbeit pa
			JOIN lehre.tbl_projektbetreuer USING (projektarbeit_id)
			JOIN public.tbl_person pers USING (person_id)
			LEFT JOIN public.tbl_benutzer ben USING (person_id)
			LEFT JOIN public.tbl_mitarbeiter ma ON ben.uid =  ma.mitarbeiter_uid
			WHERE ben.aktiv
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
		$qry = '
			SELECT tbl_projektbetreuer.person_id, tbl_projektbetreuer.projektarbeit_id, student_uid
			FROM lehre.tbl_projektbetreuer
			JOIN lehre.tbl_projektarbeit USING (projektarbeit_id)
			WHERE zugangstoken = ? AND zugangstoken_gueltigbis >= NOW()
			ORDER BY tbl_projektbetreuer.insertamum DESC, projektarbeit_id DESC
			LIMIT 1
		';

		return $this->execQuery($qry, array($zugangstoken));
	}

	/**
	 * Holt Zweitbegutachter einer Projektarbeit mit Mail.
	 * @param $erstbegutachter_person_id int person_id des Erstbegutachters
	 * @param $projektarbeit_id int
	 * @param $student_uid string uid des Studenten der Arbeit abgibt
	 * @return object | bool
	 */
	public function getZweitbegutachterWithToken($erstbegutachter_person_id, $projektarbeit_id, $student_uid)
	{
		$qry_betr = "SELECT betr.person_id, betr.projektarbeit_id, pers.anrede, betr.zugangstoken, betr.zugangstoken_gueltigbis, tbl_benutzer.uid, kontakt,
				trim(COALESCE(titelpre,'')||' '||COALESCE(vorname,'')||' '||COALESCE(nachname,'')||' '||COALESCE(titelpost,'')) as voller_name,
				CASE WHEN tbl_benutzer.uid IS NULL THEN kontakt ELSE tbl_benutzer.uid || '@".DOMAIN."' END AS email, abg.abgabedatum
				FROM lehre.tbl_projektbetreuer betr
				JOIN lehre.tbl_projektarbeit parb ON betr.projektarbeit_id = parb.projektarbeit_id 
				JOIN public.tbl_person pers ON betr.person_id = pers.person_id
				LEFT JOIN public.tbl_kontakt ON pers.person_id = tbl_kontakt.person_id AND kontakttyp = 'email' AND zustellung = true
				LEFT JOIN public.tbl_benutzer ON pers.person_id = tbl_benutzer.person_id
				LEFT JOIN campus.tbl_paabgabe abg ON betr.projektarbeit_id = abg.projektarbeit_id AND abg.paabgabetyp_kurzbz = 'end'
				WHERE betr.betreuerart_kurzbz = 'Zweitbegutachter'
				AND betr.projektarbeit_id = ?
				AND parb.student_uid = ?
				AND EXISTS (
					SELECT 1 FROM lehre.tbl_projektbetreuer
					WHERE person_id = ?
					AND betreuerart_kurzbz = 'Erstbegutachter'
					AND projektarbeit_id = betr.projektarbeit_id
				)
				AND (tbl_benutzer.aktiv OR tbl_benutzer.aktiv IS NULL)
				ORDER BY betr.insertamum DESC
				LIMIT 1";

		return $this->execQuery($qry_betr, array($projektarbeit_id, $student_uid, $erstbegutachter_person_id));
	}

	/**
	 * Generiert neuen Token für externen Zweitbetreuer.
	 * @param int $zweitbegutachter_person_id
	 * @param int $projektarbeit_id
	 * @return array
	 */
	public function generateZweitbegutachterToken($zweitbegutachter_person_id, $projektarbeit_id)
	{
		$betreuerUidQry = "SELECT uid, zugangstoken, zugangstoken_gueltigbis, tbl_projektbetreuer.person_id
							FROM lehre.tbl_projektbetreuer
							JOIN public.tbl_person USING(person_id)
							LEFT JOIN public.tbl_benutzer USING(person_id)
							WHERE projektarbeit_id = ?
							AND tbl_projektbetreuer.person_id = ?
							AND betreuerart_kurzbz = 'Zweitbegutachter'
							LIMIT 1";

		$betreueruidres = $this->execQuery($betreuerUidQry, array($projektarbeit_id, $zweitbegutachter_person_id));

		if (!hasData($betreueruidres))
			return error('Zweitbegutachter nicht gefunden');

		$row_betr = getData($betreueruidres)[0];

		if (!isset($row_betr->uid))
		{
			do {
				$token = generateToken(16);
				$qry_tokencheck = $this->load(array('zugangstoken' => $token));
			} while(hasData($qry_tokencheck));

			$result = $this->update(
				array('projektarbeit_id' => $projektarbeit_id,
					'person_id' => $row_betr->person_id,
					'betreuerart_kurzbz' => 'Zweitbegutachter'),
				array('zugangstoken' => $token,
					'zugangstoken_gueltigbis' => date('Y-m-d', strtotime('+1 year')))
			);

			return $result;
		}
		else
			return success("Account vorhanden, kein Token benötigt");
	}
}
