<?php

// get assigned Konfiguration
$query = "SELECT
				konftyp.konfigurationstyp_kurzbz, fe.fehlercode, fe.fehler_kurzbz, konf.konfiguration, fe.app,
				konftyp.beschreibung AS  konfigurationsbeschreibung, konftyp.konfigurationsdatentyp, fe.fehlertext
			FROM
				system.tbl_fehler_konfiguration konf
				JOIN system.tbl_fehler_konfigurationstyp konftyp USING (konfigurationstyp_kurzbz)
				JOIN system.tbl_fehler fe USING (fehlercode)
			ORDER BY
				konf.konfigurationstyp_kurzbz, fe.fehlercode";

$filterWidgetArray = array(
	'query' => $query,
	'app' => 'core',
	'datasetName' => 'fehlerKonfiguration',
	'filter_id' => $this->input->get('filter_id'),
	'tableUniqueId' => 'issuesKonfiguration',
	'requiredPermissions' => 'admin',
	'datasetRepresentation' => 'tablesorter',
	'additionalColumns' => array('Delete'),
	'columnsAliases' => array(
		ucfirst($this->p->t('fehlermonitoring', 'konfigurationstyp')),
		ucfirst($this->p->t('fehlermonitoring', 'fehlercode')),
		ucfirst($this->p->t('fehlermonitoring', 'fehlerkurzbz')),
		ucfirst($this->p->t('fehlermonitoring', 'konfigurationswert')),
		'Application',
		ucfirst($this->p->t('fehlermonitoring', 'konfigurationsbeschreibung')),
		ucfirst($this->p->t('fehlermonitoring', 'konfigurationsdatentyp')),
		ucfirst($this->p->t('fehlermonitoring', 'fehlertext')),
	),
	'formatRow' => function($datasetRaw) {

		$datasetRaw->{'Delete'} =
			"<button
				data-konfigurationstyp-kurzbz='".$datasetRaw->{'konfigurationstyp_kurzbz'}."'
				data-fehlercode='".$datasetRaw->{'fehlercode'}."'
				class='btn btn-default deleteBtn'>"
			.ucfirst($this->p->t('ui', 'loeschen'))."</button>";

		if ($datasetRaw->{'konfigurationsbeschreibung'} == null)
		{
			$datasetRaw->{'konfigurationsbeschreibung'} = '-';
		}

		if ($datasetRaw->{'fehlertext'} == null)
		{
			$datasetRaw->{'fehlertext'} = '-';
		}

		return $datasetRaw;
	}
);

echo $this->widgetlib->widget('FilterWidget', $filterWidgetArray);
