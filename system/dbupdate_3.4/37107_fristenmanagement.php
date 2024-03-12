<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

if ($result = $db->db_query("SELECT * FROM information_schema.tables WHERE table_name='tbl_frist' AND table_schema='hr'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "
        CREATE TABLE IF NOT EXISTS hr.tbl_frist (
            frist_id bigserial NOT NULL,
            mitarbeiter_uid character varying(32),            
            ereignis_kurzbz character varying(32) NOT NULL,
            bezeichnung varchar(255),
            datum date,
            status_kurzbz character varying(32) NOT NULL,
            parameter jsonb NOT NULL,
            insertvon character varying(32) NOT NULL,
            insertamum timestamp without time zone DEFAULT now() NOT NULL,
            updatevon character varying(32),
            updateamum timestamp without time zone,
            CONSTRAINT tbl_frist_pkey PRIMARY KEY (frist_id)
        );

        CREATE TABLE IF NOT EXISTS hr.tbl_frist_ereignis (
            ereignis_kurzbz character varying(32) NOT NULL,
            bezeichnung varchar(32) NOT NULL,
            manuell boolean DEFAULT FALSE,
            CONSTRAINT tbl_ereignis_pkey PRIMARY KEY (ereignis_kurzbz)
        );

        COMMENT ON TABLE hr.tbl_frist_ereignis IS E'Key-Table of fristen (deadline) events';

        CREATE TABLE IF NOT EXISTS hr.tbl_frist_status (
            status_kurzbz character varying(32) NOT NULL,
            bezeichnung varchar(32),
            CONSTRAINT tbl_frist_status_pk PRIMARY KEY (status_kurzbz)
        );

        COMMENT ON TABLE hr.tbl_frist_status IS E'Key-Table of fristen status (new, done)';

        ALTER TABLE hr.tbl_frist ADD CONSTRAINT tbl_frist_mitarbeiter_uid_fk FOREIGN KEY (mitarbeiter_uid)
		REFERENCES public.tbl_mitarbeiter (mitarbeiter_uid) MATCH FULL
		ON DELETE SET NULL ON UPDATE CASCADE;

        ALTER TABLE hr.tbl_frist ADD CONSTRAINT tbl_frist_ereignis_kurzbz_fk FOREIGN KEY (ereignis_kurzbz)
		REFERENCES hr.tbl_frist_ereignis (ereignis_kurzbz) MATCH FULL
		ON DELETE SET NULL ON UPDATE CASCADE;

        ALTER TABLE hr.tbl_frist ADD CONSTRAINT tbl_frist_status_kurzbz_fk FOREIGN KEY (status_kurzbz)
		REFERENCES hr.tbl_frist_status (status_kurzbz) MATCH FULL
		ON DELETE SET NULL ON UPDATE CASCADE;

        GRANT SELECT, UPDATE, INSERT, DELETE ON hr.tbl_frist TO vilesci;
        GRANT SELECT, UPDATE, INSERT, DELETE ON hr.tbl_frist_status TO vilesci;
        GRANT SELECT, UPDATE, INSERT, DELETE ON hr.tbl_frist_ereignis TO vilesci;

        INSERT INTO hr.tbl_frist_status(status_kurzbz, bezeichnung) VALUES('neu','Neu');
        INSERT INTO hr.tbl_frist_status(status_kurzbz, bezeichnung) VALUES('in_bearbeitung','In Bearbeitung');
        INSERT INTO hr.tbl_frist_status(status_kurzbz, bezeichnung) VALUES('erledigt','Erledigt');

        INSERT INTO hr.tbl_frist_ereignis(ereignis_kurzbz, bezeichnung) VALUES('dv_beginn','DV Beginn');
        INSERT INTO hr.tbl_frist_ereignis(ereignis_kurzbz, bezeichnung) VALUES('dv_ende','DV Ende');



        ";
    
        if (! $db->db_query($qry))
            echo '<strong>Fristenmanagement: ' . $db->db_last_error() . '</strong><br>';
        else
            echo 'Fristenmanagementtabellen wurden neu erstellt';
    }
}
