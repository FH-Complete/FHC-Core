<?php
require_once('../../../include/datum.class.php');
require_once('../../../include/projekt.class.php');
require_once('../../../include/projektphase.class.php');
require_once('../../../include/zeitaufzeichnung.class.php');

/**
 * Description of zeitaufzeichnung_import
 *
 * @author chris
 */
class zeitaufzeichnung_import {

	protected $errors;
	protected $warnings;
	protected $infos;

	protected $p;
	protected $datum;

	protected $project;
	protected $phase;
	protected $limitdate;

	protected $zeit;


	/**
	 * @param phrasen $p The Translator object
	 */
	public function __construct($p) {
		$this->errors   = [];
		$this->warnings = [];
		$this->infos    = [];

		$this->p = $p;
		$this->datum = new datum();

		$this->project = new projekt();
		$this->phase = new projektphase();
		$this->limitdate = date('c', strtotime("+5 weeks"));

		$this->zeit = new zeitaufzeichnung();
	}


	/**
	 * @return boolean
	 */
	public function hasErrors() {
		return !empty($this->errors);
	}

	/**
	 * @return boolean
	 */
	public function hasWarnings() {
		return !empty($this->warnings);
	}

	/**
	 * @return boolean
	 */
	public function hasInfos() {
		return !empty($this->infos);
	}

	/**
	 * @return string
	 */
	public function ErrorsToHTML() {
		$html = '';
		foreach ($this->errors as $msg) {
			$html .= '<span style="color:red;"><b>' . $msg . '</b></span><br>' . "\n";
		}
		return $html;
	}

	/**
	 * @return string
	 */
	public function WarningsToHTML() {
		$html = '';
		foreach ($this->warnings as $msg) {
			$html .= '<span style="color:orange;"><b>' . $msg . '</b></span><br>' . "\n";
		}
		return $html;
	}

	/**
	 * @return string
	 */
	public function InfosToHTML() {
		$html = '';
		foreach ($this->infos as $msg) {
			$html .= '<span style="color:green;"><b>' . $msg . '</b></span><br>' . "\n";
		}
		return $html;
	}

	/**
	 * @return string
	 */
	public function OutputToHTML() {
		return $this->InfosToHTML() . $this->WarningsToHTML() . $this->ErrorsToHTML();
	}

	/**
	 * @param string $msg
	 * @return void
	 */
	protected function addError($msg) {
		$this->errors[] = $msg;
	}

	/**
	 * @param string $msg
	 * @return void
	 */
	protected function addWarning($msg) {
		$this->warnings[] = $msg;
	}

	/**
	 * @param string $msg
	 * @return void
	 */
	protected function addInfo($msg) {
		$this->infos[] = $msg;
	}


	/**
	 * @param string $uid The user id
	 * @param string $day "Y-m-d" formatted datestring
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function checkZeitsperren($uid, $day) {
		$zs = new zeitsperre();

		if (!$zs->getSperreByDate($uid, $day, null, zeitsperre::NUR_BLOCKIERENDE_ZEITSPERREN)) {
			throw new Exception($this->p->t("global/fehlerBeimSpeichernDerDaten") . ": Fehler beim Überprüfen der Zeitsperren");
		}

		if (count($zs->result) !== 0) {
			$zsdate = new DateTime($day);
			$zsdate = $zsdate->format('d.m.Y');
			throw new Exception($this->p->t("global/fehlerBeimSpeichernDerDaten") . ": " . $this->p->t("zeitaufzeichnung/zeitsperreVorhanden", [$zsdate, $zs->result[0]->zeitsperretyp_kurzbz]));
		}
	}

	/**
	 * @param string $date datetimestring
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function checkLimitdatum($date) {
		if ($this->datum->formatDatum($date, 'Y-m-d H:i:s') > $this->limitdate) {
			throw new Exception($this->p->t("global/fehlerBeimSpeichernDerDaten") . ': Eingabe nicht möglich da (' . $date . ') zu weit in der Zukunft liegt.');
		}
	}

	/**
	 * @param string $start datestring
	 * @param string $end datestring
	 * @param string $aktivitaet_kurzbz
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function checkDienstreise($start, $end, $aktivitaet_kurzbz) {
		$startDate = $this->datum->formatDatum($start, 'Y-m-d');
		$endDate = $this->datum->formatDatum($end, 'Y-m-d');
		if ($startDate != $endDate && $aktivitaet_kurzbz != "DienstreiseMT") {
			throw new Exception($this->p->t("global/fehlerBeimSpeichernDerDaten")
				.': Eingabe nicht möglich, da keine Zeitaufzeichnung über mehrere Tage erlaubt ist (ausgenommen Dienstreisen).');
		}
	}

	/**
	 * @param string $end timestring
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function checkTagesgenau($end) {
		$endTime = $this->datum->formatDatum($end, 'H:i:s');
		if ($endTime == '00:00:00') {
			throw new Exception($this->p->t("global/fehlerBeimSpeichernDerDaten")
				.': Bitte Arbeitszeiten gemäß Arbeitsaufzeichnung Leitfaden tagesgenau abgrenzen: Nur Eingaben von 00:00 bis 23:59 erlaubt!');
		}
	}

	/**
	 * @param string $projekt_kurzbz
	 * @param string $start datestring
	 * @param string $end datestring
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function checkProjectInterval($projekt_kurzbz, $start, $end) {
		if (!$this->project->checkProjectInCorrectTime($projekt_kurzbz, $start, $end)) {
			throw new Exception($this->p->t("global/fehlerBeimSpeichernDerDaten") . ': Eingabe nicht möglich, da angegebenes Anfangs und Enddatum nicht in den Projektzeitrahmen fällt: (' . $start . ') (' . $end . ')');
		}
	}

	/**
	 * @param string $phase The Projektphase ID
	 * @param string $start datestring
	 * @param string $end datestring
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function checkPhaseInterval($phase, $start, $end) {
		if (!$this->phase->checkProjectphaseInCorrectTime($phase, $start, $end)) {
			throw new Exception($this->p->t("global/fehlerBeimSpeichernDerDaten") . ': Eingabe nicht möglich, da angegebenes Anfangs und Enddatum nicht in den Projektphasenzeitrahmen fällt: (' . $start . ') (' . $end . ')');
		}
	}

	/**
	 * @param string $start datetime
	 * @param string $end datetime
	 * @return void
	 */
	protected function processPause($start, $end) {
		if (isset($_POST['genPause'])) {
			$p_start = $this->datum->formatDatum($start, 'Y-m-d H:i:s');
			$p_end = $this->datum->formatDatum($end, 'Y-m-d H:i:s');
			$this->checkPauseInArbeitszeit($p_start, $p_end);
			$this->checkPauseValid($p_start, $p_end);
			$this->savePauseGeneric($start, $end);
		}
	}

	/**
	 * @param string $start "Y-m-d H:i:s" formatted datetime
	 * @param string $end "Y-m-d H:i:s" formatted datetime
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function checkPauseInArbeitszeit($start, $end) {
		if ($this->zeit->start > $start || $this->zeit->ende < $end) {
			throw new Exception($this->p->t("global/fehlerBeimSpeichernDerDaten") . ': Pause außerhalb der Arbeitszeit');
		}
	}

	/**
	 * @param string $start "Y-m-d H:i:s" formatted datetime
	 * @param string $end "Y-m-d H:i:s" formatted datetime
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function checkPauseValid($start, $end) {
		if ($start > $end) {
			throw new Exception($this->p->t("global/fehlerBeimSpeichernDerDaten") . ': Fehlerhafte Pausenzeiten');
		}
	}

	/**
	 * @param string $start datetime
	 * @param string $end datetime
	 * @return void
	 */
	protected function savePauseGeneric($start, $end) {
		//Eintrag Arbeit bis zur Pause
		$ende = $this->zeit->ende;
		$this->zeit->ende = $this->datum->formatDatum($start, 'Y-m-d H:i:s');
		if (!$this->zeit->save()) {
			$this->addError($this->p->t("global/fehlerBeimSpeichernDerDaten") . ': ' . $this->zeit->errormsg);
		}
		//Eintrag für die Pause
		$pause = new zeitaufzeichnung();
		$pause->new = true;
		$pause->insertamum = date('Y-m-d H:i:s');
		$pause->updateamum = date('Y-m-d H:i:s');
		$pause->updatevon = $this->user;
		$pause->insertvon = $this->user;
		$pause->uid = $this->user;
		$pause->aktivitaet_kurzbz = 'Pause';
		$pause->homeoffice = $this->zeit->homeoffice;
		$pause->start = $this->datum->formatDatum($start, 'Y-m-d H:i:s');
		$pause->ende = $this->datum->formatDatum($end, 'Y-m-d H:i:s');
		$pause->beschreibung = '';
		if (!$pause->save()) {
			$this->addError($this->p->t("global/fehlerBeimSpeichernDerDaten") . ': ' . $pause->errormsg);
		}
		// Eintrag Arbeit ab der Pause
		if ($this->zeit->new == false) {
			$this->zeit->new = true;
			$this->zeit->insertamum = date('Y-m-d H:i:s');
			$this->zeit->insertvon = $this->user;
		}

		$this->zeit->start =  $this->datum->formatDatum($end, 'Y-m-d H:i:s');
		$this->zeit->ende = $ende;
	}

}
