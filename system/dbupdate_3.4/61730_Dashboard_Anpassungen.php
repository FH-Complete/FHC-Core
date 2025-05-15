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

if(empty($errorList)){
	echo '<br>successfully updated dashboard.tbl_dashboard_preset';
}
else{
	foreach($errorList as $error){
		echo $error;
	}
}
