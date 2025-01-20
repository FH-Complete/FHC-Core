<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Model to work with the filesystem, it represents a directory
 * It could be extended or could be used directly to work on the given path
 */
class FS_Model extends CI_Model
{
	const READ_MODE = 'r';
	const READ_WRITE_MODE = 'w+';
	const READ_APPEND_MODE = 'a+';
	const BLOCK_SIZE = 8192;
	const META_URI = 'uri';

	private $_path; // Directory where this model can operate

	/**
	 * Set properties
	 */
	public function __construct($path)
	{
		parent::__construct();

		$this->_path = $path;
	}

	// ------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Opens a file in read mode and returns its file handle
	 */
	public function openRead($filename)
	{
		return $this->_open($filename, self::READ_MODE);
	}

	/**
	 * Opens a file in read and write mode and returns its file handle
	 * If the file does not exist then it is created
	 */
	public function openReadWrite($filename)
	{
		return $this->_open($filename, self::READ_WRITE_MODE);
	}

	/**
	 * Opens a file in read and append mode and returns its file handle
	 * If the file does not exist then it is created
	 */
	public function openReadAppend($filename)
	{
		return $this->_open($filename, self::READ_APPEND_MODE);
	}

	/**
	 * Closes a file handle
	 */
	public function close($fileHandle)
	{
		return fclose($fileHandle) === true ? success() : error('Error while closing the file handler');
	}

	/**
	 * Reads a block of bytes from the given file
	 * Returns a success that contains the block
	 * On failure returns an error
	 */
	public function readBlock($fileHandle)
	{
		// Reads a block of bytes from the file
		$block = fread($fileHandle, self::BLOCK_SIZE);

		// If an error occurred
		if ($block === false)
		{
			// Prepare the error message
			$errorMsg = 'An error occurred while reading a file';

			// Tries to get the file name and to concatenate it to the error message
			$fileMetaData = stream_get_meta_data($fileHandle);
			if (isset($fileMetaData[self::META_URI])) $errorMsg .= ': '.$fileMetaData[self::META_URI];

			return error($errorMsg); // returns the error
		}

		return success($block); // return success if everything was fine
	}

	/**
	 * Writes/appends (depending on how the file was opened) a content into a file
	 * Returns a success that contains the written number of bytes
	 * On failure returns an error
	 */
	public function write($fileHandle, $content)
	{
		// Writes the provided content to the file
		$writeResult = fwrite($fileHandle, $content);

		// If an error occurred
		if ($writeResult === false)
		{
			$errorMsg = 'An error occurred while writing a file';

			// Tries to get the file name and to concatenate it to the error message
			$fileMetaData = stream_get_meta_data($fileHandle);
			if (isset($fileMetaData[self::META_URI])) $errorMsg .= ': '.$fileMetaData[self::META_URI];

			return error($errorMsg); // returns the error
		}

		return success($writeResult);
	}

	/**
	 * Removes a given file
	 */
	public function remove($filename)
	{
		// Check if the property _path represents a valid directory
		$checkResult = $this->_checkPath();

		if (isError($checkResult)) return $checkResult; // If not then return the error

		// Check filename
		if (isEmptyString($filename)) return error('The given filename is not valid');

		// remove file
		if (unlink($this->_path.DIRECTORY_SEPARATOR.$filename) === true)
		{
			return success();
		}
		else
		{
			return error('An error occurred while removing a file');
		}
	}

	// ------------------------------------------------------------------------------------------------------------------
	// Old public methods that work with the base64 encoding, not to be used!

	/**
	 * Read data from the given file and encode its content to base64
	 */
	public function readBase64($filename)
	{
		// Open the file in read mode
		$openReadResult = $this->openRead($filename);
		if (isError($openReadResult)) return $openReadResult; // if an error occurred then return it

		$fileContent = ''; // to store the file content
		$fileHandle = getData($openReadResult); // get the file handle

		// While the end of the file is not reached and the read does not fail
		while (!feof($fileHandle) && isSuccess($readBlockResult = $this->readBlock($fileHandle)))
		{
			// Concatenate the content of the file
			$fileContent .= getData($readBlockResult);
		}

		// If an error occurred while reading then return it
		if (isError($readBlockResult)) return $readBlockResult;

		// Close the file handler
		$closeResult = $this->close($fileHandle);
		if (isError($closeResult)) return $closeResult; // if it fails then return the error

		// If everything was fine encode the file content into base64 and return it as a success
		return success(base64_encode($fileContent));
	}

	/**
	 * Writes the given content into the given file. The content is base64 encoded
	 */
	public function writeBase64($filename, $content)
	{
		// Open the file in read and write mode
		$openWriteResult = $this->openReadWrite($filename);
		if (isError($openWriteResult)) return $openWriteResult; // if an error occurred then return it

		$fileHandle = getData($openWriteResult); // get the file handle

		// Writes the given base64 encoded content into to given file
		$writeResult = $this->write($fileHandle, base64_decode($content));
		// If an error occurred while writing then return it
		if (isError($writeResult)) return $writeResult;

		// Close the file handler
		$closeResult = $this->close($fileHandle);
		if (isError($closeResult)) return $closeResult; // if it fails then return the error

		// If everything was fine
		return success();
	}

	/**
	 * Appends the given content into the given file. The content is base64 encoded
	 */
	public function appendBase64($filename, $content)
	{
		// Open the file in read and append mode
		$openWriteResult = $this->openReadAppend($filename);
		if (isError($openWriteResult)) return $openWriteResult; // if an error occurred then return it

		$fileHandle = getData($openWriteResult); // get the file handle

		// Writes the given base64 encoded content into to given file
		$writeResult = $this->write($fileHandle, base64_decode($content));
		// If an error occurred while writing then return it
		if (isError($writeResult)) return $writeResult;

		// Close the file handler
		$closeResult = $this->close($fileHandle);
		if (isError($closeResult)) return $closeResult; // if it fails then return the error

		// If everything was fine
		return success();
	}

	/**
	 * Delete data from file system
	 * NOTE: it does not work with the base64 encoding but it has been kept for retro compatibility
	 *
	 * @param   string $id  Primary Key for DELETE
	 * @return  array
	 */
	public function removeBase64($filename)
	{
		// Check Class-Attributes
		if (is_null($this->_path)) return error('The given _path in not valid', EXIT_ERROR);

		// Check method parameters
		if (is_null($filename)) return error('The given filename is not valid', EXIT_ERROR);

		if (unlink($this->_path.DIRECTORY_SEPARATOR.$filename) === true)
		{
			return success();
		}
		else
		{
			return error('An error occurred while removing a file', EXIT_ERROR);
		}
	}

	/**
	 * Rename a file
	 * NOTE: it does not work with the base64 encoding but it has been kept for retro compatibility
	 *
	 * @param   string $id  Primary Key for DELETE
	 * @return  array
	 */
	public function renameBase64($filename, $newFilename)
	{
		// Check Class-Attributes
		if (is_null($this->_path)) return error('The given _path in not valid', EXIT_ERROR);

		// Check method parameters
		if (is_null($filename)) return error('The given filename is not valid', EXIT_ERROR);
		if (is_null($newFilename)) return error('The given new filename is not valid', EXIT_ERROR);

		if (rename($this->_path.DIRECTORY_SEPARATOR.$filename, $this->_path.DIRECTORY_SEPARATOR.$newFilename) === true)
		{
			return success();
		}
		else
		{
			return error('An error occurred while renaming a file', EXIT_ERROR);
		}
	}

	// ------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Checks if the given $this->_path is a valid directory
	 */
	private function _checkPath()
	{
		// If _path...
		if (!isEmptyString($this->_path) // ...is a not empty string...
			&& file_exists($this->_path) && is_dir($this->_path)) // ...exists on the file system and it is a directory...
		{
			return success(); // return a success
		}

		// If not a valid path return an error
		return error('The given path is not valid: '.$this->_path);
	}

	/**
	 * Open a file using the provided mode
	 * It returns a file handle
	 * Or write and append if the file does not exist then creates it
	 */
	private function _open($filename, $mode)
	{
		// Check if the property _path represents a valid directory
		$checkResult = $this->_checkPath();
		if (isError($checkResult)) return $checkResult; // If not then return the error

		// Full file path: path + filename
		$fileFullPath = $this->_path.DIRECTORY_SEPARATOR.$filename;

		// If needed then check if the file exists and really it is a file
		if ($mode == self::READ_MODE && (!file_exists($fileFullPath) || !is_file($fileFullPath)))
		{
			return error('Trying to read a not existing file');
		}

		// If needed then check if it is possible to read from the path and the file
		if ($mode == self::READ_MODE && (!is_readable($this->_path) || !is_readable($fileFullPath)))
		{
			return error('The given path or filename are not readable: '.$fileFullPath);
		}

		// If needed then check if the path and the filename are writable
		if (($mode == self::READ_WRITE_MODE || $mode == self::READ_APPEND_MODE)
			&& (!is_writable($this->_path) || (file_exists($fileFullPath) && !is_writable($fileFullPath))))
		{
			return error('The given path or filename are not writable: '.$fileFullPath);
		}

		// Open the file in read mode
		$fileHandle = fopen($fileFullPath, $mode);

		// If it was a failure the return the error
		if ($fileHandle === false) return error('An error occurred while opening a file in '.$mode.' mode');

		// Otherwise return the file handle
		return success($fileHandle);
	}
}

