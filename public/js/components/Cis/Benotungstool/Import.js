import BsModal from '../../Bootstrap/Modal.js';
import NotenlisteLinks from "./NotenlisteLinks.js";
import {dmyToIso, isoToDmy} from "../../../helpers/DateHelpers.js";

/**
 * The two import dialogs. The user pastes lines from Excel: one row per line, the cells separated by a
 * tab. The dialog finds the student and checks the format of each cell. It warns for a bad line and
 * emits the other rows. The parent checks the Antritt chain and sends the rows.
 *
 *   Pruefung import  Kennung, Datum, Note  -> 'import-pruefungen' [{ uid, lehreinheit_id, datum, note, punkte }]
 *   Noten import     Kennung, Note         -> 'import-lv-noten'   [{ uid, note, punkte }]
 *
 * CIS_GESAMTNOTE_IMPORT_SPALTEN_* gives the column order. In the Punkte mode the Note cell holds Punkte.
 */
export default {
	name: "BenotungstoolImport",
	components: {
		BsModal,
		NotenlisteLinks,
		Textarea: primevue.textarea
	},
	props: {
		students: { type: Array, default: () => [] },
		config: { type: Object, default: null },
		notenOptions: { type: Array, default: () => [] },
		lehrveranstaltung: { type: Object, default: null },
		semKurzbz: { type: String, default: null },
		lehreinheiten: { type: Array, default: () => [] },
		selectedLehreinheit: { type: Object, default: null }
	},
	emits: ['import-pruefungen', 'import-lv-noten'],
	data() {
		return {
			pruefungenText: '',
			lvNotenText: ''
		};
	},
	computed: {
		pruefungenHint() {
			return this.$capitalize(this.$p.t('benotungstool/notenimportHinweistextv6')) + this.kuerzelHint;
		},
		lvNotenHint() {
			return this.$capitalize(this.$p.t('benotungstool/notenimportHinweistextv5')) + this.kuerzelHint;
		},
		/** One more bullet while the short form of tbl_note.anmerkung is allowed. */
		kuerzelHint() {
			if (!this.config?.CIS_GESAMTNOTE_IMPORT_NOTENKUERZEL) return '';

			return '• ' + this.$capitalize(this.$p.t('benotungstool/c4importNotenkuerzel')) + '<br>';
		}
	},
	methods: {
		openPruefungen() {
			this.$refs.modalPruefungen.show();
		},

		openLvNoten() {
			this.$refs.modalLvNoten.show();
		},

		submitPruefungen() {
			const rows = this.parseLines(this.pruefungenText, 3, 'c4importRowNotDateFormat', this.parsePruefungRow);
			this.$refs.modalPruefungen.hide();
			this.$emit('import-pruefungen', rows);
		},

		submitLvNoten() {
			const rows = this.parseLines(this.lvNotenText, 2, 'c4importRowNotNoteFormat', this.parseLvNoteRow);
			this.$refs.modalLvNoten.hide();
			this.$emit('import-lv-noten', rows);
		},

		/** Splits the text into rows. A line with the wrong number of cells names only its line number. */
		parseLines(text, cellCount, formatPhrase, parseRow) {
			const rows = [];

			text.split('\n').forEach((line, index) => {
				if (line.trim() === '') return;

				const lineNr = index + 1;
				const cells = line.split('\t');
				if (cells.length !== cellCount) {
					this.$fhcAlert.alertWarning(this.$p.t('benotungstool/' + formatPhrase, [lineNr]));
					return;
				}

				const row = parseRow(cells, lineNr);
				if (row) rows.push(row);
			});

			return rows;
		},

		parsePruefungRow(cells, lineNr) {
			const kennung = cells[this.columnIndex('pruefung', 'kennung')];

			const student = this.findStudent(kennung);
			if (!student) return this.warnLine('c4importNoStudentFoundForIdInRow', kennung, lineNr);

			const datum = this.parseDatum(cells[this.columnIndex('pruefung', 'datum')]);
			if (!datum) return this.warnLine('c4importInvalidDateFoundForIdInRow', kennung, lineNr);

			const value = this.parseNoteCell(cells[this.columnIndex('pruefung', 'note')]);
			if (!value) return this.warnLine('c4importNoGradeFoundForIdInRow', kennung, lineNr);

			return { uid: student.uid, lehreinheit_id: student.lehreinheit_id, datum, ...value };
		},

		parseLvNoteRow(cells, lineNr) {
			const kennung = cells[this.columnIndex('noten', 'kennung')];

			const student = this.findStudent(kennung);
			if (!student) return this.warnLine('c4importNoStudentFoundForIdInRow', kennung, lineNr);

			const value = this.parseNoteCell(cells[this.columnIndex('noten', 'note')]);
			if (!value) return this.warnLine('c4importNoGradeFoundForIdInRow', kennung, lineNr);

			return { uid: student.uid, ...value };
		},

		/** @param {'pruefung'|'noten'} kind */
		columnIndex(kind, name) {
			const key = kind === 'pruefung' ? 'CIS_GESAMTNOTE_IMPORT_SPALTEN_PRUEFUNG' : 'CIS_GESAMTNOTE_IMPORT_SPALTEN_NOTEN';
			return this.config[key].indexOf(name);
		},

		/** A Kennung is a uid (it starts with a letter) or a Matrikelnummer (it starts with a digit). */
		findStudent(kennung) {
			const value = String(kennung ?? '').trim();

			if (/^[0-9]/.test(value)) return this.students.find(s => s.matrikelnr?.trim() === value);
			if (/^[a-zA-Z]/.test(value)) return this.students.find(s => s.uid?.trim() === value);

			return null;
		},

		/** @returns {string|null} 'yyyy-MM-dd', or null if the value does not match CIS_GESAMTNOTE_IMPORT_DATUMSFORMAT */
		parseDatum(value) {
			const text = String(value ?? '').trim();

			if (this.config.CIS_GESAMTNOTE_IMPORT_DATUMSFORMAT === 'yyyy-MM-dd') {
				// the round trip rejects a day that does not exist, for example 2026-02-31
				return dmyToIso(isoToDmy(text)) === text ? text : null;
			}

			return dmyToIso(text);
		},

		/**
		 * The Note cell: in the Punkte mode a number (a decimal comma counts as a point), else a Note of
		 * the Lehre, by its key or, with CIS_GESAMTNOTE_IMPORT_NOTENKUERZEL, by its short form.
		 *
		 * @returns {{note, punkte}|null}
		 */
		parseNoteCell(value) {
			const text = String(value ?? '').trim();

			if (this.config.CIS_GESAMTNOTE_PUNKTE) return { note: null, punkte: Number.parseFloat(text.replace(',', '.')) };

			const option = this.findNote(text);
			return option?.lehre ? { note: option.note, punkte: null } : null;
		},

		findNote(text) {
			if (text === '') return null;

			const byKey = this.notenOptions.find(n => String(n.note).trim() === text);
			if (byKey || !this.config.CIS_GESAMTNOTE_IMPORT_NOTENKUERZEL) return byKey ?? null;

			const byKuerzel = this.notenOptions.filter(n => String(n.anmerkung ?? '').trim().toLowerCase() === text.toLowerCase());
			return byKuerzel.length === 1 ? byKuerzel[0] : null;
		},

		warnLine(phrase, kennung, lineNr) {
			this.$fhcAlert.alertWarning(this.$p.t('benotungstool/' + phrase, [kennung, lineNr]));
			return null;
		}
	},
	template: `
		<bs-modal data-cy="modal-pruefung-import" ref="modalPruefungen" class="bootstrap-prompt" dialogClass="modal-lg" bodyClass="px-4 py-4">
			<template v-slot:title>{{ $capitalize($p.t('benotungstool/c4pruefungImportieren')) }}</template>
			<template v-slot:default>
				<div v-html="pruefungenHint"></div>
				<Textarea data-cy="pruefung-import-text" v-model="pruefungenText" rows="5" class="w-100 mt-3" :placeholder="$p.t('benotungstool/c4importPlaceholder')"></Textarea>
				<NotenlisteLinks class="mt-3" :lehrveranstaltung="lehrveranstaltung" :sem_kurzbz="semKurzbz" :lehreinheiten="lehreinheiten" :selected-lehreinheit="selectedLehreinheit" />
			</template>
			<template v-slot:footer>
				<button type="button" data-cy="pruefung-import-submit" class="btn btn-primary" @click="submitPruefungen">{{ $capitalize($p.t('benotungstool/c4import')) }}</button>
			</template>
		</bs-modal>

		<bs-modal data-cy="modal-lvnoten-import" ref="modalLvNoten" class="bootstrap-prompt" dialogClass="modal-lg" bodyClass="px-4 py-4">
			<template v-slot:title>{{ $capitalize($p.t('benotungstool/c4notenImportieren')) }}</template>
			<template v-slot:default>
				<div v-html="lvNotenHint"></div>
				<Textarea data-cy="lvnoten-import-text" v-model="lvNotenText" rows="5" class="w-100 mt-3" :placeholder="$p.t('benotungstool/c4importNotePlaceholder')"></Textarea>
				<NotenlisteLinks class="mt-3" :lehrveranstaltung="lehrveranstaltung" :sem_kurzbz="semKurzbz" :lehreinheiten="lehreinheiten" :selected-lehreinheit="selectedLehreinheit" />
			</template>
			<template v-slot:footer>
				<button type="button" data-cy="lvnoten-import-submit" class="btn btn-primary" @click="submitLvNoten">{{ $capitalize($p.t('benotungstool/c4import')) }}</button>
			</template>
		</bs-modal>
	`
};
