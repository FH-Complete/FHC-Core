<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 */
abstract class FS_Model extends CI_Model
{
	protected $filepath;  // Path of the file

	/**
	 * Loads FilesystemLib and set properties
	 */
	public function __construct($filepath = null)
	{
		parent::__construct();

		// Load the filesystem library
		$this->load->library('FilesystemLib');

		$this->filepath = $filepath;
	}

	/**
	 * Read data from file system
	 *
	 * @return array
	 */
	public function read($filename)
	{
		// Check Class-Attributes
		if (is_null($this->filepath)) return error('The given filepath in not valid', EXIT_ERROR);

		// Check method parameters
		if (is_null($filename)) return error('The given filename is not valid', EXIT_ERROR);

		if (!is_null($data = $this->filesystemlib->read($this->filepath, $filename)))
		{
			return success(base64_encode($data));
		}
		else
		{
			return error('An error occurred while reading a file', EXIT_ERROR);
		}
	}

	/**
	 * Writing data to file system
	 *
	 * @param   string $fileContent File content
	 * @return  object
	 */
	public function write($filename, $content)
	{
		// Check Class-Attributes
		if (is_null($this->filepath)) return error('The given filepath in not valid', EXIT_ERROR);

		// Check method parameters
		if (is_null($content)) return error('The given file content is not valid', EXIT_ERROR);
		if (is_null($filename)) return error('The given filename is not valid', EXIT_ERROR);

		if ($this->filesystemlib->write($this->filepath, $filename, base64_decode($content)) === true)
		{
			return success();
		}
		else
		{
			return error('An error occurred while writing a file', EXIT_ERROR);
		}
	}

	/**
	 * Append data to a file
	 *
	 * @param   array $data File content
	 * @return  array
	 */
	public function append($filename, $content)
	{
		// Check Class-Attributes
		if (is_null($this->filepath)) return error('The given filepath in not valid', EXIT_ERROR);

		// Check method parameters
		if (is_null($content)) return error('The given file content is not valid', EXIT_ERROR);
		if (is_null($filename)) return error('The given filename is not valid', EXIT_ERROR);

		if ($this->filesystemlib->append($this->filepath, $filename, base64_decode($content)) === true)
		{
			return success();
		}
		else
		{
			return error('An error occurred while appending to a file', EXIT_ERROR);
		}
	}

	/**
	 * Delete data from file system
	 *
	 * @param   string $id  Primary Key for DELETE
	 * @return  array
	 */
	public function remove($filename)
	{
		// Check Class-Attributes
		if (is_null($this->filepath)) return error('The given filepath in not valid', EXIT_ERROR);

		// Check method parameters
		if (is_null($filename)) return error('The given filename is not valid', EXIT_ERROR);

		if ($this->filesystemlib->remove($this->filepath, $filename) === true)
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
	 *
	 * @param   string $id  Primary Key for DELETE
	 * @return  array
	 */
	public function rename($filename, $newFilename)
	{
		// Check Class-Attributes
		if (is_null($this->filepath)) return error('The given filepath in not valid', EXIT_ERROR);

		// Check method parameters
		if (is_null($filename)) return error('The given filename is not valid', EXIT_ERROR);
		if (is_null($newFilename)) return error('The given new filename is not valid', EXIT_ERROR);

		if ($this->filesystemlib->rename($this->filepath, $filename, $this->filepath, $newFilename) === true)
		{
			return success();
		}
		else
		{
			return error('An error occurred while renaming a file', EXIT_ERROR);
		}
	}
}
