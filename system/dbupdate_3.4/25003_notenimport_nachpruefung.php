<?php

// lehre.tbl_pruefungstyp: add type Termin3
if($result = $db->db_query("SELECT 1 FROM lehre.tbl_pruefungstyp WHERE pruefungstyp_kurzbz='Termin3'"))
{
	if($db->db_num_rows($result)==0)
	{
		$qry = "INSERT INTO lehre.tbl_pruefungstyp(pruefungstyp_kurzbz, beschreibung, abschluss) VALUES('Termin3', '3.Termin', false);";

		if(!$db->db_query($qry))
			echo '<strong>Prüfungstyp: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Prüfungstyp 3.Termin in lehre.tbl_pruefungstyp hinzugefügt';
	}
}
