<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

if ($result = @$db->db_query("SELECT 1 FROM system.tbl_app WHERE app = 'tempus'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO system.tbl_app (app) VALUES ('tempus');";

		if (!$db->db_query($qry))
			echo '<strong>system.tbl_app: ' . $db->db_last_error() . '</strong><br>';
		else
			echo '<br>system.tbl_app: tempus hinzugefügt';
	}
}

if ($result = @$db->db_query("SELECT 1 FROM system.tbl_issue_status WHERE status_kurzbz = 'accepted'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO system.tbl_issue_status (status_kurzbz, bezeichnung_mehrsprachig)
				VALUES ('accepted', '{\"Akzeptiert\",\"Accepted\"}');";

		if (!$db->db_query($qry))
			echo '<strong>system.tbl_issue_status accepted: ' . $db->db_last_error() . '</strong><br>';
		else
			echo '<br>system.tbl_issue_status: accepted hinzugefügt';
	}
}

if ($result = @$db->db_query("SELECT 1 FROM pg_class WHERE relname = 'idx_issue_kalender_id_a'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "CREATE INDEX idx_issue_kalender_id_a
					ON system.tbl_issue (((behebung_parameter->>'kalender_id_a')::integer)) 
				WHERE verarbeitetamum IS NULL AND (behebung_parameter->>'kalender_id_a') IS NOT NULL;";

		if (!$db->db_query($qry))
			echo '<strong>Index idx_issue_kalender_id_a angelegt: ' . $db->db_last_error() . '</strong><br>';
		else
			echo '<br>Index idx_issue_kalender_id_a angelegt';
	}
}

if ($result = @$db->db_query("SELECT 1 FROM pg_class WHERE relname = 'idx_issue_kalender_id_b'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "CREATE INDEX idx_issue_kalender_id_b
					ON system.tbl_issue (((behebung_parameter->>'kalender_id_b')::integer)) 
				WHERE verarbeitetamum IS NULL AND (behebung_parameter->>'kalender_id_a') IS NOT NULL;";

		if (!$db->db_query($qry))
			echo '<strong>Index idx_issue_kalender_id_b angelegt: ' . $db->db_last_error() . '</strong><br>';
		else
			echo '<br>Index idx_issue_kalender_id_b angelegt';
	}
}

if ($result = @$db->db_query("SELECT 1 FROM pg_class WHERE relname = 'idx_issue_offen_fehlercode'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "CREATE INDEX idx_issue_offen_fehlercode ON system.tbl_issue (fehlercode) WHERE verarbeitetamum IS NULL;";

		if (!$db->db_query($qry))
			echo '<strong>Index idx_issue_offen_fehlercode angelegt: ' . $db->db_last_error() . '</strong><br>';
		else
			echo '<br>Index idx_issue_offen_fehlercode angelegt';
	}
}

if ($result = @$db->db_query("SELECT 1 FROM pg_class WHERE relname = 'idx_kalender_von_bis'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "CREATE INDEX idx_kalender_von_bis ON lehre.tbl_kalender (von, bis);";

		if (!$db->db_query($qry))
			echo '<strong>Index idx_kalender_von_bis angelegt: ' . $db->db_last_error() . '</strong><br>';
		else
			echo '<br>Index idx_kalender_von_bis angelegt';
	}
}

if ($result = @$db->db_query("SELECT 1 FROM pg_class WHERE relname = 'idx_kalender_vorgaenger_kalender_id'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "CREATE INDEX idx_kalender_vorgaenger_kalender_id ON lehre.tbl_kalender (vorgaenger_kalender_id) WHERE vorgaenger_kalender_id IS NOT NULL;";

		if (!$db->db_query($qry))
			echo '<strong>Index idx_kalender_vorgaenger_kalender_id angelegt: ' . $db->db_last_error() . '</strong><br>';
		else
			echo '<br>Index idx_kalender_vorgaenger_kalender_id angelegt';
	}
}

if ($result = @$db->db_query("SELECT 1 FROM pg_class WHERE relname = 'idx_kalender_reservierung'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "CREATE INDEX idx_kalender_reservierung ON lehre.tbl_kalender (kalender_id) WHERE typ = 'reservierung';";

		if (!$db->db_query($qry))
			echo '<strong>Index idx_kalender_reservierung angelegt: ' . $db->db_last_error() . '</strong><br>';
		else
			echo '<br>Index idx_kalender_reservierung angelegt';
	}
}

if ($result = @$db->db_query("SELECT 1 FROM pg_class WHERE relname = 'idx_kalender_eindeutige_gruppe'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "CREATE INDEX idx_kalender_eindeutige_gruppe ON lehre.tbl_kalender (eindeutige_kalender_gruppen_id);";

		if (!$db->db_query($qry))
			echo '<strong>Index idx_kalender_eindeutige_gruppe angelegt: ' . $db->db_last_error() . '</strong><br>';
		else
			echo '<br>Index idx_kalender_eindeutige_gruppe angelegt';
	}
}

if ($result = @$db->db_query("SELECT 1 FROM pg_class WHERE relname = 'idx_lehreinheitmitarbeiter_uid'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "CREATE INDEX idx_lehreinheitmitarbeiter_uid ON lehre.tbl_lehreinheitmitarbeiter (mitarbeiter_uid);";

		if (!$db->db_query($qry))
			echo '<strong>Index idx_lehreinheitmitarbeiter_uid angelegt: ' . $db->db_last_error() . '</strong><br>';
		else
			echo '<br>Index idx_lehreinheitmitarbeiter_uid angelegt';
	}
}

if ($result = @$db->db_query("SELECT 1 FROM pg_class WHERE relname = 'idx_kalender_lehreinheit_lehreinheit_id'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "CREATE INDEX idx_kalender_lehreinheit_lehreinheit_id ON lehre.tbl_kalender_lehreinheit (lehreinheit_id);";

		if (!$db->db_query($qry))
			echo '<strong>Index idx_kalender_lehreinheit_lehreinheit_id angelegt: ' . $db->db_last_error() . '</strong><br>';
		else
			echo '<br>Index idx_kalender_lehreinheit_lehreinheit_id angelegt';
	}
}

if ($result = @$db->db_query("SELECT 1 FROM pg_class WHERE relname = 'idx_kalender_event_teilnehmer_uid'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "CREATE INDEX idx_kalender_event_teilnehmer_uid ON lehre.tbl_kalender_event_teilnehmer (uid);";

		if (!$db->db_query($qry))
			echo '<strong>Index idx_kalender_event_teilnehmer_uid angelegt: ' . $db->db_last_error() . '</strong><br>';
		else
			echo '<br>Index idx_kalender_event_teilnehmer_uid angelegt';
	}
}

if ($result = @$db->db_query("SELECT 1 FROM pg_class WHERE relname = 'idx_zeitsperre_uid_datum'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "CREATE INDEX idx_zeitsperre_uid_datum ON campus.tbl_zeitsperre (mitarbeiter_uid, vondatum, bisdatum);";

		if (!$db->db_query($qry))
			echo '<strong>Index idx_zeitsperre_uid_datum angelegt: ' . $db->db_last_error() . '</strong><br>';
		else
			echo '<br>Index idx_zeitsperre_uid_datum angelegt';
	}
}

if ($result = @$db->db_query("SELECT 1 FROM pg_class WHERE relname = 'idx_benutzergruppe_gruppe_semester'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "CREATE INDEX idx_benutzergruppe_gruppe_semester ON public.tbl_benutzergruppe (gruppe_kurzbz, studiensemester_kurzbz);";

		if (!$db->db_query($qry))
			echo '<strong>Index idx_benutzergruppe_gruppe_semester angelegt: ' . $db->db_last_error() . '</strong><br>';
		else
			echo '<br>Index idx_benutzergruppe_gruppe_semester angelegt';
	}
}

if ($result = @$db->db_query("SELECT 1 FROM pg_class WHERE relname = 'idx_studentlehrverband_stg_sem_studsem'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "CREATE INDEX idx_studentlehrverband_stg_sem_studsem ON public.tbl_studentlehrverband (studiengang_kz, semester, studiensemester_kurzbz);";

		if (!$db->db_query($qry))
			echo '<strong>Index idx_studentlehrverband_stg_sem_studsem angelegt: ' . $db->db_last_error() . '</strong><br>';
		else
			echo '<br>Index idx_studentlehrverband_stg_sem_studsem angelegt';
	}
}

if ($result = @$db->db_query("SELECT 1 FROM pg_class WHERE relname = 'idx_betriebsmittel_kalender_gruppe'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "CREATE INDEX idx_betriebsmittel_kalender_gruppe ON lehre.tbl_betriebsmittel_kalender (eindeutige_kalender_gruppen_id);";

		if (!$db->db_query($qry))
			echo '<strong>Index idx_betriebsmittel_kalender_gruppe angelegt: ' . $db->db_last_error() . '</strong><br>';
		else
			echo '<br>Index idx_betriebsmittel_kalender_gruppe angelegt';
	}
}

if ($result = @$db->db_query("SELECT 1 FROM pg_class WHERE relname = 'idx_betriebsmittel_kalender_bm'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "CREATE INDEX idx_betriebsmittel_kalender_bm ON lehre.tbl_betriebsmittel_kalender (betriebsmittel_id);";

		if (!$db->db_query($qry))
			echo '<strong>Index idx_betriebsmittel_kalender_bm angelegt: ' . $db->db_last_error() . '</strong><br>';
		else
			echo '<br>Index idx_betriebsmittel_kalender_bm angelegt';
	}
}