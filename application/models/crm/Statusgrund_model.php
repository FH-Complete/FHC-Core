<?php

class Statusgrund_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = "public.tbl_status_grund";
		$this->pk = "statusgrund_id";
	}

	public function getStatus($status_kurzbz = null, $aktiv = null, $statusgrund_kurzbz = null)
	{
		$this->addOrder('bezeichnung_mehrsprachig');
		$where = array();

		if (!is_null($status_kurzbz))
		{
			$this->addJoin('public.tbl_status_grund_status', 'statusgrund_id');
			$where['tbl_status_grund_status.status_kurzbz'] = $status_kurzbz;
		}
		if (!is_null($aktiv))
			$where['aktiv'] = $aktiv;
		if (!is_null($statusgrund_kurzbz))
			$where['statusgrund_kurzbz'] = $statusgrund_kurzbz;

		$status = $this->loadWhere($where);

		return success($status->retval);
	}

	public function getAktiveGruende()
	{
		$lang = '[(SELECT index FROM public.tbl_sprache WHERE sprache=' . $this->escape(getUserLanguage()) . ' LIMIT 1)]';

		$this->addSelect('tbl_status_grund.*, tbl_status_grund_status.status_kurzbz');

		$this->addSelect('bezeichnung_mehrsprachig' . $lang . ' AS bezeichnung');
		$this->addSelect('beschreibung' . $lang . ' AS beschreibung');
		$this->addJoin('public.tbl_status_grund_status', 'statusgrund_id');

		$this->addOrder('bezeichnung_mehrsprachig' . $lang);

		return $this->loadWhere([
			'aktiv' => true
		]);
	}
}
