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
		parent::__construct(array(
			'migrateStundenplan' => ['admin:rw'],
			'migrateReservierung' => ['admin:rw'],
			'migrateStundenplanBetriebsmittelEntries' => ['admin:rw']
		));
		$this->load->model('ressource/Kalender_model', 'KalenderModel');
		$this->load->model('ressource/Kalender_Lehreinheit_model', 'KalenderLehreinheitModel');
		$this->load->model('ressource/Kalender_Ort_model', 'KalenderOrtModel');
		$this->load->model('ressource/Stundenplandev_Kalender_model', 'SyncModel');
		$this->load->model('ressource/Reservierung_Kalender_model', 'SyncReservierungModel');
		$this->load->model('ressource/Kalender_Event_Teilnehmer_model', 'KalenderEventTeilnehmerModel');
		$this->load->model('ressource/Kalender_Event_model', 'KalenderEventModel');
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
		$this->load->model('person/Benutzer_model', 'BenutzerModel');
	}

	/**
	 * Everything has a beginning
	 */
	public function migrateStundenplan($von = null, $bis = null, $studiengang_kz = null)
	{
		$db = new DB_Model();

		$stpldevsql = '
			WITH eindeutige_stunden AS (
				SELECT DISTINCT unr, datum, stunde
				FROM lehre.tbl_stundenplandev
				WHERE datum >= ? AND datum <= ?';

		$params = [$von, $bis];

		if (!is_null($studiengang_kz))
		{
			$stpldevsql .= ' AND studiengang_kz = ?';

			$params[] = $studiengang_kz;
		}

		$stpldevsql .= '),
			
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

		array_push($params, $von, $bis);
		$stpldev = $db->execReadOnlyQuery($stpldevsql, $params);

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
				$ids = is_array($block->stundenplandev_ids) ? $block->stundenplandev_ids : explode(',', $block->stundenplandev_ids);
				/*$ids = array_map('intval', $ids);*/

				$this->SyncModel->db->where('stundenplandev_id IN (' . implode(',', $ids) . ')');
				$sync_result = $this->SyncModel->load();

				if (!hasData($sync_result))
				{
					$kalender_id = $this->_insertKalender($block, 'lehreinheit');
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
			}
		}
	}

	public function migrateReservierung($von = null, $bis = null, $ort_kurzbz = null)
	{
		$db = new DB_Model();

		$qry = "WITH eindeutige_stunden AS (
					SELECT DISTINCT titel, beschreibung, datum, stunde
					FROM campus.tbl_reservierung
					WHERE datum >= ? AND datum <= ?";

		$params = array($von, $bis);

		if (!is_null($ort_kurzbz))
		{
			$qry .= " AND ort_kurzbz = ?";
			$params[] = $ort_kurzbz;
		}

		$qry .=	"),
					 block_keys AS (
						 SELECT
							 titel, beschreibung, datum, stunde,
							 stunde - ROW_NUMBER() OVER (PARTITION BY titel, beschreibung, datum ORDER BY stunde) AS block_nr
						 FROM eindeutige_stunden
					 ),
					 blocks AS (
						 SELECT
							 bk.titel,
							 bk.beschreibung,
							 bk.datum,
							 bk.block_nr,
							 MIN(bk.stunde) AS stunde_von,
							 MAX(bk.stunde) AS stunde_bis,
							 array_agg(DISTINCT r.reservierung_id::text) AS reservierung_ids,
							 array_agg(DISTINCT r.uid) AS uids,
							 array_agg(DISTINCT r.gruppe_kurzbz) AS gruppen_kurzbz,
							 array_agg(DISTINCT ROW(r.semester, r.verband, r.gruppe)::text) AS svg_kombis,
							 array_agg(DISTINCT r.ort_kurzbz) AS orte_kurzbz,
							 MIN(r.studiengang_kz) AS studiengang_kz,
							 MIN(r.veranstaltung_id) AS veranstaltung_id,
							 MIN(r.reservierung_id) AS reservierung_id,
							 MAX(r.insertamum) AS insertamum,
							 (array_agg(r.insertvon ORDER BY r.insertamum ASC))[1] AS insertvon
						 FROM block_keys bk
								  JOIN campus.tbl_reservierung r
									   ON r.titel = bk.titel AND r.beschreibung = bk.beschreibung AND r.datum = bk.datum AND r.stunde = bk.stunde
						 WHERE r.datum >= ? AND r.datum <= ?
						 GROUP BY bk.titel, bk.beschreibung, bk.datum, bk.block_nr
					 )
				SELECT
					b.*,
					(b.datum + s_von.beginn) AS von,
					(b.datum + s_bis.ende)   AS bis
				FROM blocks b
						 JOIN lehre.tbl_stunde s_von ON s_von.stunde = b.stunde_von
						 JOIN lehre.tbl_stunde s_bis ON s_bis.stunde = b.stunde_bis
				ORDER BY b.reservierung_id DESC;";

		array_push($params, $von, $bis);
		$reservierung_data = $db->execReadOnlyQuery($qry, $params);


		if (hasData($reservierung_data))
		{
			$data = getData($reservierung_data);

			foreach($data as $block)
			{

				$ids = is_array($block->reservierung_ids) ? $block->reservierung_ids : explode(',', $block->reservierung_ids);

				$this->SyncReservierungModel->db->where('reservierung_id IN (' . implode(',', $ids) . ')');
				$sync_result = $this->SyncReservierungModel->load();

				if (!hasData($sync_result))
				{
					$kalender_id = $this->_insertKalender($block, 'reservierung');
					if ($kalender_id)
					{
						$this->_insertReservierungSync($block->reservierung_ids, $kalender_id);
					}
				}
				else
				{
					$syncData = getData($sync_result);
					$kalender_id = $syncData[0]->kalender_id;
					$last_sync = $syncData[0]->lastupdate;
					$synced_ids = array_column($syncData, 'reservierung_id');

					if ($block->insertamum > $last_sync)
					{
						$this->_updateKalender($kalender_id, $block);
						$this->_updateReservierungSync($synced_ids, $kalender_id);
					}

					$fehlende = array_diff($block->reservierung_ids, $synced_ids);
					if (!empty($fehlende))
					{
						$this->_insertReservierungSync($fehlende, $kalender_id);
					}
				}
			}
		}
	}

	public function migrateStundenplanBetriebsmittelEntries() {
		$this->setKalendarEntriesGroupIDs();
		$this->setKalendarEntriesGroupIDsForChildren();

		$dbModel = new DB_Model();

		$deleteOldImportedTempusEntriesQuery = "DELETE from lehre.tbl_betriebsmittel_kalender WHERE quelle != 'tempus_neu' OR quelle IS NULL;";
		$createHelperTypeQuery = "DO $$
			BEGIN
				IF NOT EXISTS (
					SELECT 1
					FROM pg_type
					WHERE typname = 'betriebsmittel_info'
				) THEN
					CREATE TYPE betriebsmittel_info AS (
						betriebsmittel_id bigint,
						insertamum timestamp,
						insertvon text
					);
				END IF;
			END $$;";
		
		$query = $deleteOldImportedTempusEntriesQuery . 
				 $createHelperTypeQuery .
		"WITH test AS (
			SELECT 
				tk.eindeutige_gruppen_id AS eindeutige_gruppen_id,
				array_agg(tk.kalender_id),
				array_agg(
					ROW(tsb.betriebsmittel_id, tsb.insertamum, tsb.insertvon)::betriebsmittel_info
				) AS betriebsmittel_data
			FROM sync.tbl_stundenplandev_kalender AS sk
			JOIN lehre.tbl_kalender tk ON tk.kalender_id = sk.kalender_id
			JOIN lehre.tbl_stundenplan_betriebsmittel tsb ON tsb.stundenplandev_id = sk.stundenplandev_id 
			GROUP BY tk.eindeutige_gruppen_id 
			)
			INSERT INTO lehre.tbl_betriebsmittel_kalender (
				eindeutige_kalender_gruppen_id, 
				betriebsmittel_id,
				insertamum,
				insertvon
				)
			SELECT
				t.eindeutige_gruppen_id,
				bm.betriebsmittel_id,
				bm.insertamum,
				bm.insertvon
			FROM test t
			CROSS JOIN LATERAL unnest(t.betriebsmittel_data) AS bm
			ON CONFLICT (eindeutige_kalender_gruppen_id, betriebsmittel_id) DO NOTHING;
			;";

		$dbModel->db->query($query);
	}

	private function setKalendarEntriesGroupIDs() {
		$dbModel = new DB_Model();

		$query = "UPDATE lehre.tbl_kalender
			SET eindeutige_gruppen_id = gen_random_uuid()
			WHERE vorgaenger_kalender_id IS NULL
			AND eindeutige_gruppen_id IS NULL;";

		$dbModel->db->query($query);

	}

	private function setKalendarEntriesGroupIDsForChildren() {
		$dbModel = new DB_Model();

		$query = "WITH RECURSIVE tree AS 
			(
				SELECT
					kalender_id,
					vorgaenger_kalender_id,
					kalender_id AS root_id,
					eindeutige_gruppen_id as root_eindeutige_gruppen_id
				FROM lehre.tbl_kalender
				WHERE vorgaenger_kalender_id IS NULL

				UNION ALL

				SELECT
					i.kalender_id,
					i.vorgaenger_kalender_id,
					t.root_id,
					t.root_eindeutige_gruppen_id 
				FROM lehre.tbl_kalender i
				JOIN tree t
				ON i.vorgaenger_kalender_id = t.kalender_id
				where i.eindeutige_gruppen_id is NULL
			
			)
			UPDATE lehre.tbl_kalender k
			SET eindeutige_gruppen_id = t.root_eindeutige_gruppen_id
			FROM tree t
			WHERE k.kalender_id = t.kalender_id
			AND k.eindeutige_gruppen_id IS NULL;";

		$dbModel->db->query($query);
	}

	private function _insertKalender($block, $typ)
	{
		$result = $this->KalenderModel->insert(
			array (
				'von' => $block->von,
				'bis' => $block->bis,
				'typ' => $typ,
				'status_kurzbz'=> 'live',
				'insertamum' => $block->insertamum,
				'insertvon' => $block->insertvon,
				'updateamum' => $block->updateamum ?? null,
				'updatevon' => $block->updatevon ?? null
			)
		);
		if(!isSuccess($result))
			return null;

		$kalender_id = getData($result);

		if ($typ === 'lehreinheit')
		{
			$this->KalenderLehreinheitModel->insert(
				array (
					'kalender_id' => $kalender_id,
					'lehreinheit_id'=> $block->lehreinheit_id
				)
			);
		}
		else if ($typ === 'reservierung')
		{
			$this->KalenderEventModel->insert(array(
				'kalender_id' => $kalender_id,
				'titel' => $block->titel,
				'beschreibung' => $block->beschreibung
			));


			if ($block->insertvon)
			{
				$user = $this->BenutzerModel->load(array('uid' => $block->insertvon));

				if (hasData($user))
				{
					$this->KalenderEventTeilnehmerModel->insert(array(
						'kalender_id' => $kalender_id,
						'uid' => getData($user)[0]->uid,
						'rolle_kurzbz' => 'organisator'
					));
				}
			}

			$uids = is_array($block->uids) ? $block->uids : explode(',', $block->uids);
			foreach ($uids as $uid)
			{
				$this->KalenderEventTeilnehmerModel->insert(array(
					'kalender_id' => $kalender_id,
					'uid' => $uid,
					'rolle_kurzbz' => 'teilnehmer'
				));
			}

			$semester_range = $this->StudiensemesterModel->getByDateRange($block->von, $block->bis);
			if (isError($semester_range)) return $semester_range;
			$studiensemester_kurzbz = getData($semester_range)[0]->studiensemester_kurzbz ?? null;

			$gruppen = is_array($block->gruppen_kurzbz) ? $block->gruppen_kurzbz : explode(',', $block->gruppen_kurzbz ?? '');

			foreach ($gruppen as $gruppe_kurzbz)
			{
				$gruppe_kurzbz = trim($gruppe_kurzbz);
				if (!empty($gruppe_kurzbz))
				{
					$this->KalenderEventTeilnehmerModel->insert(array(
						'kalender_id' => $kalender_id,
						'gruppe_kurzbz' => $gruppe_kurzbz,
						'studiengang_kz' => $block->studiengang_kz,
						'studiensemester_kurzbz' => $studiensemester_kurzbz,
						'rolle_kurzbz' => 'teilnehmer'
					));
				}
			}

			$rooms = is_array($block->orte_kurzbz) ? $block->orte_kurzbz : explode(',', $block->orte_kurzbz ?? '');
			foreach ($rooms as $room_kurzbz)
			{
				$room_kurzbz = trim($room_kurzbz);
				if (!empty($room_kurzbz))
				{
					$this->KalenderOrtModel->insert(
						array (
							'kalender_id' => $kalender_id,
							'ort_kurzbz' => $room_kurzbz
						)
					);
				}
			}

			foreach ($block->svg_kombis as $kombi_str)
			{
				$kombi_str = trim($kombi_str, '()');
				list($sem, $verb, $grp) = explode(',', $kombi_str);

				$sem = trim($sem) === '' ? null : trim($sem);
				$verb = trim($verb) === '' ? null : trim($verb);
				$grp = trim($grp) === '' ? null : trim($grp);

				if (is_null($sem) && is_null($verb) && is_null($grp))
					continue;

				$this->KalenderEventTeilnehmerModel->insert(array(
					'kalender_id' => $kalender_id,
					'studiengang_kz' => $block->studiengang_kz,
					'semester' => $sem,
					'verband' => $verb,
					'gruppe' => $grp,
					'studiensemester_kurzbz' => $studiensemester_kurzbz,
					'rolle_kurzbz' => 'teilnehmer'
				));
			}
		}

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
	private function _insertReservierungSync($ids, $kalender_id)
	{

		foreach($ids as $id)
		{
			$this->SyncReservierungModel->insert(
				array (
					'reservierung_id' => $id,
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

	private function _updateReservierungSync($ids, $kalender_id)
	{
		foreach($ids as $id)
		{
			$this->SyncReservierungModel->update(
				array (
					'reservierung_id' => $id,
					'kalender_id' => $kalender_id
				),
				array (
					'lastupdate' => date('Y-m-d H:i:s')
				)
			);
		}
	}
}
