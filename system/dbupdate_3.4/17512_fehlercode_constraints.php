<?php

if (! defined('DB_NAME')) exit('No direct script access allowed');

// Remove UNIQUE constraint on fehlercode and fehler_kurzbz on system.tbl_fehler
if ($result = @$db->db_query("SELECT conname FROM pg_constraint WHERE conname = 'uk_tbl_fehler_fehlercode_fehler_kurzbz'"))
{
	if ($db->db_num_rows($result) > 0)
	{
		$qry = "ALTER TABLE system.tbl_fehler DROP CONSTRAINT uk_tbl_fehler_fehlercode_fehler_kurzbz;";

		if (!$db->db_query($qry))
			echo '<strong>system.tbl_fehler '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Removed UNIQUE constraint on "fehlercode" and "fehler_kurzbz" from system.tbl_fehler<br>';
	}
}

// Add NOT NULL constraint on fehler_kurzbz and fehlercode_extern on system.tbl_fehler
if ($result = @$db->db_query("SELECT conname FROM pg_constraint WHERE conname = 'chk_tbl_fehler_fehler_kurzbz_fehlercode_extern'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$hasError = false;
		$qry = "SELECT 1 FROM system.tbl_fehler WHERE fehler_kurzbz IS NULL AND fehlercode_extern IS NULL AND fehlercode = 'UNKNOWN_ERROR'";

		$result = $db->db_query($qry);
		if ($db->db_num_rows($result)>0)
		{
			// Add fehler_kurzbz TO UNKNOWN_ERROR to satisfy constraint
			$qry = "UPDATE system.tbl_fehler SET fehler_kurzbz='unbekannterCoreFehler' WHERE fehlercode = 'UNKNOWN_ERROR';";

			if(!$db->db_query($qry))
			{
				echo '<strong>system.tbl_fehler '.$db->db_last_error().'</strong><br>';
				$hasError = true;
			}
			else
			{
				echo '<br>Add fehler_kurzbz to UNKNOWN_ERROR in system.tbl_fehler';
			}
		}

		if (!$hasError)
		{
			$qry = "ALTER TABLE system.tbl_fehler ADD CONSTRAINT chk_tbl_fehler_fehler_kurzbz_fehlercode_extern CHECK (fehler_kurzbz IS NOT NULL OR fehlercode_extern IS NOT NULL);";

			if (!$db->db_query($qry))
				echo '<strong>system.tbl_fehler '.$db->db_last_error().'</strong><br>';
			else
				echo '<br>Added NOT NULL constraint on "fehlercode" and "fehler_kurzbz" from system.tbl_fehler<br>';
		}
	}
}
