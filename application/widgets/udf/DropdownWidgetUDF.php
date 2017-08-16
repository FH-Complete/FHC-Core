<?php

/**
 * 
 */
class DropdownWidgetUDF extends DropdownWidget
{
	/**
	 * 
	 */
	public function render($parameters)
	{
		$tmpElements = array();
		
		// 
		foreach($parameters as $parameter)
		{
			// 
			if ((is_array($parameter) && count($parameter) == 2)
				|| (is_string($parameter) || is_numeric($parameter))
				|| (is_object($parameter) && isset($parameter->{PARENT::ID_FIELD}) && isset($parameter->{PARENT::DESCRIPTION_FIELD})))
			{
				$element = new stdClass(); // 
				// 
				if (is_array($parameter) && count($parameter) == 2)
				{
					$element->{PARENT::ID_FIELD} = $parameter[0]; // 
					$element->{PARENT::DESCRIPTION_FIELD} = $parameter[1]; // 
				}
				// 
				else if (is_string($parameter) || is_numeric($parameter))
				{
					$element->{PARENT::ID_FIELD} = $parameter; // 
					$element->{PARENT::DESCRIPTION_FIELD} = $parameter; // 
				}
				// 
				else if (is_object($parameter) && isset($parameter->{PARENT::ID_FIELD}) && isset($parameter->{PARENT::DESCRIPTION_FIELD}))
				{
					$element->{PARENT::ID_FIELD} = $parameter->{PARENT::ID_FIELD}; // 
					$element->{PARENT::DESCRIPTION_FIELD} = $parameter->{PARENT::DESCRIPTION_FIELD}; // 
				}
				
				array_push($tmpElements, $element); // 
			}
		}
		
		$this->setElementsArray(
			success($tmpElements),
			true,
			$this->_args[HTMLWidget::HTML_ARG_NAME][HTMLWidget::PLACEHOLDER],
			'No data found for this UDF'
		);
		
		$this->loadDropDownView();
		
		echo $this->content();
    }
}