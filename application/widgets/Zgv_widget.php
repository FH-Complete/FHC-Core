<?php

class Zgv_widget extends DropdownWidget
{
	public function display($widgetData)
	{
		// Zgv
		$this->load->model('codex/Zgv_model', 'ZgvModel');
		$this->ZgvModel->addOrder('zgv_bez');

		$this->addSelectToModel($this->ZgvModel, 'zgv_code', 'zgv_bez');

		$this->setElementsArray(
			$this->ZgvModel->load(),
			true,
			$this->p->t('ui', 'bitteEintragWaehlen')
		);

		$this->loadDropDownView($widgetData);
	}
}
