<?php
require_once('zeitaufzeichnung_import.class.php');

/**
 * Description of zeitaufzeichnung_import_post
 *
 * @author chris
 */
class zeitaufzeichnung_import_post extends zeitaufzeichnung_import {

	protected $datum;

	protected $user;
	protected $edit;
	protected $data;


	/**
	 * @param phrasen $p The Translator object
	 * @param string $user The user ID
	 * @param boolean $edit Edit or create a new one
	 * @param array $data The array keys are:
	 *  - aktivitaet_kurzbz    string
	 *  - beschreibung         string
	 *  - bis                  datum
	 *  - bis_pause            datum
	 *  - homeoffice           boolean
	 *  - kunde_uid            string ID
	 *  - oe_kurzbz_1          string
	 *  - oe_kurzbz_2          string
	 *  - projekt_kurzbz       string
	 *  - projektphase_id      string ID
	 *  - service_id           string ID
	 *  - von                  datum
	 *  - von_pause            datum
	 *  - zeitaufzeichnung_id  string ID
	 * @param string $filename
	 */
	public function __construct(phrasen $p, $user, $edit, $data) {
		parent::__construct($p);

		$this->user = $user;
		$this->edit = $edit;
		$this->data = $data;
	}


	/**
	 * @return string
	 */
	public function ErrorsToHTML() {
		$html = '';
		foreach ($this->errors as $msg) {
			$html .= '<span id="triggerPhasenReset" style="color:red;"><b>' . $msg . '</b></span><br>' . "\n";
		}
		return $html;
	}


	/**
	 * @return void
	 */
	public function import() {
		try {
			$this->checkNew($this->data['zeitaufzeichnung_id']);
			$this->prepareZeitaufzeichnung($this->data['aktivitaet_kurzbz'], $this->data['von'], $this->data['bis'], $this->data['beschreibung'], $this->data['oe_kurzbz_1'], $this->data['oe_kurzbz_2'], $this->data['projekt_kurzbz'], $this->data['projektphase_id'], $this->data['homeoffice'], $this->data['service_id'], $this->data['kunde_uid']);
			$this->checkZeitsperren($this->user, $this->datum->formatDatum($this->data['von'], 'Y-m-d'));
			$this->checkProjectInterval($this->data['projekt_kurzbz'], $this->data['von'], $this->data['bis']);
			$this->checkLimitdatum($this->data['von']);
			$this->checkLimitdatum($this->data['bis']);
			$this->checkPhaseInterval($this->data['projektphase_id'], $this->data['von'], $this->data['bis']);
			$this->checkDienstreise($this->data['von'], $this->data['bis'], $this->data['aktivitaet_kurzbz']);
			$this->checkTagesgenau($this->data['bis']);
			$this->processPause($this->data['von_pause'], $this->data['bis_pause']);
			$this->saveZeit();
		} catch (Exception $ex) {
			$this->addError($ex->getMessage());
		}
	}

	/**
	 * @param string $zeitaufzeichnung_id
	 * @return void
	 */
	protected function checkNew($zeitaufzeichnung_id) {
		if($this->edit) {
			if(!$this->zeit->load($zeitaufzeichnung_id))
				die($this->p->t("global/fehlerBeimLadenDesDatensatzes"));

			$this->zeit->new = false;
		} else {
			$this->zeit->new = true;
			$this->zeit->insertamum = date('Y-m-d H:i:s');
			$this->zeit->insertvon = $this->user;
		}
	}

	/**
	 * @param string $aktivitaet_kurzbz
	 * @param string $von datetime
	 * @param string $bis datetime
	 * @param string $beschreibung
	 * @param string $oe_kurzbz_1
	 * @param string $oe_kurzbz_2
	 * @param string $projekt_kurzbz
	 * @param string $projektphase_id
	 * @param boolean $homeoffice
	 * @param string $service_id
	 * @param string $kunde_uid
	 * @return void
	 */
	protected function prepareZeitaufzeichnung($aktivitaet_kurzbz, $von, $bis, $beschreibung, $oe_kurzbz_1, $oe_kurzbz_2, $projekt_kurzbz, $projektphase_id, $homeoffice, $service_id, $kunde_uid) {
		$this->zeit->uid = $this->user;
		$this->zeit->aktivitaet_kurzbz = $aktivitaet_kurzbz;
		$this->zeit->start = $this->datum->formatDatum($von, 'Y-m-d H:i:s');
		$this->zeit->ende = $this->datum->formatDatum($bis, 'Y-m-d H:i:s');
		$this->zeit->beschreibung = $beschreibung;
		$this->zeit->oe_kurzbz_1 = $oe_kurzbz_1;
		$this->zeit->oe_kurzbz_2 = $oe_kurzbz_2;
		$this->zeit->updateamum = date('Y-m-d H:i:s');
		$this->zeit->updatevon = $this->user;
		$this->zeit->projekt_kurzbz = $projekt_kurzbz;
		$this->zeit->projektphase_id = $projektphase_id;
		$this->zeit->homeoffice = $homeoffice;
		$this->zeit->service_id = $service_id;
		$this->zeit->kunde_uid = $kunde_uid;
	}

	/**
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function saveZeit() {
		if (!$this->zeit->save()) {
			throw new Exception($this->p->t("global/fehlerBeimSpeichernDerDaten") . ': ' . $this->zeit->errormsg);
		} else if (!$this->hasErrors()) {
			$this->addInfo($this->p->t("global/datenWurdenGespeichert"));
		}
	}

}
