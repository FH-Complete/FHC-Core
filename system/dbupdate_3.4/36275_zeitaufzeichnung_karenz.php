<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

// Add index to system.tbl_log
if ($result = $db->db_query("SELECT * FROM information_schema.role_table_grants WHERE table_name='tbl_vertragsbestandteil_funktion' AND table_schema='hr' AND grantee='web' AND privilege_type='SELECT'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "GRANT SELECT ON hr.tbl_vertragsbestandteil_funktion TO web;";

		if (! $db->db_query($qry))
			echo '<strong>Vertragsbestandteil Funktion Rechte: ' . $db->db_last_error() . '</strong><br>';
		else
			echo 'Leserechte für Web User auf vertragsbetandteil_funktion hinzugefuegt';
	}
}
