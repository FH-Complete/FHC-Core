<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');
//EXAMPLE
/*
if(!$result = @$db->db_query("SELECT statusgrund_id FROM public.tbl_prestudentstatus LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_prestudentstatus ADD COLUMN statusgrund_id integer;
		ALTER TABLE public.tbl_prestudentstatus ADD CONSTRAINT fk_prestudentstatus_statusgrund FOREIGN KEY (statusgrund_id) REFERENCES public.tbl_status_grund (statusgrund_id) ON DELETE RESTRICT ON UPDATE CASCADE;";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_prestudentstatus: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>public.tbl_prestudentstatus: Spalte statusgrund_id hinzugefuegt';
}
*/
