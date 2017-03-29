<?php

class Sprache_widget extends DropdownWidget
{
    public function display($widgetData)
	{
		$this->load->model('system/Sprache_model', 'SpracheModel');
		$this->SpracheModel->addOrder('sprache');
		
		$this->addSelectToModel($this->SpracheModel, 'sprache', 'sprache');
		
		$this->setElementsArray($this->SpracheModel->load());
		
		$this->loadDropDownView($widgetData);
    }
}