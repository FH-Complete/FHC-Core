<?php
/**
 * Description of zeitaufzeichnung_csv_import
 *
 * @author bambi
 */
class zeitaufzeichnung_csv_import {
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
	
	protected $tmpname;
	protected $fh;

	protected $errors;
	protected $infos;
	
	protected $anzahl;
	protected $importtage_array;
	protected $ende_vorher;

	protected $zeit;
	protected $user;

	protected $project_kurzbz_array;
	protected $projectphasen_kurzbz_array;

	protected $sperrdatum;
	protected $limitdatum;
	
	protected $projects_of_user;
	protected $projektph_of_user;
	
	protected $datum;

	protected $p;

	protected $data;
	protected $current_line;

	protected $homeoffice;

	public function __construct(phrasen $p, zeitaufzeichnung $zeit, 
		array $project_kurzbz_array, array $projectphasen_kurzbz_array, 
		$sperrdatum, $limitdatum, projekt $projects_of_user, 
		projektphase $projektph_of_user, datum $datum, $user) {
		$this->p        = $p;
		$this->zeit     = $zeit;
		
		$this->project_kurzbz_array         = $project_kurzbz_array;
		$this->projectphasen_kurzbz_array   = $projectphasen_kurzbz_array;
		
		$this->sperrdatum = $sperrdatum;
		$this->limitdatum = $limitdatum;
		
		$this->projects_of_user     = $projects_of_user;
		$this->projektph_of_user    = $projektph_of_user;
		
		$this->datum = $datum;
		
		$this->user     = $user;
		$this->errors   = array();
		$this->infos    = array();
	}
	
	public function parseCSV() {
		$this->tmpname = $_FILES['csv']['tmp_name'];
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
	
	public function hasErrors() {
		return !empty($this->errors);
	}

	public function hasInfos() {
		return !empty($this->infos);
	}
	
	public function ErrorsToHTML() {
		$html = '';
		foreach ($this->errors as $msg) {
			$html .= '<span style="color:red;"><b>' . $msg . '</b></span><br>' . "\n";
		}
		return $html;
	}

	public function InfosToHTML() {
		$html = '';
		foreach ($this->infos as $msg) {
			$html .= '<span style="color:green;"><b>' . $msg . '</b></span><br>' . "\n";
		}
		return $html;
	}   
	
	protected function addError($msg, $prepend_current_line=false) {
		if( $prepend_current_line ) {
			$msg = 'Zeile ' . $this->current_line . ' - ' . $msg;
		}
		$this->errors[] = $msg;
	}
	
	protected function addInfo($msg) {
		$this->infos[] = $msg;
	}
	
	protected function checkMimeType() {
		$mimeType = mime_content_type($this->tmpname);
		if( $mimeType !== 'text/plain' ) {
			throw new Exception('Datei ist nicht im CSV Format.');
		}
	}

	protected function openFileForReading() {
		if( false === ($this->fh = fopen($this->tmpname, 'r')) ) 
		{
			throw new Exception('CSV - Datei konnte nicht zum lesen geöffnet werden.');
		}
	}
	
	protected function checkEncoding() {
		$filecontent = file_get_contents($this->tmpname);
		if( !mb_detect_encoding($filecontent, 'UTF-8', true) ) {
			throw new Exception('Datei konnte nicht importiert werden. Encoding ist nicht UTF-8!');
		}
	}

	protected function iterateRows() {
		set_time_limit(0);
		$this->anzahl = 0;
		$this->importtage_array = array();
		$this->ende_vorher = date('Y-m-d H:i:s');

		$this->data = null;
		$this->current_line = 0;
		while(($this->data = fgetcsv($this->fh, 1000, ';', '"')) !== FALSE)
		{
			if( false !== strpos($this->data[self::USER], '#') ) {
				// ignore lines starting with #
				continue;
			}
			$this->current_line++;
			$this->processData();
		}
	}

	protected function processData() {
		try {
			$this->checkUser();
			$this->checkProject();
			$this->checkPhase();
			$this->initData();
			$this->checkZeitsperren();
			$this->checkSperrdatum();
			$this->checkLimitdatum();
			$this->checkDienstreise();
			$this->checkTagesgenau();
			$this->checkProjectInterval();
			$this->checkPhaseInterval();
			$this->checkVals();
			$this->mapLehreIntern();
			$this->prepareZeitaufzeichnung();
			$this->checkImporttage();
			$this->saveZeit();
		} catch (Exception $ex) {
			$this->addError($ex->getMessage(), true);
		}
	}

	protected function checkUser() {
		if( $this->data[self::USER] !== $this->user && (strpos($this->data[self::USER],'#') !== false) ) 
		{
			throw new Exception($this->p->t("global/fehlerBeimSpeichernDerDaten").': Falsche UID nicht importiert ('.$this->data[self::USER].')');
		}
	}

	protected function checkProject() {
		if(!empty($this->data[self::PROJEKT]) && !in_array($this->data[self::PROJEKT], $this->project_kurzbz_array) && empty($this->data[self::PHASE]))
		{
			throw new Exception($this->p->t("global/fehlerBeimSpeichernDerDaten").': Eingabe nicht möglich, da Sie folgendem Projekt entweder nicht zugewiesen sind oder das Projekt schon abgeschlossen wurde: ('.$this->data[self::PROJEKT].')');
		}
		
	}

	protected function checkPhase() {
		if(!empty($this->data[self::PHASE]) && !in_array($this->data[self::PHASE], $this->projectphasen_kurzbz_array))
		{
			throw new Exception($this->p->t("global/fehlerBeimSpeichernDerDaten").': Eingabe nicht möglich, da Sie folgender Projektphase entweder nicht zugewiesen sind oder die Projektphase schon abgeschlossen wurde: ('.$this->data[self::PHASE].')');
		}
	}

	protected function initData() {
		if (!isset($this->data[self::OE])) {
			$this->data[self::OE] = NULL;
		}
		if (!isset($this->data[self::PROJEKT])) {
			$this->data[self::PROJEKT] = NULL;
		}
		if (!isset($this->data[self::PHASE])) {
			$this->data[self::PHASE] = NULL;
		}
		if (!isset($this->data[self::SERVICE])) {
			$this->data[self::SERVICE] = NULL;
		}
	}
	
	protected function checkZeitsperren() {
		$zscheck = checkZeitsperren($this->p, $this->user, $this->datum->formatDatum($this->data[self::STARTDT], $format = 'Y-m-d'));
		if ($zscheck['status'] === false) {
			throw new Exception($this->p->t("global/fehlerBeimSpeichernDerDaten") . ": " . $zscheck['msg']);
		}
	}
	
	protected function checkSperrdatum() {
		if ($this->datum->formatDatum($this->data[self::STARTDT], $format='Y-m-d H:i:s') < $this->sperrdatum) {
			throw new Exception($this->p->t("global/fehlerBeimSpeichernDerDaten")
				.': Eingabe nicht möglich da vor dem Sperrdatum ('.$this->data[self::STARTDT].')');
		}
		
	}
	
	protected function checkLimitdatum() {
		if ($this->datum->formatDatum($this->data[self::STARTDT], $format='Y-m-d H:i:s') > $this->limitdatum) {
			throw new Exception($this->p->t("global/fehlerBeimSpeichernDerDaten")
				.': Eingabe nicht möglich da ('.$this->data[self::STARTDT].') zu weit in der Zukunft liegt.');
		}        
	}
	
	protected function checkDienstreise() {
		$vonCSV = $this->datum->formatDatum($this->data[self::STARTDT], $format='Y-m-d');
		$bisCSV = $this->datum->formatDatum($this->data[self::ENDEDT], $format='Y-m-d');
		$dateVonCSV = new DateTime($vonCSV);
		$dateBisCSV = new DateTime($bisCSV);
		if ($dateVonCSV!=$dateBisCSV && $this->data[self::AKTIVITAET]!="DienstreiseMT")
		{
			throw new Exception($this->p->t("global/fehlerBeimSpeichernDerDaten")
				.': Eingabe nicht möglich, da keine Zeitaufzeichnung über mehrere Tage erlaubt ist (ausgenommen Dienstreisen).');
		}
	}
	
	protected function checkTagesgenau() {
		$bisHour = $this->datum->formatDatum($this->data[self::ENDEDT], $format = 'H:i:s');
		if ($bisHour == '00:00:00') {
			throw new Exception($this->p->t("global/fehlerBeimSpeichernDerDaten")
				.': Bitte Arbeitszeiten gemäß Arbeitsaufzeichnung Leitfaden tagesgenau abgrenzen: Nur Eingaben von 00:00 bis 23:59 erlaubt!');
		}
	}
	
	protected function checkProjectInterval() {
		if (empty($this->data[self::PHASE]) && !empty($this->data[self::PROJEKT]) 
			&& !$this->projects_of_user->checkProjectInCorrectTime($this->data[self::PROJEKT], $this->data[self::STARTDT], $this->data[self::ENDEDT]))
		{
			throw new Exception($this->p->t("global/fehlerBeimSpeichernDerDaten")
				.': Eingabe nicht möglich, da angegebenes Anfangs und Enddatum nicht in den Projektzeitrahmen fällt: ('
				.$this->data[self::STARTDT].') ('.$this->data[self::ENDEDT].')');
		}
	}
	
	protected function checkPhaseInterval() {
		if (!empty($this->data[self::PHASE]) && !$this->projektph_of_user->checkProjectphaseInCorrectTime($this->data[self::PHASE], $this->data[self::STARTDT], $this->data[self::ENDEDT]))
		{
			throw new Exception($this->p->t("global/fehlerBeimSpeichernDerDaten")
				.': Eingabe nicht möglich, da angegebenes Anfangs und Enddatum nicht in den Projektphasenzeitrahmen fällt: ('
				.$this->data[self::STARTDT].') ('.$this->data[self::ENDEDT].')');
		}
	}

	protected function checkVals() {
		$failedvals = $this->_checkVals($this->data[self::OE],$this->data[self::PROJEKT],$this->data[self::PHASE],$this->data[self::SERVICE]);
		if( count($failedvals) > 0 )
		{
			throw new Exception($this->p->t("global/fehlerBeimSpeichernDerDaten").': Fehlerhafte Werte  ('.implode(', ', $failedvals).')');
		}
	}

	protected function _checkVals ($oe_val, $project_val, $phase_val, $service_val) {
		$error = array();
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

	protected function mapLehreIntern() {
		if ($this->data[self::AKTIVITAET] == 'LehreIntern') {
			$this->data[self::AKTIVITAET] = 'Lehre';
		}
	}

	protected function prepareZeitaufzeichnung() {
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
		$this->zeit->uid = $this->data[self::USER];
		$this->zeit->aktivitaet_kurzbz = $this->data[self::AKTIVITAET];
		$this->zeit->start = $this->datum->formatDatum($this->data[self::STARTDT], $format='Y-m-d H:i:s');
		$this->zeit->ende = $this->datum->formatDatum($this->data[self::ENDEDT], $format='Y-m-d H:i:s');
		if (isset($this->data[self::BESCHREIBUNG])) {
			$this->zeit->beschreibung = $this->data[self::BESCHREIBUNG];
		}
		if (isset($this->data[self::OE])) {
			$this->zeit->oe_kurzbz_1 = $this->data[self::OE];
		}
		if (isset($this->data[self::PROJEKT])) {
			$this->zeit->projekt_kurzbz = $this->data[self::PROJEKT];
		}
		if (isset($this->data[self::PHASE])) {
			$this->zeit->projektphase_id = $this->data[self::PHASE];
		}
		if (isset($this->data[self::SERVICE])) {
			$this->zeit->service_id = $this->data[self::SERVICE];
		}
		$this->zeit->homeoffice = false;
		if (isset($this->data[self::HOMEOFFICE])) {
			$this->zeit->homeoffice = (strtolower($this->data[self::HOMEOFFICE]) == 'true');
			if (strtolower($this->data[self::HOMEOFFICE]) == 'true') {
				// check, ob homeoffice gemäß Bisverwendung
				$vonCSV = $this->datum->formatDatum($this->data[self::STARTDT], $format = 'Y-m-d');
				$verwendung = new bisverwendung();
				$verwendung->getVerwendungDatum($this->data[self::USER], $vonCSV);

				foreach ($verwendung->result as $v) {
					if ($v->homeoffice) {
						$this->zeit->homeoffice = true;
					} else {
						$this->addError($this->p->t("zeitaufzeichnung/homeofficeNichtErlaubt", ($vonCSV)));
						$this->zeit->homeoffice = false;
					}
				}
			}
		}
	}
	
	protected function checkImporttage() {
		$tag = $this->datum->formatDatum($this->data[self::STARTDT], $format='Y-m-d');

		if(!in_array($tag, $this->importtage_array))
		{
			$this->importtage_array[] = $tag;
			$this->zeit->deleteEntriesForUser($this->user, $tag);
			$tag_aktuell = $tag;
		}
		else if ($this->ende_vorher < $this->zeit->start) 
		{
			$this->savePause();
		}
		
		$this->ende_vorher = $this->zeit->ende;
	}
	
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

	protected function saveZeit() {
		if($this->data[self::STARTDT] != $this->data[self::ENDEDT])
		{
			if(!$this->zeit->save())
			{
				$this->addError($this->p->t("global/fehlerBeimSpeichernDerDaten").': '.$this->zeit->errormsg.'('.$this->zeit->start.')', true);
			}
			else {
				$this->anzahl++;
			}
		}
		else {
			$this->anzahl++;
		}
	}
	
	protected function checkAndCleanup() {
		if($this->anzahl>0)
		{
			$this->addInfo($this->p->t("global/datenWurdenGespeichert").' ('.$this->anzahl.')');
			foreach ($this->importtage_array as $ptag)
			{
				$this->zeit->cleanPausenForUser($this->user, $ptag);
			}
		}
	}
}
