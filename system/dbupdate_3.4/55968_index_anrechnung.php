<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

// Add index to lehre.tbl_anrechnung.prestudent_id
if ($result = $db->db_query("SELECT * FROM pg_class WHERE relname='idx_anrechnungen_prestudent_id'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "CREATE INDEX idx_anrechnungen_prestudent_id ON lehre.tbl_anrechnung USING btree (prestudent_id)";

		if (! $db->db_query($qry))
			echo '<strong>Indizes: ' . $db->db_last_error() . '</strong><br>';
		else
			echo 'Index fuer lehre.tbl_anrechnung_prestudent_id hinzugefuegt';
	}
}

// Add index to lehre.tbl_anrechnung.studiensemester_kurzbz
if ($result = $db->db_query("SELECT * FROM pg_class WHERE relname='idx_anrechnungen_studiensemester_kurzbz'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "CREATE INDEX idx_anrechnungen_studiensemester_kurzbz ON lehre.tbl_anrechnung USING btree (studiensemester_kurzbz)";

		if (! $db->db_query($qry))
			echo '<strong>Indizes: ' . $db->db_last_error() . '</strong><br>';
		else
			echo 'Index fuer lehre.tbl_anrechnung_studiensemester_kurzbz hinzugefuegt';
	}
}
