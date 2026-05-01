<?php
if (!defined('DB_NAME')) exit('No direct script access allowed');

// Add new name type in public.tbl_variablenname
if ($result = @$db->db_query("SELECT 1 FROM public.tbl_variablenname WHERE name = 'stv_favorites';"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO public.tbl_variablenname(name, defaultwert) VALUES('stv_favorites', null);";

		if (!$db->db_query($qry))
			echo '<strong>public.tbl_variablenname '.$db->db_last_error().'</strong><br>';
		else
			echo 'public.tbl_variablenname: Added name "stv_favorites"<br>';
	}
}
