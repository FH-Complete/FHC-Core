<?php

class Dropdown_widget extends DropdownWidget
{
	public function display($widgetData)
	{
		$elements = $widgetData['elements'];
		$emptyElement = $widgetData['emptyElement'];

		$this->setElementsArray(
			$elements,
			true,
			$emptyElement,
			'No data present'
		);

		$this->loadDropDownView($widgetData);
	}
}
