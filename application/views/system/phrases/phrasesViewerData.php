<?php

	$filterWidgetArray = array(
		'query' => '
			SELECT p.phrase_id AS "PhraseId",
				p.app AS "Application",
				p.category AS "Category",
				p.phrase AS "PhraseName",
				pt.sprache AS "Language",
				pt.text AS "Phrase",
				pt.description AS "Description",
				pt.orgeinheit_kurzbz AS "OrganisationUnit",
				pt.orgform_kurzbz AS "OrganizationalForm"
			 FROM system.tbl_phrase p
			 JOIN system.tbl_phrasentext pt USING(phrase_id)
		 ORDER BY p.app, p.category, p.phrase, pt.sprache
		',
		'requiredPermissions' => 'admin',
		'datasetRepresentation' => 'tablesorter',
		'columnsAliases' => array(
			'Phrase id',
			'Application',
			'Category',
			'Phrase name',
			'Language',
			'Phrase',
			'Description',
			'Organisation unit',
			'Organizational form'
		),
		'formatRow' => function($datasetRaw) {

			if (isEmptyString($datasetRaw->Description)) $datasetRaw->Description = 'NA';
			if (isEmptyString($datasetRaw->OrganisationUnit)) $datasetRaw->OrganisationUnit = 'NA';
			if (isEmptyString($datasetRaw->OrganizationalForm)) $datasetRaw->OrganizationalForm = 'NA';

			return $datasetRaw;
		}
	);

	$filterWidgetArray['app'] = 'core';
	$filterWidgetArray['datasetName'] = 'phrases';
	$filterWidgetArray['filter_id'] = $this->input->get('filter_id');

	echo $this->widgetlib->widget('FilterWidget', $filterWidgetArray);
?>

