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
		$this->_checkDms();
	}

	private function _checkDms($dmsIdCounter = 0, $totalFileCounter = 0, $nonExistentFiles = [])
	{
		$limit = 100;

		// get dms entries
		$qry = "
			SELECT 
				DISTINCT ON (dms_id) dms_id, vers.filename, vers.version
			FROM
				campus.tbl_dms
				LEFT JOIN campus.tbl_dms_version vers USING (dms_id)
			WHERE
				dms_id > $dmsIdCounter
				AND dms_id IN (395281, 395280)
			ORDER BY
				dms_id, vers.version DESC
			LIMIT $limit";

		$result = $this->DmsModel->execReadOnlyQuery($qry);

		if (isError($result))
		{
			echo getError($result);
			return;
		}

		if (!hasData($result))
		{
			echo "\nDMS check finished!";
			echo "\n----------------------------------";
			echo "\n$totalFileCounter files checked, ".count($nonExistentFiles)." file(s) exist in DMS, but not in file system:\n";
			echo implode("\n", $nonExistentFiles)."\n";
			return;
		}

		$data = getData($result);

		$dir = DMS_PATH;
		$dms_id = 0;

		foreach ($data as $dms)
		{
			$dms_id = $dms->dms_id;
			$totalFileCounter++;
			$fullPath = $dir.$dms->filename;
			//echo "Checking dms entry with id $dms_id...\n";
			if (!file_exists($fullPath))
			{
				$nonExistentFiles[] = $fullPath;
			}
		}

		$this->_checkDms($dms_id, $totalFileCounter, $nonExistentFiles);
	}

	/**
	 * 
	 * @param
	 * @return object success or error
	 */
	private function _checkFiles()
	{
		$missingDms = [];
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
				$missingDms[] = $filename;
			}
			$count++;
		}

		echo "\nFile system check finished!";
		echo "\n----------------------------------";
		echo "\n$count files checked, ".count($missingDms)." file(s) exist in file system, but not in database:\n";
		echo $dir.implode("\n$dir", $missingDms)."\n";
	}
}
