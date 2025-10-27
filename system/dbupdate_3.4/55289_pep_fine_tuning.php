<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

// system.tbl_berechtigung: add berechtigung addons/reports:begrenzt
if($result = $db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz='addon/reports:begrenzt'"))
{
	if($db->db_num_rows($result)==0)
	{
	$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES('addon/reports:begrenzt', 'Reports nur über Direktlink aufrufbar');";

		if(!$db->db_query($qry))
			echo '<strong>Berechtigung: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Neue Berechtigung addon/reports:begrenzt zu system.tbl_berechtigung hinzugefügt';
	}
}
