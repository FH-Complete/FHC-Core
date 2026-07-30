<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

//Create Sequence hr.tbl_zeitmodell and grant Rights
if ($result = @$db->db_query("SELECT * FROM pg_class WHERE relname = 'tbl_zeitmodell_zeitmodell_id_seq'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		if ($count = @$db->db_query("SELECT * FROM hr.tbl_zeitmodell"))
		{
			$count = $db->db_num_rows($count) + 1;
			$qry = 'CREATE SEQUENCE hr.tbl_zeitmodell_zeitmodell_id_seq START ';
			$qry .= $count;
			if(!$db->db_query($qry))
			{
				echo '<strong> hr.tbl_zeitmodell '.$db->db_last_error().'</strong><br>';
			}
			else
			{
				echo '<br>hr.tbl_zeitmodell: Sequence hr.tbl_zeitmodell_zeitmodell_id_seq mit Startwert ' . $count . ' erstellt';
				$qry2 = "GRANT SELECT, UPDATE ON hr.tbl_zeitmodell_zeitmodell_id_seq TO vilesci;
						GRANT SELECT, UPDATE ON hr.tbl_zeitmodell_zeitmodell_id_seq TO web;";
				if(!$db->db_query($qry2))
				{
					echo '<strong>hr.tbl_zeitmodell_zeitmodell_id_seq Berechtigungen: '.$db->db_last_error().'</strong><br>';
				}
				else
				{
					echo '<br>hr.tbl_zeitmodell: Rechte auf hr.tbl_zeitmodell_zeitmodell_id_seq fuer web user und vilesci gesetzt ';
				}
			}
		}
	}
}

//Create Table
if ($result = $db->db_query("SELECT * FROM information_schema.tables WHERE table_name='tbl_zeitmodell' AND table_schema='hr'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "
			CREATE TABLE IF NOT EXISTS hr.tbl_zeitmodell (
				zeitmodell_id integer,
				zeitmodell_kurzbz varchar(32) UNIQUE,
				beschreibung text,
				aktiv boolean NOT NULL DEFAULT FALSE,
				stundenanzahl numeric(8,2),
				ext_id text,
				sort smallint,
				insertamum TIMESTAMP DEFAULT NOW(),
				insertvon VARCHAR(32),
				updateamum TIMESTAMP,
				updatevon VARCHAR(32),
				CONSTRAINT tbl_zeitmodell_pkey PRIMARY KEY (zeitmodell_id)
			);

			GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE hr.tbl_zeitmodell TO vilesci;";

		if (! $db->db_query($qry))
			echo '<strong>Zeitmodell: ' . $db->db_last_error() . '</strong><br>';
		else
			echo 'hr.tbl_zeitmodell wurde neu erstellt<br>';
	}
}

//hr.tbl_zeitmodell DEFAULT einstellen
if ($result = @$db->db_query("SELECT column_default FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = 'tbl_zeitmodell'AND column_name = 'zeitmodell_id' and column_default is null"))
{
	if($db->db_num_rows($result)==1)
	{
		$qry = "ALTER TABLE hr.tbl_zeitmodell ALTER COLUMN zeitmodell_id SET DEFAULT nextval('hr.tbl_zeitmodell_zeitmodell_id_seq'::regclass);";

		if(!$db->db_query($qry))
			echo '<strong> hr.tbl_zeitmodell '.$db->db_last_error().'</strong><br>';
		else
			echo '<br> hr.tbl_zeitmodell: Defaultwert bei Spalte hr.tbl_zeitmodell_id gesetzt';
	}
}

//foreign key zu Vertragsbestandteil
if ($result = @$db->db_query("SELECT * FROM information_schema.columns WHERE column_name='zeitmodell_id' AND table_name='tbl_vertragsbestandteil_zeitaufzeichnung' AND table_schema='hr'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "
			ALTER TABLE 
				hr.tbl_vertragsbestandteil_zeitaufzeichnung 
			ADD COLUMN
				zeitmodell_id integer;
			ALTER TABLE hr.tbl_vertragsbestandteil_zeitaufzeichnung ADD CONSTRAINT vertragsbestandteil_zeitaufzeichnung_zeitmodell_fk FOREIGN KEY (zeitmodell_id) REFERENCES hr.tbl_zeitmodell (zeitmodell_id) ON DELETE RESTRICT ON UPDATE CASCADE;
		";
		if (! $db->db_query($qry))
			echo '<strong>Zeitmodell: ' . $db->db_last_error() . '</strong><br>';
		else
			echo '<br>Spalte zeitmodell_id wurde in hr.tbl_vertragsbestandteil_zeitaufzeichnung neu erstellt<br>';
	}
}
