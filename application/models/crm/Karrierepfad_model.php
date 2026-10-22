<?php

/**
 * Karrierepfad: marketing specialisation of a Studiengang, linked to Studienpläne.
 * Not official: never print it on a Zeugnis or report it to BIS.
 */
class Karrierepfad_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_karrierepfad';
		$this->pk = 'karrierepfad_id';
	}

	/**
	 * Gets the Karrierepfade linked to a Studienplan, with the Bezeichnung in the user language.
	 * A Karrierepfad is selectable for a prestudent if it is aktiv and linked to the
	 * Studienplan of the last status. A value stored earlier stays valid without this check.
	 *
	 * @param integer		$studienplan_id
	 * @param boolean		$aktiv				true: only active Karrierepfade
	 *
	 * @return stdClass
	 */
	public function getByStudienplan($studienplan_id, $aktiv = true)
	{
		$query = "
			SELECT
				kp.*,
				kp.bezeichnung_mehrsprachig[(SELECT index FROM public.tbl_sprache WHERE sprache = ? LIMIT 1)] AS bezeichnung
			FROM public.tbl_karrierepfad kp
			JOIN public.tbl_karrierepfad_studienplan USING (karrierepfad_id)
			WHERE studienplan_id = ?";
		if ($aktiv)
			$query .= " AND kp.aktiv";
		$query .= " ORDER BY kp.sort NULLS LAST, kp.karrierepfad_kurzbz";

		return $this->execQuery($query, array(getUserLanguage(), $studienplan_id));
	}
}
