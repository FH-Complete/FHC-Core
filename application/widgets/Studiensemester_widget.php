<?php

class Studiensemester_widget extends DropdownWidget
{
    public function display($widgetData)
	{
		// Studiensemester
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
		$this->StudiensemesterModel->addOrder('studiensemester_kurzbz', 'DESC');
		
		$this->addSelectToModel($this->StudiensemesterModel, 'studiensemester_kurzbz', 'studiensemester_kurzbz');
		
		$this->setElementsArray(
			$this->StudiensemesterModel->load(),
			true,
			'Select a studiensemester...',
			'No studiensemester found'
		);
		
		$this->loadDropDownView($widgetData);
    }
}