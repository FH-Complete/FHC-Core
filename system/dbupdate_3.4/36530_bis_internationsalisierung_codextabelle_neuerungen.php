<?php

if (! defined('DB_NAME')) exit('No direct script access allowed');


if($result = @$db->db_query("SELECT 1 FROM bis.tbl_mobilitaetsprogramm WHERE mobilitaetsprogramm_code IN (43, 44, 45, 46, 47, 48, 49, 50, 51, 52)"))
{
	if($db->db_num_rows($result) < 10)
	{
		$qry = "
				INSERT INTO bis.tbl_mobilitaetsprogramm(mobilitaetsprogramm_code, kurzbz, beschreibung, sichtbar, sichtbar_outgoing) VALUES
				(43, 'AMinKunst', 'Auslandsstipendium des Bundesministeriums für Kunst', TRUE, TRUE),
				(44, 'BundeslandProg', 'Bundesland-Programm', TRUE, TRUE),
				(45, 'ERASMUSSMS', 'ERASMUS+ (SMS) - Studienaufenthalte', TRUE, TRUE),
				(46, 'ERASMUSSMT', 'ERASMUS+ (SMT) - Studierendenpraktika', TRUE, TRUE),
				(47, 'ERASMUSMundus', 'Erasmus Mundus Joint Master Degrees / Erasmus Mundus Joint Master', FALSE, FALSE),
				(48, 'EUGrad', 'Mobilitätsprogramm für Graduierte im EU-Bereich', TRUE, TRUE),
				(49, 'ÖStiftung', 'Stipendienstiftung der Republik Österreich', TRUE, FALSE),
				(50, 'ÖAkadWiss', 'Stipendienabkommen der Österreichischen Akademie der Wissenschaften', TRUE, TRUE),
				(51, 'FondWissForsch', 'Stipendium des Fonds zur Förderung der wissenschaftlichen Forschung', TRUE, TRUE),
				(52, 'SEMP', 'Swiss-European Mobility Programme (SEMP)', TRUE, TRUE)
				ON CONFLICT (mobilitaetsprogramm_code) DO NOTHING;
			";

		if(!$db->db_query($qry))
			echo '<strong>bis.tbl_mobilitaetsprogramm: '.$db->db_last_error().'</strong><br>';
		else
			echo ' bis.tbl_mobilitaetsprogramm: Mobilitätsprogramme hinzugefuegt<br>';
	}
}
