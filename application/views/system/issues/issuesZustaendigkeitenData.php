<?php

// get assigned Zustaendigkeiten
$query = "SELECT fehlerzustaendigkeiten_id, fe.fehlercode, fe.fehlercode_extern, fehler_kurzbz, fehlertext, fehlertyp_kurzbz, fe.app,
       		pers.person_id, pers.vorname, pers.nachname,
       		oe.oe_kurzbz, oe.bezeichnung AS oe_bezeichnung, funk.funktion_kurzbz, funk.beschreibung AS funktion_beschreibung
			FROM system.tbl_fehler_zustaendigkeiten zst
			JOIN system.tbl_fehler fe USING (fehlercode)
			LEFT JOIN public.tbl_person pers USING (person_id)
			LEFT JOIN public.tbl_organisationseinheit oe USING (oe_kurzbz)
			LEFT JOIN public.tbl_funktion funk USING (funktion_kurzbz)
			ORDER BY fe.fehlercode, pers.nachname, oe.bezeichnung, funk.beschreibung";

$filterWidgetArray = array(
    'query' => $query,
	'app' => 'core',
	'datasetName' => 'fehlerZustaendigkeiten',
	'filter_id' => $this->input->get('filter_id'),
    'tableUniqueId' => 'issuesZustaendigkeiten',
    'requiredPermissions' => 'admin',
    'datasetRepresentation' => 'tablesorter',
	'additionalColumns' => array('Delete'),
    'columnsAliases' => array(
    	'ID',
		ucfirst($this->p->t('fehlermonitoring', 'fehlercode')),
		ucfirst($this->p->t('fehlermonitoring', 'fehlercodeExtern')),
		ucfirst($this->p->t('fehlermonitoring', 'fehlerkurzbz')),
		ucfirst($this->p->t('fehlermonitoring', 'fehlertext')),
		ucfirst($this->p->t('fehlermonitoring', 'fehlertyp')),
		'app',
		'PersonId',
		ucfirst($this->p->t('person', 'vorname')),
		ucfirst($this->p->t('person', 'nachname')),
		ucfirst($this->p->t('fehlermonitoring', 'oeKurzbz')),
		ucfirst($this->p->t('fehlermonitoring', 'oeBezeichnung')),
		ucfirst($this->p->t('fehlermonitoring', 'funktionKurzbz')),
		ucfirst($this->p->t('fehlermonitoring', 'funktionBeschreibung'))
    ),
	'formatRow' => function($datasetRaw) {

		$datasetRaw->{'Delete'} =
			"<button id='".$datasetRaw->{'fehlerzustaendigkeiten_id'}."' class='btn btn-default deleteBtn'>"
			.ucfirst($this->p->t('ui', 'loeschen'))."</button>";

		if ($datasetRaw->{'person_id'} == null)
		{
			$datasetRaw->{'person_id'} = '-';
		}

		if ($datasetRaw->{'vorname'} == null)
		{
			$datasetRaw->{'vorname'} = '-';
		}

		if ($datasetRaw->{'nachname'} == null)
		{
			$datasetRaw->{'nachname'} = '-';
		}

		if ($datasetRaw->{'oe_kurzbz'} == null)
		{
			$datasetRaw->{'oe_kurzbz'} = '-';
		}

		if ($datasetRaw->{'oe_bezeichnung'} == null)
		{
			$datasetRaw->{'oe_bezeichnung'} = '-';
		}

		if ($datasetRaw->{'funktion_kurzbz'} == null)
		{
			$datasetRaw->{'funktion_kurzbz'} = '-';
		}

		if ($datasetRaw->{'funktion_beschreibung'} == null)
		{
			$datasetRaw->{'funktion_beschreibung'} = '-';
		}

		return $datasetRaw;
	}
);

echo $this->widgetlib->widget('FilterWidget', $filterWidgetArray);
