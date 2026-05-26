<?php
class Fehlerzustaendigkeiten_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'system.tbl_fehler_zustaendigkeiten';
		$this->pk = 'fehlerzustaendigkeiten_id';
	}

	/**
	 * Get all Fehler for which user is hauptzuständig (is in charge).
	 * @return object success or error
	 */
	public function getFehlerForUserHauptzustaendig()
	{
		$params = [getAuthPersonId()];

		$this->load->model('person/Benutzerfunktion_model', 'BenutzerfunktionModel');

		// get oes of uid for which there is a current funktion
		$all_funktionen_oe_kurzbz = array();
		$benutzerfunktionRes = $this->BenutzerfunktionModel->getBenutzerFunktionByUid(getAuthUID(), null, date('Y-m-d'), date('Y-m-d'));

		if (isError($benutzerfunktionRes)) return $benutzerfunktionRes;

		if (hasData($benutzerfunktionRes))
		{
			foreach (getData($benutzerfunktionRes) as $benutzerfunktion)
			{
				$all_funktionen_oe_kurzbz[$benutzerfunktion->oe_kurzbz][] = $benutzerfunktion->funktion_kurzbz;
			}
		}

		$query = "WITH zustaendigkeiten AS (
					SELECT fehlercode,
						CASE
							WHEN zst.person_id = ?";

		if (!isEmptyArray($all_funktionen_oe_kurzbz))
		{
			$params[] = array_keys($all_funktionen_oe_kurzbz);
			$query .= " OR (zst.oe_kurzbz IN ? AND zst.funktion_kurzbz IS NULL)  /* if oe is specified in fehler_zustaendigkeiten */";

			// check for each oe for each function if zustaendig
			foreach ($all_funktionen_oe_kurzbz as $oe_kurzbz => $funktionen_kurzbz)
			{
				foreach ($funktionen_kurzbz as $funktion_kurzbz)
				{
					$query .= " OR (zst.oe_kurzbz = '$oe_kurzbz' AND zst.funktion_kurzbz = '$funktion_kurzbz')";
				}
			}
		}

		$query .= " THEN TRUE
					ELSE FALSE
				END AS \"zustaendig\"
			FROM system.tbl_fehler_zustaendigkeiten zst
		)
		SELECT
			fehler.fehler_kurzbz, fehler.fehlercode
		FROM
			system.tbl_fehler fehler
			LEFT JOIN zustaendigkeiten USING(fehlercode)
		WHERE
			zustaendigkeiten.fehlercode IS NULL
			OR zustaendigkeiten.zustaendig = TRUE";

		return $this->execReadOnlyQuery($query, $params);
	}

	/**
	 * Gets active Mitarbeiter not assigned to a Fehler.
	 * @param $fehlercode
	 * @return object
	 */
	public function getNonAssignedMitarbeiter($fehlercode)
	{
		$query = "SELECT person_id, ben.uid, vorname, nachname, titelpre, titelpost, personalnummer
					FROM public.tbl_mitarbeiter
					JOIN public.tbl_benutzer ben ON tbl_mitarbeiter.mitarbeiter_uid = ben.uid
					JOIN public.tbl_person pers USING (person_id)
					WHERE ben.aktiv
					AND NOT EXISTS (
					    SELECT 1 FROM system.tbl_fehler_zustaendigkeiten
					    WHERE person_id = pers.person_id
					      AND fehlercode = ?
					)
					ORDER BY nachname, vorname, uid";

		return $this->execReadOnlyQuery($query, array($fehlercode));
	}

	/**
	 * Gets Funktionen not assigned to a Fehler (over an organisational unit).
	 * @param $fehlercode
	 * @param $oe_kurzbz
	 * @return object
	 */
	public function getNonAssignedFunktionen($fehlercode, $oe_kurzbz)
	{
		$query = "SELECT funktion_kurzbz, beschreibung
					FROM public.tbl_funktion funk
					WHERE aktiv
					AND NOT EXISTS (
					    SELECT 1 FROM system.tbl_fehler_zustaendigkeiten
					    WHERE funktion_kurzbz = funk.funktion_kurzbz
					    AND fehlercode = ?
					    AND oe_kurzbz = ?
					)
					ORDER BY beschreibung";

		return $this->execReadOnlyQuery($query, array($fehlercode, $oe_kurzbz));
	}
}
