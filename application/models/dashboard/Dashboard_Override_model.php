<?php
class Dashboard_Override_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'dashboard.tbl_dashboard_benutzer_override';
		$this->pk = 'override_id';
	}


	/**
	 * Get Overrides of given uid.
	 * @param integer dashboard_id
	 * @param string $uid
	 * @return array
	 */
	public function getOverride($dashboard_id, $uid)
	{
		return $this->loadWhere(array('dashboard_id' => $dashboard_id, 'uid'=> $uid));
	}

	/**
	 * Get Overrides of given uid and funktion_kurzbz and widget_id
	 * @param integer dashboard_id
	 * @param string $uid
	 * @return array
	 */
	//TODO(MANU) move to bookmark model
	public function getTagFilter($widgetId, $uid, $funktion_kurzbz)
	{
	//	$qry = "SELECT override -> ? -> 'widgets' -> ? -> 'tags' AS tags FROM dashboard.tbl_dashboard_benutzer_override WHERE uid = ?";

	//	$qry = " SELECT override -> '" . $funktion_kurzbz . "'-> 'widgets'-> '" . $widgetId . "'-> 'tags' AS tags FROM dashboard.tbl_dashboard_benutzer_override WHERE uid = '" . $uid . "';";

		//unter config
		$qry = " SELECT override -> '" . $funktion_kurzbz . "'-> 'widgets'-> '" . $widgetId . "'-> 'config' -> 'tags' AS tags FROM dashboard.tbl_dashboard_benutzer_override WHERE uid = '" . $uid . "';";
/*		$qry = <<<SQL
			SELECT
			  override -> ?
					   -> 'widgets'
					   -> ?
					   -> 'tags' AS tags
			FROM dashboard.tbl_dashboard_benutzer_override
			WHERE uid = ?
			SQL;*/

		//return $qry;

		return $this->execQuery($qry);
	//	return $this->execQuery($qry, array($funktion_kurzbz, $widgetId, $uid));
	}

	//TODO(MANU) move to bookmark model
	public function addTagFilter($widgetId, $uid, $funktion_kurzbz, $tags)
	{
		if (is_array($tags)) {
			$tags = json_encode($tags); // convert PHP array to JSON string
		}
		$params = [$funktion_kurzbz, $widgetId, $tags, $uid];

		$qry = "
				UPDATE dashboard.tbl_dashboard_benutzer_override
				SET override = jsonb_set(
					COALESCE(override, '{}'::jsonb),
					format('{%s,widgets,%s,config,tags}',? , ?)::text[],
					?::jsonb,
					true
				)
				WHERE uid = ?
			";
	//	return $qry;

		return $this->execQuery($qry, $params);
	}
}
