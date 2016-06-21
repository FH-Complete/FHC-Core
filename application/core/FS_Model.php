<?php

class FS_Model extends FHC_Model
{
	protected $filepath;  // Path of the file
	protected $acl;  // Name of the permissions array index for FS writing, reading...
	
	function __construct($filepath = null)
	{
		parent::__construct();
		$this->load->library('FilesystemLib');
		$this->acl = $this->config->item('fhc_acl');
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
		if (is_null($this->filepath))
			return $this->_error(lang('fhc_'.FHC_ERROR), FHC_MODEL_ERROR);
		
		// Check method parameters
		if (is_null($filename))
			return $this->_error(lang('fhc_'.FHC_ERROR), FHC_MODEL_ERROR);

		// Check rights
		if (! $this->fhc_db_acl->isBerechtigt($this->acl[$this->filepath], 's'))
			return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->acl[$this->filepath], FHC_MODEL_ERROR);
		
		if (!is_null($data = $this->filesystemlib->read($this->filepath, $filename)))
		{
			return $this->_success(base64_encode($data));
		}
		else
		{
			return $this->_error(lang('fhc_'.FHC_ERROR), FHC_MODEL_ERROR);
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
		if (is_null($this->filepath))
			return $this->_error(lang('fhc_'.FHC_ERROR), FHC_MODEL_ERROR);
		
		// Check method parameters
		if (is_null($filename))
			return $this->_error(lang('fhc_'.FHC_ERROR), FHC_MODEL_ERROR);
		if (is_null($content))
			return $this->_error(lang('fhc_'.FHC_ERROR), FHC_MODEL_ERROR);

		// Check rights
		if (! $this->fhc_db_acl->isBerechtigt($this->acl[$this->filepath], 'i'))
			return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->acl[$this->filepath], FHC_MODEL_ERROR);

		if ($this->filesystemlib->write($this->filepath, $filename, base64_decode($content)) === true)
		{
			return $this->_success(FHC_SUCCESS);
		}
		else
		{
			return $this->_error(lang('fhc_'.FHC_ERROR), FHC_MODEL_ERROR);
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
		if (is_null($this->filepath))
			return $this->_error(lang('fhc_'.FHC_ERROR), FHC_MODEL_ERROR);
		
		// Check method parameters
		if (is_null($filename))
			return $this->_error(lang('fhc_'.FHC_ERROR), FHC_MODEL_ERROR);
		if (is_null($content))
			return $this->_error(lang('fhc_'.FHC_ERROR), FHC_MODEL_ERROR);

		// Check rights
		if (! $this->fhc_db_acl->isBerechtigt($this->acl[$this->filepath], 'i'))
			return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->acl[$this->filepath], FHC_MODEL_ERROR);

		if ($this->filesystemlib->append($this->filepath, $filename, base64_decode($content)) === true)
		{
			return $this->_success(FHC_SUCCESS);
		}
		else
		{
			return $this->_error(lang('fhc_'.FHC_ERROR), FHC_MODEL_ERROR);
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
		if (is_null($this->filepath))
			return $this->_error(lang('fhc_'.FHC_ERROR), FHC_MODEL_ERROR);
		
		// Check method parameters
		if (is_null($filename))
			return $this->_error(lang('fhc_'.FHC_ERROR), FHC_MODEL_ERROR);

		// Check rights
		if (! $this->fhc_db_acl->isBerechtigt($this->acl[$this->filepath], 'd'))
			return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->acl[$this->filepath], FHC_MODEL_ERROR);

		if ($this->filesystemlib->remove($this->filepath, $filename) === true)
		{
			return $this->_success(FHC_SUCCESS);
		}
		else
		{
			return $this->_error(lang('fhc_'.FHC_ERROR), FHC_MODEL_ERROR);
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
		if (is_null($this->filepath))
			return $this->_error(lang('fhc_'.FHC_ERROR), FHC_MODEL_ERROR);
		
		// Check method parameters
		if (is_null($filename))
			return $this->_error(lang('fhc_'.FHC_ERROR), FHC_MODEL_ERROR);
		if (is_null($newFilename))
			return $this->_error(lang('fhc_'.FHC_ERROR), FHC_MODEL_ERROR);
		
		// Check rights
		if (! $this->fhc_db_acl->isBerechtigt($this->acl[$this->filepath], 'u'))
			return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->acl[$this->filepath], FHC_MODEL_ERROR);

		if ($this->filesystemlib->rename($this->filepath, $filename, $this->filepath, $newFilename) === true)
		{
			return $this->_success(FHC_SUCCESS);
		}
		else
		{
			return $this->_error(lang('fhc_'.FHC_ERROR), FHC_MODEL_ERROR);
		}
	}
}