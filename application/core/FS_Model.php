<?php

class FS_Model extends FHC_Model
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

	/** ---------------------------------------------------------------
	 * Read data from file system
	 *
	 * @return array
	 */
	public function read($filename)
	{
		// Check Class-Attributes
		if (is_null($this->filepath)) return error(FHC_MODEL_ERROR, FHC_ERROR);

		// Check method parameters
		if (is_null($filename)) return error(FHC_MODEL_ERROR, FHC_ERROR);

		// Check rights
		if (isError($ent = $this->isEntitled($this->filepath, PermissionLib::SELECT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR))) return $ent;

		if (!is_null($data = $this->filesystemlib->read($this->filepath, $filename)))
		{
			return success(base64_encode($data));
		}
		else
		{
			return error(FHC_MODEL_ERROR, FHC_ERROR);
		}
	}

	/** ---------------------------------------------------------------
	 * Writing data to file system
	 *
	 * @param   string $fileContent File content
	 * @return  object
	 */
	public function write($filename, $content)
	{
		// Check Class-Attributes
		if (is_null($this->filepath)) return error(FHC_MODEL_ERROR, FHC_ERROR);

		// Check method parameters
		if (is_null($filename)) return error(FHC_MODEL_ERROR, FHC_ERROR);
		if (is_null($content)) return error(FHC_MODEL_ERROR, FHC_ERROR);

		// Check rights
		if (isError(($ent = $this->isEntitled($this->filepath, PermissionLib::INSERT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR)))) return $ent;

		if ($this->filesystemlib->write($this->filepath, $filename, base64_decode($content)) === true)
		{
			return success(FHC_SUCCESS);
		}
		else
		{
			return error(FHC_MODEL_ERROR, FHC_ERROR);
		}
	}

	/** ---------------------------------------------------------------
	 * Append data to a file
	 *
	 * @param   array $data File content
	 * @return  array
	 */
	public function append($filename, $content)
	{
		// Check Class-Attributes
		if (is_null($this->filepath)) return error(FHC_MODEL_ERROR, FHC_ERROR);

		// Check method parameters
		if (is_null($content)) return error(FHC_MODEL_ERROR, FHC_ERROR);
		if (is_null($filename)) return error(FHC_MODEL_ERROR, FHC_ERROR);

		// Check rights
		if (isError($ent = $this->isEntitled($this->filepath, PermissionLib::INSERT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR))) return $ent;

		if ($this->filesystemlib->append($this->filepath, $filename, base64_decode($content)) === true)
		{
			return success(FHC_SUCCESS);
		}
		else
		{
			return error(FHC_MODEL_ERROR, FHC_ERROR);
		}
	}

	/** ---------------------------------------------------------------
	 * Delete data from file system
	 *
	 * @param   string $id  Primary Key for DELETE
	 * @return  array
	 */
	public function remove($filename)
	{
		// Check Class-Attributes
		if (is_null($this->filepath)) return error(FHC_MODEL_ERROR, FHC_ERROR);

		// Check method parameters
		if (is_null($filename)) return error(FHC_MODEL_ERROR, FHC_ERROR);

		// Check rights
		if (isError($ent = $this->isEntitled($this->filepath, PermissionLib::DELETE_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR))) return $ent;

		if ($this->filesystemlib->remove($this->filepath, $filename) === true)
		{
			return success(FHC_SUCCESS);
		}
		else
		{
			return error(FHC_MODEL_ERROR, FHC_ERROR);
		}
	}

	/** ---------------------------------------------------------------
	 * Rename a file
	 *
	 * @param   string $id  Primary Key for DELETE
	 * @return  array
	 */
	public function rename($filename, $newFilename)
	{
		// Check Class-Attributes
		if (is_null($this->filepath)) return error(FHC_MODEL_ERROR, FHC_ERROR);

		// Check method parameters
		if (is_null($filename)) return error(FHC_MODEL_ERROR, FHC_ERROR);
		if (is_null($newFilename)) return error(FHC_MODEL_ERROR, FHC_ERROR);

		// Check rights
		if (isError($ent = $this->isEntitled($this->filepath, PermissionLib::UPDATE_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR))) return $ent;

		if ($this->filesystemlib->rename($this->filepath, $filename, $this->filepath, $newFilename) === true)
		{
			return success(FHC_SUCCESS);
		}
		else
		{
			return error(FHC_MODEL_ERROR, FHC_ERROR);
		}
	}
}
