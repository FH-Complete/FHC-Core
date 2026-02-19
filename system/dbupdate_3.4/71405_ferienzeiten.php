<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

//Add column ferien_id to lehre.tbl_ferien
if(!@$db->db_query("SELECT ferien_id FROM lehre.tbl_ferien LIMIT 1"))
{
	$qry = 'ALTER TABLE lehre.tbl_ferien ADD COLUMN ferien_id integer;';

	if(!$db->db_query($qry))
		echo '<strong> lehre.tbl_ferien '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>lehre.tbl_ferien: Neue Spalte ferien_id hinzugefügt';
}

//Column ferien_id mit Werten befüllen
if($result = @$db->db_query("SELECT ferien_id FROM lehre.tbl_ferien where ferien_id is not null LIMIT 1"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = 'UPDATE lehre.tbl_ferien et SET ferien_id =
		(SELECT rownumber FROM (SELECT ROW_NUMBER() OVER (ORDER BY bezeichnung, studiengang_kz)
			AS rownumber, t.* FROM lehre.tbl_ferien t ORDER BY bezeichnung, studiengang_kz) rn
			WHERE rn.bezeichnung = et.bezeichnung
			AND rn.studiengang_kz = et.studiengang_kz);
			';

		if(!$db->db_query($qry))
			echo '<strong> lehre.tbl_ferien '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>lehre.tbl_ferien: Spalte bis.tbl_ferien_id mit Werten aufgefüllt';
	}
}

//Create Sequence lehre.tbl_ferien and grant Rights
if ($result = @$db->db_query("SELECT * FROM pg_class WHERE relname = 'tbl_ferien_ferien_id_seq'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		if ($count = @$db->db_query("SELECT * FROM lehre.tbl_ferien"))
		{
			$count = $db->db_num_rows($count) + 1;
			$qry = 'CREATE SEQUENCE lehre.tbl_ferien_ferien_id_seq START ';
			$qry .= $count;
			if(!$db->db_query($qry))
			{
				echo '<strong> lehre.tbl_ferien '.$db->db_last_error().'</strong><br>';
			}
			else
			{
				echo '<br>lehre.tbl_ferien: Sequence lehre.tbl_ferien_ferien_id_seq mit Startwert ' . $count . ' erstellt';
				$qry2 = "GRANT SELECT, UPDATE ON lehre.tbl_ferien_ferien_id_seq TO vilesci;
						GRANT SELECT, UPDATE ON lehre.tbl_ferien_ferien_id_seq TO web;";
				if(!$db->db_query($qry2))
				{
					echo '<strong>lehre.tbl_ferien_ferien_id_seq Berechtigungen: '.$db->db_last_error().'</strong><br>';
				}
				else
				{
					echo '<br>lehre.tbl_ferien: Rechte auf lehre.tbl_ferien_ferien_id_seq fuer web user und vilesci gesetzt ';
				}
			}

		}
	}
}

//lehre.tbl_ferien auf NOT NULL setzen
if ($result = @$db->db_query("SELECT is_nullable FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = 'tbl_ferien' AND column_name = 'ferien_id' and is_nullable = 'NO'"))
{
	if($db->db_num_rows($result)==0)
	{
		$qry = 'ALTER TABLE lehre.tbl_ferien ALTER COLUMN ferien_id SET NOT NULL';

		if(!$db->db_query($qry))
		echo '<strong> lehre.tbl_ferien '.$db->db_last_error().'</strong><br>';
			else
		echo '<br>lehre.tbl_ferien: Spalte bis.tbl_ferien_id auf NOT NULL gesetzt';
	}
}

//lehre.tbl_ferien DEFAULT einstellen
if ($result = @$db->db_query("SELECT column_default FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = 'tbl_ferien' AND column_name = 'ferien_id' and column_default is null"))
{
	if($db->db_num_rows($result)==1)
	{
		$qry = "ALTER TABLE lehre.tbl_ferien ALTER COLUMN ferien_id SET DEFAULT nextval('lehre.tbl_ferien_ferien_id_seq'::regclass);";

		if(!$db->db_query($qry))
			echo '<strong> lehre.tbl_ferien '.$db->db_last_error().'</strong><br>';
		else
			echo '<br> lehre.tbl_ferien: Defaultwert bei Spalte bis.tbl_ferien_id gesetzt';
	}
}

//DELETE Constraint PRIMARY KEY pk_tbl_ferien (bezeichnung, studiengang_kz) entfernen
if ($result = @$db->db_query("SELECT conname FROM pg_constraint WHERE conname = 'pk_tbl_ferien'"))
{
	if($db->db_num_rows($result)==1)
	{
		$qry = "ALTER TABLE lehre.tbl_ferien DROP CONSTRAINT pk_tbl_ferien;";

		if (!$db->db_query($qry))
			echo '<strong>lehre.tbl_ferien: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>lehre.tbl_ferien: Primary Key pk_tbl_ferien (bezeichnung, studiengang_kz) entfernt ';
	}
}

// ADD PRIMARY KEY tbl_ferien_pk to lehre.tbl_ferien
if ($result = @$db->db_query("SELECT conname FROM pg_constraint WHERE conname = 'tbl_ferien_pk'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "ALTER TABLE lehre.tbl_ferien ADD CONSTRAINT tbl_ferien_pk PRIMARY KEY(ferien_id);";

		if (!$db->db_query($qry))
			echo '<strong>slehre.tbl_ferien: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>lehre.tbl_ferien: Primary Key tbl_ferien_pk (ferien_id) hinzugefügt';
	}
}
