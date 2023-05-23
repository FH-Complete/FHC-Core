<?php

if (! defined('DB_NAME')) exit('No direct script access allowed');

// Creates table bis.tbl_bismeldestichtag if it doesn't exist and grants privileges
if (!$result = @$db->db_query('SELECT 1 FROM bis.tbl_bismeldestichtag LIMIT 1'))
{
	$qry = 'CREATE TABLE bis.tbl_bismeldestichtag (
				studiensemester_kurzbz varchar(16),
				meldestichtag date NOT NULL,
				insertamum timestamp DEFAULT NOW(),
				insertvon varchar(32),
				updateamum timestamp,
				updatevon varchar(32)
			);

			COMMENT ON TABLE bis.tbl_bismeldestichtag IS \'Deadline Dates for BIS reporting\';

			ALTER TABLE bis.tbl_bismeldestichtag ADD CONSTRAINT pk_bismeldestichtag PRIMARY KEY (studiensemester_kurzbz);

			ALTER TABLE bis.tbl_bismeldestichtag ADD CONSTRAINT fk_bismeldestichtag_studiensemester_kurzbz FOREIGN KEY (studiensemester_kurzbz) REFERENCES public.tbl_studiensemester(studiensemester_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT;';

	if (!$db->db_query($qry))
		echo '<strong>bis.tbl_bismeldestichtag: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>bis.tbl_bismeldestichtag table created';

	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE bis.tbl_bismeldestichtag TO web;';
	if (!$db->db_query($qry))
		echo '<strong>bis.tbl_bismeldestichtag: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on bis.tbl_bismeldestichtag';

	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE bis.tbl_bismeldestichtag TO vilesci;';
	if (!$db->db_query($qry))
		echo '<strong>bis.tbl_bismeldestichtag: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on bis.tbl_bismeldestichtag';
}
