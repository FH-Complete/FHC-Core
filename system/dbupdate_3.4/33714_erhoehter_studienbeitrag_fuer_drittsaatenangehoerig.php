<?php
// Added Buchungstyp "StudiengebuehrErhoeht"
if ($result = @$db->db_query("SELECT 1 FROM public.tbl_buchungstyp WHERE buchungstyp_kurzbz = 'StudiengebuehrErhoeht';"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO public.tbl_buchungstyp (buchungstyp_kurzbz, beschreibung, standardtext, standardbetrag) VALUES ('StudiengebuehrErhoeht', 'Erhöhter Studienbeitrag', 'Erhöhter Studienbeitrag', '-3000');";
		if (!$db->db_query($qry))
			echo '<strong>public.tbl_buchungstyp '.$db->db_last_error().'</strong><br>';
		else
			echo ' public.tbl_buchungstyp: Added buchungstyp "StudiengebuehrErhoeht" <br>';
	}
}