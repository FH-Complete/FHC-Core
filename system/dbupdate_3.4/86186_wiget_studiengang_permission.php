<?php
// Add permission: dashboard/widget_studiengang
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz = 'dashboard/widget_studiengang';"))
{
    if($db->db_num_rows($result) == 0)
    {
        $qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES('dashboard/widget_studiengang', 'Dashboard Widget Studiengang anzeigen');";

        if(!$db->db_query($qry))
        {
            echo '<strong>system.tbl_berechtigung '.$db->db_last_error().'</strong><br>';
        }
        else
        {
            echo 'system.tbl_berechtigung: Added permission "dashboard/widget_studiengang"<br>';
        }
    }
}

// Add permission: dashboard/widget_studiengang since widget was created without
if($result = @$db->db_query("SELECT 1 FROM dashboard.tbl_widget WHERE berechtigung_kurzbz = 'dashboard/widget_studiengang' and widget_kurzbz = 'studiengang';"))
{
    if($db->db_num_rows($result) == 0)
    {
        $qry = "UPDATE dashboard.tbl_widget SET berechtigung_kurzbz = 'dashboard/widget_studiengang' WHERE widget_kurzbz = 'studiengang';";

        if(!$db->db_query($qry))
        {
            echo '<strong>dashboard.tbl_widget '.$db->db_last_error().'</strong><br>';
        }
        else
        {
            echo 'dashboard.tbl_widget: Added permission "dashboard/widget_studiengang" to widget "studiengang"<br>';
        }
    }
}
