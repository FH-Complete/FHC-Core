<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');


if(!$result = @$db->db_query("SELECT 1 FROM campus.tbl_studierendenantrag_statustyp LIMIT 1"))
{
	$qry = "CREATE TABLE campus.tbl_studierendenantrag_statustyp (
			studierendenantrag_statustyp_kurzbz			VARCHAR(32) NOT NULL,
			bezeichnung									VARCHAR(128)[] NOT NULL,
			CONSTRAINT tbl_studierendenantrag_statustyp_pk PRIMARY KEY(studierendenantrag_statustyp_kurzbz)
		);

		GRANT SELECT, INSERT ON campus.tbl_studierendenantrag_statustyp TO vilesci;
		GRANT SELECT ON campus.tbl_studierendenantrag_statustyp TO web;";

	if(!$db->db_query($qry))
		echo '<strong>campus.tbl_studierendenantrag_statustyp: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>campus.tbl_studierendenantrag_statustyp: table created';
}

if($result = @$db->db_query("SELECT 1 FROM campus.tbl_studierendenantrag_statustyp WHERE studierendenantrag_statustyp_kurzbz = 'Erstellt' "))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO campus.tbl_studierendenantrag_statustyp
			(studierendenantrag_statustyp_kurzbz, bezeichnung)
			VALUES
			('Erstellt', '{\"Erstellt\",\"Created\"}'),
			('Genehmigt', '{\"Bestätigt\",\"Approved\"}'),
			('Abgelehnt', '{\"Abgelehnt\",\"Rejected\"}'),
			('Verzichtet', '{\"Verzichtet\",\"Pass\"}'),
			('Offen', '{\"Offen\",\"Reopened\"}'),
			('Zurueckgezogen', '{\"Zurückgezogen\",\"Cancelled\"}'),
			('Lvszugewiesen', '{\"Lvszugewiesen\",\"Lvsassigned\"}'),
			('EmailVersandt', '{\"Email Versandt\",\"Reminder Sent\"}'),
			('ErsteAufforderungVersandt', '{\"1.Aufforderung Versandt\",\"1st Request Sent\"}'),
			('ZweiteAufforderungVersandt', '{\"2.Aufforderung Versandt\",\"2nd Request Sent\"}'),
			('Beeinsprucht', '{\"Beeinsprucht\",\"Objected\"}'),
			('EinspruchAbgelehnt', '{\"Einspruch abgelehnt\",\"Objection denied\"}'),
			('Abgemeldet', '{\"Abgemeldet\",\"Deregistered\"}');
			";
		if (!$db->db_query($qry))
			echo '<strong>campus.tbl_studierendenantrag_statustyp (insert): '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>campus.tbl_studierendenantrag_statustyp: table prefilled';
	}
}
if($result = @$db->db_query("SELECT 1 FROM campus.tbl_studierendenantrag_statustyp WHERE studierendenantrag_statustyp_kurzbz = 'Abgemeldet' "))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO campus.tbl_studierendenantrag_statustyp
			(studierendenantrag_statustyp_kurzbz, bezeichnung)
			VALUES
			('Abgemeldet', '{\"Abgemeldet\",\"Deregistered\"}');
			";
		if (!$db->db_query($qry))
			echo '<strong>campus.tbl_studierendenantrag_statustyp (insert): '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>campus.tbl_studierendenantrag_statustyp: "Abgemeldet" added';
	}
}

if(!$result = @$db->db_query("SELECT 1 FROM campus.tbl_studierendenantrag LIMIT 1"))
{
	$qry = "CREATE TABLE campus.tbl_studierendenantrag (
			studierendenantrag_id						INTEGER NOT NULL,
			prestudent_id								INTEGER NOT NULL,
			studiensemester_kurzbz						VARCHAR(32) NOT NULL,
			datum										TIMESTAMP NULL,
			typ											VARCHAR(32) NOT NULL,
			insertamum									TIMESTAMP	DEFAULT NOW(),
			insertvon									VARCHAR(32) NOT NULL,
			datum_wiedereinstieg						TIMESTAMP NULL,
			grund										TEXT NULL,
			dms_id										INTEGER NULL,
			CONSTRAINT tbl_studierendenantrag_pk PRIMARY KEY(studierendenantrag_id)
		);
		CREATE SEQUENCE campus.tbl_studierendenantrag_studierendenantrag_id_seq
			 INCREMENT BY 1
			 NO MAXVALUE
			 NO MINVALUE
			 CACHE 1;
		ALTER TABLE campus.tbl_studierendenantrag ALTER COLUMN studierendenantrag_id SET DEFAULT nextval('campus.tbl_studierendenantrag_studierendenantrag_id_seq');

		GRANT SELECT, INSERT ON campus.tbl_studierendenantrag TO vilesci;
		GRANT SELECT, INSERT ON campus.tbl_studierendenantrag TO web;
		GRANT SELECT, UPDATE ON campus.tbl_studierendenantrag_studierendenantrag_id_seq TO vilesci;
		GRANT SELECT, UPDATE ON campus.tbl_studierendenantrag_studierendenantrag_id_seq TO web;";

	if(!$db->db_query($qry))
		echo '<strong>campus.tbl_studierendenantrag: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>campus.tbl_studierendenantrag: table created';
}

if(!$result = @$db->db_query("SELECT 1 FROM campus.tbl_studierendenantrag_status LIMIT 1"))
{
	$qry = "CREATE TABLE campus.tbl_studierendenantrag_status (
			studierendenantrag_status_id				INTEGER NOT NULL,
			studierendenantrag_id						INTEGER NOT NULL,
			studierendenantrag_statustyp_kurzbz			VARCHAR(32) NOT NULL,
			insertamum									TIMESTAMP            DEFAULT NOW(),
			insertvon									VARCHAR(32) NOT NULL,
			grund										TEXT NULL,
			CONSTRAINT tbl_studierendenantrag_status_pk PRIMARY KEY(studierendenantrag_status_id),
			CONSTRAINT tbl_studierendenantrag_fk FOREIGN KEY (studierendenantrag_id) REFERENCES campus.tbl_studierendenantrag(studierendenantrag_id) ON UPDATE CASCADE ON DELETE RESTRICT,
			CONSTRAINT tbl_studierendenantrag_statustyp_fk FOREIGN KEY (studierendenantrag_statustyp_kurzbz) REFERENCES campus.tbl_studierendenantrag_statustyp(studierendenantrag_statustyp_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT
		);
		CREATE SEQUENCE campus.tbl_studierendenantrag_status_studierendenantrag_status_id_seq
			 INCREMENT BY 1
			 NO MAXVALUE
			 NO MINVALUE
			 CACHE 1;
		ALTER TABLE campus.tbl_studierendenantrag_status ALTER COLUMN studierendenantrag_status_id SET DEFAULT nextval('campus.tbl_studierendenantrag_status_studierendenantrag_status_id_seq');

		GRANT SELECT, INSERT, DELETE ON campus.tbl_studierendenantrag_status TO vilesci;
		GRANT SELECT, INSERT, DELETE ON campus.tbl_studierendenantrag_status TO web;
		GRANT SELECT, UPDATE ON campus.tbl_studierendenantrag_status_studierendenantrag_status_id_seq TO vilesci;
		GRANT SELECT, UPDATE ON campus.tbl_studierendenantrag_status_studierendenantrag_status_id_seq TO web;";

	if(!$db->db_query($qry))
		echo '<strong>campus.tbl_studierendenantrag_status: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>campus.tbl_studierendenantrag_status: table created';
}

if(!$result = @$db->db_query("SELECT 1 FROM campus.tbl_studierendenantrag_lehrveranstaltung LIMIT 1"))
{
	$qry = "CREATE TABLE campus.tbl_studierendenantrag_lehrveranstaltung (
			studierendenantrag_lehrveranstaltung_id		INTEGER NOT NULL,
			studierendenantrag_id						INTEGER NOT NULL,
			lehrveranstaltung_id						INTEGER NOT NULL,
			studiensemester_kurzbz						VARCHAR(16) NOT NULL,
			note										SMALLINT NOT NULL,
			anmerkung									TEXT NULL,
			insertamum									TIMESTAMP            DEFAULT NOW(),
			insertvon									VARCHAR(32) NOT NULL,
			CONSTRAINT tbl_studierendenantrag_lehrveranstaltung_pk PRIMARY KEY(studierendenantrag_lehrveranstaltung_id),
			CONSTRAINT tbl_studiensemester_fk FOREIGN KEY (studiensemester_kurzbz) REFERENCES public.tbl_studiensemester(studiensemester_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT,
			CONSTRAINT tbl_note_fk FOREIGN KEY (note) REFERENCES lehre.tbl_note(note) ON UPDATE CASCADE ON DELETE RESTRICT,
			CONSTRAINT tbl_studierendenantrag_fk FOREIGN KEY (studierendenantrag_id) REFERENCES campus.tbl_studierendenantrag(studierendenantrag_id) ON UPDATE CASCADE ON DELETE RESTRICT
		);
		CREATE SEQUENCE campus.tbl_studierendenantrag_lehrveranstaltung_studierendenantrag_lehrveranstaltung_id_seq
			 INCREMENT BY 1
			 NO MAXVALUE
			 NO MINVALUE
			 CACHE 1;
		ALTER TABLE campus.tbl_studierendenantrag_lehrveranstaltung ALTER COLUMN studierendenantrag_lehrveranstaltung_id SET DEFAULT nextval('campus.tbl_studierendenantrag_lehrveranstaltung_studierendenantrag_lehrveranstaltung_id_seq');

		GRANT SELECT, INSERT, DELETE ON campus.tbl_studierendenantrag_lehrveranstaltung TO vilesci;
		GRANT SELECT, INSERT ON campus.tbl_studierendenantrag_lehrveranstaltung TO web;
		GRANT SELECT, UPDATE ON campus.tbl_studierendenantrag_lehrveranstaltung_studierendenantrag_lehrveranstaltung_id_seq TO vilesci;
		GRANT SELECT, UPDATE ON campus.tbl_studierendenantrag_lehrveranstaltung_studierendenantrag_lehrveranstaltung_id_seq TO web;";

	if(!$db->db_query($qry))
		echo '<strong>campus.tbl_studierendenantrag_lehrveranstaltung: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>campus.tbl_studierendenantrag_lehrveranstaltung: table created';
}

if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz = 'student/studierendenantrag';"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES('student/studierendenantrag', 'Berechtigung für Bearbeiten Studierendenanträge');";

		if(!$db->db_query($qry))
			echo '<strong>system.tbl_berechtigung '.$db->db_last_error().'</strong><br>';
		else
			echo ' system.tbl_berechtigung: Added permission for student/studierendenantrag<br>';
	}
}

if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz = 'student/antragfreigabe';"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES('student/antragfreigabe', 'Berechtigung für Freigabe der Studierendenanträge');";

		if(!$db->db_query($qry))
			echo '<strong>system.tbl_berechtigung '.$db->db_last_error().'</strong><br>';
		else
			echo ' system.tbl_berechtigung: Added permission for student/antragfreigabe<br>';
	}
}

if (!$result = @$db->db_query("SELECT campus.get_status_studierendenantrag(0)"))
{
	$qry = 'CREATE FUNCTION campus.get_status_studierendenantrag(integer) RETURNS character varying
    LANGUAGE plpgsql
    STABLE
    RETURNS NULL ON NULL INPUT
    AS $_$
        DECLARE i_studierendenantrag_id ALIAS FOR $1;
        DECLARE rec RECORD;
        BEGIN
            SELECT INTO rec studierendenantrag_statustyp_kurzbz
            FROM campus.tbl_studierendenantrag_status
            WHERE studierendenantrag_id=i_studierendenantrag_id
            ORDER BY insertamum desc
            LIMIT 1;

        RETURN rec.studierendenantrag_statustyp_kurzbz;
        END;
        $_$;

	ALTER FUNCTION campus.get_status_studierendenantrag(integer) OWNER TO fhcomplete;';

	if(!$db->db_query($qry))
		echo '<strong>campus.get_status_studierendenantrag(integer): '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>campus.get_status_studierendenantrag(integer): function created';
}
elseif ($result = @$db->db_query("SELECT 1 FROM pg_proc WHERE proname='get_status_studierendenantrag' AND provolatile='s'"))
{
	if ($db->db_num_rows($result) == 0) {
		$qry = 'ALTER FUNCTION campus.get_status_studierendenantrag(integer) STABLE;';
		$qry .= 'ALTER FUNCTION campus.get_status_studierendenantrag(integer) RETURNS NULL ON NULL INPUT;';

		if(!$db->db_query($qry))
			echo '<strong>campus.get_status_studierendenantrag(integer): '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>campus.get_status_studierendenantrag(integer): function updated';
	}
}

if (!$result = @$db->db_query("SELECT campus.get_status_id_studierendenantrag(0)"))
{
	$qry = 'CREATE FUNCTION campus.get_status_id_studierendenantrag(integer) RETURNS integer
    LANGUAGE plpgsql
    STABLE
    RETURNS NULL ON NULL INPUT
    AS $_$
        DECLARE i_studierendenantrag_id ALIAS FOR $1;
        DECLARE rec RECORD;
        BEGIN
            SELECT INTO rec studierendenantrag_status_id
            FROM campus.tbl_studierendenantrag_status
            WHERE studierendenantrag_id=i_studierendenantrag_id
            ORDER BY insertamum desc
            LIMIT 1;

        RETURN rec.studierendenantrag_status_id;
        END;
        $_$;

	ALTER FUNCTION campus.get_status_id_studierendenantrag(integer) OWNER TO fhcomplete;';

	if(!$db->db_query($qry))
		echo '<strong>campus.get_status_id_studierendenantrag(integer): '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>campus.get_status_id_studierendenantrag(integer): function created';
}
elseif ($result = @$db->db_query("SELECT 1 FROM pg_proc WHERE proname='get_status_id_studierendenantrag' AND provolatile='s'"))
{
	if ($db->db_num_rows($result) == 0) {
		$qry = 'ALTER FUNCTION campus.get_status_id_studierendenantrag(integer) STABLE;';
		$qry .= 'ALTER FUNCTION campus.get_status_id_studierendenantrag(integer) RETURNS NULL ON NULL INPUT;';

		if(!$db->db_query($qry))
			echo '<strong>campus.get_status_id_studierendenantrag(integer): '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>campus.get_status_id_studierendenantrag(integer): function updated';
	}
}

if (!$result = @$db->db_query("SELECT public.get_absem_prestudent(0, null)"))
{
	$qry = 'CREATE FUNCTION public.get_absem_prestudent(integer, character varying) RETURNS integer
    LANGUAGE plpgsql
    STABLE
    AS $_$
        DECLARE i_prestudent_id ALIAS FOR $1;
        DECLARE cv_studiensemester_kurzbz ALIAS FOR $2;
        DECLARE rec RECORD;
        BEGIN
            IF (cv_studiensemester_kurzbz IS NULL) THEN
                SELECT INTO rec ausbildungssemester
                FROM public.tbl_prestudentstatus
                WHERE prestudent_id=i_prestudent_id
                ORDER BY datum desc,insertamum desc, ext_id desc
                LIMIT 1;
            ELSE
                SELECT INTO rec ausbildungssemester
                FROM tbl_prestudentstatus
                WHERE prestudent_id=i_prestudent_id AND studiensemester_kurzbz=cv_studiensemester_kurzbz
                ORDER BY datum desc,insertamum desc, ext_id desc
                LIMIT 1;
            END IF;

        RETURN rec.ausbildungssemester;
        END;
        $_$;

	ALTER FUNCTION public.get_absem_prestudent(integer, character varying) OWNER TO fhcomplete;';

	if(!$db->db_query($qry))
		echo '<strong>public.get_absem_prestudent(integer, character varying): '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>public.get_absem_prestudent(integer, character varying): function created';
}
elseif ($result = @$db->db_query("SELECT 1 FROM pg_proc WHERE proname='get_absem_prestudent' AND provolatile='s'"))
{
	if ($db->db_num_rows($result) == 0) {
		$qry = 'ALTER FUNCTION public.get_absem_prestudent(integer, character varying) STABLE;';

		if(!$db->db_query($qry))
			echo '<strong>public.get_absem_prestudent(integer, character varying): '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>public.get_absem_prestudent(integer, character varying): function updated';
	}
}
if (!$result = @$db->db_query("SELECT public.get_stdsem_prestudent(0, null)"))
{
	$qry = 'CREATE FUNCTION public.get_stdsem_prestudent(integer, character varying) RETURNS character varying
    LANGUAGE plpgsql
    STABLE
    AS $_$
        DECLARE i_prestudent_id ALIAS FOR $1;
        DECLARE cv_studiensemester_kurzbz ALIAS FOR $2;
        DECLARE rec RECORD;
        BEGIN
            IF (cv_studiensemester_kurzbz IS NULL) THEN
                SELECT INTO rec studiensemester_kurzbz
                FROM public.tbl_prestudentstatus
                WHERE prestudent_id=i_prestudent_id
                ORDER BY datum desc,insertamum desc, ext_id desc
                LIMIT 1;
            ELSE
                SELECT INTO rec studiensemester_kurzbz
                FROM tbl_prestudentstatus
                WHERE prestudent_id=i_prestudent_id AND studiensemester_kurzbz=cv_studiensemester_kurzbz
                ORDER BY datum desc,insertamum desc, ext_id desc
                LIMIT 1;
            END IF;

        RETURN rec.studiensemester_kurzbz;
        END;
        $_$;

	ALTER FUNCTION public.get_stdsem_prestudent(integer, character varying) OWNER TO fhcomplete;';

	if(!$db->db_query($qry))
		echo '<strong>public.get_stdsem_prestudent(integer, character varying): '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>public.get_stdsem_prestudent(integer, character varying): function created';
}
elseif ($result = @$db->db_query("SELECT 1 FROM pg_proc WHERE proname='get_stdsem_prestudent' AND provolatile='s'"))
{
	if ($db->db_num_rows($result) == 0) {
		$qry = 'ALTER FUNCTION public.get_stdsem_prestudent(integer, character varying) STABLE;';

		if(!$db->db_query($qry))
			echo '<strong>public.get_stdsem_prestudent(integer, character varying): '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>public.get_stdsem_prestudent(integer, character varying): function updated';
	}
}

if ($result = @$db->db_query("SELECT 1 FROM pg_proc WHERE proname='get_rolle_prestudent' AND provolatile='s'"))
{
	if ($db->db_num_rows($result) == 0) {
		$qry = 'ALTER FUNCTION public.get_rolle_prestudent(integer, character varying) STABLE;';

		if(!$db->db_query($qry))
			echo '<strong>public.get_rolle_prestudent(integer, character varying): '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>public.get_rolle_prestudent(integer, character varying): function updated';
	}
}

if($result = @$db->db_query("SELECT 1 FROM public.tbl_status_grund WHERE statusgrund_kurzbz = 'abbrecherStgl';"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO public.tbl_status_grund(statusgrund_kurzbz, status_kurzbz, aktiv, beschreibung, bezeichnung_mehrsprachig) VALUES('abbrecherStgl', 'Abbrecher', TRUE, '{\"durch Stgl\", \"by Course Director\"}', '{\"durch Stgl\", \"by Course Director\"}');";

		if(!$db->db_query($qry))
			echo '<strong>public.tbl_status_grund '.$db->db_last_error().'</strong><br>';
		else
			echo ' public.tbl_status_grund: Added Statusgrund abbrecherStgl for Abbrecher<br>';
	}
}

if($result = @$db->db_query("SELECT 1 FROM public.tbl_status_grund WHERE statusgrund_kurzbz = 'abbrecherStud';"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO public.tbl_status_grund(statusgrund_kurzbz, status_kurzbz, aktiv, beschreibung, bezeichnung_mehrsprachig) VALUES('abbrecherStud', 'Abbrecher', TRUE, '{\"durch Stud\", \"by Student\"}', '{\"durch Stud\", \"by Student\"}');";

		if(!$db->db_query($qry))
			echo '<strong>public.tbl_status_grund '.$db->db_last_error().'</strong><br>';
		else
			echo ' public.tbl_status_grund: Added Statusgrund abbrecherStud for Abbrecher<br>';
	}
}

if($result = @$db->db_query("SELECT 1 FROM public.tbl_status_grund WHERE statusgrund_kurzbz = 'preabbrecher';"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO public.tbl_status_grund(statusgrund_kurzbz, status_kurzbz, aktiv, beschreibung, bezeichnung_mehrsprachig) VALUES('preabbrecher', 'Student', TRUE, '{\"Pre-Abbrecher\", \"Pre-Aborted\"}', '{\"Pre-Abbrecher\", \"Pre-Aborted\"}');";

		if(!$db->db_query($qry))
			echo '<strong>public.tbl_status_grund '.$db->db_last_error().'</strong><br>';
		else
			echo ' public.tbl_status_grund: Added Statusgrund pre-abbrecher for Student<br>';
	}
}
