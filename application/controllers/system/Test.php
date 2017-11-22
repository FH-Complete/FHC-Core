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
	public function index()
	{
		echo $this->widgetlib->widget(
			'FilterWidget',
			array(
				'app' => 'OpenProject',
				'datasetName' => 'Arbeitspakete',
				'query' => '
					SELECT p.person_id AS PersonId,
							p.nachname AS Nachname,
							p.vorname AS Vorname,
							k.kontakt AS Email
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
