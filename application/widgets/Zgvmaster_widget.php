<?php

class Zgvmaster_widget extends DropdownWidget
{
	public function display($widgetData)
	{
		// Zgv master
		$this->load->model('codex/zgvmaster_model', 'ZgvmasterModel');
		$this->ZgvmasterModel->addOrder('zgvmas_bez');

		$this->addSelectToModel($this->ZgvmasterModel, 'zgvmas_code', 'zgvmas_bez');

		$this->setElementsArray(
			$this->ZgvmasterModel->load(),
			true,
			'Zgv wählen...',
			'keine Zgv gefunden'
		);

		$this->loadDropDownView($widgetData);
	}
}