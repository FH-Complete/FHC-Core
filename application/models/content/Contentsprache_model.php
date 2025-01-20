<?php
class Contentsprache_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_contentsprache';
		$this->pk = 'contentsprache_id';
	}

	/**
	 * Prueft ob der Content in der angegeben Sprache vorhanden ist
	 * 
	 * @param int				$content_id
	 * @param string			$sprache
	 * @param int | null		$version (optional)
	 * @param boolean | null	$sichtbar (optional)
	 * @return stdClass
	 */
	public function exists($content_id, $sprache, $version=null, $sichtbar=null)
	{
		$condition = ['content_id' => $content_id, 'sprache' => $sprache];

		if ($version)
			$condition['version'] = $version;

		if ($sichtbar !== null)
			$condition['sichtbar'] = $sichtbar;

		$result = $this->loadWhere($condition);
		
		if (isError($result))
			return $result;

		return success(!!getData($result));
	}
	
}
