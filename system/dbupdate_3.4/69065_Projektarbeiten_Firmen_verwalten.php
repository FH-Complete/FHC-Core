<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

// Add permission: paarbeit/beurteilung_loeschen
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz = 'paarbeit/beurteilung_loeschen';"))
{
    if($db->db_num_rows($result) == 0)
    {
        $qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES('paarbeit/beurteilung_loeschen', 'Berechtigung zum Löschen von Projektarbeitsbeurteilung');";

        if(!$db->db_query($qry))
        {
            echo '<strong>system.tbl_berechtigung '.$db->db_last_error().'</strong><br>';
        }
        else
        {
            echo 'system.tbl_berechtigung: Added permission for paarbeit/beurteilung_loeschen<br>';
        }
    }
}