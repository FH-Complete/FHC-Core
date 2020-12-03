<?php
class Benutzer_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_benutzer';
		$this->pk = array('uid');
		$this->hasSequence = false;
	}

	public function getFromPersonId($person_id)
	{
		return $this->loadWhere(array('person_id' => $person_id, 'aktiv' => true));
	}

	/**
	 *
	 */
	public function getActiveUserByPersonIdAndOrganisationUnit($person_id, $oe_kurzbz)
	{
		$sql = 'SELECT
					b.uid,
					b.insertamum
				FROM
					public.tbl_prestudent ps
					JOIN public.tbl_studiengang sg USING (studiengang_kz)
					JOIN public.tbl_student USING(prestudent_id)
					JOIN public.tbl_benutzer b ON(uid = student_uid)
				WHERE ps.person_id = ?
					AND sg.oe_kurzbz = ?
					AND b.aktiv = TRUE';

		return $this->execQuery($sql, array($person_id, $oe_kurzbz));
	}

	/**
	 * Checks if alias exists
	 * @param $alias
	 */
	public function aliasExists($alias)
	{
		$this->addSelect('1');
		$result = $this->loadWhere(array('alias' => $alias));

		if (isSuccess($result))
		{
			if (hasData($result))
			{
				$result = success(array(true));
			}
			else
			{
				$result = success(array(false));
			}
		}

		return $result;
	}

	/**
	 * Generates alias for a uid.
	 * @param $uid
	 * @return array the alias if newly generated
	 */
	public function generateAlias($uid)
	{
		$aliasres = '';
		$this->addLimit(1);
		$this->addSelect('vorname, nachname');
		$this->addJoin('public.tbl_person', 'person_id');
		$nameresult = $this->loadWhere(array('uid' => $uid));

		if (hasData($nameresult))
		{
			$aliasdata = getData($nameresult);
			$alias = $this->_sanitizeAliasName($aliasdata[0]->vorname).'.'.$this->_sanitizeAliasName($aliasdata[0]->nachname);
			$aliasexists = $this->aliasExists($alias);

			if (hasData($aliasexists) && !getData($aliasexists)[0])
				$aliasres = $alias;
		}
		return success($aliasres);
	}

	// --------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Sanitizes a string used for alias. Replaces special characters, spaces, sets lower case.
	 * @param string $str
	 * @return string
	 */
	private function _sanitizeAliasName($str)
	{
		$str = sanitizeProblemChars($str);
		return mb_strtolower(str_replace(' ','_', $str));
	}
}
