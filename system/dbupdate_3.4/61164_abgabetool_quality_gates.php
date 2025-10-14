<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');


if($result = $db->db_query("SELECT 1 FROM information_schema.columns WHERE table_schema = 'campus' AND table_name = 'tbl_paabgabetyp' AND column_name = 'aktiv'"))
{
	if($db->db_num_rows($result) === 0)
	{

		$qry = "ALTER TABLE campus.tbl_paabgabetyp
				ADD COLUMN IF NOT EXISTS aktiv BOOLEAN DEFAULT true;";

		if(!$db->db_query($qry))
			echo '<strong>campus.tbl_paabgabetyp: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>paabgabetyp column aktiv default true hinzugefuegt';
	}
}

if($result = $db->db_query("SELECT 1 FROM information_schema.columns WHERE table_schema = 'campus' AND table_name = 'tbl_paabgabetyp' AND column_name = 'upload_allowed_default'"))
{
	if($db->db_num_rows($result) === 0)
	{

		$qry = "ALTER TABLE campus.tbl_paabgabetyp
				ADD COLUMN IF NOT EXISTS upload_allowed_default BOOLEAN DEFAULT true;";

		if(!$db->db_query($qry))
			echo '<strong>campus.tbl_paabgabetyp: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>paabgabetyp column upload_allowed_default default true hinzugefuegt';
	}
}

if($result = $db->db_query("SELECT 1 FROM information_schema.columns WHERE table_schema = 'campus' AND table_name = 'tbl_paabgabetyp' AND column_name = 'benotbar'"))
{
	if($db->db_num_rows($result) === 0)
	{

		$qry = "ALTER TABLE campus.tbl_paabgabetyp
				ADD COLUMN IF NOT EXISTS benotbar BOOLEAN DEFAULT true;";

		if(!$db->db_query($qry))
			echo '<strong>campus.tbl_paabgabetyp: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>paabgabetyp column benotbar default true hinzugefuegt';
	}
}

// TODO DEFINE ACTUAL VALUES BENOTBAR / UPLOAD_ALLOWED_DEFAULT / AKTIV FOR EACH PAABGABETYPE - DEVLOPER DEFAULTS BELOW
if($result = $db->db_query("SELECT 1 FROM campus.tbl_paabgabetyp WHERE paabgabetyp_kurzbz='qualgate1'"))
{
	if($db->db_num_rows($result) === 0)
	{
		$qry = "INSERT INTO campus.tbl_paabgabetyp (paabgabetyp_kurzbz, bezeichnung, benotbar, upload_allowed_default, aktiv)
					VALUES('qualgate1', 'Quality Gate 1', true, true, true);";

		if(!$db->db_query($qry))
			echo '<strong>campus.tbl_paabgabetyp: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>paabgabetyp quality gate 1 hinzugefuegt';
	}
}

// set new cols for zwischenabgabe
if($result = $db->db_query("SELECT 1 FROM campus.tbl_paabgabetyp WHERE paabgabetyp_kurzbz='zwischen'"))
{
	if($db->db_num_rows($result) === 1)
	{
		$qry = "UPDATE campus.tbl_paabgabetyp 
				SET benotbar = false,
				    upload_allowed_default = true,
				    aktiv = true
				WHERE paabgabetyp_kurzbz='zwischen';";

		if(!$db->db_query($qry))
			echo '<strong>campus.tbl_paabgabetyp: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>paabgabetyp zwischen updated benotbar = false, upload_allowed_default = true, aktiv = true';
	}
}

// set new cols for note
if($result = $db->db_query("SELECT 1 FROM campus.tbl_paabgabetyp WHERE paabgabetyp_kurzbz='note'"))
{
	if($db->db_num_rows($result) === 1)
	{
		$qry = "UPDATE campus.tbl_paabgabetyp 
				SET benotbar = false,
				    upload_allowed_default = false,
				    aktiv = false
				WHERE paabgabetyp_kurzbz='note';";

		if(!$db->db_query($qry))
			echo '<strong>campus.tbl_paabgabetyp: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>paabgabetyp note updated benotbar = false, upload_allowed_default = false, aktiv = false';
	}
}

// set new cols for abstract / entwurf
if($result = $db->db_query("SELECT 1 FROM campus.tbl_paabgabetyp WHERE paabgabetyp_kurzbz='abstract'"))
{
	if($db->db_num_rows($result) === 1)
	{
		$qry = "UPDATE campus.tbl_paabgabetyp 
				SET benotbar = false,
				    upload_allowed_default = true,
				    aktiv = true
				WHERE paabgabetyp_kurzbz='abstract';";

		if(!$db->db_query($qry))
			echo '<strong>campus.tbl_paabgabetyp: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>paabgabetyp abstract updated benotbar = false, upload_allowed_default = true, aktiv = true';
	}
}

// set new cols for endabgabe / end
if($result = $db->db_query("SELECT 1 FROM campus.tbl_paabgabetyp WHERE paabgabetyp_kurzbz='end'"))
{
	if($db->db_num_rows($result) === 1)
	{
		$qry = "UPDATE campus.tbl_paabgabetyp 
				SET benotbar = false,
				    upload_allowed_default = true,
				    aktiv = true
				WHERE paabgabetyp_kurzbz='end';";

		if(!$db->db_query($qry))
			echo '<strong>campus.tbl_paabgabetyp: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>paabgabetyp end updated benotbar = false, upload_allowed_default = true, aktiv = true';
	}
}

// set new cols for endabgabe im sekretariat / enda
if($result = $db->db_query("SELECT 1 FROM campus.tbl_paabgabetyp WHERE paabgabetyp_kurzbz='enda'"))
{
	if($db->db_num_rows($result) === 1)
	{
		$qry = "UPDATE campus.tbl_paabgabetyp 
				SET benotbar = false,
				    upload_allowed_default = false,
				    aktiv = false
				WHERE paabgabetyp_kurzbz='enda';";

		if(!$db->db_query($qry))
			echo '<strong>campus.tbl_paabgabetyp: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>paabgabetyp enda updated benotbar = false, upload_allowed_default = false, aktiv = false';
	}
}

if($result = $db->db_query("SELECT 1 FROM campus.tbl_paabgabetyp WHERE paabgabetyp_kurzbz='qualgate2'"))
{
	if($db->db_num_rows($result) === 0)
	{
		$qry = "INSERT INTO campus.tbl_paabgabetyp (paabgabetyp_kurzbz, bezeichnung, benotbar, upload_allowed_default, aktiv)
					VALUES('qualgate2', 'Quality Gate 2', true, true, true);";

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
				ADD COLUMN IF NOT EXISTS note SMALLINT DEFAULT NULL,
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
				ADD COLUMN IF NOT EXISTS upload_allowed boolean DEFAULT true;";

		if(!$db->db_query($qry))
			echo '<strong>campus.tbl_paabgabe: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>paabgabe column upload_allowed default false hinzugefuegt';
	}
}

if($result = $db->db_query("SELECT 1 FROM information_schema.columns WHERE table_schema = 'campus' AND table_name = 'tbl_paabgabe' AND column_name = 'beurteilungsnotiz'"))
{
	if($db->db_num_rows($result) === 0)
	{
		$qry = "ALTER TABLE campus.tbl_paabgabe
				ADD COLUMN IF NOT EXISTS beurteilungsnotiz text DEFAULT NULL;";

		if(!$db->db_query($qry))
			echo '<strong>campus.tbl_paabgabe: '.$db->db_last_error().'</strong><br>';
		else
			echo "<br>paabgabe column beurteilungsnotiz default '' hinzugefuegt";
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
