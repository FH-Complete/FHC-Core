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

	/**
	 * Gets active Benutzer from person_id
	 * @param $person_id
	 * @return object
	 */
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

	/**
	 * Generates alias for a person_id
	 * @param $person_id
	 * @return string
	 */
	public function generateAliasByPersonId($person_id)
	{
		$sql = 'SELECT p.vorname, p.nachname
				FROM public.tbl_person p
				where person_id = ?';

		$nameresult = $this->execQuery($sql, array($person_id));

		$nameresult = current(getData($nameresult)) ?: null;

		if($nameresult)
		{
			$alias = $this->_sanitizeAliasName($nameresult->vorname).'.'.$this->_sanitizeAliasName($nameresult->nachname);

			$aliasexists = $this->aliasExists($alias);

			if (hasData($aliasexists) && current(getData($aliasexists)))
				$alias = "";
		}

		return success($alias);
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

	/**
	 * Generiert einen Aktivierungscode
	 */
	function generateActivationKey()
	{
		$keyvalues=array('0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F');
		$key='';
		for($i=0;$i<32;$i++)
			$key.=$keyvalues[mt_rand(0,15)];

		return success(md5(encryptData(uniqid(mt_rand(), true),$key)));
	}

	/**
	 * Check if Benutzer already exists
	 * @param String $uid
	 * @return 0 if not exists, 1 if it does
	 */
	public function checkIfExistingBenutzer($uid)
	{
		$qry = "SELECT 
    				count(*) as anzahl 
				FROM 
				    public.tbl_benutzer 
				WHERE 
				    uid = ? ";

		$result = $this->execQuery($qry, array($uid));

		if (isError($result))
		{
			return error($result);
		}

		$resultObject = current(getData($result));

		if (property_exists($resultObject, 'anzahl'))
		{
			$resultValue = (int) $resultObject->anzahl;

			if ($resultValue > 0)
			{
				return success("1");
			}
			else
			{
				return success("0");
			}
		}	
	}
}
