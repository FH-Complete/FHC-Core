<?php
if (!defined('DB_NAME')) exit('No direct script access allowed');

// Add new name type in public.tbl_variablenname
if ($result = @$db->db_query("SELECT 1 FROM public.tbl_variablenname WHERE name = 'font_size';"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO public.tbl_variablenname(name, defaultwert) VALUES('font_size', null);
				UPDATE public.tbl_variable SET name = 'font_size' WHERE name = 'stv_font_size';
				DELETE FROM public.tbl_variablenname WHERE name = 'stv_font_size';
";

		if (!$db->db_query($qry))
			echo '<strong>public.tbl_variablenname '.$db->db_last_error().'</strong><br>';
		else
			echo 'public.tbl_variablenname: Added name "font_size"<br>';
			echo 'public.tbl_variable: Updated name "stv_font_size" to "font_size"<br>';
			echo 'public.tbl_variablenname: Deleted name "stv_font_size"<br>';
	}
}
