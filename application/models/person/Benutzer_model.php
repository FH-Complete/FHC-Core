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
		$sql = 'SELECT b.uid
				  FROM public.tbl_benutzer b
				  JOIN public.tbl_prestudent ps USING (person_id)
				  JOIN public.tbl_studiengang sg USING (studiengang_kz)
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

		if (!isError($result))
		{
			if (hasData($result))
			{
				$result = success(array(true));
			}
			else if (!hasData($result))
			{
				$result = success(array(false));
			}
		}

		return $result;
	}

	/**
	 * Generates alias for a uid.
	 * @param $uid
	 * @return array the alias
	 */
	public function generateAlias($uid)
	{
		$aliasres = '';
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
	 * Sanitizes a string used for alias. Replaces special characters, spaces, upper case.
	 * @param string $str
	 * @return string
	 */
	private function _sanitizeAliasName($str)
	{
		$enc = 'UTF-8';

		$acentos = array(
			'A' => '/&Agrave;|&Aacute;|&Acirc;|&Atilde;|&Aring;/',
			'Ae' => '/&Auml;/',
			'a' => '/&agrave;|&aacute;|&acirc;|&atilde;|&aring;/',
			'ae'=> '/&auml;/',
			'C' => '/&Ccedil;/',
			'c' => '/&ccedil;/',
			'E' => '/&Egrave;|&Eacute;|&Ecirc;|&Euml;/',
			'e' => '/&egrave;|&eacute;|&ecirc;|&euml;/',
			'I' => '/&Igrave;|&Iacute;|&Icirc;|&Iuml;/',
			'i' => '/&igrave;|&iacute;|&icirc;|&iuml;/',
			'N' => '/&Ntilde;/',
			'n' => '/&ntilde;/',
			'O' => '/&Ograve;|&Oacute;|&Ocirc;|&Otilde;/',
			'Oe' => '/&Ouml;/',
			'o' => '/&ograve;|&oacute;|&ocirc;|&otilde;/',
			'oe' => '/&ouml;/',
			'U' => '/&Ugrave;|&Uacute;|&Ucirc;/',
			'Ue' => '/&Uuml;/',
			'u' => '/&ugrave;|&uacute;|&ucirc;/',
			'ue' => '/&uuml;/',
			'Y' => '/&Yacute;/',
			'y' => '/&yacute;|&yuml;/',
			'a.' => '/&ordf;/',
			'o.' => '/&ordm;/',
			'ss' => '/&szlig;/',
		);

		$str = preg_replace($acentos, array_keys($acentos), htmlentities($str,ENT_NOQUOTES, $enc));
		return mb_strtolower(str_replace(' ','_', $str));
	}
}
