<?php
class Contentgruppe_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_contentgruppe';
		$this->pk = array('gruppe_kurzbz', 'content_id');
	}

	/**
	 * Prueft ob der Zugriff auf den Content eingeschraenkt ist auf
	 * eine bestimmte Benutzergruppe
	 * 
	 * @param int			$content_id
	 * 
	 * @return stdClass		success(true) wenn eingeschraenkt sonst success(false)
	 */
	public function islocked($content_id)
	{
		$islocked = $this->loadWhere(['content_id' => $content_id]);

		if (isError($islocked))
			return $islocked;
		return success(!!getData($islocked));
	}
	
	/**
	 * Prueft ob ein User die Berechtigung fuer das Anzeigen des 
	 * Contents besitzt
	 * 
	 * @param int			$content_id	ID des Contents
	 * @param string		$uid		User der versucht auf den Content zuzugreifen
	 * 
	 * @return stdClass
	 */
	public function berechtigt($content_id, $uid)
	{
		$islocked = $this->islocked($content_id);
		if (isError($islocked))
			return $islocked;
		
		$condition = ['uid' => $uid];
		if (getData($islocked)) {
			$condition['content_id'] = $content_id;
		}
		$this->addJoin('public.vw_gruppen', 'gruppe_kurzbz');

		$result = $this->loadWhere($condition);

		if (isError($result))
			return $result;
		return success(!!getData($result));
	}
	
}
