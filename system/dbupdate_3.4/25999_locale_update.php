<?php

if ($result = $db->db_query("SELECT * FROM information_schema.columns WHERE column_name = 'lc_time' AND table_name = 'tbl_sprache' AND table_schema = 'public';")) {
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "
		ALTER TABLE public.tbl_sprache ADD lc_time VARCHAR(255) ;
		UPDATE public.tbl_sprache SET lc_time = 'en-GB' where locale ='en-US'; 
		UPDATE public.tbl_sprache SET lc_time = 'de-AT' where locale ='de-AT'; 
		";

		if (!$db->db_query($qry))
			echo '<strong>public.tbl_sprache: ' . $db->db_last_error() . '</strong><br>';
		else
			echo '<br>public.tbl_sprache: column lc_time was successfully added';
	}
}

