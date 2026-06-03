<?php

// neuen studierendenantrag_statustyp mit studierendenantrag_statustyp_kurzbz = 'Storniert' als Zeile hinzufügen
if($result = @$db->db_query("SELECT 1 FROM campus.tbl_studierendenantrag_statustyp WHERE studierendenantrag_statustyp_kurzbz= 'Storniert';"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO campus.tbl_studierendenantrag_statustyp(studierendenantrag_statustyp_kurzbz, bezeichnung) VALUES ('Storniert', '{Storniert, Terminated}');";

		if(!$db->db_query($qry))
			echo '<strong>campus.tbl_studierendenantrag_statustyp: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>campus.tbl_studierendenantrag_statustyp: Zeile Storniert hinzugefuegt!<br>';
	}
}
