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
							break;
						}
						// If the name property is not present then show an error
						if (!isset($jsonSchema->{UDFLib::NAME}))
						{
							show_error(sprintf('%s.%s: Attribute "name" not present in the json schema', $schema, $table));
							break;
						}
						
						// If a UDF is specified and is present in the json schemas list or no UDF is specified
						if ((isset($field) && $field == $jsonSchema->{UDFLib::NAME}) || !isset($field))
						{
							$this->_setAttributesWithPhrases($jsonSchema); // Set attributes using phrases
							
							$this->_setValidationAttributes($jsonSchema); // Set validation attributes
							
							$this->_setNameAndId($jsonSchema); // Set name and id attributes
							
							$this->_render($jsonSchema); // Render the HTML for this UDF
							
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
    private function _setNameAndId($jsonSchema)
    {
		$this->htmlParameters[HTMLWidget::HTML_ID] = $jsonSchema->{UDFLib::NAME};
		$this->htmlParameters[HTMLWidget::HTML_NAME] = $jsonSchema->{UDFLib::NAME};
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
    private function _render($jsonSchema)
    {
		// Checkbox
		if ($jsonSchema->{UDFLib::TYPE} == 'checkbox')
		{
			$this->_renderCheckbox($jsonSchema);
		}
		// Textfield
		else if ($jsonSchema->{UDFLib::TYPE} == 'textfield')
		{
			$this->_renderTextfield($jsonSchema);
		}
		// Textarea
		else if ($jsonSchema->{UDFLib::TYPE} == 'textarea')
		{
			$this->_renderTextarea($jsonSchema);
		}
		// Date
		else if ($jsonSchema->{UDFLib::TYPE} == 'date')
		{
			
		}
		// Dropdown
		else if ($jsonSchema->{UDFLib::TYPE} == 'dropdown')
		{
			$this->_renderDropdown($jsonSchema);
		}
		// Multiple dropdown
		else if ($jsonSchema->{UDFLib::TYPE} == 'multipledropdown')
		{
			$this->_renderDropdown($jsonSchema, true);
		}
    }
    
    /**
     * Renders a dropdown element
     */
	private function _renderDropdown($jsonSchema, $multiple = false)
	{
		// Selected element/s
		if (isset($this->_args[UDFLib::UDFS_ARG_NAME])
			&& isset($this->_args[UDFLib::UDFS_ARG_NAME][$jsonSchema->{UDFLib::NAME}]))
		{
			$this->_args[DropdownWidget::SELECTED_ELEMENT] = $this->_args[UDFLib::UDFS_ARG_NAME][$jsonSchema->{UDFLib::NAME}];
		}
		else
		{
			$this->_args[DropdownWidget::SELECTED_ELEMENT] = null;
		}
		
		$dropdownWidgetUDF = new DropdownWidgetUDF($this->_name, $this->_args);
		$parameters = array();
		
		// If the list of values to show is an array
		if (isset($jsonSchema->{UDFLib::LIST_VALUES}->enum))
		{
			$parameters = $jsonSchema->{UDFLib::LIST_VALUES}->enum;
		}
		// If the list of values to show should be retrived with a SQL statement
		else if (isset($jsonSchema->{UDFLib::LIST_VALUES}->sql))
		{
			$queryResult = $this->UDFModel->execQuery($jsonSchema->{UDFLib::LIST_VALUES}->sql);
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
	private function _renderTextarea($jsonSchema)
	{
		$text = null; // text value
		$textareaUDF = new TextareaWidgetUDF($this->_name, $this->_args);
		
		// Set text value if present in the DB
		if (isset($this->_args[UDFLib::UDFS_ARG_NAME])
			&& isset($this->_args[UDFLib::UDFS_ARG_NAME][$jsonSchema->{UDFLib::NAME}]))
		{
			$text = $this->_args[UDFLib::UDFS_ARG_NAME][$jsonSchema->{UDFLib::NAME}];
		}
		
		$textareaUDF->render($text);
	}
	
	/**
     * Renders an input text element
     */
	private function _renderTextfield($jsonSchema)
	{
		$text = null; // text value
		$textareaUDF = new TextfieldWidgetUDF($this->_name, $this->_args);
		
		// Set text value if present in the DB
		if (isset($this->_args[UDFLib::UDFS_ARG_NAME])
			&& isset($this->_args[UDFLib::UDFS_ARG_NAME][$jsonSchema->{UDFLib::NAME}]))
		{
			$text = $this->_args[UDFLib::UDFS_ARG_NAME][$jsonSchema->{UDFLib::NAME}];
		}
		
		$textareaUDF->render($text);
	}
	
	/**
     * Renders a checkbox element
     */
	private function _renderCheckbox($jsonSchema)
	{
		// Set checkbox value if present in the DB
		if (isset($this->_args[UDFLib::UDFS_ARG_NAME])
			&& isset($this->_args[UDFLib::UDFS_ARG_NAME][$jsonSchema->{UDFLib::NAME}]))
		{
			$this->_args[CheckboxWidget::VALUE_FIELD] = $this->_args[UDFLib::UDFS_ARG_NAME][$jsonSchema->{UDFLib::NAME}];
		}
		else
		{
			$this->_args[CheckboxWidget::VALUE_FIELD] = CheckboxWidget::HTML_DEFAULT_VALUE;
		}
		
		$checkboxWidgetUDF = new CheckboxWidgetUDF($this->_name, $this->_args);
		
		$checkboxWidgetUDF->render();
	}
    
    /**
     * Sets the attributes of the HTML element using the phrases system
     */
    private function _setAttributesWithPhrases($jsonSchema)
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
					$this->htmlParameters[HTMLWidget::LABEL] = $tmpResult->retval[0]->text;
				}
				else
				{
					$this->htmlParameters[HTMLWidget::LABEL] = null;
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
					$this->htmlParameters[HTMLWidget::TITLE] = $tmpResult->retval[0]->text;
				}
				else
				{
					$this->htmlParameters[HTMLWidget::TITLE] = null;
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
					$this->htmlParameters[HTMLWidget::PLACEHOLDER] = $tmpResult->retval[0]->text;
				}
				else
				{
					$this->htmlParameters[HTMLWidget::PLACEHOLDER] = null;
				}
			}
		}
    }
    
    /**
     * Sets the validation attributes of the HTML element using the configuration inside the json schema
     */
    private function _setValidationAttributes($jsonSchema)
    {
		// Validation attributes set by default to null
		$this->htmlParameters[HTMLWidget::REGEX] = null;
		$this->htmlParameters[HTMLWidget::REQUIRED] = null;
		$this->htmlParameters[HTMLWidget::MIN_VALUE] = null;
		$this->htmlParameters[HTMLWidget::MAX_VALUE] = null;
		
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
						$this->htmlParameters[HTMLWidget::REGEX] = $regex->expression;
					}
				}
			}
			
			// Required
			if (isset($jsonSchemaValidation->{UDFLib::REQUIRED}))
			{
				$this->htmlParameters[HTMLWidget::REQUIRED] = $jsonSchemaValidation->{UDFLib::REQUIRED};
			}
			
			// Min value
			if (isset($jsonSchemaValidation->{UDFLib::MIN_VALUE}))
			{
				$this->htmlParameters[HTMLWidget::MIN_VALUE] = $jsonSchemaValidation->{UDFLib::MIN_VALUE};
			}
			
			// Max value
			if (isset($jsonSchemaValidation->{UDFLib::MAX_VALUE}))
			{
				$this->htmlParameters[HTMLWidget::MAX_VALUE] = $jsonSchemaValidation->{UDFLib::MAX_VALUE};
			}
			
			// Min length
			if (isset($jsonSchemaValidation->{UDFLib::MIN_LENGTH}))
			{
				$this->htmlParameters[HTMLWidget::MIN_LENGTH] = $jsonSchemaValidation->{UDFLib::MIN_LENGTH};
			}
			
			// Max length
			if (isset($jsonSchemaValidation->{UDFLib::MAX_LENGTH}))
			{
				$this->htmlParameters[HTMLWidget::MAX_LENGTH] = $jsonSchemaValidation->{UDFLib::MAX_LENGTH};
			}
		}
	}
}