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
	 * Sorted like the Notenliste of the StV: the scale 1-5 by its value first, every other Note
	 * alphabetically after it.
	 *
	 * @param string|null $sortOrder 'skala' or 'bezeichnung'. The caller holds the configuration.
	 *                                Without a value the model reads NOTEN_SORTIERUNG itself, for the
	 *                                callers outside the Benotungstool.
	 */
	public function getAllActive($sortOrder = null) {
		$qry ="SELECT *
			FROM lehre.tbl_note
			WHERE aktiv = true";

		if($sortOrder === null) {
			$this->config->load('noten', FALSE, TRUE);
			$sortOrder = $this->config->item('NOTEN_SORTIERUNG');
		}

		$qry .= ($sortOrder === 'bezeichnung')
			? " ORDER BY bezeichnung"
			: " ORDER BY CASE WHEN note BETWEEN 1 AND 5 THEN 0 ELSE 1 END,
				CASE WHEN note BETWEEN 1 AND 5 THEN note END,
				bezeichnung";
		
		return $this->execReadOnlyQuery($qry);
	}
}
