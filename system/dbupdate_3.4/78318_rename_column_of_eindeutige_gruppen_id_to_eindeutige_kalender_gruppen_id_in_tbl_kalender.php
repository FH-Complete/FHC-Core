<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

$result = $db->db_query("SELECT 1 FROM information_schema.columns WHERE table_schema = 'lehre' AND table_name = 'tbl_kalender' AND column_name = 'eindeutige_gruppen_id'");
if($db->db_num_rows($result) === 1)
{

	$qry = "ALTER TABLE lehre.tbl_kalender
			RENAME COLUMN eindeutige_gruppen_id TO eindeutige_kalender_gruppen_id";

	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_kalender: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>lehre.tbl_kalender column eindeutige_gruppen_id renamed to eindeutige_kalender_gruppen_id';

}