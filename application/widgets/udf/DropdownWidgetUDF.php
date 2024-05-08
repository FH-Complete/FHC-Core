<?php

/**
 * It exends the DropdownWidget class to represent an HTML dropdown
 */
class DropdownWidgetUDF extends DropdownWidget
{
	/**
	 * NOTE: echo $this->content() is needed
	 */
	public function render($parameters)
	{
		// Array that will contains the elements to be displayed in the dropdown
		$tmpNewElements = array();

		// Loops through the given parameters
		foreach($parameters as $parameter)
		{
			// Every single element of the array is checked, and it could only be:
			// - An array with two elements OR
			// - A string or a number OR
			// - An object with two properties: id and description
			if ((is_array($parameter) && count($parameter) == 2)
				|| (is_string($parameter) || is_numeric($parameter))
				|| (is_object($parameter) && isset($parameter->{DropdownWidget::ID_FIELD})
					&& isset($parameter->{DropdownWidget::DESCRIPTION_FIELD})))
			{
				$newElement = new stdClass(); // New single element
				// If the single element is an array of two element
				if (is_array($parameter) && count($parameter) == 2)
				{
					$newElement->{DropdownWidget::ID_FIELD} = $parameter[0]; //
					$newElement->{DropdownWidget::DESCRIPTION_FIELD} = $parameter[1]; //
				}
				// If the single element is a string or a number
				else if (is_string($parameter) || is_numeric($parameter))
				{
					$newElement->{DropdownWidget::ID_FIELD} = $parameter; //
					$newElement->{DropdownWidget::DESCRIPTION_FIELD} = $parameter; //
				}
				// If the single element is an object with two properties: id and description
				else if (is_object($parameter) && isset($parameter->{DropdownWidget::ID_FIELD})
					&& isset($parameter->{DropdownWidget::DESCRIPTION_FIELD}))
				{
					$newElement->{DropdownWidget::ID_FIELD} = $parameter->{DropdownWidget::ID_FIELD}; //
					$newElement->{DropdownWidget::DESCRIPTION_FIELD} = $parameter->{DropdownWidget::DESCRIPTION_FIELD}; //
				}

				array_push($tmpNewElements, $newElement); // Add $newElement into $tmpNewElements
			}
		}

		// Set the list of elements
		$this->setElementsArray(
			success($tmpNewElements),
			true,
			$this->htmlParameters[HTMLWidget::PLACEHOLDER],
			'No data found for this UDF'
		);

		$this->loadDropDownView();

		echo $this->content();
    }
}
