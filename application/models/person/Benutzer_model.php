<?php

use \CI3_Events as Events;

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
		$this->addLimit(1);
		$this->addSelect('vorname, nachname');
		$this->addJoin('public.tbl_person', 'person_id');
		$nameresult = $this->loadWhere(array('uid' => $uid));

		if (isError($nameresult))
			return $nameresult;

		if (!hasData($nameresult))
			return success('');
		
		$aliasdata = current(getData($nameresult));

		return $this->generateAliasFromName($aliasdata->vorname, $aliasdata->nachname);
	}

	/**
	 * Generates alias for a vor- and nachname.
	 *
	 * @param string						$vorname
	 * @param string						$nachname
	 *
	 * @return stdClass
	 */
	public function generateAliasFromName($vorname, $nachname)
	{
		$alias = $this->_sanitizeAliasName($vorname . '.' . $nachname);
		
		$result = $this->aliasExists($alias);

		if (isError($result))
			return $result;

		if (current(getData($result)))
			return success('');

		return success($alias);
	}

	/**
	 * Generates a matrikelnummer
	 *
	 * @param string						$oe_kurzbz
	 *
	 * @return stdClass
	 */
	public function generateMatrikelnummer($oe_kurzbz)
	{
		$matrikelnummer = false;

		Events::trigger(
			'generate_matrikelnummer',
			function ($value) use ($matrikelnummer) {
				$matrikelnummer = $value;
			},
			$oe_kurzbz
		);

		if ($matrikelnummer !== false)
			return success($matrikelnummer);

		return success(null);
	}

	/**
	 * Generates an activation key
	 *
	 * @return string
	 */
	public function generateActivationkey()
	{
		$this->load->library('CryptLib');

		$key = '';
		for ($i=0; $i<32; $i++)
			$key .= ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F'][mt_rand(0, 15)];

		$value = uniqid(mt_rand(), true);
		$length = strlen($value);
		$value = str_pad($value, $length + 32 - ($length % 32), chr(0));

		return md5($this->cryptlib->RIJNDAEL_256_ECB($value, $key, true));
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
		return mb_strtolower(str_replace(' ', '_', $str));
	}
}
