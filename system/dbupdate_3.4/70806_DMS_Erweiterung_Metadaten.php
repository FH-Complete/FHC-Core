<?php
if (!defined('DB_NAME')) exit('No direct script access allowed');

// Check if the column campus.tbl_dms_version.oe_kurzbz_verantwortlich exists
if ($result = @$db->db_query("
	SELECT 1
	  FROM information_schema.columns
	 WHERE table_schema = 'campus'
	   AND table_name = 'tbl_dms_version'
	   AND column_name = 'oe_kurzbz_verantwortlich';
"))
{
	// If the column does not exist
	if ($db->db_num_rows($result) == 0)
	{
		// Then try to create it
		$info_msg = 'Added column campus.tbl_dms_version.oe_kurzbz_verantwortlich<br>';
		if (!$result = @$db->db_query('ALTER TABLE IF EXISTS "campus"."tbl_dms_version" ADD COLUMN IF NOT EXISTS "oe_kurzbz_verantwortlich" VARCHAR(32) NULL;'))
			$info_msg = '<strong>campus.tbl_dms_version '.$db->db_last_error().'</strong><br>';
		echo $info_msg;
	}
}

// Check if the column campus.tbl_dms_version.archiviert exists
if ($result = @$db->db_query("
	SELECT 1
	  FROM information_schema.columns
	 WHERE table_schema = 'campus'
	   AND table_name = 'tbl_dms_version'
	   AND column_name = 'archiviert';
"))
{
	// If the column does not exist
	if ($db->db_num_rows($result) == 0)
	{
		// Then try to create it
		$info_msg = 'Added column campus.tbl_dms_version.archiviert<br>';
		if (!$result = @$db->db_query('ALTER TABLE IF EXISTS "campus"."tbl_dms_version" ADD COLUMN IF NOT EXISTS "archiviert" BOOLEAN NOT NULL DEFAULT FALSE;'))
			$info_msg = '<strong>campus.tbl_dms_version '.$db->db_last_error().'</strong><br>';
		echo $info_msg;
	}
}

// Check if the column campus.tbl_dms_version.gueltig_ab exists
if ($result = @$db->db_query("
	SELECT 1
	  FROM information_schema.columns
	 WHERE table_schema = 'campus'
	   AND table_name = 'tbl_dms_version'
	   AND column_name = 'gueltig_ab';
"))
{
	// If the column does not exist
	if ($db->db_num_rows($result) == 0)
	{
		// Then try to create it
		$info_msg = 'Added column campus.tbl_dms_version.gueltig_ab<br>';
		if (!$result = @$db->db_query('ALTER TABLE IF EXISTS "campus"."tbl_dms_version" ADD COLUMN IF NOT EXISTS "gueltig_ab" TIMESTAMP NULL;'))
			$info_msg = '<strong>campus.tbl_dms_version '.$db->db_last_error().'</strong><br>';
		echo $info_msg;
	}
}

// Check if the foreign key fk_organisationseinheit_dms_verantwortlich exists
if ($result = @$db->db_query("
	SELECT 1
	  FROM information_schema.constraint_column_usage 
	 WHERE constraint_schema = 'campus'
	   AND constraint_name = 'fk_organisationseinheit_dms_verantwortlich';
"))
{
	// If the foreign key does not exist
	if ($db->db_num_rows($result) == 0)
	{
		// Then try to create it
		$info_msg = 'Added foreign key fk_organisationseinheit_dms_verantwortlich<br>';
		if (!$result = @$db->db_query('
			ALTER TABLE "campus"."tbl_dms_version"
			ADD CONSTRAINT "fk_organisationseinheit_dms_verantwortlich"
			FOREIGN KEY ("oe_kurzbz_verantwortlich")
			REFERENCES "public"."tbl_organisationseinheit" ("oe_kurzbz")
			ON DELETE RESTRICT ON UPDATE CASCADE;
		'))
			$info_msg = '<strong>campus.tbl_dms_version '.$db->db_last_error().'</strong><br>';
		echo $info_msg;
	}
}

