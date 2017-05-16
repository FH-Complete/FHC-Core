<?php

class Vorlage_widget extends DropdownWidget
{
    public function display($widgetData)
	{
		// Loads 
        $this->load->library('OrganisationseinheitLib');
		
		$vorlage = $this->organisationseinheitlib->treeSearchEntire(
			'(
				SELECT v.vorlage_kurzbz, v.bezeichnung, vs.version, vs.oe_kurzbz, vs.aktiv, vs.subject, vs.text, v.mimetype
				  FROM tbl_vorlagestudiengang vs INNER JOIN tbl_vorlage v USING(vorlage_kurzbz)
			) templates',
			'templates',
			array("templates.vorlage_kurzbz AS id", "UPPER(templates.oe_kurzbz) || ' - ' || templates.bezeichnung || ' - V' || templates.version AS description"),
			'templates.aktiv = TRUE
			AND templates.subject IS NOT NULL
			AND templates.text IS NOT NULL
			AND templates.mimetype = \'text/html\'',
			"description ASC",
			'eas',
			true
		);
		
		array_pop($vorlage->retval);
		
		$this->setElementsArray(
			$vorlage,
			true,
			'Select a vorlage...',
			'No vorlage found'
		);
		
		$this->loadDropDownView($widgetData);
    }
}