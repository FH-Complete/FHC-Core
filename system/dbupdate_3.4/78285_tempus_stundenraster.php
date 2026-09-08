<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

if($result = $db->db_query("SELECT * FROM system.tbl_berechtigung WHERE berechtigung_kurzbz='basis/tempus_stundenraster'"))
{
	if($db->db_num_rows($result)==0)
	{
		$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung)
				VALUES('basis/tempus_stundenraster','Berechtigung, um den Stundenraster zu aktivieren/deaktivieren');";

		if(!$db->db_query($qry))
			echo '<strong>Berechtigung: '.$db->db_last_error().'</strong><br>';
		else
			echo 'Neue Berechtigung basis/tempus_stundenraster hinzugefuegt!<br>';
	}
}
