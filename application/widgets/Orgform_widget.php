<?php

class Orgform_widget extends DropdownWidget
{
    public function display($widgetData)
	{
		$this->load->model('codex/Orgform_model', 'OrgFormModel');
		$this->OrgFormModel->addOrder('bezeichnung');
		
		$this->addSelectToModel($this->OrgFormModel, 'orgform_kurzbz', 'bezeichnung');
		
		$this->setElementsArray($this->OrgFormModel->load());
		
		$this->loadDropDownView($widgetData);
    }
}