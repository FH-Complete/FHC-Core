<?php

if (! defined('DB_NAME')) exit('No direct script access allowed');


if ($result = @$db->db_query("SELECT 1 FROM system.tbl_app WHERE app='softwarebereitstellung' LIMIT 1"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO system.tbl_app(app) VALUES('softwarebereitstellung');";

		if(!$db->db_query($qry))
			echo '<strong>system.tbl_app: '.$db->db_last_error().'</strong><br>';
		else
			echo ' system.tbl_app: softwarebereitstellung hinzugefügt<br>';
	}
}
