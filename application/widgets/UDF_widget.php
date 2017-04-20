<?php

/**
 * 
 */
class UDF_widget extends UDF_widget_tpl
{
    public function display($widgetData)
	{
		$schema = $widgetData[UDF_widget_tpl::SCHEMA_ARG_NAME];
		$table = $widgetData[UDF_widget_tpl::TABLE_ARG_NAME];
		$field = $widgetData[UDF_widget_tpl::FIELD_ARG_NAME];
		
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
				show_error('UDF_widget: generic error occurred');
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
						var_dump($jsonSchema);
						
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
							$dropdownWidget = new DropdownWidget();
						}
						else if ($jsonSchema->type == 'multipledropdown')
						{
							
						}
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
}