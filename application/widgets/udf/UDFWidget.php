<?php

/**
 * Represents a generic UDF element
 * It's used to render the HTML of a UDF element using widgets
 */
class UDFWidget extends HTMLWidget
{
	/**
	 * Called by the WidgetLib, it renders the HTML of the UDF
	 */
    public function display($widgetData)
	{
		// _ci is the instance of Code Igniter and the library UDFLib was previously loaded,
		// so now is it possibile to call the method displayUDFWidget of UDFLib
		// to render the HTML of this UDF
		$this->_ci->udflib->displayUDFWidget($widgetData);
    }
}