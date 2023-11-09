<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

// system.tbl_app: add type mobilityonline
if($result = $db->db_query("SELECT 1 FROM system.tbl_app WHERE app='mobilityonline'"))
{
	if($db->db_num_rows($result)==0)
	{
	$qry = "INSERT INTO system.tbl_app(app) VALUES('mobilityonline');";

		if(!$db->db_query($qry))
			echo '<strong>App: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Neue App mobilityonline in system.tbl_app hinzugefügt';
	}
}
