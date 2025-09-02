<?php
$filterCmptArray = array(
	"app" => 'core',
	'datasetName' => 'vertragsverwaltung',
	'query' =>  '
		SELECT
			b.uid , p.person_id,
			p.vorname, p.nachname,
			gebdatum,
			COALESCE(b.alias, b.uid) AS email,
			STRING_AGG(DISTINCT va.bezeichnung, \', \') AS Vertragsarten,
			STRING_AGG(DISTINCT u.bezeichnung, \', \') AS Unternehmen,
			STRING_AGG(d.dienstverhaeltnis_id::TEXT, \', \') AS ids,
				CASE
				WHEN b.aktiv = true THEN \'aktiv\'
				ELSE \'naktiv\'
				END AS "aktiv"
			FROM
				hr.tbl_dienstverhaeltnis d
			JOIN public.tbl_benutzer b ON d.mitarbeiter_uid = b.uid
			JOIN public.tbl_person p ON p.person_id = b.person_id
			JOIN public.tbl_organisationseinheit u ON d.oe_kurzbz = u.oe_kurzbz
			JOIN hr.tbl_vertragsart va ON d.vertragsart_kurzbz = va.vertragsart_kurzbz
			WHERE b.aktiv = true
			GROUP BY b.uid, p.person_id, p.vorname, p.nachname, b.alias, b.aktiv
			ORDER BY nachname, vorname
		',
	'requiredPermissions' => 'vertrag/mitarbeiter'
	);