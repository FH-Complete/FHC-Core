<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

//Add column sort to dashboard.tbl_bookmark
if(!@$db->db_query("SELECT sort FROM dashboard.tbl_bookmark LIMIT 1")) {
	$qry = "ALTER TABLE dashboard.tbl_bookmark ADD COLUMN sort integer DEFAULT NULL;
			COMMENT ON COLUMN dashboard.tbl_bookmark.sort IS 'Sort Index for Bookmark.';
			";

	if (!$db->db_query($qry))
		echo '<strong>dashboard.tbl_bookmark ' . $db->db_last_error() . '</strong><br>';
	else
		echo '<br>Spalte sort zu Tabelle dashboard.tbl_bookmark hinzugefügt';

	//add preliminary Sort for all bookmarks if NULL
	if(@$db->db_query("SELECT sort FROM dashboard.tbl_bookmark LIMIT 1")) {
		$qry = "WITH ranked AS (
		  SELECT
			t1.bookmark_id,
			(
			  SELECT COUNT(*)
			  FROM dashboard.tbl_bookmark t2
			  WHERE t2.uid = t1.uid
				AND t2.bookmark_id <= t1.bookmark_id
			) AS rn
		  FROM dashboard.tbl_bookmark t1
		)
		UPDATE dashboard.tbl_bookmark t
		SET sort = ranked.rn
		FROM ranked
		WHERE t.bookmark_id = ranked.bookmark_id
		  AND t.sort IS NULL;";

		if (!$db->db_query($qry))
			echo '<strong>dashboard.tbl_bookmark ' . $db->db_last_error() . '</strong><br>';
		else
			echo '<br>Tabelle dashboard.tbl_bookmark: Spalte sort mit vorläufiger Sortierung befüllt';
	}
}

//set column tag to type JSONB
if($result = @$db->db_query("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='dashboard'
								AND TABLE_NAME='tbl_bookmark' AND COLUMN_NAME = 'tag'
								AND DATA_TYPE='character varying' AND character_maximum_length='255';"))
{
	$qry = "
		ALTER TABLE dashboard.tbl_bookmark
		ALTER COLUMN tag TYPE jsonb
		USING tag::jsonb;
			  ";

	if (!$db->db_query($qry))
		echo '<strong>dashboard.tbl_bookmark ' . $db->db_last_error() . '</strong><br>';
	else
		echo '<br>Tabelle dashboard.tbl_bookmark: Spalte tag auf Typ JSONB geändert';

}