<?php

/**
 * 
 */
class UDFWidget extends HTMLWidget
{
	const NAME = 'name';
	const TYPE = 'type';
	const VALIDATION = 'validation';
	const LIST_VALUES = 'listValues';
	const REGEX_LANGUAGE = 'js';
	
	const PHRASES_APP_NAME = 'core';
	
    public function display($widgetData)
	{
		$schema = $widgetData[UDFLib::SCHEMA_ARG_NAME];
		$table = $widgetData[UDFLib::TABLE_ARG_NAME];
		
		if (isset($widgetData[UDFLib::FIELD_ARG_NAME]))
		{
			$field = $widgetData[UDFLib::FIELD_ARG_NAME];
		}
		
		$udfResults = $this->_loadUDF($schema, $table);
		if (hasData($udfResults))
		{
			$udf = $udfResults->retval[0];
			if (isset($udf->jsons))
			{
				$jsonSchemas = json_decode($udf->jsons);
				if (is_object($jsonSchemas) || is_array($jsonSchemas))
				{
					// 
					if (is_object($jsonSchemas))
					{
						$jsonSchemasArray = array($jsonSchemas);
					}
					else
					{
						$jsonSchemasArray = $jsonSchemas;
					}
					
					$found = false; // 
					
					$this->_sortJsonSchemas($jsonSchemasArray); // 
					
					//
					foreach($jsonSchemasArray as $jsonSchema)
					{
						// 
						if (!isset($jsonSchema->{UDFWidget::TYPE}))
						{
							show_error(sprintf('%s.%s: Attribute "type" not present in the json schema', $schema, $table));
							break;
						}
						if (!isset($jsonSchema->{UDFWidget::NAME}))
						{
							show_error(sprintf('%s.%s: Attribute "name" not present in the json schema', $schema, $table));
							break;
						}
						
						// 
						if ((isset($field) && $field == $jsonSchema->{UDFWidget::NAME}) || !isset($field))
						{
							$this->_setAttributesWithPhrases($jsonSchema);
							
							$this->_setValidationAttributes($jsonSchema);
							
							$this->_setNameAndId($jsonSchema);
							
							$this->_render($jsonSchema);
							
							// 
							if (isset($field) && $field == $jsonSchema->{UDFWidget::NAME})
							{
								$found = true;
								break;
							}
						}
					}
					
					// 
					if (isset($field) && !$found)
					{
						show_error(sprintf('%s.%s: No schema present for field: %s', $schema, $table, $field));
					}
				}
				else
				{
					show_error(sprintf('%s.%s: Not a valid json schema', $schema, $table));
				}
			}
			else
			{
				show_error(sprintf('%s.%s: Does not contain "jsons" field', $schema, $table));
			}
		}
    }
    
    /**
     * 
     */
    private function _setNameAndId($jsonSchema)
    {
		$this->_args[HTMLWidget::HTML_ARG_NAME][HTMLWidget::HTML_ID] = $jsonSchema->{UDFWidget::NAME};
		$this->_args[HTMLWidget::HTML_ARG_NAME][HTMLWidget::HTML_NAME] = $jsonSchema->{UDFWidget::NAME};
    }
    
    /**
     * 
     */
    private function _sortJsonSchemas(&$jsonSchemasArray)
    {
		// 
		usort($jsonSchemasArray, function ($a, $b) {
			// 
			if (!isset($a->sort))
			{
				$a->sort = 9999;
			}
			if (!isset($b->sort))
			{
				$b->sort = 9999;
			}
			
			if ($a->sort == $b->sort)
			{
				return 0;
			}
			
			return ($a->sort < $b->sort) ? -1 : 1;
		});
    }
    
    /**
     * 
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
     * 
     */
    private function _render($jsonSchema)
    {
		// Type
		if ($jsonSchema->{UDFWidget::TYPE} == 'checkbox')
		{
			$this->_renderCheckbox($jsonSchema);
		}
		else if ($jsonSchema->{UDFWidget::TYPE} == 'textfield')
		{
			$this->_renderTextfield($jsonSchema);
		}
		else if ($jsonSchema->{UDFWidget::TYPE} == 'textarea')
		{
			$this->_renderTextarea($jsonSchema);
		}
		else if ($jsonSchema->{UDFWidget::TYPE} == 'date')
		{
			
		}
		else if ($jsonSchema->{UDFWidget::TYPE} == 'dropdown')
		{
			$this->_renderDropdown($jsonSchema);
		}
		else if ($jsonSchema->{UDFWidget::TYPE} == 'multipledropdown')
		{
			$this->_renderDropdown($jsonSchema, true);
		}
    }
    
    /**
     * 
     */
	private function _renderDropdown($jsonSchema, $multiple = false)
	{
		if (isset($this->_args[UDFLib::UDFS_ARG_NAME])
			&& isset($this->_args[UDFLib::UDFS_ARG_NAME][$jsonSchema->{UDFWidget::NAME}]))
		{
			$this->_args[DropdownWidget::SELECTED_ELEMENT] = $this->_args[UDFLib::UDFS_ARG_NAME][$jsonSchema->{UDFWidget::NAME}];
		}
		else
		{
			$this->_args[DropdownWidget::SELECTED_ELEMENT] = null;
		}
		
		$dropdownWidgetUDF = new DropdownWidgetUDF($this->_name, $this->_args);
		$parameters = array();
		
		// 
		if (isset($jsonSchema->{UDFWidget::LIST_VALUES}->enum))
		{
			$parameters = $jsonSchema->{UDFWidget::LIST_VALUES}->enum;
		}
		// 
		else if (isset($jsonSchema->{UDFWidget::LIST_VALUES}->sql))
		{
			$queryResult = $this->UDFModel->execQuery($jsonSchema->{UDFWidget::LIST_VALUES}->sql);
			if (hasData($queryResult))
			{	
				$parameters = $queryResult->retval;
			}
		}
		
		if ($multiple)
		{
			$dropdownWidgetUDF->setMultiple();
		}
		
		$dropdownWidgetUDF->render($parameters);
	}
	
	/**
     * 
     */
	private function _renderTextarea($jsonSchema)
	{
		$textareaUDF = new TextareaWidgetUDF($this->_name, $this->_args);
		$text = null;
		
		if (isset($this->_args[UDFLib::UDFS_ARG_NAME])
			&& isset($this->_args[UDFLib::UDFS_ARG_NAME][$jsonSchema->{UDFWidget::NAME}]))
		{
			$text = $this->_args[UDFLib::UDFS_ARG_NAME][$jsonSchema->{UDFWidget::NAME}];
		}
		
		$textareaUDF->render($text);
	}
	
	/**
     * 
     */
	private function _renderTextfield($jsonSchema)
	{
		$textareaUDF = new TextfieldWidgetUDF($this->_name, $this->_args);
		$text = null;
		
		if (isset($this->_args[UDFLib::UDFS_ARG_NAME])
			&& isset($this->_args[UDFLib::UDFS_ARG_NAME][$jsonSchema->{UDFWidget::NAME}]))
		{
			$text = $this->_args[UDFLib::UDFS_ARG_NAME][$jsonSchema->{UDFWidget::NAME}];
		}
		
		$textareaUDF->render($text);
	}
	
	/**
     * 
     */
	private function _renderCheckbox($jsonSchema)
	{
		if (isset($this->_args[UDFLib::UDFS_ARG_NAME])
			&& isset($this->_args[UDFLib::UDFS_ARG_NAME][$jsonSchema->{UDFWidget::NAME}]))
		{
			$this->_args[CheckboxWidget::VALUE_FIELD] = $this->_args[UDFLib::UDFS_ARG_NAME][$jsonSchema->{UDFWidget::NAME}];
		}
		else
		{
			$this->_args[CheckboxWidget::VALUE_FIELD] = HTMLWidget::HTML_DEFAULT_VALUE;
		}
		
		$checkboxWidgetUDF = new CheckboxWidgetUDF($this->_name, $this->_args);
		
		$checkboxWidgetUDF->render();
	}
    
    /**
     * 
     */
    private function _setAttributesWithPhrases($jsonSchema)
    {
		// Description, title and placeholder
		if (isset($jsonSchema->{UDFLib::LABEL})
			|| isset($jsonSchema->{UDFLib::TITLE})
			|| isset($jsonSchema->{UDFLib::PLACEHOLDER}))
		{
			// 
			$this->_ci->load->library('PhrasesLib');
			
			// 
			if (isset($jsonSchema->{UDFLib::LABEL}))
			{
				$tmpResult = $this->_ci->phraseslib->getPhrases(
					UDFWidget::PHRASES_APP_NAME,
					DEFAULT_LEHREINHEIT_SPRACHE,
					$jsonSchema->{UDFLib::LABEL},
					null,
					null,
					'no'
				);
				if (hasData($tmpResult))
				{
					$this->_args[HTMLWidget::HTML_ARG_NAME][UDFLib::LABEL] = $tmpResult->retval[0]->text;
				}
				else
				{
					$this->_args[HTMLWidget::HTML_ARG_NAME][UDFLib::LABEL] = null;
				}
			}
			
			// 
			if (isset($jsonSchema->{UDFLib::TITLE}))
			{
				$tmpResult = $this->_ci->phraseslib->getPhrases(
					UDFWidget::PHRASES_APP_NAME,
					DEFAULT_LEHREINHEIT_SPRACHE,
					$jsonSchema->{UDFLib::TITLE},
					null,
					null,
					'no'
				);
				if (hasData($tmpResult))
				{
					$this->_args[HTMLWidget::HTML_ARG_NAME][UDFLib::TITLE] = $tmpResult->retval[0]->text;
				}
				else
				{
					$this->_args[HTMLWidget::HTML_ARG_NAME][UDFLib::TITLE] = null;
				}
			}
			
			// 
			if (isset($jsonSchema->{UDFLib::PLACEHOLDER}))
			{
				$tmpResult = $this->_ci->phraseslib->getPhrases(
					UDFWidget::PHRASES_APP_NAME,
					DEFAULT_LEHREINHEIT_SPRACHE,
					$jsonSchema->{UDFLib::PLACEHOLDER},
					null,
					null,
					'no'
				);
				if (hasData($tmpResult))
				{
					$this->_args[HTMLWidget::HTML_ARG_NAME][UDFLib::PLACEHOLDER] = $tmpResult->retval[0]->text;
				}
				else
				{
					$this->_args[HTMLWidget::HTML_ARG_NAME][UDFLib::PLACEHOLDER] = null;
				}
			}
		}
    }
    
    /**
     * 
     */
    private function _setValidationAttributes($jsonSchema)
    {
		// Validation
		$this->_args[HTMLWidget::HTML_ARG_NAME][UDFWidget::REQUIRED] = null;
		$this->_args[HTMLWidget::HTML_ARG_NAME][UDFLib::REGEX] = null;
		$this->_args[HTMLWidget::HTML_ARG_NAME][UDFLib::MAX_VALUE] = null;
		$this->_args[HTMLWidget::HTML_ARG_NAME][UDFLib::MIN_VALUE] = null;
		
		if (isset($jsonSchema->validation))
		{
			if (isset($jsonSchema->{UDFWidget::VALIDATION}->{UDFLib::REGEX})
				&& is_array($jsonSchema->{UDFWidget::VALIDATION}->{UDFLib::REGEX}))
			{
				foreach($jsonSchema->{UDFWidget::VALIDATION}->{UDFLib::REGEX} as $regex)
				{
					if ($regex->language === UDFWidget::REGEX_LANGUAGE)
					{
						$this->_args[HTMLWidget::HTML_ARG_NAME][UDFLib::REGEX] = $regex->expression;
					}
				}
			}
			
			if (isset($jsonSchema->{UDFWidget::VALIDATION}->{UDFLib::REQUIRED}))
			{
				$this->_args[HTMLWidget::HTML_ARG_NAME][UDFWidget::REQUIRED] = $jsonSchema->{UDFWidget::VALIDATION}->{UDFLib::REQUIRED};
			}
			
			if (isset($jsonSchema->{UDFWidget::VALIDATION}->{UDFLib::MAX_VALUE}))
			{
				$this->_args[HTMLWidget::HTML_ARG_NAME][UDFLib::MAX_VALUE] = $jsonSchema->{UDFWidget::VALIDATION}->{UDFLib::MAX_VALUE};
			}
			
			if (isset($jsonSchema->{UDFWidget::VALIDATION}->{UDFLib::MIN_VALUE}))
			{
				$this->_args[HTMLWidget::HTML_ARG_NAME][UDFLib::MIN_VALUE] = $jsonSchema->{UDFWidget::VALIDATION}->{UDFLib::MIN_VALUE};
			}
		}
	}
}