<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class InfoCenter extends VileSci_Controller
{
	public function __construct()
    {
        parent::__construct();

        $this->load->library('WidgetLib');
    }

	/**
	 *
	 */
	public function index()
	{
		$listFiltersSent = array(
			'Sent 1' => 100,
			'Sent 2' => 200,
			'Sent 3' => 300
		);

		$listFiltersNotSent = array(
			'Not Sent 1' => 400,
			'Not Sent 2' => 500,
			'Not Sent 3' => 600
		);

		$this->load->view(
			'system/infocenter/infocenter.php',
			array(
				'listFiltersSent' => $listFiltersSent,
				'listFiltersNotSent' => $listFiltersNotSent
			)
		);
	}

	/**
	 *
	 */
	public function filter($filterId = null)
	{
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
			'hideHeader' => false,
			'hideSave' => false,
			'checkboxes' => array('PersonId'),
			'additionalColumns' => array('Delete', 'Edit'),
			'formatRaw' => function($fieldName, $fieldValue, $datasetRaw) {

				if ($fieldName == 'PersonId')
				{
					$datasetRaw->{$fieldName} = '<a href="view/'.$datasetRaw->PersonId.'">'.$fieldValue.'</a>';
				}
				elseif ($fieldName == 'Delete')
				{
					$datasetRaw->{$fieldName} = '<a href="delete/'.$datasetRaw->PersonId.'">Delete</a>';
				}
				elseif ($fieldName == 'Edit')
				{
					$datasetRaw->{$fieldName} = '<a href="edit/'.$datasetRaw->PersonId.'">Edit</a>';
				}

				return $datasetRaw;
			}
		);

		if ($filterId == null)
		{
			$filterWidgetArray['app'] = 'core';
			$filterWidgetArray['datasetName'] = 'kontakts';
			$filterWidgetArray['filterKurzbz'] = 'This filter filters';
		}
		else
		{
			$filterWidgetArray['filterId'] = $filterId;
		}

		echo $this->widgetlib->widget('FilterWidget', $filterWidgetArray);
	}
}
