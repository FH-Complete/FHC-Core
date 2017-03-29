<?php

class Organisationseinheit_widget extends DropdownWidget
{
    public function display($widgetData)
	{
		$this->load->model('organisation/Organisationseinheit_model', 'OrganisationseinheitModel');
		
		if (isset($widgetData['typ']))
			$typ = $widgetData['typ'];
		else
			$typ = null;
		
		// NOTE: no need to call addSelectToModel because getRecursiveList already returns
		// the correct names of the fields
		
		$this->setElementsArray($this->OrganisationseinheitModel->getRecursiveList($typ));
		
		$this->loadDropDownView($widgetData);
    }
}