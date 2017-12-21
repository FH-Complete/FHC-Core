<?php

	$filterWidgetArray = array(
		'query' => '
			SELECT p.person_id AS "PersonId",
					p.vorname AS "Vorname",
					p.nachname AS "Nachname",
					p.gebdatum AS "Gebdatum",
					l.zeitpunkt AS "LastAction",
					l.insertvon AS "User/Operator"
			  FROM public.tbl_person p INNER JOIN system.tbl_log l USING(person_id)
			 WHERE p.aktiv = TRUE
			   AND l.app = \'aufnahme\'
		',
		'hideHeader' => false,
		'hideSave' => false,
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
		$filterWidgetArray['app'] = 'aufnahme';
		$filterWidgetArray['datasetName'] = 'PersonActions';
		$filterWidgetArray['filterKurzbz'] = 'InfoCenterNotSentApplicationAll';
	}

	echo $this->widgetlib->widget('FilterWidget', $filterWidgetArray);
?>
