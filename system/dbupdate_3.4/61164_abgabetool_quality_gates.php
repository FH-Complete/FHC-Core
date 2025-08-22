<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

// add campus.tbl_paabgabetyp options for Quality Gates
if($result = $db->db_query("SELECT 1 FROM campus.tbl_paabgabetyp WHERE paabgabetyp_kurzbz='qualgate1'"))
{
	if($db->db_num_rows($result) === 0)
	{
		$qry = "INSERT INTO campus.tbl_paabgabetyp (paabgabetyp_kurzbz, bezeichnung) VALUES('qualgate1', 'Quality Gate 1');";

		if(!$db->db_query($qry))
			echo '<strong>campus.tbl_paabgabetyp: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>paabgabetyp quality gate 1 hinzugefuegt';
	}
}

if($result = $db->db_query("SELECT 1 FROM campus.tbl_paabgabetyp WHERE paabgabetyp_kurzbz='qualgate2'"))
{
	if($db->db_num_rows($result) === 0)
	{
		$qry = "INSERT INTO campus.tbl_paabgabetyp (paabgabetyp_kurzbz, bezeichnung) VALUES('qualgate2', 'Quality Gate 2');";

		if(!$db->db_query($qry))
			echo '<strong>campus.tbl_paabgabetyp: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>paabgabetyp quality gate 2 hinzugefuegt';
	}
}
