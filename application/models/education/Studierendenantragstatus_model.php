<?php
class Studierendenantragstatus_model extends DB_Model
{

	const STATUS_CREATED = 'Erstellt';
	const STATUS_APPROVED = 'Genehmigt';
	const STATUS_REJECTED = 'Abgelehnt';
	const STATUS_PASS = 'Verzichtet';
	const STATUS_REOPENED = 'Offen';
	const STATUS_CANCELLED = 'Zurueckgezogen';
	const STATUS_LVSASSIGNED = 'Lvszugewiesen';
	const STATUS_REMINDERSENT = 'EmailVersandt';
	const STATUS_REQUESTSENT_1 = 'ErsteAufforderungVersandt';
	const STATUS_REQUESTSENT_2 = 'ZweiteAufforderungVersandt';
	const STATUS_OBJECTED = 'Beeinsprucht';
	const STATUS_OBJECTION_DENIED = 'EinspruchAbgelehnt';
	const STATUS_DEREGISTERED = 'Abgemeldet';

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_studierendenantrag_status';
		$this->pk = 'studierendenantrag_status_id';
	}

	public function loadWithTyp($studierendenantrag_status_id)
	{
		$lang = 'SELECT index FROM public.tbl_sprache WHERE sprache=' . $this->escape(getUserLanguage());

		$this->addSelect($this->dbTable . '.*');
		$this->addSelect('bezeichnung[(' . $lang . ')] AS typ');

		$this->addJoin('campus.tbl_studierendenantrag_statustyp', 'studierendenantrag_statustyp_kurzbz');

		return $this->load($studierendenantrag_status_id);
	}

	public function loadWithTypWhere($where)
	{
		$lang = 'SELECT index FROM public.tbl_sprache WHERE sprache=' . $this->escape(getUserLanguage());

		$this->addSelect($this->dbTable . '.*');
		$this->addSelect('bezeichnung[(' . $lang . ')] AS typ');

		$this->addJoin('campus.tbl_studierendenantrag_statustyp', 'studierendenantrag_statustyp_kurzbz');

		return $this->loadWhere($where);
	}
}
