<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

if(!$result = @$db->db_query("SELECT prestudent_id FROM bis.tbl_bisio LIMIT 1"))
{
	$qry = "ALTER TABLE bis.tbl_bisio ADD COLUMN prestudent_id int;
			UPDATE bis.tbl_bisio
			SET prestudent_id = student.prestudent_id
			FROM tbl_student student
			WHERE tbl_bisio.student_uid = student.student_uid;";
	if(!$db->db_query($qry))
		echo '<strong>bis.tbl_bisio: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>bis.tbl_bisio: Spalte prestudent_id hinzugefuegt';
	
	$qry = "CREATE INDEX idx_bisio_prestudent_id ON bis.tbl_bisio USING btree (prestudent_id);";
	
	if(!$db->db_query($qry))
		echo '<strong>bis.tbl_bisio: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>bis.tbl_bisio: Index prestudent_id hinzugefuegt';

	$qry = "CREATE OR REPLACE view bis.vw_bisio
			(studiensemester_kurzbz, status_kurzbz, person_id, prestudent_id, student_uid, bisio_id,
			mobilitaetsprogramm_code, nation_code, von, bis, zweck_code, ort, universitaet, lehreinheit_id, matrikelnr,
			studiengang_kz, semester, aufmerksamdurch_kurzbz, berufstaetigkeit_code, ausbildungcode, zgv_code, zgvort,
			zgvdatum, zgvmas_code, zgvmaort, zgvmadatum, aufnahmeschluessel, facheinschlberuf, reihungstest_id,
			anmeldungreihungstest, reihungstestangetreten, rt_gesamtpunkte, bismelden, dual, rt_punkte1, rt_punkte2,
			ausstellungsstaat, rt_punkte3, zgvdoktor_code, zgvdoktorort, zgvdoktordatum, mentor, zgvnation,
			zgvmanation, zgvdoktornation, ausbildungssemester, datum, orgform_kurzbz, studienplan_id, bestaetigtam,
			 bestaetigtvon, fgm, faktiv, bewerbung_abgeschicktamum)
		as
		SELECT tbl_prestudentstatus.studiensemester_kurzbz,
			tbl_prestudentstatus.status_kurzbz,
			tbl_prestudent.person_id,
			tbl_prestudent.prestudent_id,
			tbl_student.student_uid,
			tbl_bisio.bisio_id,
			tbl_bisio.mobilitaetsprogramm_code,
			tbl_bisio.nation_code,
			tbl_bisio.von,
			tbl_bisio.bis,
			tbl_bisio.zweck_code,
			tbl_bisio.ort,
			tbl_bisio.universitaet,
			tbl_bisio.lehreinheit_id,
			tbl_student.matrikelnr,
			tbl_prestudent.studiengang_kz,
			tbl_student.semester,
			tbl_prestudent.aufmerksamdurch_kurzbz,
			tbl_prestudent.berufstaetigkeit_code,
			tbl_prestudent.ausbildungcode,
			tbl_prestudent.zgv_code,
			tbl_prestudent.zgvort,
			tbl_prestudent.zgvdatum,
			tbl_prestudent.zgvmas_code,
			tbl_prestudent.zgvmaort,
			tbl_prestudent.zgvmadatum,
			tbl_prestudent.aufnahmeschluessel,
			tbl_prestudent.facheinschlberuf,
			tbl_prestudent.reihungstest_id,
			tbl_prestudent.anmeldungreihungstest,
			tbl_prestudent.reihungstestangetreten,
			tbl_prestudent.rt_gesamtpunkte,
			tbl_prestudent.bismelden,
			tbl_prestudent.dual,
			tbl_prestudent.rt_punkte1,
			tbl_prestudent.rt_punkte2,
			tbl_prestudent.ausstellungsstaat,
			tbl_prestudent.rt_punkte3,
			tbl_prestudent.zgvdoktor_code,
			tbl_prestudent.zgvdoktorort,
			tbl_prestudent.zgvdoktordatum,
			tbl_prestudent.mentor,
			tbl_prestudent.zgvnation,
			tbl_prestudent.zgvmanation,
			tbl_prestudent.zgvdoktornation,
			tbl_prestudentstatus.ausbildungssemester,
			tbl_prestudentstatus.datum,
			tbl_prestudentstatus.orgform_kurzbz,
			tbl_prestudentstatus.studienplan_id,
			tbl_prestudentstatus.bestaetigtam,
			tbl_prestudentstatus.bestaetigtvon,
			tbl_prestudentstatus.fgm,
			tbl_prestudentstatus.faktiv,
			tbl_prestudentstatus.bewerbung_abgeschicktamum
		FROM bis.tbl_bisio
			JOIN tbl_student USING (prestudent_id)
			JOIN tbl_prestudent ON tbl_bisio.prestudent_id = tbl_prestudent.prestudent_id
			LEFT JOIN tbl_prestudentstatus ON tbl_prestudent.prestudent_id = tbl_prestudentstatus.prestudent_id AND
			(tbl_prestudentstatus.status_kurzbz::text = 'Incoming'::text OR
			tbl_prestudentstatus.status_kurzbz::text = 'Outgoing'::text);

		COMMENT ON VIEW bis.vw_bisio is 'Incoming Outgoing';
		
		ALTER TABLE bis.vw_bisio OWNER to fhcomplete;
		GRANT SELECT ON bis.vw_bisio TO web;
		GRANT SELECT ON bis.vw_bisio TO vilesci;
		";
	
	if (!$db->db_query($qry))
		echo '<strong>bis.vw_bisio: ' . $db->db_last_error() . '</strong><br />';
	else
		echo 'bis.vw_bisio: Join angepasst<br />';
}

if ($result = @$db->db_query("SELECT student_uid FROM bis.tbl_bisio LIMIT 1;"))
{
	$qry = "ALTER TABLE bis.tbl_bisio DROP COLUMN student_uid;";
	
	if (!$db->db_query($qry))
		echo '<strong>bis.tbl_bisio: ' . $db->db_last_error() . '</strong><br>';
	else
		echo 'bis.tbl_bisio: Spalte student_uid entfernt.<br>';
}



