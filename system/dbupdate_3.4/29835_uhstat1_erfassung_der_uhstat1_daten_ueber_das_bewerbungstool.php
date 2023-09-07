<?php

if (! defined('DB_NAME')) exit('No direct script access allowed');


if (!$result = @$db->db_query('SELECT 1 FROM bis.tbl_abschluss LIMIT 1'))
{
	$qry = "CREATE TABLE bis.tbl_abschluss
			(
				ausbildung_code integer NOT NULL,
				abschluss_bez varchar(128),
				bezeichnung character varying(128)[],
				aktiv boolean NOT NULL DEFAULT true,
				in_oesterreich boolean,
				CONSTRAINT pk_tbl_abschluss PRIMARY KEY (ausbildung_code)
			);

			COMMENT ON TABLE bis.tbl_abschluss IS 'Key-Table of graduation';
			COMMENT ON COLUMN bis.tbl_abschluss.aktiv IS 'Shows wether graduation is still valid.';
			COMMENT ON COLUMN bis.tbl_abschluss.in_oesterreich IS 'Shows if graduation was obtained in Austria.';

			GRANT SELECT ON bis.tbl_abschluss TO web;
			GRANT SELECT, UPDATE, INSERT, DELETE ON bis.tbl_abschluss TO vilesci;

			-- prefill values
			INSERT INTO bis.tbl_abschluss(ausbildung_code, abschluss_bez, bezeichnung, in_oesterreich) VALUES(110, 'Pflichtschule', '{\"Pflichtschule (mit/ohne Abschluss)\", \"Compulsory school (Completed/not completed)\"}', true);
			INSERT INTO bis.tbl_abschluss(ausbildung_code, abschluss_bez, bezeichnung, in_oesterreich) VALUES(121, 'Lehre', '{\"Lehre\", \"Apprenticeship\"}', true);
			INSERT INTO bis.tbl_abschluss(ausbildung_code, abschluss_bez, bezeichnung, in_oesterreich) VALUES(122, 'Mittlere Schule ohne Matura', '{\"Mittlere Schule ohne Matura (z.B. Handelsschule, Fachschule)\", \"School for intermediate vocational education (without university entrance qualification)\"}', true);
			INSERT INTO bis.tbl_abschluss(ausbildung_code, abschluss_bez, bezeichnung, in_oesterreich) VALUES(123, 'Meisterprüfung', '{\"Meisterprüfung\", \"Master craftsman''s diploma\"}', true);
			INSERT INTO bis.tbl_abschluss(ausbildung_code, abschluss_bez, bezeichnung, in_oesterreich) VALUES(131, 'AHS', '{\"AHS (allgemein bildende höhere Schule)\", \"Academic secondary school\"}', true);
			INSERT INTO bis.tbl_abschluss(ausbildung_code, abschluss_bez, bezeichnung, in_oesterreich) VALUES(132, 'BHS', '{\"BHS (berufsbildende höhere Schule, z.B. HAK, HTL)\", \"College for higher vocational education\"}', true);
			INSERT INTO bis.tbl_abschluss(ausbildung_code, abschluss_bez, bezeichnung, in_oesterreich) VALUES(133, 'Sonstige Hochschulzugangsberechtigung', '{\"Sonstige Hochschulzugangsberechtigung (z.B. Berufsreifeprüfung)\", \"Other university entrance qualification (e.g. ''Berufsreifeprüfung'')\"}', true);
			INSERT INTO bis.tbl_abschluss(ausbildung_code, abschluss_bez, bezeichnung, in_oesterreich) VALUES(141, 'Akademie', '{\"Akademie (z.B. PÄDAK, SOZAK)\", \"Academy (for example PÄDAK, SOZAK)\"}', true);
			INSERT INTO bis.tbl_abschluss(ausbildung_code, abschluss_bez, bezeichnung, in_oesterreich) VALUES(142, 'Universität/Hochschule', '{\"Universität/Hochschule\", \"University/university of applied sciences/university college of teacher education\"}', true);
			INSERT INTO bis.tbl_abschluss(ausbildung_code, abschluss_bez, bezeichnung, in_oesterreich) VALUES(210, 'Pflichtschule', '{\"Pflichtschule (mit/ohne Abschluss)\", \"Compulsory school (Completed/not completed)\"}', false);
			INSERT INTO bis.tbl_abschluss(ausbildung_code, abschluss_bez, bezeichnung, in_oesterreich) VALUES(220, 'Ausbildung ohne Hochschulzugangsberechtigung', '{\"Lehre oder mittlere Schule ohne Matura/Ausbildung ohne Hochschulzugangsberechtigung\", \"Apprenticeship or school for intermediate vocational education (education without university entrance qualification)\"}', false);
			INSERT INTO bis.tbl_abschluss(ausbildung_code, abschluss_bez, bezeichnung, in_oesterreich) VALUES(230, 'Ausbildung mit Hochschulzugangsberechtigung', '{\"Höhere Schule mit Matura / Ausbildung mit Hochschulzugangsberechtigung (z.B. Abitur)\", \"Higher secondary school with university entrance qualification\"}', false);
			INSERT INTO bis.tbl_abschluss(ausbildung_code, abschluss_bez, bezeichnung, in_oesterreich) VALUES(240, 'Universität/Hochschule', '{\"Universität/Hochschule\", \"University/university of applied sciences/university college of teacher education\"}', false);
			INSERT INTO bis.tbl_abschluss(ausbildung_code, abschluss_bez, bezeichnung) VALUES(999, 'unbekannt', '{\"Ich weiß nicht, welchen Abschluss meine erziehungsberechtigte Person erlangt hat.\", \"I do not know what degree my legal guardian got.\"}');
		";

	if(!$db->db_query($qry))
		echo '<strong>bis.tbl_abschluss: '.$db->db_last_error().'</strong><br>';
	else
		echo ' bis.tbl_abschluss: Tabelle hinzugefuegt<br>';
}

if (!$result = @$db->db_query('SELECT 1 FROM bis.tbl_uhstat1daten LIMIT 1'))
{
	$qry = "CREATE SEQUENCE bis.tbl_uhstat1daten_uhstat1daten_id_seq
				INCREMENT BY 1
				NO MAXVALUE
				NO MINVALUE
				START WITH 1
				CACHE 1
				NO CYCLE;

			CREATE TABLE bis.tbl_uhstat1daten
			(
				uhstat1daten_id integer DEFAULT nextval('bis.tbl_uhstat1daten_uhstat1daten_id_seq'::regclass),
				mutter_geburtsstaat varchar(3),
				mutter_bildungsstaat varchar(3),
				mutter_geburtsjahr smallint,
				mutter_bildungmax integer,
				vater_geburtsstaat varchar(3),
				vater_bildungsstaat varchar(3),
				vater_geburtsjahr smallint,
				vater_bildungmax integer,
				person_id integer NOT NULL,
				insertamum timestamp without time zone DEFAULT now(),
				insertvon character varying(32),
				updateamum timestamp without time zone,
				updatevon character varying(32),
				CONSTRAINT pk_tbl_uhstat1daten PRIMARY KEY (uhstat1daten_id)
			);

			ALTER TABLE bis.tbl_uhstat1daten ADD CONSTRAINT fk_tbl_uhstat1daten_mutter_geburtsstaat FOREIGN KEY (mutter_geburtsstaat)
			REFERENCES bis.tbl_nation (nation_code) MATCH SIMPLE
			ON DELETE RESTRICT ON UPDATE CASCADE;

			ALTER TABLE bis.tbl_uhstat1daten ADD CONSTRAINT fk_tbl_uhstat1daten_mutter_bildungsstaat FOREIGN KEY (mutter_bildungsstaat)
			REFERENCES bis.tbl_nation (nation_code) MATCH SIMPLE
			ON DELETE RESTRICT ON UPDATE CASCADE;

			ALTER TABLE bis.tbl_uhstat1daten ADD CONSTRAINT fk_tbl_uhstat1daten_mutter_bildungmax FOREIGN KEY (mutter_bildungmax)
			REFERENCES bis.tbl_abschluss (ausbildung_code) MATCH SIMPLE
			ON DELETE RESTRICT ON UPDATE CASCADE;

			ALTER TABLE bis.tbl_uhstat1daten ADD CONSTRAINT fk_tbl_uhstat1daten_vater_geburtsstaat FOREIGN KEY (vater_geburtsstaat)
			REFERENCES bis.tbl_nation (nation_code) MATCH SIMPLE
			ON DELETE RESTRICT ON UPDATE CASCADE;

			ALTER TABLE bis.tbl_uhstat1daten ADD CONSTRAINT fk_tbl_uhstat1daten_vater_bildungsstaat FOREIGN KEY (vater_bildungsstaat)
			REFERENCES bis.tbl_nation (nation_code) MATCH SIMPLE
			ON DELETE RESTRICT ON UPDATE CASCADE;

			ALTER TABLE bis.tbl_uhstat1daten ADD CONSTRAINT fk_tbl_uhstat1daten_vater_bildungmax FOREIGN KEY (vater_bildungmax)
			REFERENCES bis.tbl_abschluss (ausbildung_code) MATCH SIMPLE
			ON DELETE RESTRICT ON UPDATE CASCADE;

			ALTER TABLE bis.tbl_uhstat1daten ADD CONSTRAINT fk_tbl_uhstat1daten_person_id FOREIGN KEY (person_id)
			REFERENCES public.tbl_person (person_id) MATCH SIMPLE
			ON DELETE RESTRICT ON UPDATE CASCADE;

			ALTER TABLE bis.tbl_uhstat1daten ADD CONSTRAINT uk_uhstat1daten_person_id UNIQUE(person_id);

			COMMENT ON TABLE bis.tbl_uhstat1daten IS 'UHSTAT1 data for a person (statistical data)';
			COMMENT ON COLUMN bis.tbl_uhstat1daten.mutter_geburtsstaat IS 'Birth country of mother of person';
			COMMENT ON COLUMN bis.tbl_uhstat1daten.mutter_bildungsstaat IS 'Education country of mother of person';
			COMMENT ON COLUMN bis.tbl_uhstat1daten.mutter_geburtsjahr IS 'Birth year of mother of person';
			COMMENT ON COLUMN bis.tbl_uhstat1daten.mutter_bildungmax IS 'Highest completed level of education of mother (code)';
			COMMENT ON COLUMN bis.tbl_uhstat1daten.vater_geburtsstaat IS 'Birth country of father of person';
			COMMENT ON COLUMN bis.tbl_uhstat1daten.vater_bildungsstaat IS 'Education country of father of person';
			COMMENT ON COLUMN bis.tbl_uhstat1daten.vater_geburtsjahr IS 'Birth year of father of person';
			COMMENT ON COLUMN bis.tbl_uhstat1daten.vater_bildungmax IS 'Highest completed level of education of father (code)';

			GRANT SELECT, UPDATE, INSERT, DELETE ON bis.tbl_uhstat1daten TO web;
			GRANT SELECT, UPDATE, INSERT, DELETE ON bis.tbl_uhstat1daten TO vilesci;
			GRANT SELECT, UPDATE ON bis.tbl_uhstat1daten_uhstat1daten_id_seq TO vilesci;
			GRANT SELECT, UPDATE ON bis.tbl_uhstat1daten_uhstat1daten_id_seq TO web;
		";

	if(!$db->db_query($qry))
		echo '<strong>bis.tbl_uhstat1daten: '.$db->db_last_error().'</strong><br>';
	else
		echo ' bis.tbl_uhstat1daten: Tabelle hinzugefuegt<br>';
}

// Add permission for managing UHSTAT1 data
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz = 'student/uhstat1daten_verwalten';"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES('student/uhstat1daten_verwalten', 'UHSTAT1 Daten verwalten');";

		if(!$db->db_query($qry))
			echo '<strong>system.tbl_berechtigung '.$db->db_last_error().'</strong><br>';
		else
			echo ' system.tbl_berechtigung: Added permission for student/uhstat1daten_verwalten<br>';
	}
}
