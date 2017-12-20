<?php
	$filterWidgetArray = array(
		'query' => '
			SELECT p.person_id AS "PersonId",
					p.nachname AS "Nachname",
					p.vorname AS "Vorname",
					k.kontakt AS "Email",
					p.aktiv AS "Aktiv",
					k.updateamum AS "UpdateDate"
			  FROM public.tbl_person p INNER JOIN public.tbl_kontakt k USING(person_id)
			 WHERE p.aktiv = TRUE
			   AND p.person_id = k.person_id
			   AND k.kontakttyp = \'email\'
			   AND p.person_id < 1000
		',
		'hideHeader' => true,
		'hideSave' => true,
		'additionalColumns' => array('Details'),
		'formatRaw' => function($fieldName, $fieldValue, $datasetRaw) {

			$link = '<a href="%s%s" target="_blank">Details</a>';

			if ($fieldName == 'Details')
			{
				$datasetRaw->{$fieldName} = sprintf(
					$link,
					base_url('index.ci.php/system/infocenter/infocenterDetails/showDetails/'),
					$datasetRaw->PersonId
				);
			}

			return $datasetRaw;
		}
	);

	$filterId = isset($_GET['filterId']) ? $_GET['filterId'] : null;

	if (isset($filterId) && is_numeric($filterId))
	{
		$filterWidgetArray['filterId'] = $filterId;
	}
	else
	{
		$filterWidgetArray['app'] = 'core';
		$filterWidgetArray['datasetName'] = 'kontakts';
		$filterWidgetArray['filterKurzbz'] = 'This filter filters';
	}

	echo $this->widgetlib->widget('FilterWidget', $filterWidgetArray);
?>
