<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');


if (!$result = @$db->db_query("SELECT public.get_orgform_prestudent(0, null)"))
{
	$qry = 'CREATE FUNCTION public.get_orgform_prestudent(integer, character varying) RETURNS character varying
	LANGUAGE plpgsql
	STABLE
	AS $_$
		DECLARE i_prestudent_id ALIAS FOR $1;
		DECLARE cv_studiensemester_kurzbz ALIAS FOR $2;
		DECLARE rec RECORD;
		BEGIN
			IF (cv_studiensemester_kurzbz IS NULL) THEN
				SELECT INTO rec COALESCE(
					tbl_studienplan.orgform_kurzbz,
					tbl_prestudentstatus.orgform_kurzbz,
					tbl_studiengang.orgform_kurzbz
				) AS orgform_kurzbz
				FROM public.tbl_prestudentstatus
				JOIN lehre.tbl_studienplan USING (studienplan_id)
				JOIN public.tbl_prestudent USING (prestudent_id)
				JOIN public.tbl_studiengang USING (studiengang_kz)
				WHERE tbl_prestudentstatus.prestudent_id = i_prestudent_id
				ORDER BY
					tbl_prestudentstatus.datum desc,
					tbl_prestudentstatus.insertamum desc,
					tbl_prestudentstatus.ext_id desc
				LIMIT 1;
			ELSE
				SELECT INTO rec COALESCE(
					tbl_studienplan.orgform_kurzbz,
					tbl_prestudentstatus.orgform_kurzbz,
					tbl_studiengang.orgform_kurzbz
				) AS orgform_kurzbz
				FROM public.tbl_prestudentstatus
				JOIN lehre.tbl_studienplan USING (studienplan_id)
				JOIN public.tbl_prestudent USING (prestudent_id)
				JOIN public.tbl_studiengang USING (studiengang_kz)
				WHERE tbl_prestudentstatus.prestudent_id = i_prestudent_id
				AND tbl_prestudentstatus.studiensemester_kurzbz = cv_studiensemester_kurzbz
				ORDER BY
					tbl_prestudentstatus.datum desc,
					tbl_prestudentstatus.insertamum desc,
					tbl_prestudentstatus.ext_id desc
				LIMIT 1;
			END IF;

		RETURN rec.orgform_kurzbz;
		END;
		$_$;

	ALTER FUNCTION public.get_orgform_prestudent(integer, character varying) OWNER TO fhcomplete;';

	if(!$db->db_query($qry))
		echo '<strong>public.get_orgform_prestudent(integer, character varying): '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>public.get_orgform_prestudent(integer, character varying): function created';
}

// public.vw_prestudentstatus use get_orgform_prestudent function
if($result = $db->db_query("SELECT view_definition FROM information_schema.views WHERE table_schema='public' AND table_name='vw_prestudentstatus'"))
{
	if($row = $db->db_fetch_object($result))
	{
		if(!mb_stristr($row->view_definition, 'get_orgform_prestudent'))
		{
			$qry = "
			CREATE OR REPLACE VIEW public.vw_prestudentstatus AS
			SELECT tbl_prestudent.prestudent_id,
				tbl_person.person_id,
				tbl_person.staatsbuergerschaft,
				tbl_person.geburtsnation,
				tbl_person.sprache,
				tbl_person.anrede,
				tbl_person.titelpost,
				tbl_person.titelpre,
				tbl_person.nachname,
				tbl_person.vorname,
				tbl_person.vornamen,
				tbl_person.gebdatum,
				tbl_person.gebort,
				tbl_person.gebzeit,
				tbl_person.foto,
				tbl_person.homepage,
				tbl_person.svnr,
				tbl_person.ersatzkennzeichen,
				tbl_person.familienstand,
				tbl_person.geschlecht,
				tbl_person.anzahlkinder,
				tbl_person.aktiv,
				tbl_person.bundesland_code,
				tbl_person.kompetenzen,
				tbl_person.kurzbeschreibung,
				tbl_person.zugangscode,
				tbl_person.foto_sperre,
				tbl_person.matr_nr,
				tbl_prestudent.aufmerksamdurch_kurzbz,
				tbl_prestudent.studiengang_kz,
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
				(SELECT
							COALESCE(anmeldedatum, tbl_rt_person.insertamum::date)
						FROM
							public.tbl_rt_person
							JOIN public.tbl_reihungstest ON(rt_id=reihungstest_id)
							JOIN lehre.tbl_studienplan USING(studienplan_id)
							JOIN lehre.tbl_studienordnung USING(studienordnung_id)
						WHERE
							person_id=tbl_prestudent.person_id
							AND tbl_reihungstest.studiensemester_kurzbz=prestudentstatus.studiensemester_kurzbz
							AND tbl_studienordnung.studiengang_kz=tbl_prestudent.studiengang_kz
						ORDER BY anmeldedatum DESC, tbl_rt_person.insertamum DESC limit 1
					) as anmeldungreihungstest,
				tbl_prestudent.reihungstestangetreten,
				tbl_prestudent.rt_gesamtpunkte,
				tbl_prestudent.bismelden,
				tbl_prestudent.anmerkung,
				tbl_prestudent.dual,
				tbl_prestudent.rt_punkte1,
				tbl_prestudent.rt_punkte2,
				tbl_prestudent.ausstellungsstaat,
				tbl_prestudent.rt_punkte3,
				tbl_prestudent.zgvdoktor_code,
				tbl_prestudent.zgvdoktorort,
				tbl_prestudent.zgvdoktordatum,
				tbl_prestudent.mentor,
				prestudentstatus.status_kurzbz,
				prestudentstatus.studiensemester_kurzbz,
				prestudentstatus.ausbildungssemester,
				prestudentstatus.datum,
				prestudentstatus.insertamum,
				prestudentstatus.insertvon,
				prestudentstatus.updateamum,
				prestudentstatus.updatevon,
				public.get_orgform_prestudent(
					prestudentstatus.prestudent_id,
					prestudentstatus.studiensemester_kurzbz
				)::character varying(3) AS orgform_kurzbz,
				prestudentstatus.studienplan_id,
				prestudentstatus.bestaetigtam,
				prestudentstatus.bestaetigtvon,
				prestudentstatus.fgm,
				prestudentstatus.faktiv,
				tbl_studiengang.kurzbz,
				tbl_studiengang.kurzbzlang,
				tbl_studiengang.typ,
				tbl_studiensemester.start,
				tbl_studiensemester.ende,
				tbl_studiensemester.studienjahr_kurzbz,
				substr(tbl_studiensemester.studiensemester_kurzbz::text, 3) || lower(substr(tbl_studiensemester.studiensemester_kurzbz::text, 1, 1)) AS studiensemester,
				    CASE
				        WHEN tbl_studiengang.typ = 'b'::bpchar AND tbl_prestudent.zgv_code IS NOT NULL OR tbl_studiengang.typ = 'm'::bpchar AND tbl_prestudent.zgvmas_code IS NOT NULL OR tbl_studiengang.typ = 'd'::bpchar AND tbl_prestudent.zgvdoktor_code IS NOT NULL THEN true
				        ELSE false
				    END AS zgv,
				    CASE
				        WHEN tbl_prestudentstatus.prestudent_id IS NULL THEN false
				        ELSE true
				    END AS student,
				date_part('week'::text, prestudentstatus.datum) AS kw,
				tbl_prestudent.priorisierung
				FROM public.tbl_person
				 JOIN public.tbl_prestudent USING (person_id)
				 JOIN public.tbl_prestudentstatus prestudentstatus USING (prestudent_id)
				 JOIN public.tbl_studiengang USING (studiengang_kz)
				 JOIN public.tbl_studiensemester USING (studiensemester_kurzbz)
				 LEFT JOIN public.tbl_prestudentstatus ON tbl_prestudentstatus.prestudent_id = prestudentstatus.prestudent_id AND tbl_prestudentstatus.studiensemester_kurzbz::text = prestudentstatus.studiensemester_kurzbz::text AND tbl_prestudentstatus.status_kurzbz::text = 'Student'::text;

			GRANT SELECT ON public.vw_prestudentstatus TO vilesci;
			GRANT SELECT ON public.vw_prestudentstatus TO web;
			";

			if(!$db->db_query($qry))
				echo '<strong>public.vw_prestudentstatus:'.$db->db_last_error().'</strong><br>';
			else
				echo '<br>public.vw_prestudentstatus adapted to use get_orgform_prestudent';
		}
	}
}
