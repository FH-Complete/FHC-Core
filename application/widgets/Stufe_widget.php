<?php

class Stufe_widget extends DropdownWidget
{
    public function display($widgetData)
	{
		// Stufe
		$this->load->model('crm/Reihungstest_model', 'ReihungstestModel');
		$this->ReihungstestModel->addSelect('DISTINCT ON(stufe) stufe');
		$this->ReihungstestModel->addOrder('stufe');
		
		$this->addSelectToModel($this->ReihungstestModel, 'stufe', 'stufe');
		
		$this->setElementsArray(
			$this->ReihungstestModel->loadWhere('stufe IS NOT NULL'),
			true,
			'Select a stufe...',
			'No stufen found'
		);
		
		$this->loadDropDownView($widgetData);
    }
}