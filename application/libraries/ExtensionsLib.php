<?php
/**
 * Copyright (C) 2022 fhcomplete.org
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

use \stdClass as stdClass;

/**
 * Library to manage core extensions
 */
class ExtensionsLib
{
	const SQL_DIRECTORY = 'sql'; // directory where to retrieve SQL scripts
	const SQL_FILE_EXTENSION = '.sql'; // SQL scripts file extension

	const FILE_INPUT_NAME = 'extension'; // name of the HTTP parameter containing the archive data

	const EXTENSION_JSON_NAME = 'extension.json'; // file that contains extension data
	const EXTENSIONS_DIR_NAME = 'extensions'; // name of the directories where will be created the symlinks

	private $_ci;

	private $ARCHIVE_EXTENSIONS = array('.tgz', '.tbz2'); // accepted file extensions for an uploaded extension
	private $UPLOAD_PATH; // temporary directory to store the upload file and checks the archive
	private $EXTENSIONS_PATH; // directory where all the extensions are

	// Directories that are part of the extension archive
	private $SOFTLINK_TARGET_DIRECTORIES = array(
		APPPATH => array('config', 'components', 'controllers', 'helpers', 'hooks', 'libraries', 'models', 'views', 'widgets'),
		DOC_ROOT => array('public')
	);

	private $_errorOccurred; // boolean, true if an error occurred while installing an extension
	private $_currentInstalledExtensionVersion; // contains the version of the current installation of an extension

	/**
	 * Class constructor
	 */
	public function __construct()
	{
		$this->UPLOAD_PATH = APPPATH.'tmp/';
		$this->EXTENSIONS_PATH = APPPATH.'extensions/';
		// Get code igniter instance
		$this->_ci =& get_instance();

		// Loads message configurationx
		$this->_ci->config->load('message');

		// Loads EPrintfLib
		$this->_ci->load->library('EPrintfLib');

		// Loading models
		$this->_ci->load->model('system/Extensions_model', 'ExtensionsModel');

		// Set default values fot class properties
		$this->_errorOccurred = false;
		$this->_currentInstalledExtensionVersion = 0;
	}

	// -------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Main method to install an extension
	 * If no filename / Extensionname is provided the extension should be uploaded via webupload
	 *
	 * @param $extensionName string Name of Extension (optional)
	 * @param $filename Path to tgz Extension File (optional)
	 */
	public function installExtension($extensionName = null, $filename = null)
	{
		$extensionDB = null; // contains data from DB about an extension
		$extensionJson = null; // contains the extension.json data

		$this->_printInfo('WARNING!!! Please do not change page or stop this procedure before it is finished');

		if (!is_null($extensionName) && !is_null($filename))
		{
			$uploadData = new stdClass();
			$uploadData->fullPath = $filename;
			$uploadData->extensionName = $extensionName;
		}
		else
		{
			$this->_loadUploadLibrary(); // loads CI upload library
			$uploadData = $this->_uploadExtension(); // perform the upload of the file and returns info about it
		}

		if ($uploadData != null) // if no error occurred
		{
			$this->_extractExtension($uploadData->fullPath); // extract the archive of the uploaded extension

			if (!$this->_errorOccurred) // if no error occurred
			{
				// Retives data about any previous installation of this extension
				$extensionDB = $this->_loadPreviousInstallation($uploadData->extensionName);
			}

			if (!$this->_errorOccurred) // if no error occurred
			{
				// Checks the structure of the uploaded extension
				$this->_chkFSStructure($uploadData->extensionName);
			}

			if (!$this->_errorOccurred) // if no error occurred
			{
				// Checks file extension.json and returns its content
				$extensionJson = $this->_chkExtensionJson($uploadData->extensionName, $extensionDB);
			}

			if ($extensionJson != null) // if no error occurred
			{
				$this->_printStart('Proceding with the installation of the extension: '.$extensionJson->name);
				$this->_printEnd();

				$this->_cleanPreviousInstallation($extensionJson); // cleans any previous installation

				$this->_installExtension($extensionJson); // records extension data in DB

				if (!$this->_errorOccurred) // if no error occurred
				{
					// Loads and executes neede SQL scripts
					$this->_loadSQLs(
						$this->UPLOAD_PATH.$extensionJson->name.'/'.ExtensionsLib::SQL_DIRECTORY,
						$extensionJson
					);
				}

				if (!$this->_errorOccurred) // if no error occurred
				{
					// Move the extracted extension from the temporary directory to the extensions install directory
					$this->_moveExtension($extensionJson->name);
				}

				if (!$this->_errorOccurred) // if no error occurred
				{
					// Create the symlinks to the installed extension
					$this->_createSymLinks($extensionJson->name);
				}
			}
			else
			{
				$this->_errorOccurred = true;
			}
		}
		else
		{
			$this->_errorOccurred = true;
		}

		if ($this->_errorOccurred === false) // if no errors occurred
		{
			if (!$this->_rrm($uploadData->fullPath)) // removes uploaded file
			{
				$this->_printInfo('Error while cleaning upload directory. Not a blocking error');
			}

			$this->_printMessage('Extension correctly installed, you can safely close this page');
		}
		else
		{
			$this->_printError('There was a blocking error while installing/updating an extension, rolling back');

			$this->_rollback($uploadData, $extensionDB, $extensionJson); // rock & rollback!
		}
	}

	/**
	 * Delete an installed extension using the extension_id stored in the DB
	 */
	public function delExtension($extensionId)
	{
		$delExtension = false;

		// Loads data about this extension from the DB
		$result = $this->_ci->ExtensionsModel->load($extensionId);
		if (hasData($result)) // if something was found
		{
			$extensionName = $result->retval[0]->name; // extension name
			$this->_delSoftLinks($extensionName); // not to be checked, could fail if the extension is disabled
			// remove the extension from the extensions installation directory
			$delExtension = $this->_rrm($this->EXTENSIONS_PATH.$extensionName);

			// Select all the version of this extension
			$this->_ci->ExtensionsModel->addSelect('extension_id');
			$result = $this->_ci->ExtensionsModel->loadWhere(array('name' => $extensionName));
			// If something was found
			if (hasData($result))
			{
				// Loops on them
				foreach ($result->retval as $extension)
				{
					// Remove them all
					$result = $this->_ci->ExtensionsModel->delete($extension->extension_id);
					if (isSuccess($result)) $delExtension = true;
				}
			}
		}

		return $delExtension;
	}

	/**
	 * Retrieve the list of all the installed extensions
	 */
	public function getInstalledExtensions()
	{
		return $this->_ci->ExtensionsModel->getInstalledExtensions();
	}

	/**
	 * To enable an extension
	 */
	public function enableExtension($extensionId)
	{
		return $this->_toggleExtension($extensionId, true);
	}

	/**
	 * To disable an extension
	 */
	public function disableExtension($extensionId)
	{
		return $this->_toggleExtension($extensionId, false);
	}

	// -------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Loads the upload library of CI
	 */
	private function _loadUploadLibrary()
	{
		$this->_ci->load->library(
			'upload',
			array(
				'upload_path' => $this->UPLOAD_PATH,
				'allowed_types' => '*',
				'overwrite' => true
			)
		);
	}

	/**
	 * Perform the upload of an extension archive and returns its data
	 */
	private function _uploadExtension()
	{
		$_uploadExtension = null;

		$this->_printStart('Uploading extension');

		// If the upload was a success
		if ($this->_ci->upload->do_upload(ExtensionsLib::FILE_INPUT_NAME))
		{
			$uploadData = $this->_ci->upload->data(); // retrieves data about the uploaded file
			// Checks the file extension
			$uploadedFileExtension = '.'.pathinfo($uploadData['full_path'], PATHINFO_EXTENSION);
			if (!in_array($uploadedFileExtension, $this->ARCHIVE_EXTENSIONS))
			{
				$this->_printFailure('file extension must be tgz OR tbz2');

				// Remove the uploaded file
				if (isset($uploadData['full_path']) && file_exists($uploadData['full_path']))
				{
					$this->_rrm($uploadData['full_path']);
				}
			}
			else
			{
				// Returns the extension name and the full path of the uploaded file
				$_uploadExtension = new stdClass();
				$_uploadExtension->extensionName = str_replace($this->ARCHIVE_EXTENSIONS, '', $uploadData['file_name']);
				$_uploadExtension->fullPath = $uploadData['full_path'];
			}
		}
		else
		{
			$this->_printFailure($this->_ci->upload->display_errors('', ''));
		}

		$this->_printSuccess($_uploadExtension != null);

		$this->_printEnd();

		return $_uploadExtension;
	}

	/**
	 * To extract the extension archive
	 */
	private function _extractExtension($uploadPath)
	{
		$this->_printStart('Extracting extension');

		try
		{
			// Extracts the uploaded file
			$pd = new PharData($uploadPath);

			$pd->extractTo($this->UPLOAD_PATH, null, true);
		}
		catch (UnexpectedValueException $uva)
		{
			$this->_errorOccurred = true;
			$this->_printFailure('provided an invalid archive');
		}
		catch (PharException $pe)
		{
			$this->_errorOccurred = true;
			$this->_printFailure('generic error occurred, check logs');
		}

		$this->_printSuccess(!$this->_errorOccurred);

		$this->_printEnd();
	}

	/**
	 * Loads any previous installations of the given extension from DB
	 */
	private function _loadPreviousInstallation($extensionName)
	{
		$extensionDB = null;

		$this->_printStart('Loads any previous installation data');

		// Loads the last version of the previous installation of this extension
		$this->_ci->ExtensionsModel->addOrder('version', 'DESC');
		$this->_ci->ExtensionsModel->addLimit(1);
		$result = $this->_ci->ExtensionsModel->loadWhere(array('name' => $extensionName));
		if (isError($result))
		{
			$this->_errorOccurred = true;
			$this->_printFailure('data base error: '.$result->retval);
		}
		else
		{
			if (hasData($result)) // if found
			{
				$extensionDB = $result->retval[0]; // return it!
			}
			else
			{
				$this->_printMessage('not found');
			}
		}

		$this->_printSuccess(!$this->_errorOccurred);

		$this->_printEnd();

		return $extensionDB;
	}

	/**
	 * Checks the structure of the extension archive
	 */
	private function _chkFSStructure($extensionName)
	{
		$this->_printStart('Checking extension file system structure');

		// Checks if the root directory of this archive has the same name of the extension
		if (is_dir($this->UPLOAD_PATH.$extensionName))
		{
			// Checks if file extension.json exists inside the uploaded archive
			if (!file_exists($this->UPLOAD_PATH.$extensionName.'/'.ExtensionsLib::EXTENSION_JSON_NAME))
			{
				$this->_errorOccurred = true;
				$this->_printFailure('missing extension.json');
			}
		}
		else
		{
			$this->_errorOccurred = true;
			$this->_printFailure('the root directory of the archive must have the same extension name');
		}

		$this->_printSuccess(!$this->_errorOccurred);

		$this->_printEnd();
	}

	/**
	 * Checks if extension.json is correct
	 */
	private function _chkExtensionJson($extensionName, $extensionDB)
	{
		$this->_printStart('Parsing and checking extension.json');

		// Decodes extension.json
		$extensionJson = json_decode(
			file_get_contents($this->UPLOAD_PATH.$extensionName.'/'.ExtensionsLib::EXTENSION_JSON_NAME)
		);

		// Checks if the parameter name of the extension.json has the same value of the extension name
		if ($extensionJson != null && isset($extensionJson->name) && $extensionJson->name == $extensionName)
		{
			// Checks if the parameter version of the extension.json file exists
			if (isset($extensionJson->version))
			{
				$extensionJson->currentInstalledVersion = 0; // default current installed version of this extension

				if ($extensionDB != null) // if no previous installation was found in DB
				{
					$extensionJson->extension_id = $extensionDB->extension_id; // get the extension_id from DB
					$extensionJson->currentInstalledVersion = $extensionDB->version; // get the current installed version from DB

					$this->_printMessage('Extension already installed!');
					$this->_printMessage('Current version: '.$extensionDB->version);
					$this->_printMessage('Version of the uploaded extension: '.$extensionJson->version);

					if ($extensionJson->version == $extensionDB->version)
					{
						$this->_printMessage('Updating the same version!');
					}
					elseif ($extensionJson->version > $extensionDB->version)
					{
						$this->_printMessage('Updating to a new version!');
					}
					else // downgrade is not possible
					{
						$extensionJson = null;
						$this->_printFailure('downgrade must be performed manually');
					}
				}
				else
				{
					$this->_printMessage('Version of the uploaded extension: '.$extensionJson->version);
				}

				// If no errors occurred
				if ($extensionJson != null)
				{
					// Default value
					$fhcomplete_version = 0;

					require_once('version.php'); // get the core version

					// Checks if the required core version of the extension is the same of this system
					if (isset($extensionJson->core_version) && version_compare($extensionJson->core_version, $fhcomplete_version,'<='))
					{
						$this->_printMessage('Required core version: '.$extensionJson->core_version);
						$this->_printMessage('Current core version: '.$fhcomplete_version);

						// Checks parameter dependencies of the extension.json
						if (isset($extensionJson->dependencies)
							&& is_array($extensionJson->dependencies)
							&& count($extensionJson->dependencies) > 0)
						{
							// Gets the required dependencies
							$result = $this->_ci->ExtensionsModel->getDependencies($extensionJson->dependencies);
							// If they are matcheds
							if (hasData($result) && count($result->retval) == count($extensionJson->dependencies))
							{
								if (isset($extensionJson->dependencies))
								{
									$extensionJson->dependencies = str_replace('[', '{', json_encode($extensionJson->dependencies));
									$extensionJson->dependencies = str_replace(']', '}', $extensionJson->dependencies);

									$this->_printMessage('Required dependencies: '.$extensionJson->dependencies);
								}
								else
								{
									$extensionJson->dependencies = '';

									$this->_printMessage('No dependencies are required');
								}
							}
							else // Otherwise not to be installed
							{
								$extensionJson = null;
								$this->_printFailure('dependencies are missing, install them to proceed');
							}
						}
						// Malformed dependencies parameter
						elseif (isset($extensionJson->dependencies) && !is_array($extensionJson->dependencies))
						{
							$extensionJson = null;
							$this->_printFailure('dependencies parameter must be an array');
						}
						// No dependencies required
						elseif (!isset($extensionJson->dependencies))
						{
							$this->_printMessage('No dependencies are required');
						}
					}
					else
					{
						$extensionJson = null;
						$this->_printFailure('core_version parameter is missing or it is not equal to the versione of the core');
					}
				}
			}
			else
			{
				$extensionJson = null;
				$this->_printFailure('version is missing');
			}
		}
		else
		{
			$extensionJson = null;
			$this->_printFailure('name is missing or not equal to extension name');
		}

		$this->_printSuccess($extensionJson != null);

		$this->_printEnd();

		return $extensionJson;
	}

	/**
	 * Clean any previous installations of the given archive
	 */
	private function _cleanPreviousInstallation($extensionJson)
	{
		$this->_printStart('Cleaning any previous installations in DB and file system');

		// If a previous installation of this extension was found
		if (isset($extensionJson->extension_id))
		{
			// Off with their heads!
			if ($this->delExtension($extensionJson->extension_id))
			{
				$this->_printSuccess(true);
			}
			else
			{
				$this->_printFailure('please check logs');
			}
		}
		else
		{
			$this->_printMessage('No need to clean, no previous installations found');
		}

		$this->_printEnd();
	}

	/**
	 * Insert extension's data into the DB
	 */
	private function _installExtension($extensionJson)
	{
		$this->_printStart('Adding new entry in the DB');

		$result = $this->_ci->ExtensionsModel->insert(
			array(
				'name' => $extensionJson->name,
				'description' => isset($extensionJson->description) ? $extensionJson->description : null,
				'version' => $extensionJson->version,
				'license' => isset($extensionJson->license) ? $extensionJson->license : null,
				'url' => isset($extensionJson->url) ? $extensionJson->url : null,
				'core_version' => $extensionJson->core_version,
				'dependencies' => isset($extensionJson->dependencies) ? $extensionJson->dependencies : null
			)
		);
		if (isSuccess($result))
		{
			$this->_printSuccess(true);
		}
		else
		{
			$this->_errorOccurred = true;
			$this->_printFailure('error while saving extension into DB');
		}

		$this->_printEnd();
	}

	/**
	 * Loads all the SQL scripts from the extension archive and executes them
	 */
	private function _loadSQLs($pkgSQLsPath, $extensionJson)
	{
		$this->_printStart('Loading and executing SQL files');
		$this->_printInfo('WARNING: if this step will fail, the database and all the directories');
		$this->_printInfo('have to be clean manually before install again this extension');

		$startVersion = $extensionJson->currentInstalledVersion; // current installed version extension

		// If the current installed version extension is less then the uploaded extension
		if ($extensionJson->currentInstalledVersion < $extensionJson->version)
		{
			$startVersion++; // +1
		}

		// Loops through the versions
		for ($sqlDir = $startVersion; $sqlDir <= $extensionJson->version; $sqlDir++)
		{
			// If a directory with the same value of the version is present in the sql scripts directory
			$files = glob($pkgSQLsPath.'/'.$sqlDir.'/*'.ExtensionsLib::SQL_FILE_EXTENSION);
			if ($files != false)
			{
				// Loads every sql files
				foreach ($files as $file)
				{
					$sql = file_get_contents($file); // gets the entire content of the file

					$this->_printMessage('Executing query:');
					$this->_printMessage($sql);

					// Try to execute that
					$resultQuery = @$this->_ci->ExtensionsModel->executeQuery($sql);

					// If _not_ a success
					if (!isSuccess($resultQuery))
					{
						$this->_errorOccurred = true;
						$this->_printFailure(' error occurred while executing the query');
						$this->_printInfo('Is not possible to rollback the DB changes, must be done manually');
						break;
					}
					else
					{
						$this->_printMessage('Query result:');
						var_dump(getData($resultQuery)); // KEEP IT!!!
						$this->_ci->eprintflib->printEOL();
					}
				}
			}
		}

		$this->_printSuccess(!$this->_errorOccurred);

		$this->_printEnd();
	}

	/**
	 * Move the extension extracted archive from the temporary directory to the extensions install directory
	 */
	private function _moveExtension($extensionName)
	{
		$this->_printStart('Moving the upload extension from upload folder to extension folder');

		$this->_printMessage('Current extension directory: '.$this->UPLOAD_PATH.$extensionName);
		$this->_printMessage('Directory where it will be moved: '.$this->EXTENSIONS_PATH.$extensionName);

		if (rename($this->UPLOAD_PATH.$extensionName.'/', $this->EXTENSIONS_PATH.$extensionName))
		{
			$this->_printSuccess(true);
		}
		else
		{
			$this->_errorOccurred = true;
			$this->_printFailure('error while moving');
		}

		$this->_printEnd();
	}

	/**
	 * Creates the symlinks to the installed extension
	 * Wrapper method to check the result of the operation and to print out info
	 */
	private function _createSymLinks($extensionName)
	{
		$this->_printStart('Creating symlinks');

		if ($this->_addSoftLinks($extensionName))
		{
			$this->_printSuccess(true);
		}
		else
		{
			$this->_errorOccurred = true;
			$this->_printFailure('error while creating sym links');
		}

		$this->_printEnd();
	}

	/**
	 * Remove all the symlinks to the installed extension
	 */
	private function _delSoftLinks($extensionName)
	{
		$_delSoftLinks = false;

		foreach ($this->SOFTLINK_TARGET_DIRECTORIES as $rootPath => $targetDirectories)
		{
			foreach ($targetDirectories as $targetDirectory)
			{
				if (file_exists($rootPath.$targetDirectory.'/'.ExtensionsLib::EXTENSIONS_DIR_NAME.'/'.$extensionName))
				{
					$_delSoftLinks = unlink($rootPath.$targetDirectory.'/'.ExtensionsLib::EXTENSIONS_DIR_NAME.'/'.$extensionName);
				}
			}
		}

		return $_delSoftLinks;
	}

	/**
	 * Recursive remove of a file or a directory
	 */
	private function _rrm($dir)
	{
		if (!file_exists($dir))
		{
			return true;
		}

		if (!is_dir($dir))
		{
			return unlink($dir);
		}

		foreach (scandir($dir) as $item)
		{
			if ($item == '.' || $item == '..')
			{
				continue;
			}

			if (!$this->_rrm($dir.DIRECTORY_SEPARATOR.$item))
			{
				return false;
			}
		}

		return rmdir($dir);
	}

	/**
	 * Creates the symlinks to the installed extension
	 */
	private function _addSoftLinks($extensionName)
	{
		$_addSoftLinks = false;
		$extensionPath = $this->EXTENSIONS_PATH.$extensionName.'/';

		// For every target directory
		foreach ($this->SOFTLINK_TARGET_DIRECTORIES as $rootPath => $targetDirectories)
		{
			foreach ($targetDirectories as $targetDirectory)
			{
				// If destination of the symlink does not exist
				if (!file_exists($rootPath.$targetDirectory.'/'.ExtensionsLib::EXTENSIONS_DIR_NAME.'/'.$extensionName))
				{
					// If the target directory does not exist than creates that
					if (!is_dir($extensionPath.$targetDirectory))
					{
						mkdir($extensionPath.$targetDirectory);
					}

					// Create the symlink
					$_addSoftLinks = symlink(
						$extensionPath.$targetDirectory,
						$rootPath.$targetDirectory.'/'.ExtensionsLib::EXTENSIONS_DIR_NAME.'/'.$extensionName
					);
					if (!$_addSoftLinks)
					{
						log_message('error', 'Failed to create Symlink to '.$extensionPath.$targetDirectory);
						break;
					}
				}
			}
		}

		return $_addSoftLinks;
	}

	/**
	 * To rollback an extension installation
	 * It will be removed all the information about the given extension from DB and file system
	 */
	private function _rollback($uploadData, $extensionDB, $extensionJson)
	{
		$this->_printStart('Rolling back the installation');

		$this->_printMessage('Removing the uploaded file from upload directory');
		if ($uploadData != null && isset($uploadData->fullPath) && file_exists($uploadData->fullPath))
		{
			$this->_rrm($uploadData->fullPath);
		}

		$this->_printMessage('Removing the extracted data from the upload directory');
		if ($uploadData != null
			&& isset($uploadData->extensionName)
			&& file_exists($this->UPLOAD_PATH.$uploadData->extensionName))
		{
			$this->_rrm($this->UPLOAD_PATH.$uploadData->extensionName);
		}

		// If the upload of the file is a success and the extension name is present and no previous installation were found
		if ($uploadData != null && isset($uploadData->extensionName) && $extensionDB == null)
		{
			// Loads all the previous installations of this extension
			$this->_ci->ExtensionsModel->addOrder('version', 'DESC');
			$this->_ci->ExtensionsModel->addLimit(1);
			$result = $this->_ci->ExtensionsModel->loadWhere(array('name' => $uploadData->extensionName));
			if (hasData($result)) // if found
			{
				// Remove them all from file system and DB
				$this->_printMessage('Removing entries in the DB related to this extension and from extensions directory');
				$this->delExtension($result->retval[0]->extension_id);
			}
		}
		else // otherwise
		{
			// Remove them all only from DB
			if ($extensionJson != null && isset($extensionJson->extension_id))
			{
				$this->_ci->ExtensionsModel->delete($extensionJson->extension_id);
			}
		}

		$this->_printMessage('Rollback finished');

		$this->_printEnd();
	}

	/**
	 * To enable/disable an extension
	 */
	private function _toggleExtension($extensionId, $enabled)
	{
		$_toggleExtension = false;

		// Loads data from DB about the given extension
		$result = $this->_ci->ExtensionsModel->load($extensionId);
		if (hasData($result))
		{
			$extensionName = $result->retval[0]->name; // extension name

			// If to be enabled
			if ($enabled === true)
			{
				// Add the symlinks
				$_toggleExtension = $this->_addSoftLinks($extensionName);
			}
			else // If to be disabled
			{
				// Remove all the symlinks
				$_toggleExtension = $this->_delSoftLinks($extensionName);
			}

			if ($_toggleExtension) // if is a success
			{
				// Updates DB
				$result = $this->_ci->ExtensionsModel->update($extensionId, array('enabled' => $enabled));
				if (isSuccess($result))
				{
					$_toggleExtension = true;
				}
				else // if DB update fails remove symlinks from file system
				{
					$this->_delSoftLinks($extensionName);
				}
			}
		}

		return $_toggleExtension;
	}

	/**
	 * Wrapper method to print a generic error
	 */
	private function _printError($error)
	{
		$this->_ci->eprintflib->printError($error);
	}

	/**
	 * Wrapper method to print an error
	 */
	private function _printFailure($error)
	{
		$this->_printError('Failed: '.$error);
	}

	/**
	 * Wrapper method to print a generic message
	 */
	private function _printMessage($message)
	{
		$this->_ci->eprintflib->printMessage($message);
	}

	/**
	 * Wrapper method to print a success
	 */
	private function _printSuccess($cond)
	{
		if ($cond === true)
		{
			$this->_printMessage('Success!!!');
		}
	}

	/**
	 * Wrapper method to print info
	 */
	private function _printInfo($info)
	{
		$this->_ci->eprintflib->printInfo($info);
	}

	/**
	 * Wrapper method to print a start message
	 */
	private function _printStart($startMessage)
	{
		$this->_printInfo('------------------------------------------------------------------------------------------');
		$this->_printMessage($startMessage);
	}

	/**
	 * Wrapper method to print an end message
	 */
	private function _printEnd()
	{
		$this->_printInfo('------------------------------------------------------------------------------------------');
	}
}
