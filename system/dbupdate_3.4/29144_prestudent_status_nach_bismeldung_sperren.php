<?php

if (! defined('DB_NAME')) exit('No direct script access allowed');

// Creates table bis.tbl_bismeldestichtag if it doesn't exist and grants privileges
if (!$result = @$db->db_query('SELECT 1 FROM bis.tbl_bismeldestichtag LIMIT 1'))
{
	$qry = "CREATE TABLE bis.tbl_bismeldestichtag (
				meldestichtag_id integer,
				meldestichtag date NOT NULL,
				studiensemester_kurzbz varchar(16) NOT NULL,
				insertamum timestamp DEFAULT NOW(),
				insertvon varchar(32),
				updateamum timestamp,
				updatevon varchar(32)
			);

			COMMENT ON TABLE bis.tbl_bismeldestichtag IS 'Deadline Dates for BIS reporting';

			ALTER TABLE bis.tbl_bismeldestichtag ADD CONSTRAINT pk_bismeldestichtag PRIMARY KEY (meldestichtag_id);

			CREATE SEQUENCE bis.tbl_meldestichtag_meldestichtag_id_seq
				INCREMENT BY 1
				NO MAXVALUE
				NO MINVALUE
				CACHE 1;

			ALTER TABLE bis.tbl_bismeldestichtag ALTER COLUMN meldestichtag_id SET DEFAULT nextval('bis.tbl_meldestichtag_meldestichtag_id_seq');
			ALTER TABLE bis.tbl_bismeldestichtag ADD CONSTRAINT fk_bismeldestichtag_studiensemester_kurzbz FOREIGN KEY (studiensemester_kurzbz) REFERENCES public.tbl_studiensemester(studiensemester_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT;";

	if (!$db->db_query($qry))
		echo '<strong>bis.tbl_bismeldestichtag: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>bis.tbl_bismeldestichtag table created';

	$qry = 'GRANT SELECT ON TABLE bis.tbl_bismeldestichtag TO web;';
	if (!$db->db_query($qry))
		echo '<strong>bis.tbl_bismeldestichtag: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on bis.tbl_bismeldestichtag';

	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE bis.tbl_bismeldestichtag TO vilesci;';
	if (!$db->db_query($qry))
		echo '<strong>bis.tbl_bismeldestichtag: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on bis.tbl_bismeldestichtag';

	$qry = 'GRANT SELECT, UPDATE ON SEQUENCE bis.tbl_meldestichtag_meldestichtag_id_seq TO web;';
	if (!$db->db_query($qry))
		echo '<strong>bis.tbl_meldestichtag_meldestichtag_id_seq: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on bis.tbl_meldestichtag_meldestichtag_id_seq';

	$qry = 'GRANT SELECT, UPDATE ON SEQUENCE bis.tbl_meldestichtag_meldestichtag_id_seq TO vilesci;';
	if (!$db->db_query($qry))
		echo '<strong>bis.tbl_meldestichtag_meldestichtag_id_seq: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on bis.tbl_meldestichtag_meldestichtag_id_seq';
}

// Add permission for edit Bismelden flag
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz = 'student/editBismelden';"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES('student/editBismelden', 'Ändern des Bismelden Attributs pro Student');";

		if(!$db->db_query($qry))
			echo '<strong>system.tbl_berechtigung '.$db->db_last_error().'</strong><br>';
		else
			echo ' system.tbl_berechtigung: Added permission editBismelden<br>';
	}
}
