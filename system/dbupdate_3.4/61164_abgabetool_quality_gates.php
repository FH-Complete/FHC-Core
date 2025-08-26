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
				ADD COLUMN note SMALLINT NOT NULL DEFAULT 9,
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

if($result = $db->db_query("SELECT 1 FROM information_schema.columns WHERE table_schema = 'campus' AND table_name = 'tbl_paabgabe' AND column_name = 'upload_required'"))
{
	if($db->db_num_rows($result) === 0)
	{
		$qry = "ALTER TABLE campus.tbl_paabgabe
				ADD COLUMN IF NOT EXISTS upload_required boolean DEFAULT false;";

		if(!$db->db_query($qry))
			echo '<strong>campus.tbl_paabgabe: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>paabgabe column upload_required default false hinzugefuegt';
	}
}