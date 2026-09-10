<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

if ($result = $db->db_query("SELECT * FROM pg_class WHERE relname='idx_tbl_benutzerfunktion_uid'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "CREATE INDEX idx_tbl_benutzerfunktion_uid ON public.tbl_benutzerfunktion USING btree (uid)";

		if (! $db->db_query($qry))
			echo '<strong>idx_tbl_benutzerfunktion_uid: ' . $db->db_last_error() . '</strong><br>';
		else
			echo 'Index idx_tbl_benutzerfunktion_uid angelegt<br>';
	}
}
