<?php

class Vorlage_widget extends DropdownWidget
{
    public function display($widgetData)
	{
		$this->load->model('system/Vorlage_model', 'VorlageModel');
		$this->VorlageModel->addOrder('vorlage_kurzbz');
		
		$this->addSelectToModel($this->VorlageModel, 'vorlage_kurzbz', 'bezeichnung');
		
		$this->setElementsArray(
			$this->VorlageModel->loadWhere(array('mimetype' => 'text/html')),
			true,
			'Select a vorlage...',
			'No vorlage found'
		);
		
		$this->loadDropDownView($widgetData);
    }
}