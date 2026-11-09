<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

if($result = $db->db_query("SELECT 1 FROM system.tbl_webservicetyp WHERE webservicetyp_kurzbz = 'konto'"))
{
	if($db->db_num_rows($result) === 0)
	{
		$qry = "insert into system.tbl_webservicetyp (webservicetyp_kurzbz, beschreibung) values('konto','Änderungen von Kontobuchungen')";

		if(!$db->db_query($qry))
			echo '<strong>Public Tabelle Webservicetyp: '.$db->db_last_error().'</strong><br>';
		else
			echo "<br>Webservicetyp 'konto' hinzugefuegt";
	}
}