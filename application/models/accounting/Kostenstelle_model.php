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
	 * Gets Kostenstellen for a geschaeftsjahr, as determined by the geschaeftsjahrvon and bis fields
	 * Gets Kostenstellen of current Geschaeftsjahr if Geschaeftsjahr not specified
	 * @param $geschaeftsjahr
	 * @return array|null Kostenstellen or empty array if no geschaeftsjahr found
	 */
	public function getKostenstellenForGeschaeftsjahr($geschaeftsjahr = null)
	{
		$this->load->model('organisation/geschaeftsjahr_model', 'GeschaeftsjahrModel');

		$gj = $this->_getGeschaeftsjahr($geschaeftsjahr);

		if (hasData($gj))
		{
			$gjstart = $gj->retval[0]->start;

			$query = 'SELECT kostenstelle_id, kurzbz, wawi.tbl_kostenstelle.bezeichnung, wawi.tbl_kostenstelle.aktiv, wawi.tbl_kostenstelle.oe_kurzbz
					FROM wawi.tbl_kostenstelle 
					LEFT JOIN public.tbl_geschaeftsjahr kgjvon on wawi.tbl_kostenstelle.geschaeftsjahrvon = kgjvon.geschaeftsjahr_kurzbz
					LEFT JOIN public.tbl_geschaeftsjahr kgjbis on wawi.tbl_kostenstelle.geschaeftsjahrbis = kgjbis.geschaeftsjahr_kurzbz 
					WHERE
					(DATE ? >= kgjvon.start OR wawi.tbl_kostenstelle.geschaeftsjahrvon IS NULL)
					AND
					(DATE ? < kgjbis.ende OR wawi.tbl_kostenstelle.geschaeftsjahrbis IS NULL)
					ORDER BY wawi.tbl_kostenstelle.bezeichnung';

			return $this->execQuery($query, array($gjstart, $gjstart));
		}
		else
		{
			return success(array());
		}
	}

	/**
	 * Gets Kostenstellen for a geschaeftsjahr, as determined by the geschaeftsjahrvon and bis fields, together with their oe,
	 * hierarchally sorted, gets Kostenstellen of current Geschaeftsjahr if Geschaeftsjahr not specified
	 * @param null $geschaeftsjahr
	 * @return array|null
	 */
	public function getKostenstellenForGeschaeftsjahrWithOe($geschaeftsjahr = null)
	{
		$this->load->model('organisation/geschaeftsjahr_model', 'GeschaeftsjahrModel');

		$gj = $this->_getGeschaeftsjahr($geschaeftsjahr);

		if (hasData($gj))
		{
			$gjstart = $gj->retval[0]->start;

			$query = "WITH RECURSIVE tree (oe_kurzbz, bezeichnung, path, level, organisationseinheittyp_kurzbz) AS (
					SELECT oe_kurzbz,
							bezeichnung || ' (' || organisationseinheittyp_kurzbz || ')' AS bezeichnung,
							oe_kurzbz || '|' AS path, 0 AS level,
							organisationseinheittyp_kurzbz
					  FROM tbl_organisationseinheit
					 WHERE oe_parent_kurzbz IS NULL
					   AND aktiv = true
				 UNION ALL
					SELECT oe.oe_kurzbz,
							oe.bezeichnung || ' (' || oe.organisationseinheittyp_kurzbz || ')' AS bezeichnung,
							tree.path || oe.oe_kurzbz || '|' AS path, tree.level + 1 AS level,
							oe.organisationseinheittyp_kurzbz
					  FROM tree JOIN tbl_organisationseinheit oe ON (tree.oe_kurzbz = oe.oe_parent_kurzbz)
			)
			SELECT oe_kurzbz,
					SUBSTRING(REGEXP_REPLACE(path, '[A-z]+\|', '-', 'g') || rec.bezeichnung, 2) AS oe_description, 
					level,
					kst.kostenstelle_id as kostenstelle_id,
					kst.kurzbz as kostenstelle_kurzbz,
					kst.bezeichnung as kostenstelle_bezeichnung,
					kst.aktiv as kostenstelle_aktiv
			  FROM tree rec
				JOIN (
					SELECT kostenstelle_id, kurzbz, wawi.tbl_kostenstelle.bezeichnung, wawi.tbl_kostenstelle.aktiv, wawi.tbl_kostenstelle.oe_kurzbz as kstoe
					FROM wawi.tbl_kostenstelle 
					LEFT JOIN public.tbl_geschaeftsjahr kgjvon on wawi.tbl_kostenstelle.geschaeftsjahrvon = kgjvon.geschaeftsjahr_kurzbz
					LEFT JOIN public.tbl_geschaeftsjahr kgjbis on wawi.tbl_kostenstelle.geschaeftsjahrbis = kgjbis.geschaeftsjahr_kurzbz 
					WHERE
					(DATE ? >= kgjvon.start OR wawi.tbl_kostenstelle.geschaeftsjahrvon IS NULL)
					AND
					(DATE ? < kgjbis.ende OR wawi.tbl_kostenstelle.geschaeftsjahrbis IS NULL)
					ORDER BY wawi.tbl_kostenstelle.bezeichnung) 
				kst on kst.kstoe =  rec.oe_kurzbz
				ORDER BY level";

			return $this->execQuery($query, array($gjstart, $gjstart));
		}
		else
		{
			return success(array());
		}
	}

	/**
	 * Gets all Kostenstellen for which logged in user is berechtigt.
	 * @param null $berechtigung_kurzbz
	 * @param null $art
	 * @return array
	 */
	public function getKostenstellenBerechtigt($berechtigung_kurzbz = null, $art = null)
	{
		$allkostenstellen = $this->load();
		$kostenstellen = array();

		if (hasData($allkostenstellen))
		{
			foreach ($allkostenstellen->retval as $kostenstelle)
			{
				if ($this->permissionlib->isBerechtigt($berechtigung_kurzbz, $art, null, $kostenstelle->kostenstelle_id))
				{
					$kostenstellen[] = $kostenstelle;
				}
			}
		}

		return success($kostenstellen);
	}

	/**
	 * Gets either given Geschaeftsjahr, current Geschaeftsjahr if not given, or chronologically last Geschaeftsjahr if there is no current
	 * @param $geschaeftsjahr
	 * @return mixed
	 */
	protected function _getGeschaeftsjahr($geschaeftsjahr)
	{
		if ($geschaeftsjahr === null)
		{
			$gj = $this->GeschaeftsjahrModel->getCurrGeschaeftsjahr();
			if (!hasData($gj))
			{
				$this->GeschaeftsjahrModel->addSelect('geschaeftsjahr_kurzbz, start, ende');
				$this->GeschaeftsjahrModel->addOrder('start', 'DESC');
				$gj = $this->GeschaeftsjahrModel->load();
			}
		}
		else
		{
			$this->GeschaeftsjahrModel->addSelect('geschaeftsjahr_kurzbz, start, ende');
			$gj = $this->GeschaeftsjahrModel->load($geschaeftsjahr);
		}
		return $gj;
	}
}
