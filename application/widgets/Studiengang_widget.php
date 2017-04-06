<?php

class Studiengang_widget extends DropdownWidget
{
    public function display($widgetData)
	{
		// Studiengaenge
		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');
		$this->StudiengangModel->addOrder('kurzbzlang');
		
		$this->addSelectToModel($this->StudiengangModel, 'studiengang_kz', '\'(\' || kurzbzlang || \') \' || bezeichnung');
		
		$this->setElementsArray(
			$this->StudiengangModel->loadWhere(array('aktiv' => true)),
			true,
			'Select a studiengang...',
			'No studiengaenge found'
		);
		
		$this->loadDropDownView($widgetData);
    }
}