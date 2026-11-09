<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

if($result = @$db->db_query("
	SELECT 1
	WHERE NOT has_sequence_privilege('web', 'lehre.tbl_kalender_ort_kalender_ort_id_seq', 'USAGE')
"))
{
	if($db->db_num_rows($result) > 0)
	{
		$qry = "GRANT USAGE ON lehre.tbl_kalender_ort_kalender_ort_id_seq TO web;";

		if(!$db->db_query($qry))
			echo '<strong>lehre.tbl_kalender_ort: '.$db->db_last_error().'</strong><br>';
		else
			echo '<strong>lehre.tbl_kalender_ort sequence usage grant extended to WEB user</strong><br>';
	}
}