<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

// Add index to system.tbl_log
if ($result = $db->db_query("SELECT * FROM pg_class WHERE relname='idx_webserivcelog_executetime'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "CREATE INDEX idx_webserivcelog_executetime ON system.tbl_webservicelog USING btree (execute_time)";

		if (! $db->db_query($qry))
			echo '<strong>Indizes: ' . $db->db_last_error() . '</strong><br>';
		else
			echo 'Index fuer system.tbl_webservicelog.execute_time hinzugefuegt';
	}
}
