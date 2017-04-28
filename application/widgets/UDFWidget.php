<?php

/**
 * 
 */
class UDFWidget extends UDFWidgetTpl
{
    public function display($widgetData)
	{
		$schema = $widgetData[UDFWidgetTpl::SCHEMA_ARG_NAME];
		$table = $widgetData[UDFWidgetTpl::TABLE_ARG_NAME];
		$field = $widgetData[UDFWidgetTpl::FIELD_ARG_NAME];
		
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
		else
		{
			$udf = $udfResults->retval[0];
			if (isset($udf->jsons))
			{
				$jsonSchema = json_decode($udf->jsons);
				if (is_object($jsonSchema))
				{
					if (isset($jsonSchema->type))
					{
						$this->_render($jsonSchema);
					}
					else
					{
						show_error(sprintf('%s.%s: Attribute "type" not present in the json schema', $schema, $table));
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
    private function _render($jsonSchema)
    {
// 		var_dump($jsonSchema);
		
		// Description, title and placeholder
		if (isset($jsonSchema->title) || isset($jsonSchema->description) || isset($jsonSchema->placeholder))
		{
			$this->_ci->load->library('PhrasesLib');
			
			if (isset($jsonSchema->title))
			{
				$tmpResult = $this->_ci->phraseslib->getPhrases('core', 'German', $jsonSchema->title, null, null, 'no');
				if (hasData($tmpResult))
				{
					$this->_args[Widget::HTML_ARG_NAME][UDFWidgetTpl::LABEL] = $tmpResult->retval[0]->text;
				}
				else
				{
					$this->_args[Widget::HTML_ARG_NAME][UDFWidgetTpl::LABEL] = null;
				}
			}
			
			if (isset($jsonSchema->description))
			{
				$tmpResult = $this->_ci->phraseslib->getPhrases('core', 'German', $jsonSchema->description, null, null, 'no');
				if (hasData($tmpResult))
				{
					$this->_args[Widget::HTML_ARG_NAME][UDFWidgetTpl::TITLE] = $tmpResult->retval[0]->text;
				}
				else
				{
					$this->_args[Widget::HTML_ARG_NAME][UDFWidgetTpl::TITLE] = null;
				}
			}
			
			if (isset($jsonSchema->placeholder))
			{
				$tmpResult = $this->_ci->phraseslib->getPhrases('core', 'German', $jsonSchema->placeholder, null, null, 'no');
				if (hasData($tmpResult))
				{
					$this->_args[Widget::HTML_ARG_NAME][UDFWidgetTpl::PLACEHOLDER] = $tmpResult->retval[0]->text;
				}
				else
				{
					$this->_args[Widget::HTML_ARG_NAME][UDFWidgetTpl::PLACEHOLDER] = null;
				}
			}
		}
		
		// Validation
		$this->_args[Widget::HTML_ARG_NAME][UDFWidgetTpl::REGEX] = null;
		$this->_args[Widget::HTML_ARG_NAME][UDFWidgetTpl::REQUIRED] = null;
		$this->_args[Widget::HTML_ARG_NAME][UDFWidgetTpl::MAX_VALUE] = null;
		$this->_args[Widget::HTML_ARG_NAME][UDFWidgetTpl::MIN_VALUE] = null;
		
		if (isset($jsonSchema->validation))
		{
			if (isset($jsonSchema->validation->regex) && is_array($jsonSchema->validation->regex))
			{
				foreach($jsonSchema->validation->regex as $regex)
				{
					if ($regex->language === 'js')
					{
						$this->_args[Widget::HTML_ARG_NAME][UDFWidgetTpl::REGEX] = $regex->expression;
					}
				}
			}
			
			if (isset($jsonSchema->validation->required))
			{
				$this->_args[Widget::HTML_ARG_NAME][UDFWidgetTpl::REQUIRED] = $jsonSchema->validation->required;
			}
			
			if (isset($jsonSchema->validation->{'max-value'}))
			{
				$this->_args[Widget::HTML_ARG_NAME][UDFWidgetTpl::MAX_VALUE] = $jsonSchema->validation->{'max-value'};
			}
			
			if (isset($jsonSchema->validation->{'min-value'}))
			{
				$this->_args[Widget::HTML_ARG_NAME][UDFWidgetTpl::MIN_VALUE] = $jsonSchema->validation->{'min-value'};
			}
		}
		
// 		var_dump($this->_args);
		
		// Type
		if ($jsonSchema->type == 'checkbox')
		{
			
		}
		else if ($jsonSchema->type == 'textfield')
		{
			
		}
		else if ($jsonSchema->type == 'textarea')
		{
			
		}
		else if ($jsonSchema->type == 'date')
		{
			
		}
		else if ($jsonSchema->type == 'dropdown')
		{
			$dropdownWidget = new DropdownWidget($this->_name, $this->_args);
			$dropdownWidget->render(array('elements' => $jsonSchema->listValues->enum));
		}
		else if ($jsonSchema->type == 'multipledropdown')
		{
			$multipleDropdownWidget = new MultipleDropdownWidget($this->_name, $this->_args);
			$multipleDropdownWidget->render(array('elements' => $jsonSchema->listValues->enum));
		}
    }
}