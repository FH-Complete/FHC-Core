<?php

$ci =& get_instance();
$ci->load->library('PermissionLib');

$oes = array('');
$oes = $ci->permissionlib->getOE_isEntitledFor('lehre/lvplan');

$filterCmptArray = array(
	'app' => 'tempus',
	'datasetName' => 'tempusIssues',
	'query' => "
		SELECT
			issue.issue_id,
			CASE
				WHEN issue.fehlercode LIKE 'TEMPUS_AZG%' THEN 'AZG'
				WHEN issue.fehlercode LIKE 'TEMPUS_KOLL%' THEN 'Kollision'
			END AS kategorie,
			fehler.fehler_kurzbz,
			issue.inhalt,
			UPPER(issue.oe_kurzbz) AS oe_anzeige,
			issue.status_kurzbz,
			COALESCE(termin_a.von, (issue.behebung_parameter->>'datum')::timestamp) AS von,
			COALESCE(termin_a.von, (issue.behebung_parameter->>'datum')::timestamp)::date - current_date AS tage_bis_termin
		FROM system.tbl_issue issue
		JOIN system.tbl_fehler fehler ON fehler.fehlercode = issue.fehlercode
		JOIN system.tbl_fehler_app fehler_app ON fehler_app.fehlercode = issue.fehlercode AND fehler_app.app = 'tempus'
		LEFT JOIN lehre.tbl_kalender termin_a ON termin_a.kalender_id = (issue.behebung_parameter->>'kalender_id_a')::integer
		WHERE issue.verarbeitetamum IS NULL AND issue.oe_kurzbz IN ('". implode("', '", $oes) . "')
		ORDER BY COALESCE(termin_a.von, (issue.behebung_parameter->>'datum')::timestamp)
	",
	'requiredPermissions' => 'lehre/lvplan'
);