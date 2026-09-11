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

	/** Fallback for PRUEFUNG_TYP_JE_ANTRITT. */
	private static $_typJeAntrittVorgabe = [1 => 'Termin1', 2 => 'Termin2', 3 => 'Termin3'];

	private $_ci;
	private $_noteCache = [];
	private $_specialNotes = null;
	private $_typen = null;

	public function __construct()
	{
		$this->_ci =& get_instance();
		$this->_ci->load->model('education/LePruefung_model', 'LePruefungModel');
		$this->_ci->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');
		$this->_ci->load->model('education/Note_model', 'NoteModel');
		$this->_ci->load->model('education/Pruefungstyp_model', 'PruefungstypModel');
		$this->_ci->load->config('noten');
	}

	/** Builds the history. Each exam gets position, zaehlt, antritt_nr and kommissionell. @return stdClass */
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

		// deciding grade: last counting attempt, else the course grade
		$aktuelleNote = null;
		foreach ($eintraege as $eintrag) {
			if ($eintrag->zaehlt) $aktuelleNote = $eintrag->note;
		}
		if ($aktuelleNote === null && $implizit) $aktuelleNote = $lvNote;

		// a pass closes the chain: a final grade always, any other positive one unless
		// CIS_GESAMTNOTE_NOTENVERBESSERUNG is on
		$bestanden = $this->istAbschliessendeNote($aktuelleNote)
			|| (!$this->darfVerbessern() && $this->istPositiveNote($aktuelleNote));

		if ($bestanden) $terminal = true;

		$maxAntritte = $this->getMaxAntritte();

		// credited: the row stays visible, but you cannot enter anything
		$angerechnet = $this->istAnrechnungsnote($zeugnisNote);

		$verlauf = new stdClass();
		$verlauf->pruefungen = $eintraege;
		$verlauf->antrittCount = $antrittCount;
		$verlauf->maxAntritte = $maxAntritte;
		$verlauf->terminal = $terminal;
		// closed by a pass, not by the attempt limit
		$verlauf->bestanden = $bestanden;
		$verlauf->angerechnet = $angerechnet;
		$verlauf->impliziterErstantritt = $implizit;
		$verlauf->canAdd = !$terminal && !$angerechnet && $antrittCount < $maxAntritte;

		// after the first exam each new entry is a repeat attempt
		$verlauf->erstantrittMoeglich = !$angerechnet && (count($eintraege) === 0);

		// The last attempt of the chain is kommissionell (§17 Abs 1). The role therefore follows
		// from the position, and the user interface offers no type to select.
		if (count($eintraege) === 0 && !$implizit) {
			$verlauf->naechsteRolle = self::ROLLE_ERSTANTRITT;
		} else {
			// an exam exists -> the next is a repeat at least, even if none counted yet
			$verlauf->naechsteRolle = $this->rolleFuerAntritt(max(2, $antrittCount + 1));
		}

		// The next attempt is the kommissionelle one, but this tool may not create it.
		$verlauf->kommPruefGesperrt = $verlauf->canAdd
			&& $verlauf->naechsteRolle === self::ROLLE_KOMMISSIONELL
			&& !$this->darfKommPruefAnlegen();

		if ($verlauf->kommPruefGesperrt) $verlauf->canAdd = false;

		return $verlauf;
	}

	/** May this tool CREATE the kommissionelle Prüfung? An existing one is always shown. @return bool */
	public function darfKommPruefAnlegen()
	{
		$erlaubt = $this->_ci->config->item('CIS_GESAMTNOTE_ALLOW_CREATE_KOMMPRUEF');
		return $erlaubt === null ? true : (bool) $erlaubt;
	}

	/** May a student attempt again after a positive grade? @return bool */
	public function darfVerbessern()
	{
		return $this->configBool('CIS_GESAMTNOTE_NOTENVERBESSERUNG', false);
	}

	/**
	 * Attempt from which the exam is kommissionell, or null if none is.
	 * Config takes a number, 0 for never, or 'letzter'.
	 *
	 * @return int|null
	 */
	public function getKommissionellAbAntritt()
	{
		$wert = $this->_ci->config->item('CIS_GESAMTNOTE_KOMMISSIONELL_AB_ANTRITT');

		if ($wert === null || $wert === 'letzter') {
			$max = $this->getMaxAntritte();

			// a chain of one has nothing to close
			return $max > 1 ? $max : null;
		}

		return (is_numeric($wert) && (int) $wert >= 1) ? (int) $wert : null;
	}

	/** Attempts that count, kommissionell included. Config wins, else the old flags. @return int */
	public function getMaxAntritte()
	{
		$max = $this->_ci->config->item('CIS_GESAMTNOTE_MAX_ANTRITTE');
		if (is_numeric($max) && (int) $max > 0) return (int) $max;

		return $this->maxAntritteAusAltFlags();
	}

	/** Grade an empty entry falls back to, named by config. @return mixed|null */
	public function getNoteNichtEingetragen()
	{
		return $this->getSpecialNotes()['nichtEingetragen'];
	}

	/** All exams of one student in one course, in chronological order. @return array */
	public function getPruefungen($student_uid, $lv_id, $sem_kurzbz)
	{
		$result = $this->_ci->LePruefungModel->getPruefungenByUidTypLvStudiensemester($student_uid, null, $lv_id, $sem_kurzbz);
		if (isError($result) || !hasData($result)) return [];

		return $this->sortPruefungen(getData($result));
	}

	/**
	 * Special grades, resolved from config names to tbl_note keys. A PK means something else in
	 * every installation, so no config holds one. An unknown name is dropped.
	 *
	 * @return array{entschuldigt: mixed, ohneAntritt: array, anrechnung: array, limitMap: array}
	 */
	public function getSpecialNotes()
	{
		if ($this->_specialNotes !== null) return $this->_specialNotes;

		$entschuldigt = $this->getNoteByBezeichnung($this->_ci->config->item('NOTE_ENTSCHULDIGT_BEZEICHNUNG'));

		$ohneAntritt = $this->resolveNoten('NOTEN_OHNE_ANTRITT_BEZEICHNUNGEN');
		if ($entschuldigt !== null && !in_array($entschuldigt, $ohneAntritt)) $ohneAntritt[] = $entschuldigt;

		// the limit names a grade, the rules work on its key
		$limitMap = [];
		foreach ($this->configArray('NOTEN_OCCURANCE_LIMIT_MAP') as $bezeichnung => $limit) {
			$note = $this->getNoteByBezeichnung($bezeichnung);
			if ($note !== null) $limitMap[$note] = $limit;
		}

		$this->_specialNotes = [
			'entschuldigt' => $entschuldigt,
			'ohneAntritt' => array_values($ohneAntritt),
			'anrechnung' => $this->resolveNoten('NOTEN_ANRECHNUNG_BEZEICHNUNGEN'),
			'abschliessend' => $this->resolveNoten('NOTEN_ABSCHLIESSEND_BEZEICHNUNGEN'),
			'nichtEingetragen' => $this->getNoteByBezeichnung($this->_ci->config->item('NOTE_NICHT_EINGETRAGEN_BEZEICHNUNG')),
			// order matters here: best grade first
			'rangfolge' => $this->resolveNoten('NOTEN_RANGFOLGE_BEZEICHNUNGEN'),
			'limitMap' => $limitMap
		];
		return $this->_specialNotes;
	}

	/** The exam types that never use an attempt. @return array */
	public function getTypenOhneAntritt()
	{
		$typen = $this->_ci->config->item('PRUEFUNG_TYPEN_OHNE_ANTRITT');
		return is_array($typen) ? $typen : ['zusKommPruef'];
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
	 * Does a Termin after attempt 1 exist?
	 *
	 * Attempt 1 and the LV-Note are the same performance, so the takeover path may still write it.
	 * From the first repeat on the grade belongs to the attempt. Decided by position, not by count.
	 *
	 * @return bool
	 */
	public function hatWiederholung($pruefungen)
	{
		foreach ($this->buildVerlauf($pruefungen)->pruefungen as $termin) {
			if ($termin->position > 1) return true;
		}

		return false;
	}

	/** Grade with no better one: closes the chain for good. @return bool */
	public function istAbschliessendeNote($note)
	{
		if ($note === null || $note === '') return false;

		return in_array($note, $this->getSpecialNotes()['abschliessend']);
	}

	/** 'angerechnet' or 'intern angerechnet'. The TRANSCRIPT grade decides. @return bool */
	public function istAnrechnungsnote($note)
	{
		if ($note === null || $note === '') return false;
		return in_array($note, $this->getSpecialNotes()['anrechnung']);
	}

	/** Tells you if an exam takes place before a commission. @return bool */
	public function istKommissionell($typ)
	{
		$typen = $this->_ci->config->item('PRUEFUNG_KOMMISSIONELL_TYPEN');
		if (!is_array($typen)) $typen = ['kommPruef', 'zusKommPruef'];
		return in_array($typ, $typen);
	}

	/**
	 * Is this grade a pass? tbl_note.positiv also sits on non-achievements ('entschuldigt',
	 * 'Teilgenommen'), so ask this only about a grade that uses an attempt.
	 *
	 * @return bool
	 */
	public function istPositiveNote($note)
	{
		if ($note === null || $note === '') return false;

		$noteRow = $this->getNote($note);

		return $noteRow ? (bool) $noteRow->positiv : false;
	}

	/** Is the new grade worse? An unknown grade is never worse. @return bool */
	public function istSchlechter($neu, $alt)
	{
		$vergleich = $this->vergleicheNoten($neu, $alt);

		return $vergleich !== null && $vergleich > 0;
	}

	/** Legacy pruefungstyp for an attempt number; Stv still reads that column. @return string */
	public function legacyTypFuerAntritt($antrittNr)
	{
		$antrittNr = max(1, (int) $antrittNr);
		$typen = $this->getTypen();
		$jeAntritt = $this->getTypJeAntritt();

		$kommissionell = $this->_ci->config->item('PRUEFUNG_TYP_KOMMISSIONELL');
		if (!is_string($kommissionell) || $kommissionell === '') $kommissionell = 'kommPruef';

		// the kommissionell role owns its type and beats the position
		if ($this->rolleFuerAntritt($antrittNr) === self::ROLLE_KOMMISSIONELL && isset($typen[$kommissionell])) {
			return $kommissionell;
		}

		// a type missing from tbl_pruefungstyp breaks the FK, so step down
		for ($n = $antrittNr; $n >= 1; $n--) {
			if (isset($jeAntritt[$n]) && isset($typen[$jeAntritt[$n]])) return $jeAntritt[$n];
		}

		return isset($jeAntritt[1]) ? $jeAntritt[1] : self::$_typJeAntrittVorgabe[1];
	}

	/** Legacy type for a new repeat; never below Termin2. @return string */
	public function legacyTypFuerWiederholung($verlauf)
	{
		return $this->legacyTypFuerAntritt(max(2, $verlauf->antrittCount + 1));
	}

	/** Role of one attempt: 1 = erstantritt, later = pruefung, from the commission on = kommissionell. @return string */
	public function rolleFuerAntritt($antrittNr)
	{
		$nr = max(1, (int) $antrittNr);
		$abKommissionell = $this->getKommissionellAbAntritt();

		if ($abKommissionell !== null && $nr >= $abKommissionell) return self::ROLLE_KOMMISSIONELL;

		return $nr === 1 ? self::ROLLE_ERSTANTRITT : self::ROLLE_PRUEFUNG;
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

	/** Occurrence limit per NOTEN_OCCURANCE_LIMIT_MAP; $excludePruefungId skips the edited row. @return bool */
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

	/**
	 * Writes attempt 1 as its own exam row. A course grade without any exam IS attempt 1; while
	 * that row is missing the next exam becomes attempt 2 and the whole legacy chain shifts.
	 *
	 * Idempotent: nothing is written once a repeat exists. Nothing is written either if the only
	 * exam documents a result of its own (see darfErstantrittUeberschreiben).
	 *
	 * @param bool  $mitDatum        also write $datum into an existing attempt 1. Only the grader
	 *                               picks that date - a release must not move it.
	 * @param mixed $lehreinheit_id  null = look it up
	 * @return stdClass|null the exam row, or null if nothing was written
	 */
	public function upsertErstantritt($student_uid, $lv_id, $sem_kurzbz, $note, $punkte, $datum, $mitarbeiter_uid = null, $mitDatum = false, $lehreinheit_id = null)
	{
		$pruefungen = $this->getPruefungen($student_uid, $lv_id, $sem_kurzbz);
		if ($this->hatWiederholung($pruefungen)) return null;

		$jetzt = date('Y-m-d H:i:s');
		$tag = substr((string) $datum, 0, 10);

		if (count($pruefungen) === 1) {
			// The course grade belongs to attempt 1 only. An excused Termin keeps its own grade.
			if (!$this->darfErstantrittUeberschreiben($pruefungen[0])) return null;

			$daten = [
				'note' => $note,
				'punkte' => $punkte,
				'updateamum' => $jetzt,
				'updatevon' => getAuthUID()
			];
			if ($mitDatum) $daten['datum'] = $tag;

			$this->_ci->LePruefungModel->update($pruefungen[0]->pruefung_id, $daten);

			return $this->ladePruefung($pruefungen[0]->pruefung_id);
		}

		// the server finds the Lehreinheit; it does not use a value from the client
		if ($lehreinheit_id === null) {
			$resLe = $this->_ci->LehrveranstaltungModel->getLeByStudent($student_uid, $sem_kurzbz, $lv_id);
			if (isError($resLe) || !hasData($resLe)) return null;
			$lehreinheit_id = current(getData($resLe))->lehreinheit_id;
		}

		$id = $this->_ci->LePruefungModel->insert([
			'lehreinheit_id' => $lehreinheit_id,
			'student_uid' => $student_uid,
			'mitarbeiter_uid' => $mitarbeiter_uid,
			'note' => $note,
			'punkte' => $punkte,
			'pruefungstyp_kurzbz' => $this->legacyTypFuerAntritt(1),
			'datum' => $tag,
			'anmerkung' => '',
			'insertamum' => $jetzt,
			'insertvon' => getAuthUID(),
			'updateamum' => null,
			'updatevon' => null,
			'ext_id' => null
		]);

		return $id ? $this->ladePruefung($id->retval) : null;
	}

	/**
	 * Guards a NEW attempt: A limit, B chronology, C occurrence limit, D waiting period.
	 *
	 * @return array|null [phraseKey, extraParams] or null if permitted
	 */
	public function validateAdd($pruefungen, $note, $datum, $lvNote = null, $zeugnisNote = null)
	{
		if ($this->istAnrechnungsnote($zeugnisNote)) return ['c4angerechnetKeinePruefung', []];

		$verlauf = $this->buildVerlauf($pruefungen, $lvNote, $zeugnisNote);

		// the next attempt is kommissionell, and this tool may not create it
		if ($verlauf->kommPruefGesperrt) return ['kommPruefNichtErlaubt', []];

		// A: the first attempt only materialises the course grade, so it adds no attempt
		if ($verlauf->naechsteRolle !== self::ROLLE_ERSTANTRITT && !$verlauf->canAdd) {
			// a pass and a used-up limit are different answers
			if ($verlauf->bestanden) return ['pruefungNachBestandenerNote', []];

			return ['maxAntritteReached', [$verlauf->maxAntritte]];
		}

		// B: no attempt before an existing exam; same day counts as too early unless configured
		$gleicherTag = $this->configBool('CIS_GESAMTNOTE_TERMIN_GLEICHER_TAG', false);
		$newDate = substr((string) $datum, 0, 10);
		foreach ($pruefungen as $p) {
			$d = substr((string) $p->datum, 0, 10);
			if ($d === '') continue;
			if ($gleicherTag ? ($d > $newDate) : ($d >= $newDate)) {
				return ['pruefungDatumBeforeExisting', []];
			}
		}

		// C: occurrence limit, e.g. one 'entschuldigt' only
		if ($this->ueberschreitetNotenLimit($pruefungen, $note, null)) {
			return ['noteOccuranceLimitReached', []];
		}

		// D: the waiting period between two attempts
		return $this->pruefeAntrittsabstand($verlauf, $newDate);
	}

	/**
	 * Guards an EDIT. Date must stay between the neighbouring exams; a later exam locks the grade.
	 *
	 * Returns a phrase key, not a text - the controller adds student_uid and translates.
	 *
	 * @return array|null [phraseKey, extraParams] or null if permitted
	 */
	public function validateEdit($pruefungen, $pruefung_id, $newNote, $newDatum, $lvNote = null, $zeugnisNote = null)
	{
		if ($pruefung_id === null || $pruefung_id === '') return null; // add, not an edit

		if ($this->istAnrechnungsnote($zeugnisNote)) return ['c4angerechnetKeinePruefung', []];

		if (count($pruefungen) === 0) return null;

		$verlauf = $this->buildVerlauf($pruefungen, $lvNote, $zeugnisNote);

		// the record being edited
		$current = null;
		foreach ($verlauf->pruefungen as $p) {
			if ($p->pruefung_id == $pruefung_id) { $current = $p; break; }
		}
		if ($current === null) return null;

		// Stv owns exams that use no attempt (zusKommPruef); not validated here
		if (in_array($current->pruefungstyp_kurzbz, $this->getTypenOhneAntritt())) return null;

		$currentDate = substr((string) $current->datum, 0, 10);
		$new         = substr((string) $newDatum, 0, 10);

		// bounds: the exams directly before and after this one
		$lower = null; $upper = null;
		foreach ($verlauf->pruefungen as $p) {
			if ($p->pruefung_id == $current->pruefung_id) continue;

			$d = substr((string) $p->datum, 0, 10);
			if ($d === '') continue;

			if ($d < $currentDate) { if ($lower === null || $d > $lower) $lower = $d; }
			elseif ($d > $currentDate) { if ($upper === null || $d < $upper) $upper = $d; }
		}

		// grade is locked once a later attempt exists
		if ($this->configBool('CIS_GESAMTNOTE_NOTE_SPERRE_BEI_SPAETEREM_TERMIN', true)
			&& $this->hatSpaeterenTermin($verlauf, $current->pruefung_id) && $newNote != $current->note) {
			return ['pruefungNoteLocked', []];
		}

		// datum must stay strictly between the neighbouring exam dates
		if (($lower !== null && $new <= $lower) || ($upper !== null && $new >= $upper)) {
			return ['pruefungDatumOutOfRange', []];
		}

		// an edit may change the note too -> re-check the occurrence limit
		if ($this->ueberschreitetNotenLimit($verlauf->pruefungen, $newNote, $current->pruefung_id)) {
			return ['noteOccuranceLimitReached', []];
		}

		return null;
	}

	/**
	 * Compares two grades via NOTEN_RANGFOLGE_BEZEICHNUNGEN (tbl_note.notenwert is NULL everywhere).
	 *
	 * @return int|null <0 if $a is better, 0 equal, null if one grade is not in the list
	 */
	public function vergleicheNoten($a, $b)
	{
		$rang = $this->getSpecialNotes()['rangfolge'];

		$ia = array_search($a, $rang);
		$ib = array_search($b, $rang);
		if ($ia === false || $ib === false) return null;

		return $ia - $ib;
	}

	/**
	 * Does this Termin use an attempt? The NOTE decides; the type only via
	 * PRUEFUNG_TYPEN_OHNE_ANTRITT, which never counts.
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

	/** One configuration value that must be a list. @return array */
	private function configArray($key)
	{
		$wert = $this->_ci->config->item($key);
		return is_array($wert) ? $wert : [];
	}

	/** One switch; an absent key keeps the default. @return bool */
	private function configBool($key, $default)
	{
		$wert = $this->_ci->config->item($key);
		return $wert === null ? (bool) $default : (bool) $wert;
	}

	/** Days, or null if the rule is off. @return int|null */
	private function configTage($key)
	{
		$wert = $this->_ci->config->item($key);
		if (!is_numeric($wert)) return null;

		return ((int) $wert >= 0) ? (int) $wert : null;
	}

	/**
	 * May the course grade go into this existing exam row? Attempt 1 and the course grade are the
	 * same performance, therefore that row takes the grade. An empty row takes it too: it waits for
	 * the result.
	 *
	 * A row with a result of its own does NOT take it. 'entschuldigt' and 'Nicht beurteilt' use no
	 * attempt, but they document a dated event. The course grade then stays the implicit first
	 * attempt, which buildVerlauf already counts.
	 *
	 * @return bool
	 */
	private function darfErstantrittUeberschreiben($pruefung)
	{
		// the student administration owns the types that never use an attempt (zusKommPruef)
		if (in_array($pruefung->pruefungstyp_kurzbz, $this->getTypenOhneAntritt())) return false;

		if ($this->zaehltAlsAntritt($pruefung->note, $pruefung->pruefungstyp_kurzbz)) return true;

		// the row carries no result yet
		$offen = $this->getSpecialNotes()['nichtEingetragen'];

		return $offen !== null && $pruefung->note == $offen;
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
	 * @return mixed|null the key of the grade, or null if no grade carries the name
	 */
	private function getNoteByBezeichnung($bezeichnung)
	{
		if (!is_string($bezeichnung) || trim($bezeichnung) === '') return null;

		$result = $this->_ci->NoteModel->loadWhere(['bezeichnung' => $bezeichnung]);
		return (!isError($result) && hasData($result)) ? getData($result)[0]->note : null;
	}

	/**
	 * tbl_pruefungstyp as a map: sorts old rows and limits the legacy type to types that exist.
	 * A missing type breaks the FK (Termin3 is absent by default).
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

	/** @return array attempt number => legacy type */
	private function getTypJeAntritt()
	{
		$typen = $this->configArray('PRUEFUNG_TYP_JE_ANTRITT');

		return count($typen) > 0 ? $typen : self::$_typJeAntrittVorgabe;
	}

	/** @return stdClass|null */
	private function ladePruefung($pruefung_id)
	{
		$result = $this->_ci->LePruefungModel->load($pruefung_id);
		return (!isError($result) && hasData($result)) ? getData($result)[0] : null;
	}

	/** 1 (the original assessment) + TERMIN2 + TERMIN3 + KOMMPRUEF. @return int */
	private function maxAntritteAusAltFlags()
	{
		$max = 1;
		if (defined('CIS_GESAMTNOTE_PRUEFUNG_TERMIN2') && CIS_GESAMTNOTE_PRUEFUNG_TERMIN2) $max++;
		if (defined('CIS_GESAMTNOTE_PRUEFUNG_TERMIN3') && CIS_GESAMTNOTE_PRUEFUNG_TERMIN3) $max++;
		if (defined('CIS_GESAMTNOTE_PRUEFUNG_KOMMPRUEF') && CIS_GESAMTNOTE_PRUEFUNG_KOMMPRUEF) $max++;

		return $max;
	}

	/**
	 * Waiting period between the last attempt and a new one.
	 *
	 * Runs between ATTEMPTS, not dates: an excused Termin must not push the next attempt away.
	 *
	 * @return array|null [phraseKey, extraParams] or null if permitted
	 */
	private function pruefeAntrittsabstand($verlauf, $newDate)
	{
		$min = $this->configTage('CIS_GESAMTNOTE_ANTRITT_MIN_ABSTAND_TAGE');
		$max = $this->configTage('CIS_GESAMTNOTE_ANTRITT_MAX_ABSTAND_TAGE');
		if (($min === null && $max === null) || $newDate === '') return null;

		// last Termin that uses an attempt
		$letzter = null;
		foreach ($verlauf->pruefungen as $p) {
			if (!$p->zaehlt) continue;

			$d = substr((string) $p->datum, 0, 10);
			if ($d !== '' && ($letzter === null || $d > $letzter)) $letzter = $d;
		}
		if ($letzter === null) return null;

		$abstand = $this->tageZwischen($letzter, $newDate);
		if ($abstand === null) return null;

		if ($min !== null && $abstand < $min) return ['pruefungAbstandZuKurz', [$min]];
		if ($max !== null && $abstand > $max) return ['pruefungAbstandZuLang', [$max]];

		return null;
	}

	/** The grades of one configuration key, found by their name. @return array */
	private function resolveNoten($bezeichnungKey)
	{
		$noten = [];
		foreach ($this->configArray($bezeichnungKey) as $bezeichnung) {
			$note = $this->getNoteByBezeichnung($bezeichnung);
			if ($note !== null && !in_array($note, $noten)) $noten[] = $note;
		}

		return array_values($noten);
	}

	/** Whole days between two Y-m-d days. @return int|null */
	private function tageZwischen($von, $bis)
	{
		$a = DateTime::createFromFormat('Y-m-d|', $von);
		$b = DateTime::createFromFormat('Y-m-d|', $bis);
		if ($a === false || $b === false) return null;

		$diff = $a->diff($b);

		return $diff->invert ? -((int) $diff->days) : (int) $diff->days;
	}
}
