import BsModal from '../../Bootstrap/Modal.js';
import VueDatePicker from '../../vueDatepicker.js.php';
import ApiNoten from "../../../api/factory/noten.js";
import {debounce} from "../../../helpers/debounce.js";
import {today, toIsoDate, parseIsoDate, addDays} from "../../../helpers/DateHelpers.js";

/**
 * The Pruefung dialog. It collects the input and emits it. The parent checks the Antritt chain and
 * writes.
 *
 *   openForCell(student, pruefung, field)  a new or a changed Pruefung of one student   -> 'save-pruefung'     (student, pruefung)
 *   openForSelection()                     one new Pruefung on one day for the selection -> 'create-pruefungen' (pruefung)
 *
 * The table holds the selection. The multiselect emits 'select-students', the parent selects the rows,
 * and the prop selectedStudents follows.
 */
export default {
	name: "BenotungstoolPruefungDialog",
	components: {
		BsModal,
		Datepicker: VueDatePicker,
		Dropdown: primevue.dropdown,
		InputNumber: primevue.inputnumber,
		Multiselect: primevue.multiselect
	},
	props: {
		config: { type: Object, default: null },
		notenOptions: { type: Array, default: () => [] },
		lvId: { default: null },
		semKurzbz: { type: String, default: null },
		// the students that can get a Pruefung, in the order of the table
		studentOptions: { type: Array, default: () => [] },
		selectedStudents: { type: Array, default: () => [] }
	},
	emits: ['save-pruefung', 'create-pruefungen', 'select-students'],
	data() {
		return {
			// the student of the cell; null in the dialog for the selection
			student: null,
			pruefung: null,
			selectedDate: today(),
			selectedNote: null,
			selectedPunkte: null,
			// a later Pruefung locks the Note; the date stays editable
			noteLocked: false,
			dateMin: null,
			dateMax: null,
			// the Pruefung names the Lektor of the Lehreinheit, not the user; with several Lektoren the user selects one
			lektoren: [],
			selectedLektor: null,
			debouncedShowNoteForPunkte: null
		};
	},
	computed: {
		title() {
			if (!this.student) return this.$capitalize(this.$p.t('benotungstool/c4addNewPruefung'));

			const phrase = this.pruefung ? 'benotungstool/editPruefungFor' : 'benotungstool/createPruefungFor';
			return this.$capitalize(this.$p.t(phrase)) + ' ' + this.student.vorname + ' ' + this.student.nachname;
		},
		lehreNoten() {
			return this.notenOptions.filter(n => n.lehre === true);
		},
		/** The students that get the Pruefung. */
		students() {
			return this.student ? [this.student] : this.selectedStudents;
		},
		/** The hint: for these students the new Pruefung also writes the LV-Note. */
		withoutLvNote() {
			return this.pruefung ? [] : this.students.filter(s => !s.verlauf.hasLvNote);
		}
	},
	watch: {
		/** Warns early if the selected Note reached its limit (verlauf.notenAtLimit); the server checks it too. */
		selectedNote(option, previous) {
			if (!option || !this.student) return;

			// the changed Pruefung may keep its own Note
			const limit = this.student.verlauf.notenAtLimit[option.note];
			if (limit == null || option.note == this.pruefung?.note) return;

			this.$fhcAlert.alertWarning(this.$capitalize(this.$p.t('benotungstool/c4noteLimitUeberschritten', [
				option.bezeichnung, this.student.uid, limit
			])));
			this.selectedNote = previous;
		}
	},
	created() {
		this.debouncedShowNoteForPunkte = debounce(this.showNoteForPunkte, 500);
	},
	methods: {
		openForCell(student, pruefung, field) {
			this.student = student;
			this.pruefung = pruefung;
			this.loadLektoren([student.lehreinheit_id]);

			// the 'antritt' column has no date, so a new Pruefung starts with today
			const datum = pruefung?.datum ?? (/^\d{4}-\d{2}-\d{2}$/.test(field) ? field : null);
			this.selectedDate = datum ? parseIsoDate(datum) : today();
			this.selectedNote = pruefung ? this.notenOptions.find(n => n.note == pruefung.note) ?? null : null;
			this.selectedPunkte = pruefung?.punkte ?? null;
			// the server locks the Note of a Pruefung with a later one
			this.noteLocked = !!pruefung?.note_locked;

			const bounds = this.pruefungDateBounds(student, pruefung, field);
			this.dateMin = bounds.min;
			this.dateMax = bounds.max;

			this.$refs.modal.show();
		},

		openForSelection() {
			this.student = null;
			this.pruefung = null;
			this.loadLektoren(this.selectedStudents.map(s => s.lehreinheit_id));

			// empty, without a Note
			this.selectedDate = today();
			this.selectedNote = null;
			this.selectedPunkte = null;
			this.noteLocked = false;
			this.dateMin = null;
			this.dateMax = null;

			this.$refs.modal.show();
		},

		/**
		 * The date of a Pruefung stays strictly between its neighbours, so the order of the Antritte
		 * stays. A column without a date ('antritt_2') means a new Pruefung after all others.
		 *
		 * @returns {{min: Date|null, max: Date|null}} the inclusive bounds of the date picker
		 */
		pruefungDateBounds(student, pruefung, field) {
			const own = String(pruefung?.datum ?? field ?? '').slice(0, 10);
			const reference = /^\d{4}-\d{2}-\d{2}$/.test(own) ? own : '9999-12-31';

			let lower = null;
			let upper = null;
			(student.verlauf.pruefungen ?? []).forEach(p => {
				if (pruefung && p.pruefung_id === pruefung.pruefung_id) return;

				const day = String(p.datum ?? '').slice(0, 10);
				if (!day) return;
				if (day < reference && (lower === null || day > lower)) lower = day;
				if (day > reference && (upper === null || day < upper)) upper = day;
			});

			return {
				min: lower ? addDays(parseIsoDate(lower), 1) : null,
				max: upper ? addDays(parseIsoDate(upper), -1) : null
			};
		},

		submit() {
			if (this.student) this.submitForCell(); else this.submitForSelection();
		},

		submitForCell() {
			// a typed date can pass the bounds of the date picker
			if ((this.dateMin && this.selectedDate < this.dateMin) || (this.dateMax && this.selectedDate > this.dateMax)) {
				this.$fhcAlert.alertWarning(this.$capitalize(this.$p.t('benotungstool/pruefungDatumOutOfRangeHint')));
				return;
			}

			// Without a selection the Note stays empty; the server writes 'Noch nicht eingetragen'. Without
			// the Punkte mode a 0 would be a value: the server derives the Note from it.
			const pruefung = {
				pruefung_id: this.pruefung?.pruefung_id ?? null,
				lehreinheit_id: this.student.lehreinheit_id,
				datum: toIsoDate(this.selectedDate),
				note: this.noteLocked ? this.pruefung.note : (this.selectedNote?.note ?? ''),
				punkte: this.config.CIS_GESAMTNOTE_PUNKTE ? (this.noteLocked ? this.pruefung.punkte : this.selectedPunkte) ?? null : null,
				mitarbeiter_uid: this.selectedLektor
			};

			this.$refs.modal.hide();
			this.$emit('save-pruefung', this.student, pruefung);
		},

		submitForSelection() {
			// a missing LV-Note blocks nothing: the server writes it with the Note of this Pruefung
			const pruefung = {
				datum: toIsoDate(this.selectedDate),
				note: this.selectedNote?.note ?? null,
				punkte: this.selectedPunkte ?? null,
				mitarbeiter_uid: this.selectedLektor
			};

			this.$refs.modal.hide();
			this.$emit('create-pruefungen', pruefung);
		},

		/** The parent selects the rows in the table; the Lektoren follow the new selection. */
		selectStudents(students) {
			this.$emit('select-students', students);
			this.loadLektoren(students.map(s => s.lehreinheit_id));
		},

		/** The Lektoren of the Lehreinheit. With exactly one Lektor the selection stays empty; the server sets that Lektor. */
		loadLektoren(lehreinheitIds) {
			this.lektoren = [];
			this.selectedLektor = null;

			// a Lektor selection makes sense only if all students share one Lehreinheit
			const distinct = [...new Set(lehreinheitIds.filter(id => id != null))];
			if (distinct.length !== 1) return;

			this.$api.call(ApiNoten.getLektorenForLehreinheit(this.lvId, this.semKurzbz, distinct[0])).then(res => {
				this.lektoren = res.data.map(l => ({
					...l,
					name: `${l.vorname ?? ''} ${l.nachname ?? ''}`.trim() || l.mitarbeiter_uid
				}));

				// a selection only if there is something to select
				if (this.lektoren.length > 1) {
					const current = this.pruefung?.mitarbeiter_uid;
					this.selectedLektor = this.lektoren.some(l => l.mitarbeiter_uid === current) ? current : this.lektoren[0].mitarbeiter_uid;
				}
			});
		},

		/** In the Punkte mode the dialog shows the Note of the Punkte, from the Notenschluessel of the LV. */
		showNoteForPunkte(event) {
			const punkte = event.value === '' ? null : event.value;

			this.$api.call(ApiNoten.getNoteByPunkte(this.lvId, this.semKurzbz, punkte)).then(res => {
				if (res.data >= 0) this.selectedNote = this.notenOptions.find(n => n.note == res.data);
			});
		},

		/** The multiselect label: "uid – Nachname Vorname – Antritte: n". */
		studentLabel(student) {
			return `${student.uid} – ${student.nachname} ${student.vorname} – ${this.$capitalize(this.$p.t('benotungstool/c4antrittCountv2'))}: ${student.verlauf.antrittCount}`;
		}
	},
	template: `
		<bs-modal data-cy="modal-pruefung" ref="modal" class="bootstrap-prompt" dialogClass="modal-lg" bodyClass="px-4 py-4">
			<template v-slot:title>{{ title }}</template>
			<template v-slot:default>
				<div class="row align-items-center justify-content-center">
					<div class="col-3 text-center">{{ $capitalize($p.t('benotungstool/c4date')) }}:</div>
					<div class="col-6" data-cy="pruefung-datum">
						<datepicker v-model="selectedDate" :clearable="false" :enableTimePicker="false" format="dd.MM.yyyy" placeholder="TT.MM.JJJJ"
							:min-date="dateMin" :max-date="dateMax" :text-input="true" :auto-apply="true">
						</datepicker>
					</div>
				</div>
				<div v-if="noteLocked" class="row mt-2 justify-content-center">
					<div class="col-9 text-center text-muted small" data-cy="pruefung-note-locked">{{ $capitalize($p.t('benotungstool/pruefungNoteLockedHint')) }}</div>
				</div>
				<div v-if="config?.CIS_GESAMTNOTE_PUNKTE" class="row mt-3 align-items-center justify-content-center">
					<div class="col-3 text-center">{{ $capitalize($p.t('benotungstool/c4punkte')) }}:</div>
					<div class="col-6">
						<InputNumber data-cy="pruefung-punkte" v-model="selectedPunkte" @input="debouncedShowNoteForPunkte" :disabled="noteLocked"
							inputId="pruefungPunkte" :min="0" :max="100000" class="w-100">
						</InputNumber>
					</div>
				</div>
				<div class="row mt-3 align-items-center justify-content-center">
					<div class="col-3 text-center">{{ $capitalize($p.t('lehre/note')) }}:</div>
					<div class="col-6">
						<Dropdown data-cy="pruefung-note" :placeholder="$capitalize($p.t('lehre/note'))" :disabled="config?.CIS_GESAMTNOTE_PUNKTE || noteLocked"
							:style="{'width': '100%'}" optionLabel="bezeichnung" v-model="selectedNote" :options="lehreNoten" showClear>
						</Dropdown>
					</div>
				</div>
				<div v-if="!student" class="row mt-3 align-items-center justify-content-center">
					<div class="col-3 text-center">{{ $capitalize($p.t('benotungstool/prueflingSelectionv2')) }}:</div>
					<div class="col-6">
						<Multiselect data-cy="pruefung-students" :modelValue="selectedStudents" @update:modelValue="selectStudents" :options="studentOptions" :optionLabel="studentLabel"
							:placeholder="$capitalize($p.t('benotungstool/prueflingSelectionv2'))" :maxSelectedLabels="3" showToggleAll class="w-100" />
					</div>
				</div>
				<div v-if="lektoren.length > 1" class="row mt-3 align-items-center justify-content-center">
					<div class="col-3 text-center">{{ $capitalize($p.t('benotungstool/c4lektor')) }}:</div>
					<div class="col-6">
						<Dropdown data-cy="pruefung-lektor" :style="{'width': '100%'}" v-model="selectedLektor" :options="lektoren"
							optionLabel="name" optionValue="mitarbeiter_uid">
						</Dropdown>
					</div>
				</div>
				<div v-if="withoutLvNote.length" class="alert alert-info mt-3 mb-0 py-2" data-cy="pruefung-without-lvnote">
					<div>{{ $p.t('benotungstool/c4lvNoteWirdAngelegtHinweis') }}</div>
					<div v-if="!student" class="small mt-1">{{ withoutLvNote.map(s => s.vorname + ' ' + s.nachname + ' (' + s.uid + ')').join(', ') }}</div>
				</div>
			</template>
			<template v-slot:footer>
				<button type="button" data-cy="pruefung-submit" class="btn btn-primary" @click="submit">
					{{ student ? $capitalize($p.t('global/speichern')) : $capitalize($p.t('benotungstool/c4addNewPruefung')) }}
				</button>
			</template>
		</bs-modal>
	`
};
