<?php
class Uhstat1daten_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'bis.tbl_uhstat1daten';
		$this->pk = 'uhstat1daten_id';
	}

	/**
	 * Gets person data needed for sending as UHSTAT1 data.
	 * @param array $person_id_arr
	 * @param string $studiensemester
	 * @param array $status_kurzbz
	 * @return object success with prestudents or error
	 */
	public function getUHSTAT1PersonData($person_id_arr)
	{
		if (!isset($person_id_arr) || isEmptyArray($person_id_arr)) return success([]);
		
		$params = array($person_id_arr);

		$prstQry = "SELECT
						DISTINCT ON (pers.person_id)
						pers.person_id, uhstat_daten.uhstat1daten_id, pers.svnr, pers.ersatzkennzeichen, pers.geburtsnation,
						uhstat_daten.mutter_geburtsstaat, uhstat_daten.mutter_bildungsstaat, uhstat_daten.mutter_geburtsjahr,
						uhstat_daten.mutter_bildungmax, uhstat_daten.vater_geburtsstaat, uhstat_daten.vater_bildungsstaat,
						uhstat_daten.vater_geburtsjahr, uhstat_daten.vater_bildungmax,
						kzVbpkAs.inhalt AS \"vbpkAs\", kzVbpkBf.inhalt AS \"vbpkBf\"
					FROM
						public.tbl_person pers
						JOIN public.tbl_prestudent ps USING (person_id)
						JOIN public.tbl_studiengang stg USING (studiengang_kz)
						JOIN bis.tbl_uhstat1daten uhstat_daten USING (person_id)
						LEFT JOIN public.tbl_kennzeichen kzVbpkAs ON kzVbpkAs.kennzeichentyp_kurzbz = 'vbpkAs'AND kzVbpkAs.person_id = pers.person_id AND kzVbpkAs.aktiv
						LEFT JOIN public.tbl_kennzeichen kzVbpkBf ON kzVbpkBf.kennzeichentyp_kurzbz = 'vbpkBf'AND kzVbpkBf.person_id = pers.person_id AND kzVbpkBf.aktiv
					WHERE
						ps.bismelden
						AND stg.melderelevant
						AND pers.person_id IN ?
					ORDER BY
						pers.person_id";

		return $this->execReadOnlyQuery(
			$prstQry,
			$params
		);
	}
}
