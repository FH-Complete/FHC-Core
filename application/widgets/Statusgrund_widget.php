<?php

class Statusgrund_widget extends DropdownWidget
{
	public function display($widgetData)
	{
		// Zgv
		$this->load->model('crm/statusgrund_model', 'StatusgrundModel');
		$this->StatusgrundModel->addOrder('statusgrund_id');

		$this->addSelectToModel($this->StatusgrundModel, 'statusgrund_id', 'bezeichnung_mehrsprachig[1]');

		$this->setElementsArray(
			$this->StatusgrundModel->load(),
			true,
			'Select a Statusgrund...',
			'No Statusgrund found'
		);

		$this->loadDropDownView($widgetData);
	}
}