<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH . '/libraries/MigrationLib.php';

class Migration_Reihungstestperson extends MigrationLib
{
	public function __construct()
	{
		parent::__construct();
	}

	public function up()
	{
		$this->startUP();

		$this->execQuery('CREATE OR REPLACE VIEW testtool.vw_auswertung_ablauf AS
			SELECT tbl_gebiet.gebiet_id,
			tbl_gebiet.bezeichnung AS gebiet,
			tbl_ablauf.reihung,
			tbl_gebiet.maxpunkte,
			tbl_pruefling.pruefling_id,
			tbl_pruefling.prestudent_id,
			tbl_person.vorname,
			tbl_person.nachname,
			tbl_person.gebdatum,
			tbl_person.geschlecht,
			tbl_pruefling.semester,
			upper(tbl_studiengang.typ::character varying(1)::text || tbl_studiengang.kurzbz::text) AS stg_kurzbz,
			tbl_studiengang.bezeichnung AS stg_bez,
			tbl_pruefling.registriert,
			tbl_pruefling.idnachweis,
			(
				SELECT sum(tbl_vorschlag.punkte) AS sum
				FROM
					testtool.tbl_vorschlag
					JOIN testtool.tbl_antwort USING (vorschlag_id)
					JOIN testtool.tbl_frage USING (frage_id)
				WHERE
					tbl_antwort.pruefling_id = tbl_pruefling.pruefling_id
					AND tbl_frage.gebiet_id = tbl_gebiet.gebiet_id
			) AS punkte,
			tbl_rt_person.rt_id as reihungstest_id,
			tbl_ablauf.gewicht
			FROM
				testtool.tbl_pruefling
				JOIN testtool.tbl_ablauf ON (tbl_ablauf.studiengang_kz = tbl_pruefling.studiengang_kz)
				JOIN testtool.tbl_gebiet USING (gebiet_id)
				JOIN public.tbl_prestudent USING (prestudent_id)
				JOIN public.tbl_person USING (person_id)
				JOIN public.tbl_rt_person USING (person_id)
				JOIN lehre.tbl_studienplan ON (tbl_studienplan.studienplan_id=tbl_rt_person.studienplan_id)
				JOIN lehre.tbl_studienordnung ON(tbl_studienordnung.studienordnung_id = tbl_studienplan.studienordnung_id)
				JOIN public.tbl_studiengang ON (tbl_prestudent.studiengang_kz = tbl_studiengang.studiengang_kz)
			WHERE
				NOT (tbl_ablauf.gebiet_id IN ( SELECT tbl_kategorie.gebiet_id FROM testtool.tbl_kategorie))
				AND tbl_studienordnung.studiengang_kz=tbl_pruefling.studiengang_kz;

		CREATE OR REPLACE VIEW testtool.vw_auswertung_kategorie_semester AS
		SELECT
			tbl_kategorie.kategorie_kurzbz,
			tbl_person.vorname,
			tbl_person.nachname,
			tbl_person.gebdatum,
			tbl_person.geschlecht,
			tbl_prestudent.prestudent_id,
			tbl_rt_person.rt_id as reihungstest_id,
			tbl_gebiet.gebiet_id,
			upper(tbl_studiengang.typ::character varying(1)::text || tbl_studiengang.kurzbz::text) AS stg_kurzbz,
			tbl_studiengang.bezeichnung AS stg_bez,
			tbl_pruefling.registriert,
			tbl_pruefling.idnachweis,
			tbl_pruefling.semester,
			tbl_pruefling.pruefling_id,
			(
				SELECT
					sum(tbl_vorschlag.punkte) AS sum
				FROM
					testtool.tbl_vorschlag
					JOIN testtool.tbl_antwort USING (vorschlag_id)
					JOIN testtool.tbl_frage USING (frage_id)
				WHERE
					tbl_antwort.pruefling_id = tbl_pruefling.pruefling_id
					AND tbl_frage.gebiet_id = tbl_gebiet.gebiet_id
					AND tbl_frage.kategorie_kurzbz::text = tbl_kategorie.kategorie_kurzbz::text
			) AS punkte
		FROM
			testtool.tbl_pruefling
			JOIN testtool.tbl_ablauf ON (tbl_ablauf.studiengang_kz = tbl_pruefling.studiengang_kz)
			JOIN testtool.tbl_gebiet USING (gebiet_id)
			JOIN testtool.tbl_kategorie USING (gebiet_id)
			JOIN public.tbl_prestudent USING (prestudent_id)
			JOIN public.tbl_person USING (person_id)
			JOIN public.tbl_studiengang ON (tbl_prestudent.studiengang_kz = tbl_studiengang.studiengang_kz)
			JOIN public.tbl_rt_person USING(person_id)
			JOIN lehre.tbl_studienplan ON(tbl_studienplan.studienplan_id=tbl_rt_person.studienplan_id)
			JOIN lehre.tbl_studienordnung ON(tbl_studienordnung.studienordnung_id = tbl_studienplan.studienordnung_id)
		WHERE
			tbl_studienordnung.studiengang_kz = tbl_pruefling.studiengang_kz;

		CREATE OR REPLACE VIEW testtool.vw_auswertung_kategorie AS
		SELECT
			tbl_kategorie.kategorie_kurzbz,
			tbl_person.vorname,
			tbl_person.nachname,
			tbl_person.gebdatum,
			tbl_person.geschlecht,
			tbl_prestudent.prestudent_id,
			tbl_rt_person.rt_id as reihungstest_id,
			tbl_gebiet.gebiet_id,
			upper(tbl_studiengang.typ::character varying(1)::text || tbl_studiengang.kurzbz::text) AS stg_kurzbz,
			tbl_studiengang.bezeichnung AS stg_bez,
			tbl_pruefling.registriert,
			tbl_pruefling.idnachweis,
			tbl_pruefling.semester,
			tbl_pruefling.pruefling_id,
 			(
				SELECT
					sum(tbl_vorschlag.punkte) AS sum
				FROM
					testtool.tbl_vorschlag
					JOIN testtool.tbl_antwort USING (vorschlag_id)
					JOIN testtool.tbl_frage USING (frage_id)
				WHERE
					tbl_antwort.pruefling_id = tbl_pruefling.pruefling_id
					AND tbl_frage.gebiet_id = tbl_gebiet.gebiet_id
					AND tbl_frage.kategorie_kurzbz::text = tbl_kategorie.kategorie_kurzbz::text
			) AS punkte
			FROM
				testtool.tbl_pruefling
				JOIN testtool.tbl_ablauf ON (tbl_ablauf.studiengang_kz = tbl_pruefling.studiengang_kz AND tbl_ablauf.semester = tbl_pruefling.semester)
				JOIN testtool.tbl_gebiet USING (gebiet_id)
				JOIN testtool.tbl_kategorie USING (gebiet_id)
				JOIN public.tbl_prestudent USING (prestudent_id)
				JOIN public.tbl_person USING (person_id)
				JOIN public.tbl_studiengang ON (tbl_prestudent.studiengang_kz = tbl_studiengang.studiengang_kz)
				JOIN public.tbl_rt_person USING(person_id)
				JOIN lehre.tbl_studienplan ON (tbl_studienplan.studienplan_id = tbl_rt_person.studienplan_id)
				JOIN lehre.tbl_studienordnung ON (tbl_studienordnung.studienordnung_id = tbl_studienplan.studienordnung_id)
			WHERE
				tbl_studienordnung.studiengang_kz=tbl_pruefling.studiengang_kz;

			CREATE OR REPLACE VIEW testtool.vw_auswertung AS
			SELECT
				tbl_gebiet.gebiet_id,
				tbl_gebiet.bezeichnung AS gebiet,
				tbl_gebiet.maxpunkte,
				tbl_pruefling.pruefling_id,
				tbl_pruefling.prestudent_id,
				tbl_person.vorname,
				tbl_person.nachname,
				tbl_person.gebdatum,
				tbl_person.geschlecht,
				tbl_pruefling.semester,
				upper(tbl_studiengang.typ::character varying(1)::text || tbl_studiengang.kurzbz::text) AS stg_kurzbz,
				tbl_studiengang.bezeichnung AS stg_bez,
				tbl_pruefling.registriert,
				tbl_pruefling.idnachweis,
				(
					SELECT
						sum(tbl_vorschlag.punkte) AS sum
					FROM
						testtool.tbl_vorschlag
						JOIN testtool.tbl_antwort USING (vorschlag_id)
						JOIN testtool.tbl_frage USING (frage_id)
					WHERE
						tbl_antwort.pruefling_id = tbl_pruefling.pruefling_id
						AND tbl_frage.gebiet_id = tbl_gebiet.gebiet_id
				) AS punkte,
				tbl_rt_person.rt_id as reihungstest_id,
				tbl_ablauf.gewicht,
				tbl_person.person_id
			FROM
				testtool.tbl_pruefling
				JOIN testtool.tbl_ablauf ON (tbl_ablauf.studiengang_kz = tbl_pruefling.studiengang_kz AND tbl_ablauf.semester = tbl_pruefling.semester)
				JOIN testtool.tbl_gebiet USING (gebiet_id)
				JOIN public.tbl_prestudent USING (prestudent_id)
				JOIN public.tbl_person USING (person_id)
				JOIN public.tbl_studiengang ON tbl_prestudent.studiengang_kz = tbl_studiengang.studiengang_kz
				JOIN public.tbl_rt_person USING (person_id)
				JOIN lehre.tbl_studienplan ON (tbl_studienplan.studienplan_id = tbl_rt_person.studienplan_id)
				JOIN lehre.tbl_studienordnung ON (tbl_studienordnung.studienordnung_id = tbl_studienplan.studienordnung_id)
			WHERE
				tbl_studienordnung.studiengang_kz = tbl_prestudent.studiengang_kz
				AND NOT (tbl_ablauf.gebiet_id IN ( SELECT tbl_kategorie.gebiet_id
					FROM testtool.tbl_kategorie));');

		$this->endUP();
	}

	public function down()
	{
		$this->startDown();
		$this->endDown();
	}
}