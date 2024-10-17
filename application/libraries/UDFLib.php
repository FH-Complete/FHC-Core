<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Library to manage UDFs
 */
class UDFLib
{
	const UDF_UNIQUE_ID = 'udfUniqueId'; // Name of the UDF widget unique id

	const SESSION_NAME = 'FHC_UDF_WIDGET'; // Name of the session area used for UDFs

	// Parameters names
	const WIDGET_NAME = 'UDFWidget'; // UDFWidget name
	const SCHEMA_ARG_NAME = 'schema'; // Schema parameter name
	const TABLE_ARG_NAME = 'table'; // Table parameter name
	const FIELD_ARG_NAME = 'field'; // Field parameter name
	const UDFS_ARG_NAME = 'udfs';  // UDFs parameter name

	// UDF json schema attributes
	const NAME = 'name'; // UDF name attribute
	const TYPE = 'type'; // UDF type attribute
	const SORT = 'sort'; // UDF sort attribute
	const VALIDATION = 'validation'; // UDF validation attribute
	const LIST_VALUES = 'listValues'; // UDF listValues attribute
	const FE_REGEX_LANGUAGE = 'js'; // UDF javascript regex language attribute (front end)
	const BE_REGEX_LANGUAGE = 'php'; // UDF php regex language attribute (back end)

	// ...to specify permissions that are needed to use this TableWidget
	const REQUIRED_PERMISSIONS_PARAMETER = 'requiredPermissions';

	const PERMISSION_TABLE_METHOD = 'UDFWidget'; // Name for fake method to be checked by the PermissionLib
	const PERMISSION_TYPE_READ = 'r';
	const PERMISSION_TYPE_WRITE = 'w';

	// ...to specify the primary key name and value
	const PRIMARY_KEY_NAME = 'primaryKeyName';
	const PRIMARY_KEY_VALUE = 'primaryKeyValue';

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

	const PHRASES_APP_NAME = 'core'; // Name of the app parameter used to retrieve phrases

	private $_ci; // Code igniter instance

	private $_udfUniqueId; // Property that contains the UDF widget unique id

	private $_definition_cache = [];

	/**
	 * Gets CI instance
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
		if ((isset($args[self::SCHEMA_ARG_NAME]) && !isEmptyString($args[self::SCHEMA_ARG_NAME]))
			&& (isset($args[self::TABLE_ARG_NAME]) && !isEmptyString($args[self::TABLE_ARG_NAME])))
		{
			// Loads the widget library
			$this->_ci->load->library('WidgetLib');

			// Loads widgets to render HTML for UDF
			loadResource(APPPATH.'widgets/udf');

			// Default external block is true
			if (!isset($args[self::FIELD_ARG_NAME]) && !isset($htmlArgs[HTMLWidget::EXTERNAL_BLOCK]))
			{
				$htmlArgs[HTMLWidget::EXTERNAL_BLOCK] = true;
			}

			return $this->_ci->widgetlib->widget(
				self::WIDGET_NAME,
				$args,
				$htmlArgs
			);
		}
		else
		{
			if (!isset($args[self::SCHEMA_ARG_NAME]) || isEmptyString($args[self::SCHEMA_ARG_NAME]))
			{
				show_error(self::SCHEMA_ARG_NAME.' parameter is missing!');
			}
			if (!isset($args[self::TABLE_ARG_NAME]) || isEmptyString($args[self::TABLE_ARG_NAME]))
			{
				show_error(self::TABLE_ARG_NAME.' parameter is missing!');
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
		$field = null;
		$schema = $widgetData[self::SCHEMA_ARG_NAME]; // schema attribute
		$table = $widgetData[self::TABLE_ARG_NAME]; // table attribute

		if (isset($widgetData[self::FIELD_ARG_NAME]))
		{
			$field = $widgetData[self::FIELD_ARG_NAME]; // UDF name
		}

		$udfResults = $this->_loadUDF($schema, $table); // loads UDF definition
		if (hasData($udfResults))
		{
			$udf = getData($udfResults)[0]; // only one record is loaded
			if (isset($udf->jsons))
			{
				$jsonSchemas = json_decode($udf->jsons); // decode the json schema
				if (is_object($jsonSchemas) || is_array($jsonSchemas))
				{
					//
					$this->_printStartUDFBlock($widgetData);

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
						if (!isset($jsonSchema->{self::TYPE}))
						{
							show_error(sprintf('%s.%s: Attribute "type" not present in the json schema', $schema, $table));
						}
						// If the name property is not present then show an error
						if (!isset($jsonSchema->{self::NAME}))
						{
							show_error(sprintf('%s.%s: Attribute "name" not present in the json schema', $schema, $table));
						}
						// If the requiredPermissions property is not present then show an error
						if (!isset($jsonSchema->{self::REQUIRED_PERMISSIONS_PARAMETER}))
						{
							show_error(sprintf('%s.%s: Attribute "requiredPermissions" not present in the json schema', $schema, $table));
						}

						// Set the required permissions for this UDF
						$this->_setRequiredPermissions($jsonSchema->{self::NAME}, $jsonSchema->{self::REQUIRED_PERMISSIONS_PARAMETER});

						// If a UDF is specified and is present in the json schemas list or no UDF is specified
						if ((isset($field) && $field == $jsonSchema->{self::NAME}) || !isset($field))
						{
							// If the user has the permissions to read this field
							if ($this->_readAllowed($jsonSchema->{self::REQUIRED_PERMISSIONS_PARAMETER}))
							{
								// Set attributes using phrases
								$this->_setAttributesWithPhrases($jsonSchema, $widgetData[HTMLWidget::HTML_ARG_NAME]);

								// Set validation attributes
								$this->_setValidationAttributes($jsonSchema, $widgetData[HTMLWidget::HTML_ARG_NAME]);

								// Set name and id attributes
								$this->_setNameAndId($jsonSchema, $widgetData[HTMLWidget::HTML_ARG_NAME]);

								// Set if the field is in read only mode
								$this->_setReadOnly($jsonSchema, $widgetData[HTMLWidget::HTML_ARG_NAME]);

								// Render the HTML for this UDF
								$this->_render($jsonSchema, $widgetData);
							}
							// otherwise the UDF is not displayed

							// If a UDf is specified and it was found then stop looking through this list
							if (isset($field) && $field == $jsonSchema->{self::NAME})
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

					//
					$this->_printEndUDFBlock();
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
	 * UDFs permissions check and convertion to read them from database
	 */
	public function prepareUDFsRead(&$data, $schemaAndTable, $udfValues = null)
	{
		$this->_ci->load->model('system/UDF_model', 'UDFModel');

		// Retrieves UDFs definitions for this table
		$resultUDFsDefinitions = $this->_ci->UDFModel->getUDFsDefinitions($schemaAndTable);

		// If an error occurred while reading from database
		if (isError($resultUDFsDefinitions))
		{
			$data = array(); // then set data as an empty array
			return; // and exit from this method
		}

		// If there are no UDFs defined for this table the return
		if (!hasData($resultUDFsDefinitions)) return;

		// If not an error and has data, decodes json that define the UDFs for this table
		$decodedUDFDefinitions = json_decode(
			getData($resultUDFsDefinitions)[0]->{self::COLUMN_JSON_DESCRIPTION}
		);

		// Looping on results, resultElement is an object that represent a database record
		foreach ($data as $resultElement)
		{
			// Decode the JSON column udf_values
			$udfColumn = json_decode($resultElement->{self::COLUMN_NAME});

			// If this is not a valid JSON then skip to the next database record
			if ($udfColumn == null) continue;

			// For each UDF column of this database record
			foreach (get_object_vars($udfColumn) as $columnName => $columnValue)
			{
				$udfColumnToBeRemoved = $columnName; // let's try to remove it

				// Loops through the UDFs definitions
				foreach ($decodedUDFDefinitions as $decodedUDFDefinition)
				{
					// If the column exists in the UDF definition
					if ($columnName == $decodedUDFDefinition->{self::NAME})
					{
						$udfColumnToBeRemoved = null; // then keep it
					}
				}

				// If in this record have been found a _not_ defined UDF then remove it
				if (!isEmptyString($udfColumnToBeRemoved)) unset($udfColumn->{$udfColumnToBeRemoved});
			}

			// Loops through the UDFs definitions
			foreach ($decodedUDFDefinitions as $decodedUDFDefinition)
			{
				// Checks if the requiredPermissions is available and it is a valid array or a valid string
				if (isset($decodedUDFDefinition->{self::REQUIRED_PERMISSIONS_PARAMETER})
					&& (!isEmptyArray($decodedUDFDefinition->{self::REQUIRED_PERMISSIONS_PARAMETER})
						|| !isEmptyString($decodedUDFDefinition->{self::REQUIRED_PERMISSIONS_PARAMETER})))
				{
					// Then check if the user has the permissions to read such UDF
					if (!$this->_readAllowed($decodedUDFDefinition->{self::REQUIRED_PERMISSIONS_PARAMETER}))
					{
						// If not then remove the UDF from the result set
						unset($udfColumn->{$decodedUDFDefinition->{self::NAME}});
					}
				}
				else // If not then remove the UDF from the result set
				{
					unset($udfColumn->{$decodedUDFDefinition->{self::NAME}});
				}
			}

			// Add the defined and permitted UDF columns to the record set
			foreach (get_object_vars($udfColumn) as $columnName => $columnValue)
			{
				$resultElement->{$columnName} = $columnValue;
			}

			// And finally remove the UDFs column
			unset($resultElement->{self::COLUMN_NAME});
		}
	}

	/**
	 * UDFs validation and permissions check to write them into database
	 */
	public function prepareUDFsWrite(&$data, $schemaAndTable, $udfValues = null)
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
				getData($resultUDFsDefinitions)[0]->{self::COLUMN_JSON_DESCRIPTION}
			);

			// Loops through the UDFs definitions
			foreach ($decodedUDFDefinitions as $decodedUDFDefinition)
			{
				// Checks if the requiredPermissions is available and it is a valid array or a valid string
				if (isset($decodedUDFDefinition->{self::REQUIRED_PERMISSIONS_PARAMETER})
					&& (!isEmptyArray($decodedUDFDefinition->{self::REQUIRED_PERMISSIONS_PARAMETER})
						|| !isEmptyString($decodedUDFDefinition->{self::REQUIRED_PERMISSIONS_PARAMETER})))
				{
					// Then check if the user has the permissions to write such UDF
					if (!$this->_writeAllowed($decodedUDFDefinition->{self::REQUIRED_PERMISSIONS_PARAMETER}))
					{
						// If the logged user has no permissions then remove the UDF
						unset($udfsParameters[$decodedUDFDefinition->{self::NAME}]);
					}
				}
				else
				{
					// If no permissions have been defined for this UDF then remove it
					unset($udfsParameters[$decodedUDFDefinition->{self::NAME}]);
				}

				// Loops through the UDFs values that should be stored
				foreach ($udfsParameters as $key => $val)
				{
					$tmpValidateArray = array(); // temporary variable used to store the returned value from _validateUDFs

					// If this is the definition of this UDF
					if ($decodedUDFDefinition->{self::NAME} == $key)
					{
						if (isset($decodedUDFDefinition->{self::VALIDATION})) // If validation rules are present for this UDF
						{
							// Checks if the given UDF is required and the result will be stored in $chkRequiredPassed
							// If $chkRequiredPassed == true => required check passed
							// If $chkRequiredPassed == false => required check NOT passed
							$chkRequiredPassed = true;
							// If required property is present in the UDF description and it is true
							if (isset($decodedUDFDefinition->{self::VALIDATION}->{self::REQUIRED})
								&& $decodedUDFDefinition->{self::VALIDATION}->{self::REQUIRED} === true)
							{
								// If this UDF is a checkbox and the given value is false
								// OR
								// if this UD7F is NOT a checkbox and the given value is null
								if (($decodedUDFDefinition->{self::TYPE} == self::CHKBOX_TYPE && $val === false)
									|| ($decodedUDFDefinition->{self::TYPE} != self::CHKBOX_TYPE && $val == null))
								{
									$chkRequiredPassed = false; // not passed
									// A new error is generated and added to array $requiredUDFsArray
									$requiredUDFsArray[$decodedUDFDefinition->{self::NAME}] = error(
										$decodedUDFDefinition->{self::NAME},
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
								if ($decodedUDFDefinition->{self::TYPE} != self::CHKBOX_TYPE)
								{
									// If required property is NOT present in the UDF description
									if (!isset($decodedUDFDefinition->{self::VALIDATION}->{self::REQUIRED}))
									{
										$toBeValidated = true;
									}
									// If required property is present in the UDF description and it is true
									if (isset($decodedUDFDefinition->{self::VALIDATION}->{self::REQUIRED})
										&& $decodedUDFDefinition->{self::VALIDATION}->{self::REQUIRED} === true)
									{
										$toBeValidated = true;
									}
									// If required property is present in the UDF description and it is true and the given value is null
									if (isset($decodedUDFDefinition->{self::VALIDATION}->{self::REQUIRED})
										&& $decodedUDFDefinition->{self::VALIDATION}->{self::REQUIRED} === false
										&& $val != null)
									{
										$toBeValidated = true;
									}
								}

								if ($toBeValidated === true) // Checks if validation should be performed
								{
									$tmpValidateArray = $this->_validateUDFs(
										$decodedUDFDefinition->{self::VALIDATION},
										$decodedUDFDefinition->{self::NAME},
										$val
									);
								}
							}
						}

						// If validation is ok copy the value that is to be stored into $toBeStoredUDFsArray
						if (isEmptyArray($tmpValidateArray))
						{
							$toBeStoredUDFsArray[$key] = $val;
						}
						else // otherwise store the validation errors in $notValidUDFsArray
						{
							$notValidUDFsArray = array_merge($notValidUDFsArray, $tmpValidateArray);
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
			if (isEmptyArray($notValidUDFsArray))
			{
				// An update is performed, then in this case it preserves the values
				// of the UDF that are not updated
				if (!isEmptyArray($udfValues))
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
					$data[self::COLUMN_NAME] = $encodedToBeStoredUDFs;
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
	public function isUDFColumn($columnName, $columnType = self::COLUMN_TYPE)
	{
		$isUDFColumn = false;

		if (substr($columnName, 0, strlen(self::COLUMN_PREFIX)) == self::COLUMN_PREFIX
			&& $columnType == self::COLUMN_TYPE)
		{
			$isUDFColumn = true;
		}

		return $isUDFColumn;
	}

	/**
	 * Set the _udfUniqueId property
	 */
	public function setUDFUniqueId($udfUniqueId)
	{
		$this->_udfUniqueId = $udfUniqueId;
	}

	/**
	 * Return an unique string that identify this UDF widget
	 * NOTE: The default value is the URI where the FilterWidget is called
	 * If the fhc_controller_id is present then is also used
	 */
	public function setUDFUniqueIdByParams($params)
	{
		if ($params != null
			&& is_array($params)
			&& isset($params[self::UDF_UNIQUE_ID])
			&& !isEmptyString($params[self::UDF_UNIQUE_ID]))
		{
			$udfUniqueId = $this->_ci->router->directory.$this->_ci->router->class.'/'.
				$this->_ci->router->method.'/'.
				$params[self::UDF_UNIQUE_ID];

			$this->setUDFUniqueId($udfUniqueId);
		}
	}

	/**
	 * Wrapper method to the session helper funtions to retrieve the whole session for this UDF widget
	 */
	public function getSession()
	{
		return getSessionElement(self::SESSION_NAME, $this->_udfUniqueId);
	}

	/**
	 * Wrapper method to the session helper funtions to retrieve one element from the session of this UDF widget
	 */
	public function getSessionElement($name)
	{
		$session = getSessionElement(self::SESSION_NAME, $this->_udfUniqueId);

		if (isset($session[$name]))
		{
			return $session[$name];
		}

		return null;
	}

	/**
	 * Wrapper method to the session helper funtions to set the whole session for this UDF widget
	 */
	public function setSession($data)
	{
		setSessionElement(self::SESSION_NAME, $this->_udfUniqueId, $data);
	}

	/**
	 * Wrapper method to the session helper funtions to set one element in the session for this UDF widget
	 */
	public function setSessionElement($name, $value)
	{
		$session = getSessionElement(self::SESSION_NAME, $this->_udfUniqueId);

		$session[$name] = $value;

		setSessionElement(self::SESSION_NAME, $this->_udfUniqueId, $session); // stores the single value
	}

	/**
	 * Save UDFs
	 */
	public function saveUDFs($udfs)
	{
		// Read the all session for this udf widget
		$session = $this->getSession();

		// If session is empty then return an error
		if ($session == null) return error('No UDFWidget loaded');

		// Workaround to load CI
		$this->_ci->load->model('system/UDF_model', 'UDFModel');

		// Initialize a new DB_Model
		$dbModel = new DB_Model();

		// Setup the new dbModel object with...
		$dbModel->setup(
			$session[self::SCHEMA_ARG_NAME], // ... schema...
			$session[self::TABLE_ARG_NAME], // ...table...
			$session[self::PRIMARY_KEY_NAME] // ...and primary key name
		);

		// Returns the result of the database update operation to save UDFs
		return $dbModel->update(
			array($session[self::PRIMARY_KEY_NAME] => $session[self::PRIMARY_KEY_VALUE]),
			get_object_vars($udfs)
		);
	}

	/**
	 * Gets the UDF definitions for a model
	 *
	 * @param DB_Model			$targetModel
	 *
	 * @return stdClass
	 */
	public function getDefinitionForModel($targetModel)
	{
		$dbTable = $targetModel->getDbTable();
		if (!isset($this->_definition_cache[$dbTable])) {
			$this->_ci->load->model('system/UDF_model', 'UDFModel');
			list($schema, $table) = explode('.', $dbTable);
			$result = $this->_ci->UDFModel->loadWhere([
				'schema' => $schema,
				'table' => $table
			]);
			if (isError($result))
				return $result;
			if (!hasData($result))
				$this->_definition_cache[$dbTable] = [];
			else
				$this->_definition_cache[$dbTable] = json_decode(current($result->retval)->jsons, true);
		}
		return success($this->_definition_cache[$dbTable]);
	}

	/**
	 * Gets the UDFs for db entry with translated params and resolved listValues for dropdowns
	 *
	 * @param DB_Model			$targetModel
	 * @param mixed				$id
	 *
	 * @return stdClass
	 */
	public function getFieldArray($targetModel, $id)
	{
		// Load Libraries
		$this->_ci->load->library('PhrasesLib');
		$this->_ci->load->library('PermissionLib');

		$result = $this->getDefinitionForModel($targetModel);
		if (isError($result))
			return $result;
		$definitions = $result->retval;

		usort($definitions, function ($a, $b) {
			return $a[self::SORT] - $b[self::SORT];
		});

		$values = $targetModel->getUDFs($id);

		$fields = [];
		foreach ($definitions as $field) {
			// check read permissions
			if (!$this->_ci->permissionlib->hasAtLeastOne(
				$field[self::REQUIRED_PERMISSIONS_PARAMETER],
				self::PERMISSION_TABLE_METHOD,
				self::PERMISSION_TYPE_READ
			))
				continue;

			// set value
			if (isset($values[$field[self::NAME]])) {
				$field['value'] = $values[$field[self::NAME]];
			} elseif (isset($field['defaultValue'])) {
				$field['value'] = $field['defaultValue'];
			} elseif (isset($field[self::TYPE]) && $field[self::TYPE] == 'checkbox') {
				$field['value'] = false;
			} else {
				$field['value'] = '';
			}

			// translate params
			foreach ([self::LABEL, self::TITLE, self::PLACEHOLDER] as $key) {
				if (isset($field[$key])) {
					$res = $this->_ci->phraseslib->getPhrases(self::PHRASES_APP_NAME, getUserLanguage(), $field[$key], null, null, 'no');
					if (hasData($res))
						$field[$key] = current(getData($res))->text;
				}
			}

			// check write permissions
			$field['disabled'] = !$this->_ci->permissionlib->hasAtLeastOne(
				$field[self::REQUIRED_PERMISSIONS_PARAMETER],
				self::PERMISSION_TABLE_METHOD,
				self::PERMISSION_TYPE_WRITE
			);

			// set listValues for dropdowns
			if (isset($field[self::LIST_VALUES])) {
				if (isset($field[self::LIST_VALUES]['enum'])) {
					$field['options'] = $field[self::LIST_VALUES]['enum'];
				} elseif (isset($field[self::LIST_VALUES]['sql'])) {
					$res = $this->_ci->UDFModel->execReadOnlyQuery($field[self::LIST_VALUES]['sql']);
					$field['options'] = hasData($res) ? getData($res) : [];
				}
			}

			// add to array
			$fields[] = $field;
		}
		return success($fields);
	}

	/**
	 * Gets a validation config array for CI form_validation
	 *
	 * @param DB_Model			$targetModel
	 * @param array				(optional) $filter
	 *
	 * @return stdClass
	 */
	public function getCiValidations($targetModel, $filter = null)
	{
		$result = $this->getDefinitionForModel($targetModel);
		if (isError($result))
			return $result;
		$definitions = getData($result);

		$result = [];
		foreach ($definitions as $def) {
			if ($filter && !isset($filter[$def['name']]))
				continue;
			$validations = [];
			if (isset($def['requiredPermissions']))
				$validations[] = 'has_write_permissions[' . implode(',', $def['requiredPermissions']) . ']';
			if (isset($def['required']))
				$validations[] = 'required';
			if (isset($def['validation'])) {
				if (isset($def['validation']['max-value']))
					$validations[] = 'less_than_equal_to[' . $def['validation']['max-value'] . ']';
				if (isset($def['validation']['min-value']))
					$validations[] = 'greater_than_equal_to[' . $def['validation']['min-value'] . ']';
				if (isset($def['validation']['max-length']))
					$validations[] = 'max_length[' . $def['validation']['max-length'] . ']';
				if (isset($def['validation']['min-length']))
					$validations[] = 'min_length[' . $def['validation']['min-length'] . ']';
				if (isset($def['validation']['regex']) && is_array($def['validation']['regex'])) {
					foreach ($def['validation']['regex'] as $regex) {
						if ($regex['language'] == 'php') {
							$validations[] = 'regex_match[' . $regex['expression'] . ']';
						}
					}
				}
			}
			if ($validations)
				$result[] = [
					'field' => $def['name'],
					'label' => $def['title'],
					'rules' => $validations
				];
		}
		return success($result);
	}

	// -------------------------------------------------------------------------------------------------
	// Private methods
	//

	/**
	 * Checks if at least one of the permissions given as parameter belongs to the authenticated user in read mode
	 * Wrapper method to permissionlib->hasAtLeastOne
	 */
	private function _readAllowed($requiredPermissions)
	{
		$readAllowed = false;

		// If the user is logged then it is possible to check the permissions
		if (isLogged())
		{
			$this->_ci->load->library('PermissionLib'); // Load permission library

			$readAllowed = $this->_ci->permissionlib->hasAtLeastOne(
				$requiredPermissions,
				self::PERMISSION_TABLE_METHOD,
				self::PERMISSION_TYPE_READ
			);
		} // otherwise it is not possible to check the permissions

		return $readAllowed;
	}

	/**
	 * Checks if at least one of the permissions given as parameter belongs to the authenticated user in write mode
	 * Wrapper method to permissionlib->hasAtLeastOne
	 */
	private function _writeAllowed($requiredPermissions)
	{
		$writeAllowed = false;

		// If the user is logged then it is possible to check the permissions
		if (isLogged())
		{
			$this->_ci->load->library('PermissionLib'); // Load permission library

			$writeAllowed = $this->_ci->permissionlib->hasAtLeastOne(
				$requiredPermissions,
				self::PERMISSION_TABLE_METHOD,
				self::PERMISSION_TYPE_WRITE
			);
		} // otherwise it is not possible to check the permissions

		return $writeAllowed;
	}

	/**
	 * Set an array of required permissions for a UDF into the session
	 */
	private function _setRequiredPermissions($udfName, $permissions)
	{
		// Get the session for this UDFWidget
		$session = $this->getSession();

		// If does _not_ exist yet in the session
		if (!isset($session[self::REQUIRED_PERMISSIONS_PARAMETER]))
		{
			$session[self::REQUIRED_PERMISSIONS_PARAMETER] = array();
		}

		// Set the required permission in the session for this UDFWidget
		$session[self::REQUIRED_PERMISSIONS_PARAMETER][$udfName] = $permissions;

		// Write into the session
		$this->setSession($session);
	}

	/**
	 * Print the block for UDFs
	 */
	private function _printStartUDFBlock($widgetData)
	{
		$startBlock = '<div type="%s" udfUniqueId="%s">'."\n";

		echo sprintf(
			$startBlock,
			self::WIDGET_NAME,
			$widgetData[self::UDF_UNIQUE_ID]
		);
	}

	/**
	 * Print the end of the UDFs block
	 */
	private function _printEndUDFBlock()
	{
		echo '</div>'."\n";
	}

	/**
	 * Move UDFs from $data to $UDFs
	 */
	private function _popUDFParameters(&$data)
	{
		$udfsParameters = array();

		foreach ($data as $columnName => $columnValue)
		{
			if ($this->isUDFColumn($columnName))
			{
				$udfsParameters[$columnName] = $columnValue; // stores UDF value into property UDFs
				unset($data[$columnName]); // remove from data
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
					if (isset($decodedUDFValidation->{self::MIN_VALUE})
						&& $udfVal < $decodedUDFValidation->{self::MIN_VALUE})
					{
						// validation is failed and the error is stored in $returnArrayValidation
						$returnArrayValidation[] = error($udfName, EXIT_VALIDATION_UDF_MIN_VALUE);
					}

					// If max value attribute is present in the validation for this UDF,
					// then checks if the value of this UDF is compliant to this attribute
					if (isset($decodedUDFValidation->{self::MAX_VALUE})
						&& $udfVal > $decodedUDFValidation->{self::MAX_VALUE})
					{
						// validation is failed and the error is stored in $returnArrayValidation
						$returnArrayValidation[] = error($udfName, EXIT_VALIDATION_UDF_MAX_VALUE);
					}
				}

				$strUdfVal = strval($udfVal); // store in $strUdfVal the string conversion of $udfVal
				// If min length attribute is present in the validation for this UDF,
				// then checks if the value of this UDF is compliant to this attribute
				if (isset($decodedUDFValidation->{self::MIN_LENGTH}) && isset($strUdfVal)
					&& strlen($strUdfVal) < $decodedUDFValidation->{self::MIN_LENGTH})
				{
					// validation is failed and the error is stored in $returnArrayValidation
					$returnArrayValidation[] = error($udfName, EXIT_VALIDATION_UDF_MIN_LENGTH);
				}

				// If max length attribute is present in the validation for this UDF,
				// then checks if the value of this UDF is compliant to this attribute
				if (isset($decodedUDFValidation->{self::MAX_LENGTH}) && isset($strUdfVal)
					&& strlen($strUdfVal) > $decodedUDFValidation->{self::MAX_LENGTH})
				{
					// validation is failed and the error is stored in $returnArrayValidation
					$returnArrayValidation[] = error($udfName, EXIT_VALIDATION_UDF_MAX_LENGTH);
				}

				// If $udfVal is a string
				if (is_string($udfVal))
				{
					// Search for a php regular expression in the validation of this UDF, if one is found
					// then checks if the value of this UDF is compliant to this attribute
					if (isset($decodedUDFValidation->{self::REGEX})
						&& is_array($decodedUDFValidation->{self::REGEX}))
					{
						foreach ($decodedUDFValidation->{self::REGEX} as $regexIndx => $regex)
						{
							if ($regex->language == self::BE_REGEX_LANGUAGE)
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

		return $returnArrayValidation;
	}

	/**
	 * Disable the HTML element if in read only mode
	 */
	private function _setReadOnly($jsonSchema, &$htmlParameters)
	{
		// If write permissions _not_ exist then set the field as disabled
		if (!$this->_writeAllowed($jsonSchema->{self::REQUIRED_PERMISSIONS_PARAMETER}))
		{
			$htmlParameters[HTMLWidget::DISABLED] = HTMLWidget::DISABLED; // any values is fine
		}
		else // otherwise restore to default
		{
			if (isset($htmlParameters[HTMLWidget::DISABLED])) unset($htmlParameters[HTMLWidget::DISABLED]);
		}
	}

	/**
	 * Set the name and id attribute of the HTML element
	 */
	private function _setNameAndId($jsonSchema, &$htmlParameters)
	{
		$htmlParameters[HTMLWidget::HTML_ID] = $jsonSchema->{self::NAME};
		$htmlParameters[HTMLWidget::HTML_NAME] = $jsonSchema->{self::NAME};
	}

	/**
	 * Sort the list of UDF by sort property
	 */
	private function _sortJsonSchemas(&$jsonSchemasArray)
	{
		usort($jsonSchemasArray, function ($a, $b) {
			if (!isset($a->{self::SORT}))
			{
				$a->{self::SORT} = 9999;
			}
			if (!isset($b->{self::SORT}))
			{
				$b->{self::SORT} = 9999;
			}
			if ($a->{self::SORT} == $b->{self::SORT})
			{
				return 0;
			}

			return ($a->{self::SORT} < $b->{self::SORT}) ? -1 : 1;
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
			show_error(getError($udfResults));
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
		if ($jsonSchema->{self::TYPE} == 'checkbox')
		{
			$this->_renderCheckbox($jsonSchema, $widgetData);
		}
		// Textfield
		elseif ($jsonSchema->{self::TYPE} == 'textfield')
		{
			$this->_renderTextfield($jsonSchema, $widgetData);
		}
		// Textarea
		elseif ($jsonSchema->{self::TYPE} == 'textarea')
		{
			$this->_renderTextarea($jsonSchema, $widgetData);
		}
		// Date
		elseif ($jsonSchema->{self::TYPE} == 'date')
		{
			// To be done
		}
		// Dropdown
		elseif ($jsonSchema->{self::TYPE} == 'dropdown')
		{
			$this->_renderDropdown($jsonSchema, $widgetData);
		}
		// Multiple dropdown
		elseif ($jsonSchema->{self::TYPE} == 'multipledropdown')
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
		if (isset($widgetData[self::UDFS_ARG_NAME])
			&& isset($widgetData[self::UDFS_ARG_NAME][$jsonSchema->{self::NAME}]))
		{
			$widgetData[DropdownWidget::SELECTED_ELEMENT] = $widgetData[self::UDFS_ARG_NAME][$jsonSchema->{self::NAME}];
		}
		else
		{
			$widgetData[DropdownWidget::SELECTED_ELEMENT] = null;
		}

		$dropdownWidgetUDF = new DropdownWidgetUDF(self::WIDGET_NAME, $widgetData);
		$parameters = array();

		// If the list of values to show is an array
		if (isset($jsonSchema->{self::LIST_VALUES}->enum))
		{
			$parameters = $jsonSchema->{self::LIST_VALUES}->enum;
		}
		// If the list of values to show should be retrieved with a SQL statement
		elseif (isset($jsonSchema->{self::LIST_VALUES}->sql))
		{
			// UDFModel is loaded in method _loadUDF that is called before the current method
			$queryResult = $this->_ci->UDFModel->execReadOnlyQuery($jsonSchema->{self::LIST_VALUES}->sql);
			if (hasData($queryResult))
			{
				$parameters = getData($queryResult);
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
		$textareaUDF = new TextareaWidgetUDF(self::WIDGET_NAME, $widgetData);

		// Set text value if present in the DB
		if (isset($widgetData[self::UDFS_ARG_NAME])
			&& isset($widgetData[self::UDFS_ARG_NAME][$jsonSchema->{self::NAME}]))
		{
			$text = $widgetData[self::UDFS_ARG_NAME][$jsonSchema->{self::NAME}];
		}

		$textareaUDF->render($text);
	}

	/**
	 * Renders an input text element
	 */
	private function _renderTextfield($jsonSchema, &$widgetData)
	{
		$text = null; // text value
		$textareaUDF = new TextfieldWidgetUDF(self::WIDGET_NAME, $widgetData);

		// Set text value if present in the DB
		if (isset($widgetData[self::UDFS_ARG_NAME])
			&& isset($widgetData[self::UDFS_ARG_NAME][$jsonSchema->{self::NAME}]))
		{
			$text = $widgetData[self::UDFS_ARG_NAME][$jsonSchema->{self::NAME}];
		}

		$textareaUDF->render($text);
	}

	/**
	 * Renders a checkbox element
	 */
	private function _renderCheckbox($jsonSchema, &$widgetData)
	{
		// Set checkbox value if present in the DB
		if (isset($widgetData[self::UDFS_ARG_NAME])
			&& isset($widgetData[self::UDFS_ARG_NAME][$jsonSchema->{self::NAME}]))
		{
			$widgetData[CheckboxWidget::VALUE_FIELD] = $widgetData[self::UDFS_ARG_NAME][$jsonSchema->{self::NAME}];
		}
		else
		{
			$widgetData[CheckboxWidget::VALUE_FIELD] = CheckboxWidget::HTML_DEFAULT_VALUE;
		}

		$checkboxWidgetUDF = new CheckboxWidgetUDF(self::WIDGET_NAME, $widgetData);

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
		if (isset($jsonSchema->{self::LABEL})
			|| isset($jsonSchema->{self::TITLE})
			|| isset($jsonSchema->{self::PLACEHOLDER}))
		{
			// Loads phrases library
			$this->_ci->load->library('PhrasesLib');

			// If is set the label property in the json schema
			if (isset($jsonSchema->{self::LABEL}))
			{
				// Load the related phrase
				$tmpResult = $this->_ci->phraseslib->getPhrases(
					self::PHRASES_APP_NAME,
					getUserLanguage(),
					$jsonSchema->{self::LABEL},
					null,
					null,
					'no'
				);
				if (hasData($tmpResult))
				{
					$htmlParameters[HTMLWidget::LABEL] = getData($tmpResult)[0]->text;
				}
			}

			// If is set the title property in the json schema
			if (isset($jsonSchema->{self::TITLE}))
			{
				// Load the related phrase
				$tmpResult = $this->_ci->phraseslib->getPhrases(
					self::PHRASES_APP_NAME,
					getUserLanguage(),
					$jsonSchema->{self::TITLE},
					null,
					null,
					'no'
				);
				if (hasData($tmpResult))
				{
					$htmlParameters[HTMLWidget::TITLE] = getData($tmpResult)[0]->text;
				}
			}

			// If is set the placeholder property in the json schema
			if (isset($jsonSchema->{self::PLACEHOLDER}))
			{
				// Load the related phrase
				$tmpResult = $this->_ci->phraseslib->getPhrases(
					self::PHRASES_APP_NAME,
					getUserLanguage(),
					$jsonSchema->{self::PLACEHOLDER},
					null,
					null,
					'no'
				);
				if (hasData($tmpResult))
				{
					$htmlParameters[HTMLWidget::PLACEHOLDER] = getData($tmpResult)[0]->text;
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
		if (isset($jsonSchema->{self::VALIDATION}))
		{
			$jsonSchemaValidation =& $jsonSchema->{self::VALIDATION}; // Reference for a better code readability

			// Front-end regex
			if (isset($jsonSchemaValidation->{self::REGEX})
				&& is_array($jsonSchemaValidation->{self::REGEX}))
			{
				foreach ($jsonSchemaValidation->{self::REGEX} as $regex)
				{
					if ($regex->language === self::FE_REGEX_LANGUAGE)
					{
						$htmlParameters[HTMLWidget::REGEX] = $regex->expression;
					}
				}
			}

			// Required
			if (isset($jsonSchemaValidation->{self::REQUIRED}))
			{
				$htmlParameters[HTMLWidget::REQUIRED] = $jsonSchemaValidation->{self::REQUIRED};
			}

			// Min value
			if (isset($jsonSchemaValidation->{self::MIN_VALUE}))
			{
				$htmlParameters[HTMLWidget::MIN_VALUE] = $jsonSchemaValidation->{self::MIN_VALUE};
			}

			// Max value
			if (isset($jsonSchemaValidation->{self::MAX_VALUE}))
			{
				$htmlParameters[HTMLWidget::MAX_VALUE] = $jsonSchemaValidation->{self::MAX_VALUE};
			}

			// Min length
			if (isset($jsonSchemaValidation->{self::MIN_LENGTH}))
			{
				$htmlParameters[HTMLWidget::MIN_LENGTH] = $jsonSchemaValidation->{self::MIN_LENGTH};
			}

			// Max length
			if (isset($jsonSchemaValidation->{self::MAX_LENGTH}))
			{
				$htmlParameters[HTMLWidget::MAX_LENGTH] = $jsonSchemaValidation->{self::MAX_LENGTH};
			}
		}
	}
}

