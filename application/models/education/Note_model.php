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
	
	/**
	 * Sorted like the grade list of the Stv: scale 1-5 by its value first, every other grade
	 * alphabetically after it.
	 *
	 * @param string|null $sortierung 'skala' or 'bezeichnung'. The caller holds the configuration.
	 *                                Without a value the model reads NOTEN_SORTIERUNG itself, for the
	 *                                callers outside the Benotungstool.
	 */
	public function getAllActive($sortierung = null) {
		$qry ="SELECT *
			FROM lehre.tbl_note
			WHERE aktiv = true";

		if($sortierung === null) {
			// flat, like every other consumer of this file. A sectioned load does not work here: the
			// controller loads the same file flat, and the second load then returns early.
			$this->config->load('noten', FALSE, TRUE);
			$sortierung = $this->config->item('NOTEN_SORTIERUNG');
		}

		$qry .= ($sortierung === 'bezeichnung')
			? " ORDER BY bezeichnung"
			: " ORDER BY CASE WHEN note BETWEEN 1 AND 5 THEN 0 ELSE 1 END,
				CASE WHEN note BETWEEN 1 AND 5 THEN note END,
				bezeichnung";
		
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