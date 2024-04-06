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
            sort smallint,
            CONSTRAINT tbl_ereignis_pkey PRIMARY KEY (ereignis_kurzbz)
        );

        COMMENT ON TABLE hr.tbl_frist_ereignis IS E'Key-Table of fristen (deadline) events';

        CREATE TABLE IF NOT EXISTS hr.tbl_frist_status (
            status_kurzbz character varying(32) NOT NULL,
            bezeichnung varchar(32),
            sort smallint,
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

        INSERT INTO hr.tbl_frist_status(status_kurzbz, bezeichnung, sort) VALUES('neu','Neu', 1);
        INSERT INTO hr.tbl_frist_status(status_kurzbz, bezeichnung, sort) VALUES('in_bearbeitung','In Bearbeitung', 2);
        INSERT INTO hr.tbl_frist_status(status_kurzbz, bezeichnung, sort) VALUES('erledigt','Erledigt', 3);

        INSERT INTO hr.tbl_frist_ereignis(ereignis_kurzbz, bezeichnung, sort) VALUES('dv_beginn','DV Beginn', 1);
        INSERT INTO hr.tbl_frist_ereignis(ereignis_kurzbz, bezeichnung, sort) VALUES('dv_ende','DV Ende', 2);
        INSERT INTO hr.tbl_frist_ereignis(ereignis_kurzbz, bezeichnung, sort) VALUES('stunden_beginn','Stunden Beginn', 3);
        INSERT INTO hr.tbl_frist_ereignis(ereignis_kurzbz, bezeichnung, sort) VALUES('stunden_ende','Stunden Ende', 4);
        INSERT INTO hr.tbl_frist_ereignis(ereignis_kurzbz, bezeichnung, sort) VALUES('karenz_beginn','Karenz Beginn', 5);
        INSERT INTO hr.tbl_frist_ereignis(ereignis_kurzbz, bezeichnung, sort) VALUES('karenz_ende','Karenz Ende', 6);
        INSERT INTO hr.tbl_frist_ereignis(ereignis_kurzbz, bezeichnung, sort) VALUES('zeitaufzeichnung_beginn','Zeitaufzeichnung Beginn', 7);
        INSERT INTO hr.tbl_frist_ereignis(ereignis_kurzbz, bezeichnung, sort) VALUES('zeitaufzeichnung_ende','Zeitaufzeichnung Ende', 8);
        INSERT INTO hr.tbl_frist_ereignis(ereignis_kurzbz, bezeichnung, sort) VALUES('befristung_beginn','Befristung Beginn', 9);
        INSERT INTO hr.tbl_frist_ereignis(ereignis_kurzbz, bezeichnung, sort) VALUES('befristung_ende','Befristung Ende', 10);
        INSERT INTO hr.tbl_frist_ereignis (ereignis_kurzbz , bezeichnung, manuell, sort) VALUES('manuell1','Manuell 1', true, 11);
        INSERT INTO hr.tbl_frist_ereignis (ereignis_kurzbz , bezeichnung, manuell, sort) VALUES('manuell2','Manuell 2', true, 12);
        INSERT INTO hr.tbl_frist_ereignis (ereignis_kurzbz , bezeichnung, manuell, sort) VALUES('manuell3','Manuell 3', true, 13);
        INSERT INTO hr.tbl_frist_ereignis (ereignis_kurzbz , bezeichnung, manuell, sort) VALUES('manuell4','Manuell 4', true, 14);


        ";
    
        if (! $db->db_query($qry))
            echo '<strong>Fristenmanagement: ' . $db->db_last_error() . '</strong><br>';
        else
            echo 'Fristenmanagementtabellen wurden neu erstellt';
    }
}
