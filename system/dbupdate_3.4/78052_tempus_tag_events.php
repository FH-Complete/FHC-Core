<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

$result = $db->db_query("SELECT 1
	FROM information_schema.columns
	WHERE table_schema = 'public'
		AND table_name = 'tbl_notizzuordnung'
		AND column_name = 'eindeutige_kalender_gruppen_id'");

if ($db->db_num_rows($result) === 0)
{
	$qry = "ALTER TABLE public.tbl_notizzuordnung
		ADD COLUMN eindeutige_kalender_gruppen_id UUID NULL DEFAULT NULL";

	if (!$db->db_query($qry))
		echo '<strong>public.tbl_notizzuordnung: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>public.tbl_notizzuordnung: eindeutige_kalender_gruppen_id column added';
}
