<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

// Add multilingual name to Anrechnung Begruendung (German, English)
if ($result = $db->db_query("SELECT 1 FROM information_schema.columns WHERE table_schema = 'lehre' AND table_name = 'tbl_anrechnung_begruendung' AND column_name = 'bezeichnung_mehrsprachig'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		// Unknown names keep the German name as English name
		$qry = "
			ALTER TABLE lehre.tbl_anrechnung_begruendung ADD COLUMN bezeichnung_mehrsprachig character varying(128)[];

			UPDATE lehre.tbl_anrechnung_begruendung SET bezeichnung_mehrsprachig = ARRAY[
				bezeichnung,
				CASE bezeichnung
					WHEN 'externes Zeugnis' THEN 'external certificate'
					WHEN 'kompatible Lehrveranstaltung' THEN 'compatible course'
					WHEN 'Prüfung' THEN 'examination'
					WHEN 'berufliche Praxis' THEN 'professional practice'
					WHEN 'Hochschulzeugnis' THEN 'university certificate'
					ELSE bezeichnung
				END
			];
		";

		if (!$db->db_query($qry))
			echo '<strong>lehre.tbl_anrechnung_begruendung: ' . $db->db_last_error() . '</strong><br>';
		else
			echo '<br>lehre.tbl_anrechnung_begruendung: Spalte bezeichnung_mehrsprachig hinzugefuegt';
	}
}
