<?php

if (! defined('DB_NAME')) exit('No direct script access allowed');

if($result = @$db->db_query("SELECT character_maximum_length FROM information_schema.columns WHERE column_name='insertvon' AND table_name='tbl_benutzerrolle' AND table_schema='system' AND character_maximum_length = 16;"))
{
	if ($db->db_num_rows($result) === 1)
	{
		$second_result = @$db->db_query("SELECT view_definition FROM information_schema.views WHERE table_schema='system' AND table_name='vw_berechtigung_nichtrekursiv'");
		if ($db->db_num_rows($second_result) === 1)
		{
			$qry = "DROP VIEW system.vw_berechtigung; 
					DROP VIEW system.vw_berechtigung_nichtrekursiv;";

			if(!$db->db_query($qry))
				echo '<strong>system.vw_berechtigung:'.$db->db_last_error().'</strong><br>';
			else
			{
				$qry = "ALTER TABLE system.tbl_benutzerrolle 
							ALTER COLUMN insertvon TYPE varchar(32),
							ALTER COLUMN updatevon TYPE varchar(32);";
				if(!$db->db_query($qry))
					echo '<strong>system.tbl_benutzerrolle: '.$db->db_last_error().'</strong><br>';
				else
				{
					echo 'system.tbl_benutzerrolle: Spalte insertvon, updatevon auf 32 Zeichen verlaengert<br>';

					$qry = "
						CREATE OR REPLACE VIEW system.vw_berechtigung_nichtrekursiv AS
							SELECT
								uid, berechtigung_kurzbz,
								-- art zusammenfassung und nur die nehmen die gleich sind
								CASE WHEN length(art)>length(art1) THEN art1 ELSE art END as art,
								oe_kurzbz, kostenstelle_id
							FROM
								(
								-- Normal
								SELECT
									benutzerberechtigung_id, tbl_benutzerrolle.uid, tbl_benutzerrolle.funktion_kurzbz,
									tbl_benutzerrolle.rolle_kurzbz, tbl_benutzerrolle.berechtigung_kurzbz, tbl_benutzerrolle.art, tbl_benutzerrolle.art art1,
									tbl_benutzerrolle.oe_kurzbz, tbl_benutzerrolle.studiensemester_kurzbz, tbl_benutzerrolle.start,
									tbl_benutzerrolle.ende, tbl_benutzerrolle.negativ, tbl_benutzerrolle.updateamum, tbl_benutzerrolle.updatevon,
									tbl_benutzerrolle.insertamum, tbl_benutzerrolle.insertvon,tbl_benutzerrolle.kostenstelle_id,tbl_benutzerrolle.anmerkung
								FROM
									system.tbl_benutzerrolle JOIN system.tbl_berechtigung USING(berechtigung_kurzbz)
				
								-- Rollen
								UNION
								SELECT
									benutzerberechtigung_id, tbl_benutzerrolle.uid, tbl_benutzerrolle.funktion_kurzbz,
									tbl_benutzerrolle.rolle_kurzbz, tbl_berechtigung.berechtigung_kurzbz, tbl_benutzerrolle.art, tbl_rolleberechtigung.art art1,
									tbl_benutzerrolle.oe_kurzbz, tbl_benutzerrolle.studiensemester_kurzbz, tbl_benutzerrolle.start,
									tbl_benutzerrolle.ende, tbl_benutzerrolle.negativ, tbl_benutzerrolle.updateamum, tbl_benutzerrolle.updatevon,
									tbl_benutzerrolle.insertamum, tbl_benutzerrolle.insertvon,tbl_benutzerrolle.kostenstelle_id,tbl_benutzerrolle.anmerkung
								FROM
									system.tbl_benutzerrolle JOIN system.tbl_rolle USING(rolle_kurzbz)
									JOIN system.tbl_rolleberechtigung USING(rolle_kurzbz)
									JOIN system.tbl_berechtigung ON(tbl_rolleberechtigung.berechtigung_kurzbz=tbl_berechtigung.berechtigung_kurzbz)
				
								-- Funktionen
								UNION
								SELECT
									benutzerberechtigung_id, tbl_benutzerfunktion.uid, tbl_benutzerrolle.funktion_kurzbz,
									tbl_benutzerrolle.rolle_kurzbz, tbl_benutzerrolle.berechtigung_kurzbz, tbl_benutzerrolle.art, tbl_benutzerrolle.art art1,
									tbl_benutzerfunktion.oe_kurzbz, tbl_benutzerrolle.studiensemester_kurzbz, tbl_benutzerrolle.start,
									tbl_benutzerrolle.ende, tbl_benutzerrolle.negativ, tbl_benutzerrolle.updateamum, tbl_benutzerrolle.updatevon,
									tbl_benutzerrolle.insertamum, tbl_benutzerrolle.insertvon,tbl_benutzerrolle.kostenstelle_id,tbl_benutzerrolle.anmerkung
								FROM
									system.tbl_benutzerrolle JOIN public.tbl_benutzerfunktion USING(funktion_kurzbz)
								WHERE
									(tbl_benutzerfunktion.datum_von IS NULL OR tbl_benutzerfunktion.datum_von<=now())
									AND (tbl_benutzerfunktion.datum_bis IS NULL OR tbl_benutzerfunktion.datum_bis>=now())
				
								-- Funktion Mitarbeiter
								UNION
								SELECT
									benutzerberechtigung_id, vw_mitarbeiter.uid, tbl_benutzerrolle.funktion_kurzbz,
									tbl_benutzerrolle.rolle_kurzbz, tbl_benutzerrolle.berechtigung_kurzbz, tbl_benutzerrolle.art, tbl_benutzerrolle.art art1,
									tbl_benutzerrolle.oe_kurzbz, tbl_benutzerrolle.studiensemester_kurzbz, tbl_benutzerrolle.start,
									tbl_benutzerrolle.ende, tbl_benutzerrolle.negativ, tbl_benutzerrolle.updateamum, tbl_benutzerrolle.updatevon,
									tbl_benutzerrolle.insertamum, tbl_benutzerrolle.insertvon,tbl_benutzerrolle.kostenstelle_id,tbl_benutzerrolle.anmerkung
								FROM
									system.tbl_benutzerrolle, campus.vw_mitarbeiter
								WHERE
									tbl_benutzerrolle.funktion_kurzbz='Mitarbeiter' and vw_mitarbeiter.aktiv
				
				
								-- Funktion Student
								UNION
								SELECT
									benutzerberechtigung_id, vw_student.uid, tbl_benutzerrolle.funktion_kurzbz,
									tbl_benutzerrolle.rolle_kurzbz, tbl_benutzerrolle.berechtigung_kurzbz, tbl_benutzerrolle.art, tbl_benutzerrolle.art art1,
									tbl_benutzerrolle.oe_kurzbz, tbl_benutzerrolle.studiensemester_kurzbz, tbl_benutzerrolle.start,
									tbl_benutzerrolle.ende, tbl_benutzerrolle.negativ, tbl_benutzerrolle.updateamum, tbl_benutzerrolle.updatevon,
									tbl_benutzerrolle.insertamum, tbl_benutzerrolle.insertvon,tbl_benutzerrolle.kostenstelle_id,tbl_benutzerrolle.anmerkung
								FROM
									system.tbl_benutzerrolle, campus.vw_student
								WHERE
									tbl_benutzerrolle.funktion_kurzbz='Student' and vw_student.aktiv
							) as a
							JOIN public.tbl_benutzer USING(uid)
							WHERE
								-- Datumsgrenzen beruecksichtigen
								tbl_benutzer.aktiv = true
								AND (a.start<=now() OR a.start is null)
								AND (a.ende>=now() OR a.ende is null)
				
								-- Neagtiv Rechte entfernen
								AND not negativ
								AND NOT EXISTS(SELECT
									1
								FROM
									system.tbl_benutzerrolle JOIN system.tbl_berechtigung USING(berechtigung_kurzbz) WHERE uid=a.uid AND berechtigung_kurzbz=a.berechtigung_kurzbz AND negativ);
				
							GRANT SELECT ON system.vw_berechtigung_nichtrekursiv TO web;
							GRANT SELECT ON system.vw_berechtigung_nichtrekursiv TO vilesci;
							
							CREATE OR REPLACE VIEW system.vw_berechtigung AS
								WITH RECURSIVE oes(oe_kurzbz, pfad) as
								(
									SELECT
										oe_kurzbz, '/' || oe_kurzbz::text as pfad FROM public.tbl_organisationseinheit
									WHERE
										oe_parent_kurzbz is null AND aktiv = true
									UNION ALL
									SELECT
										o.oe_kurzbz, COALESCE(oes.pfad,'') || '/' || COALESCE(o.oe_kurzbz,'') as pfad
									FROM
										public.tbl_organisationseinheit o, oes
									WHERE
										o.oe_parent_kurzbz=oes.oe_kurzbz and aktiv = true
								)
								SELECT
									uid, berechtigung_kurzbz, art, oes.oe_kurzbz, kostenstelle_id
								FROM
									system.vw_berechtigung_nichtrekursiv, oes
								WHERE
									(oes.pfad || '/' like '%/' || vw_berechtigung_nichtrekursiv.oe_kurzbz || '/%'
									OR (vw_berechtigung_nichtrekursiv.oe_kurzbz is null AND kostenstelle_id is null))
								UNION
								SELECT
									uid, berechtigung_kurzbz, art, null::varchar(32), kostenstelle_id
								FROM
									system.vw_berechtigung_nichtrekursiv
								WHERE
									kostenstelle_id is not null;
						
								GRANT SELECT ON system.vw_berechtigung TO web;
								GRANT SELECT ON system.vw_berechtigung TO vilesci;
							";

					if(!$db->db_query($qry))
						echo '<strong>system.vw_berechtigung_nichtrekursiv:'.$db->db_last_error().'</strong><br>';
				}
			}
		}
		else
		{
			$qry = "ALTER TABLE system.tbl_benutzerrolle 
							ALTER COLUMN insertvon TYPE varchar(32),
							ALTER COLUMN updatevon TYPE varchar(32);";
			if(!$db->db_query($qry))
				echo '<strong>system.tbl_benutzerrolle: '.$db->db_last_error().'</strong><br>';
			else
				echo 'system.tbl_benutzerrolle: Spalte insertvon, updatevon auf 32 Zeichen verlaengert<br>';
		}
	}

}

