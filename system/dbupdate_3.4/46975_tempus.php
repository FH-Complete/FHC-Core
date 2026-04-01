<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

// Kalender Tabelle fuer neues Tempus
if(!$result = @$db->db_query("SELECT kalender_id FROM lehre.tbl_kalender LIMIT 1"))
{
	$qry = "CREATE TABLE lehre.tbl_kalender (
		kalender_id bigserial NOT NULL,
		von timestamp NOT NULL,
		bis timestamp NOT NULL,
		typ character varying(32),
		status_kurzbz character varying(32),
		vorgaenger_kalender_id bigint,
		insertamum timestamp DEFAULT now(),
		insertvon character varying(32),
		updateamum timestamp DEFAULT now(),
		updatevon character varying(32),
		CONSTRAINT tbl_kalender_pk PRIMARY KEY (kalender_id)
	);

	COMMENT ON TABLE lehre.tbl_kalender IS 'Schedule Calendar Events';

	CREATE TABLE lehre.tbl_kalender_typ (
		typ character varying(32) NOT NULL,
		CONSTRAINT tbl_kalender_typ_pk PRIMARY KEY (typ)
	);

	COMMENT ON TABLE lehre.tbl_kalender_typ IS 'Type of Calendar Events';

	INSERT INTO lehre.tbl_kalender_typ (typ) VALUES (E'lehreinheit');
	INSERT INTO lehre.tbl_kalender_typ (typ) VALUES (E'reservierung');
	INSERT INTO lehre.tbl_kalender_typ (typ) VALUES (E'event');

	CREATE TABLE lehre.tbl_kalender_lehreinheit (
		lehreinheit_id integer NOT NULL,
		kalender_id bigint NOT NULL,
		CONSTRAINT tbl_kalender_lehreinheit_pk PRIMARY KEY (lehreinheit_id,kalender_id)
	);

	COMMENT ON TABLE lehre.tbl_kalender_lehreinheit IS 'Connects Calender Events to Courses';

	ALTER TABLE lehre.tbl_kalender_lehreinheit ADD CONSTRAINT tbl_lehreinheit_fk FOREIGN KEY (lehreinheit_id)
	REFERENCES lehre.tbl_lehreinheit (lehreinheit_id) MATCH FULL
	ON DELETE RESTRICT ON UPDATE CASCADE;

	CREATE TABLE lehre.tbl_kalender_ort (
		kalender_ort_id bigserial NOT NULL,
		location text,
		ort_kurzbz character varying(32),
		kalender_id bigint,
		CONSTRAINT tbl_kalender_ort_pk PRIMARY KEY (kalender_ort_id)
	);

	COMMENT ON TABLE lehre.tbl_kalender_ort IS E'Connects one Calendar Entry to multiple Rooms';

	COMMENT ON COLUMN lehre.tbl_kalender_ort.location IS E'Text Description if not a physical inhouse Room (External Location, Conference Link, etc)';

	ALTER TABLE lehre.tbl_kalender_ort ADD CONSTRAINT tbl_kalender_fk FOREIGN KEY (kalender_id)
	REFERENCES lehre.tbl_kalender (kalender_id) MATCH FULL
	ON DELETE RESTRICT ON UPDATE CASCADE;

	ALTER TABLE lehre.tbl_kalender_ort ADD CONSTRAINT tbl_ort_fk FOREIGN KEY (ort_kurzbz)
	REFERENCES public.tbl_ort (ort_kurzbz) MATCH FULL
	ON DELETE RESTRICT ON UPDATE CASCADE;

	ALTER TABLE lehre.tbl_kalender_lehreinheit ADD CONSTRAINT tbl_kalender_fk FOREIGN KEY (kalender_id)
	REFERENCES lehre.tbl_kalender (kalender_id) MATCH FULL
	ON DELETE RESTRICT ON UPDATE CASCADE;

	CREATE TABLE lehre.tbl_kalender_status (
		status_kurzbz character varying(32) NOT NULL,
		bezeichnung text,
		bezeichnung_mehrsprachig character varying(255)[] NOT NULL,
		sort smallint,
		CONSTRAINT tbl_kalender_status_pk PRIMARY KEY (status_kurzbz)
	);

	COMMENT ON TABLE lehre.tbl_kalender_status IS 'Calender visibility Status';

	INSERT INTO lehre.tbl_kalender_status (status_kurzbz, bezeichnung, bezeichnung_mehrsprachig, sort) VALUES (E'planning', E'planning', E'{\"In Planung\", \"Planning\"}', 1);
	INSERT INTO lehre.tbl_kalender_status (status_kurzbz, bezeichnung, bezeichnung_mehrsprachig, sort) VALUES (E'tosync', E'tosync', E'{\"Zu synchronisieren\", \"To sync\"}', 2);
	INSERT INTO lehre.tbl_kalender_status (status_kurzbz, bezeichnung, bezeichnung_mehrsprachig, sort) VALUES (E'todelete', E'todelete', E'{\"Zu löschen\", \"To delete\"}', 3);
	INSERT INTO lehre.tbl_kalender_status (status_kurzbz, bezeichnung, bezeichnung_mehrsprachig, sort) VALUES (E'visible_lektor', E'Sichtbar für Lektoren', E'{\"Sichtbar für Lektoren\", \"Visible for lecturers\"}', 4);
	INSERT INTO lehre.tbl_kalender_status (status_kurzbz, bezeichnung, bezeichnung_mehrsprachig, sort) VALUES (E'deleted', E'deleted', E'{\"Gelöscht\", \"Deleted\"}', 5);
	INSERT INTO lehre.tbl_kalender_status (status_kurzbz, bezeichnung, bezeichnung_mehrsprachig, sort) VALUES (E'archived', E'archived', E'{\"Archiviert\", \"Archived\"}', 6);
	INSERT INTO lehre.tbl_kalender_status (status_kurzbz, bezeichnung, bezeichnung_mehrsprachig, sort) VALUES (E'visible_student', E'Sichtbar für Studierende', E'{\"Sichtbar für Studierende\", \"Visible for students\"}', 7);

	ALTER TABLE lehre.tbl_kalender ADD CONSTRAINT tbl_kalender_status_fk FOREIGN KEY (status_kurzbz)
	REFERENCES lehre.tbl_kalender_status (status_kurzbz) MATCH FULL
	ON DELETE RESTRICT ON UPDATE CASCADE;

	ALTER TABLE lehre.tbl_kalender ADD CONSTRAINT tbl_kalender_typ_fk FOREIGN KEY (typ)
	REFERENCES lehre.tbl_kalender_typ (typ) MATCH FULL
	ON DELETE RESTRICT ON UPDATE CASCADE;

	GRANT SELECT, UPDATE, INSERT, DELETE ON lehre.tbl_kalender to vilesci;
	GRANT SELECT, UPDATE, INSERT, DELETE ON lehre.tbl_kalender to web;

	GRANT SELECT, UPDATE, INSERT, DELETE ON lehre.tbl_kalender_status to vilesci;
	GRANT SELECT ON lehre.tbl_kalender_status to web;

	GRANT SELECT, UPDATE, INSERT, DELETE ON lehre.tbl_kalender_lehreinheit to vilesci;
	GRANT SELECT, UPDATE, INSERT, DELETE ON lehre.tbl_kalender_lehreinheit to web;

	GRANT SELECT, UPDATE, INSERT, DELETE ON lehre.tbl_kalender_ort to vilesci;
	GRANT SELECT, UPDATE, INSERT, DELETE ON lehre.tbl_kalender_ort to web;

	GRANT SELECT, UPDATE, INSERT, DELETE ON lehre.tbl_kalender_typ to vilesci;
	GRANT SELECT ON lehre.tbl_kalender_typ to web;

	CREATE TABLE sync.tbl_stundenplandev_kalender(
		stundenplandev_kalender_id bigserial NOT NULL,
		stundenplandev_id integer NOT NULL,
		kalender_id bigint NOT NULL,
		lastupdate timestamp,
		CONSTRAINT tbl_stundenplandev_kalender_pk PRIMARY KEY (stundenplandev_kalender_id)
	);

	GRANT SELECT, UPDATE, INSERT, DELETE ON sync.tbl_stundenplandev_kalender to vilesci;
	COMMENT ON TABLE sync.tbl_stundenplandev_kalender IS 'Migration from old Stundenplan to new Kalender Table';

	GRANT USAGE ON lehre.tbl_kalender_kalender_id_seq TO vilesci;
	GRANT USAGE ON lehre.tbl_kalender_kalender_id_seq TO web;
	GRANT USAGE ON sync.tbl_stundenplandev_kalender_stundenplandev_kalender_id_seq TO vilesci;
	GRANT USAGE ON lehre.tbl_kalender_ort_kalender_ort_id_seq TO vilesci;

 	CREATE INDEX idx_kalender_ort_kalender_id ON lehre.tbl_kalender_ort USING btree (kalender_id);
	CREATE INDEX idx_kalender_ort_kalender_id_ort_kurzbz ON lehre.tbl_kalender_ort USING btree (ort_kurzbz, kalender_id);
	CREATE INDEX idx_kalender_von ON lehre.tbl_kalender USING btree (von);
	";

	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_kalender: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>lehre.tbl_kalender: neue Tabellen hinzugefuegt';
}
