<?php

// neuen Organisationseinheittyp (Programm) als Zeile hinzufügen
if($result = @$db->db_query("SELECT 1 FROM public.tbl_organisationseinheittyp WHERE organisationseinheittyp_kurzbz= 'Programm';"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO public.tbl_organisationseinheittyp(organisationseinheittyp_kurzbz, beschreibung, bezeichnung) VALUES ('Programm', 'Programm', 'Programm');";

		if(!$db->db_query($qry))
			echo '<strong>public.tbl_organisationseinheittyp: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>public.tbl_organisationseinheittyp: Zeile Programm hinzugefuegt!<br>';
	}
}
