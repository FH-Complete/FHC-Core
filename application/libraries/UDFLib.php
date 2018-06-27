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
	const BE_REGEX_LANGUAGE = 'php'; // UDF php regex language attribute (back end)

	// HTML components
	const LABEL = 'title';
	const TITLE = 'description';
	const PLACEHOLDER = 'placeholder';

	// Validation attributes
	const REGEX = 'regex';
	const REQUIRED = 'required';
	const MAX_VALUE = 'max-value';
	const MIN_VALUE = 'min-value';
	const MAX_LENGTH = 'max-length';
	const MIN_LENGTH = 'min-length';

	// UDF DB constants
	const COLUMN_TYPE = 'jsonb';
	const COLUMN_NAME = 'udf_values';
	const COLUMN_PREFIX = 'udf_';
	const COLUMN_JSON_DESCRIPTION = 'jsons';

	const CHKBOX_TYPE = 'checkbox'; // UDF checkbox type

	const PHRASES_APP_NAME = 'core'; // Name of the app parameter used to retrive phrases

	private $_ci; // Code igniter instance

	/**
	 * Loads fhc helper
	 */
	public function __construct()
	{
		$this->_ci =& get_instance();
	}

	// -------------------------------------------------------------------------------------------------
	// Public methods

	/**
     * UDFWidget
     */
    public function UDFWidget($args, $htmlArgs = array())
    {
		if (!empty($args[UDFLib::SCHEMA_ARG_NAME]) && !empty($args[UDFLib::TABLE_ARG_NAME]))
		{
			// Loads the widget library
			$this->_ci->load->library('WidgetLib');

			// Loads widgets to render HTML for UDF
			loadResource(APPPATH.'widgets/udf');

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
	 *
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
					foreach ($jsonSchemasArray as $jsonSchema)
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
	 * Manage UDFs
	 */
	public function manageUDFs(&$data, $schemaAndTable, $udfValues = null)
	{
		$validate = success(true); // returned value
		// Contains a list of validation errors for the UDFs that have not passed the validation
		$notValidUDFsArray = array();

		$this->_ci->load->model('system/UDF_model', 'UDFModel');

		// Retrieves UDFs definitions for this table
		$resultUDFsDefinitions = $this->_ci->UDFModel->getUDFsDefinitions($schemaAndTable);
		if (hasData($resultUDFsDefinitions)) // standard check if everything is ok and data are present
		{
			// Get udf values from $data & clean udf values from $data
			// NOTE: Must be performed here because the load method populates the property UDFs too
			$udfsParameters = $this->_popUDFParameters($data);

			$requiredUDFsArray = array(); // contains a list of required UDFs
			// Contains the UDFs values to be stored
			// NOTE: the UDFs supplied that are not present in the UDF definition of this table, will be discarded
			$toBeStoredUDFsArray = array();

			// Decodes json that define the UDFs for this table
			$decodedUDFDefinitions = json_decode(
				$resultUDFsDefinitions->retval[0]->{UDFLib::COLUMN_JSON_DESCRIPTION}
			);

			// Loops through the UDFs definitions
			for ($i = 0; $i < count($decodedUDFDefinitions); $i++)
			{
				$decodedUDFDefinition = $decodedUDFDefinitions[$i]; // Definition of a single UDF

				// Loops through the UDFs values that should be stored
				foreach ($udfsParameters as $key => $val)
				{
					$tmpValidate = success(true); // temporary variable used to store the returned value from _validateUDFs

					// If this is the definition of this UDF
					if ($decodedUDFDefinition->{UDFLib::NAME} == $key)
					{
						if (isset($decodedUDFDefinition->{UDFLib::VALIDATION})) // If validation rules are present for this UDF
						{
							// Checks if the given UDF is required and the result will be stored in $chkRequiredPassed
							// If $chkRequiredPassed == true => required check passed
							// If $chkRequiredPassed == false => required check NOT passed
							$chkRequiredPassed = true;
							// If required property is present in the UDF description and it is true
							if (isset($decodedUDFDefinition->{UDFLib::VALIDATION}->{UDFLib::REQUIRED})
								&& $decodedUDFDefinition->{UDFLib::VALIDATION}->{UDFLib::REQUIRED} === true)
							{
								// If this UDF is a checkbox and the given value is false
								// OR
								// if this UDF is NOT a checkbox and the given value is null
								if (($decodedUDFDefinition->{UDFLib::TYPE} == UDFLib::CHKBOX_TYPE && $val === false)
									|| ($decodedUDFDefinition->{UDFLib::TYPE} != UDFLib::CHKBOX_TYPE && $val == null))
								{
									$chkRequiredPassed = false; // not passed
									// A new error is generated and added to array $requiredUDFsArray
									$requiredUDFsArray[$decodedUDFDefinition->{UDFLib::NAME}] = error(
										$decodedUDFDefinition->{UDFLib::NAME},
										EXIT_VALIDATION_UDF_REQUIRED
									);
								}
							}

							// If the previous required check has failed then the validation is not performed
							if ($chkRequiredPassed === true)
							{
								// Checks if the validation should be performed
								// If $toBeValidated == true => validation is performed
								// If $toBeValidated == false => validation is NOT performed
								$toBeValidated = false;
								// If this UDF is NOT a checkbox
								if ($decodedUDFDefinition->{UDFLib::TYPE} != UDFLib::CHKBOX_TYPE)
								{
									// If required property is NOT present in the UDF description
									if (!isset($decodedUDFDefinition->{UDFLib::VALIDATION}->{UDFLib::REQUIRED}))
									{
										$toBeValidated = true;
									}
									// If required property is present in the UDF description and it is true
									if (isset($decodedUDFDefinition->{UDFLib::VALIDATION}->{UDFLib::REQUIRED})
										&& $decodedUDFDefinition->{UDFLib::VALIDATION}->{UDFLib::REQUIRED} === true)
									{
										$toBeValidated = true;
									}
									// If required property is present in the UDF description and it is true and the given value is null
									if (isset($decodedUDFDefinition->{UDFLib::VALIDATION}->{UDFLib::REQUIRED})
										&& $decodedUDFDefinition->{UDFLib::VALIDATION}->{UDFLib::REQUIRED} === false
										&& $val != null)
									{
										$toBeValidated = true;
									}
								}

								if ($toBeValidated === true) // Checks if validation should be performed
								{
									$tmpValidate = $this->_validateUDFs(
										$decodedUDFDefinition->{UDFLib::VALIDATION},
										$decodedUDFDefinition->{UDFLib::NAME},
										$val
									);
								}
							}
						}

						// If validation is ok copy the value that is to be stored into $toBeStoredUDFsArray
						if (isSuccess($tmpValidate))
						{
							$toBeStoredUDFsArray[$key] = $val;
						}
						else // otherwise store the validation error in $notValidUDFsArray
						{
							$notValidUDFsArray[] = $tmpValidate;
						}
					}
				}
			}

			// Copies the remaining required UDFs into $notValidUDFsArray
			// because they were not supplied, therefore must be notified as error
			foreach ($requiredUDFsArray as $key => $val)
			{
				$notValidUDFsArray[] = array($val);
			}

			// If the validation of all the supplied UDFs is ok
			if (count($notValidUDFsArray) == 0)
			{
				// An update is performed, then in this case it preserves the values
				// of the UDF that are not updated
				if (is_array($udfValues) && count($udfValues) > 0)
				{
					foreach ($udfValues as $fieldName => $fieldValue)
					{
						// If this field is not present in the given parameters
						// then copy it from the DB without changes
						if (!array_key_exists($fieldName, $toBeStoredUDFsArray))
						{
							$toBeStoredUDFsArray[$fieldName] = $fieldValue;
						}
					}
				}
				$encodedToBeStoredUDFs = json_encode($toBeStoredUDFsArray); // encode to json
				if ($encodedToBeStoredUDFs !== false) // if encode was ok
				{
					// Save the supplied UDFs values
					$data[UDFLib::COLUMN_NAME] = $encodedToBeStoredUDFs;
				}
			}
			else // otherwise the returning value will be the list of UDFs validation errors
			{
				$validate = error($notValidUDFsArray, EXIT_VALIDATION_UDF);
			}
		}

		return $validate;
	}

	/**
	 * isUDFColumn
	 */
	public function isUDFColumn($columnName, $columnType)
	{
		$isUDFColumn = false;

		if (substr($columnName, 0, strlen(UDFLib::COLUMN_PREFIX)) == UDFLib::COLUMN_PREFIX
			&& $columnType == UDFLib::COLUMN_TYPE)
		{
			$isUDFColumn = true;
		}

		return $isUDFColumn;
	}

	// -------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Move UDFs from $data to $UDFs
	 */
	private function _popUDFParameters(&$data)
	{
		$udfsParameters = array();

		foreach ($data as $key => $val)
		{
			if (substr($key, 0, 4) == UDFLib::COLUMN_PREFIX)
			{
				$udfsParameters[$key] = $val; // stores UDF value into property UDFs
				unset($data[$key]); // remove from data
			}
		}

		return $udfsParameters;
	}

	/**
	 * Validates UDF value
	 */
	private function _validateUDFs($decodedUDFValidation, $udfName, $udfValue)
	{
		$returnArrayValidation = array(); // returned value

		// If $udfValue is not an array, then store it inside a new array
		$tmpUdfValues = $udfValue;
		if (!is_array($udfValue))
		{
			$tmpUdfValues = array($udfValue);
		}

		// Loops through all the supplied UDFs values
		foreach ($tmpUdfValues as $udfValIndx => $udfVal)
		{
			// If the single UDF value is not an array or an object
			if (!is_array($udfVal) && !is_object($udfVal))
			{
				// If the UDF value is numeric (integer, float, double...)
				if (is_numeric($udfVal))
				{
					// If min value attribute is present in the validation for this UDF,
					// then checks if the value of this UDF is compliant to this attribute
					if (isset($decodedUDFValidation->{UDFLib::MIN_VALUE})
						&& $udfVal < $decodedUDFValidation->{UDFLib::MIN_VALUE})
					{
						// validation is failed and the error is stored in $returnArrayValidation
						$returnArrayValidation[] = error($udfName, EXIT_VALIDATION_UDF_MIN_VALUE);
					}

					// If max value attribute is present in the validation for this UDF,
					// then checks if the value of this UDF is compliant to this attribute
					if (isset($decodedUDFValidation->{UDFLib::MAX_VALUE})
						&& $udfVal > $decodedUDFValidation->{UDFLib::MAX_VALUE})
					{
						// validation is failed and the error is stored in $returnArrayValidation
						$returnArrayValidation[] = error($udfName, EXIT_VALIDATION_UDF_MAX_VALUE);
					}
				}

				$strUdfVal = strval($udfVal); // store in $strUdfVal the string conversion of $udfVal
				// If min length attribute is present in the validation for this UDF,
				// then checks if the value of this UDF is compliant to this attribute
				if (isset($decodedUDFValidation->{UDFLib::MIN_LENGTH}) && isset($strUdfVal)
					&& strlen($strUdfVal) < $decodedUDFValidation->{UDFLib::MIN_LENGTH})
				{
					// validation is failed and the error is stored in $returnArrayValidation
					$returnArrayValidation[] = error($udfName, EXIT_VALIDATION_UDF_MIN_LENGTH);
				}

				// If max length attribute is present in the validation for this UDF,
				// then checks if the value of this UDF is compliant to this attribute
				if (isset($decodedUDFValidation->{UDFLib::MAX_LENGTH}) && isset($strUdfVal)
					&& strlen($strUdfVal) > $decodedUDFValidation->{UDFLib::MAX_LENGTH})
				{
					// validation is failed and the error is stored in $returnArrayValidation
					$returnArrayValidation[] = error($udfName, EXIT_VALIDATION_UDF_MAX_LENGTH);
				}

				// If $udfVal is a string
				if (is_string($udfVal))
				{
					// Search for a php regular expression in the validation of this UDF, if one is found
					// then checks if the value of this UDF is compliant to this attribute
					if (isset($decodedUDFValidation->{UDFLib::REGEX})
						&& is_array($decodedUDFValidation->{UDFLib::REGEX}))
					{
						foreach ($decodedUDFValidation->{UDFLib::REGEX} as $regexIndx => $regex)
						{
							if ($regex->language == UDFLib::BE_REGEX_LANGUAGE)
							{
								if (preg_match($regex->expression, $udfVal) != 1)
								{
									// validation is failed and the error is stored in $returnArrayValidation
									$returnArrayValidation[] = error($udfName, EXIT_VALIDATION_UDF_REGEX);
								}
							}
						}
					}
				}
			}
			else // otherwise the validation is failed and the error is stored in $returnArrayValidation
			{
				$returnArrayValidation[] = error($udfName, EXIT_VALIDATION_UDF_NOT_VALID_VAL);
			}
		}

		// If no UDF validation errors were raised, it's a success!!
		if (count($returnArrayValidation) == 0)
		{
			$returnArrayValidation = success(true);
		}

		return $returnArrayValidation;
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
		usort($jsonSchemasArray, function ($a, $b) {
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
			elseif (is_string($udfResults))
			{
				show_error($udfResults);
			}
			else
			{
				show_error('UDFWidget: generic error occurred');
			}
		}
		elseif (!hasData($udfResults))
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
		elseif ($jsonSchema->{UDFLib::TYPE} == 'textfield')
		{
			$this->_renderTextfield($jsonSchema, $widgetData);
		}
		// Textarea
		elseif ($jsonSchema->{UDFLib::TYPE} == 'textarea')
		{
			$this->_renderTextarea($jsonSchema, $widgetData);
		}
		// Date
		elseif ($jsonSchema->{UDFLib::TYPE} == 'date')
		{
			// To be done
		}
		// Dropdown
		elseif ($jsonSchema->{UDFLib::TYPE} == 'dropdown')
		{
			$this->_renderDropdown($jsonSchema, $widgetData);
		}
		// Multiple dropdown
		elseif ($jsonSchema->{UDFLib::TYPE} == 'multipledropdown')
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
		elseif (isset($jsonSchema->{UDFLib::LIST_VALUES}->sql))
		{
			// UDFModel is loaded in method _loadUDF that is called before the current method
			$queryResult = $this->_ci->UDFModel->execReadOnlyQuery($jsonSchema->{UDFLib::LIST_VALUES}->sql);
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
		// By default set to null all the attributes
		$htmlParameters[HTMLWidget::LABEL] = null;
		$htmlParameters[HTMLWidget::TITLE] = null;
		$htmlParameters[HTMLWidget::PLACEHOLDER] = null;

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
					DEFAULT_LANGUAGE,
					$jsonSchema->{UDFLib::LABEL},
					null,
					null,
					'no'
				);
				if (hasData($tmpResult))
				{
					$htmlParameters[HTMLWidget::LABEL] = $tmpResult->retval[0]->text;
				}
			}

			// If is set the title property in the json schema
			if (isset($jsonSchema->{UDFLib::TITLE}))
			{
				// Load the related phrase
				$tmpResult = $this->_ci->phraseslib->getPhrases(
					UDFLib::PHRASES_APP_NAME,
					DEFAULT_LANGUAGE,
					$jsonSchema->{UDFLib::TITLE},
					null,
					null,
					'no'
				);
				if (hasData($tmpResult))
				{
					$htmlParameters[HTMLWidget::TITLE] = $tmpResult->retval[0]->text;
				}
			}

			// If is set the placeholder property in the json schema
			if (isset($jsonSchema->{UDFLib::PLACEHOLDER}))
			{
				// Load the related phrase
				$tmpResult = $this->_ci->phraseslib->getPhrases(
					UDFLib::PHRASES_APP_NAME,
					DEFAULT_LANGUAGE,
					$jsonSchema->{UDFLib::PLACEHOLDER},
					null,
					null,
					'no'
				);
				if (hasData($tmpResult))
				{
					$htmlParameters[HTMLWidget::PLACEHOLDER] = $tmpResult->retval[0]->text;
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
		$htmlParameters[HTMLWidget::MIN_LENGTH] = null;
		$htmlParameters[HTMLWidget::MAX_LENGTH] = null;

		// If validation property is present in the json schema
		if (isset($jsonSchema->{UDFLib::VALIDATION}))
		{
			$jsonSchemaValidation =& $jsonSchema->{UDFLib::VALIDATION}; // Reference for a better code readability

			// Front-end regex
			if (isset($jsonSchemaValidation->{UDFLib::REGEX})
				&& is_array($jsonSchemaValidation->{UDFLib::REGEX}))
			{
				foreach ($jsonSchemaValidation->{UDFLib::REGEX} as $regex)
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
