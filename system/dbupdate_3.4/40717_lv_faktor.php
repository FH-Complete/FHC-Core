<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');
if (!$result = @$db->db_query("SELECT 1 FROM lehre.tbl_lehrveranstaltung_faktor LIMIT 1"))
{
	$qry = "

		CREATE TABLE lehre.tbl_lehrveranstaltung_faktor
		(
			lehrveranstaltung_faktor_id integer NOT NULL,
			lehrveranstaltung_id integer NOT NULL,
			faktor numeric NOT NULL,
			studiensemester_kurzbz_von varchar(16) NOT NULL,
			studiensemester_kurzbz_bis varchar(16),
			insertamum timestamp DEFAULT NOW(),
			insertvon varchar(32),
			updateamum timestamp,
			updatevon varchar(32),
			CONSTRAINT tbl_lehrveranstaltung_faktor_pk PRIMARY KEY (lehrveranstaltung_faktor_id)
		);

		CREATE SEQUENCE lehre.lehrveranstaltung_faktor_id_seq
			START WITH 1
			INCREMENT BY 1
			NO MAXVALUE
			NO MINVALUE
			CACHE 1;

		ALTER TABLE lehre.tbl_lehrveranstaltung_faktor ALTER COLUMN lehrveranstaltung_faktor_id SET DEFAULT nextval('lehre.lehrveranstaltung_faktor_id_seq');
		ALTER TABLE lehre.tbl_lehrveranstaltung_faktor ADD CONSTRAINT fk_lehrveranstaltung_faktor_lehrveranstaltung_id FOREIGN KEY (lehrveranstaltung_id) REFERENCES lehre.tbl_lehrveranstaltung (lehrveranstaltung_id) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE lehre.tbl_lehrveranstaltung_faktor ADD CONSTRAINT fk_lehrveranstaltung_faktor_studiensemester_von FOREIGN KEY (studiensemester_kurzbz_von) REFERENCES public.tbl_studiensemester(studiensemester_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT;
		ALTER TABLE lehre.tbl_lehrveranstaltung_faktor ADD CONSTRAINT fk_lehrveranstaltung_faktor_studiensemester_bis FOREIGN KEY (studiensemester_kurzbz_bis) REFERENCES public.tbl_studiensemester(studiensemester_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT;
		
		GRANT SELECT ON lehre.tbl_lehrveranstaltung_faktor TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON lehre.tbl_lehrveranstaltung_faktor TO vilesci;
		GRANT SELECT ON lehre.lehrveranstaltung_faktor_id_seq TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON lehre.lehrveranstaltung_faktor_id_seq TO vilesci;
		";
	
	if (!$db->db_query($qry))
		echo '<strong>lehre.tbl_lehrveranstaltung_faktor: ' . $db->db_last_error() . '</strong><br>';
	else
		echo 'Tabelle: lehre.tbl_lehrveranstaltung_faktor erstellt!';

	//TODO ggf default wert in eine config
	$qry = "
		INSERT INTO lehre.tbl_lehrveranstaltung_faktor
		(lehrveranstaltung_id, faktor, studiensemester_kurzbz_von, insertvon)
		(
			SELECT lehrveranstaltung_id,
				2,
				(
					SELECT public.tbl_studiensemester.studiensemester_kurzbz
					FROM public.tbl_studiensemester
					ORDER BY start LIMIT 1
				),
				'checksystem'
			FROM lehre.tbl_lehrveranstaltung
			WHERE lehrtyp_kurzbz IN ('lv', 'tpl')
		);
	";
	if (!$db->db_query($qry))
		echo '<strong>lehre.tbl_lehrveranstaltung_faktor: ' . $db->db_last_error() . '</strong><br>';
	else
		echo 'Tabelle: lehre.tbl_lehrveranstaltung_faktor befüllt!';
}
