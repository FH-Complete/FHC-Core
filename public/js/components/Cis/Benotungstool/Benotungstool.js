import {CoreFilterCmpt} from "../../filter/Filter.js";
import ApiNoten from "../../../api/factory/noten.js";
import ApiStudiensemester from "../../../api/factory/studiensemester.js";
import BsModal from '../../Bootstrap/Modal.js';
import BsOffcanvas from '../../Bootstrap/Offcanvas.js';
import VueDatePicker from '../../vueDatepicker.js.php';
import LehreinheitenModule from '../../DropdownModes/LehreinheitenModule.js';
import MobilityLegende from '../../Mobility/Legende.js';
import NotenlisteLinks from "./NotenlisteLinks.js";
import FhcOverlay from "../../Overlay/FhcOverlay.js";
import {debounce} from "../../../helpers/debounce.js";
import {centeredTextFormatter} from "../../../tabulator/formatter/centered.js";
import * as NotenRules from "./notenRules.js";

export const Benotungstool = {
	name: "Benotungstool",
	components: {
		BsModal,
		BsOffcanvas,
		CoreFilterCmpt,
		MobilityLegende,
		NotenlisteLinks,
		Dropdown: primevue.dropdown,
		Divider: primevue.divider,
		InputNumber: primevue.inputnumber,
		Password: primevue.password,
		Textarea: primevue.textarea,
		Datepicker: VueDatePicker,
		Multiselect: primevue.multiselect,
		FhcOverlay
	},
	props: {
		lv_id: {
			default: null,
			required: false
		},
		sem_kurzbz: {
			default: null,
			required: false
		}
	},
	data() {
		return {
			headerFiltersRestored: false,
			filtersRestored: false,
			filteredRows: null,
			filteredcount: 0,
			colLayoutRestored: false,
			sortRestored: false,
			stateRestored: false,
			persistenceID: 'notenToolTable2026-02-16',
			freezePersistenceID: 'notenToolStickyCols',
			// the identity columns the user may pin to the left while scrolling the pruefungs columns
			freezableColumnFields: ['selectCol', 'uid', 'vorname', 'nachname'],
			// which of those are currently sticky (per-column selection, persisted)
			stickyColumnSelection: JSON.parse(localStorage.getItem('notenToolStickyCols') ?? '["selectCol","uid"]'),
			debouncedFetchPunkteForPruefung: null,
			config: null, // cis config
			neuesPruefungsdatumModalVisible: false,
			loading: false,
			selectedUids: [], // shared selection state
			selectedLehreinheit: null,
			tabulatorCanBeBuilt: false,
			selectedPruefungNote: null,
			selectedPruefungDate: new Date(), // v-model for pruefung edit datepicker
			selectedPruefungPunkte: null,
			pruefungNoteLocked: false, // grade read-only when a later pruefung exists (date stays editable)
			pruefungDateMin: null,
			pruefungDateMax: null,
			distinctPruefungsDates: null,
			// The column layout of the exams: 'antritt' (one column for each attempt number) or 'datum'
			// (one column for each exam date). null uses the default from the configuration. The browser
			// keeps the choice of the user.
			pruefungsspalten: localStorage.getItem('notenToolPruefungsspalten'),
			// The students of the current entry that have no course grade. The new exam creates the
			// grade, and the dialog shows a hint before that happens.
			pruefungStudent: null,
			pruefung: null,
			// Lehrende der betroffenen Lehreinheit. Die Prüfung trägt den Lektor, nicht den, der sie
			// einträgt - bei mehreren Lehrenden wählt der Benutzer aus.
			lehrendeDerLehreinheit: [],
			selectedLehrender: null,
			password: '',
			changedNotenCounter: 0,
			tableVersion: 0, // incremented on table sort/filter/data changes so getStudentenOptions mirrors the table
			tabulatorUuid: Vue.ref(0),
			domain: '',
			importString: '', // Prüfungsimport textarea (uid + date + note)
			importStringNoten: '', // legacy Notenimport textarea (uid + note)
			teilnoten: null,
			lv: null,
			studenten: null,
			pruefungen: null,
			studiensemester: null,
			selectedSemester: null,
			isAssistenz: false,
			assistenzStudiengaenge: null,
			selectedStudiengang: null,
			lehrveranstaltungen: null,
			selectedLehrveranstaltung: null,
			tableBuiltResolve: null,
			notenOptions: null,
			notenOptionsLehre: null,
			notenOptionsPromise: null,
			tableBuiltPromise: null,
			notenTableOptions: null, // built later when noten are available
			notenTableEventHandlers: [
			{
				event: "rowSelectionChanged",
				handler: async (data, rows) => {
					// avoid an expensive update loop if selection happens in modal
					if(this.neuesPruefungsdatumModalVisible) return
					
					if(data.length == 1 && this.selectedUids.length == 1 && data[0].uid === this.selectedUids[0].uid){
						// special case to work around an internal tabulator selection quirk
						this.selectedUids = []
					} else {
						this.selectedUids = data.filter(d => d.selectable);
					}
					
				}
			},
			{
				event: "cellEdited",
				handler: async (cell) => {
					const field = cell.getField()

					if(field === 'note_vorschlag') {
						const rowData = cell.getRow().getData();
						const newValue = cell.getValue();
						const original = rowData._originalNoteVorschlag;

						// If nothing was selected, restore
						if (newValue == null || newValue === "" || newValue === original) {
							// revert value
							cell.setValue(original, true);
						}

						delete rowData._originalNoteVorschlag; // Clean up

						const row = cell.getRow()
						row.reformat() // trigger reformat of arrow

						this.detachNoteVorschlagToggle()
					}
				}
			},
			{
				event: "cellEditing",
				handler: (cell) => {
					if(cell.getField() !== 'note_vorschlag') return
					const el = cell.getElement()
					if(!el) return

					this.detachNoteVorschlagToggle() // drop any stale listener

					// clicking the already-open cell again should close the editor. Ignore clicks on the
					// dropdown options themselves so selecting a value still works.
					const listener = (ev) => {
						if(ev.target?.closest && ev.target.closest('.tabulator-edit-list')) return
						ev.stopPropagation()
						try { cell.cancelEdit() } catch(e) {}
						// the options popup is rendered outside the cell and lingers after cancelEdit -> remove it
						document.querySelectorAll('.tabulator-edit-list').forEach(list => list.remove())
					}
					this._nvCloseEl = el
					this._nvCloseListener = listener
					// defer so the very click that opened the editor doesn't immediately close it again.
					// capture phase so it still fires if the editor's input stops propagation.
					setTimeout(() => {
						if(this._nvCloseEl === el && this._nvCloseListener === listener) {
							el.addEventListener('mousedown', listener, true)
						}
					}, 0)
				}
			},
			{
				event: "cellEditCancelled",
				handler: (cell) => {
					if(cell.getField() !== 'note_vorschlag') return
					this.detachNoteVorschlagToggle()
				}
			},
			{
				event: "cellClick",
				handler: async (e, cell) => {
					const field = cell.getField()
					
					if(field == "mobility_zusatz") {
						this.$refs.drawer.show()
						e.stopPropagation()
						this.undoSelection(cell)
					} else if (field == "punkte" || field == "note_vorschlag" || field == "übernehmen") {
						this.undoSelection(cell)
					}
				}
			},
			{
				event: 'dataFiltered',
				handler: (filters, rows) => {
					this.filteredRows = rows;
					this.filteredcount = rows.length;
					this.tableVersion++; // keep the "neue Prüfung" dropdown in sync with the filtered table

					if (!this.selectedUids.length) return;

					const visibleData = new Set(rows.map(r => r.getData()));
					const filteredOut = this.selectedUids.filter(su => !visibleData.has(su));
					if (!filteredOut.length) return;

					const filteredOutSet = new Set(filteredOut);
					this.$refs.notenTable.tabulator.getSelectedRows()
						.filter(r => filteredOutSet.has(r.getData()))
						.forEach(r => r.deselect());
				}
			}
			]};
	},
	methods: {
		loadState() {
			return JSON.parse(localStorage.getItem(this.persistenceID) || "null");
		},
		saveState(table) {
			// Only save if we have finished the initial restoration 
			// AND the table actually has columns (to avoid saving empty states)
			if (!this.stateRestored) return;
			
			const rawLayout = table.getColumnLayout();
			const filteredLayout = rawLayout.filter(col => {
				if(this.notenTableOptions.columns.some(colDef => colDef.field === col.field)) return col
				return null
			})
			
			// TODO: if dynamic cols have sort/filter/headerfilter functionality filter them here before persisting
			// into local storage
			const rawSorters = table.getSorters()
			
			const rawFilters = table.getFilters()
			
			const rawHeaderFilters = table.getHeaderFilters()
			const state = {
				columns: filteredLayout.map(col => ({
					field: col.field,
					visible: col.visible,
					width: col.width,
				})),
				sort: rawSorters.map(s => ({
					field: s.field,
					dir: s.dir,
				})),
				filters: rawFilters,
				headerFilters:  rawHeaderFilters
			};

			localStorage.setItem(this.persistenceID, JSON.stringify(state));
		},
		stickyClass(field) {
			// Klasse liegt immer an; ob wirklich sticky, schaltet applyStickyColumnState per
			// container class, because updateDefinition breaks the Vue.computed titles
			return this.freezableColumnFields.includes(field) ? 'sticky-col' : undefined
		},
		recomputeStickyOffsets() {
			// position each sticky column at the cumulative width of the preceding sticky columns
			// so multiple sticky columns stack next to each other instead of overlapping at left:0
			const table = this.$refs.notenTable?.tabulator
			const el = document.getElementById('notentable')
			if(!table || !el) return

			let offset = 0
			// iterate in actual display order so column reordering is respected
			table.getColumns().forEach(col => {
				const field = col.getField()
				if(!this.freezableColumnFields.includes(field)) return

				if(this.stickyColumnSelection.includes(field)) {
					el.style.setProperty('--sl-' + field, offset + 'px')
					offset += col.getWidth()
				} else {
					el.style.setProperty('--sl-' + field, '0px')
				}
			})
		},
		applyStickyColumnState() {
			// reflect the current per-column sticky selection onto the table container
			// (a `sticky-on-<field>` class enables position:sticky for that column in CSS) + offsets
			const el = document.getElementById('notentable')
			if(!el) return
			this.freezableColumnFields.forEach(field => {
				el.classList.toggle('sticky-on-' + field, this.stickyColumnSelection.includes(field))
			})
			this.recomputeStickyOffsets()
		},
		onStickySelectionChange() {
			// MultiSelect change handler: persist + apply (instant, no table rebuild required)
			try { localStorage.setItem(this.freezePersistenceID, JSON.stringify(this.stickyColumnSelection)) } catch(e) {}
			this.applyStickyColumnState()
		},
		handleTableBuilt() {
			const table = this.$refs.notenTable.tabulator;

			this.tableBuiltResolve()
			
			const saved = this.loadState();

			// setup change eventlisteners
			const events = [
				"columnMoved", "columnResized", "columnVisibilityChanged",
				"filterChanged", "headerFilterChanged", "dataSorted",
				"columnSorted", "sortersChanged"
			];

			events.forEach(eventName => {
				table.on(eventName, () => this.saveState(table));
			});

			// keep the sticky-column offsets in sync when columns are resized / moved / shown-hidden
			["columnResized", "columnMoved", "columnVisibilityChanged"].forEach(eventName => {
				table.on(eventName, () => this.recomputeStickyOffsets());
			});

			// the "neue Prüfung" dropdown uses the same order as the table
			table.on("dataSorted", () => this.tableVersion++);

			// renderComplete restore state logic
			table.on("renderComplete", () => {
				// widths are settled here, so (re)apply the sticky container classes + cumulative offsets
				this.applyStickyColumnState();

				if (this.stateRestored) return;

				// layout restore should be happening in setupData()

				if (saved?.filters && !this.filtersRestored) {
					this.filtersRestored = true;
					table.setFilter(saved.filters);
				}

				if (saved?.headerFilters && !this.headerFiltersRestored) {
					this.headerFiltersRestored = true;
					saved.headerFilters.forEach(hf => {
						table.setHeaderFilterValue(hf.field, hf.value);
					});
				}

				if (saved?.sort?.length && !this.sortRestored) {
					this.sortRestored = true;
					setTimeout(() => {
						const sortList = saved.sort.map(s => {
							const col = table.columnManager.findColumn(s.field);
							return col ? { column: col, dir: s.dir } : null;
						}).filter(Boolean);

						if (sortList.length) {
							table.setSort(sortList);
						}
					}, 100);
				}

				this.stateRestored = true;
			});

			// finalize the promise
			if (this.tableResolve) this.tableResolve();
		},
		undoSelection(cell) {
			// checks if cells row is selected and unselects -> imitates columns which dont trigger row selection
			// but actually just revert it after the fact

			const row = cell.getRow()
			if(row.isSelected()) {
				row.deselect();
			}
		},
		detachNoteVorschlagToggle() {
			// remove the "click again to close" listener attached while a note_vorschlag editor is open
			if(this._nvCloseEl && this._nvCloseListener) {
				this._nvCloseEl.removeEventListener('mousedown', this._nvCloseListener, true)
			}
			this._nvCloseEl = null
			this._nvCloseListener = null
		},
		// using this to expose input event of editor element properly, tabulator makes it hard to access on default editor
		// implemented after tabulator/src/js/modules/edit/defaults/editors/number.js
		liveNumberEditor(cell, onRendered, success, cancel) {
			const editor = document.createElement("input");
			editor.setAttribute("type", "number");
			editor.value = cell.getValue();
			
			const row = cell.getRow()
			const rowData = row.getData()
			
			rowData._debouncedFetchNoteForPunkte = debounce(this.fetchNoteForPunkte, 500)
			editor.addEventListener("input", (e) => {
				rowData._debouncedFetchNoteForPunkte(e.target.value, row)
			});
			
			onRendered(() => {
				editor.focus();
				editor.style.height = "100%";
			});

			editor.addEventListener("change", () => success(editor.value));
			editor.addEventListener("blur", () => success(editor.value));
			editor.addEventListener("keydown", (e) => {
				if (e.keyCode === 13) success(editor.value);
				if (e.keyCode === 27) cancel();
			});

			return editor;
		},
		fetchNoteForPunkte(valueParam, row) {
			const value = valueParam == '' ? null : valueParam
			this.$api.call(ApiNoten.getNoteByPunkte(value, this.lv_id, this.sem_kurzbz)).then(res => {
				if(res?.meta?.status === 'success' && res.data >= 0) {
					row.update({note_vorschlag: res.data})
					row.reformat()
				}
			})
		},
		fetchNoteForPunktePruefung(event) {
			const value = event.value == '' ? null : event.value
			this.$api.call(ApiNoten.getNoteByPunkte(value, this.lv_id, this.sem_kurzbz)).then(res => {
				if(res?.meta?.status === 'success' && res.data >= 0) {
					this.selectedPruefungNote = this.notenOptions.find(n => n.note == res.data)
				}
			})
		},
		isValidDate_ddmmyyyy(str) {
			if (typeof str !== 'string') return false;
		
			// Check format: dd.mm.yyyy
			const regex = /^(\d{2})\.(\d{2})\.(\d{4})$/;
			const match = str.match(regex);
			if (!match) return false;
		
			// Extract date parts
			const day = parseInt(match[1], 10);
			const month = parseInt(match[2], 10);
			const year = parseInt(match[3], 10);
		
			// Check valid ranges
			if (month < 1 || month > 12 || day < 1 || day > 31) return false;
		
			// Handle months with different days and leap years
			const date = new Date(year, month - 1, day);
			return (
				date.getFullYear() === year &&
				date.getMonth() === month - 1 &&
				date.getDate() === day
			);
		},
		identifyUid(str) {
			if (typeof str !== 'string') return null;
			const firstChar = str.charAt(0);
		
			if (/^[0-9]$/.test(firstChar)) {
				return 'matrikelnr';
			} else if (/^[a-zA-Z]$/.test(firstChar)) {
				return 'uid';
			} else {
				return null;
			}
		},
		validatePruefungBulk(pruefungen) {
			// Do not send a row that cannot work. A missing course grade is no problem, because the
			// imported grade creates it.
			const validatedPruefungen = []
			pruefungen.forEach( p => {
				const student = this.studenten.find(s => s.uid === p.uid)

				// check if student antrittCount is too high already
				if(!this.canAddPruefung(student)) {
					this.$fhcAlert.alertWarning('Student ' + student.uid + ' hat bereits ' + student.hoechsterAntritt + ' Prüfungsantritte abgelegt. Die Zeile wurde übersprungen.')
					return
				}

				// get student for pruefung and check if proposed datum does not conflict (no new pruefungen before existing ones)
				const youngerPruefung = student.pruefungen.find(pr => {
					return pr.dateObj >= p.dateObj
				})
				if(youngerPruefung) {
					this.$fhcAlert.alertWarning('Student ' + student.uid + ' hat bereits eine Prüfung am '+ youngerPruefung.datum +' eingetragen. Die Zeile wurde übersprungen.')
					return
				}
				
				validatedPruefungen.push(p)
			})
			
			pruefungen.splice(0, pruefungen.length, ...validatedPruefungen);
		},
		validateNotenBulk(noten) {
			// in case we need to further validate noten, currently parser does all
		},
		parseNote(rowParts, notenbulk, rowNum) {
			const id = this.identifyUid(rowParts[0])
			const idTrimmed = rowParts[0].trim()
			let student = null
			
			if(id === 'matrikelnr') { // find student by matrnr and use uid later on
				student = this.studenten.find(s => s.matrikelnr?.trim() === idTrimmed)
			} else if(id === 'uid') {
				student = this.studenten.find(s => s.uid?.trim() === idTrimmed)
			}
			if(!student) {
				this.$fhcAlert.alertWarning(this.$p.t('benotungstool/c4importNoStudentFoundForIdInRow', [rowParts[0], rowNum]))
				return
			}

			let punkte = null
			let note = null
			if(this.config?.CIS_GESAMTNOTE_PUNKTE) {
				punkte = Number.parseFloat(rowParts[1])
			} else {
				note = rowParts[1]

				// find notenoption and check if its allowed to use in lehre
				const notenOption = this.notenOptions.find(n => n.note == note)
				if(!notenOption?.lehre) {
					this.$fhcAlert.alertWarning(this.$p.t('benotungstool/c4importNoGradeFoundForIdInRow', [rowParts[0], rowNum]))
					return
				}
			}
			
			notenbulk.push({uid: student.uid, note, punkte})
		},
		parsePruefung(rowParts, pruefungbulk, rowNum) {
			const id = this.identifyUid(rowParts[0])
			const idTrimmed = rowParts[0].trim()
			let student = null
			if(id === 'matrikelnr') { // find student by matrnr and use uid later on
				student = this.studenten.find(s => s.matrikelnr?.trim() === idTrimmed)
			} else if(id === 'uid') {
				student = this.studenten.find(s => s.uid?.trim() === idTrimmed)
			}
			if(!student) {
				this.$fhcAlert.alertWarning(this.$p.t('benotungstool/c4importNoStudentFoundForIdInRow', [rowParts[0], rowNum]))
				return
			}

			const datum = rowParts[1] // should be in 'dd.MM.yyyy'
			if(!this.isValidDate_ddmmyyyy(datum)) {
				this.$fhcAlert.alertWarning(this.$p.t('benotungstool/c4importInvalidDateFoundForIdInRow', [rowParts[0], rowNum]))
				return	
			}
			const datumParts = datum.split('.')
			const day = datumParts[0]
			const month = datumParts[1].padStart(2, '0')
			const year = datumParts[2].padStart(2, '0')
			const dateStr = `${year}-${month}-${day}`
			
			// build date obj for validation later on
			let monthInt = parseInt(month, 10)
			monthInt -= 1
			const dateObj = new Date(year, monthInt, day)

			
			let punkte = null
			let note = null
			if(this.config?.CIS_GESAMTNOTE_PUNKTE) {
				punkte = Number.parseFloat(rowParts[2]) 
			} else {
				note = rowParts[2]

				// find notenoption and check if its allowed to use in lehre
				const notenOption = this.notenOptions.find(n => n.note == note)
				if(!notenOption?.lehre) {
					this.$fhcAlert.alertWarning(this.$p.t('benotungstool/c4importNoGradeFoundForIdInRow', [rowParts[0], rowNum]))
					return
				}
			}

			pruefungbulk.push({uid: student.uid, datum: dateStr, note, punkte, lehreinheit_id: student.lehreinheit_id, dateObj})
		},
		saveNotenBulk(notenbulk) {
			this.loading = true
			this.$api.call(ApiNoten.saveNotenvorschlagBulk(this.lv_id, this.sem_kurzbz, notenbulk)).then(res => {
				if(res.meta.status === 'success') {
					// the answer uses the uid as the key: a course grade or an error message for each row
					let errorList = ''

					Object.keys(res.data ?? {}).forEach(uid => {
						const lvn = res.data[uid]
						const s = this.studenten.find(s => s.uid === uid)
						if(!s) return

						if(typeof lvn === 'string') { errorList += lvn + '\n'; return }

						s.note_vorschlag = lvn.note
						this.applyLvGesamtnote(s, lvn)
					})

					if(errorList !== '') this.$fhcAlert.alertError(errorList)

					this.$fhcAlert.alertDefault(
						'success',
						'Info',
						this.$capitalize(this.$p.t('benotungstool/notenImportSuccessAlert')),
						true
					)
				}

				this.$refs.notenTable.tabulator.redraw(true)
			}).finally(()=>{
				this.loading = false
			})
		},
		savePruefungBulk(pruefungenbulk) {
			this.loading = true
			this.$api.call(ApiNoten.saveStudentPruefungBulk(this.lv_id, this.sem_kurzbz, pruefungenbulk))
				.then((res)=> {
					if(res.meta.status === 'success') {
						// separate per-row backend rejections (localized string messages) from actual saves
						let errorList = ''
						Object.keys(res.data ?? {}).forEach(uid => {
							const entry = res.data[uid]
							if(!entry?.savedPruefung) {
								errorList += entry + '\n'
							}
						})
						if(errorList !== '') {
							this.$fhcAlert.alertError(errorList)
						}

						this.$fhcAlert.alertDefault(
							'success',
							'Info',
							this.$capitalize(this.$p.t('benotungstool/pruefungImportSuccessAlert')),
							true
						)
						this.handleAddNewPruefungenResponse(res, pruefungenbulk)
					}
				}).finally(()=>{this.loading = false})
		},
		handleAddNewPruefungenResponse(res, uids) {
			// in case we reload when changing lva_id or stsem to always consider local storage layout
			this.colLayoutRestored = false;

			const pruefungen = res.data
			uids.forEach(entry => {
				const rowResult = pruefungen[entry.uid]

				const student = this.studenten.find(s => s.uid == entry.uid)
				if(!student) return

				// savedPruefung is the proof of success, not verlauf. The server sends verlauf also
				// after a failed insert. The caller shows the error messages.
				if(!rowResult?.savedPruefung || !rowResult?.verlauf) return

				this.applyLvGesamtnote(student, rowResult.lvgesamtnote)
				this.applyVerlauf(student, rowResult.verlauf)
			})

			this.loading = false

			// keep the scroll position, so that the user still sees the same rows after a bulk entry
			this.preserveScroll(() => {
				this.applyPruefungColumns()
				const loaded = this.$refs.notenTable.tabulator.setData(this.studenten);
				this.$refs.notenTable.tabulator.redraw(true);
				return loaded
			})
		},
		/** Takes the course grade from the server answer. All entry paths use this method. */
		applyLvGesamtnote(student, lvgesamtnote) {
			if(!student || !lvgesamtnote) return

			student.lv_note = lvgesamtnote.note
			student.freigabedatum = this.parseDate(lvgesamtnote.freigabedatum)
			student.benotungsdatum = this.parseDate(lvgesamtnote.benotungsdatum)
			student.freigegeben = this.checkFreigabe(student.freigabedatum, student.benotungsdatum, student.uid)

			if(this.config?.CIS_GESAMTNOTE_PUNKTE) student.punkte = lvgesamtnote.punkte

			// teilnoten is the source of changedNoten (the counter and the list of the release)
			if(this.teilnoten && this.teilnoten[student.uid]) {
				this.teilnoten[student.uid].note_lv = lvgesamtnote.note
				this.teilnoten[student.uid].punkte_lv = lvgesamtnote.punkte
				this.teilnoten[student.uid]['freigabedatum'] = lvgesamtnote.freigabedatum
				this.teilnoten[student.uid]['benotungsdatum'] = lvgesamtnote.benotungsdatum
			}

			this.changedNotenCounter++ // computed changedNoten neu auswerten
		},
		/** Builds the row again from the server history. The client counts no attempts itself. */
		applyVerlauf(student, verlauf) {
			if(!verlauf) return

			// Take the full object. If you copy single fields, the other fields are absent (for
			// example angerechnet and hatLvNote) and the user interface is wrong until the next load.
			student.verlauf = {...verlauf}
			delete student.verlauf.pruefungen

			student.pruefungen = []
			delete student['kommPruef']

			;(verlauf.pruefungen ?? []).forEach(p => {
				p.dateObj = this.parseISODate(p.datum)
				// the final exam has its own column, and that column is always the last one
				if(p.kommissionell) student['kommPruef'] = p
				else student.pruefungen.push(p)
			})

			// Update the flat list too. It decides if the user can edit the grade proposal and the
			// points (see getColumnsDefinition).
			this.pruefungen = (this.pruefungen ?? []).filter(p => p.student_uid !== student.uid)
			;(verlauf.pruefungen ?? []).forEach(p => this.pruefungen.push(p))

			this.syncDistinctPruefungsDates()
			this.indexPruefungen(student)

			student.hoechsterAntritt = this.getAntrittCountStudent(student)
			this.recalculateSelectable(student)
			this.reformatStudentRow(student)
		},
		/** Derives the date columns from all rows. They are visible in the 'datum' mode only. */
		syncDistinctPruefungsDates() {
			const dates = new Set()
			this.studenten?.forEach(s => (s.pruefungen ?? []).forEach(p => dates.add(p.datum)))
			this.distinctPruefungsDates = [...dates].sort()
		},
		/** The field name of the column that shows one exam. */
		pruefungField(pruefung, index) {
			if(pruefung.kommissionell) return 'kommPruef'
			return this.pruefungsspaltenModus === 'antritt' ? ('antritt_' + (index + 1)) : pruefung.datum
		},
		/** Puts the exams into the column fields. Remove the old fields first, or they stay behind. */
		indexPruefungen(student) {
			(student._pruefungFields ?? []).forEach(f => { delete student[f] })

			const fields = []
			student.pruefungen.forEach((p, i) => {
				const field = this.pruefungField(p, i)
				student[field] = p
				fields.push(field)
			})

			student._pruefungFields = fields
		},
		/** One definition for an exam column. It is the same in both column modes. */
		pruefungColumnDef(field, title) {
			// must hold the fixed tracks of .pruefung-cell and a readable grade text
			const minWidth = this.pruefungsspaltenModus === 'antritt' ? 320 : 250

			return {
				title,
				field,
				formatter: this.pruefungFormatter,
				titleFormatter: this.pruefungTitleFormatter,
				sorter: this.pruefungSorter,
				topCalc: this.terminCalcFunc,
				topCalcFormatter: this.terminCalcFormatter,
				hozAlign: "center",
				widthGrow: 1,
				minWidth,
				width: minWidth,
				visible: true,
				tooltip: false
			}
		},
		/**
		 * 'antritt' makes one column for each attempt number and puts the date into the cell. Use it
		 * if each student has an own exam date. 'datum' makes one column for each exam date. Use it
		 * if the students share the exam dates.
		 */
		buildPruefungColumns() {
			if(this.pruefungsspaltenModus === 'antritt') {
				let count = 0
				this.studenten?.forEach(s => {
					// Add one more column while this row can get one more attempt. The kommissionelle
					// attempt has its own column, so it needs no attempt column.
					const kommissionellFaellig = s.verlauf?.naechsteRolle === 'kommissionell'
					const frei = this.canAddPruefung(s) && !kommissionellFaellig ? 1 : 0
					const needed = (s.pruefungen?.length ?? 0) + frei
					if(needed > count) count = needed
				})

				return Array.from({length: count}, (_, i) =>
					this.pruefungColumnDef('antritt_' + (i + 1), this.$capitalize(this.$p.t('benotungstool/c4antrittNr', [i + 1])))
				)
			}

			return (this.distinctPruefungsDates ?? []).map(date =>
				this.pruefungColumnDef(date, this.formatDatumDMY(date))
			)
		},
		/**
		 * The only place that builds the columns. setColumns builds the full table again, therefore
		 * call it only if the set of columns is different.
		 */
		applyPruefungColumns(force = false) {
			const table = this.$refs.notenTable?.tabulator
			if(!table || !this.notenTableOptions) return

			const pruefungCols = this.buildPruefungColumns()
			const key = pruefungCols.map(c => c.field).join('|')

			if(!force && key === this._pruefungColumnKey) return
			this._pruefungColumnKey = key

			const cols = [...this.notenTableOptions.columns.slice(0, -1)]
			const kommCol = this.config?.CIS_GESAMTNOTE_PRUEFUNG_KOMMPRUEF
				? this.notenTableOptions.columns[this.notenTableOptions.columns.length - 1]
				: null

			pruefungCols.forEach(c => cols.push(c))
			if(kommCol) cols.push(kommCol) // abschliessender Termin bleibt die letzte Spalte

			table.setColumns(this.restoreColumnLayout(cols))
			this.applyKommPruefSpalte()
		},
		/** Blendet die kommissionelle Spalte ein oder aus. Nach jedem setColumns nötig. */
		applyKommPruefSpalte() {
			if(!this.config?.CIS_GESAMTNOTE_PRUEFUNG_KOMMPRUEF) return

			const col = this.$refs.notenTable?.tabulator?.getColumn("kommPruef")
			if(!col) return

			if(this.kommPruefSpalteNoetig) col.show()
			else col.hide()
		},
		/**
		 * Keeps the scroll position while a table operation renders the table again.
		 *
		 * There are two traps. Read and write .tabulator-tableholder directly, because Tabulator does
		 * not update rowManager.scrollLeft horizontally. Set the position again in the next two
		 * frames, because Tabulator renders later and resets the position. Vue.nextTick is too early.
		 */
		preserveScroll(operation) {
			const holder = this.$refs.notenTable?.tabulator?.element?.querySelector('.tabulator-tableholder')

			const left = holder?.scrollLeft ?? 0
			const top = holder?.scrollTop ?? 0

			const result = operation()

			// nothing to restore, and do not work against a scroll to the top that the user wants
			if(!holder || (!left && !top)) return result

			const restore = () => {
				holder.scrollLeft = left
				holder.scrollTop = top
			}

			restore()
			requestAnimationFrame(() => { restore(); requestAnimationFrame(restore) })

			// setData gives a promise in Tabulator, therefore set the position again after the load
			if(result && typeof result.then === 'function') result.then(() => requestAnimationFrame(restore))

			return result
		},
		/**
		 * Apply the layout that the browser kept BEFORE Tabulator takes the columns. After that the
		 * internal definitions and the Vue reactives are in conflict.
		 */
		restoreColumnLayout(cols) {
			const saved = this.loadState()
			if(!saved?.columns) return cols

			const colMap = new Map(cols.map(c => [c.field, c]))
			const restored = []

			// the columns in the SAVED order, with the saved width and visibility
			saved.columns.forEach(savedCol => {
				const originalDef = colMap.get(savedCol.field)
				if(originalDef) {
					restored.push({...originalDef, width: savedCol.width, visible: savedCol.visible})
					colMap.delete(savedCol.field)
				}
			})

			colMap.forEach(def => restored.push(def)) // neue Spalten anhängen

			this.colLayoutRestored = true
			return restored
		},
		/** Changes the column mode. The browser keeps the choice. */
		setPruefungsspalten(modus) {
			if(modus !== 'antritt' && modus !== 'datum') return

			this.pruefungsspalten = modus
			localStorage.setItem('notenToolPruefungsspalten', modus)

			this.studenten?.forEach(s => this.indexPruefungen(s))

			// keep the vertical position: all columns change, but the row stays the same
			this.preserveScroll(() => {
				this.applyPruefungColumns(true)
				const loaded = this.$refs.notenTable?.tabulator?.setData(this.studenten)
				this.$refs.notenTable?.tabulator?.redraw(true)
				return loaded
			})
		},
		reformatStudentRow(student) {
			const table = this.$refs.notenTable.tabulator
			if(!table) return

			const row = table.rowManager.getRowFromDataObject(student)
			if(!row) return // Zeile noch nicht gerendert (zB direkt nach einem Datenwechsel)

			const rowComponent = row.getComponent()
			rowComponent.reformat()
		},
		importPruefungen() {
			// Exam import: each row must have a date: "UID/Matrikelnr <TAB> Datum <TAB> Note".
			// Each row creates a dated exam attempt.
			const rows = this.importString.split('\n')
			const bulk = []

			rows.forEach((r, i) => {
				if(r.trim() === '') return // ignore empty/trailing lines
				const rowParts = r.split('\t')
				const rowNum = i + 1
				if(rowParts.length === 3) {
					this.parsePruefung(rowParts, bulk, rowNum)
				} else {
					this.$fhcAlert.alertWarning(this.$p.t('benotungstool/c4importRowNotDateFormat', [rowNum]))
				}
			})

			// parsePruefung validates date + grade and resolves uid/matrikelnr;
			// validatePruefungBulk additionally checks antritte and that no earlier-dated antritt is created
			this.validatePruefungBulk(bulk)
			this.savePruefungBulk(bulk)

			this.$refs.modalContainerPruefungImport.hide()
		},
		importNoten() {
			// classic Notenimport (legacy, config gated): "UID/Matrikelnr <TAB> Note" per row,
			// writes the LV grade directly without creating a pruefung.
			const rows = this.importStringNoten.split('\n')
			const bulk = []

			rows.forEach((r, i) => {
				if(r.trim() === '') return // ignore empty/trailing lines
				const rowParts = r.split('\t')
				const rowNum = i + 1
				if(rowParts.length === 2) {
					this.parseNote(rowParts, bulk, rowNum)
				} else {
					this.$fhcAlert.alertWarning(this.$p.t('benotungstool/c4importRowNotNoteFormat', [rowNum]))
				}
			})

			this.validateNotenBulk(bulk)
			this.saveNotenBulk(bulk)

			this.$refs.modalContainerNotenImport.hide()
		},
		selectionArraysAreEqual(arr1, arr2) {
			if(arr1.length !== arr2.length) return false

			const sortFunc = (s1, s2) => {
				if(s1.nachname > s2.nachname) {
					return 1
				} else if (s1.nachname < s2.nachname) {
					return -1
				} else {
					return 0
				}
			}
			const sortedArr1 = arr1.sort(sortFunc)
			const sortedArr2 = arr2.sort(sortFunc)

			const arrsREqual = sortedArr1.every((val, index) => val === sortedArr2[index]);

			return arrsREqual
		},
		getNotenTableOptions() {

			return {
				height: 700,
				virtualDom: true,
				renderVerticalBuffer: 1000,
				index: 'uid',
				layout: 'fitData',
				placeholder: this.$capitalize(this.$p.t('global/noDataAvailable')),
				selectable: true,
				selectableRangeMode: "click", // shift+click
				selectablePersistence: false, // reset selection on table reload
				selectableCheck: this.selectableCheck,
				rowHeight: 30,
				rowFormatter: this.fixTabulatorSelectionFormatter,
				columns: this.getColumnsDefinition(),
				persistence: false,
			}

		},
		selectableCheck(row, e) {
			// a student is selectable if the student can get one more attempt
			return this.canAddPruefung(row.getData());
		},
		getColumnsDefinition() {
			const columns = []


			columns.push({
				formatter: function (cell, formatterParams, onRendered) {
					// create the built-in checkbox
					let checkbox = document.createElement("input");
					checkbox.type = "checkbox";

					// reflect the row's actual selection state so it survives re-renders (e.g. sorting/filtering)
					checkbox.checked = cell.getRow().isSelected();

					// Handle select manually
					checkbox.addEventListener("click", (e) => {
						e.stopPropagation();

						// call our function
						if (formatterParams && formatterParams.handleClick) {
							formatterParams.handleClick(e, cell);
						}
					});

					return checkbox;
				},
				titleFormatter: function (cell, formatterParams, onRendered) {
					// create the built-in checkbox
					let checkbox = document.createElement("input");
					checkbox.type = "checkbox";

					// reflect "all selectable rows selected" so the header box survives re-renders too
					onRendered(() => {
						const allowed = (cell.getTable().getRows("active") || []).filter(r => r.getData().selectable);
						checkbox.checked = allowed.length > 0 && allowed.every(r => r.isSelected());
					});

					// Handle "select all" manually
					checkbox.addEventListener("click", (e) => {
						e.stopPropagation();

						// call our function
						if (formatterParams && formatterParams.handleClick) {
							formatterParams.handleClick(e, cell);
						}
					});

					return checkbox;
				},
				hozAlign: "center",
				headerSort: false,
				formatterParams: {
					handleClick: this.selectHandler
				},
				titleFormatterParams: {
					handleClick: this.selectAllHandler
				},
				width: 50,
				minWidth: 50,
				cssClass: this.stickyClass('selectCol'),
				field: 'selectCol',
				title: ''
			})
			// Minimum widths: a column with a header filter or an editor needs more space than its
			// title. If not, the column starts too narrow and cuts the filter field and the values.
			columns.push({title: 'UID', field: 'uid', tooltip: false,  topCalc: this.sumCalcFunc, formatter: centeredTextFormatter, minWidth: 110, cssClass: this.stickyClass('uid')})
			columns.push({title: Vue.computed(() => this.$capitalize(this.$p.t('benotungstool/c4mail'))), field: 'email', formatter: this.mailFormatter, tooltip: false,  visible: false, minWidth: 170, variableHeight: true})
			columns.push({title: Vue.computed(() => this.$capitalize(this.$p.t('benotungstool/c4antrittCountv2'))), field: 'hoechsterAntritt', formatter: centeredTextFormatter, tooltip: false, minWidth: 100})
			columns.push({title: Vue.computed(() => this.$capitalize(this.$p.t('benotungstool/c4vorname'))), field: 'vorname', formatter: centeredTextFormatter, headerFilter: true, tooltip: false, minWidth: 140, cssClass: this.stickyClass('vorname')})
			columns.push({title: Vue.computed(() => this.$capitalize(this.$p.t('benotungstool/c4nachname'))), field: 'nachname', formatter: centeredTextFormatter, headerFilter: true, minWidth: 140, cssClass: this.stickyClass('nachname')})
			columns.push({title: Vue.computed(() => this.$capitalize(this.$p.t('benotungstool/c4anwesenheitsquote'))), field: 'anwquote', formatter: this.percentFormatter, minWidth: 120})
			columns.push({title: Vue.computed(() => this.$capitalize(this.$p.t('benotungstool/c4mobility'))), field: 'mobility_zusatz', formatter: centeredTextFormatter, headerFilter: true, visible: false, minWidth: 140})
			if(this.config?.CIS_GESAMTNOTE_PRUEFUNG_MOODLE_LE_NOTE) {
				columns.push({title: Vue.computed(() => this.$capitalize(this.$p.t('benotungstool/c4teilnoten'))), field: 'teilnote', formatter: this.teilnotenFormatter, minWidth: 160, variableHeight: true})
			}
			if(this.config?.CIS_GESAMTNOTE_PUNKTE) {
				columns.push({title: Vue.computed(() => this.$capitalize(this.$p.t('benotungstool/c4punkte'))), field: 'punkte',
					minWidth: 110,
					editor: this.liveNumberEditor,
					editable: (cell) => {
						const rowData = cell.getRow().getData();
						if(this.pruefungen?.find(p => p.student_uid == rowData.uid)) return false

						return true
					},
					variableHeight: true
				})
			}
			columns.push({title: Vue.computed(() => this.$capitalize(this.$p.t('benotungstool/c4notenvorschlag'))), field: 'note_vorschlag',
				minWidth: 160,
				editor: 'list',
				editorParams: (cell) => {
					// write original cell value into row to it can be retrieved if edit is cancelled without selection
					const rowData = cell.getRow().getData();
					rowData._originalNoteVorschlag = cell.getValue();

					return {
						values: this.notenOptionsLehre.map(opt => ({
							label: opt.bezeichnung,
							value: opt.note
						}))
					};
				},
				editable: (cell) => {
					// punkte features enables mapping but unable to set note directly
					if(this.config?.CIS_GESAMTNOTE_PUNKTE) return false
					const rowData = cell.getRow().getData();
					const noteOption = this.notenOptions.find(opt => opt.note == rowData.note)
					if(!noteOption) return true

					// also if student has any pruefungsnote disable noten selection
					if(this.pruefungen?.find(p => p.student_uid == rowData.uid)) return false

					return noteOption.lkt_ueberschreibbar
				},
				formatter: (cell) => {
					const rowData = cell.getRow().getData();
					const value = cell.getValue()
					const match = this.notenOptions?.find(opt => opt.note == value)
					const val =  match ? match.bezeichnung : value
					const p = this.pruefungen?.find(p => p.student_uid == rowData.uid)
					let style = ''

					if(val === undefined) return ''
					if(p || !match?.lkt_ueberschreibbar) style = 'color: gray;font-style: italic; background-color: #f0f0f0;pointer-events: none;opacity: 0.6;user-select: none;cursor: not-allowed;'
					return '<div style="'+style+'">' + val + '</div>'
				}
			})
			columns.push({title: Vue.computed(() => this.$capitalize(this.$p.t('benotungstool/c4notenvorschlagUebernehmen'))), field: 'übernehmen', width: 150, minWidth: 100, hozAlign: 'center', formatter: this.arrowFormatter,
				cellClick: this.saveNote,
				variableHeight: true})
			columns.push({title: Vue.computed(() => this.$capitalize(this.$p.t('benotungstool/c4lvnote'))), field: 'lv_note',
				minWidth: 160,
				formatter: this.notenFormatter,
				headerFilter: 'list',
				headerFilterParams: () => {
					return { values: ["\u00A0",this.$p.t('benotungstool/c4noteEmpty') ,this.$p.t('benotungstool/c4positiv'), this.$p.t('benotungstool/c4negativ') ,...this.notenOptions.map(opt => opt.bezeichnung)] }
				},
				headerFilterFunc: this.notenFilterFunc
			})
			columns.push({title: Vue.computed(() => this.$capitalize(this.$p.t('benotungstool/c4freigabe'))), field: 'freigegeben', formatter: this.freigabeFormatter, minWidth: 130, variableHeight: true})
			columns.push({title: Vue.computed(() => this.$capitalize(this.$p.t('benotungstool/c4zeugnisnote'))),
				field: 'note',
				minWidth: 160,
				formatter: this.notenFormatter,
				topCalc: this.negativeNotenCalc,
				topCalcFormatter: this.negativeNotenCalcFormatter,
				headerFilter: 'list',
				headerFilterParams: () => {
					return { values: ["\u00A0", this.$p.t('benotungstool/c4noteEmpty'),this.$p.t('benotungstool/c4positiv'), this.$p.t('benotungstool/c4negativ') ,...this.notenOptions.map(opt => opt.bezeichnung)] }
				},
				headerFilterFunc: this.notenFilterFunc
			})
			columns.push({title: Vue.computed(() => this.kommPruefSpaltentitel),
				field: 'kommPruef', widthGrow: 1,
				formatter: this.pruefungFormatter,
				sorter: this.pruefungSorter,
				topCalc: this.terminCalcFunc,
				topCalcFormatter: this.terminCalcFormatter,
				hozAlign:"center", minWidth: 200, visible: false,
				tooltip: false
			})
		
			return columns
		},
		pruefungSorter(a, b, aRow, bRow, column, dir, params) {
			if (a === null || typeof a === "undefined" || a === '') return -1;
			if (b === null || typeof b === "undefined" || b === '') return 1;

			// sort by notenvalue since pruefungen are in same date by column
			return a.note - b.note
		},
		selectHandler(e, cell) {
			const row = cell.getRow();

			if(row.isSelected()){
				row.deselect();
			} else {
				row.select();
			}

			// stop built-in handler
			e.stopPropagation();
			return false;
		},
		selectAllHandler(e, cell) {
			const table = cell.getTable();
			const rows = this.filteredRows ?? table.getRows();

			const allowed = rows.filter(r => r.getData().selectable);
			const selected = allowed.every(r => r.isSelected());

			if (selected) {
				allowed.forEach(r => r.deselect());
				e.target.checked = false;
			} else {
				allowed.forEach(r => r.select());
				e.target.checked = true;
			}

			e.stopPropagation();
			return false;
		},
		fixTabulatorSelectionFormatter(row) {
			// if a row is not selectable, remove the checkbox from the dom

			const data = row.getData()

			// test hook: you can address a row by its uid (see tests/cypress/support/pages)
			row.getElement().setAttribute('data-cy', 'student-row-' + data.uid)

			if(!this.canAddPruefung(data)) {
				const el = row.getElement()
				el.children[0]?.children[0]?.remove()
				
				el.classList.remove("tabulator-selectable");
				el.classList.add("tabulator-unselectable");
			} else {
				const el = row.getElement()

				el.classList.add("tabulator-selectable");
				el.classList.remove("tabulator-unselectable");
			}
		},
		terminCalcFunc(entries) {
			return entries.reduce((acc, cur) => {
				if(cur !== undefined) acc++
				return acc
			}, 0)
		},
		terminCalcFormatter(cell) {
			const cellval = cell.getValue()
			return this.$capitalize(this.$p.t('benotungstool/prueflingSelectionv2'))+': ' + cellval
		},
		negativeNotenCalcFormatter(cell) {
			const cellval = cell.getValue()
			return this.$capitalize(this.$p.t('benotungstool/c4negativ'))+': ' + cellval
		},
		negativeNotenCalc(entries) {
			return entries.reduce((acc, cur) => {
				const opt = this.notenOptions.find(opt => opt.note == cur)
				if(opt && !opt.positiv) acc++
				return acc
			}, 0)
		},
		sumCalcFunc(entries) {
			return entries.length	
		},
		notenFilterFunc(filterVal, rowVal) {
			// option of the searchterm
			const opt = this.notenOptions.find(opt => opt.bezeichnung === filterVal)
			// searchterm is not empty fallback and the note finds an option match
			if(rowVal !== null && rowVal !== undefined && opt?.note == rowVal) {
				return true
			}
			
			// empty searchterm fallback to show all
			if(filterVal === "\u00A0" || filterVal === "" || filterVal === null) {
				return true
			}
			
			// specific searchterm cases
			if(filterVal === this.$capitalize(this.$p.t('benotungstool/c4positiv'))) {
				// option of the rowValue
				const valOpt = this.notenOptions.find(opt => opt.note == rowVal)
				if(!valOpt) return false
				return valOpt.positiv
			}
			if(filterVal === this.$capitalize(this.$p.t('benotungstool/c4negativ'))) {
				const valOpt = this.notenOptions.find(opt => opt.note == rowVal)
				if(!valOpt) return false
				return !valOpt.positiv
			}
			if(filterVal === this.$capitalize(this.$p.t('benotungstool/c4noteEmpty')) && rowVal === null) {
				return true
			}
			
			return false
		},
		parseDate(timestamp) {
			if(!timestamp) return null
			const [datePart, timePart] = timestamp.split(" ");
			const [year, month, day] = datePart.split("-").map(Number);
			const [hour, minute, second] = timePart.split(":").map(Number);
			return new Date(year, month - 1, day, hour, minute, second);
		},
		checkFreigabe(freigabedatum, benotungsdatum) {
			return NotenRules.checkFreigabe(freigabedatum, benotungsdatum)
		},
		unselectableFormatter(row) {
			
		},
		notenFormatter(cell) {
			const value = cell.getValue()
			const field = cell.getField()
			let style = 'display: flex; justify-content: start; align-items: center; height: 100%;';
			// If the transcript grade is different from the released grade, the transcript grade gets
			// a red border.
			
			
			const data = cell.getData()
			if(field == 'note' && data.note && data.note != data.lv_note) {
				style += 'color:red; border-color:red; border-style:solid; border-width:1px;'
			}

			const match = this.notenOptions.find(opt => opt.note == value)
			const val = match ? match.bezeichnung : value
			if(val) return '<div style="'+style+'">' + val + '</div>'
			else return ''
			
		},
		freigabeFormatter(cell) {
			const value = cell.getValue()

			let style = 'display: flex; justify-content: center; align-items: center; height: 100%;'

			// data-state makes the state testable, and the test does not depend on the icon
			const wrap = (icon) =>
				'<div data-cy="freigabe-state" data-state="' + value + '" style="' + style + '">' + icon + '</div>'

			if(value === 'ok') {
				return wrap('<i class="fa fa-circle-check" style="color:green"></i>')
			} else if (value === 'offen') {
				return wrap('<i class="fa-regular fa-circle"></i>')
			} else if (value === 'changed') {
				return wrap('<i class="fa fa-circle-check"></i>')
			}

			return value
		},
		async saveNote(e, cell) { // Notenvorschlag freigeben
			const row = cell.getRow()
			const data = row.getData()

			if(!data.note_vorschlag) return
			if(data.note_vorschlag == data.lv_note) return

			// as soon as exams exist, the grade belongs to the exam history
			if(data.pruefungen.length) return

			this.loading = true
			this.$api.call(ApiNoten.saveNotenvorschlag(this.lv_id, this.sem_kurzbz, data.uid, data.note_vorschlag, data.punkte))
				.then((res) => {
				if (res.meta.status === 'success') {
					const s = this.studenten.find(s => s.uid === data.uid)

					// the same path as after an exam, so that the release state is the same
					this.applyLvGesamtnote(s, res.data[0])

					row.update({ lv_note: s.lv_note, freigegeben: s.freigegeben })
					row.reformat() // trigger reformat of arrow
				}
			}).finally(()=>this.loading = false)
		},
		teilnotenFormatter(cell) {
			const val = cell.getValue()

			let style = 'white-space: pre-line;'

			return '<div style="">'+val+'</div>'
		},
		/** Tells you if this row can get one more attempt. The value comes from the server. */
		canAddPruefung(student) {
			return NotenRules.canAddPruefung(student, this.config)
		},
		hasLaterPruefung(student, pruefung) {
			// an exam with a later date locks the grade; you can still correct the date
			if(!pruefung) return false

			const alle = [...(student.pruefungen ?? [])]
			if(student.kommPruef) alle.push(student.kommPruef)

			return alle.some(p => p.pruefung_id != pruefung.pruefung_id && p.position > pruefung.position)
		},
		/** The cell of an exam column. indexPruefungen decides which attempt the cell shows. */
		pruefungFormatter(cell) {
			const data = cell.getData()

			// credited: the row is visible, but it gets no exams
			if(data.verlauf?.angerechnet) return ''

			// A transcript grade that you cannot overwrite locks the exam columns. Without a transcript
			// grade the columns stay open, because the first attempt starts there.
			const noteDef = data.note ? this.notenOptions.find(n => n.note == data.note) : null
			if(noteDef && !noteDef.lkt_ueberschreibbar) return ''

			const field = cell.getColumn().getField()
			const studentPruefung = data[field]

			// in the attempt mode the column has no date, therefore the cell shows it
			const antrittModus = this.pruefungsspaltenModus === 'antritt'

			// fixed tracks grade | date | action, so that they align in all rows
			const rowDiv = document.createElement('div')
			rowDiv.className = antrittModus ? 'pruefung-cell mit-datum' : 'pruefung-cell'

			const addSlot = (className, content) => {
				const div = document.createElement('div')
				div.className = className

				if(typeof content === 'string') div.textContent = content
				else if(content instanceof HTMLElement) div.appendChild(content)

				rowDiv.appendChild(div)
				return div
			}

			const addButton = (label, title, onClick, cy) => {
				const button = document.createElement('button')
				button.className = 'btn btn-outline-secondary'
				button.textContent = label
				button.dataset.cy = cy
				if(title) button.title = title
				button.addEventListener('click', onClick)

				addSlot('pruefung-action', button)
			}

			if(studentPruefung) {
				// The badge shows the attempt number. A kommissionelle exam shows K, because its number
				// follows from the position anyway. An exam that does not count (excused, not assessed)
				// has no number and gets a neutral style.
				const attemptLabel = studentPruefung.kommissionell
					? 'K'
					: (studentPruefung.antritt_nr ? String(studentPruefung.antritt_nr) : '–')
				const attemptClass = studentPruefung.kommissionell
					? 'attempt-k'
					: (studentPruefung.zaehlt ? ('attempt-t' + Math.min(studentPruefung.antritt_nr, 3)) : 'attempt-none')

				rowDiv.classList.add('pruefung-badge', attemptClass)
				rowDiv.setAttribute('data-attempt', attemptLabel)
				rowDiv.setAttribute('data-cy', 'pruefung-cell')
				rowDiv.setAttribute('data-note', studentPruefung.note)

				// The student administration owns the escalation (zusKommPruef), therefore that cell has
				// no action button and the space belongs to the grade name.
				const nurAnzeige = studentPruefung.kommissionell && !studentPruefung.zaehlt
				if(nurAnzeige) rowDiv.classList.add('ohne-aktion')

				const noteDefEntry = this.notenOptions.find(n => n.note == studentPruefung.note)
				const bezeichnung = noteDefEntry?.bezeichnung || ''
				addSlot('pruefung-note', bezeichnung).title = bezeichnung

				if(antrittModus) addSlot('pruefung-datum', this.formatDatumDMY(studentPruefung.datum))

				if(nurAnzeige) return rowDiv

				// An exam with a later date locks the GRADE, but you can still correct the date. The
				// button stays active, and the dialog locks the grade field.
				const noteLocked = this.hasLaterPruefung(data, studentPruefung)

				addButton(
					this.$capitalize(this.$p.t('benotungstool/changePruefungButtonText')),
					noteLocked ? this.$capitalize(this.$p.t('benotungstool/pruefungNoteLockedHint')) : null,
					() => this.openPruefungModal(data, studentPruefung, field),
					'btn-pruefung-edit'
				)

				return rowDiv
			}

			// An empty cell gets an add button only if one more attempt is possible and if the column
			// is after all exams that exist.
			if(!this.canAddPruefung(data)) return ''

			// The last attempt is kommissionell, therefore only the kommPruef column may take it. All
			// other columns stay empty at that point.
			const kommissionellFaellig = data.verlauf?.naechsteRolle === 'kommissionell'
			if(field === 'kommPruef' ? !kommissionellFaellig : kommissionellFaellig) return ''

			if(antrittModus && field !== 'kommPruef') {
				// only the next free attempt column shows an add button
				if(field !== ('antritt_' + ((data.pruefungen?.length ?? 0) + 1))) return ''
			} else if(field !== 'kommPruef') {
				// no new exam before an exam that exists
				if((data.pruefungen ?? []).some(p => p.datum >= field)) return ''
			}

			addButton(
				this.$capitalize(this.$p.t('benotungstool/addPruefungButtonText')),
				null,
				() => this.openPruefungModal(data, null, field),
				'btn-pruefung-add'
			)

			return rowDiv
		},
		parseISODate(iso) {
			const parts = (iso ?? '').slice(0, 10).split('-')
			if(parts.length !== 3) return null
			return new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]))
		},
		formatDatumDMY(datum) {
			// 'YYYY-MM-DD[ ...]' -> 'DD.MM.YYYY'
			const parts = (datum ?? '').slice(0, 10).split('-')
			if(parts.length !== 3) return ''
			return `${parts[2]}.${parts[1]}.${parts[0]}`
		},
		addDays(date, days) {
			const d = new Date(date)
			d.setDate(d.getDate() + days)
			return d
		},
		getPruefungDateBounds(student, pruefung, fallbackDate) {
			// the exam date must stay strictly between the dates of the chronologically adjacent
			// pruefungen so the attempt order is preserved. Returns inclusive datepicker bounds.
			// A column name that is not a date ('antritt_2', 'kommPruef') means a new exam after all
			// existing ones. Use a date in the far future, so every existing exam is a lower bound.
			const raw = (pruefung?.datum ?? fallbackDate ?? '').slice(0, 10)
			const refDate = /^\d{4}-\d{2}-\d{2}$/.test(raw) ? raw : '9999-12-31'
			let lower = null, upper = null;
			(student.pruefungen ?? []).forEach(p => {
				if(pruefung && p.pruefung_id != null && pruefung.pruefung_id != null && p.pruefung_id === pruefung.pruefung_id) return
				const d = (p.datum ?? '').slice(0, 10)
				if(!d) return
				if(d < refDate) { if(lower === null || d > lower) lower = d }
				else if(d > refDate) { if(upper === null || d < upper) upper = d }
			})
			return {
				min: lower ? this.addDays(this.parseISODate(lower), 1) : null,
				max: upper ? this.addDays(this.parseISODate(upper), -1) : null
			}
		},
		/**
		 * Lädt die Lehrenden einer Lehreinheit für den Dialog. Bei genau einem Lehrenden bleibt die
		 * Auswahl leer; der Server setzt ihn dann selbst ein.
		 */
		ladeLehrende(lehreinheitIds) {
			this.lehrendeDerLehreinheit = []
			this.selectedLehrender = null

			const eindeutig = [...new Set((lehreinheitIds ?? []).filter(id => id != null))]
			if(eindeutig.length !== 1) return

			this.$api.call(ApiNoten.getLehrendeFuerLehreinheit(eindeutig[0], this.lv_id, this.sem_kurzbz))
				.then(res => {
					if(res?.meta?.status !== 'success') return
					this.lehrendeDerLehreinheit = (res.data ?? []).map(l => ({
						...l,
						anzeigename: `${l.vorname ?? ''} ${l.nachname ?? ''}`.trim() || l.mitarbeiter_uid
					}))

					// nur eine Auswahl anbieten, wenn es etwas zu wählen gibt
					if(this.lehrendeDerLehreinheit.length > 1) {
						const bestehend = this.pruefung?.mitarbeiter_uid
						this.selectedLehrender = this.lehrendeDerLehreinheit
							.some(l => l.mitarbeiter_uid === bestehend)
								? bestehend
								: this.lehrendeDerLehreinheit[0].mitarbeiter_uid
					}
				})
		},
		openPruefungModal(student, pruefung = null, field) {
			this.pruefungStudent = student
			this.pruefung = pruefung
			this.ladeLehrende([student?.lehreinheit_id])

			// In the attempt mode the column has no date, therefore a new exam starts with today.
			const dateStr = this.pruefung?.datum ?? (/^\d{4}-\d{2}-\d{2}$/.test(field) ? field : null)
			this.selectedPruefungDate = dateStr ? this.parseISODate(dateStr) : new Date()

			// grade is locked once a later pruefung exists; only the date may be corrected
			this.pruefungNoteLocked = !!(pruefung && this.hasLaterPruefung(student, pruefung))

			// constrain the date to stay between the neighbouring exam dates
			const bounds = this.getPruefungDateBounds(student, pruefung, field)
			this.pruefungDateMin = bounds.min
			this.pruefungDateMax = bounds.max


			if(this.pruefung?.note) {
				this.selectedPruefungNote = this.notenOptions.find(n => n.note == this.pruefung.note)
			} else {
				this.selectedPruefungNote = null
			}

			this.selectedPruefungPunkte = this.pruefung?.punkte ?? null

			this.$refs.modalContainerPruefung.show()
		},
		pruefungTitleFormatter(cell) {
			const def = cell.getColumn().getDefinition()
			return def.title;
		},
		arrowFormatter(cell) {
			const row = cell.getRow()
			const data = row.getData()
			
			let style = 'display: flex; justify-content: center; align-items: center; height: 100%;'
			
			if(!data.note_vorschlag || (data.note_vorschlag == data.lv_note) || data.pruefungen.length) {
				// arrow to ambiguous in meaning, use str8 forward worded button here instead
				// uncolored arrow
				// return '<div style="'+style+'">' +
				// 	'<i class="fa fa-arrow-right"></i></div>'

				return ''
			}
			
			const button = document.createElement('button');
			button.className = 'btn btn-outline-secondary';
			button.dataset.cy = 'btn-uebernehmen';
			button.textContent = this.$capitalize(this.$p.t('benotungstool/c4notenvorschlagUebernehmen'));
			return button;
			
			// // can save a notenvorschlag -> colored
			// return '<div style="'+style+'">' +
			// 	'<i class="fa fa-arrow-right fa-2xl" style="color:#00649C"></i></div>'
		},
		mailFormatter(cell) {
			const val = cell.getValue()

			let style = 'display: flex; justify-content: center; align-items: center; height: 100%;'
			
			return '<div style="'+style+'">' +
				'<a href='+val+'><i class="fa fa-envelope" style="color:#00649C"></i></a></div>'
		},
		percentFormatter(cell) {
			const data = cell.getData()
			const val = data.anwquote ?? '-'
			return '<div style="display: flex; justify-content: center; align-items: center; height: 100%">'+ val + ' %</div>'	
		},
		buildMailToLink(student){
			return 'mailto:' + student.uid +'@'+ this.domain
		},
		tableResolve(resolve) {
			this.tableBuiltResolve = resolve
		},
		notenOptionsResolve(resolve) {
			this.notenOptionsResolve = resolve
		},
		async setupData(data){
			// in case we reload when changing lva_id or stsem to always consider local storage layout
			this.colLayoutRestored = false;

			this.studenten = data[0] ?? []
			this.studenten.forEach(s => {
				s.pruefungen = []
				// fallback label; the full label incl. Antritte is set once hoechsterAntritt is known (below)
				s.infoString = `${s.uid} – ${s.nachname} ${s.vorname}`// used for multiselect (uid first); full label incl. Antritte set below
			})
			this.pruefungen = data[1] ?? []
			this.domain = data[2]

			// holds the grade proposals from Moodle, lv_note and the exam history of each student
			this.teilnoten = data[3] ?? []

			this.distinctPruefungsDates = []

			this.pruefungen?.forEach(p => {
				const student = this.studenten.find(s => s.uid === p.student_uid)
				if(!student) return

				p.dateObj = this.parseISODate(p.datum)

				// the final exam has its own column, and that column is always the last one
				if(p.kommissionell) {
					student['kommPruef'] = p
				} else {
					student.pruefungen.push(p)
					if(!this.distinctPruefungsDates.includes(p.datum)) this.distinctPruefungsDates.push(p.datum)
				}
			})

			this.distinctPruefungsDates.sort()

			this.studenten?.forEach(s => {
				// the server sends the exams in the order of the history; this only makes it sure
				s.pruefungen.sort((p1, p2) => (p1.position ?? 0) - (p2.position ?? 0))

				// the attempt count, the limits and the next possible role come from the server
				s.verlauf = this.teilnoten[s.uid]?.verlauf ?? null
				this.indexPruefungen(s)

				s.hoechsterAntritt = this.getAntrittCountStudent(s)
				// multiselect label, uid first: "uid – Nachname Vorname – Antritte: n"
				// (order in the dropdown mirrors the table, see getStudentenOptions)
				s.infoString = `${s.uid} – ${s.nachname} ${s.vorname} – ${this.$capitalize(this.$p.t('benotungstool/c4antrittCountv2'))}: ${s.hoechsterAntritt}`
				s.email = this.buildMailToLink(s)
				s.lv_note = this.teilnoten[s.uid].note_lv
				s.freigabedatum = this.parseDate(this.teilnoten[s.uid]['freigabedatum'])
				s.benotungsdatum = this.parseDate(this.teilnoten[s.uid]['benotungsdatum'])
				s.freigegeben = this.checkFreigabe(s.freigabedatum, s.benotungsdatum, s.uid);

				s.punkte = this.teilnoten[s.uid].punkte_lv

				const grades = this.teilnoten[s.uid].grades
				s.teilnote = ''
				s.mobility_zusatz = this.teilnoten[s.uid].mobility_zusatz
				grades.forEach(g => {
					// some moodle noten are numeric, some are strings like "Sehr Gut", "Bestanden" etc...
					const notenOption = this.notenOptions.find(n=>n.note == g.grade || n.bezeichnung == g.grade)
					if(notenOption.positiv) s.teilnote += ('<span>'+g.text +'</span>'+ '<br/>')
					else s.teilnote += ('<span style="color: red;">'+g.text +'</span>'+ '<br/>')
				})

				this.recalculateSelectable(s)
			})

			// a new course: always build the columns again, and reset the scroll position
			this.applyPruefungColumns(true)

			this.$refs.notenTable.tabulator.setData(this.studenten);
			this.$refs.notenTable.tabulator.redraw(true);

			// reflect the sticky-column selection on the freshly (re)built columns
			this.applyStickyColumnState()

			// build the options of the "neue Prüfung" dropdown again for the new data
			this.tableVersion++

			// keep the loading overlay up until the browser has actually painted the rebuilt table
			// (the caller, loadNoten, awaits setupData before clearing `loading`)
			await new Promise(requestAnimationFrame)
		},
		loadNoten(lv_id, sem_kurzbz) {
			if (!lv_id || !sem_kurzbz) return
			this.loading = true
			this.$api.call(ApiNoten.getStudentenNoten(lv_id, sem_kurzbz))
				.then(async res => {
					if(res?.data) await this.setupData(res.data)
					else {
						this.$fhcAlert.alertError('no data found')
						this.$refs.notenTable.tabulator.setData([]);
						this.$refs.notenTable.tabulator.redraw(true);
					}
					if(res?.meta?.getExternalGradesError) this.$fhcAlert.alertError(this.$p.t('benotungstool/c4moodleTeilnotenError', [res.meta.getExternalGradesError]))
				}).finally(()=> {
					this.loading = false
			})
		},
		handleUuidDefined(uuid) {
			this.tabulatorUuid = uuid
		},
		calcMaxTableHeight() {
			const tableID = this.tabulatorUuid ? ('-' + this.tabulatorUuid) : ''
			const tableDataSet = document.getElementById('filterTableDataset' + tableID);
			if(!tableDataSet) return
			const rect = tableDataSet.getBoundingClientRect();

			this.notenTableOptions.height = window.visualViewport.height - rect.top - 50
			this.$refs.notenTable.tabulator.setHeight(this.notenTableOptions.height)
		},
		setLehrveranstaltungen(lvaData, preselectLvId = null) {
			this.lehrveranstaltungen = lvaData
			this.lehrveranstaltungen.forEach(lva => {
				lva.fullString = `${lva.stg_kurzbz} - ${lva.lv_semester} - ${lva.orgform}: ${lva.lv_bezeichnung}`
			})
			this.selectedLehrveranstaltung = preselectLvId
				? this.lehrveranstaltungen.find(lva => lva.lehrveranstaltung_id == preselectLvId) ?? null
				: null
		},
		// An assistant selects a degree programme first, and then sees the courses of that
		// entitlement. A teacher sees the own courses directly.
		setupLvSource(sem_kurzbz, preselectLvId = null) {
			return this.$api.call(ApiNoten.getBenotungstoolContext(sem_kurzbz, preselectLvId)).then(res => {
				this.isAssistenz = !!res.data?.isAssistenz

				if (this.isAssistenz) {
					this.setAssistenzStudiengaenge(res.data?.studiengaenge ?? [])

					// deep-link: preselect the requested LV's Studiengang, load its LVs and preselect the LV.
					// Without a (resolvable) deep-link, wait for a manual Studiengang selection.
					const preStgKz = res.data?.preselectStudiengang_kz
					this.selectedStudiengang = preStgKz
						? this.assistenzStudiengaenge.find(s => s.studiengang_kz == preStgKz) ?? null
						: null

					if (this.selectedStudiengang) {
						return this.loadLehrveranstaltungenForStudiengang(this.selectedStudiengang.studiengang_kz, sem_kurzbz, preselectLvId)
					}

					this.lehrveranstaltungen = null
					this.selectedLehrveranstaltung = null
				} else {
					// teacher: assigned LVs delivered by the same context call (deep-linked lv_id preselected)
					this.setLehrveranstaltungen(res.data?.lehrveranstaltungen ?? [], preselectLvId)
				}
			})
		},
		setAssistenzStudiengaenge(studiengaenge) {
			this.assistenzStudiengaenge = studiengaenge
			this.assistenzStudiengaenge.forEach(stg => {
				stg.fullString = `${stg.kuerzel} - ${stg.bezeichnung}`
			})
		},
		loadLehrveranstaltungenForStudiengang(studiengang_kz, sem_kurzbz, preselectLvId = null) {
			return this.$api.call(ApiNoten.getLvForStudiengang(studiengang_kz, sem_kurzbz)).then(res => {
				this.setLehrveranstaltungen(res.data, preselectLvId)
			})
		},
		stgChanged(e) {
			const sem = this.selectedSemester?.studiensemester_kurzbz ?? this.sem_kurzbz
			const stg = e.value?.studiengang_kz ?? null

			this.selectedLehrveranstaltung = null
			if (stg && sem) {
				this.loading = true
				this.loadLehrveranstaltungenForStudiengang(stg, sem).finally(() => this.loading = false)
			} else {
				this.lehrveranstaltungen = null
			}
		},
		getOptionLabelStg(option) {
			return option.fullString
		},
		async setupCreated() {
			this.loading = true

			this.debouncedFetchPunkteForPruefung = debounce(this.fetchNoteForPunktePruefung, 500)
			
			// fetch cis config regarding gesamtnoteneingabe, needs to be fetched before setup can finish
			const configPromise = this.$api.call(ApiNoten.getCisConfig()).then(res => {
				this.config = res.data
			})

			this.$api.call(ApiStudiensemester.getAllStudiensemesterAndAktOrNext()).then(res => {
				this.studiensemester = res.data[0]
				
				let defaultSem = this.sem_kurzbz
					? this.studiensemester.find(s => s.studiensemester_kurzbz === this.sem_kurzbz)
					: null

				if (!defaultSem) {
					const aktOrNext = res.data[1]
					let aktKurzbz = null
					if (typeof aktOrNext === 'string')        aktKurzbz = aktOrNext
					else if (Array.isArray(aktOrNext))        aktKurzbz = aktOrNext[0]?.studiensemester_kurzbz ?? aktOrNext[0]
					else if (aktOrNext && typeof aktOrNext === 'object') aktKurzbz = aktOrNext.studiensemester_kurzbz

					defaultSem = this.studiensemester.find(s => s.studiensemester_kurzbz === aktKurzbz)
				}

				this.selectedSemester = defaultSem ?? this.studiensemester[0] ?? null

				const sem = this.selectedSemester?.studiensemester_kurzbz
				if (sem) this.setupLvSource(sem, this.lv_id)
			})
			
			LehreinheitenModule.setupContext(this.$.appContext.config.globalProperties)
			LehreinheitenModule.bindParams(Vue.ref(Vue.computed(() => this.LeDropdownParams)));
			
			// fetch noten dropdown
			await this.$api.call(ApiNoten.getNoten()).then(async res => {
				this.notenOptions = res.data
				this.notenOptionsLehre = res.data.filter(n => n.lehre === true)
				
				await configPromise
				this.notenTableOptions = this.getNotenTableOptions()
				this.tabulatorCanBeBuilt = true // because promises would be more work and not much better here
			}).catch(e => {
				this.loading = false
			})
			
		},
		async setupMounted() {
			this.tableBuiltPromise = new Promise(this.tableResolve)
			await this.tableBuiltPromise

			if (this.lv_id && this.sem_kurzbz) {
				this.loadNoten(this.lv_id, this.sem_kurzbz)
			} else {
				this.loading = false
			}
			this.calcMaxTableHeight()
		},
		lvChanged(e) {
			const sem = this.selectedSemester?.studiensemester_kurzbz ?? this.sem_kurzbz
			this.$router.push({
				name: "Benotungstool",
				params: {
					sem_kurzbz: sem,
					lv_id: e.value.lehrveranstaltung_id
				}
			})
			
			// reload data
			this.loadNoten(e.value.lehrveranstaltung_id, sem)
		},
		ssChanged(e) {
			const sem = e.value.studiensemester_kurzbz
			const keepLvId = this.selectedLehrveranstaltung?.lehrveranstaltung_id ?? this.lv_id
			const keepStg = this.selectedStudiengang?.studiengang_kz

			this.loading = true
			this.$api.call(ApiNoten.getBenotungstoolContext(sem)).then(res => {
				this.isAssistenz = !!res.data?.isAssistenz

				if (this.isAssistenz) {
					// keep the selected Studiengang if it still exists this semester, then reload its LVs
					this.setAssistenzStudiengaenge(res.data?.studiengaenge ?? [])
					this.selectedStudiengang = keepStg
						? this.assistenzStudiengaenge.find(s => s.studiengang_kz == keepStg) ?? null
						: null

					const loadLvs = this.selectedStudiengang
						? this.loadLehrveranstaltungenForStudiengang(this.selectedStudiengang.studiengang_kz, sem, keepLvId)
						: Promise.resolve(this.lehrveranstaltungen = null)

					return loadLvs.then(() => this.afterSemesterChange(sem))
				}

				// teacher: assigned LVs for the new semester come from the same context call
				this.setLehrveranstaltungen(res.data?.lehrveranstaltungen ?? [], keepLvId)
				return this.afterSemesterChange(sem)
			}).finally(() => this.loading = false)
		},
		afterSemesterChange(sem) {
			const lvId = this.selectedLehrveranstaltung?.lehrveranstaltung_id ?? null

			this.$router.push({
				name: "Benotungstool",
				params: { sem_kurzbz: sem, lv_id: lvId ?? undefined }
			})

			if (lvId) {
				this.loadNoten(lvId, sem)
			} else if (this.$refs.notenTable?.tabulator) {
				this.$refs.notenTable.tabulator.setData([])
			}
		},
		getOptionLabel(option) {
			return option.studiensemester_kurzbz
		},
		getOptionLabelLv(option) {
			return option.fullString
		},
		getOptionLabelLe(option) {
			return option.infoString
		},
		savePruefungEingabe() {
			// keep the date within the neighbouring exam dates (a typed value can bypass the picker bounds)
			if((this.pruefungDateMin && this.selectedPruefungDate < this.pruefungDateMin) ||
				(this.pruefungDateMax && this.selectedPruefungDate > this.pruefungDateMax)) {
				this.$fhcAlert.alertWarning(this.$capitalize(this.$p.t('benotungstool/pruefungDatumOutOfRangeHint')))
				return
			}

			const year = this.selectedPruefungDate.getFullYear();
			const month = String(this.selectedPruefungDate.getMonth() + 1).padStart(2, '0'); // Months are 0-based
			const day = String(this.selectedPruefungDate.getDate()).padStart(2, '0');
			const dateStr = `${year}-${month}-${day}`;

			// when the grade is locked (later pruefung exists) keep the existing note untouched
			const note = this.pruefungNoteLocked && this.pruefung
				? this.pruefung.note
				: (this.selectedPruefungNote?.note ?? 9) // noch nicht eingetragen
			const punkte = this.pruefungNoteLocked && this.pruefung
				? (this.pruefung.punkte ?? 0)
				: (this.selectedPruefungPunkte ?? 0)

			this.loading = true
			this.$api.call(ApiNoten.saveStudentPruefung(
				this.pruefungStudent.uid,
				note,
				punkte,
				dateStr,
				this.lv_id,
				this.pruefungStudent.lehreinheit_id,
				this.sem_kurzbz,
				this.pruefung?.pruefung_id ?? null, // null = adding a new pruefung, otherwise edit of this record
				this.selectedLehrender
			)).then(res => {
				if(res.meta.status === 'success') { //'Prüfung für Student ' + this.pruefungStudent.uid + ' bearbeitet oder angelegt'
					this.$fhcAlert.alertDefault(
						'success',
						'Info',
						this.$capitalize(this.$p.t('benotungstool/pruefungSaveForUid', [this.pruefungStudent.uid])),
						true
					)
					const s = this.studenten.find(s => s.uid === res.data[1]?.student_uid)

					this.applyLvGesamtnote(s, res.data[1])

					// build the full row again from the server history; this covers a new exam and an edit
					this.colLayoutRestored = false
					this.preserveScroll(() => {
						this.applyVerlauf(s, res.data[2])
						this.applyPruefungColumns()
						this.$refs.notenTable.tabulator.redraw(true)
					})
				}
			}).finally(()=> {
				this.pruefungStudent = null
				this.pruefung = null
				this.loading = false
			})

			this.$refs.modalContainerPruefung.hide()
		},
		recalculateSelectable(student) {
			const vueThis = this
			Object.defineProperty(student, 'selectable', {
				get() {
					return vueThis.canAddPruefung(student)
				},
				set() {
					// empty setter so tabulator doesnt scream
				},
				enumerable: true,
				configurable: true
			})
			// a student can be selectable now, therefore build the "neue Prüfung" options again
			this.tableVersion++
		},
		saveNoteneingabe() {
			this.loading = true
			this.$api.call(ApiNoten.saveStudentenNoten(this.password, this.changedNoten, this.lv_id, this.sem_kurzbz))
				.then((res) => {
				if(res.meta.status === 'success') {
					this.$fhcAlert.alertDefault(
						'success',
						'Info',
						'Noten gespeichert',
						true
					)
				}
				
				res.data.forEach(d => {
					const s = this.studenten.find(s => s.uid === d.uid)
					if(!s) return

					s.freigabedatum = this.parseDate(d.freigabedatum)
					s.benotungsdatum = this.parseDate(d.benotungsdatum)
					s.freigegeben = this.checkFreigabe(s.freigabedatum, s.benotungsdatum, s.uid);

					// die Freigabe legt den ersten Antritt an; ohne den Verlauf bliebe die Zeile bis
					// zum nächsten Laden leer
					if(d.verlauf) this.applyVerlauf(s, d.verlauf)
				})
				this.changedNotenCounter++;

				// die neuen Termine brauchen unter Umständen eine zusätzliche Spalte
				this.preserveScroll(() => {
					this.applyPruefungColumns()
					const loaded = this.$refs.notenTable.tabulator.setData(this.studenten)
					this.$refs.notenTable.tabulator.redraw(true)
					return loaded
				})
			}).finally(() => {
				this.loading = false
			})
			
			this.$refs.modalContainerNotenSpeichern.hide()
		},
		openSaveModal() {
			this.$refs.modalContainerNotenSpeichern.show()
		},
		openNewPruefungsdatumModal() {
			// the same fields as the dialog in the table: both start empty and without a grade
			this.selectedPruefungNote = null
			this.selectedPruefungPunkte = null
			this.selectedPruefungDate = new Date()
			// nur bei einer gemeinsamen Lehreinheit gibt es eine sinnvolle Lektorenauswahl
			this.ladeLehrende((this.selectedUids ?? []).map(s => s.lehreinheit_id))
			this.$refs.modalContainerNeuesPruefungsdatum.show()
		},
		openPruefungImportModal() {
			this.$refs.modalContainerPruefungImport.show()
		},
		openNotenImportModal() {
			this.$refs.modalContainerNotenImport.show()
		},
		getOptionLabelNotePruefung(option) {
			return option.bezeichnung
		},
		addPruefung(){
			const year = this.selectedPruefungDate.getFullYear();
			const month = String(this.selectedPruefungDate.getMonth() + 1).padStart(2, '0'); // Months are 0-based
			const day = String(this.selectedPruefungDate.getDate()).padStart(2, '0');
			const dateStrDb = `${year}-${month}-${day}`;
			const dateStrFront = `${day}.${month}.${year}`;

			const uids = []

			this.selectedUids.forEach(student => {

				// check if student antrittCount is too high already
				if(!this.canAddPruefung(student)) {
					this.$fhcAlert.alertWarning('Student ' + student.uid + ' hat bereits ' + student.hoechsterAntritt + ' Prüfungsantritte abgelegt. Es wird keine Prüfung angelegt.')
					return
				}

				// get student for pruefung and check if proposed datum does not conflict (no new pruefungen before existing ones)
				const youngerPruefung = student.pruefungen.find(pr => {
					return pr.dateObj >= this.selectedPruefungDate
				})
				if(youngerPruefung) {
					this.$fhcAlert.alertWarning('Student ' + student.uid + ' hat bereits eine Prüfung am '+ youngerPruefung.datum +' eingetragen. Es wird keine Prüfung angelegt.')
					return
				}

				uids.push({
					uid: student.uid,
					lehreinheit_id: student.lehreinheit_id
				})
			})

			this.$refs.modalContainerNeuesPruefungsdatum.hide()

			if(!uids.length) return

			// A missing course grade blocks nothing. The server creates it from the grade of this
			// exam. The dialog shows a hint, see pruefungOhneLvNote.
			this.loading = true;
			this.$api.call(ApiNoten.createPruefungen(
				uids,
				dateStrDb,
				this.lv_id,
				this.sem_kurzbz,
				this.selectedPruefungNote?.note ?? null,
				this.selectedPruefungPunkte ?? null,
				this.selectedLehrender
			)).then(res => {
				if(res.meta.status === "success") {

					// iterate over response data
					//  -> alert successful pruefungen
					//  -> alert denied pruefungen + reason

					let uidListSuccess = ''
					let uidListError = ''
					const successData = []
					Object.keys(res.data).forEach(student_uid => {
						const student = res.data[student_uid]
						// actual pruefung has been allocated
						if(student.savedPruefung) {
							uidListSuccess += ' ' + student_uid

							// keep res.data format intact for handleResponse method
							successData[student_uid] = student
						} else { // there should be an error message why no pruefungen where allocated for this person, many reasons possible
							uidListError += student_uid + ' - ' + student +'\n'// student variable is the error message here
						}
					})

					if(uidListError != '') {
						this.$fhcAlert.alertError(
							this.$capitalize(this.$p.t('benotungstool/c4pruefungAnlageError', [dateStrFront])) + ': ' + uidListError + ' '
						)
					}

					if(uidListSuccess != '') {
						this.$fhcAlert.alertDefault(
							'success',
							'Info',
							this.$capitalize(this.$p.t('benotungstool/pruefungAngelegtAn', [dateStrFront])) + ': ' + uidListSuccess,
							true
						)

						this.handleAddNewPruefungenResponse({data: successData}, uids)
					}

				}
			}).finally(()=> this.loading = false)
		},
		getAntrittCountStudent(student) {
			return NotenRules.antrittCountStudent(student, this.config, this.notenOptions)
		}
	},
	watch: {
		selectedUids(newVal, oldVal) {
			// im offenen Sammeldialog ändert das Multiselect die Auswahl; die Lektorenliste hängt
			// an der Lehreinheit und muss dann neu geladen werden
			if(this.neuesPruefungsdatumModalVisible) {
				this.ladeLehrende((newVal ?? []).map(s => s.lehreinheit_id))
			}

			const table = this.$refs.notenTable?.tabulator

			if (!table) return;

			const allRows = table.getRows();
			
			allRows.forEach(row => {
				const rowData = row.getData();
				const found = newVal.find(stud => stud.uid == rowData.uid)
				if (found) {
					row.select(); // ensure row is selected
					const cb = row.getElement().children[0]?.children[0]
					if(cb) cb.checked = true
				} else {
					row.deselect(); // ensure row is deselected
					const cb = row.getElement().children[0]?.children[0]
					if(cb) cb.checked = false
				}
			});
		},
		selectedLehreinheit(newVal) {
			const table = this.$refs.notenTable?.tabulator
			if (!table) return

			const others = table.getFilters().filter(f => f.field !== 'lehreinheit_id')

			table.clearFilter()

			const next = newVal
				? [...others, { field: 'lehreinheit_id', type: '=', value: newVal.lehreinheit_id }]
				: others

			if (next.length) table.setFilter(next)
		},
		selectedLehrveranstaltung(newVal, oldVal) {
			if (this.selectedLehreinheit) {
				this.selectedLehreinheit = null
			}
		},
		kommPruefSpalteNoetig() {
			this.applyKommPruefSpalte()
		},
		selectedPruefungNote(newVal, oldVal) {
			if (!newVal || !this.pruefungStudent) return

			const limitMap = this.config?.NOTEN_OCCURANCE_LIMIT_MAP
			if (!limitMap) return
			console.log(limitMap)
			
			const note = newVal.note
			const limit = limitMap[note]
			if (limit == null) return

			// all exams of the history; the row holds the final exam separately
			const allPruefungen = [...this.pruefungStudent.pruefungen]
			if (this.pruefungStudent.kommPruef) allPruefungen.push(this.pruefungStudent.kommPruef)

			// count existing occurrences of this note, excluding the pruefung being edited
			// (its note is about to be replaced by this very selection)
			const existingCount = allPruefungen.reduce((acc, p) => {
				if (this.pruefung && p.pruefung_id === this.pruefung.pruefung_id) return acc
				if (p.note == note) acc++
				return acc
			}, 0)

			// this selection adds one more occurrence -> would it cross the limit?
			if (existingCount + 1 > limit) {
				// TODO: phrase
				this.$fhcAlert.alertWarning(
					'Note "' + newVal.bezeichnung + '" darf bei ' + this.pruefungStudent.uid +
					' maximal ' + limit + ' mal vergeben werden. Auswahl wurde zurückgesetzt.'
				)
				this.selectedPruefungNote = oldVal // revert to last valid choice
			}
		}
	},
	computed: {
		countsToHTML() {
			return this.$p.t('global/ausgewaehlt')
				+ ': <strong>' + (this.selectedUids.length || 0) + '</strong>'
				+ ' | '
				+ this.$p.t('global/gefiltert')
				+ ': <strong>' + (this.filteredcount || 0) + '</strong>'
				+ ' | '
				+ this.$p.t('global/gesamt')
				+ ': <strong>' + (this.studenten?.length || 0) + '</strong>';
		},
		// a hint for the dialog: for these students the exam creates the course grade
		pruefungOhneLvNote() {
			return (this.selectedUids ?? []).filter(s => NotenRules.brauchtNeueLvNote(s))
		},
		// the same hint in the dialog of the table: both dialogs show the same text
		pruefungStudentOhneLvNote() {
			return !this.pruefung && !!this.pruefungStudent && NotenRules.brauchtNeueLvNote(this.pruefungStudent)
		},
		// the active column layout: the choice of the user, or the default from the configuration
		pruefungsspaltenModus() {
			return this.pruefungsspalten ?? this.config?.CIS_GESAMTNOTE_PRUEFUNGSSPALTEN ?? 'antritt'
		},
		getFreigabeCounter() {
			return this.studenten ? this.studenten.reduce((acc, cur) => {
				if(cur.freigegeben == 'changed') {
					acc++
				}
				return acc
			}, 0) : 0
		},
		LehreinheitenModule() {
			return LehreinheitenModule;
		},
		LeDropdownParams() {
			return {
				lv_id: this.selectedLehrveranstaltung?.lehrveranstaltung_id ?? null,
				sem_kurzbz: this.selectedSemester?.studiensemester_kurzbz ?? null
			}
		},	
		getStudentenOptions() {
			// the multiselect mirrors the table: the same order after the sort and the filter
			const _ = this.tableVersion // reactive dependency, wird bei Sort/Filter/Daten erhöht
			const table = this.$refs.notenTable?.tabulator

			if(!table) {
				return this.studenten ? this.studenten.filter(s => s.selectable) : []
			}

			// getRows("active") returns the rows in their current display order (after sort + filter)
			return table.getRows("active")
				.map(r => r.getData())
				.filter(s => s.selectable)
		},
		getKommPruefCount(){
			let counter = 0
			this.studenten?.forEach(s => {if(s['kommPruef']){counter++}})
			return counter
		},
		/**
		 * Titel der kommissionellen Spalte. Im Datumsmodus tragen alle anderen Spalten ihr Datum im
		 * Kopf, daher bekommt diese es auch - aber nur, wenn alle Termine darin auf denselben Tag
		 * fallen. Bei verschiedenen Daten steht das Datum weiterhin in der Zelle.
		 */
		kommPruefSpaltentitel() {
			const titel = this.$capitalize(this.$p.t('benotungstool/c4kommPruef'))
			if(this.pruefungsspaltenModus !== 'datum') return titel

			const daten = new Set(
				(this.studenten ?? [])
					.map(s => (s.kommPruef?.datum ?? '').slice(0, 10))
					.filter(d => d !== '')
			)
			if(daten.size !== 1) return titel

			return `${titel} - ${this.formatDatumDMY([...daten][0])}`
		},
		/**
		 * Die kommissionelle Spalte wird gebraucht, sobald ein Termin darin steht ODER bei einer
		 * Zeile der letzte Antritt fällig ist - sonst fehlt der Platz zum Anlegen.
		 */
		kommPruefSpalteNoetig() {
			if(!this.config?.CIS_GESAMTNOTE_PRUEFUNG_KOMMPRUEF) return false
			if(this.getKommPruefCount > 0) return true

			return (this.studenten ?? []).some(s => s.verlauf?.naechsteRolle === 'kommissionell')
		},
		getSaveBtnClass() {
			return this.changedNoten?.length ? "btn btn-primary ml-2" : "btn btn-secondary ml-2"
		},
		getNewBtnClass() {
			return "btn btn-primary ml-2"
		},
		getNotenImportBtnClass() {
			return "btn btn-primary ml-2"
		},
		changedNoten() {
			const v = this.changedNotenCounter // hack to trigger computed
			const cs = this.studenten ? this.studenten.reduce((acc, cur) => {
				const teilnote = this.teilnoten[cur.uid]
				if(teilnote.note_lv && (cur.benotungsdatum > cur.freigabedatum)) {
					
					// write noteBezeichnung into changed Note so we can send emails in backend easier...
					const opt = this.notenOptions.find(opt => opt.note == cur.lv_note) 
					cur.noteBezeichnung = opt.bezeichnung
					
					acc.push(cur)
				}
				return acc
			}, []) : []
			return cs
		},
		// The preview of the release dialog, over changedNoten. The release writes the course grade
		// only, not the transcript grade. Therefore the dialog compares the two.
		freigabeSummary() {
			return this.changedNoten.map(s => {
				const zeugnisOpt = this.notenOptions?.find(opt => opt.note == s.note)
				const releaseOpt = this.notenOptions?.find(opt => opt.note == s.lv_note)
				return {
					uid: s.uid,
					name: `${s.vorname} ${s.nachname}`,
					currentNote: zeugnisOpt ? zeugnisOpt.bezeichnung : (s.note || '—'),
					releasedNote: releaseOpt ? releaseOpt.bezeichnung : (s.lv_note || '—'),
					changed: (s.note ?? '') != (s.lv_note ?? '')
				}
			})
		},
		getNotenfreigabeHinweistext() {
			return this.$capitalize(this.$p.t('benotungstool/notenfreigabeHinweistextv4'))
		},
		getPruefungimportHinweistext() {
			return this.$capitalize(this.$p.t('benotungstool/notenimportHinweistextv6'))
		},
		getNotenimportHinweistext() {
			return this.$capitalize(this.$p.t('benotungstool/notenimportHinweistextv5'))
		},
		freezableColumnOptions() {
			// the identity columns the user may pin to the left (matches freezableColumnFields)
			return [
				{ field: 'selectCol', label: this.$capitalize(this.$p.t('benotungstool/c4selection')) },
				{ field: 'uid',       label: 'UID' },
				{ field: 'vorname',   label: this.$capitalize(this.$p.t('benotungstool/c4vorname')) },
				{ field: 'nachname',  label: this.$capitalize(this.$p.t('benotungstool/c4nachname')) }
			]
		}
	},
	created() {
		this.setupCreated()
	},
	mounted() {
		this.setupMounted()
	},
	template: `
		<bs-modal data-cy="modal-pruefung-import" ref="modalContainerPruefungImport" class="bootstrap-prompt" dialogClass="modal-lg" bodyClass="px-4 py-4">
			<template v-slot:title>{{$capitalize($p.t('benotungstool/c4pruefungImportieren'))}}</template>
			<template v-slot:default>
				<div class="row justify-content-center">
					<div class="col-12" v-html="getPruefungimportHinweistext"></div>
				</div>
				<div class="row mt-3 justify-content-center">
					<div class="col-12">
						<Textarea data-cy="pruefung-import-text" v-model="importString" rows="5" class="w-100" :placeholder="$p.t('benotungstool/c4importPlaceholder')"></Textarea>
					</div>
				</div>
				<div class="row mt-3 justify-content-center">
					<div class="col-12">
						<NotenlisteLinks
							:lehrveranstaltung="selectedLehrveranstaltung"
							:sem_kurzbz="selectedSemester?.studiensemester_kurzbz"
							:selected-lehreinheit="selectedLehreinheit" />
					</div>
				</div>
			</template>
			<template v-slot:footer>
				<button type="button" data-cy="pruefung-import-submit" class="btn btn-primary" @click="importPruefungen">{{ $capitalize($p.t('benotungstool/c4import')) }}</button>
			</template>
		</bs-modal>

		<bs-modal data-cy="modal-noten-import" ref="modalContainerNotenImport" class="bootstrap-prompt" dialogClass="modal-lg" bodyClass="px-4 py-4">
			<template v-slot:title>{{$capitalize($p.t('benotungstool/c4notenImportieren'))}}</template>
			<template v-slot:default>
				<div class="row justify-content-center">
					<div class="col-12" v-html="getNotenimportHinweistext"></div>
				</div>
				<div class="row mt-3 justify-content-center">
					<div class="col-12">
						<Textarea data-cy="noten-import-text" v-model="importStringNoten" rows="5" class="w-100" :placeholder="$p.t('benotungstool/c4importNotePlaceholder')"></Textarea>
					</div>
				</div>
				<div class="row mt-3 justify-content-center">
					<div class="col-12">
						<NotenlisteLinks
							:lehrveranstaltung="selectedLehrveranstaltung"
							:sem_kurzbz="selectedSemester?.studiensemester_kurzbz"
							:selected-lehreinheit="selectedLehreinheit" />
					</div>
				</div>
			</template>
			<template v-slot:footer>
				<button type="button" data-cy="noten-import-submit" class="btn btn-primary" @click="importNoten">{{ $capitalize($p.t('benotungstool/c4import')) }}</button>
			</template>
		</bs-modal>

		<bs-modal data-cy="modal-neue-pruefung" ref="modalContainerNeuesPruefungsdatum" class="bootstrap-prompt" dialogClass="modal-lg" bodyClass="px-4 py-4"
			@hideBsModal="neuesPruefungsdatumModalVisible = false"
			@showBsModal="neuesPruefungsdatumModalVisible = true">
			<template v-slot:title>{{$capitalize($p.t('benotungstool/c4addNewPruefung'))}}</template>
			<template v-slot:default>
				<div class="row align-items-center justify-content-center">
					<div class="col-3 text-center">{{$capitalize($p.t('benotungstool/c4date'))}}:</div>
					<div class="col-6" data-cy="neue-pruefung-datum">
						<datepicker
							v-model="selectedPruefungDate"
							:clearable="false"
							format="dd.MM.yyyy"
							placeholder="TT.MM.JJJJ"
							:enableTimePicker="false"
							:text-input="true"
							:auto-apply="true">
						</datepicker>
					</div>
				</div>

				<div v-if="config?.CIS_GESAMTNOTE_PUNKTE == true" class="row mt-3 align-items-center justify-content-center">
					<div class="col-3 text-center">{{$capitalize($p.t('benotungstool/c4punkte'))}}:</div>
					<div class="col-6">
						<InputNumber
							data-cy="neue-pruefung-punkte"
							v-model="selectedPruefungPunkte"
							inputId="neuePruefungPunkteInput" :min="0" :max="100000"
							class="w-100">
						</InputNumber>
					</div>
				</div>

				<div class="row mt-3 align-items-center justify-content-center">
					<div class="col-3 text-center">{{$capitalize($p.t('lehre/note'))}}:</div>
					<div class="col-6">
						<Dropdown data-cy="neue-pruefung-note" :placeholder="$capitalize($p.t('lehre/note'))"
							:disabled="config?.CIS_GESAMTNOTE_PUNKTE == true"
							:style="{'width': '100%'}" :optionLabel="getOptionLabelNotePruefung"
							v-model="selectedPruefungNote" :options="notenOptionsLehre" showClear>
						</Dropdown>
					</div>
				</div>

				<div class="row mt-3 align-items-center justify-content-center">
					<div class="col-3 text-center">{{$capitalize($p.t('benotungstool/prueflingSelectionv2'))}}:</div>
					<div class="col-6">
						<Multiselect
							data-cy="neue-pruefung-studenten"
							v-model="selectedUids"
							:options="getStudentenOptions"
							optionLabel="infoString"
							placeholder="Studenten auswählen"
							:maxSelectedLabels="3"
							showToggleAll
							class="w-100" />
					</div>
				</div>

				<div v-if="lehrendeDerLehreinheit.length > 1" class="row mt-3 align-items-center justify-content-center">
					<div class="col-3 text-center">{{$capitalize($p.t('benotungstool/c4lektor'))}}:</div>
					<div class="col-6">
						<Dropdown data-cy="neue-pruefung-lektor" :style="{'width': '100%'}"
							v-model="selectedLehrender" :options="lehrendeDerLehreinheit"
							optionLabel="anzeigename" optionValue="mitarbeiter_uid">
						</Dropdown>
					</div>
				</div>

				<div v-if="pruefungOhneLvNote.length" class="row mt-3 justify-content-center">
					<div class="col-12">
						<div class="alert alert-info mb-0 py-2" data-cy="neue-pruefung-ohne-lvnote">
							<div>{{ $p.t('benotungstool/c4lvNoteWirdAngelegtHinweis') }}</div>
							<div class="small mt-1">
								<span v-for="(s, i) in pruefungOhneLvNote" :key="s.uid">
									<span v-if="i">, </span>{{ s.vorname }} {{ s.nachname }} ({{ s.uid }})
								</span>
							</div>
						</div>
					</div>
				</div>
			</template>
			<template v-slot:footer>
				<button type="button" data-cy="neue-pruefung-submit" class="btn btn-primary" @click="addPruefung">{{ $capitalize($p.t('benotungstool/c4addNewPruefung')) }}</button>
			</template>
		</bs-modal>

		<bs-modal data-cy="modal-freigabe" ref="modalContainerNotenSpeichern" class="bootstrap-prompt" dialogClass="modal-lg" bodyClass="px-4 py-4">
			<template v-slot:title>{{ $p.t('benotungstool/noteneingabeSpeichern') }}</template>
			<template v-slot:default>
				<div class="row justify-content-center">
					<div class="col-12" v-html="getNotenfreigabeHinweistext"></div>
				</div>
				<div v-if="freigabeSummary.length" class="row mt-3 justify-content-center">
					<div class="col-12">
						<div class="fw-bold mb-1">{{ $p.t('benotungstool/c4freigabeSummaryHeading', [freigabeSummary.length]) }}</div>
						<div class="border rounded" style="max-height: 250px; overflow-y: auto;">
							<table class="table table-sm table-hover align-middle mb-0">
								<thead>
									<tr>
										<th class="bg-body sticky-top">{{ $capitalize($p.t('benotungstool/c4student')) }}</th>
										<th class="bg-body sticky-top">{{ $capitalize($p.t('benotungstool/c4freigabeSummaryCurrent')) }}</th>
										<th class="bg-body sticky-top"></th>
										<th class="bg-body sticky-top">{{ $capitalize($p.t('benotungstool/c4freigabeSummaryReleased')) }}</th>
									</tr>
								</thead>
								<tbody>
									<tr v-for="row in freigabeSummary" :key="row.uid" :data-cy="'freigabe-row-' + row.uid">
										<td>{{ row.name }} <span class="text-muted">({{ row.uid }})</span></td>
										<td>{{ row.currentNote }}</td>
										<td class="text-center"><i class="fa fa-arrow-right"></i></td>
										<td data-cy="freigabe-row-released" :class="{ 'fw-bold text-success': row.changed }">{{ row.releasedNote }}</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="small text-muted mt-1">{{ $p.t('benotungstool/c4freigabeSummaryLegend') }}</div>
					</div>
				</div>
				<div v-else class="row mt-3 justify-content-center">
					<div class="col-12 text-center text-muted" data-cy="freigabe-summary-empty">{{ $p.t('benotungstool/c4freigabeSummaryEmpty') }}</div>
				</div>
				<div class="row mt-3 justify-content-center">
					<div class="col-auto" data-cy="freigabe-passwort">
						<Password v-model="password" :feedback="false" showIcon="fa fa-eye" :toggleMask="true" :promptLabel="$p.t('benotungstool/passwort')"></Password>
					</div>
				</div>
			</template>
			<template v-slot:footer>
				<button type="button" data-cy="freigabe-submit" class="btn btn-primary" @click="saveNoteneingabe">{{ $p.t('benotungstool/noteneingabeBestätigen') }}</button>
			</template>
		</bs-modal>

		<bs-modal data-cy="modal-pruefung" ref="modalContainerPruefung" class="bootstrap-prompt" dialogClass="modal-lg" bodyClass="px-4 py-4">
			<template v-slot:title>{{ pruefung ? $capitalize($p.t('benotungstool/editPruefungFor')) : $capitalize($p.t('benotungstool/createPruefungFor')) }} {{pruefungStudent?.vorname}} {{pruefungStudent?.nachname}}</template>
			<template v-slot:default>
				<div class="row align-items-center justify-content-center">
					<div class="col-3 text-center">{{$capitalize($p.t('benotungstool/c4date'))}}:</div>
					<div class="col-6" data-cy="pruefung-datum">
						<datepicker
							v-model="selectedPruefungDate"
							:clearable="false"
							:enableTimePicker="false"
							format="dd.MM.yyyy"
							placeholder="TT.MM.JJJJ"
							:min-date="pruefungDateMin"
							:max-date="pruefungDateMax"
							:text-input="true"
							:auto-apply="true">
						</datepicker>
					</div>
				</div>
				<div v-if="pruefungNoteLocked" class="row mt-2 justify-content-center">
					<div class="col-9 text-center text-muted small" data-cy="pruefung-note-locked">{{$capitalize($p.t('benotungstool/pruefungNoteLockedHint'))}}</div>
				</div>
				<div v-if="config?.CIS_GESAMTNOTE_PUNKTE == true" class="row mt-3 align-items-center justify-content-center">
					<div class="col-3 text-center">{{$capitalize($p.t('benotungstool/c4punkte'))}}:</div>
					<div class="col-6">
						<InputNumber
							data-cy="pruefung-punkte"
							v-model="selectedPruefungPunkte"
							@input="debouncedFetchPunkteForPruefung"
							:disabled="pruefungNoteLocked"
							inputId="selectedPruefungInput" :min="0" :max="100000"
							class="w-100">
						</InputNumber>
					</div>
				</div>
				<div class="row mt-3 align-items-center justify-content-center">
					<div class="col-3 text-center">{{$capitalize($p.t('lehre/note'))}}:</div>
					<div class="col-6">
						<Dropdown data-cy="pruefung-note" :placeholder="$capitalize($p.t('lehre/note'))"
							:disabled="config?.CIS_GESAMTNOTE_PUNKTE == true || pruefungNoteLocked"
							:style="{'width': '100%'}" :optionLabel="getOptionLabelNotePruefung"
							v-model="selectedPruefungNote" :options="notenOptionsLehre" showClear>
							<template #optionsgroup="slotProps">
								<div> {{ option.bezeichnung }} </div>
							</template>
						</Dropdown>
					</div>
				</div>

				<div v-if="lehrendeDerLehreinheit.length > 1" class="row mt-3 align-items-center justify-content-center">
					<div class="col-3 text-center">{{$capitalize($p.t('benotungstool/c4lektor'))}}:</div>
					<div class="col-6">
						<Dropdown data-cy="pruefung-lektor" :style="{'width': '100%'}"
							v-model="selectedLehrender" :options="lehrendeDerLehreinheit"
							optionLabel="anzeigename" optionValue="mitarbeiter_uid">
						</Dropdown>
					</div>
				</div>

				<div v-if="pruefungStudentOhneLvNote" class="row mt-3 justify-content-center">
					<div class="col-12">
						<div class="alert alert-info mb-0 py-2" data-cy="pruefung-ohne-lvnote">
							{{ $p.t('benotungstool/c4lvNoteWirdAngelegtHinweis') }}
						</div>
					</div>
				</div>
			</template>
			<template v-slot:footer>
				<button type="button" data-cy="pruefung-submit" class="btn btn-primary" @click="savePruefungEingabe">{{ $capitalize($p.t('global/speichern')) }}</button>
			</template>
		</bs-modal>

		<BsOffcanvas
			ref="drawer"
			placement="end"
			:backdrop="true"
			:style="{ '--bs-offcanvas-width': '600px' }"
		>
			<template #title>
			
			</template>
		
			<MobilityLegende/>		
		
			<template #footer>
			
			</template>
		</BsOffcanvas>

		<FhcOverlay :active="loading"></FhcOverlay>

		<div class="row align-items-center gy-2 mb-2">
			<div class="col-12 col-xxl-auto">
				<h2 class="mb-0">{{$capitalize($p.t('benotungstool/benotungstoolTitle'))}}</h2>
				<h5 class="mb-0 text-truncate" style="max-width: 22rem;">{{ selectedLehrveranstaltung?.lv_bezeichnung }}</h5>
			</div>

			<div class="col-12 col-xxl">
				<div class="d-flex flex-wrap align-items-center justify-content-xxl-end" style="gap: 0.5rem;">

					<div class="d-flex align-items-center" style="flex: 1 1 12rem; min-width: 9rem; gap: 0.35rem;" v-if="isAssistenz">
						<label class="col-form-label py-0 text-nowrap flex-shrink-0 d-none d-xxl-inline">{{$capitalize($p.t('lehre/studiengang'))}}:</label>
						<Dropdown @change="stgChanged" class="flex-grow-1" :style="{'minWidth': '0'}" :optionLabel="getOptionLabelStg"
							:placeholder="$capitalize($p.t('lehre/studiengang'))"
							v-model="selectedStudiengang" :options="assistenzStudiengaenge" appendTo="self">
							<template #optionsgroup="slotProps">
								<div> {{ option.fullString }} </div>
							</template>
						</Dropdown>
					</div>

					<div class="d-flex align-items-center" style="flex: 1 1 12rem; min-width: 9rem; gap: 0.35rem;">
						<label class="col-form-label py-0 text-nowrap flex-shrink-0 d-none d-xxl-inline">{{$capitalize($p.t('lehre/lehrveranstaltung'))}}:</label>
						<Dropdown @change="lvChanged" class="flex-grow-1" :style="{'minWidth': '0'}" :optionLabel="getOptionLabelLv"
							:placeholder="$capitalize($p.t('lehre/lehrveranstaltung'))"
							v-model="selectedLehrveranstaltung" :options="lehrveranstaltungen" appendTo="self">
							<template #optionsgroup="slotProps">
								<div> {{ option.fullString }} </div>
							</template>
						</Dropdown>
					</div>

					<div class="d-flex align-items-center" style="flex: 1 1 12rem; min-width: 9rem; gap: 0.35rem;">
						<label class="col-form-label py-0 text-nowrap flex-shrink-0 d-none d-xxl-inline">{{$capitalize($p.t('lehre/lehreinheit'))}}:</label>
						<Dropdown class="flex-grow-1" :style="{'minWidth': '0'}" v-bind="LehreinheitenModule"
							v-model="selectedLehreinheit" showClear appendTo="self">
							<template #option="slotProps">
								<div>
									{{ slotProps.option.infoString }}
									<i class="fa-solid fa-user"></i>
									{{ slotProps.option.studentcount }}
									<i class="fa-solid fa-calendar-days"></i>
									{{ slotProps.option.termincount }}
								</div>
							</template>
						</Dropdown>
					</div>

					<div class="d-flex align-items-center" style="flex: 1 1 8rem; min-width: 7rem; gap: 0.35rem;">
						<label class="col-form-label py-0 text-nowrap flex-shrink-0 d-none d-xxl-inline">{{$capitalize($p.t('lehre/studiensemester'))}}:</label>
						<Dropdown @change="ssChanged" class="flex-grow-1" :style="{'minWidth': '0'}" :optionLabel="getOptionLabel"
							v-model="selectedSemester" :options="studiensemester" appendTo="self">
							<template #optionsgroup="slotProps">
								<div> {{ option.studiensemester_kurzbz }} </div>
							</template>
						</Dropdown>
					</div>

				</div>
			</div>
		</div>
		
		<div id="notentable" data-cy="benotungstool-table" class="row" :style="'overflow-x: auto;'">
			<core-filter-cmpt
				v-if="tabulatorCanBeBuilt"
				@uuidDefined="handleUuidDefined"
				:description="countsToHTML"
    			:useSelectionSpan="false"
				:title="''"
				ref="notenTable"
				:tabulator-options="notenTableOptions"
				:tabulator-events="notenTableEventHandlers"
				@tableBuilt="handleTableBuilt"
				tableOnly
				:sideMenu="false"
			>
				 <template #actions>

					<Multiselect
						v-model="stickyColumnSelection"
						:options="freezableColumnOptions"
						optionLabel="label" optionValue="field"
						:placeholder="$capitalize($p.t('benotungstool/freezeColumnsToggle'))"
						:maxSelectedLabels="0"
						:selectedItemsLabel="$capitalize($p.t('benotungstool/freezeColumnsLabel'))"
						showToggleAll
						@change="onStickySelectionChange"
						class="ml-2"
						style="min-width: 12rem" />

					<div class="btn-group ml-2" role="group">
						<button type="button"
							:class="pruefungsspaltenModus === 'antritt' ? 'btn btn-primary' : 'btn btn-outline-primary'"
							:title="$capitalize($p.t('benotungstool/c4spaltenAntrittHint'))"
							@click="setPruefungsspalten('antritt')">
							{{$capitalize($p.t('benotungstool/c4spaltenAntritt'))}}
						</button>
						<button type="button"
							:class="pruefungsspaltenModus === 'datum' ? 'btn btn-primary' : 'btn btn-outline-primary'"
							:title="$capitalize($p.t('benotungstool/c4spaltenDatumHint'))"
							@click="setPruefungsspalten('datum')">
							{{$capitalize($p.t('benotungstool/c4spaltenDatum'))}}
						</button>
					</div>

					<button data-cy="btn-neue-pruefung" @click="openNewPruefungsdatumModal" role="button" :class="getNewBtnClass">
						{{$capitalize($p.t('benotungstool/c4addNewPruefung'))}} <i class="fa fa-plus"></i>
					</button>
					
					<button data-cy="btn-pruefung-import" v-if="config?.CIS_GESAMTNOTE_PRUEFUNGSIMPORT" @click="openPruefungImportModal" role="button" :class="getNotenImportBtnClass">
						{{$capitalize($p.t('benotungstool/c4pruefungImportieren'))}} <i class="fa fa-file-import"></i>
					</button>
					<button data-cy="btn-noten-import" v-if="config?.CIS_GESAMTNOTE_NOTENIMPORT" @click="openNotenImportModal" role="button" :class="getNotenImportBtnClass">
						{{$capitalize($p.t('benotungstool/c4notenImportieren'))}} <i class="fa fa-file-import"></i>
					</button>
					<button data-cy="btn-freigabe" @click="openSaveModal" role="button" :class="getSaveBtnClass">
						{{$capitalize($p.t('benotungstool/approveGradesv2', [getFreigabeCounter]))}} <i class="fa fa-save"></i>
					</button>
					
				 </template>
			</core-filter-cmpt>
		</div>
    `,
};

export default Benotungstool;
