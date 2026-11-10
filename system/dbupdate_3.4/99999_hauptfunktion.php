<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

if ($result = $db->db_query("SELECT 1 FROM information_schema.columns WHERE table_schema='hr' AND table_name='tbl_vertragsbestandteil_funktion' AND column_name='hauptfunktion'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "

		ALTER TABLE 
			hr.tbl_vertragsbestandteil_funktion 
		    ADD COLUMN IF NOT EXISTS
			hauptfunktion boolean default false;

		COMMENT ON COLUMN hr.tbl_vertragsbestandteil_funktion.hauptfunktion IS E'Hauptfunktion im angegebenen Zeitraum';
		
		";

		if (! $db->db_query($qry))
			echo '<strong>Funktion: ' . $db->db_last_error() . '</strong><br>';
		else
			echo 'Feld für hauptfunktion wurden neu erstellt<br>';
	}
}
