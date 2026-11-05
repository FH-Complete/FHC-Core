<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

if($result = @$db->db_query("SELECT studierendenantrag_statustyp_kurzbz FROM campus.tbl_studierendenantrag_statustyp where studierendenantrag_statustyp_kurzbz = 'GeprueftAssistenz' LIMIT 1"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "
                INSERT INTO campus.tbl_studierendenantrag_statustyp
                (
                    studierendenantrag_statustyp_kurzbz,
                    bezeichnung
                )
                VALUES
                (
                    'GeprueftAssistenz',
                    '{GeprueftAssistenz, Checked_Ass}'
                )
            ";

		if(!$db->db_query($qry))
			echo '<strong> campus.tbl_studierendenantrag_statustyp '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>campus.tbl_studierendenantrag_statustyp: neuer Eintrag GeprueftAssistenz in Spalte studierendenantrag_statustyp_kurzbz ';
	}
}
