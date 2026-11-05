<?php
// Add permission: dashboard/widget_studstatus_ma
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz = 'dashboard/widget_studstatus_ma';"))
{
    if($db->db_num_rows($result) == 0)
    {
        $qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES('dashboard/widget_studstatus_ma', 'Dashboard Widget Studstatus f. Mitarbeiter');";

        if(!$db->db_query($qry))
        {
            echo '<strong>system.tbl_berechtigung '.$db->db_last_error().'</strong><br>';
        }
        else
        {
            echo 'system.tbl_berechtigung: Added permission "dashboard/widget_studstatus_ma"<br>';
        }
    }
}

// neues widget mit widget_kurzbz = 'studstatus' als Zeile hinzufügen
if($result = @$db->db_query("SELECT 1 FROM dashboard.tbl_widget WHERE widget_kurzbz= 'studstatus';"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "
			INSERT INTO dashboard.tbl_widget (
				widget_kurzbz,
				beschreibung,
				arguments,
				setup,
				berechtigung_kurzbz
			)
			VALUES (
				'studstatus',
				'Widget Todos Studierendenstatus',
				'{
					\"css\": \"d-flex justify-content-center align-items-center h-100\",
					\"title\": \"Tasks Studstatus\"
				}'::jsonb,
				'{
					\"file\": \"public/js/components/DashboardWidget/StudStatus.js\",
					\"icon\": \"/skin/images/fh_technikum_wien_illustration_klein.png\",
					\"name\": \"Studierendenstatus\",
					\"width\": {
						\"max\": 2,
						\"min\": 1
					},
					\"height\": {
						\"max\": 2,
						\"min\": 1
					},
					\"hideFooter\": true
				}'::jsonb,
				'dashboard/widget_studstatus_ma'
			);";

		if(!$db->db_query($qry))
			echo '<strong>dashboard.tbl_widget: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>dashboard.tbl_widget: Widget studstatus hinzugefuegt!<br>';
	}
}

// neuer eintrag in dashboard.tbl_dashboard_widget
if($result = @$db->db_query("SELECT 1 FROM dashboard.tbl_dashboard_widget join dashboard.tbl_dashboard USING(dashboard_id) join dashboard.tbl_widget USING(widget_id) WHERE dashboard_kurzbz= 'CIS' AND widget_kurzbz = 'studstatus';"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "
			INSERT INTO
			  dashboard.tbl_dashboard_widget (dashboard_id, widget_id)
			VALUES
			  (
				(SELECT dashboard_id FROM dashboard.tbl_dashboard WHERE dashboard_kurzbz = 'CIS'),
				(SELECT widget_id FROM dashboard.tbl_widget WHERE widget_kurzbz = 'studstatus')
			  );
			";

		if(!$db->db_query($qry))
			echo '<strong>dashboard.tbl_dashboard_widget: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>dashboard.tbl_dashboard_widget: Widget "studstatus" zum Dashboard "CIS" hinzugefuegt!<br>';
	}
}

// Add permission: dashboard/widget_studstatus_ma if widget was created without
if($result = @$db->db_query("SELECT 1 FROM dashboard.tbl_widget WHERE berechtigung_kurzbz = 'dashboard/widget_studstatus_ma' and widget_kurzbz = 'studstatus';"))
{
    if($db->db_num_rows($result) == 0)
    {
        $qry = "UPDATE dashboard.tbl_widget SET berechtigung_kurzbz = 'dashboard/widget_studstatus_ma' WHERE widget_kurzbz = 'studstatus';";

        if(!$db->db_query($qry))
        {
            echo '<strong>dashboard.tbl_widget '.$db->db_last_error().'</strong><br>';
        }
        else
        {
            echo 'dashboard.tbl_widget: Added permission "dashboard/widget_studstatus_ma" to widget "studstatus"<br>';
        }
    }
}