<?php

$viewsArray = array(
	'vw_projektressourcen' =>
		'CREATE OR REPLACE VIEW fue.vw_projektressourcen (
					projekt_ressource_id,
					projekt_kurzbz,
					projektphase_id,
					projektphase,
					typ,
					ressource_id,
					ressource,
					funktion_kurzbz,
					start,
					ende,
					oe_kurzbz,
					projektbudget,
					aufwandstyp_kurzbz,
					projektphase_fk,
					phasenbudget,
					personentage,
					nummer,
					titel,
					aufwand
				)
					AS
						SELECT tpr.projekt_ressource_id,
							COALESCE(tpr.projekt_kurzbz, tpp.projekt_kurzbz) AS projekt_kurzbz,
							tpr.projektphase_id,
							tpp.bezeichnung AS projektphase,
							COALESCE(tpp.typ, \'Projekt\'::character varying) AS typ,
							tpr.ressource_id,
							tr.bezeichnung AS ressource,
							tpr.funktion_kurzbz,
							COALESCE(tpp.start, tp.beginn) AS start,
							COALESCE(tpp.ende, tp.ende) AS ende,
							tp.oe_kurzbz,
							tp.budget AS projektbudget,
							tp.aufwandstyp_kurzbz,
							tpp.projektphase_fk,
							tpp.budget AS phasenbudget,
							tpp.personentage,
							tp.nummer,
							tp.titel,
							tpr.aufwand
						FROM fue.tbl_projekt_ressource tpr
							JOIN fue.tbl_ressource tr USING (ressource_id)
							LEFT JOIN fue.tbl_projekt tp USING (projekt_kurzbz)
							LEFT JOIN fue.tbl_projektphase tpp USING (projektphase_id);'
);

