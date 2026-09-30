<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

// Creates table public.tbl_notiz_typ if it doesn't exist and grants privileges
if (!$result = @$db->db_query('SELECT 1 FROM testtool.tbl_pruefling_gebiet LIMIT 1'))
{
	$qry = 'CREATE TABLE testtool.tbl_pruefling_gebiet
			(
				prueflinggebiet_id integer NOT NULL,
				pruefling_id integer NOT NULL,
				gebiet_id integer NOT NULL,
				insertamum timestamp DEFAULT now()
			);
			
			ALTER TABLE testtool.tbl_pruefling_gebiet OWNER TO fhcomplete;
			ALTER TABLE testtool.tbl_pruefling_gebiet ADD CONSTRAINT pk_tbl_pruefling_gebiet PRIMARY KEY (prueflinggebiet_id);
			ALTER TABLE testtool.tbl_pruefling_gebiet ADD CONSTRAINT fk_tbl_pruefling_gebiet_pruefling_id FOREIGN KEY (pruefling_id) REFERENCES testtool.tbl_pruefling(pruefling_id) ON UPDATE CASCADE ON DELETE RESTRICT;
			ALTER TABLE testtool.tbl_pruefling_gebiet ADD CONSTRAINT fk_tbl_pruefling_gebiet_gebiet_id FOREIGN KEY (gebiet_id) REFERENCES testtool.tbl_gebiet(gebiet_id) ON UPDATE CASCADE ON DELETE RESTRICT;

			CREATE SEQUENCE testtool.tbl_pruefling_gebiet_prueflinggebiet_id_seq
				START WITH 1
				INCREMENT BY 1
				NO MINVALUE
				NO MAXVALUE
				CACHE 1;

			ALTER TABLE testtool.tbl_pruefling_gebiet_prueflinggebiet_id_seq OWNER TO fhcomplete;
			ALTER TABLE testtool.tbl_pruefling_gebiet ALTER COLUMN prueflinggebiet_id SET DEFAULT nextval(\'testtool.tbl_pruefling_gebiet_prueflinggebiet_id_seq\');

			GRANT SELECT, UPDATE, INSERT, DELETE ON testtool.tbl_pruefling_gebiet TO web;
			GRANT SELECT, UPDATE, INSERT, DELETE ON testtool.tbl_pruefling_gebiet TO vilesci;
			GRANT SELECT, UPDATE ON testtool.tbl_pruefling_gebiet_prueflinggebiet_id_seq TO vilesci;
			GRANT SELECT, UPDATE ON testtool.tbl_pruefling_gebiet_prueflinggebiet_id_seq TO web;
			';

	if (!$db->db_query($qry))
		echo '<strong>public.tbl_pruefling_gebiet: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>public.tbl_pruefling_gebiet table created';
}

