<?php
// neues widget mit widget_kurzbz = 'studstatus' als Zeile hinzufügen
if($result = @$db->db_query("SELECT 1 FROM dashboard.tbl_widget WHERE widget_kurzbz= 'studstatus';"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "
			INSERT INTO dashboard.tbl_widget (
				widget_id,
				widget_kurzbz,
				beschreibung,
				arguments,
				setup
			)
			VALUES (
				9,
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
				}'::jsonb
			);";

		if(!$db->db_query($qry))
			echo '<strong>dashboard.tbl_widget: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>dashboard.tbl_widget: Widget studstatus hinzugefuegt!<br>';
	}
}
