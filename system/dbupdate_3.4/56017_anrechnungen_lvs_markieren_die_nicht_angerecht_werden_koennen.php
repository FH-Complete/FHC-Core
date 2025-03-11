<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

//Add column anrechenbar to lehre.tbl_lehrveranstaltung
if(!@$db->db_query("SELECT anrechenbar FROM lehre.tbl_lehrveranstaltung LIMIT 1"))
{
	$qry = 'ALTER TABLE lehre.tbl_lehrveranstaltung ADD COLUMN anrechenbar boolean DEFAULT true;';

	if(!$db->db_query($qry))
		echo '<strong> lehre.tbl_lehrveranstaltung '.$db->db_last_error().'</strong><br>';
	else
	{
		echo '<br>lehre.tbl_lehrveranstaltung: Neue Spalte anrechenbar hinzugefügt';

		$qry = 'UPDATE lehre.tbl_lehrveranstaltung SET anrechenbar = true';

		if(!$db->db_query($qry))
			echo '<strong> lehre.tbl_lehrveranstaltung '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>lehre.tbl_lehrveranstaltung: anrechenbar auf true gesetzt';
	}
}
