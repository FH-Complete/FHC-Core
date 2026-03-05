<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

if($result = $db->db_query("SELECT 1 FROM information_schema.columns WHERE table_schema = 'lehre' AND table_name = 'tbl_projektarbeit' AND column_name = 'lehrveranstaltung_id'"))
{
	if($db->db_num_rows($result) === 0)
	{

		$qry = "ALTER TABLE lehre.tbl_projektarbeit
				ADD COLUMN IF NOT EXISTS lehrveranstaltung_id INTEGER;";

		if(!$db->db_query($qry))
			echo '<strong>lehre.tbl_projektarbeit: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>tbl_projektarbeit column lehrveranstaltung_id hinzugefuegt';
	}
}

// TODO: check for and add fk constraint lehrveranstaltung_id
//if($result = $db->db_query("SELECT 1 FROM information_schema.columns WHERE table_schema = 'lehre' AND table_name = 'tbl_projektarbeit' AND column_name = 'lehrveranstaltung_id'"))
//{
//	if($db->db_num_rows($result) === 0)
//	{
//
//		$qry = "ALTER TABLE lehre.tbl_projektarbeit
//				ADD COLUMN IF NOT EXISTS lehrveranstaltung_id INTEGER;";
//
//		if(!$db->db_query($qry))
//			echo '<strong>lehre.tbl_projektarbeit: '.$db->db_last_error().'</strong><br>';
//		else
//			echo '<br>tbl_projektarbeit column lehrveranstaltung_id hinzugefuegt';
//	}
//}

if($result = $db->db_query("SELECT 1 FROM information_schema.columns WHERE table_schema = 'lehre' AND table_name = 'tbl_projektarbeit' AND column_name = 'studiensemester_kurzbz'"))
{
	if($db->db_num_rows($result) === 0)
	{

		$qry = "ALTER TABLE lehre.tbl_projektarbeit
				ADD COLUMN IF NOT EXISTS lehrveranstaltung_id INTEGER;";

		if(!$db->db_query($qry))
			echo '<strong>lehre.tbl_projektarbeit: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>tbl_projektarbeit column lehrveranstaltung_id hinzugefuegt';
	}
}