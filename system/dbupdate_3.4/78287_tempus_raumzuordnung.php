<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

if ($result = @$db->db_query("SELECT 1 FROM public.tbl_variablenname WHERE name = 'roomless_planning';"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO public.tbl_variablenname(name, defaultwert) VALUES('roomless_planning', false);
				INSERT INTO public.tbl_variablenname(name, defaultwert) VALUES('priority_room_planning', false);
				INSERT INTO public.tbl_variablenname(name, defaultwert) VALUES('dialog_room_planning', false);";

		if (!$db->db_query($qry))
			echo '<strong>public.tbl_variablenname '.$db->db_last_error().'</strong><br>';
		else
			echo 'public.tbl_variablenname: Added "roomless_planning", "priority_room_planning", "dialog_room_planning"<br>';
	}
}