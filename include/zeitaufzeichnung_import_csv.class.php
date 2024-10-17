<?php
require_once('zeitaufzeichnung_import.class.php');
require_once('../../../include/organisationseinheit.class.php');
require_once('../../../include/vertragsbestandteil.class.php');

/**
 * Description of zeitaufzeichnung_csv_import
 *
 * @author bambi
 */
class zeitaufzeichnung_import_csv extends zeitaufzeichnung_import {

	const USER          = 0;
	const AKTIVITAET    = 1;
	const STARTDT       = 2;
	const ENDEDT        = 3;
	const BESCHREIBUNG  = 4;
	const OE            = 5;
	const PROJEKT       = 6;
	const PHASE         = 7;
	const SERVICE       = 8;
	const HOMEOFFICE    = 9;
	const ANZAHL_PFLICHTFELDER = 4;

	protected $tmpname;
	protected $fh;

	protected $anzahl;
	protected $importtage_array;
	protected $ende_vorher;

	protected $user;

	protected $project_kurzbz_array;
	protected $projectphasen_kurzbz_array;

	protected $sperrdatum;

	protected $current_line;

	protected $homeoffice;


	/**
	 * @param phrasen $p The Translator object
	 * @param string $user The user ID
	 * @param string $sperrdatum "c" formatted datetimestring
	 * @param string $filename
	 */
	public function __construct(phrasen $p, $user, $sperrdatum, $filename) {
		parent::__construct($p);

		$this->user     = $user;
		$this->tmpname = $filename;
		$this->sperrdatum = $sperrdatum;

		$this->project_kurzbz_array         = [];
		$projects = $this->project->getProjekteListForMitarbeiter($user);
		foreach ($projects as $pp)
			$this->project_kurzbz_array[] = (string) $pp->projekt_kurzbz;

		$this->projectphasen_kurzbz_array   = [];
		$projektphasen = $this->phase->getProjectphaseForMitarbeiter($user);
		foreach ($projektphasen as $pp)
			$this->projectphasen_kurzbz_array[] = (string) $pp->projektphase_id;
	}


	/**
	 * @param string $msg
	 * @param boolean $prepend_current_line
	 * @return void
	 */
	protected function addError($msg, $prepend_current_line = false) {
		if( $prepend_current_line ) {
			$msg = 'Zeile ' . $this->current_line . ' - ' . $msg;
		}
		$this->errors[] = $msg;
	}


	/**
	 * @return void
	 */
	public function import() {
		try {
			$this->checkMimeType();
			$this->openFileForReading();
			$this->checkEncoding();
			$this->iterateRows();
			$this->checkAndCleanup();
		} catch (Exception $ex) {
			$this->addError($ex->getMessage());
		}
	}


	/**
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function checkMimeType() {
		$mimeType = mime_content_type($this->tmpname);
		if ($mimeType !== 'text/plain' ) {
			throw new Exception('Datei ist nicht im CSV Format.');
		}
	}

	/**
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function openFileForReading() {
		if (false === ($this->fh = fopen($this->tmpname, 'r')) )
		{
			throw new Exception('CSV - Datei konnte nicht zum lesen geöffnet werden.');
		}
	}

	/**
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function checkEncoding() {
		$filecontent = file_get_contents($this->tmpname);
		if (!mb_detect_encoding($filecontent, 'UTF-8', true) ) {
			throw new Exception('Datei konnte nicht importiert werden. Encoding ist nicht UTF-8!');
		}
	}

	/**
	 * @return void
	 */
	protected function iterateRows() {
		set_time_limit(0);

		$this->anzahl = 0;
		$this->importtage_array = array();
		$this->ende_vorher = date('Y-m-d H:i:s');

		$data = null;
		$this->current_line = 0;
		while (($data = fgetcsv($this->fh, 1000, ';', '"')) !== FALSE) {
			if ((false !== strpos($data[self::USER], '#'))
				|| count($data) <  self::ANZAHL_PFLICHTFELDER) {
				// ignore lines starting with #
				continue;
			}
			$this->current_line++;
			$this->processData($data);
		}
	}

	/**
	 * @param array $data
	 * @return void
	 */
	protected function processData($data) {
		try {
			$this->checkUser($data[self::USER]);
			$this->initData($data);
			$this->checkProject($data[self::PROJEKT], $data[self::PHASE]);
			$this->checkPhase($data[self::PHASE]);
			$this->checkPhaseBebuchbar($data[self::PHASE]);
			$this->checkIfArbeitspaketZuWaehlen($data[self::PROJEKT], $data[self::PHASE]);
			$this->checkZeitsperren($this->user, $this->datum->formatDatum($data[self::STARTDT], 'Y-m-d'));
			$this->checkSperrdatum($data[self::STARTDT]);
			$this->checkLimitdatum($data[self::STARTDT]);
			$this->checkDienstreise($data[self::STARTDT], $data[self::ENDEDT], $data[self::AKTIVITAET]);
			$this->checkTagesgenau($data[self::ENDEDT]);
			if(empty($data[self::PHASE]))
				$this->checkProjectInterval($data[self::PROJEKT], $data[self::STARTDT], $data[self::ENDEDT]);
			$this->checkPhaseInterval($data[self::PHASE], $data[self::STARTDT], $data[self::ENDEDT]);
			$this->checkVals($data[self::OE],$data[self::PROJEKT],$data[self::PHASE],$data[self::SERVICE]);
			$this->mapLehreIntern($data);
			$this->prepareZeitaufzeichnung($data);
			$this->checkImporttage($data[self::STARTDT]);
			$this->saveZeit($data[self::STARTDT], $data[self::ENDEDT]);
		} catch (Exception $ex) {
			$this->addError($ex->getMessage(), true);
		}
	}

	/**
	 * @param string $user The User ID
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function checkUser($user) {
		if ($user !== $this->user || (strpos($user, '#') !== false))
		{
			throw new Exception($this->p->t("global/fehlerBeimSpeichernDerDaten").': Falsche UID nicht importiert (' . $user . ')');
		}
	}

	/**
	 * @param string $project The Project ID or empty string
	 * @param string $phase The Phase ID or empty string
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function checkProject($project, $phase) {
		if(!empty($project) && !in_array($project, $this->project_kurzbz_array) && empty($phase))
		{
			throw new Exception($this->p->t("global/fehlerBeimSpeichernDerDaten").': Eingabe nicht möglich, da Sie folgendem Projekt entweder nicht zugewiesen sind oder das Projekt schon abgeschlossen wurde: (' . $project . ')');
		}

	}

	/**
	 * @param string $phase The Phase ID or empty string
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function checkPhase($phase) {
		if(!empty($phase) && !in_array($phase, $this->projectphasen_kurzbz_array))
		{
			throw new Exception($this->p->t("global/fehlerBeimSpeichernDerDaten").': Eingabe nicht möglich, da Sie folgender Projektphase entweder nicht zugewiesen sind oder die Projektphase schon abgeschlossen wurde: (' . $phase . ')');
		}
	}

	/**
	 * @param array $data
	 * @return void
	 */
	protected function initData(&$data) {
		foreach ([self::OE, self::PROJEKT, self::PHASE, self::SERVICE] as $key)
			if (!isset($data[$key]))
				$data[$key] = NULL;

		if (!isset($data[self::HOMEOFFICE]))
			$data[$key] = false;
	}

	/**
	 * @param string $start datetimestring
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function checkSperrdatum($start) {
		if ($this->datum->formatDatum($start, 'Y-m-d H:i:s') < $this->sperrdatum) {
			throw new Exception($this->p->t("global/fehlerBeimSpeichernDerDaten") . ': Eingabe nicht möglich da vor dem Sperrdatum (' . $start . ')');
		}

	}

	/**
	 * @param string $oe_val
	 * @param string $project_val
	 * @param string $phase_val
	 * @param string $service_val
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function checkVals($oe_val, $project_val, $phase_val, $service_val) {
		$failedvals = $this->_checkVals($oe_val, $project_val, $phase_val, $service_val);
		if( count($failedvals) > 0 )
		{
			throw new Exception($this->p->t("global/fehlerBeimSpeichernDerDaten").': Fehlerhafte Werte  ('.implode(', ', $failedvals).')');
		}
	}

	/**
	 * @param string $oe_val
	 * @param string $project_val
	 * @param string $phase_val
	 * @param string $service_val
	 * @return array
	 */
	protected function _checkVals ($oe_val, $project_val, $phase_val, $service_val) {
		$error = [];
		if ($service_val && ( filter_var($service_val, FILTER_VALIDATE_INT) === false )) {
			$error[] = 'service';
		}
		if ($phase_val && ( filter_var($phase_val, FILTER_VALIDATE_INT) === false )) {
			$error[] = 'phase';
		}
		if ($oe_val) {
			$oecheck = new organisationseinheit($oe_val);
			if ($oecheck->errormsg) {
				$error[] = 'OE';
			}
		}
		if ($project_val) {
			$procheck = new projekt($project_val);
			if ($procheck->errormsg) {
				$error[] = 'projekt';
			}
		}
		return $error;
	}

	/**
	 * @param array $data
	 * @return void
	 */
	protected function mapLehreIntern(&$data) {
		if ($data[self::AKTIVITAET] == 'LehreIntern') {
			$data[self::AKTIVITAET] = 'Lehre';
		}
	}

	/**
	 * @param array $data
	 * @return void
	 */
	protected function prepareZeitaufzeichnung($data) {
		$this->zeit->new = true;
		$this->zeit->beschreibung = NULL;
		$this->zeit->oe_kurzbz_1 = NULL;
		$this->zeit->projekt_kurzbz = NULL;
		$this->zeit->projektphase_id = NULL;
		$this->zeit->service_id = NULL;

		$this->zeit->insertamum = date('Y-m-d H:i:s');
		$this->zeit->updateamum = date('Y-m-d H:i:s');
		$this->zeit->updatevon = $this->user;
		$this->zeit->insertvon = $this->user;
		$this->zeit->uid = $data[self::USER];
		$this->zeit->aktivitaet_kurzbz = $data[self::AKTIVITAET];
		$this->zeit->start = $this->datum->formatDatum($data[self::STARTDT], 'Y-m-d H:i:s');
		$this->zeit->ende = $this->datum->formatDatum($data[self::ENDEDT], 'Y-m-d H:i:s');
		if (isset($data[self::BESCHREIBUNG])) {
			$this->zeit->beschreibung = $data[self::BESCHREIBUNG];
		}
		if (isset($data[self::OE])) {
			$this->zeit->oe_kurzbz_1 = $data[self::OE];
		}
		if (isset($data[self::PROJEKT])) {
			$this->zeit->projekt_kurzbz = $data[self::PROJEKT];
		}
		if (isset($data[self::PHASE])) {
			$this->zeit->projektphase_id = $data[self::PHASE];
		}
		if (isset($data[self::SERVICE])) {
			$this->zeit->service_id = $data[self::SERVICE];
		}
		$this->zeit->homeoffice = false;
		if (isset($data[self::HOMEOFFICE])) {
			$this->zeit->homeoffice = (strtolower($data[self::HOMEOFFICE]) == 'true');
			if (strtolower($data[self::HOMEOFFICE]) == 'true') {
				// check, ob homeoffice gemäß Bisverwendung
				$vonCSV = $this->datum->formatDatum($data[self::STARTDT], 'Y-m-d');

                $vbt = new vertragsbestandteil();
                $homeoffice = $vbt->hasHomeoffice($data[self::USER], $vonCSV);

                if ($homeoffice) {
                    $this->zeit->homeoffice = true;
                } else {
                    $this->addWarning($this->p->t("zeitaufzeichnung/homeofficeNichtErlaubt", [$vonCSV]));
                    $this->zeit->homeoffice = false;
                }
			}
		}
	}

	/**
	 * @param string $start datestring
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function checkImporttage($start) {
		$tag = $this->datum->formatDatum($start, 'Y-m-d');

		if (!in_array($tag, $this->importtage_array)) {
			$this->importtage_array[] = $tag;
			$this->zeit->deleteEntriesForUser($this->user, $tag);
		} else if ($this->ende_vorher < $this->zeit->start) {
			$this->savePause();
		}

		$this->ende_vorher = $this->zeit->ende;
	}

	/**
	 * @return void
	 */
	protected function savePause() {
		$pause = new zeitaufzeichnung();
		$pause->new = true;
		$pause->insertamum = date('Y-m-d H:i:s');
		$pause->updateamum = date('Y-m-d H:i:s');
		$pause->updatevon = $this->user;
		$pause->insertvon = $this->user;
		$pause->uid = $this->user;
		$pause->aktivitaet_kurzbz = 'Pause';
		$pause->start = $this->ende_vorher;
		$pause->ende = $this->zeit->start;
		$pause->beschreibung = '';
		$pause->homeoffice = $this->zeit->homeoffice;
		if(!$pause->save())
		{
			$this->addError($this->p->t("global/fehlerBeimSpeichernDerDaten").': '.$pause->errormsg, true);
		}
	}

	/**
	 * @param string $start datetimestring
	 * @param string $end datetimestring
	 * @return void
	 */
	protected function saveZeit($start, $end) {
		if ($start != $end) {
			if (!$this->zeit->save()) {
				$this->addError($this->p->t("global/fehlerBeimSpeichernDerDaten") . ': ' . $this->zeit->errormsg . '(' . $this->zeit->start . ')', true);
			} else {
				$this->anzahl++;
			}
		} else {
			$this->anzahl++;
		}
	}

	/**
	 * @return void
	 */
	protected function checkAndCleanup() {
		if ($this->anzahl > 0) {
			$this->addInfo($this->p->t("global/datenWurdenGespeichert") . ' (' . $this->anzahl . ')');
			foreach ($this->importtage_array as $ptag) {
				$this->zeit->cleanPausenForUser($this->user, $ptag);
			}
		}
	}

}
