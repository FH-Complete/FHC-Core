<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class AkteLibOld extends FHC_Controller
{
	const AKTE_KATEGORIE_KURZBZ = 'Akte';
	const AKTE_CONTENT_PROPERTY = 'file_content';
	const AKTE_TITEL_PROPERTY = 'titel';

	private $_ci; // Code igniter instance
	private $_uid;

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		$this->_ci =& get_instance();
		$this->_uid = getAuthUID();

		$this->_ci->load->model('crm/Akte_model', 'AkteModel');
		$this->_ci->load->model('content/Dms_model', 'DmsModel');
		$this->_ci->load->model('content/DmsVersion_model', 'DmsVersionModel');
		$this->_ci->load->model('content/DmsFS_model', 'DmsFSModel');
	}

	public function insertAkteDms($akte)
	{
		$dmsRes = $this->_insertDmsFromAkteData($akte);

		if (isError($dmsRes))
			return $dmsRes;

		if (hasData($dmsRes))
		{
			$dms_id = getData($dmsRes)['dms_id'];
			$akte['dms_id'] = $dms_id;
			unset($akte[self::AKTE_CONTENT_PROPERTY]);

			// insert Akte
			$akteRes = $this->_ci->AkteModel->insert($akte);

			if (isError($akteRes))
				return $akteRes;

			if (hasData($akteRes))
			{
				$akte_id = getData($akteRes);

				return success(
					array(
						'akte_id' => $akte_id,
						'dms_id' => $dms_id
					)
				);
			}
			else
				return error("Error when inserting Akte");
		}
		else
			return error("Error when inserting Dms");

	}

	public function updateAkteDms($akte_id, $akte, $overwriteVersion = false)
	{
		$dms_id = null;
		$maxVersion = 0;

		// get latest version number
		$db = new DB_Model();
		$akteDmsRes = $db->execReadOnlyQuery(
			'SELECT dms_id, max(version) AS max_version
			FROM public.tbl_akte ak
			LEFT JOIN campus.tbl_dms USING(dms_id)
			LEFT JOIN campus.tbl_dms_version USING(dms_id)
			WHERE akte_id = ?
			GROUP BY dms_id',
			array($akte_id)
		);

		if (isError($akteDmsRes))
			return $akteDmsRes;

		if (hasData($akteDmsRes))
		{
			$akteDms = getData($akteDmsRes)[0];

			// if no dms, insert new
			if (is_null($akteDms->dms_id))
			{
				$dmsRes = $this->_insertDmsFromAkteData($akte);

				if (isError($dmsRes))
					return $dmsRes;

				if (hasData($dmsRes))
				{
					$dms_id = getData($dmsRes)['dms_id'];
				}
				else
					return error('Error when inserting dms');
			}
			else
			{
				$dms_id = $akteDms->dms_id;

				// otherwise update dms version
				$maxVersion = $akteDms->max_version;

				if (is_numeric($maxVersion))
				{
					// overwrite latest
					$currVersion = $overwriteVersion === true ? $maxVersion : $maxVersion + 1;

					$writeFileRes = $this->_writeFile($akte[self::AKTE_TITEL_PROPERTY], $akte[self::AKTE_CONTENT_PROPERTY], $dms_id, $currVersion);

					if (isError($writeFileRes))
						return $writeFileRes;

					if (hasData($writeFileRes))
					{
						// update or insert dms version
						$filename = getData($writeFileRes);
						$dmsVersionRes = $this->_upsertDmsVersion($dms_id, $currVersion, $filename, $akte);

						if (isError($dmsVersionRes))
							return $dmsVersionRes;
					}
					else
						return error("Error when writing file");
				}
				else
					return error("invalid dms version");
			}

/*			var_dump("DMS ID");
			var_dump($dms_id);*/

			if (!is_numeric($dms_id))
				return error("invalid dms id");

			// update Akte and link akte to inserted dms
			$akte['dms_id'] = $dms_id;
			unset($akte[self::AKTE_CONTENT_PROPERTY]);

			$akteUpdateRes = $this->_ci->AkteModel->update(
				$akte_id,
				$akte
			);

			if (isError($akteUpdateRes))
				return $akteUpdateRes;

			return success(
				array(
					'akte_id' => $akte_id,
					'dms_id' => $dms_id,
					'version' => $maxVersion
				)
			);
		}
		else
			return error("Akte not found");
	}

	private function _insertDmsFromAkteData($akte)
	{
		if (!isset($akte[self::AKTE_TITEL_PROPERTY]))
			return error("Akte has no title");

		if (!isset($akte[self::AKTE_CONTENT_PROPERTY]))
			return error("Akte has no inhalt");

		// write akte to filesystem
		$fileWriteRes = $this->_writeFile($akte[self::AKTE_TITEL_PROPERTY], $akte[self::AKTE_CONTENT_PROPERTY]);

		if (isError($fileWriteRes))
			return $fileWriteRes;

		if (hasData($fileWriteRes))
		{
			$filename = getData($fileWriteRes);

			// insert dms
			$dmsRes = $this->_ci->DmsModel->insert(
				array(
					'kategorie_kurzbz' => self::AKTE_KATEGORIE_KURZBZ
				)
			);

			if (isError($dmsRes))
				return $dmsRes;

			if (hasData($dmsRes))
			{
				$dms_id = getData($dmsRes);

				// insert dms version
				$dmsVersionRes = $this->_upsertDmsVersion($dms_id, 0, $filename, $akte);

				if (isError($dmsVersionRes))
					return $dmsVersionRes;

				return success(
					array(
						'dms_id' => $dms_id
					)
				);
			}
			else
				return error("Error when inserting DMS");
		}
		else
			return error("Error when writing file");
	}

	private function _writeFile($akteFilename, $akteInhalt, $dms_id = null, $version = null)
	{
		$filename = null;

		if (isset($dms_id) && isset($version))
		{
			$this->_ci->DmsVersionModel->addSelect('filename');
			$dmsVersionRes = $this->_ci->DmsVersionModel->loadWhere(
				array(
					'dms_id' => $dms_id,
					'version' => $version
				)
			);

			if (isError($dmsVersionRes))
				return $dmsVersionRes;

			if (hasData($dmsVersionRes))
			{
				$filename = getData($dmsVersionRes)[0]->filename;
			}
		}
		else
		{
			$filename = $this->_getUniqueFilename($akteFilename);
		}

		if (isEmptyString($filename))
			return error('No filename provided.');

		// write akte to filesystem
		$writeRes = $this->_ci->DmsFSModel->write($filename, $akteInhalt);

		if (isError($writeRes))
			return $writeRes;

		return success($filename);
	}

	private function _upsertDmsVersion($dms_id, $version, $filename, $akte)
	{
		$dmsVersionToSave = array(
			'dms_id' => $dms_id,
			'version' => $version,
			'filename' => $filename,
			'mimetype' => isset($akte['mimetype']) ? $akte['mimetype'] : null, // TODO check if exists
			'name' => isset($akte['titel']) ? $akte['titel'] : null, // TODO check if exists,
		);

		$dmsVersionRes = $this->_ci->DmsVersionModel->loadWhere(
			array(
				'dms_id' => $dms_id,
				'version' => $version
			)
		);

		if (isError($dmsVersionRes))
			return $dmsVersionRes;

		if (hasData($dmsVersionRes))
		{
			$dmsVersionToSave['updatevon'] = $this->_uid;
			$dmsVersionToSave['updateamum'] = date('Y-m-d H:i:s');
			return $this->_ci->DmsVersionModel->update(
				array(
					'dms_id' => $dms_id,
					'version' => $version
				),
				$dmsVersionToSave
			);
		}
		else
		{
			$dmsVersionToSave['insertvon'] = $this->_uid;
			$dmsVersionToSave['insertamum'] = date('Y-m-d H:i:s');
			return $this->_ci->DmsVersionModel->insert($dmsVersionToSave);
		}
	}

	private function _getUniqueFilename($filename)
	{
		$uniqueFilename = uniqid();
		$fileExtension = pathinfo($filename, PATHINFO_EXTENSION);

		if (!isEmptyString($fileExtension))
			$uniqueFilename .= '.'.$fileExtension;

		return $uniqueFilename;
	}
}
