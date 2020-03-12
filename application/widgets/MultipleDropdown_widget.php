<?php

class MultipleDropdown_widget extends DropdownWidget
{
	public function display($widgetData)
	{
		$elements = $widgetData['elements'];

		$this->setElementsArray(
			$elements,
			false,
			'',
			'No data present'
		);

		$this->setMultiple();

		$this->loadDropDownView($widgetData);
	}
}
