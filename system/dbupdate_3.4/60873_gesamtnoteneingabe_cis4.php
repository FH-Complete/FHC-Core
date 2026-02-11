<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

if($result = $db->db_query("SELECT 1 FROM public.tbl_vorlage WHERE vorlage_kurzbz = 'Notenfreigabe'"))
{
	if($db->db_num_rows($result) === 0)
	{
		$qry = "INSERT INTO public.tbl_vorlage (vorlage_kurzbz, bezeichnung, anmerkung, mimetype)
				VALUES ('Notenfreigabe', 'Notenfreigabe', null, 'text/html')
				ON CONFLICT (vorlage_kurzbz) DO NOTHING;";

		if(!$db->db_query($qry))
			echo '<strong>system.tbl_vorlage: '.$db->db_last_error().'</strong><br>';
		else
			echo "<br>system.tbl_vorlage Notenfreigabe hinzugefuegt";
	}
}