<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

// add campus.tbl_paabgabetyp options for Quality Gates
if($result = $db->db_query("SELECT 1 FROM campus.tbl_paabgabetyp WHERE paabgabetyp_kurzbz='qualgate1'"))
{
	if($db->db_num_rows($result) === 0)
	{
		$qry = "INSERT INTO campus.tbl_paabgabetyp (paabgabetyp_kurzbz, bezeichnung) VALUES('qualgate1', 'Quality Gate 1');";

		if(!$db->db_query($qry))
			echo '<strong>campus.tbl_paabgabetyp: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>paabgabetyp quality gate 1 hinzugefuegt';
	}
}

if($result = $db->db_query("SELECT 1 FROM campus.tbl_paabgabetyp WHERE paabgabetyp_kurzbz='qualgate2'"))
{
	if($db->db_num_rows($result) === 0)
	{
		$qry = "INSERT INTO campus.tbl_paabgabetyp (paabgabetyp_kurzbz, bezeichnung) VALUES('qualgate2', 'Quality Gate 2');";

		if(!$db->db_query($qry))
			echo '<strong>campus.tbl_paabgabetyp: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>paabgabetyp quality gate 2 hinzugefuegt';
	}
}

if($result = $db->db_query("SELECT 1 FROM information_schema.columns WHERE table_schema = 'campus' AND table_name = 'tbl_paabgabe' AND column_name = 'note'"))
{
	if($db->db_num_rows($result) === 0)
	{

		$qry = "ALTER TABLE campus.tbl_paabgabe
				ADD COLUMN note SMALLINT DEFAULT NULL,
				ADD CONSTRAINT tbl_paabgabe_note_fkey
					FOREIGN KEY (note)
					REFERENCES lehre.tbl_note(note)
					ON UPDATE CASCADE ON DELETE RESTRICT;";
		
		if(!$db->db_query($qry))
			echo '<strong>campus.tbl_paabgabe: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>paabgabe column note default 9 (noch nicht eingetragen) hinzugefuegt';
	}
}

if($result = $db->db_query("SELECT 1 FROM information_schema.columns WHERE table_schema = 'campus' AND table_name = 'tbl_paabgabe' AND column_name = 'upload_allowed'"))
{
	if($db->db_num_rows($result) === 0)
	{
		$qry = "ALTER TABLE campus.tbl_paabgabe
				ADD COLUMN IF NOT EXISTS upload_allowed boolean DEFAULT false;";

		if(!$db->db_query($qry))
			echo '<strong>campus.tbl_paabgabe: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>paabgabe column upload_allowed default false hinzugefuegt';
	}
}

if($result = $db->db_query("SELECT 1 FROM information_schema.columns WHERE table_schema = 'campus' AND table_name = 'tbl_paabgabe' AND column_name = 'notiz'"))
{
	if($db->db_num_rows($result) === 0)
	{
		$qry = "ALTER TABLE campus.tbl_paabgabe
				ADD COLUMN IF NOT EXISTS notiz text DEFAULT NULL;";

		if(!$db->db_query($qry))
			echo '<strong>campus.tbl_paabgabe: '.$db->db_last_error().'</strong><br>';
		else
			echo "<br>paabgabe column notiz default '' hinzugefuegt";
	}
}

if($result = $db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz = 'basis/abgabe_student'"))
{
	if($db->db_num_rows($result) === 0)
	{
		$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung)
				SELECT 'basis/abgabe_student', 'Recht um Abgabetool für Studenten zu bedienen'";

		if(!$db->db_query($qry))
			echo '<strong>system.tbl_berechtigung: '.$db->db_last_error().'</strong><br>';
		else
			echo "<br>system.tbl_berechtigung insert basis/abgabe_student hinzugefuegt";
	}
}

if($result = $db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz = 'basis/abgabe_lektor'"))
{
	if($db->db_num_rows($result) === 0)
	{
		$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung)
				SELECT 'basis/abgabe_lektor', 'Recht um Abgabetool für Lektoren zu bedienen'";

		if(!$db->db_query($qry))
			echo '<strong>system.tbl_berechtigung: '.$db->db_last_error().'</strong><br>';
		else
			echo "<br>system.tbl_berechtigung insert basis/abgabe_lektor hinzugefuegt";
	}
}

if($result = $db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz = 'basis/abgabe_assistenz'"))
{
	if($db->db_num_rows($result) === 0)
	{
		$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung)
				SELECT 'basis/abgabe_assistenz', 'Recht um Abgabetool für Assistenzen zu bedienen'";

		if(!$db->db_query($qry))
			echo '<strong>system.tbl_berechtigung: '.$db->db_last_error().'</strong><br>';
		else
			echo "<br>system.tbl_berechtigung insert basis/abgabe_assistenz hinzugefuegt";
	}
}
