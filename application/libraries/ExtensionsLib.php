<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Library to manage core extensions
 */
class ExtensionsLib
{
	const SQL_DIRECTORY = 'sql';
	const SQL_FILE_EXTENSION = '.sql';
	const FILE_INPUT_NAME = 'extension';
	const ARCHIVE_EXTENSIONS = array('.tgz', '.tbz2');
	const EXTENSION_JSON_NAME = 'extension.json';
	const UPLOAD_PATH = APPPATH.'tmp/';
	const EXTENSIONS_PATH = APPPATH.'extensions/';
	const EXTENSIONS_DIR_NAME = 'extensions';
	const SOFTLINK_TARGET_DIRECTORIES = array('config', 'controllers', 'helpers', 'hooks', 'libraries', 'models', 'views', 'widgets');

	private $_errorOccurred; //
	private $_currentInstalledExtensionVersion; //

	/**
	 * Class constructor
	 */
	public function __construct()
    {
		// Get code igniter instance
        $this->ci =& get_instance();

		// Loads message configurationx
		$this->ci->config->load('message');

		// Loads EPrintfLib
		$this->ci->load->library('EPrintfLib');

		// Loading models
		$this->ci->load->model('system/Extensions_model', 'ExtensionsModel');

		//
		$this->_errorOccurred = false;
		$this->_currentInstalledExtensionVersion = 0;
	}

	// -------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 *
	 */
	public function installExtension()
	{
		$extensionDB = null;
		$extensionJson = null;

		$this->_printInfo('WARNING!!! Please do not change page or stop this procedure before it is finished');

        $this->_loadUploadLibrary();

		$uploadData = $this->_uploadExtension();

		if ($uploadData != null)
		{
			$this->_extractExtension($uploadData->fullPath);

			if (!$this->_errorOccurred)
			{
				$extensionDB = $this->_loadPreviousInstallation($uploadData->extensionName);
			}

			if (!$this->_errorOccurred)
			{
				$this->_chkFSStructure($uploadData->extensionName);
			}

			if (!$this->_errorOccurred)
			{
				$extensionJson = $this->_chkExtensionJson($uploadData->extensionName, $extensionDB);
			}

			if ($extensionJson != null)
			{
				$this->_printStart('Proceding with the installation of the extension: '.$extensionJson->name);
				$this->_printEnd();

				$this->_cleanPreviousInstallation($extensionJson);

				$this->_installExtension($extensionJson);

				if (!$this->_errorOccurred)
				{
					$this->_loadSQLs(
						ExtensionsLib::UPLOAD_PATH.$extensionJson->name.'/'.ExtensionsLib::SQL_DIRECTORY,
						$extensionJson
					);
				}

				if (!$this->_errorOccurred)
				{
					$this->_moveExtension($extensionJson->name);
				}

				if (!$this->_errorOccurred)
				{
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

		if ($this->_errorOccurred === false)
		{
			if (!$this->_rrm($uploadData->fullPath))
			{
				$this->_printInfo('Error while cleaning upload directory. Not a blocking error');
			}

			$this->_printMessage('Extension correctly installed, you can safely close this page');
		}
		else
		{
			$this->_printError('There was a blocking error while installing/updating an extension, rolling back');

			$this->_rollback($uploadData, $extensionDB, $extensionJson);
		}
	}

	/**
	 *
	 */
	public function delExtension($extensionId)
	{
		$delExtension = false;

		$result = $this->ci->ExtensionsModel->load($extensionId);
		if (hasData($result))
		{
			$extensionName = $result->retval[0]->name;
			$this->_delSoftLinks($extensionName); // not to be checked, could fail if the extension is disabled
			$delExtension = $this->_rrm(ExtensionsLib::EXTENSIONS_PATH.$extensionName);

			$this->ci->ExtensionsModel->addSelect('extension_id');
			$result = $this->ci->ExtensionsModel->loadWhere(array('name' => $extensionName));
			if (hasData($result))
			{
				$extsArray = array();
				foreach ($result->retval as $key => $extension)
				{
					$result = $this->ci->ExtensionsModel->delete($extension->extension_id);
					if (isSuccess($result))
					{
						$delExtension = true;
					}
				}
			}
		}

		return $delExtension;
	}

	/**
	 *
	 */
	public function getInstalledExtensions()
	{
		return $this->ci->ExtensionsModel->getInstalledExtensions();
	}

	/**
	 *
	 */
	public function enableExtension($extensionId)
	{
		return $this->_toggleExtension($extensionId, true);
	}

	/**
	 *
	 */
	public function disableExtension($extensionId)
	{
		return $this->_toggleExtension($extensionId, false);
	}

	// -------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 *
	 */
	private function _loadUploadLibrary()
	{
		$this->ci->load->library(
			'upload',
			array(
				'upload_path' => ExtensionsLib::UPLOAD_PATH,
				'allowed_types' => '*',
				'overwrite' => true
			)
		);
	}

	/**
	 *
	 */
	private function _uploadExtension()
	{
		$_uploadExtension = null;

		$this->_printStart('Uploading extension');

		if ($this->ci->upload->do_upload(ExtensionsLib::FILE_INPUT_NAME))
		{
			$uploadData = $this->ci->upload->data();
			$uploadedFileExtension = '.'.pathinfo($uploadData['full_path'], PATHINFO_EXTENSION);
			if (!in_array($uploadedFileExtension, ExtensionsLib::ARCHIVE_EXTENSIONS))
			{
				$this->_printFailure('file extension must be tgz OR tbz2');

				if (isset($uploadData['full_path']) && file_exists($uploadData['full_path']))
				{
					$this->_rrm($uploadData['full_path']);
				}
			}
			else
			{
				$_uploadExtension = new stdClass();
				$_uploadExtension->extensionName = str_replace(ExtensionsLib::ARCHIVE_EXTENSIONS, '', $uploadData['file_name']);
				$_uploadExtension->fullPath = $uploadData['full_path'];
			}
		}
		else
		{
			$this->_printFailure($this->ci->upload->display_errors('', ''));
		}

		$this->_printSuccess($_uploadExtension != null);

		$this->_printEnd();

		return $_uploadExtension;
	}

	/**
	 *
	 */
	private function _extractExtension($uploadPath)
	{
		$this->_printStart('Extracting extension');

		try
		{
			$pd = new PharData($uploadPath);

			$pd->extractTo(ExtensionsLib::UPLOAD_PATH, null, true);
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
	 *
	 */
	private function _loadPreviousInstallation($extensionName)
	{
		$extensionDB = null;

		$this->_printStart('Loads any previous installation data');

		$this->ci->ExtensionsModel->addOrder('version', 'DESC');
		$this->ci->ExtensionsModel->addLimit(1);
		$result = $this->ci->ExtensionsModel->loadWhere(array('name' => $extensionName));
		if (isError($result))
		{
			$this->_errorOccurred = true;
			$this->_printFailure('data base error');
			var_dump($result);
		}
		else
		{
			if (hasData($result))
			{
				$extensionDB = $result->retval[0];
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
	 *
	 */
	private function _chkFSStructure($extensionName)
	{
		$this->_printStart('Checking extension file system structure');

		if (is_dir(ExtensionsLib::UPLOAD_PATH.$extensionName))
		{
			if (!file_exists(ExtensionsLib::UPLOAD_PATH.$extensionName.'/'.ExtensionsLib::EXTENSION_JSON_NAME))
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
	 *
	 */
	private function _chkExtensionJson($extensionName, $extensionDB)
	{
		$this->_printStart('Parsing and checking extension.json');

		$extensionJson = json_decode(
			file_get_contents(ExtensionsLib::UPLOAD_PATH.$extensionName.'/'.ExtensionsLib::EXTENSION_JSON_NAME)
		);

		if ($extensionJson != null && isset($extensionJson->name) && $extensionJson->name == $extensionName)
		{
			if (isset($extensionJson->version))
			{
				$extensionJson->currentInstalledVersion = 0;

				if ($extensionDB != null)
				{
					$extensionJson->extension_id = $extensionDB->extension_id; //
					$extensionJson->currentInstalledVersion = $extensionDB->version;

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
					else
					{
						$extensionJson = null;
						$this->_printFailure('downgrade must be performed manually');
					}
				}
				else
				{
					$this->_printMessage('Version of the uploaded extension: '.$extensionJson->version);
				}

				if ($extensionJson != null)
				{
					require_once('version.php');
					if (isset($extensionJson->core_version) && $extensionJson->core_version == $fhcomplete_version)
					{
						$this->_printMessage('Required core version: '.$extensionJson->core_version);
						$this->_printMessage('Current core version: '.$fhcomplete_version);

						if (isset($extensionJson->dependencies)
							&& is_array($extensionJson->dependencies)
							&& count($extensionJson->dependencies) > 0)
						{
							$result = $this->ci->ExtensionsModel->getDependencies($extensionJson->dependencies);
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
							else
							{
								$extensionJson = null;
								$this->_printFailure('dependencies are missing, install them to proceed');
							}
						}
						elseif (isset($extensionJson->dependencies) && !is_array($extensionJson->dependencies))
						{
							$extensionJson = null;
							$this->_printFailure('dependencies parameter must be an array');
						}
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
	 *
	 */
	private function _cleanPreviousInstallation($extensionJson)
	{
		$this->_printStart('Cleaning any previous installations in DB and file system');

		if (isset($extensionJson->extension_id))
		{
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
	 *
	 */
	private function _installExtension($extensionJson)
	{
		$this->_printStart('Adding new entry in the DB');

		$result = $this->ci->ExtensionsModel->insert(
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
	 * TODO
	 */
	private function _loadSQLs($pkgSQLsPath, $extensionJson)
	{
		$this->_printStart('Loading and executing SQL files');
		$this->_printInfo('WARNING: if this step will fail, the database and all the directories');
		$this->_printInfo('have to be clean manually before install again this extension');

		$startVersion = $extensionJson->currentInstalledVersion;

		if ($extensionJson->currentInstalledVersion < $extensionJson->version)
		{
			$startVersion++;
		}

		for ($sqlDir = $startVersion; $sqlDir <= $extensionJson->version; $sqlDir++)
		{
			if (($files = glob($pkgSQLsPath.'/'.$sqlDir.'/*'.ExtensionsLib::SQL_FILE_EXTENSION)) != false)
	        {
	            foreach ($files as $file)
	            {
	                $sql = file_get_contents($file);

					$this->_printMessage('Executing query:');
					$this->_printMessage($sql);

					if (!isSuccess($result = @$this->ci->ExtensionsModel->executeQuery($sql)))
					{
						$this->_errorOccurred = true;
						$this->_printFailure(' error occurred while executing the query');
						$this->_printInfo('Is not possible to rollback the DB changes, must be done manually');
						break;
					}
					else
					{
						$this->_printMessage('Query result:');
						var_dump($result->retval);
						$this->ci->eprintflib->printEOL();
					}
	            }
	        }
		}

		$this->_printSuccess(!$this->_errorOccurred);

		$this->_printEnd();
	}

	/**
	 *
	 */
	private function _moveExtension($extensionName)
	{
		$this->_printStart('Moving the upload extension from upload folder to extension folder');

		$this->_printMessage('Current extension directory: '.ExtensionsLib::UPLOAD_PATH.$extensionName);
		$this->_printMessage('Directory where it will be moved: '.ExtensionsLib::EXTENSIONS_PATH.$extensionName);

		if (rename(ExtensionsLib::UPLOAD_PATH.$extensionName.'/', ExtensionsLib::EXTENSIONS_PATH.$extensionName))
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
	 *
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
	 *
	 */
	private function _delSoftLinks($extensionName)
	{
		$_delSoftLinks = false;

		foreach (ExtensionsLib::SOFTLINK_TARGET_DIRECTORIES as $key => $targetDirectory)
		{
			if (file_exists(APPPATH.$targetDirectory.'/'.ExtensionsLib::EXTENSIONS_DIR_NAME.'/'.$extensionName))
			{
				$_delSoftLinks = unlink(APPPATH.$targetDirectory.'/'.ExtensionsLib::EXTENSIONS_DIR_NAME.'/'.$extensionName);
			}
		}

		return $_delSoftLinks;
	}

	/**
	 * Recursive remove
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

			if (!$this->_rrm($dir . DIRECTORY_SEPARATOR . $item))
			{
				return false;
			}
		}

		return rmdir($dir);
	}

	/**
	 *
	 */
	private function _addSoftLinks($extensionName)
	{
		$_addSoftLinks = false;
		$extensionPath = ExtensionsLib::EXTENSIONS_PATH.$extensionName.'/';

		foreach (ExtensionsLib::SOFTLINK_TARGET_DIRECTORIES as $key => $targetDirectory)
		{
			if (!file_exists(APPPATH.$targetDirectory.'/'.ExtensionsLib::EXTENSIONS_DIR_NAME.'/'.$extensionName))
			{
				if (!is_dir($extensionPath.$targetDirectory))
				{
					mkdir($extensionPath.$targetDirectory);
				}

				$_addSoftLinks = symlink(
					$extensionPath.$targetDirectory,
					APPPATH.$targetDirectory.'/'.ExtensionsLib::EXTENSIONS_DIR_NAME.'/'.$extensionName
				);
				if (!$_addSoftLinks)
				{
					break;
				}
			}
		}

		return $_addSoftLinks;
	}

	/**
	 *
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
			&& file_exists(ExtensionsLib::UPLOAD_PATH.$uploadData->extensionName))
		{
			$this->_rrm(ExtensionsLib::UPLOAD_PATH.$uploadData->extensionName);
		}

		if ($uploadData != null && isset($uploadData->extensionName) && $extensionDB == null)
		{
			$this->ci->ExtensionsModel->addOrder('version', 'DESC');
			$this->ci->ExtensionsModel->addLimit(1);
			$result = $this->ci->ExtensionsModel->loadWhere(array('name' => $uploadData->extensionName));
			if (hasData($result))
			{
				$this->_printMessage('Removing entries in the DB related to this extension');
				$this->delExtension($result->retval[0]->extension_id);
			}
		}
		else
		{
			if ($extensionJson != null && isset($extensionJson->extension_id))
			{
				$this->ci->ExtensionsModel->delete($extensionJson->extension_id);
			}
		}

		$this->_printMessage('Rollback finished');

		$this->_printEnd();
	}

	/**
	 *
	 */
	private function _toggleExtension($extensionId, $enabled)
	{
		$_toggleExtension = false;

		$result = $this->ci->ExtensionsModel->load($extensionId);
		if (hasData($result))
		{
			$extensionName = $result->retval[0]->name;

			if ($enabled === true)
			{
				$_toggleExtension = $this->_addSoftLinks($extensionName);
			}
			else
			{
				$_toggleExtension = $this->_delSoftLinks($extensionName);
			}

			if ($_toggleExtension)
			{
				$result = $this->ci->ExtensionsModel->update($extensionId, array('enabled' => $enabled));
				if (isSuccess($result))
				{
					$_toggleExtension = true;
				}
				else
				{
					$this->_delSoftLinks($extensionName);
				}
			}
		}

		return $_toggleExtension;
	}

	/**
	 *
	 */
	private function _printError($error)
	{
		$this->ci->eprintflib->printError($error);
	}

	/**
	 *
	 */
	private function _printFailure($error)
	{
		$this->_printError('Failed: '.$error);
	}

	/**
	 *
	 */
	private function _printMessage($message)
	{
		$this->ci->eprintflib->printMessage($message);
	}

	/**
	 *
	 */
	private function _printSuccess($cond)
	{
		if ($cond === true)
		{
			$this->_printMessage('Success!!!');
		}
	}

	/**
	 *
	 */
	private function _printInfo($info)
	{
		$this->ci->eprintflib->printInfo($info);
	}

	/**
	 *
	 */
	private function _printStart($startMessage)
	{
		$this->_printInfo('------------------------------------------------------------------------------------------');
		$this->_printMessage($startMessage);
	}

	/**
	 *
	 */
	private function _printEnd()
	{
		$this->_printInfo('------------------------------------------------------------------------------------------');
	}
}
