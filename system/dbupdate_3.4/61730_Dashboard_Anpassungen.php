<?php

$result = @$db->db_query("SELECT preset_id FROM dashboard.tbl_dashboard_preset WHERE preset ? 'widgets'");
$result_num_rows = $db->db_num_rows($result);
$errorList = array();
for($i = 0; $i < $result_num_rows; $i++)
{
	$row = $db->db_fetch_object($result, $i);

	$qry = "
	UPDATE dashboard.tbl_dashboard_preset
	SET preset = 
	COALESCE(
	(SELECT jsonb_object_agg(keys,values) FROM (
		SELECT key AS keys,jsonb_build_object('widgets',to_jsonb(value)) AS values
		FROM jsonb_each(preset->'widgets')
	) AS subquery 
	),preset)
	WHERE preset_id = ".$row->preset_id;

	$db->db_query($qry);
	if (!$db->db_query($qry))
        array_push($errorList,'<br><strong>dashboard.tbl_dashboard_preset: ' . $db->db_last_error() . '</strong><br>') ;
    
}

$result = @$db->db_query("SELECT override_id FROM dashboard.tbl_dashboard_benutzer_override WHERE override ? 'widgets'");
$result_num_rows = $db->db_num_rows($result);
for($i = 0; $i < $result_num_rows; $i++)
{
	$row = $db->db_fetch_object($result, $i);

	$qry = "
	UPDATE dashboard.tbl_dashboard_benutzer_override
	SET override = 
	COALESCE(
	(SELECT jsonb_object_agg(keys,values) FROM (
		SELECT key AS keys,jsonb_build_object('widgets',to_jsonb(value)) AS values
		FROM jsonb_each(override->'widgets')
	) AS subquery 
	),override)
	WHERE override_id = ".$row->override_id;

	$db->db_query($qry);
	if (!$db->db_query($qry))
        array_push($errorList,'<br><strong>dashboard.tbl_dashboard_benutzer_override: ' . $db->db_last_error() . '</strong><br>') ;
    
}

if(empty($errorList)){
	echo '<br>successfully updated dashboard.tbl_dashboard_preset and dashboard.tbl_dashboard_benutzer_override';
}
else{
	foreach($errorList as $error){
		echo $error;
	}
}
