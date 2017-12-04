<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Test extends VileSci_Controller
{
	public function __construct()
    {
        parent::__construct();

        $this->load->library('WidgetLib');
    }

	/**
	 *
	 */
	public function sql()
	{
		echo $this->widgetlib->widget(
			'FilterWidget',
			array(
				'app' => 'core',
				'datasetName' => 'Arbeitspakete',
				'query' => '
					SELECT p.person_id AS "PersonId",
							p.nachname AS "Nachname",
							p.vorname AS "Vorname",
							k.kontakt AS "Email",
							p.aktiv AS "Aktiv"
					  FROM public.tbl_person p INNER JOIN public.tbl_kontakt k USING(person_id)
					 WHERE p.aktiv = TRUE
					   AND p.person_id = k.person_id
					   AND k.kontakttyp = \'email\'
					   AND p.person_id < 1000
				'
			)
		);
	}

	/**
	 *
	 */
	public function direct()
	{
		echo $this->widgetlib->widget(
			'FilterWidget',
			array(
				'app' => 'core',
				'datasetName' => 'Arbeitspakete',
				'filter_kurzbz' => 'This filter filters',
				'query' => '
					SELECT p.person_id AS "PersonId",
							p.nachname AS "Nachname",
							p.vorname AS "Vorname",
							k.kontakt AS "Email",
							p.aktiv AS "Aktiv"
					  FROM public.tbl_person p INNER JOIN public.tbl_kontakt k USING(person_id)
					 WHERE p.aktiv = TRUE
					   AND p.person_id = k.person_id
					   AND k.kontakttyp = \'email\'
					   AND p.person_id < 1000
				'
			)
		);
	}
}
