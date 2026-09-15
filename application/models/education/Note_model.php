<?php
class Note_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_note';
		$this->pk = 'note';
	}
	
	public function getAllActive() {
		$qry ="SELECT *
			FROM lehre.tbl_note
			WHERE aktiv = true";
		
		return $this->execReadOnlyQuery($qry);
	}
	
	// used to determine the primary key of note "entschuldigt" to avoid hardcoded magic numbers
	// that might differ in a different installation of fhcomplete
	public function getEntschuldigtNote() {
		$qry ="SELECT *
			FROM lehre.tbl_note
			WHERE bezeichnung = 'entschuldigt'";

		return $this->execReadOnlyQuery($qry);
	}

	// used to determine the primary key of note "noch nicht eingetragen" to avoid hardcoded magic numbers
	// that might differ in a different installation of fhcomplete
	public function getNochNichtEingetragenNote() {
		$qry ="SELECT *
			FROM lehre.tbl_note
			WHERE bezeichnung = 'Noch nicht eingetragen'";

		return $this->execReadOnlyQuery($qry);
	}
	
}