<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

if ($result = @$db->db_query("SELECT 1 FROM system.tbl_app WHERE app='lvevaluierung' LIMIT 1"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO system.tbl_app (app) VALUES ('lvevaluierung');";

		if(!$db->db_query($qry))
			echo '<strong>system.tbl_app: '.$db->db_last_error().'</strong><br>';
		else
			echo ' system.tbl_app: lvevaluierung hinzugefügt<br>';
	}
}

//Add column evaluierung to lehre.tbl_lehrveranstaltung
if(!@$db->db_query("SELECT evaluierung FROM lehre.tbl_lehrveranstaltung LIMIT 1"))
{
	$qry = "ALTER TABLE lehre.tbl_lehrveranstaltung ADD COLUMN evaluierung boolean NOT NULL DEFAULT true;
			COMMENT ON COLUMN lehre.tbl_lehrveranstaltung.evaluierung IS 'TRUE wenn für diese LV eine LV-Evaluierung durchgeführt wird';
			";

	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_lehrveranstaltung '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Spalte evaluierung zu Tabelle lehre.tbl_lehrveranstaltung hinzugefügt';
}