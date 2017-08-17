<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Library to manage UDF
 */
class UDFLib
{
	const WIDGET_NAME = 'UDFWidget';
	const SCHEMA_ARG_NAME = 'schema';
	const TABLE_ARG_NAME = 'table';
	const FIELD_ARG_NAME = 'field';
	const UDFS_ARG_NAME = 'udfs';
	
	// UDF json schema attributes
	const NAME = 'name'; // UDF name attribute
	const TYPE = 'type'; // UDF type attribute
	const SORT = 'sort'; // UDF sort attribute
	const VALIDATION = 'validation'; // UDF validation attribute
	const LIST_VALUES = 'listValues'; // UDF listValues attribute
	const FE_REGEX_LANGUAGE = 'js'; // UDF javascript regex language attribute (front end)
	
	// HTML components
	const TITLE = 'description';
	const LABEL = 'title';
	const PLACEHOLDER = 'placeholder';
	
	// Validation attributes
	const REGEX = 'regex';
	const REQUIRED = 'required';
	const MAX_VALUE = 'max-value';
	const MIN_VALUE = 'min-value';
	const MAX_LENGTH = 'max-length';
	const MIN_LENGTH = 'min-length';
	
	const PHRASES_APP_NAME = 'core'; // Name of the app parameter used to retrive phrases
	
	private $_ci; // Code igniter instance
	
	public function __construct($config = array())
	{
		$this->_ci = & get_instance();
		
		$this->_ci->load->helper('fhc');
		
		// Loads the widget library
		$this->_ci->load->library('WidgetLib');
		
        // Loads widgets to render HTML for UDF
        loadResource(APPPATH . 'widgets/udf');
	}
	
	/**
     * 
     */
    public function UDFWidget($args, $htmlArgs = array())
    {
		if (!empty($args[UDFLib::SCHEMA_ARG_NAME]) && !empty($args[UDFLib::TABLE_ARG_NAME]))
		{
			// 
			if (empty($args[UDFLib::FIELD_ARG_NAME]) && !isset($htmlArgs[HTMLWidget::EXTERNAL_BLOCK]))
			{
				$htmlArgs[HTMLWidget::EXTERNAL_BLOCK] = true;
			}
			
			return $this->_ci->widgetlib->widget(
				UDFLib::WIDGET_NAME,
				$args,
				$htmlArgs
			);
		}
		else
		{
			if (empty($args[UDFLib::SCHEMA_ARG_NAME]))
			{
				show_error(UDFLib::SCHEMA_ARG_NAME.' parameter is missing!');
			}
			if (empty($args[UDFLib::TABLE_ARG_NAME]))
			{
				show_error(UDFLib::TABLE_ARG_NAME.' parameter is missing!');
			}
		}
    }
}