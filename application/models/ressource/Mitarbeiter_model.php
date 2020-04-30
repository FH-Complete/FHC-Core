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
	 * @param $fix wenn true werden nur fixangestellte geladen
	 * @param $aktiv wenn true werden nur aktive geladen, wenn false dann nur inaktve, wenn null dann alle
	 * @param $verwendung wenn true werden alle geladen die eine BIS-Verwendung eingetragen haben
	 * @return array
	 */
	public function getPersonal($aktiv, $fix, $verwendung)
	{
		$qry = "SELECT DISTINCT ON(mitarbeiter_uid) *,
									tbl_benutzer.aktiv as aktiv,
									tbl_mitarbeiter.insertamum,
									tbl_mitarbeiter.insertvon,
									tbl_mitarbeiter.updateamum,
									tbl_mitarbeiter.updatevon
					FROM ((public.tbl_mitarbeiter JOIN public.tbl_benutzer ON(mitarbeiter_uid=uid))
					JOIN public.tbl_person USING(person_id))
			   LEFT JOIN public.tbl_benutzerfunktion USING(uid)
				   WHERE true";

		if ($fix === true)
			$qry .= " AND fixangestellt=true";
		if ($fix === false)
			$qry .= " AND fixangestellt=false";
		if ($aktiv === true)
			$qry .= " AND tbl_benutzer.aktiv=true";
		if ($aktiv === false)
			$qry .= " AND tbl_benutzer.aktiv=false";
		if ($verwendung === true)
		{
			$qry.=" AND EXISTS(SELECT * FROM bis.tbl_bisverwendung WHERE (ende>now() or ende is null) AND tbl_bisverwendung.mitarbeiter_uid=tbl_mitarbeiter.mitarbeiter_uid)";
		}
		if ($verwendung === false)
		{
			$qry.=" AND NOT EXISTS(SELECT * FROM bis.tbl_bisverwendung WHERE (ende>now() or ende is null) AND tbl_bisverwendung.mitarbeiter_uid=tbl_mitarbeiter.mitarbeiter_uid)";
		}

		return $this->execQuery($qry);
	}
}
