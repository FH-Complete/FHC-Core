<?php

if (! defined('BASEPATH'))
	exit('No direct script access allowed');

class FileCheckJob extends CLI_Controller
{
	/**
	 * Initialize FileCheckJob
	 *
	 * @return	void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 */
	public function run()
	{
		$this->load->model('content/Dms_model', 'DmsModel');
		$this->_checkFiles();
	}

	/**
	 * 
	 * @param
	 * @return object success or error
	 */
	private function _checkFiles()
	{
		$nonExistentFiles = [];
		$count = 0;

		$dir = DMS_PATH;

		$it = new RecursiveDirectoryIterator($dir);

		foreach (new RecursiveIteratorIterator($it) as $file) {
			if($file->isDir()) continue;
			$filename = $file->getFilename();
			//echo "Checking $filename...\n";
			$this->DmsModel->addSelect('dms_id');
			$this->DmsModel->addJoin('campus.tbl_dms_version', 'dms_id');
			$result = $this->DmsModel->loadWhere(['filename' => $filename]);

			if (isError($result))
			{
				echo getError($result);
				continue;
			}

			if (!hasData($result))
			{
				$nonExistentFiles[] = $filename;
			}
			$count++;
		}

		echo "\n$count files checked, ".count($nonExistentFiles)." files exist in file system, but not in database:\n";
		echo $dir.implode("\n$dir", $nonExistentFiles)."\n";
	}
}
