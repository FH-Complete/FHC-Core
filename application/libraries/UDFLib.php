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
        loadResource(APPPATH.'widgets/udf');
	}
	
	/**
     * 
     */
    public function UDFWidget($args, $htmlArgs = array())
    {
		if (!empty($args[UDFLib::SCHEMA_ARG_NAME]) && !empty($args[UDFLib::TABLE_ARG_NAME]))
		{
			// Default external block is true
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
    
    /**
	 * It renders the HTML of the UDF
	 * NOTE: When this method is called $widgetData contains different data from
	 * parameter $args in the constructor
	 */
    public function displayUDFWidget(&$widgetData)
	{
		$schema = $widgetData[UDFLib::SCHEMA_ARG_NAME]; // schema attribute
		$table = $widgetData[UDFLib::TABLE_ARG_NAME]; // table attribute
		
		if (isset($widgetData[UDFLib::FIELD_ARG_NAME]))
		{
			$field = $widgetData[UDFLib::FIELD_ARG_NAME]; // UDF name
		}
		
		$udfResults = $this->_loadUDF($schema, $table); // loads UDF definition
		if (hasData($udfResults))
		{
			$udf = $udfResults->retval[0]; // only one record is loaded
			if (isset($udf->jsons))
			{
				$jsonSchemas = json_decode($udf->jsons); // decode the json schema
				if (is_object($jsonSchemas) || is_array($jsonSchemas))
				{
					// If the schema is an object then convert it into an array
					if (is_object($jsonSchemas))
					{
						$jsonSchemasArray = array($jsonSchemas);
					}
					else // keep it as it is
					{
						$jsonSchemasArray = $jsonSchemas;
					}
					
					$found = false; // used to check if the field is found or not in the json schema
					
					$this->_sortJsonSchemas($jsonSchemasArray); // Sort the list of UDF by sort property
					
					// Loops through json schemas
					foreach($jsonSchemasArray as $jsonSchema)
					{
						// If the type property is not present then show an error
						if (!isset($jsonSchema->{UDFLib::TYPE}))
						{
							show_error(sprintf('%s.%s: Attribute "type" not present in the json schema', $schema, $table));
						}
						// If the name property is not present then show an error
						if (!isset($jsonSchema->{UDFLib::NAME}))
						{
							show_error(sprintf('%s.%s: Attribute "name" not present in the json schema', $schema, $table));
						}
						
						// If a UDF is specified and is present in the json schemas list or no UDF is specified
						if ((isset($field) && $field == $jsonSchema->{UDFLib::NAME}) || !isset($field))
						{
							// Set attributes using phrases
							$this->_setAttributesWithPhrases($jsonSchema, $widgetData[HTMLWidget::HTML_ARG_NAME]);
							
							// Set validation attributes
							$this->_setValidationAttributes($jsonSchema, $widgetData[HTMLWidget::HTML_ARG_NAME]);
							
							// Set name and id attributes
							$this->_setNameAndId($jsonSchema, $widgetData[HTMLWidget::HTML_ARG_NAME]);
							
							// Render the HTML for this UDF
							$this->_render($jsonSchema, $widgetData);
							
							// If a UDf is specified and it was found then stop looking through this list
							if (isset($field) && $field == $jsonSchema->{UDFLib::NAME})
							{
								$found = true;
								break;
							}
						}
					}
					
					// If a UDf is specified and it was not found then show an error
					if (isset($field) && !$found)
					{
						show_error(sprintf('%s.%s: No schema present for field: %s', $schema, $table, $field));
					}
				}
				else // not a valid schema
				{
					show_error(sprintf('%s.%s: Not a valid json schema', $schema, $table));
				}
			}
			else // no json column present in table tbl_udf
			{
				show_error(sprintf('%s.%s: Does not contain "jsons" field', $schema, $table));
			}
		}
    }
    
    /**
     * Set the name and id attribute of the HTML element
     */
    private function _setNameAndId($jsonSchema, &$htmlParameters)
    {
		$htmlParameters[HTMLWidget::HTML_ID] = $jsonSchema->{UDFLib::NAME};
		$htmlParameters[HTMLWidget::HTML_NAME] = $jsonSchema->{UDFLib::NAME};
    }
    
    /**
     * Sort the list of UDF by sort property
     */
    private function _sortJsonSchemas(&$jsonSchemasArray)
    {
		// 
		usort($jsonSchemasArray, function ($a, $b) {
			// 
			if (!isset($a->{UDFLib::SORT}))
			{
				$a->{UDFLib::SORT} = 9999;
			}
			if (!isset($b->{UDFLib::SORT}))
			{
				$b->{UDFLib::SORT} = 9999;
			}
			
			if ($a->{UDFLib::SORT} == $b->{UDFLib::SORT})
			{
				return 0;
			}
			
			return ($a->{UDFLib::SORT} < $b->{UDFLib::SORT}) ? -1 : 1;
		});
    }
    
    /**
     * Loads the UDF description by the given schema and table
     */
    private function _loadUDF($schema, $table)
    {
		// Loads UDF model
		$this->_ci->load->model('system/UDF_model', 'UDFModel');
		
		$udfResults = $this->_ci->UDFModel->loadWhere(
			array(
				'schema' => $schema,
				'table' => $table
			)
		);
		
		if (isError($udfResults))
		{
			if (is_object($udfResults) && isset($udfResults->retval))
			{
				show_error($udfResults->retval);
			}
			else if (is_string($udfResults))
			{
				show_error($udfResults);
			}
			else
			{
				show_error('UDFWidget: generic error occurred');
			}
		}
		else if (!hasData($udfResults))
		{
			show_error(sprintf('%s.%s does not contain UDF', $schema, $table));
		}
		
		return $udfResults;
    }
    
    /**
     * Render the HTML for the UDF
     */
    private function _render($jsonSchema, &$widgetData)
    {
		// Checkbox
		if ($jsonSchema->{UDFLib::TYPE} == 'checkbox')
		{
			$this->_renderCheckbox($jsonSchema, $widgetData);
		}
		// Textfield
		else if ($jsonSchema->{UDFLib::TYPE} == 'textfield')
		{
			$this->_renderTextfield($jsonSchema, $widgetData);
		}
		// Textarea
		else if ($jsonSchema->{UDFLib::TYPE} == 'textarea')
		{
			$this->_renderTextarea($jsonSchema, $widgetData);
		}
		// Date
		else if ($jsonSchema->{UDFLib::TYPE} == 'date')
		{
			
		}
		// Dropdown
		else if ($jsonSchema->{UDFLib::TYPE} == 'dropdown')
		{
			$this->_renderDropdown($jsonSchema, $widgetData);
		}
		// Multiple dropdown
		else if ($jsonSchema->{UDFLib::TYPE} == 'multipledropdown')
		{
			$this->_renderDropdown($jsonSchema, $widgetData, true);
		}
    }
    
    /**
     * Renders a dropdown element
     */
	private function _renderDropdown($jsonSchema, &$widgetData, $multiple = false)
	{
		// Selected element/s
		if (isset($widgetData[UDFLib::UDFS_ARG_NAME])
			&& isset($widgetData[UDFLib::UDFS_ARG_NAME][$jsonSchema->{UDFLib::NAME}]))
		{
			$widgetData[DropdownWidget::SELECTED_ELEMENT] = $widgetData[UDFLib::UDFS_ARG_NAME][$jsonSchema->{UDFLib::NAME}];
		}
		else
		{
			$widgetData[DropdownWidget::SELECTED_ELEMENT] = null;
		}
		
		$dropdownWidgetUDF = new DropdownWidgetUDF(UDFLib::WIDGET_NAME, $widgetData);
		$parameters = array();
		
		// If the list of values to show is an array
		if (isset($jsonSchema->{UDFLib::LIST_VALUES}->enum))
		{
			$parameters = $jsonSchema->{UDFLib::LIST_VALUES}->enum;
		}
		// If the list of values to show should be retrived with a SQL statement
		else if (isset($jsonSchema->{UDFLib::LIST_VALUES}->sql))
		{
			// UDFModel is loaded in method _loadUDF that is called before the current method
			$queryResult = $this->_ci->UDFModel->execQuery($jsonSchema->{UDFLib::LIST_VALUES}->sql);
			if (hasData($queryResult))
			{	
				$parameters = $queryResult->retval;
			}
		}
		
		if ($multiple) // multiple dropdown
		{
			$dropdownWidgetUDF->setMultiple();
		}
		
		$dropdownWidgetUDF->render($parameters);
	}
	
	/**
     * Renders a textarea element
     */
	private function _renderTextarea($jsonSchema, &$widgetData)
	{
		$text = null; // text value
		$textareaUDF = new TextareaWidgetUDF(UDFLib::WIDGET_NAME, $widgetData);
		
		// Set text value if present in the DB
		if (isset($widgetData[UDFLib::UDFS_ARG_NAME])
			&& isset($widgetData[UDFLib::UDFS_ARG_NAME][$jsonSchema->{UDFLib::NAME}]))
		{
			$text = $widgetData[UDFLib::UDFS_ARG_NAME][$jsonSchema->{UDFLib::NAME}];
		}
		
		$textareaUDF->render($text);
	}
	
	/**
     * Renders an input text element
     */
	private function _renderTextfield($jsonSchema, &$widgetData)
	{
		$text = null; // text value
		$textareaUDF = new TextfieldWidgetUDF(UDFLib::WIDGET_NAME, $widgetData);
		
		// Set text value if present in the DB
		if (isset($widgetData[UDFLib::UDFS_ARG_NAME])
			&& isset($widgetData[UDFLib::UDFS_ARG_NAME][$jsonSchema->{UDFLib::NAME}]))
		{
			$text = $widgetData[UDFLib::UDFS_ARG_NAME][$jsonSchema->{UDFLib::NAME}];
		}
		
		$textareaUDF->render($text);
	}
	
	/**
     * Renders a checkbox element
     */
	private function _renderCheckbox($jsonSchema, &$widgetData)
	{
		// Set checkbox value if present in the DB
		if (isset($widgetData[UDFLib::UDFS_ARG_NAME])
			&& isset($widgetData[UDFLib::UDFS_ARG_NAME][$jsonSchema->{UDFLib::NAME}]))
		{
			$widgetData[CheckboxWidget::VALUE_FIELD] = $widgetData[UDFLib::UDFS_ARG_NAME][$jsonSchema->{UDFLib::NAME}];
		}
		else
		{
			$widgetData[CheckboxWidget::VALUE_FIELD] = CheckboxWidget::HTML_DEFAULT_VALUE;
		}
		
		$checkboxWidgetUDF = new CheckboxWidgetUDF(UDFLib::WIDGET_NAME, $widgetData);
		
		$checkboxWidgetUDF->render();
	}
    
    /**
     * Sets the attributes of the HTML element using the phrases system
     */
    private function _setAttributesWithPhrases($jsonSchema, &$htmlParameters)
    {
		// Description, title and placeholder
		if (isset($jsonSchema->{UDFLib::LABEL})
			|| isset($jsonSchema->{UDFLib::TITLE})
			|| isset($jsonSchema->{UDFLib::PLACEHOLDER}))
		{
			// Loads PhrasesLib
			$this->_ci->load->library('PhrasesLib');
			
			// If is set the label property in the json schema
			if (isset($jsonSchema->{UDFLib::LABEL}))
			{
				// Load the related phrase
				$tmpResult = $this->_ci->phraseslib->getPhrases(
					UDFLib::PHRASES_APP_NAME,
					DEFAULT_LEHREINHEIT_SPRACHE,
					$jsonSchema->{UDFLib::LABEL},
					null,
					null,
					'no'
				);
				if (hasData($tmpResult))
				{
					$htmlParameters[HTMLWidget::LABEL] = $tmpResult->retval[0]->text;
				}
				else
				{
					$htmlParameters[HTMLWidget::LABEL] = null;
				}
			}
			
			// If is set the title property in the json schema
			if (isset($jsonSchema->{UDFLib::TITLE}))
			{
				// Load the related phrase
				$tmpResult = $this->_ci->phraseslib->getPhrases(
					UDFLib::PHRASES_APP_NAME,
					DEFAULT_LEHREINHEIT_SPRACHE,
					$jsonSchema->{UDFLib::TITLE},
					null,
					null,
					'no'
				);
				if (hasData($tmpResult))
				{
					$htmlParameters[HTMLWidget::TITLE] = $tmpResult->retval[0]->text;
				}
				else
				{
					$htmlParameters[HTMLWidget::TITLE] = null;
				}
			}
			
			// If is set the placeholder property in the json schema
			if (isset($jsonSchema->{UDFLib::PLACEHOLDER}))
			{
				// Load the related phrase
				$tmpResult = $this->_ci->phraseslib->getPhrases(
					UDFLib::PHRASES_APP_NAME,
					DEFAULT_LEHREINHEIT_SPRACHE,
					$jsonSchema->{UDFLib::PLACEHOLDER},
					null,
					null,
					'no'
				);
				if (hasData($tmpResult))
				{
					$htmlParameters[HTMLWidget::PLACEHOLDER] = $tmpResult->retval[0]->text;
				}
				else
				{
					$htmlParameters[HTMLWidget::PLACEHOLDER] = null;
				}
			}
		}
    }
    
    /**
     * Sets the validation attributes of the HTML element using the configuration inside the json schema
     */
    private function _setValidationAttributes($jsonSchema, &$htmlParameters)
    {
		// Validation attributes set by default to null
		$htmlParameters[HTMLWidget::REGEX] = null;
		$htmlParameters[HTMLWidget::REQUIRED] = null;
		$htmlParameters[HTMLWidget::MIN_VALUE] = null;
		$htmlParameters[HTMLWidget::MAX_VALUE] = null;
		
		// If validation property is present in the json schema
		if (isset($jsonSchema->{UDFLib::VALIDATION}))
		{
			$jsonSchemaValidation =& $jsonSchema->{UDFLib::VALIDATION}; // Reference for a better code readability
			
			// Front end regex
			if (isset($jsonSchemaValidation->{UDFLib::REGEX})
				&& is_array($jsonSchemaValidation->{UDFLib::REGEX}))
			{
				foreach($jsonSchemaValidation->{UDFLib::REGEX} as $regex)
				{
					if ($regex->language === UDFLib::FE_REGEX_LANGUAGE)
					{
						$htmlParameters[HTMLWidget::REGEX] = $regex->expression;
					}
				}
			}
			
			// Required
			if (isset($jsonSchemaValidation->{UDFLib::REQUIRED}))
			{
				$htmlParameters[HTMLWidget::REQUIRED] = $jsonSchemaValidation->{UDFLib::REQUIRED};
			}
			
			// Min value
			if (isset($jsonSchemaValidation->{UDFLib::MIN_VALUE}))
			{
				$htmlParameters[HTMLWidget::MIN_VALUE] = $jsonSchemaValidation->{UDFLib::MIN_VALUE};
			}
			
			// Max value
			if (isset($jsonSchemaValidation->{UDFLib::MAX_VALUE}))
			{
				$htmlParameters[HTMLWidget::MAX_VALUE] = $jsonSchemaValidation->{UDFLib::MAX_VALUE};
			}
			
			// Min length
			if (isset($jsonSchemaValidation->{UDFLib::MIN_LENGTH}))
			{
				$htmlParameters[HTMLWidget::MIN_LENGTH] = $jsonSchemaValidation->{UDFLib::MIN_LENGTH};
			}
			
			// Max length
			if (isset($jsonSchemaValidation->{UDFLib::MAX_LENGTH}))
			{
				$htmlParameters[HTMLWidget::MAX_LENGTH] = $jsonSchemaValidation->{UDFLib::MAX_LENGTH};
			}
		}
	}
}