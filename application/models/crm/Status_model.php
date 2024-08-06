<?php
class Status_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_status';
		$this->pk = 'status_kurzbz';
	}

	public function getAllStatiWithStatusgruende()
	{
		$lang = '[(SELECT index FROM public.tbl_sprache WHERE sprache=' . $this->escape(getUserLanguage()) . ' LIMIT 1)]';

		$this->addSelect('sg.status_kurzbz');

		$this->addSelect('statusgrund_id');
		$this->addSelect('sg.bezeichnung_mehrsprachig' . $lang . ' AS bezeichnung');
		$this->addSelect('sg.beschreibung' . $lang . ' AS beschreibung');

		$this->addJoin('public.tbl_status_grund sg', 'ON (sg.status_kurzbz = public.tbl_status.status_kurzbz)', 'LEFT');

		return $this->loadWhere([
			'aktiv'=> true,
		]);
	}
}
