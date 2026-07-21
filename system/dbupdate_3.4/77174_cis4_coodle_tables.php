<?php

if (!defined('DB_NAME'))
	exit('No direct script access allowed');

if (!$result = @$db->db_query("SELECT 1 FROM campus.tbl_coodle_surveys LIMIT 1")) {
	$qry = "CREATE TABLE campus.tbl_coodle_surveys (
		id int NOT NULL,
		creator_uid varchar(33) NOT NULL,
		title varchar(255) NOT NULL,
		description varchar(1000) NOT NULL,
		timeslot_duration int NOT NULL,
		are_selections_anonymized boolean NOT NULL,
		are_participants_anonymized boolean NOT NULL,
		max_selections int NOT NULL,
		selected_timeslot_id int,
		ends_at timestamp NOT NULL,
		completed_at timestamp,
		canceled_at timestamp,
		created_at timestamp NOT NULL,
		updated_at timestamp NOT NULL,
		CONSTRAINT tbl_coodle_surveys_id_pk PRIMARY KEY(id),
		CONSTRAINT tbl_coodle_surveys_creator_uid_fk FOREIGN KEY (creator_uid) REFERENCES public.tbl_benutzer(uid)
		);";

	if (!$db->db_query($qry))
		echo '<strong>campus.tbl_coodle_surveys: ' . $db->db_last_error() . '</strong><br>';
	else
		echo '<br>campus.tbl_coodle_surveys: table created';


	$db->db_query('CREATE SEQUENCE IF NOT EXISTS campus.seq_tbl_coodle_surveys_id
     INCREMENT BY 1
     NO MAXVALUE
     NO MINVALUE
     CACHE 1;');

	$db->db_query("ALTER TABLE campus.tbl_coodle_surveys ALTER COLUMN id SET DEFAULT nextval('campus.seq_tbl_coodle_surveys_id');");


	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE campus.tbl_coodle_surveys TO web;';
	if (!$db->db_query($qry))
		echo '<strong>campus.tbl_coodle_surveys: ' . $db->db_last_error() . '</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on campus.tbl_coodle_surveys';

	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE campus.tbl_coodle_surveys TO vilesci;';
	if (!$db->db_query($qry))
		echo '<strong>campus.tbl_coodle_surveys: ' . $db->db_last_error() . '</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on campus.tbl_coodle_surveys';

	$qry = 'GRANT USAGE ON campus.seq_tbl_coodle_surveys_id TO web;';
	if (!$db->db_query($qry))
		echo '<strong>campus.tbl_coodle_surveys: ' . $db->db_last_error() . '</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on campus.tbl_coodle_surveys';

	$qry = 'GRANT USAGE ON campus.seq_tbl_coodle_surveys_id TO vilesci;';
	if (!$db->db_query($qry))
		echo '<strong>campus.tbl_coodle_surveys: ' . $db->db_last_error() . '</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on campus.tbl_coodle_surveys';
}



if (!$result = @$db->db_query("SELECT 1 FROM campus.tbl_coodle_survey_participants LIMIT 1")) {
	$qry = "CREATE TABLE campus.tbl_coodle_survey_participants (
		survey_id int NOT NULL,
		participant_uid varchar(33) NOT NULL,
		selection varchar(255),
		CONSTRAINT tbl_coodle_survey_participants_survey_id_participant_uid_pk PRIMARY KEY(survey_id, participant_uid),
		CONSTRAINT tbl_coodle_survey_participants_survey_id_fk FOREIGN KEY (survey_id) REFERENCES campus.tbl_coodle_surveys(id),
		CONSTRAINT tbl_coodle_survey_participants_participant_uid_fk FOREIGN KEY (participant_uid) REFERENCES public.tbl_benutzer(uid)
		);";

	if (!$db->db_query($qry))
		echo '<strong>campus.tbl_coodle_survey_participants: ' . $db->db_last_error() . '</strong><br>';
	else
		echo '<br>campus.tbl_coodle_survey_participants: table created';


	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE campus.tbl_coodle_survey_participants TO web;';
	if (!$db->db_query($qry))
		echo '<strong>campus.tbl_coodle_survey_participants: ' . $db->db_last_error() . '</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on campus.tbl_coodle_survey_participants';

	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE campus.tbl_coodle_survey_participants TO vilesci;';
	if (!$db->db_query($qry))
		echo '<strong>campus.tbl_coodle_survey_participants: ' . $db->db_last_error() . '</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on campus.tbl_coodle_survey_participants';
}



if (!$result = @$db->db_query("SELECT 1 FROM campus.tbl_coodle_survey_external_participants LIMIT 1")) {
	$qry = "CREATE TABLE campus.tbl_coodle_survey_external_participants (
		id int NOT NULL,
		survey_id int NOT NULL,
		name varchar(255) NOT NULL,
		email varchar(255) NOT NULL,
		access_key varchar(255) NOT NULL,
		selection varchar(255),
		CONSTRAINT tbl_coodle_surveys_external_participants_id_pk PRIMARY KEY(id),
		CONSTRAINT tbl_coodle_survey_external_participants_survey_id_fk FOREIGN KEY (survey_id) REFERENCES campus.tbl_coodle_surveys(id)
		);";

	if (!$db->db_query($qry))
		echo '<strong>campus.tbl_coodle_survey_external_participants: ' . $db->db_last_error() . '</strong><br>';
	else
		echo '<br>campus.tbl_coodle_survey_external_participants: table created';

	$db->db_query('CREATE SEQUENCE IF NOT EXISTS campus.seq_tbl_coodle_survey_external_participants_id
     INCREMENT BY 1
     NO MAXVALUE
     NO MINVALUE
     CACHE 1;');

	$db->db_query("ALTER TABLE campus.tbl_coodle_survey_external_participants ALTER COLUMN id SET DEFAULT nextval('campus.seq_tbl_coodle_survey_external_participants_id');");


	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE campus.tbl_coodle_survey_external_participants TO web;';
	if (!$db->db_query($qry))
		echo '<strong>campus.tbl_coodle_survey_external_participants: ' . $db->db_last_error() . '</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on campus.tbl_coodle_survey_external_participants';

	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE campus.tbl_coodle_survey_external_participants TO vilesci;';
	if (!$db->db_query($qry))
		echo '<strong>campus.tbl_coodle_survey_external_participants: ' . $db->db_last_error() . '</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on campus.tbl_coodle_survey_external_participants';

	$qry = 'GRANT USAGE ON campus.seq_tbl_coodle_survey_external_participants_id TO web;';
	if (!$db->db_query($qry))
		echo '<strong>campus.tbl_coodle_survey_external_participants: ' . $db->db_last_error() . '</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on campus.tbl_coodle_survey_external_participants';

	$qry = 'GRANT USAGE ON campus.seq_tbl_coodle_survey_external_participants_id TO vilesci;';
	if (!$db->db_query($qry))
		echo '<strong>campus.tbl_coodle_survey_external_participants: ' . $db->db_last_error() . '</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on campus.tbl_coodle_survey_external_participants';
}

$db->db_query('CREATE SEQUENCE IF NOT EXISTS campus.seq_tbl_coodle_survey_external_participants_id
     INCREMENT BY 1
     NO MAXVALUE
     NO MINVALUE
     CACHE 1;');

	$db->db_query("ALTER TABLE campus.tbl_coodle_survey_external_participants ALTER COLUMN id SET DEFAULT nextval('campus.seq_tbl_coodle_survey_external_participants_id');");


if (!$result = @$db->db_query("SELECT 1 FROM campus.tbl_coodle_survey_timeslots LIMIT 1")) {
	$qry = "CREATE TABLE campus.tbl_coodle_survey_timeslots (
		id int GENERATED ALWAYS AS IDENTITY,
		survey_id int NOT NULL,
		starts_at timestamp NOT NULL,
		CONSTRAINT tbl_coodle_survey_timeslots_id_pk PRIMARY KEY(id),
		CONSTRAINT tbl_coodle_survey_timeslots_survey_id_fk FOREIGN KEY (survey_id) REFERENCES campus.tbl_coodle_surveys(id)
		);";

	if (!$db->db_query($qry))
		echo '<strong>campus.tbl_coodle_survey_timeslots: ' . $db->db_last_error() . '</strong><br>';
	else
		echo '<br>campus.tbl_coodle_survey_timeslots: table created';


	$db->db_query('CREATE SEQUENCE IF NOT EXISTS campus.seq_tbl_coodle_survey_timeslots_id
     INCREMENT BY 1
     NO MAXVALUE
     NO MINVALUE
     CACHE 1;');

	$db->db_query("ALTER TABLE campus.tbl_coodle_surveys ALTER COLUMN id SET DEFAULT nextval('campus.seq_tbl_coodle_survey_timeslots_id');");


	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE campus.tbl_coodle_survey_timeslots TO web;';
	if (!$db->db_query($qry))
		echo '<strong>campus.tbl_coodle_survey_timeslots: ' . $db->db_last_error() . '</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on campus.tbl_coodle_survey_timeslots';

	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE campus.tbl_coodle_survey_timeslots TO vilesci;';
	if (!$db->db_query($qry))
		echo '<strong>campus.tbl_coodle_survey_timeslots: ' . $db->db_last_error() . '</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on campus.tbl_coodle_survey_timeslots';

	$qry = 'GRANT USAGE ON campus.seq_tbl_coodle_survey_timeslots_id TO web;';
	if (!$db->db_query($qry))
		echo '<strong>campus.tbl_coodle_survey_timeslots: ' . $db->db_last_error() . '</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on campus.tbl_coodle_survey_timeslots';

	$qry = 'GRANT USAGE ON campus.seq_tbl_coodle_survey_timeslots_id TO vilesci;';
	if (!$db->db_query($qry))
		echo '<strong>campus.tbl_coodle_survey_timeslots: ' . $db->db_last_error() . '</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on campus.tbl_coodle_survey_timeslots';
}

if (!$result = @$db->db_query("SELECT is_active FROM campus.tbl_freebusytyp LIMIT 1")) {
	$qry = "ALTER TABLE campus.tbl_freebusytyp ADD is_active boolean NOT NULL DEFAULT TRUE;";

	if (!$db->db_query($qry))
		echo '<strong>campus.tbl_freebusytyp: ' . $db->db_last_error() . '</strong><br>';
	else {
		echo '<br>campus.tbl_freebusytyp: column is_active added';
		$db->db_query("UPDATE campus.tbl_freebusytyp SET is_active = FALSE WHERE freebusytyp_kurzbz = 'Webmail';");
	}
}