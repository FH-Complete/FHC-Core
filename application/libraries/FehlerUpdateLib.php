<?php
/**
 * Copyright (C) 2025 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

if (! defined('BASEPATH')) exit('No direct script access allowed');

class FehlerUpdateLib
{
	// Who adds phrases into the database
	const UPSERT_BY = 'FehlerUpdate';

	const CONFIG_DIRECTORY = 'config';
	const CONFIG_FEHLER_NAME = 'fehler';
	const CONFIG_FEHLER_INDEX = 'fehler';

	const TYPE_STRING = 'string';
	const TYPE_ARRAY = 'array';

	//~ // Array elements names
	const FEHLERCODE = 'fehlercode';
	const FEHLER_KURZBZ = 'fehler_kurzbz';
	const FEHLERTEXT = 'fehlertext';
	const FEHLERTYP_KURZBZ = 'fehlertyp_kurzbz';
	const APP = 'app';
	const FEHLERCODE_EXTERN = 'fehlercode_extern';

	// structure of a fehler
	// type default: string
	const FEHLER_ATTRIBUTES = [
		self::FEHLERCODE => ['required' => true],
		self::FEHLER_KURZBZ  => ['required' => false],
		self::FEHLERTEXT => ['required' => true, 'updateable' => false],
		self::FEHLERTYP_KURZBZ => ['required' => false, 'updateable' => true],
		self::APP => ['required' => true, 'types' => [self::TYPE_STRING, self::TYPE_ARRAY]],
		self::FEHLERCODE_EXTERN => ['required' => false]
	];

	private $_ci; // Code igniter instance
	private $_updateHistory = [];

	/**
	 * Loads parser library
	 */
	public function __construct()
	{
		$this->_ci =& get_instance();

		// Loads EPrintfLib
		$this->_ci->load->library('EPrintfLib');

		// Loads the Models
		$this->_ci->load->model('system/Fehler_model', 'FehlerModel');
		$this->_ci->load->model('system/App_model', 'AppModel');

		// Loads extensions lib
		$this->_ci->load->library('ExtensionsLib');
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods


	/**
	 * Install all possible fehler, from core and extensions
	 */
	public function installAll()
	{
		$this->installFromCore();

		// load fehler entries of extensions
		$extensions = $this->_ci->extensionslib->getInstalledExtensions();

		if (hasData($extensions))
		{
			$extensionArray = array();

			$extensionsData = getData($extensions);

			foreach ($extensionsData as $ext)
			{
				$this->installFrom($ext->name);
			}
		}
	}

	/**
	 * Install fehler from the core
	 */
	public function installFromCore()
	{
		$this->_installFehler();
	}

	/**
	 * Install fehler from the given path
	 */
	public function installFrom($extensionName)
	{
		if (!isset($extensionName))
		{
			$this->_ci->eprintflib->printError('Extension name missing!');
			return;
		}

		$this->_installFehler(ExtensionsLib::EXTENSIONS_DIR_NAME.'/'.$extensionName.'/'.FehlerUpdateLib::CONFIG_FEHLER_NAME);

	}

	// -----------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Install fehler from the given directory
	 */
	private function _installFehler($fehlerConfigDirectory = null)
	{
		// check that fehler config file exists
		$configDir = isset($fehlerConfigDirectory) ? $fehlerConfigDirectory : self::CONFIG_FEHLER_NAME;

		$configFilename = APPPATH.self::CONFIG_DIRECTORY.'/'.$configDir.'.php';

		if (!file_exists($configFilename))
		{
			return;
		}

		// Load Fehler Entries
		$this->_ci->load->config($configDir);
		$configArray = $this->_ci->config->item(self::CONFIG_FEHLER_INDEX);

		if (!isset($configArray) || !is_array($configArray)) // check if fehler config entries could be loaded
		{
			$this->_ci->eprintflib->printError(
				'Fehler config array could not be loaded, directory '.$configDir.' index '.self::CONFIG_FEHLER_INDEX
			);
			return;
		}

		$this->_ci->eprintflib->printInfo('------------------------------------------------------------------------------------------');
		$this->_ci->eprintflib->printInfo('Fehler installation started, directory '.$configDir);

		foreach ($configArray as $idx => $configEntry)
		{
			// create fehler from config entry
			$createFehlerResult = $this->_createFehlerFromEntry($configEntry);

			// write error if creation failed
			if (isError($createFehlerResult))
			{
				$this->_ci->eprintflib->printError(
					getError($createFehlerResult).', directory '.$configDir.', index '.$idx
				);
			}
			elseif (hasData($createFehlerResult))
			{
				// add fehler to db
				$addFehlerResult = $this->_updateFehler(getData($createFehlerResult));

				if (isError($addFehlerResult))
				{
					$this->_ci->eprintflib->printError(
						getError($addFehlerResult).', directory'.$configDir.', index '.$idx
					);
				}
			}
		}

		$this->_ci->eprintflib->printInfo('Fehler installation ended');
		$this->_ci->eprintflib->printInfo('------------------------------------------------------------------------------------------');
	}

	/**
	 * Add a new fehler to the database
	 */
	private function _updateFehler($fehler)
	{
		$fehlerReferences = $fehler['references'];
		$fehler = $fehler['fehler'];

		// Checks if the fehler already exists in the database
		$this->_ci->FehlerModel->db->where(self::FEHLERCODE.' = ', $fehler[self::FEHLERCODE]);
		if (isset($fehler[self::FEHLER_KURZBZ]) && !isEmptyString($fehler[self::FEHLER_KURZBZ]))
			$this->_ci->FehlerModel->db->or_where(self::FEHLER_KURZBZ.' = ', $fehler[self::FEHLER_KURZBZ]);
		$fehlerResult = $this->_ci->FehlerModel->load();

		// If an error occurred then return the error itself
		if (isError($fehlerResult)) return $fehlerResult;

		$updateRes = null;

		// if fehler has been found
		if (hasData($fehlerResult))
		{
			$foundFehler = getData($fehlerResult)[0];

			// check if fehlercode - fehler kurzbz combination is correct
			if ($foundFehler->{self::FEHLERCODE} != $fehler[self::FEHLERCODE]
				|| $foundFehler->{self::FEHLER_KURZBZ} != ($fehler[self::FEHLER_KURZBZ] ?? null)
			) {
				return error("Wrong fehlercode - fehler kurzbz combination: ".$fehler[self::FEHLERCODE].", ".$fehler[self::FEHLER_KURZBZ]);
			}

			$this->_ci->eprintflib->printMessage(
				"Fehler ".$fehler[self::FEHLERCODE]
					.(isset($fehler[self::FEHLER_KURZBZ]) ? " (".$fehler[self::FEHLER_KURZBZ].")" : "")
					." already exists in database"
			);

			$updateArr = [];

			// update fehler, if needed
			foreach (self::FEHLER_ATTRIBUTES as $attributeName => $attributeInfo)
			{
				// set attributes to be updated
				if (isset($attributeInfo['updateable']) && $attributeInfo['updateable'] && $foundFehler->{$attributeName} != $fehler[$attributeName])
				{
					$updateArr[$attributeName] = $fehler[$attributeName];

					if (isset($this->_updateHistory[$foundFehler->{self::FEHLERCODE}][$attributeName])
						&& $this->_updateHistory[$foundFehler->{self::FEHLERCODE}][$attributeName] != $fehler[$attributeName]
					) {
						return error("Conflicting update values for attribute ".$attributeName.", fehler ".$foundFehler->{self::FEHLERCODE});
					}
					$this->_updateHistory[$foundFehler->{self::FEHLERCODE}][$attributeName] = $fehler[$attributeName];
				}
			}

			if (!isEmptyArray($updateArr))
			{
				$updateRes = $this->_ci->FehlerModel->update(
					[self::FEHLERCODE => $foundFehler->{self::FEHLERCODE}],
					array_merge($updateArr, ['updateamum' => 'NOW()', 'updatevon' => self::UPSERT_BY])
				);
				if (isError($updateRes)) return $updateRes;

				$this->_ci->eprintflib->printMessage(
					"Fehler ".$fehler[self::FEHLERCODE].(isset($fehler[self::FEHLER_KURZBZ]) ? " (".$fehler[self::FEHLER_KURZBZ].")" : "")." updated"
				);
			}
		}
		else
		{
			// no fehler has been found

			// then add the fehler to the database
			$updateRes = $this->_ci->FehlerModel->insert(
				array_merge($fehler, ['insertamum' => 'NOW()', 'insertvon' => self::UPSERT_BY])
			);

			// If an error occurred then return the error itself
			if (isError($updateRes)) return $updateRes;

			// Prints info about the new added fehler
			$this->_ci->eprintflib->printMessage(
				sprintf(
					'A new fehler has been added into the database: '.
					'fehlercode => %s | fehler_kurzbz => %s | fehlertyp => %s',
					$fehler[self::FEHLERCODE],
					$fehler[self::FEHLER_KURZBZ],
					$fehler[self::FEHLERTYP_KURZBZ]
				)
			);
		}

		// handle references
		if (isset($fehlerReferences[self::APP]))
		{
			$this->_ci->load->model('system/FehlerApp_model', 'FehlerAppModel');
			$apps = $fehlerReferences[self::APP];

			// load all assigned apps
			$this->_ci->FehlerAppModel->addSelect(self::APP);
			$fehlerAppRes = $this->_ci->FehlerAppModel->loadWhere([self::FEHLERCODE => $fehler[self::FEHLERCODE]]);

			$fehlerApps = hasData($fehlerAppRes) ? array_column(getData($fehlerAppRes), self::APP) : [];

			$appsToInsert = array_diff($apps, $fehlerApps);

			foreach ($appsToInsert as $app)
			{
				// check if app exists in db
				$fehlerAppsInsertRes = $this->_ci->FehlerAppModel->insert(
					[self::FEHLERCODE => $fehler[self::FEHLERCODE], self::APP => $app, 'insertamum' => 'NOW()', 'insertvon' => self::UPSERT_BY]
				);

				if (isError($fehlerAppsInsertRes)) return $fehlerAppsInsertRes;

				$this->_ci->eprintflib->printMessage(
					"Added app ".$app." to fehler ".$fehler[self::FEHLERCODE]
				);
			}
		}

		// If here then no blocking errors occurred
		return success();
	}

	/**
	 * Create an array with fehler data from config entry
	 */
	private function _createFehlerFromEntry($configEntry)
	{
		$fehler = ['fehler' => [], 'references' => []];
		foreach (self::FEHLER_ATTRIBUTES as $attributeName => $attributeInfo)
		{
			$required = isset($attributeInfo['required']) && $attributeInfo['required'];
			if (!isset($configEntry[$attributeName]))
			{
				if ($required) return error('attribute'.$attributeName.' is missing');
				continue;
			}

			$attributeValue = $configEntry[$attributeName];
			$validType = false;
			if (isset($attributeInfo['types']) && is_array($attributeInfo['types']))
			{
				foreach ($attributeInfo['types'] as $type)
				{
					switch ($type)
					{
						case self::TYPE_STRING:
							if (is_string($attributeValue) || is_null($attributeValue)) $validType = true;
							break;
						case self::TYPE_ARRAY:
							if (is_array($attributeValue) && !($required && isEmptyArray($attributeValue))) $validType = true;
							break;
						//~ default:
							//~ if (is_string($configEntry[$attributeName]) || is_null($configEntry[$attributeName])) $validType = true;
					}
				}
			}
			else
			{
				$validType = is_string($attributeValue) || is_null($attributeValue);
			}

			if (!$validType)
			{
				return error('attribute'.$attributeName.' has invalid type');
			}

			if ($attributeName == self::APP)
			{
				if (is_string($attributeValue)) $attributeValue = [$attributeValue];

				foreach ($attributeValue as $app)
				{
					// check if app exists in db
					$this->_ci->AppModel->addSelect('1');
					$appRes = $this->_ci->AppModel->loadWhere(['app' => $app]);

					if (!hasData($appRes)) return error("App ".$app." does not exist");
				}
			}

			if (isset($attributeInfo['types']) && is_array($attributeInfo['types']) && in_array(self::TYPE_ARRAY, $attributeInfo['types']))
			{
				$fehler['references'][$attributeName] = $attributeValue;
			}
			else
			{
				$fehler['fehler'][$attributeName] = $attributeValue;
			}

		}
		return success($fehler);
	}
}
