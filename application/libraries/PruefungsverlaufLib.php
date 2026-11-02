<?php
/**
 * Copyright (C) 2026 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * The exam history of one student in one course: the order of the attempts, the attempt count
 * and the limits. This class is the only source of the examination rules. The controller and the
 * client only read the result.
 */
class PruefungsverlaufLib
{
	/** The first attempt: the original assessment, which is the transcript grade itself. */
	const ROLLE_ERSTANTRITT = 'erstantritt';

	/** Each attempt after the first one. */
	const ROLLE_PRUEFUNG = 'pruefung';

	/** The last attempt. The examination rules require a commission for it. */
	const ROLLE_KOMMISSIONELL = 'kommissionell';

	/** Maps an attempt number to the old exam type. Kept for backward compatibility. */
	private static $_legacyTypen = [1 => 'Termin1', 2 => 'Termin2', 3 => 'Termin3'];

	private $_ci;
	private $_noteCache = [];
	private $_specialNotes = null;
	private $_typen = null;

	public function __construct()
	{
		$this->_ci =& get_instance();
		$this->_ci->load->model('education/LePruefung_model', 'LePruefungModel');
		$this->_ci->load->model('education/Note_model', 'NoteModel');
		$this->_ci->load->model('education/Pruefungstyp_model', 'PruefungstypModel');
		$this->_ci->load->config('noten');
	}

	/**
	 * @param mixed $lvNote       counts as the implicit first attempt while no exam counts
	 * @param mixed $zeugnisNote  a credited grade makes all exams impossible
	 * @return stdClass
	 */
	public function getVerlauf($student_uid, $lv_id, $sem_kurzbz, $lvNote = null, $zeugnisNote = null)
	{
		return $this->buildVerlauf($this->getPruefungen($student_uid, $lv_id, $sem_kurzbz), $lvNote, $zeugnisNote);
	}

	/** All exams of one student in one course, in chronological order. @return array */
	public function getPruefungen($student_uid, $lv_id, $sem_kurzbz)
	{
		$result = $this->_ci->LePruefungModel->getPruefungenByUidTypLvStudiensemester($student_uid, null, $lv_id, $sem_kurzbz);
		if (isError($result) || !hasData($result)) return [];

		return $this->sortPruefungen(getData($result));
	}

	/** Sort keys: datum, then tbl_pruefungstyp.sort (old rows without a date), then pruefung_id. */
	public function sortPruefungen($pruefungen)
	{
		$pruefungen = array_values($pruefungen);
		$typen = $this->getTypen();

		usort($pruefungen, function ($a, $b) use ($typen) {
			$da = substr((string) $a->datum, 0, 10);
			$db = substr((string) $b->datum, 0, 10);

			if ($da !== $db) {
				if ($da === '') return -1;
				if ($db === '') return 1;
				return ($da < $db) ? -1 : 1;
			}

			$sa = isset($typen[$a->pruefungstyp_kurzbz]) ? (int) $typen[$a->pruefungstyp_kurzbz]->sort : 0;
			$sb = isset($typen[$b->pruefungstyp_kurzbz]) ? (int) $typen[$b->pruefungstyp_kurzbz]->sort : 0;
			if ($sa !== $sb) return ($sa < $sb) ? -1 : 1;

			return ((int) $a->pruefung_id < (int) $b->pruefung_id) ? -1 : 1;
		});

		return $pruefungen;
	}

	/**
	 * Builds the history from a list of exams. Each exam gets: position, zaehlt, antritt_nr (null
	 * if the exam does not count) and terminal.
	 *
	 * @return stdClass
	 */
	public function buildVerlauf($pruefungen, $lvNote = null, $zeugnisNote = null)
	{
		$pruefungen = $this->sortPruefungen($pruefungen);

		$antrittCount = 0;
		$terminal = false;
		$eintraege = [];

		foreach ($pruefungen as $i => $p) {
			$zaehlt = $this->zaehltAlsAntritt($p->note, $p->pruefungstyp_kurzbz);
			if ($zaehlt) $antrittCount++;

			// A kommissionelle exam closes the chain, also if its grade uses no attempt. A student
			// with a kommissionelle Prüfung gets no further exam from this tool.
			$kommissionell = $this->istKommissionell($p->pruefungstyp_kurzbz);
			if ($kommissionell) $terminal = true;

			$eintrag = clone $p;
			$eintrag->position = $i + 1;
			$eintrag->zaehlt = $zaehlt;
			$eintrag->antritt_nr = $zaehlt ? $antrittCount : null;
			$eintrag->kommissionell = $kommissionell;
			$eintraege[] = $eintrag;
		}

		// if no exam counts, the course grade itself is the first attempt
		$implizit = ($antrittCount === 0 && $lvNote !== null && $this->zaehltAlsAntritt($lvNote));
		if ($implizit) $antrittCount = 1;

		$maxAntritte = $this->getMaxAntritte();

		// credited: the row stays visible, but you cannot enter anything
		$angerechnet = $this->istAnrechnungsnote($zeugnisNote);

		$verlauf = new stdClass();
		$verlauf->pruefungen = $eintraege;
		$verlauf->antrittCount = $antrittCount;
		$verlauf->maxAntritte = $maxAntritte;
		$verlauf->terminal = $terminal;
		$verlauf->angerechnet = $angerechnet;
		$verlauf->impliziterErstantritt = $implizit;
		$verlauf->canAdd = !$terminal && !$angerechnet && $antrittCount < $maxAntritte;

		// after the first exam each new entry is a repeat attempt
		$verlauf->erstantrittMoeglich = !$angerechnet && (count($eintraege) === 0);

		// The last attempt of the chain is kommissionell (§17 Abs 1). The role therefore follows
		// from the position, and the user interface offers no type to select.
		if (count($eintraege) === 0 && !$implizit) {
			$verlauf->naechsteRolle = self::ROLLE_ERSTANTRITT;
		} elseif (($antrittCount + 1) >= $maxAntritte) {
			$verlauf->naechsteRolle = self::ROLLE_KOMMISSIONELL;
		} else {
			$verlauf->naechsteRolle = self::ROLLE_PRUEFUNG;
		}

		return $verlauf;
	}

	/** 'angerechnet' or 'intern angerechnet'. The TRANSCRIPT grade decides. @return bool */
	public function istAnrechnungsnote($note)
	{
		if ($note === null || $note === '') return false;
		return in_array($note, $this->getSpecialNotes()['anrechnung']);
	}

	/**
	 * All attempts that count, the kommissionelle attempt included. The explicit configuration
	 * wins. Without it the number comes from the old flags: the original assessment, one attempt
	 * for each active repeat, and the kommissionelle attempt.
	 *
	 * @return int
	 */
	public function getMaxAntritte()
	{
		$max = $this->_ci->config->item('CIS_GESAMTNOTE_MAX_ANTRITTE');
		if (is_numeric($max) && (int) $max > 0) return (int) $max;

		$max = 1;
		if (defined('CIS_GESAMTNOTE_PRUEFUNG_TERMIN2') && CIS_GESAMTNOTE_PRUEFUNG_TERMIN2) $max++;
		if (defined('CIS_GESAMTNOTE_PRUEFUNG_TERMIN3') && CIS_GESAMTNOTE_PRUEFUNG_TERMIN3) $max++;
		if (defined('CIS_GESAMTNOTE_PRUEFUNG_KOMMPRUEF') && CIS_GESAMTNOTE_PRUEFUNG_KOMMPRUEF) $max++;
		return $max;
	}

	/**
	 * The NOTE decides, therefore the same type can occur more than one time. The type decides in
	 * one case only: a type in PRUEFUNG_TYPEN_OHNE_ANTRITT never uses an attempt.
	 *
	 * @return bool
	 */
	public function zaehltAlsAntritt($note, $typ = null)
	{
		if ($typ !== null && in_array($typ, $this->getTypenOhneAntritt())) return false;
		if ($note === null || $note === '') return false;
		if (in_array($note, $this->getSpecialNotes()['ohneAntritt'])) return false;

		// a credited grade is not an assessment, therefore it uses no attempt
		if ($this->istAnrechnungsnote($note)) return false;

		$noteRow = $this->getNote($note);
		return $noteRow ? (bool) $noteRow->lehre : false;
	}

	/** Tells you if an exam takes place before a commission. @return bool */
	public function istKommissionell($typ)
	{
		$typen = $this->_ci->config->item('PRUEFUNG_KOMMISSIONELL_TYPEN');
		if (!is_array($typen)) $typen = ['kommPruef', 'zusKommPruef'];
		return in_array($typ, $typen);
	}

	/** The exam types that never use an attempt. @return array */
	public function getTypenOhneAntritt()
	{
		$typen = $this->_ci->config->item('PRUEFUNG_TYPEN_OHNE_ANTRITT');
		return is_array($typen) ? $typen : ['zusKommPruef'];
	}

	/**
	 * The type for an attempt number. The student administration still reads this column, so the
	 * value must be correct. The last attempt gets the kommissionelle type, the attempts before it
	 * get the old Termin types.
	 *
	 * @return string
	 */
	public function legacyTypFuerAntritt($antrittNr)
	{
		$antrittNr = max(1, (int) $antrittNr);
		$typen = $this->getTypen();

		$maxAntritte = $this->getMaxAntritte();
		$kommissionell = $this->_ci->config->item('PRUEFUNG_TYP_KOMMISSIONELL');
		if (!is_string($kommissionell) || $kommissionell === '') $kommissionell = 'kommPruef';

		if ($maxAntritte > 1 && $antrittNr >= $maxAntritte && isset($typen[$kommissionell])) {
			return $kommissionell;
		}

		for ($n = $antrittNr; $n >= 1; $n--) {
			if (isset(self::$_legacyTypen[$n]) && isset($typen[self::$_legacyTypen[$n]])) {
				return self::$_legacyTypen[$n];
			}
		}

		return self::$_legacyTypen[1];
	}

	/**
	 * The old type for a new repeat attempt. It is never lower than Termin2, also if the first
	 * exam does not use an attempt.
	 *
	 * @return string
	 */
	public function legacyTypFuerWiederholung($verlauf)
	{
		return $this->legacyTypFuerAntritt(max(2, $verlauf->antrittCount + 1));
	}

	/** @return array{entschuldigt: mixed, ohneAntritt: array, anrechnung: array, limitMap: array} */
	public function getSpecialNotes()
	{
		if ($this->_specialNotes !== null) return $this->_specialNotes;

		$cfgEntschuldigt = $this->_ci->config->item('NOTE_ENTSCHULDIGT');
		$cfgOhneAntritt = $this->_ci->config->item('NOTEN_OHNE_ANTRITT');
		$cfgLimitMap = $this->_ci->config->item('NOTEN_OCCURANCE_LIMIT_MAP');
		$bezeichnungen = $this->_ci->config->item('NOTEN_OHNE_ANTRITT_BEZEICHNUNGEN');
		if (!is_array($cfgOhneAntritt)) $cfgOhneAntritt = [];
		if (!is_array($cfgLimitMap)) $cfgLimitMap = [];
		if (!is_array($bezeichnungen)) $bezeichnungen = [];

		$resEnt = $this->_ci->NoteModel->getEntschuldigtNote();
		$entschuldigt = (!isError($resEnt) && hasData($resEnt)) ? getData($resEnt)[0]->note : $cfgEntschuldigt;

		// The name wins against the configured key. Do NOT merge the two sets: the same key has a
		// different meaning in each installation (17 is 'entschuldigt' here, but 'nicht zugelassen'
		// in the standard data).
		$ohneAntritt = [];
		foreach ($bezeichnungen as $bezeichnung) {
			$note = $this->getNoteByBezeichnung($bezeichnung);
			if ($note !== null && !in_array($note, $ohneAntritt)) $ohneAntritt[] = $note;
		}
		if (count($ohneAntritt) === 0) $ohneAntritt = $cfgOhneAntritt;
		if ($entschuldigt !== null && !in_array($entschuldigt, $ohneAntritt)) $ohneAntritt[] = $entschuldigt;

		// move the configured limit for 'entschuldigt' to the key that was found
		$limitMap = [];
		foreach ($cfgLimitMap as $k => $v) {
			$limitMap[($k == $cfgEntschuldigt) ? $entschuldigt : $k] = $v;
		}

		$this->_specialNotes = [
			'entschuldigt' => $entschuldigt,
			'ohneAntritt' => array_values($ohneAntritt),
			'anrechnung' => $this->resolveNoten('NOTEN_ANRECHNUNG_BEZEICHNUNGEN', 'NOTEN_ANRECHNUNG'),
			'limitMap' => $limitMap
		];
		return $this->_specialNotes;
	}

	/**
	 * The occurrence limit from NOTEN_OCCURANCE_LIMIT_MAP. $excludePruefungId skips the record
	 * that the user edits at this moment.
	 *
	 * @return bool
	 */
	public function ueberschreitetNotenLimit($pruefungen, $note, $excludePruefungId = null)
	{
		$limitMap = $this->getSpecialNotes()['limitMap'];

		$limit = null;
		foreach ($limitMap as $limitNote => $limitVal) {
			if ($limitNote == $note) { $limit = $limitVal; break; }
		}
		if ($limit === null) return false;

		$count = 0;
		foreach ($pruefungen as $p) {
			if ($excludePruefungId !== null && $p->pruefung_id == $excludePruefungId) continue;
			if ($p->note == $note) $count++;
		}

		return ($count + 1) > $limit;
	}

	/** An exam with a later date locks the grade. You can still correct the date. @return bool */
	public function hatSpaeterenTermin($verlauf, $pruefung_id)
	{
		$current = null;
		foreach ($verlauf->pruefungen as $p) {
			if ($p->pruefung_id == $pruefung_id) { $current = $p; break; }
		}
		if ($current === null) return false;

		foreach ($verlauf->pruefungen as $p) {
			if ($p->pruefung_id == $current->pruefung_id) continue;
			if ($p->position > $current->position) return true;
		}

		return false;
	}

	/**
	 * tbl_pruefungstyp as a map. It sorts old rows, and it limits the legacy type to the types
	 * that exist. A type that does not exist breaks the foreign key. Termin3 is absent from the
	 * standard data.
	 *
	 * @return array
	 */
	private function getTypen()
	{
		if ($this->_typen !== null) return $this->_typen;

		$this->_typen = [];
		$result = $this->_ci->PruefungstypModel->load();
		if (!isError($result) && hasData($result)) {
			foreach (getData($result) as $typ) {
				$this->_typen[$typ->pruefungstyp_kurzbz] = $typ;
			}
		}

		return $this->_typen;
	}

	/** Finds the grades by their name. The configured keys are the fallback only. @return array */
	private function resolveNoten($bezeichnungKey, $pkKey)
	{
		$bezeichnungen = $this->_ci->config->item($bezeichnungKey);
		if (!is_array($bezeichnungen)) $bezeichnungen = [];

		$noten = [];
		foreach ($bezeichnungen as $bezeichnung) {
			$note = $this->getNoteByBezeichnung($bezeichnung);
			if ($note !== null && !in_array($note, $noten)) $noten[] = $note;
		}

		if (count($noten) === 0) {
			$fallback = $this->_ci->config->item($pkKey);
			if (is_array($fallback)) $noten = $fallback;
		}

		return array_values($noten);
	}

	/**
	 * @return stdClass|null
	 */
	private function getNote($note)
	{
		if (array_key_exists($note, $this->_noteCache)) return $this->_noteCache[$note];

		$result = $this->_ci->NoteModel->load($note);
		$this->_noteCache[$note] = (!isError($result) && hasData($result)) ? getData($result)[0] : null;
		return $this->_noteCache[$note];
	}

	/**
	 * @return mixed|null
	 */
	private function getNoteByBezeichnung($bezeichnung)
	{
		$result = $this->_ci->NoteModel->loadWhere(['bezeichnung' => $bezeichnung]);
		return (!isError($result) && hasData($result)) ? getData($result)[0]->note : null;
	}
}
