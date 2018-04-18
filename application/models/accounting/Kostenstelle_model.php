<?php
class Kostenstelle_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'wawi.tbl_kostenstelle';
		$this->pk = 'kostenstelle_id';
	}

	/**
	 * Gets all active Kostenstellen for a geschaeftsjahr, as determined by the geschaeftsjahrvon and bis fields
	 * Gets Kostenstellen of current Geschaeftsjahr if Geschaeftsjahr not specified
	 * Only the Kostenstellen for which a permission exists are returned!
	 * @param $geschaeftsjahr
	 * @return array|null
	 */
	public function getActiveKostenstellenForGeschaeftsjahr($geschaeftsjahr = null)
	{
		$this->load->model('organisation/geschaeftsjahr_model', 'GeschaeftsjahrModel');

		if ($geschaeftsjahr === null)
		{
			$lgj = $this->GeschaeftsjahrModel->getCurrGeschaeftsjahr();

			if ($lgj->error)
				return error($lgj->retval);

			if (count($lgj->retval) < 1)
				return success(array());

			$geschaeftsjahr = $lgj->retval[0]->geschaeftsjahr_kurzbz;
		}

		$this->GeschaeftsjahrModel->addSelect('start, ende');
		$gj = $this->GeschaeftsjahrModel->load($geschaeftsjahr);

		if ($gj->error)
			return error($gj->retval);

		if (count($gj->retval) < 1)
			return success(array());

		$gjstart = $gj->retval[0]->start;

		$query = 'SELECT kostenstelle_id, kurzbz, wawi.tbl_kostenstelle.bezeichnung, wawi.tbl_kostenstelle.aktiv
					FROM wawi.tbl_kostenstelle 
					LEFT JOIN public.tbl_geschaeftsjahr kgjvon on wawi.tbl_kostenstelle.geschaeftsjahrvon = kgjvon.geschaeftsjahr_kurzbz
					LEFT JOIN public.tbl_geschaeftsjahr kgjbis on wawi.tbl_kostenstelle.geschaeftsjahrbis = kgjbis.geschaeftsjahr_kurzbz 
					WHERE
					(DATE ? >= kgjvon.start OR wawi.tbl_kostenstelle.geschaeftsjahrvon IS NULL)
					AND
					(DATE ? < kgjbis.ende OR wawi.tbl_kostenstelle.geschaeftsjahrbis IS NULL)
					ORDER BY wawi.tbl_kostenstelle.bezeichnung';

		$kostenstellen = $this->execQuery($query, array($gjstart, $gjstart));

		if ($kostenstellen->error)
			return error($kostenstellen->retval);

		$this->load->library('PermissionLib');

		$kostenstellenresult = array();

		//filter kostenstellen, only kostenstellen for which berechtigt
		foreach ($kostenstellen->retval as $kostenstelle)
		{
			if ($this->permissionlib->isBerechtigt('extension/budget_verwaltung', 'suid', null, $kostenstelle->kostenstelle_id) === true)
			{
				$kostenstellenresult[] = $kostenstelle;
			}
		}

		return success($kostenstellenresult);
	}
}
