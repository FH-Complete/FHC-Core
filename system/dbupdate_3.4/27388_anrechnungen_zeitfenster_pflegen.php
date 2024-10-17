<?php

if (! defined('DB_NAME')) exit('No direct script access allowed');


if (!$result = @$db->db_query('SELECT 1 FROM lehre.tbl_anrechnungszeitraum LIMIT 1'))
{
    $qry = 'CREATE TABLE lehre.tbl_anrechnungszeitraum 
            (
                anrechnungszeitraum_id integer,
    			studiensemester_kurzbz varchar(16) NOT NULL,
    			anrechnungstart date,
    			anrechnungende date,
    			insertamum timestamp default NOW(),
    			insertvon varchar(32)                                  
			);

			COMMENT ON TABLE lehre.tbl_anrechnungszeitraum IS \'Zeitfenster fuer Anrechnungen pro Studiensemester\';
			COMMENT ON COLUMN lehre.tbl_anrechnungszeitraum.anrechnungstart IS \'Zeitfenster Startdatum\';
			COMMENT ON COLUMN lehre.tbl_anrechnungszeitraum.anrechnungende IS \'Zeitfenster Enddatum\';

		    ALTER TABLE lehre.tbl_anrechnungszeitraum ADD CONSTRAINT pk_anrechnungszeitraum PRIMARY KEY (anrechnungszeitraum_id);
			ALTER TABLE lehre.tbl_anrechnungszeitraum ADD CONSTRAINT fk_anrechnungszeitraum_studiensemester_kurzbz FOREIGN KEY (studiensemester_kurzbz) REFERENCES public.tbl_studiensemester(studiensemester_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT;

            CREATE SEQUENCE lehre.seq_anrechnungszeitraum_anrechnungszeitraum_id
			START WITH 1
			INCREMENT BY 1
			NO MAXVALUE
			NO MINVALUE
			CACHE 1;
		    ALTER TABLE lehre.tbl_anrechnungszeitraum ALTER COLUMN anrechnungszeitraum_id SET DEFAULT nextval(\'lehre.seq_anrechnungszeitraum_anrechnungszeitraum_id\');

            GRANT SELECT, UPDATE, INSERT, DELETE ON lehre.tbl_anrechnungszeitraum TO web;
            GRANT SELECT, UPDATE, INSERT, DELETE ON lehre.tbl_anrechnungszeitraum TO vilesci;
            GRANT SELECT, UPDATE ON lehre.seq_anrechnungszeitraum_anrechnungszeitraum_id TO vilesci;
		    GRANT SELECT, UPDATE ON lehre.seq_anrechnungszeitraum_anrechnungszeitraum_id TO web;
		';

    if(!$db->db_query($qry))
        echo '<strong>lehre.tbl_anrechnungszeitraum: '.$db->db_last_error().'</strong><br>';
    else
        echo ' lehre.tbl_anrechnungszeitraum: Tabelle hinzugefuegt<br>';
}

// Add permission to admin Anrechnungen
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz = 'lehre/anrechnungszeitfenster';"))
{
    if($db->db_num_rows($result) == 0)
    {
        $qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES('lehre/anrechnungszeitfenster', 'Anrechnungszeitfenster anlegen');";

        if(!$db->db_query($qry))
            echo '<strong>system.tbl_berechtigung '.$db->db_last_error().'</strong><br>';
        else
            echo ' system.tbl_berechtigung: Added permission for lehre/anrechnungszeitfenster<br>';
    }
}