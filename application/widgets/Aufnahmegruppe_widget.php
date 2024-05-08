<?php

class Aufnahmegruppe_widget extends DropdownWidget
{
    public function display($widgetData)
	{
		// Gruppen
		$this->load->model('organisation/Gruppe_model', 'GruppeModel');
		$this->GruppeModel->addOrder('beschreibung');
		
		$this->addSelectToModel($this->GruppeModel, 'gruppe_kurzbz', 'beschreibung');
		
		$this->setElementsArray(
			$this->GruppeModel->loadWhere(array('aktiv' => true, 'aufnahmegruppe' => true)),
			true,
			'Select a group...',
			'No aufnahmegruppe found'
		);
		
		$this->loadDropDownView($widgetData);
    }
}