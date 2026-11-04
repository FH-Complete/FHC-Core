<?php
// Add permission: cis/eigene_zeitsperren
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz = 'cis/eigene_zeitsperren';"))
{
    if($db->db_num_rows($result) == 0)
    {
        $qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES('cis/eigene_zeitsperren', 'CI/VueJs Applikation Eigene Zeitsperren');";

        if(!$db->db_query($qry))
        {
            echo '<strong>system.tbl_berechtigung '.$db->db_last_error().'</strong><br>';
        }
        else
        {
            echo 'system.tbl_berechtigung: Added permission "cis/eigene_zeitsperren"<br>';
        }
    }
}
// Add permission: cis/andere_zeitsperren
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz = 'cis/andere_zeitsperren';"))
{
    if($db->db_num_rows($result) == 0)
    {
        $qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES('cis/andere_zeitsperren', 'CI/VueJs Applikation Zeitsperren anderer MA anzeigen');";

        if(!$db->db_query($qry))
        {
            echo '<strong>system.tbl_berechtigung '.$db->db_last_error().'</strong><br>';
        }
        else
        {
            echo 'system.tbl_berechtigung: Added permission "cis/andere_zeitsperren"<br>';
        }
    }
}