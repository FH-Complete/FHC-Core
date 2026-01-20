<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

// ADD COLUMNS insertamum, insertvon, updateamum, updatevon to system.tbl_fehler
if(!$result = @$db->db_query("SELECT insertamum FROM system.tbl_fehler LIMIT 1"))
{
	$qry = "ALTER TABLE system.tbl_fehler ADD COLUMN insertamum timestamp DEFAULT now();
			ALTER TABLE system.tbl_fehler ADD COLUMN insertvon varchar(32);
			ALTER TABLE system.tbl_fehler ADD COLUMN updateamum timestamp DEFAULT now();
			ALTER TABLE system.tbl_fehler ADD COLUMN updatevon varchar(32);";

	if(!$db->db_query($qry))
		echo '<strong>system.tbl_fehler '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Spalten insertamum, insertvon, updateamum, updatevon in system.tbl_fehler hinzugefügt';
}