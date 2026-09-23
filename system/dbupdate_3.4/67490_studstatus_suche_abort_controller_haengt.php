<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

// Add index to campus.tbl_studierendenantrag_status.studierendenantrag_id
if ($result = $db->db_query("SELECT * FROM pg_class WHERE relname='idx_tbl_studierendenantrag_status_studierendenantrag_id'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "CREATE INDEX idx_tbl_studierendenantrag_status_studierendenantrag_id ON campus.tbl_studierendenantrag_status USING btree (studierendenantrag_id)";

		if (!$db->db_query($qry))
			echo '<strong>Indizes: ' . $db->db_last_error() . '</strong><br>';
		else
			echo 'Index fuer campus.tbl_studierendenantrag_status.studierendenantrag_id hinzugefuegt';
	}
}
