<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

//Add column entwicklungs_id to bis.tbl_entwicklungsteam
if(!@$db->db_query("SELECT entwicklungsteam_id FROM bis.tbl_entwicklungsteam LIMIT 1"))
{
	$qry = 'ALTER TABLE bis.tbl_entwicklungsteam ADD COLUMN entwicklungsteam_id integer;';

	if(!$db->db_query($qry))
		echo '<strong> bis.tbl_entwicklungsteam '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>bis.tbl_entwicklungsteam: Neue Spalte entwicklungsteam_id hinzugefügt';
}

//Column entwicklungsteam_id mit Werten befüllen
if($result = @$db->db_query("SELECT entwicklungsteam_id FROM bis.tbl_entwicklungsteam where entwicklungsteam_id is not null LIMIT 1"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = 'UPDATE bis.tbl_entwicklungsteam et SET entwicklungsteam_id =
		(SELECT rownumber FROM (SELECT ROW_NUMBER() OVER (ORDER BY mitarbeiter_uid, studiengang_kz)
			AS rownumber, t.* FROM bis.tbl_entwicklungsteam t ORDER BY mitarbeiter_uid, studiengang_kz) rn
			WHERE rn.mitarbeiter_uid = et.mitarbeiter_uid
			AND rn.studiengang_kz = et.studiengang_kz);
			';

		if(!$db->db_query($qry))
			echo '<strong> bis.tbl_entwicklungsteam '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>bis.tbl_entwicklungsteam: Spalte bis.tbl_entwicklungsteam_id mit Werten aufgefüllt';
	}
}

//Create Sequence bis.tbl_entwicklungsteam and grant Rights
if ($result = @$db->db_query("SELECT * FROM pg_class WHERE relname = 'tbl_entwicklungsteam_entwicklungsteam_id_seq'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		if ($count = @$db->db_query("SELECT * FROM bis.tbl_entwicklungsteam"))
		{
			$count = $db->db_num_rows($count) + 1;
			$qry = 'CREATE SEQUENCE bis.tbl_entwicklungsteam_entwicklungsteam_id_seq START ';
			$qry .= $count;
			if(!$db->db_query($qry))
			{
				echo '<strong> bis.tbl_entwicklungsteam '.$db->db_last_error().'</strong><br>';
			}
			else
			{
				echo '<br>bis.tbl_entwicklungsteam: Sequence bis.tbl_entwicklungsteam_entwicklungsteam_id_seq mit Startwert ' . $count . ' erstellt';
				$qry2 = "GRANT SELECT, UPDATE ON bis.tbl_entwicklungsteam_entwicklungsteam_id_seq TO vilesci;
						GRANT SELECT, UPDATE ON bis.tbl_entwicklungsteam_entwicklungsteam_id_seq TO web;";
				if(!$db->db_query($qry2))
				{
					echo '<strong>bis.tbl_entwicklungsteam_entwicklungsteam_id_seqBerechtigungen: '.$db->db_last_error().'</strong><br>';
				}
				else
				{
					echo '<br>bis.tbl_entwicklungsteam: Rechte auf bis.tbl_entwicklungsteam_entwicklungsteam_id_seq fuer web user und vilesci gesetzt ';
				}
			}

		}
	}
}

//Bis.tbl_entwicklungsteam auf NOTNULL setzen
if ($result = @$db->db_query("SELECT is_nullable FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = 'tbl_entwicklungsteam' AND column_name = 'entwicklungsteam_id' and is_nullable = 'NO'"))
{
	if($db->db_num_rows($result)==0)
	{
		$qry = 'ALTER TABLE bis.tbl_entwicklungsteam ALTER COLUMN entwicklungsteam_id SET NOT NULL';

		if(!$db->db_query($qry))
		echo '<strong> bis.tbl_entwicklungsteam '.$db->db_last_error().'</strong><br>';
			else
		echo '<br>bis.tbl_entwicklungsteam: Spalte bis.tbl_entwicklungsteam_id auf NOT NULL gesetzt';
	}
}

//Bis.tbl_entwicklungsteam DEFAULT einstellen
if ($result = @$db->db_query("SELECT column_default FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = 'tbl_entwicklungsteam'AND column_name = 'entwicklungsteam_id' and column_default is null"))
{
	if($db->db_num_rows($result)==1)
	{
		$qry = "ALTER TABLE bis.tbl_entwicklungsteam ALTER COLUMN entwicklungsteam_id SET DEFAULT nextval('bis.tbl_entwicklungsteam_entwicklungsteam_id_seq'::regclass);";

		if(!$db->db_query($qry))
			echo '<strong> bis.tbl_entwicklungsteam '.$db->db_last_error().'</strong><br>';
		else
			echo '<br> bis.tbl_entwicklungsteam: Defaultwert bei Spalte bis.tbl_entwicklungsteam_id gesetzt';
	}
}

//DELETE Constraint PRIMARY KEY pk_tbl_entwicklungsteam (mitarbeiter_uid, studiengang_kz) entfernen
if ($result = @$db->db_query("SELECT conname FROM pg_constraint WHERE conname = 'pk_tbl_entwicklungsteam'"))
{
	if($db->db_num_rows($result)==1)
	{
		$qry = "ALTER TABLE bis.tbl_entwicklungsteam DROP CONSTRAINT pk_tbl_entwicklungsteam;";

		if (!$db->db_query($qry))
			echo '<strong>bis.tbl_entwicklungsteam: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>bis.tbl_entwicklungsteam: Primary Key pk_tbl_entwicklungsteam (mitarbeiter_uid, studiengang_kz) entfernt ';
	}
}

// ADD PRIMARY KEY tbl_entwicklungsteam_pk to bis.tbl_entwicklungsteam
if ($result = @$db->db_query("SELECT conname FROM pg_constraint WHERE conname = 'tbl_entwicklungsteam_pk'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "ALTER TABLE bis.tbl_entwicklungsteam ADD CONSTRAINT tbl_entwicklungsteam_pk PRIMARY KEY(entwicklungsteam_id);";

		if (!$db->db_query($qry))
			echo '<strong>sbis.tbl_entwicklungsteam: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>bis.tbl_entwicklungsteam: Primary Key tbl_entwicklungsteam_pk (entwicklungsteam_id) hinzugefügt';
	}
}
