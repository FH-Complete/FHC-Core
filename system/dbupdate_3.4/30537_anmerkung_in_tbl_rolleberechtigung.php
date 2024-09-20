<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

// ADD COLUMN anmerkung to system.tbl_rolleberechtigung
if(!$result = @$db->db_query("SELECT anmerkung FROM system.tbl_rolleberechtigung LIMIT 1"))
{
	$qry = "ALTER TABLE system.tbl_rolleberechtigung ADD COLUMN anmerkung varchar(256);
			ALTER TABLE system.tbl_rolleberechtigung ADD COLUMN insertamum timestamp DEFAULT now();
			ALTER TABLE system.tbl_rolleberechtigung ADD COLUMN insertvon varchar(32);";

	if(!$db->db_query($qry))
		echo '<strong>system.tbl_rolleberechtigung '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Spalten anmerkung, insertamum, insertvon in system.tbl_rolleberechtigung hinzugefügt';
}
