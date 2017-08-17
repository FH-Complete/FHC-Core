<?php

/**
 * It exends the Widget class to represent an HTML dropdown
 */
class CheckboxWidget extends HTMLWidget
{
	// Name of the property that will be used to store the value attribute of the option tag
	const VALUE_FIELD = 'value';
	// Name of the property that will be used to store the value between the option tags
	const DESCRIPTION_FIELD = 'description';
	// Value of value attribute of the checkbox
	const CHECKBOX_VALUE = 'true';
	// Default checkbox value
	const HTML_DEFAULT_VALUE = false;
	
	/**
	 * Loads the view that renders a checkbox
	 */
	protected function loadCheckboxView()
	{
		$this->view('widgets/checkbox', $this->_args);
	}
}