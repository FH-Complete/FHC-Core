<?php

class Studiensemester_widget extends DropdownWidget
{
    public function display($widgetData)
	{
		// Studiensemester
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
		$this->StudiensemesterModel->addOrder('start', 'DESC');
		
		$this->addSelectToModel($this->StudiensemesterModel, 'studiensemester_kurzbz', 'studiensemester_kurzbz');
		
		$this->setElementsArray(
			$this->StudiensemesterModel->load(),
			true,
			$this->p->t('lehre', 'studiensemester'),
			'No studiensemester found'
		);
		
		$this->loadDropDownView($widgetData);
    }
}