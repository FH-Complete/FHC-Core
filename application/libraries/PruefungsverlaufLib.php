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
 * The Verlauf of one student in one LV: the order of the Pruefungen, the Antritte and the limits.
 *
 * This class holds the rules of the Antritt chain. The controller holds access, Frist and Freigabe.
 * The client only reads the Verlauf.
 *
 * The StV calls getPruefungen() and legacyTypFuerAntritt(). Keep their names and signatures.
 */
class PruefungsverlaufLib
{
	/** Antritt 1. Without a Pruefung the LV-Note itself is Antritt 1. */
	const ROLE_ERSTANTRITT = 'erstantritt';

	/** Each Antritt after the first one. */
	const ROLE_PRUEFUNG = 'pruefung';

	/** The Antritt before a commission (CIS_GESAMTNOTE_KOMMISSIONELL_AB_ANTRITT). */
	const ROLE_KOMMISSIONELL = 'kommissionell';

	/** upsertErstantritt(): an existing Antritt 1 keeps its date. */
	const KEEP_DATUM = false;

	/** upsertErstantritt(): an existing Antritt 1 takes the new date. */
	const SET_DATUM = true;

	private $_ci;
	private $_noteCache = [];
	private $_specialNotes = null;
	private $_types = null;

	public function __construct()
	{
		$this->_ci =& get_instance();
		$this->_ci->load->model('education/LePruefung_model', 'LePruefungModel');
		$this->_ci->load->model('education/Note_model', 'NoteModel');
		$this->_ci->load->model('education/Pruefungstyp_model', 'PruefungstypModel');
		$this->_ci->load->config('noten');
	}

	/**
	 * Builds the Verlauf. Each Pruefung gets position, is_antritt, antritt_nr, kommissionell and
	 * note_locked. The rules and the client read this one object.
	 *
	 * @param array      $pruefungen  the Pruefungen of one student in one LV
	 * @param mixed|null $lvNote      Antritt 1 while no Pruefung is an Antritt
	 * @param mixed|null $zeugnisnote an Anrechnung blocks every Pruefung
	 * @return stdClass
	 */
	public function buildVerlauf($pruefungen, $lvNote, $zeugnisnote)
	{
		$pruefungen = $this->sortPruefungen($pruefungen);

		// a later Pruefung locks the Note of an earlier one; its date stays editable
		$lockedByLater = $this->configBool('CIS_GESAMTNOTE_NOTE_SPERRE_BEI_SPAETERER_PRUEFUNG');

		$antrittCount = 0;
		$terminal = false;
		$entries = [];

		foreach ($pruefungen as $i => $pruefung) {
			$isAntritt = $this->isAntritt($pruefung->note, $pruefung->pruefungstyp_kurzbz);
			if ($isAntritt) $antrittCount++;

			// A kommissionelle Pruefung closes the chain, also if its Note uses no Antritt.
			$kommissionell = $this->isKommissionell($pruefung->pruefungstyp_kurzbz);
			if ($kommissionell) $terminal = true;

			$entry = clone $pruefung;
			$entry->position = $i + 1;
			$entry->is_antritt = $isAntritt;
			$entry->antritt_nr = $isAntritt ? $antrittCount : null;
			$entry->kommissionell = $kommissionell;
			$entry->note_locked = $lockedByLater && $i < count($pruefungen) - 1;
			$entries[] = $entry;
		}

		// without a Pruefung that is an Antritt, the LV-Note itself is Antritt 1
		$implicit = ($antrittCount === 0 && $lvNote !== null && $this->isAntritt($lvNote));
		if ($implicit) $antrittCount = 1;

		// the deciding Note: the last Antritt, else the LV-Note
		$currentNote = null;
		foreach ($entries as $entry) {
			if ($entry->is_antritt) $currentNote = $entry->note;
		}
		if ($currentNote === null && $implicit) $currentNote = $lvNote;

		// a closing Note always ends the chain, any other positive Note only without Notenverbesserung
		$bestanden = $this->isClosingNote($currentNote)
			|| (!$this->allowsImprovement() && $this->isPositiveNote($currentNote));
		if ($bestanden) $terminal = true;

		$verlauf = new stdClass();
		$verlauf->pruefungen = $entries;
		$verlauf->antrittCount = $antrittCount;
		$verlauf->maxAntritte = $this->getMaxAntritte();
		$verlauf->terminal = $terminal;
		// closed by a pass, not by the limit
		$verlauf->bestanden = $bestanden;
		// the row stays visible, but nobody can enter anything
		$verlauf->angerechnet = $this->isAnrechnungNote($zeugnisnote);
		$verlauf->implicitErstantritt = $implicit;
		$verlauf->canAdd = !$terminal && !$verlauf->angerechnet && $antrittCount < $verlauf->maxAntritte;

		// a second Pruefung, or one Pruefung that is not Antritt 1 (next to an implicit Antritt 1).
		// This is the only rule that reads the legacy Pruefungstyp back.
		$verlauf->hasRepeat = count($entries) > 1
			|| (count($entries) === 1 && $entries[0]->pruefungstyp_kurzbz !== $this->legacyTypFuerAntritt(1));

		// The role follows from the position (§17 Abs 1), so the user interface offers no type.
		if (count($entries) === 0 && !$implicit) {
			$verlauf->nextRole = self::ROLE_ERSTANTRITT;
		} else {
			// a Pruefung exists: the next one is a repeat, also if no Pruefung is an Antritt yet
			$verlauf->nextRole = $this->roleForAntritt(max(2, $antrittCount + 1));
		}

		// the next Antritt is kommissionell, and this tool may not create it
		$verlauf->kommPruefLocked = $verlauf->canAdd
			&& $verlauf->nextRole === self::ROLE_KOMMISSIONELL
			&& !$this->canCreateKommPruef();
		if ($verlauf->kommPruefLocked) $verlauf->canAdd = false;

		$verlauf->earliestNewDay = $this->earliestNewDay($entries);
		$verlauf->notenAtLimit = $this->notenAtLimit($entries);

		return $verlauf;
	}

	/**
	 * @param mixed|null $lvNote      see buildVerlauf
	 * @param mixed|null $zeugnisnote see buildVerlauf
	 * @return stdClass
	 */
	public function getVerlauf($student_uid, $lv_id, $sem_kurzbz, $lvNote, $zeugnisnote)
	{
		return $this->buildVerlauf($this->getPruefungen($student_uid, $lv_id, $sem_kurzbz), $lvNote, $zeugnisnote);
	}

	/** All Pruefungen of one student in one LV, in the order of the Verlauf. The StV calls it. @return array */
	public function getPruefungen($student_uid, $lv_id, $sem_kurzbz)
	{
		$result = $this->_ci->LePruefungModel->getPruefungenByStudent($student_uid, $lv_id, $sem_kurzbz);
		if (isError($result) || !hasData($result)) return [];

		return $this->sortPruefungen(getData($result));
	}

	/**
	 * Checks a NEW Antritt: A the limit, B the order of the dates, C the occurrence limit, D the gap.
	 *
	 * @param stdClass $verlauf from buildVerlauf
	 * @return array|null [phraseKey, params] or null
	 */
	public function validateAdd($verlauf, $note, $datum)
	{
		if ($verlauf->angerechnet) return ['c4angerechnetKeinePruefung', []];
		if ($verlauf->kommPruefLocked) return ['kommPruefNichtErlaubt', []];

		// A: Antritt 1 only writes the LV-Note down, so it needs no free Antritt
		if ($verlauf->nextRole !== self::ROLE_ERSTANTRITT && !$verlauf->canAdd) {
			if ($verlauf->bestanden) return ['pruefungNachBestandenerNote', []];

			return ['maxAntritteReached', [$verlauf->maxAntritte]];
		}

		// one Pruefung without a result per Verlauf; more of them break the order of the Antritte
		if ($this->isOpen($note) && $this->hasOpenPruefung($verlauf->pruefungen)) return ['pruefungOhneErgebnis', []];

		// B: no Pruefung before an existing one
		$newDay = substr((string) $datum, 0, 10);
		if ($verlauf->earliestNewDay !== null && $newDay < $verlauf->earliestNewDay) return ['pruefungDatumBeforeExisting', []];

		// C: for example one 'entschuldigt' only
		if ($this->exceedsNoteLimit($verlauf->pruefungen, $note)) return ['noteOccuranceLimitReached', []];

		// D
		return $this->checkAntrittGap($verlauf, $newDay);
	}

	/**
	 * Checks a CHANGED Pruefung. The date must stay between its neighbours, and a later Pruefung locks
	 * the Note.
	 *
	 * @param stdClass $verlauf from buildVerlauf
	 * @return array|null [phraseKey, params] or null
	 */
	public function validateEdit($verlauf, $pruefung_id, $note, $datum)
	{
		if ($verlauf->angerechnet) return ['c4angerechnetKeinePruefung', []];

		// only a Pruefung of this student in this LV; the StV owns the types without an Antritt
		$current = $this->findPruefung($verlauf->pruefungen, $pruefung_id);
		if ($current === null || in_array($current->pruefungstyp_kurzbz, $this->getTypesWithoutAntritt())) {
			return ['pruefungNichtBearbeitbar', []];
		}

		if ($this->isOpen($note) && $this->hasOpenPruefung($verlauf->pruefungen, $pruefung_id)) return ['pruefungOhneErgebnis', []];

		if ($current->note_locked && $note != $current->note) return ['pruefungNoteLocked', []];

		// the date must stay strictly between the dates of the neighbours
		$currentDay = substr((string) $current->datum, 0, 10);
		$newDay = substr((string) $datum, 0, 10);
		$lower = null;
		$upper = null;
		foreach ($verlauf->pruefungen as $pruefung) {
			$day = substr((string) $pruefung->datum, 0, 10);
			if ($pruefung->pruefung_id == $current->pruefung_id || $day === '') continue;

			if ($day < $currentDay && ($lower === null || $day > $lower)) $lower = $day;
			if ($day > $currentDay && ($upper === null || $day < $upper)) $upper = $day;
		}
		if (($lower !== null && $newDay <= $lower) || ($upper !== null && $newDay >= $upper)) {
			return ['pruefungDatumOutOfRange', []];
		}

		if ($this->exceedsNoteLimit($verlauf->pruefungen, $note, $current->pruefung_id)) {
			return ['noteOccuranceLimitReached', []];
		}

		// only a new date: an old gap outside the rule must not block a Note correction
		if ($newDay === $currentDay) return null;

		return $this->checkAntrittGap($verlauf, $newDay, $current->pruefung_id);
	}

	/**
	 * The LV-Note after one Pruefung write: the Note of the last Antritt. Without an Antritt the
	 * implicit Antritt 1 stays, else nothing is entered. The LV-Note is never 'entschuldigt'.
	 *
	 * With CIS_GESAMTNOTE_VERBESSERUNG_BESSERE_GEWINNT a worse new Antritt keeps the old LV-Note.
	 *
	 * @param stdClass $verlauf     from buildVerlauf, before this write
	 * @param mixed    $pruefung_id set = the changed Pruefung, empty = a new Pruefung after all others
	 * @return array [note, punkte]
	 */
	public function deriveLvNote($verlauf, $pruefung_id, $note, $punkte, $lvNote, $lvPunkte)
	{
		$isNew = $pruefung_id === null || $pruefung_id === '';

		$result = null;
		foreach ($verlauf->pruefungen as $pruefung) {
			$row = (!$isNew && $pruefung->pruefung_id == $pruefung_id) ? [$note, $punkte] : [$pruefung->note, $pruefung->punkte];
			if ($this->isAntritt($row[0], $pruefung->pruefungstyp_kurzbz)) $result = $row;
		}
		if ($isNew && $this->isAntritt($note)) $result = [$note, $punkte];

		if ($result === null) {
			if ($verlauf->implicitErstantritt) return [$lvNote, $lvPunkte];

			return [$this->getNoteNichtEingetragen(), null];
		}

		if ($result[0] == $note
			&& $this->configBool('CIS_GESAMTNOTE_VERBESSERUNG_BESSERE_GEWINNT')
			&& $this->isWorse($note, $lvNote)) {
			return [$lvNote, $lvPunkte];
		}

		return $result;
	}

	/**
	 * Writes Antritt 1 as its own Pruefung. An LV-Note without a Pruefung IS Antritt 1. While that row
	 * is missing, the next Pruefung becomes Antritt 2 and the Pruefungstypen of the chain shift.
	 *
	 * Writes nothing once a repeat exists, and nothing into a Pruefung with a result of its own.
	 *
	 * @param bool $datumMode self::SET_DATUM or self::KEEP_DATUM for an existing Antritt 1
	 * @return stdClass|null the Pruefung, or null if nothing was written
	 */
	public function upsertErstantritt($student_uid, $lv_id, $sem_kurzbz, $note, $punkte, $datum, $datumMode, $mitarbeiter_uid, $lehreinheit_id)
	{
		$pruefungen = $this->getPruefungen($student_uid, $lv_id, $sem_kurzbz);
		// hasRepeat reads only the Pruefungen
		if ($this->buildVerlauf($pruefungen, null, null)->hasRepeat) return null;

		$now = date('Y-m-d H:i:s');
		$day = substr((string) $datum, 0, 10);

		if (count($pruefungen) === 1) {
			if (!$this->canOverwriteErstantritt($pruefungen[0])) return null;

			$data = [
				'note' => $note,
				'punkte' => $punkte,
				'updateamum' => $now,
				'updatevon' => getAuthUID()
			];
			if ($datumMode === self::SET_DATUM) $data['datum'] = $day;

			$this->_ci->LePruefungModel->update($pruefungen[0]->pruefung_id, $data);

			return $this->loadPruefung($pruefungen[0]->pruefung_id);
		}

		if ($lehreinheit_id === null) return null;

		$result = $this->_ci->LePruefungModel->insert([
			'lehreinheit_id' => $lehreinheit_id,
			'student_uid' => $student_uid,
			'mitarbeiter_uid' => $mitarbeiter_uid,
			'note' => $note,
			'punkte' => $punkte,
			'pruefungstyp_kurzbz' => $this->legacyTypFuerAntritt(1),
			'datum' => $day,
			'anmerkung' => '',
			'insertamum' => $now,
			'insertvon' => getAuthUID(),
			'updateamum' => null,
			'updatevon' => null,
			'ext_id' => null
		]);

		return $result ? $this->loadPruefung($result->retval) : null;
	}

	/**
	 * The Pruefungstyp that the tool writes for an Antritt. The StV still reads that column and calls
	 * this method. A type that tbl_pruefungstyp does not have breaks the foreign key, so step down.
	 *
	 * @return string
	 */
	public function legacyTypFuerAntritt($antrittNr)
	{
		$antrittNr = max(1, (int) $antrittNr);
		$types = $this->getTypes();

		$kommissionell = $this->_ci->config->item('PRUEFUNG_TYP_KOMMISSIONELL');
		if ($this->roleForAntritt($antrittNr) === self::ROLE_KOMMISSIONELL && isset($types[$kommissionell])) {
			return $kommissionell;
		}

		$typePerAntritt = $this->configArray('PRUEFUNG_TYP_JE_ANTRITT');
		for ($nr = $antrittNr; $nr >= 1; $nr--) {
			if (isset($typePerAntritt[$nr]) && isset($types[$typePerAntritt[$nr]])) return $typePerAntritt[$nr];
		}

		return $typePerAntritt[1];
	}

	/** The Pruefungstyp of a new repeat; never below Antritt 2. @return string */
	public function legacyTypeForRepeat($verlauf)
	{
		return $this->legacyTypFuerAntritt(max(2, $verlauf->antrittCount + 1));
	}

	/** Antritte, Antritt 1 and the kommissionelle Pruefung included. @return int */
	public function getMaxAntritte()
	{
		$max = $this->_ci->config->item('CIS_GESAMTNOTE_MAX_ANTRITTE');
		if ($max !== null) return (int) $max;

		// the flags of the old tool: Antritt 1 plus one Antritt per flag
		return 1 + (int) CIS_GESAMTNOTE_PRUEFUNG_TERMIN2 + (int) CIS_GESAMTNOTE_PRUEFUNG_TERMIN3 + (int) CIS_GESAMTNOTE_PRUEFUNG_KOMMPRUEF;
	}

	/** The Antritt from which a Pruefung is kommissionell, or null. @return int|null */
	public function getKommissionellFromAntritt()
	{
		$value = $this->_ci->config->item('CIS_GESAMTNOTE_KOMMISSIONELL_AB_ANTRITT');

		if ($value === 'letzter') {
			$max = $this->getMaxAntritte();

			// a chain of one has nothing to close
			return $max > 1 ? $max : null;
		}

		return (int) $value >= 1 ? (int) $value : null;
	}

	/** May this tool create the kommissionelle Pruefung? It always shows an existing one. @return bool */
	public function canCreateKommPruef()
	{
		return $this->configBool('CIS_GESAMTNOTE_ALLOW_CREATE_KOMMPRUEF');
	}

	/** May a student take another Antritt after a positive Note? @return bool */
	public function allowsImprovement()
	{
		return $this->configBool('CIS_GESAMTNOTE_NOTENVERBESSERUNG');
	}

	/** @return array the Pruefungstypen that never use an Antritt */
	public function getTypesWithoutAntritt()
	{
		return $this->configArray('PRUEFUNG_TYPEN_OHNE_ANTRITT');
	}

	/**
	 * The special Noten. The configuration names them, lehre.tbl_note gives their keys: a key means
	 * something else in every installation. An unknown name is dropped.
	 *
	 * @return array
	 */
	public function getSpecialNotes()
	{
		if ($this->_specialNotes !== null) return $this->_specialNotes;

		$entschuldigt = $this->getNoteByBezeichnung($this->_ci->config->item('NOTE_ENTSCHULDIGT_BEZEICHNUNG'));

		$ohneAntritt = $this->resolveNoten('NOTEN_OHNE_ANTRITT_BEZEICHNUNGEN');
		if ($entschuldigt !== null && !in_array($entschuldigt, $ohneAntritt)) $ohneAntritt[] = $entschuldigt;

		// the configuration names a Note, the rules work with its key
		$limitMap = [];
		foreach ($this->configArray('NOTEN_OCCURRENCE_LIMIT_MAP') as $bezeichnung => $limit) {
			$note = $this->getNoteByBezeichnung($bezeichnung);
			if ($note !== null) $limitMap[$note] = $limit;
		}

		$this->_specialNotes = [
			'entschuldigt' => $entschuldigt,
			'ohneAntritt' => array_values($ohneAntritt),
			'anrechnung' => $this->resolveNoten('NOTEN_ANRECHNUNG_BEZEICHNUNGEN'),
			'abschliessend' => $this->resolveNoten('NOTEN_ABSCHLIESSEND_BEZEICHNUNGEN'),
			'nichtEingetragen' => $this->getNoteByBezeichnung($this->_ci->config->item('NOTE_NICHT_EINGETRAGEN_BEZEICHNUNG')),
			// best first
			'rangfolge' => $this->resolveNoten('NOTEN_RANGFOLGE_BEZEICHNUNGEN'),
			'limitMap' => $limitMap
		];
		return $this->_specialNotes;
	}

	/** The Note of a Pruefung without a result ('Noch nicht eingetragen'). @return mixed|null */
	public function getNoteNichtEingetragen()
	{
		return $this->getSpecialNotes()['nichtEingetragen'];
	}

	/** 'angerechnet' or 'intern angerechnet'. Ask it about the Zeugnisnote. @return bool */
	public function isAnrechnungNote($note)
	{
		if ($note === null || $note === '') return false;

		return in_array($note, $this->getSpecialNotes()['anrechnung']);
	}

	/** Would one more $note cross NOTEN_OCCURRENCE_LIMIT_MAP? $pruefung_id skips the changed row. @return bool */
	public function exceedsNoteLimit($pruefungen, $note, $pruefung_id = null)
	{
		$limit = null;
		foreach ($this->getSpecialNotes()['limitMap'] as $limitNote => $value) {
			if ($limitNote == $note) $limit = $value;
		}
		if ($limit === null) return false;

		$count = 0;
		foreach ($pruefungen as $pruefung) {
			if ($pruefung_id !== null && $pruefung->pruefung_id == $pruefung_id) continue;
			if ($pruefung->note == $note) $count++;
		}

		return $count + 1 > $limit;
	}

	// --- private --------------------------------------------------------------------------------

	/** The role of one Antritt: 1 = erstantritt, later = pruefung, from the commission on = kommissionell. @return string */
	private function roleForAntritt($antrittNr)
	{
		$nr = max(1, (int) $antrittNr);
		$fromAntritt = $this->getKommissionellFromAntritt();

		if ($fromAntritt !== null && $nr >= $fromAntritt) return self::ROLE_KOMMISSIONELL;

		return $nr === 1 ? self::ROLE_ERSTANTRITT : self::ROLE_PRUEFUNG;
	}

	/**
	 * Does this Note use an Antritt? The Note decides; the type only through PRUEFUNG_TYPEN_OHNE_ANTRITT.
	 *
	 * @return bool
	 */
	private function isAntritt($note, $type = null)
	{
		if ($type !== null && in_array($type, $this->getTypesWithoutAntritt())) return false;
		if ($note === null || $note === '') return false;
		if (in_array($note, $this->getSpecialNotes()['ohneAntritt'])) return false;

		// an Anrechnung is no assessment
		if ($this->isAnrechnungNote($note)) return false;

		$noteRow = $this->getNote($note);
		return $noteRow ? (bool) $noteRow->lehre : false;
	}

	/** Is this Pruefungstyp kommissionell? @return bool */
	private function isKommissionell($type)
	{
		return in_array($type, $this->configArray('PRUEFUNG_KOMMISSIONELL_TYPEN'));
	}

	/** Is the new Note worse? A Note outside NOTEN_RANGFOLGE_BEZEICHNUNGEN is never worse. @return bool */
	private function isWorse($newNote, $oldNote)
	{
		$rangfolge = $this->getSpecialNotes()['rangfolge'];

		$newRank = array_search($newNote, $rangfolge);
		$oldRank = array_search($oldNote, $rangfolge);
		if ($newRank === false || $oldRank === false) return false;

		return $newRank > $oldRank;
	}

	/** The order of the Verlauf: datum, then tbl_pruefungstyp.sort (old rows without a date), then pruefung_id. */
	private function sortPruefungen($pruefungen)
	{
		$pruefungen = array_values($pruefungen);
		$types = $this->getTypes();

		usort($pruefungen, function ($a, $b) use ($types) {
			$dayA = substr((string) $a->datum, 0, 10);
			$dayB = substr((string) $b->datum, 0, 10);

			if ($dayA !== $dayB) {
				if ($dayA === '') return -1;
				if ($dayB === '') return 1;
				return ($dayA < $dayB) ? -1 : 1;
			}

			$sortA = isset($types[$a->pruefungstyp_kurzbz]) ? (int) $types[$a->pruefungstyp_kurzbz]->sort : 0;
			$sortB = isset($types[$b->pruefungstyp_kurzbz]) ? (int) $types[$b->pruefungstyp_kurzbz]->sort : 0;
			if ($sortA !== $sortB) return ($sortA < $sortB) ? -1 : 1;

			return ((int) $a->pruefung_id < (int) $b->pruefung_id) ? -1 : 1;
		});

		return $pruefungen;
	}

	/**
	 * May the LV-Note go into this Pruefung? Antritt 1 and the LV-Note are the same result, and an
	 * empty Pruefung waits for its result. A Pruefung with a result of its own ('entschuldigt',
	 * 'Nicht beurteilt') keeps it: the LV-Note then stays the implicit Antritt 1.
	 *
	 * @return bool
	 */
	private function canOverwriteErstantritt($pruefung)
	{
		if (in_array($pruefung->pruefungstyp_kurzbz, $this->getTypesWithoutAntritt())) return false;
		if ($this->isAntritt($pruefung->note, $pruefung->pruefungstyp_kurzbz)) return true;

		return $this->isOpen($pruefung->note);
	}

	/**
	 * The first day for a new Pruefung: it comes after all others. CIS_GESAMTNOTE_PRUEFUNG_GLEICHER_TAG
	 * allows the day of the last one.
	 *
	 * @return string|null Y-m-d, or null without a dated Pruefung
	 */
	private function earliestNewDay($entries)
	{
		$lastDay = null;
		foreach ($entries as $entry) {
			$day = substr((string) $entry->datum, 0, 10);
			if ($day !== '' && ($lastDay === null || $day > $lastDay)) $lastDay = $day;
		}

		if ($lastDay === null || $this->configBool('CIS_GESAMTNOTE_PRUEFUNG_GLEICHER_TAG')) return $lastDay;

		return date('Y-m-d', strtotime($lastDay . ' +1 day'));
	}

	/** The Noten that NOTEN_OCCURRENCE_LIMIT_MAP allows no more time in this Verlauf. @return array Note => limit */
	private function notenAtLimit($entries)
	{
		$atLimit = [];
		foreach ($this->getSpecialNotes()['limitMap'] as $limitNote => $limit) {
			$count = 0;
			foreach ($entries as $entry) {
				if ($entry->note == $limitNote) $count++;
			}
			if ($count >= $limit) $atLimit[$limitNote] = $limit;
		}

		return $atLimit;
	}

	/**
	 * The gaps between a Pruefung on $newDay and the Antritte next to it: the last one on or before
	 * the day, and the first one after it. A new Pruefung has no Antritt after it. A Pruefung without
	 * an Antritt does not count: an entschuldigt date must not push the next Antritt away.
	 *
	 * @param mixed $pruefung_id the changed Pruefung, which is no neighbour of itself; null for a new one
	 * @return array|null [phraseKey, params] or null
	 */
	private function checkAntrittGap($verlauf, $newDay, $pruefung_id = null)
	{
		$min = $this->_ci->config->item('CIS_GESAMTNOTE_ANTRITT_MIN_ABSTAND_TAGE');
		$max = $this->_ci->config->item('CIS_GESAMTNOTE_ANTRITT_MAX_ABSTAND_TAGE');
		if (($min === null && $max === null) || $newDay === '') return null;

		$before = null;
		$after = null;
		foreach ($verlauf->pruefungen as $pruefung) {
			$day = substr((string) $pruefung->datum, 0, 10);
			if (!$pruefung->is_antritt || $day === '' || ($pruefung_id !== null && $pruefung->pruefung_id == $pruefung_id)) continue;

			if ($day <= $newDay && ($before === null || $day > $before)) $before = $day;
			if ($day > $newDay && ($after === null || $day < $after)) $after = $day;
		}

		foreach ([[$before, $newDay], [$newDay, $after]] as list($from, $to)) {
			$gap = ($from !== null && $to !== null) ? $this->daysBetween($from, $to) : null;
			if ($gap === null) continue;

			if ($min !== null && $gap < (int) $min) return ['pruefungAbstandZuKurz', [(int) $min]];
			if ($max !== null && $gap > (int) $max) return ['pruefungAbstandZuLang', [(int) $max]];
		}

		return null;
	}

	/** @return array the list under $key, or an empty list */
	private function configArray($key)
	{
		$value = $this->_ci->config->item($key);
		return is_array($value) ? $value : [];
	}

	/** @return bool */
	private function configBool($key)
	{
		return (bool) $this->_ci->config->item($key);
	}

	/** Whole days from $from to $to (Y-m-d). @return int|null */
	private function daysBetween($from, $to)
	{
		$a = DateTime::createFromFormat('Y-m-d|', $from);
		$b = DateTime::createFromFormat('Y-m-d|', $to);
		if ($a === false || $b === false) return null;

		$diff = $a->diff($b);

		return $diff->invert ? -((int) $diff->days) : (int) $diff->days;
	}

	/** @return stdClass|null */
	private function findPruefung($pruefungen, $pruefung_id)
	{
		foreach ($pruefungen as $pruefung) {
			if ($pruefung->pruefung_id == $pruefung_id) return $pruefung;
		}

		return null;
	}

	/** @return stdClass|null the tbl_note row */
	private function getNote($note)
	{
		if (!array_key_exists($note, $this->_noteCache)) {
			$result = $this->_ci->NoteModel->load($note);
			$this->_noteCache[$note] = (!isError($result) && hasData($result)) ? getData($result)[0] : null;
		}

		return $this->_noteCache[$note];
	}

	/** @return mixed|null the key of the Note, or null if no Note has this Bezeichnung */
	private function getNoteByBezeichnung($bezeichnung)
	{
		if (!is_string($bezeichnung) || trim($bezeichnung) === '') return null;

		$result = $this->_ci->NoteModel->loadWhere(['bezeichnung' => $bezeichnung]);
		return (!isError($result) && hasData($result)) ? getData($result)[0]->note : null;
	}

	/** lehre.tbl_pruefungstyp by pruefungstyp_kurzbz. @return array */
	private function getTypes()
	{
		if ($this->_types !== null) return $this->_types;

		$this->_types = [];
		$result = $this->_ci->PruefungstypModel->load();
		if (!isError($result) && hasData($result)) {
			foreach (getData($result) as $type) $this->_types[$type->pruefungstyp_kurzbz] = $type;
		}

		return $this->_types;
	}

	/** Does a Pruefung other than $pruefung_id still wait for its result? @return bool */
	private function hasOpenPruefung($pruefungen, $pruefung_id = null)
	{
		foreach ($pruefungen as $pruefung) {
			if ($pruefung->pruefung_id != $pruefung_id && $this->isOpen($pruefung->note)) return true;
		}

		return false;
	}

	/** A Note with no better one ends the chain for good. @return bool */
	private function isClosingNote($note)
	{
		if ($note === null || $note === '') return false;

		return in_array($note, $this->getSpecialNotes()['abschliessend']);
	}

	/** 'Noch nicht eingetragen': the Pruefung waits for its result. @return bool */
	private function isOpen($note)
	{
		$open = $this->getNoteNichtEingetragen();

		return $open !== null && $note !== null && $note !== '' && $note == $open;
	}

	/**
	 * Is this Note a pass? tbl_note.positiv is also true for 'entschuldigt' and 'Teilgenommen', so ask
	 * it only about a Note that uses an Antritt.
	 *
	 * @return bool
	 */
	private function isPositiveNote($note)
	{
		if ($note === null || $note === '') return false;

		$noteRow = $this->getNote($note);
		return $noteRow ? (bool) $noteRow->positiv : false;
	}

	/** @return stdClass|null */
	private function loadPruefung($pruefung_id)
	{
		$result = $this->_ci->LePruefungModel->load($pruefung_id);
		return (!isError($result) && hasData($result)) ? getData($result)[0] : null;
	}

	/** The keys of the Noten that one configuration key names. @return array */
	private function resolveNoten($key)
	{
		$noten = [];
		foreach ($this->configArray($key) as $bezeichnung) {
			$note = $this->getNoteByBezeichnung($bezeichnung);
			if ($note !== null && !in_array($note, $noten)) $noten[] = $note;
		}

		return $noten;
	}
}
