<?php
if (!defined('DB_NAME')) exit('No direct script access allowed');

// Change type of wert in public.tbl_variable
if ($result = @$db->db_query("
	SELECT data_type
	FROM information_schema.columns
	WHERE table_schema = 'public'
	AND table_name = 'tbl_variable'
	AND column_name = 'wert';
")) {
	if ($db->db_num_rows($result) == 1)
	{
		$data_type = $db->db_fetch_row($result)[0];
	
		if (strtolower($data_type) != 'text')
		{
			$qry = "ALTER TABLE public.tbl_variable
				ALTER COLUMN wert
				TYPE TEXT;";

			if (!$db->db_query($qry))
				echo '<strong>public.tbl_variable '.$db->db_last_error().'</strong><br>';
			else
				echo 'public.tbl_variable: Change type of "wert" to TEXT<br>';
		}
	}
}
