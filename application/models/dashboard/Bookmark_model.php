<?php
class Bookmark_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'dashboard.tbl_bookmark';
		$this->pk = 'bookmark_id';
	}

	/**
	 * returns all bookmark tags of a user
	 */
	public function getAllBookmarkTags($uid)
	{
		$qry = "
			SELECT array_agg(DISTINCT tag) AS data
			FROM (
			  SELECT jsonb_array_elements_text(tag) AS tag
			  FROM dashboard.tbl_bookmark
			  WHERE uid = ?
			) t;
		";

		return $this->execQuery($qry, array('uid' => $uid));
	}

	/**
	 * Get Overrides of given uid and funktion_kurzbz and widget_id
	 * @param integer $widget_id
	 * @param string $uid
	 * @param string $funktion_kurzbz
	 * @return array
	 */
	public function getTagFilter($widgetId, $uid, $funktion_kurzbz)
	{
		$qry = " SELECT override -> '" . $funktion_kurzbz . "'-> 'widgets'-> '" . $widgetId . "'-> 'config' -> 'tags' AS tags FROM dashboard.tbl_dashboard_benutzer_override WHERE uid = '" . $uid . "';";

		return $this->execQuery($qry);
	}

	/*
	 * updates Tagfilter
	 * checks and changes type of config
	 * if config == array -> change to object
	 */
	public function addAndUpdateTagFilter($widgetId, $uid, $funktion_kurzbz, $tags)
	{
		$params = [
			$funktion_kurzbz, $widgetId,
			$funktion_kurzbz, $widgetId,
			$funktion_kurzbz, $widgetId,
			$funktion_kurzbz, $widgetId,
			$tags,
			$uid
		];

		$qry = "
			UPDATE dashboard.tbl_dashboard_benutzer_override
			SET override = jsonb_set(
				-- check if config is object
				jsonb_set(
					COALESCE(override, '{}'::jsonb),
					format('{%s,widgets,%s,config}', ?, ?)::text[],
					CASE
						WHEN jsonb_typeof(override #> format('{%s,widgets,%s,config}', ?, ?)::text[]) = 'array'
						THEN '{}'::jsonb
						ELSE COALESCE((override #> format('{%s,widgets,%s,config}', ?, ?)::text[])::jsonb, '{}'::jsonb)
					END,
					true
				),
				-- insert tags
				format('{%s,widgets,%s,config,tags}', ?, ?)::text[],
				?::jsonb,
				true
			)
			WHERE uid = ?
		";

		return $this->execQuery($qry, $params);
	}

	public function checkOrAddToOverride($widgetId, $uid, $funktion_kurzbz)
	{
		$params = [$funktion_kurzbz, $widgetId, $uid];
		$qry = " SELECT override -> '" . $funktion_kurzbz . "'-> 'widgets'-> '" . $widgetId . "'-> 'widgetid' as widgetid FROM dashboard.tbl_dashboard_benutzer_override WHERE uid = '" . $uid . "';";

		return $this->execQuery($qry, $params);
	}
}
