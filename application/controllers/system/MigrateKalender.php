<?php
/*
 * Job zur einmaligen Migration des Stundenplans
 *
 * Aufruf
 * php index.ci.php system/MigrateKalender
 */

if (! defined('BASEPATH')) exit('No direct script access allowed');

class MigrateKalender extends Auth_Controller
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct(array('index' => ['admin:rw']));

		$this->load->model('ressource/Kalender_model', 'KalenderModel');
		$this->load->model('ressource/Kalender_Lehreinheit_model', 'KalenderLehreinheitModel');
		$this->load->model('ressource/Kalender_Ort_model', 'KalenderOrtModel');
		$this->load->model('ressource/Stundenplandev_Kalender_model', 'SyncModel');
	}

	/**
	 * Everything has a beginning
	 */
	public function index($von = null, $bis = null)
	{

		$db = new DB_Model();

		$stpldevsql = '
			WITH eindeutige_stunden AS (
				SELECT DISTINCT unr, datum, stunde
				FROM lehre.tbl_stundenplandev
				WHERE datum >= ? AND datum <= ?
			),
			block_keys AS (
				SELECT
					unr,
					datum,
					stunde,
					stunde - ROW_NUMBER() OVER (PARTITION BY unr, datum ORDER BY stunde) AS block_nr
				FROM eindeutige_stunden
			),
			blocks AS (
				SELECT
					bk.unr,
					bk.datum,
					bk.block_nr,
					MIN(bk.stunde) AS stunde_von,
					MAX(bk.stunde) AS stunde_bis,
					MIN(sp.lehreinheit_id) AS lehreinheit_id,
					MIN(sp.ort_kurzbz) AS ort_kurzbz,
					array_agg(sp.stundenplandev_id ORDER BY bk.stunde) AS stundenplandev_ids,
					MIN(sp.insertamum) AS insertamum,
					(array_agg(sp.insertvon ORDER BY sp.insertamum ASC))[1] AS insertvon,
					MAX(sp.updateamum) AS updateamum,
					(array_agg(sp.updatevon ORDER BY sp.updateamum DESC))[1] AS updatevon
				FROM block_keys bk JOIN lehre.tbl_stundenplandev sp ON sp.unr = bk.unr AND sp.datum = bk.datum AND sp.stunde = bk.stunde
				WHERE sp.datum >= ? AND sp.datum <= ?
				GROUP BY bk.unr, bk.datum, bk.block_nr
			)
			SELECT
				b.stundenplandev_ids,
				b.unr,
				b.datum,
				b.block_nr,
				b.lehreinheit_id,
				b.ort_kurzbz,
				b.datum + stundevon.beginn AS von,
				b.datum + stundebis.ende AS bis,
				b.insertamum,
				b.insertvon,
				b.updateamum,
				b.updatevon
			FROM blocks b
				JOIN lehre.tbl_stunde stundevon ON stundevon.stunde = b.stunde_von
				JOIN lehre.tbl_stunde stundebis ON stundebis.stunde = b.stunde_bis
			ORDER BY b.datum, b.unr, b.block_nr;';

	    $stpldev = $db->execReadOnlyQuery($stpldevsql, array($von, $bis, $von, $bis));


		if (hasData($stpldev))
	    {
			// Pruefen ob der Eintrag schon in Sync Tabelle vorhanden ist
			// Wenn neuere Änderungen vorhanden dann Update
			// Wenn keine Änderungen seit leztem Sync dann Ueberspringen
			// Wenn noch nicht vorhanden neu anlegen
			// Danach ggf pruefen welceh Eintraege in der zwischenzeit geloescht wurden und
			// in der neuen Tabelle auch archivieren oder loeschen

			$data = getData($stpldev);
			foreach($data as $block)
			{
				$this->SyncModel->db->where_in('stundenplandev_id', $block->stundenplandev_ids);
				$sync_result = $this->SyncModel->load();

				if (!hasData($sync_result))
				{
					$kalender_id = $this->_insertKalender($block);
					if ($kalender_id)
					{
						$this->_insertSync($block->stundenplandev_ids, $kalender_id);
					}
				}
				else
				{
					$syncData = getData($sync_result);
					$kalender_id = $syncData[0]->kalender_id;
					$last_sync = $syncData[0]->lastupdate;
					$synced_ids = array_column($syncData, 'stundenplandev_id');

					if ($block->updateamum > $last_sync)
					{
						$this->_updateKalender($kalender_id, $block);
						$this->_updateSync($synced_ids, $kalender_id);
					}

					$fehlende = array_diff($block->stundenplandev_ids, $synced_ids);
					if (!empty($fehlende))
					{
						$this->_insertSync($fehlende, $kalender_id);
					}
				}
				/*if(hasData($SyncResult))
				{
					//bereits vorhanden
					// TODO Update
				}
				else
				{
					// Neuen Eintrag anlegen

					$von = $rowstpl->datum.' '.$rowstpl->beginn;
					$bis = $rowstpl->datum.' '.$rowstpl->ende;
					$typ = 'lehreinheit';
					$status = 'visible_student';
					$insertamum = $rowstpl->insertamum;
					$insertvon = $rowstpl->insertvon;
					$updateamum = $rowstpl->updateamum;
					$updatevon = $rowstpl->updatevon;

					$resultKalenderInsert = $this->KalenderModel->insert(
						array(
							'von' => $von,
							'bis' => $bis,
							'typ' => $typ,
							'status_kurzbz' => $status,
							'vorgaenger_kalender_id' => null,
							'insertamum' => $insertamum,
							'insertvon' => $insertvon,
							'updateamum' => $updateamum,
							'updatevon' => $updatevon
						)
					);

					if(isSuccess($resultKalenderInsert))
					{
						$kalender_id = getData($resultKalenderInsert);

						$resultKalenderInsert = $this->KalenderLehreinheitModel->insert(
							array(
								'kalender_id' => $kalender_id,
								'lehreinheit_id' => $rowstpl->lehreinheit_id,
							)
						);

						$resultKalenderInsert = $this->KalenderOrtModel->insert(
							array(
								'kalender_id' => $kalender_id,
								'ort_kurzbz' => $rowstpl->ort_kurzbz,
							)
						);

						$resultSyncInsert = $this->SyncModel->insert(
							array(
								'stundenplandev_id' => $rowstpl->stundenplandev_id,
								'kalender_id' => $kalender_id,
								'lastupdate' => date('Y-m-d H:i:s')
							)
						);

					}
				}*/
			}
		}
	}


	private function _insertKalender($block)
	{
		$result = $this->KalenderModel->insert(
			array (
				'von' => $block->von,
				'bis' => $block->bis,
				'typ' => 'lehreinheit',
				'status_kurzbz'=> 'visible_student',
				'insertamum' => $block->insertamum,
				'insertvon' => $block->insertvon,
				'updateamum' => $block->updateamum,
				'updatevon' => $block->updatevon
			)
		);
		if(!isSuccess($result))
			return null;

		$kalender_id = getData($result);

		$this->KalenderLehreinheitModel->insert(
			array (
				'kalender_id' => $kalender_id,
				'lehreinheit_id'=> $block->lehreinheit_id
			)
		);
		$this->KalenderOrtModel->insert(
			array (
				'kalender_id' => $kalender_id,
				'ort_kurzbz' => $block->ort_kurzbz
			)
		);

		return $kalender_id;
	}

	private function _insertSync($ids, $kalender_id)
	{
		foreach($ids as $id)
		{
			$this->SyncModel->insert(
				array (
					'stundenplandev_id' => $id,
					'kalender_id' => $kalender_id,
					'lastupdate' => date('Y-m-d H:i:s')
				)
			);
		}
	}

	private function _updateKalender($kalender_id, $block)
	{
		$this->KalenderModel->update(
			array (
				'kalender_id' => $kalender_id
			),
			array (
				'von' => $block->von,
				'bis' => $block->bis,
				'updateamum'=> $block->updateamum,
				'updatevon' => $block->updatevon
			)
		);
	}

	private function _updateSync($ids, $kalender_id)
	{
		foreach($ids as $id)
		{
			$this->SyncModel->update(
				array (
					'stundenplandev_id' => $id,
					'kalender_id' => $kalender_id
				),
				array (
					'lastupdate' => date('Y-m-d H:i:s')
				)
			);
		}
	}
}
