<?php
class Lvangebot_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_lvangebot';
		$this->pk = 'lvangebot_id';
	}
	
/**
	 * Prueft ob eine Abmeldung von einer Lehrveranstaltung moeglich ist
	 * und liefert die Gruppen von denen sich abgemeldet werden kann
	 * @param $lehrveranstaltung_id
	 * @param $studiensemester_kurzbz
	 * @param $uid
	 * @return $gruppen Array mit den Gruppen
	 */
	public function AbmeldungMoeglich($lehrveranstaltung_id, $studiensemester_kurzbz, $uid)
	{
		$query = "SELECT 
					gruppe_kurzbz
				FROM 
					lehre.tbl_lvangebot
					JOIN public.tbl_benutzergruppe USING(studiensemester_kurzbz, gruppe_kurzbz)
				WHERE
					tbl_lvangebot.studiensemester_kurzbz = " . $this->escape($studiensemester_kurzbz)."
					AND tbl_benutzergruppe.uid = " . $this->escape($uid)."
					AND (tbl_lvangebot.lehrveranstaltung_id = " . $this->escape(intval($lehrveranstaltung_id))."
						OR tbl_lvangebot.lehrveranstaltung_id IN(SELECT lehrveranstaltung_id_kompatibel 
							FROM lehre.tbl_lehrveranstaltung_kompatibel 
							WHERE lehrveranstaltung_id = " . $this->escape(intval($lehrveranstaltung_id))."
							)
						)";
		$res = $this->execReadOnlyQuery($query);
		$rows = (hasData($res)) ? getData($res) : array();

		$gruppen=array();
		foreach($rows as $row)
		{
			$gruppen[] = $row->gruppe_kurzbz;
		}
		return $gruppen;
	}
}
