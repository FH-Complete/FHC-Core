<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

//Add column begruendung_ects to lehre.tbl_anrechnung
if(!@$db->db_query("SELECT begruendung_ects FROM lehre.tbl_anrechnung LIMIT 1"))
{
	$qry = "ALTER TABLE lehre.tbl_anrechnung ADD COLUMN begruendung_ects text;
			COMMENT ON COLUMN lehre.tbl_anrechnung.begruendung_ects IS 'Begruendung gleichwertiger ECTS';
			";

	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_anrechnung '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Spalte begruendung_ects zu Tabelle lehre.tbl_anrechnung hinzugefügt';
}

//Add column begruendung_lvinhalt to lehre.tbl_anrechnung
if(!@$db->db_query("SELECT begruendung_lvinhalt FROM lehre.tbl_anrechnung LIMIT 1"))
{
    $qry = "ALTER TABLE lehre.tbl_anrechnung ADD COLUMN begruendung_lvinhalt text;
			COMMENT ON COLUMN lehre.tbl_anrechnung.begruendung_lvinhalt IS 'Begruendung gleichwertiger LV-Inhalte';
			";

    if(!$db->db_query($qry))
        echo '<strong>lehre.tbl_anrechnung '.$db->db_last_error().'</strong><br>';
    else
        echo '<br>Spalte begruendung_lvinhalt zu Tabelle lehre.tbl_anrechnung hinzugefügt';
}