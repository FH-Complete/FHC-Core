<?php
/**
 * FH-Complete
 *
 * @package		FHC-API
 * @author		FHC-Team
 * @copyright	Copyright (c) 2016, fhcomplete.org
 * @license		GPLv3
 * @link		http://fhcomplete.org
 * @since		Version 1.0
 * @filesource
 */
// ------------------------------------------------------------------------

if (!defined('BASEPATH')) exit('No direct script access allowed');

class FilesystemLib
{
	/*
	 * 
	 */
	public function __construct() {}
	
	/*
	 * 
	 */
	private function checkParameters($filepath, $filename)
	{
		if (isset($filepath) && isset($filename) &&
			$filepath != '' && $filename != '')
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/*
	 * 
	 */
	public function read($filepath, $filename)
	{
		$result = null;
		
		if ($this->checkParameters($filepath, $filename))
		{
			$resource = $filepath . DIRECTORY_SEPARATOR . $filename;
			if (file_exists($resource) && $fileHandle = fopen($resource, 'r'))
			{
				$result = '';
				while (!feof($fileHandle))
				{
					$result .= fread($fileHandle, 8192);
				}
				fclose($fileHandle);
			}
		}
		
		return $result;
	}
	
	/*
	 * 
	 */
	public function write($filepath, $filename, $content)
	{
		$result = null;

		if ($this->checkParameters($filepath, $filename) && isset($content))
		{
			$resource = $filepath . DIRECTORY_SEPARATOR . $filename;
			if (is_writable($filepath) && $fileHandle = fopen($resource, 'w'))
			{
				if (fwrite($fileHandle, $content) !== false)
				{
					$result = true;
				}
				fclose($fileHandle);
			}
		}
		
		return $result;
	}
	
	/*
	 * 
	 */
	public function append($filepath, $filename, $content)
	{
		$result = null;

		if ($this->checkParameters($filepath, $filename) && isset($content))
		{
			$resource = $filepath . DIRECTORY_SEPARATOR . $filename;
			if (is_writable($resource) && $fileHandle = fopen($resource, 'a'))
			{
				if (fwrite($fileHandle, $content) !== false)
				{
					$result = true;
				}
				fclose($fileHandle);
			}
		}
		
		return $result;
	}
	
	/*
	 * 
	 */
	public function remove($filepath, $filename)
	{
		$result = null;

		if ($this->checkParameters($filepath, $filename))
		{
			if (is_writable($filepath))
			{
				$resource = $filepath . DIRECTORY_SEPARATOR . $filename;
				$result = unlink($resource);
			}
		}
		
		return $result;
	}
	
	/*
	 * 
	 */
	public function rename($filepath, $filename, $newFilepath, $newFilename)
	{
		$result = null;

		if ($this->checkParameters($filepath, $filename) && $this->checkParameters($newFilepath, $newFilename))
		{
			$resource = $filepath . DIRECTORY_SEPARATOR . $filename;
			if (is_writable($filepath) && is_writable($newFilepath) && file_exists($resource))
			{
				$destination = $newFilepath . DIRECTORY_SEPARATOR . $newFilename;
				$result = rename($resource, $destination);
			}
		}
		
		return $result;
	}
}