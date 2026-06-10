<?php
// Added Buchungstyp "StudiengebuehrErhoeht"
if ($result = @$db->db_query("SELECT 1 FROM public.tbl_buchungstyp WHERE buchungstyp_kurzbz = 'KautionDrittStaat';"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO public.tbl_buchungstyp (buchungstyp_kurzbz, beschreibung, standardtext, standardbetrag) VALUES ('KautionDrittStaat', 'Kaution', 'Deposit for application, third countries', '-250');";
		if (!$db->db_query($qry))
			echo '<strong>public.tbl_buchungstyp '.$db->db_last_error().'</strong><br>';
		else
			echo ' public.tbl_buchungstyp: Added buchungstyp "KautionDrittStaat" <br>';
	}
}