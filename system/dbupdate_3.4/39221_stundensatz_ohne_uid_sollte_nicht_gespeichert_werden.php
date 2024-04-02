<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

//  Add NOT NULL constraint on uid on hr.tbl_stundensatz
if($result = @$db->db_query("SELECT is_nullable FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'hr' AND TABLE_NAME = 'tbl_stundensatz' AND COLUMN_NAME = 'uid' AND is_nullable = 'YES'"))
{
	if($db->db_num_rows($result) == 1)
	{
		$qry = "ALTER TABLE hr.tbl_stundensatz ALTER COLUMN uid SET NOT NULL";

		if (!$db->db_query($qry))
			echo '<strong>hr.tbl_stundensatz '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Added NOT NULL constraint on "uid" hr.tbl_stundensatz<br>';
	}
}
