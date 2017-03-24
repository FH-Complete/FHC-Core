<?php

class Aufnahmegruppe_widget extends Widget
{
	public function __construct($name, $args, $htmlArgs = array())
	{
		// Calling daddy
		parent::__construct($name, $args, $htmlArgs);
	}
	
    public function display($widgetData)
	{
		// Gruppen
		$this->load->model('organisation/Gruppe_model', 'GruppeModel');
		$this->GruppeModel->addOrder('beschreibung');
		$gruppen = $this->GruppeModel->loadWhere(array('aktiv' => true, 'aufnahmegruppe' => true));
		if (hasData($gruppen))
		{
			// Adding an empty element at the beginning
			$emptyElement = new stdClass();
			$emptyElement->gruppe_kurzbz = Partial::HTML_DEFAULT_VALUE;
			$emptyElement->beschreibung = 'Select a group...';
			array_unshift($gruppen->retval, $emptyElement);
		}
		else
		{
			show_error($gruppen);
		}
		
		// Data to be used in the widget view
		$widgetData['aufnahmegruppen'] = $gruppen->retval;
		
		// Loads widget view
		$this->view('widgets/aufnahmegruppe', $widgetData);
    }
}