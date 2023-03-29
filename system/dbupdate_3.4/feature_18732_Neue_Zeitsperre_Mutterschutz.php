<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

//Neue Zeitsperre Mutterschutz
if($result = $db->db_query("SELECT 1 FROM campus.tbl_zeitsperretyp WHERE zeitsperretyp_kurzbz = 'Mutter'"))
{
	if($db->db_num_rows($result)==0)
	{
		$qry = "INSERT INTO campus.tbl_zeitsperretyp (zeitsperretyp_kurzbz, beschreibung) VALUES ('Mutter', 'Mutterschutz');";

		if(!$db->db_query($qry))
		echo '<strong>campus.tbl_zeitsperretyp: '.$db->db_last_error().'</strong><br>';
		else
		echo '<br>Zeitsperretyp Mutterschutz in campus.tbl_zeitsperretyp  hinzugefügt';
	}
}
