<?php
/*
 * Job zur einmaligen Migration des Stundenplans
 *
 * Aufruf
 * php index.ci.php system/MigrateKalender
 */

if (! defined('BASEPATH')) exit('No direct script access allowed');

class MigrateKalender extends CLI_Controller
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		$this->load->model('ressource/Kalender_model', 'KalenderModel');
		$this->load->model('ressource/Kalender_Lehreinheit_model', 'KalenderLehreinheitModel');
		$this->load->model('ressource/Kalender_Ort_model', 'KalenderOrtModel');
		$this->load->model('ressource/Stundenplandev_Kalender_model', 'SyncModel');
	}

	/**
	 * Everything has a beginning
	 */
	public function index()
	{
		$von = date('Y-m-d') // TODO
		$bis = date('Y-m-d') // TODO

		$db = new DB_Model();
	    $stpldevsql = '
			SELECT *,
				(SELECT beginn FROM lehre.tbl_stunde WHERE stunde=tbl_stundenplandev.stunde) as beginn,
				(SELECT ende FROM lehre.tbl_stunde WHERE stunde=tbl_stundenplandev.stunde) as ende
			 FROM
			 	lehre.tbl_stundenplandev WHERE datum>=? and datum<=? ORDER BY datum, stunde, unr';

	    $stpldev = $db->execReadOnlyQuery($stpldevsql, array($von, $bis));
	    if (hasData($stpldev))
	    {
			// Pruefen ob der Eintrag schon in Sync Tabelle vorhanden ist
			// Wenn neuere Änderungen vorhanden dann Update
			// Wenn keine Änderungen seit leztem Sync dann Ueberspringen
			// Wenn noch nicht vorhanden neu anlegen
			// Danach ggf pruefen welceh Eintraege in der zwischenzeit geloescht wurden und
			// in der neuen Tabelle auch archivieren oder loeschen

			$data = getData($stpldev);
			foreach($data as $rowstpl)
			{
				$SyncResult = $this->SyncModel->loadWhere(
					array('stundenplandev_id' => $rowstpl->stundenplandev_id)
				);
				if(hasData($SyncResult))
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
				}
			}
		}
	}
}
