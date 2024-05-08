<?php
class Paabgabe_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_paabgabe';
		$this->pk = 'paabgabe_id';
	}

	/**
	 * Gets last Endabgabe of a Projektarbeit, including filename.
	 * @param int $projektarbeit_id
	 * @return object
	 */
	public function getEndabgabe($projektarbeit_id)
	{
		$qry = "SELECT paabgabe_id, student_uid, paabg.datum, paabg.abgabedatum, projekttyp_kurzbz, titel, titel_english,
					paabgabe_id || '_' || student_uid || '.pdf' AS filename
				FROM campus.tbl_paabgabe paabg
				JOIN lehre.tbl_projektarbeit USING (projektarbeit_id)
				WHERE projektarbeit_id = ?
				AND paabgabetyp_kurzbz = 'end'
				AND paabg.abgabedatum IS NOT NULL
				ORDER BY paabg.abgabedatum, paabg.datum DESC
				LIMIT 1";

		return $this->execQuery($qry, array($projektarbeit_id));
	}
}
