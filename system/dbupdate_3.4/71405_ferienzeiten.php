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

if(!$result = @$db->db_query("SELECT oe_kurzbz FROM lehre.tbl_ferien LIMIT 1"))
{
	$qry = "ALTER TABLE lehre.tbl_ferien
			ADD COLUMN IF NOT EXISTS oe_kurzbz VARCHAR(32),
			ADD COLUMN IF NOT EXISTS studienplan_id SMALLINT DEFAULT NULL,
			ADD CONSTRAINT tbl_ferien_studienplan_fk
				FOREIGN KEY (studienplan_id)
				REFERENCES lehre.tbl_studienplan(studienplan_id)
				ON UPDATE CASCADE ON DELETE RESTRICT,
			ADD CONSTRAINT tbl_ferien_oe_kurzbz_fk
				FOREIGN KEY (oe_kurzbz)
				REFERENCES public.tbl_organisationseinheit(oe_kurzbz)
				ON UPDATE CASCADE ON DELETE RESTRICT,
			ADD COLUMN IF NOT EXISTS insertamum timestamp DEFAULT NOW(),
			ADD COLUMN IF NOT EXISTS insertvon VARCHAR(32),
			ADD COLUMN IF NOT EXISTS updateamum timestamp,
			ADD COLUMN IF NOT EXISTS updatevon VARCHAR(32)";

	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_ferien: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>lehre.tbl_ferien columns oe_kurzbz, studienplan_id, insertamum, insertvon, updateamum, updatevon hinzugefuegt';
}

// Creates table lehre.tbl_ferientyp if it doesn't exist and grants privileges
if (!$result = @$db->db_query('SELECT 0 FROM lehre.tbl_ferientyp WHERE 0 = 1'))
{
	$qry = 'CREATE TABLE lehre.tbl_ferientyp (
				ferientyp_kurzbz VARCHAR(64),
				beschreibung VARCHAR(256) NOT NULL,
				mitarbeiter boolean NOT NULL,
				studierende boolean NOT NULL,
				lehre boolean NOT NULL
			);

			COMMENT ON TABLE lehre.tbl_ferientyp IS \'Typ-Tabelle zum Speichern von Informationen zu Ferien.\';
			COMMENT ON COLUMN lehre.tbl_ferientyp.ferientyp_kurzbz IS \'Typ der Ferien.\';
			COMMENT ON COLUMN lehre.tbl_ferientyp.mitarbeiter IS \'Ob die Ferien für MitarbeiterInnen relevant sind.\';
			COMMENT ON COLUMN lehre.tbl_ferientyp.studierende IS \'Ob die Ferien für Studierende relevant sind.\';
			COMMENT ON COLUMN lehre.tbl_ferientyp.lehre IS \'Ob Lehre in den Ferien verplant werden kann.\';

			ALTER TABLE lehre.tbl_ferientyp ADD CONSTRAINT pk_tbl_ferientyp PRIMARY KEY (ferientyp_kurzbz);

			ALTER TABLE lehre.tbl_ferien ADD COLUMN IF NOT EXISTS ferientyp_kurzbz VARCHAR(64) DEFAULT NULL;

			ALTER TABLE lehre.tbl_ferien ADD CONSTRAINT tbl_lehre_ferien_ferientyp_kurzbz_fk FOREIGN KEY (ferientyp_kurzbz)
			REFERENCES lehre.tbl_ferientyp (ferientyp_kurzbz) MATCH FULL
			ON DELETE SET NULL ON UPDATE CASCADE;';

	if (!$db->db_query($qry))
		echo '<strong>lehre.tbl_ferientyp: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>lehre.tbl_ferientyp table created';

	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE lehre.tbl_ferientyp TO vilesci;';
	if (!$db->db_query($qry))
		echo '<strong>lehre.tbl_ferientyp: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on lehre.tbl_ferientyp';
}
