import {CoreFilterCmpt} from "../../filter/Filter.js";
import ApiLehre from "../../../api/factory/lehre.js";
import ApiNoten from "../../../api/factory/noten.js";
import ApiStudiensemester from "../../../api/factory/studiensemester.js";
import BsModal from '../../Bootstrap/Modal.js';
import BsOffcanvas from '../../Bootstrap/Offcanvas.js';
import VueDatePicker from '../../vueDatepicker.js.php';
import LehreinheitenModule from '../../DropdownModes/LehreinheitenModule';
import MobilityLegende from '../../Mobility/Legende.js';
import FhcOverlay from "../../Overlay/FhcOverlay.js";
import {debounce} from "../../../helpers/debounce.js";

export const Benotungstool = {
	name: "Benotungstool",
	components: {
		BsModal,
		BsOffcanvas,
		CoreFilterCmpt,
		MobilityLegende,
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
			required: true
		},
		sem_kurzbz: {
			default: null,
			required: true
		},
		viewData: {
			type: Object,
			required: true,
			default: () => ({uid: ''}),
			validator(value) {
				return value && value.uid
			}
		}
	},
	data() {
		return {
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
			distinctPruefungsDates: null,
			pruefungStudent: null,
			pruefung: null,
			password: '',
			changedNotenCounter: 0,
			tabulatorUuid: Vue.ref(0),
			domain: '',
			importString: '',
			teilnoten: null,
			lv: null,
			studenten: null,
			pruefungen: null,
			studiensemester: null,
			selectedSemester: null,
			lehrveranstaltungen: null,
			selectedLehrveranstaltung: null,
			tableBuiltResolve: null,
			notenOptions: null,
			notenOptionsLehre: null,
			notenOptionsPromise: null,
			tableBuiltPromise: null,
			notenTableOptions: null, // built later when noten are available
			notenTableEventHandlers: [{
				event: "tableBuilt",
				handler: async () => {
					this.tableBuiltResolve()
				}
			},
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
					} else if (field === 'punkte') {
						const newValue = cell.getValue();
						if(newValue == '' || newValue == null) return
						this.$api.call(ApiNoten.getNoteByPunkte(newValue, this.lv_id, this.sem_kurzbz)).then(res => {
							if(res?.meta?.status === 'success' && res.data >= 0) {
								const row = cell.getRow();
								row.update({note_vorschlag: res.data})
							}
						})
					}
				}
			}, 
			{
				event: "cellClick",
				handler: async (e, cell) => {
					const field = cell.getField()
					
					if(field == "mobility_zusatz") {
						this.$refs.drawer.show()
						e.stopPropagation()
					}
				}
			}
			]};
	},
	methods: {
		fetchNoteForPunktePruefung(event) {
			this.$api.call(ApiNoten.getNoteByPunkte(event.value, this.lv_id, this.sem_kurzbz)).then(res => {
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
			// need to check pruefungen for validity in respect to the students nr of antritte
			// pruefungsdatum will be validated aswell so we dont get a termin 3 chronologically before
			// a termin 2 which is totally possible in the old tool
			const validatedPruefungen = []
			pruefungen.forEach( p => {
				const student = this.studenten.find(s => s.uid === p.uid)
				// check if student antrittCount is too high already
				if(student.hoechsterAntritt >= 3) {
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
			let student = null
			if(id === 'matrikelnr') { // find student by matrnr and use uid later on
				student = this.studenten.find(s => s.matrikelnr === rowParts[0])
			} else if(id === 'uid') {
				student = this.studenten.find(s => s.uid === uid)
			}
			if(!student) {
				this.$fhcAlert.alertWarning('Kein Student gefunden für ID ' + rowParts[0] + ' in Zeile Nr. ' + rowNum + ' Die Zeile wurde übersprungen.')
				return
			}

			const note = rowParts[1]

			// find notenoption and check if its allowed to use in lehre
			const notenOption = this.notenOptions.find(n => n.note == note)
			if(!notenOption.lehre) {
				this.$fhcAlert.alertWarning('Keine gültige Note gefunden für ID ' + rowParts[0] + ' in Zeile Nr. ' + rowNum + ' Die Zeile wurde übersprungen.')
				return
			}

			notenbulk.push({uid: student.uid, note})
		},
		parsePruefung(rowParts, pruefungbulk, rowNum) {
			const id = this.identifyUid(rowParts[0])
			let student = null
			if(id === 'matrikelnr') { // find student by matrnr and use uid later on
				student = this.studenten.find(s => s.matrikelnr === rowParts[0])
			} else if(id === 'uid') {
				student = this.studenten.find(s => s.uid === rowParts[0])
			}
			if(!student) {
				this.$fhcAlert.alertWarning('Kein Student gefunden für ID ' + rowParts[0] + ' in Zeile Nr. ' + rowNum + ' Die Zeile wurde übersprungen.')
				return
			}

			const datum = rowParts[1] // should be in 'dd.MM.yyyy'
			if(!this.isValidDate_ddmmyyyy(datum)) {
				this.$fhcAlert.alertWarning('Ungültiges Datumformat für ID ' + rowParts[0] + ' in Zeile Nr. ' + rowNum + '. Bitte verwenden Sie das Format "DD.MM.YYYY". Die Zeile wurde übersprungen.')
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
			
			const note = rowParts[2]

			// find notenoption and check if its allowed to use in lehre
			const notenOption = this.notenOptions.find(n => n.note == note)
			if(!notenOption.lehre) {
				
				
				this.$fhcAlert.alertWarning('Keine gültige Note gefunden für ID ' + rowParts[0] + ' in Zeile Nr. ' + rowNum + ' Die Zeile wurde übersprungen.')
				return
			}
			
			const typ = this.getPruefungstypForStudentByAntritt(student)
			
			pruefungbulk.push({uid: student.uid, datum: dateStr, note, typ, lehreinheit_id: student.lehreinheit_id, dateObj})
		},
		saveNotenBulk(notenbulk) {
			this.loading = true
			this.$api.call(ApiNoten.saveNotenvorschlagBulk(this.lv_id, this.sem_kurzbz, notenbulk)).then(res => {
				if(res.meta.status === 'success') {
					this.$fhcAlert.alertDefault(
						'success',
						'Info',
						this.$capitalize(this.$p.t('benotungstool/notenImportSuccessAlert')),
						true
					)
					const lvNoten = res.data
					

					lvNoten.forEach(lvn => {
						// 1.) get relevant student row by uid
						const s = this.studenten.find(s => s.uid === lvn.student_uid)
						s.note_vorschlag = lvn.note // TODO: check if note_vorschlag should be changed by import

						s.lv_note = lvn.note
						
						this.teilnoten[s.uid].note_lv = lvn.note
						// recalculate freigabestatus
						s.freigabedatum = this.parseDate(lvn['freigabedatum'])
						s.benotungsdatum = this.parseDate(lvn['benotungsdatum'])

						s.freigegeben = this.checkFreigabe(s.freigabedatum, s.benotungsdatum, s.uid);
					})

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
			const pruefungen = res.data
			uids.forEach(entry => {
				const saved = pruefungen[entry.uid].savedPruefung?.[0]
				const extra = pruefungen[entry.uid].extraPruefung?.[0]

				const student = this.studenten.find(s => s.uid == entry.uid)
				if(!student) return

				// check for extra pruefung (termin1) to add before
				if(extra) {
					extra.datum = extra.datum.split(' ')[0]
					if(!this.distinctPruefungsDates.includes(extra.datum)) {
						this.insertSortedDate(this.distinctPruefungsDates, extra.datum)
					}

					student.pruefungen.push(extra)
					student[extra.datum] = extra
				}

				if(!this.distinctPruefungsDates.includes(saved.datum)) {
					this.insertSortedDate(this.distinctPruefungsDates, saved.datum)
				}

				// add pruefung to pruefungen array
				student.pruefungen.push(saved)

				// add pruefung to student via its datum as a field
				student[saved.datum] = saved

				// usually should be in order naturally, just to be save
				student.pruefungen.sort((p1, p2) => {
					if(p1.datum > p2.datum) {
						return 1
					} else if (p1.datum < p2.datum) {
						return -1
					} else {
						return 0
					}
				})

				// recalculate student antritte
				student.hoechsterAntritt = this.getAntrittCountStudent(student)
			})

			// add col to table
			const cols = [...this.notenTableOptions.columns.slice(0, -1)];
			let kommCol = null
			if(this.config?.CIS_GESAMTNOTE_PRUEFUNG_KOMMPRUEF) kommCol = this.notenTableOptions.columns[this.notenTableOptions.columns.length - 1];

			// TODO: could reuse cols instead of recreating all from a variable maybe
			this.distinctPruefungsDates.forEach((date, index)=>{
				const dateparts = date.split('-')
				const titledate = `${dateparts[2]}.${dateparts[1]}.${dateparts[0]}`
				

				// TODO: test if this holds true
				const originalNote = index === 0
				cols.push({
					title: titledate,//this.$p.t('benotungstool/pruefungNr', [index+1]),
					field: date,
					formatter: this.pruefungFormatter,
					titleFormatter: this.pruefungTitleFormatter,
					hozAlign:"center",
					widthGrow: 1,
					minWidth: 150,
					originalNote,
					visible: true
				})
			})

			if(this.config?.CIS_GESAMTNOTE_PRUEFUNG_KOMMPRUEF) cols.push(kommCol) // keep kommPruef Col as last
			// redraw table

			this.loading = false

			this.$refs.notenTable.tabulator.clearSort()
			this.$refs.notenTable.tabulator.setColumns(cols)
			this.$refs.notenTable.tabulator.setData(this.studenten);
			this.$refs.notenTable.tabulator.redraw(true);
		},
		importNoten() {
			const rows = this.importString.split('\n')
			const bulk = []
			let mode = ''
			// read the lines
			rows.forEach((r,i) => {
				const rowParts = r.split('\t')
				if(rowParts.length === 3) {
					this.parsePruefung(rowParts, bulk, i)
					mode = 'pruefung' // if line parts are not uniform we are in trouble
				} else if(rowParts.length === 2) {
					this.parseNote(rowParts, bulk, i)
					mode = 'note'
				}
			})
			
			// TODO: punkte parsen!
			
			// parsers check for notenOption.lehre === true and if student uid/matrikelnr matches
			
			// pruefungen check for younger pruefungen, so there are no further antritte with 
			// previous dates from automatic imports 
			if(mode === 'note') {
				this.validateNotenBulk(bulk)
				this.saveNotenBulk(bulk)
			}
			else if (mode === 'pruefung') {
				this.validatePruefungBulk(bulk)
				this.savePruefungBulk(bulk)
			}
			
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
				virtualDom: false,
				index: 'uid',
				layout: 'fitDataStretch',
				placeholder: this.$capitalize(this.$p.t('global/noDataAvailable')),
				selectable: true,
				selectableRangeMode: "click", // shift+click
				selectablePersistence: false, // reset selection on table reload
				selectableCheck: function(row, e){
					const data = row.getData();
					
					if(data['kommPruef']) return false
					else if(data.hoechsterAntritt >= 3) return false // 3 pruefungen counted
					
					return true;  // student can be selected to add pruefung
				},
				rowHeight: 40,
				rowFormatter: this.fixTabulatorSelectionFormatter,
				columns: [
				{
					formatter: function (cell, formatterParams, onRendered) {
						// create the built-in checkbox
						let checkbox = document.createElement("input");
						checkbox.type = "checkbox";

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
					cssClass: 'sticky-col'
				}, 
				{title: 'UID', field: 'uid', tooltip: false, widthGrow: 1, topCalc: this.sumCalcFunc, cssClass: 'sticky-col'},
				{title: Vue.computed(() => this.$capitalize(this.$p.t('benotungstool/c4mail'))), field: 'email', formatter: this.mailFormatter, tooltip: false,  visible: false, widthGrow: 1, variableHeight: true},
				{title: Vue.computed(() => this.$capitalize(this.$p.t('benotungstool/c4antrittCountv2'))), field: 'hoechsterAntritt', tooltip: false, widthGrow: 1},
				{title: Vue.computed(() => this.$capitalize(this.$p.t('benotungstool/c4vorname'))), field: 'vorname', headerFilter: true, tooltip: false, widthGrow: 1},
				{title: Vue.computed(() => this.$capitalize(this.$p.t('benotungstool/c4nachname'))), field: 'nachname', headerFilter: true, widthGrow: 1},
				{title: Vue.computed(() => this.$capitalize(this.$p.t('benotungstool/c4anwesenheitsquote'))), field: 'anwquote', widthGrow: 1, formatter: this.percentFormatter},
				{title: Vue.computed(() => this.$capitalize(this.$p.t('benotungstool/c4mobility'))), field: 'mobility_zusatz', headerFilter: true, widthGrow: 1, visible: false},
				{title: Vue.computed(() => this.$capitalize(this.$p.t('benotungstool/c4teilnoten'))), field: 'teilnote', widthGrow: 1, formatter: this.teilnotenFormatter, variableHeight: true},
				{title: Vue.computed(() => this.$capitalize(this.$p.t('benotungstool/c4punkte'))), field: 'punkte', widthGrow: 1, 
					editor: 'number',
					editorParams: (cell) => {
						return {
							min: 0,
							max: 9999,
							step: 1,
							elementAttributes: {
								maxlength: "4"
							},
							selectContents: true,
							verticalNavigation: "table"
						}
					},
					variableHeight: true
				},
				{title: Vue.computed(() => this.$capitalize(this.$p.t('benotungstool/c4notenvorschlag'))), field: 'note_vorschlag',
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
						// TODO: css style this a bit
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
					},
					widthGrow: 1
				},
				{title: '', width: 50, hozAlign: 'center', formatter: this.arrowFormatter, cellClick: this.saveNote, variableHeight: true},
				{title: Vue.computed(() => this.$capitalize(this.$p.t('benotungstool/c4lvnote'))), field: 'lv_note',
					formatter: this.notenFormatter,
					headerFilter: 'list',
					headerFilterParams: () => {
						return { values: ["\u00A0",this.$p.t('benotungstool/c4noteEmpty') ,this.$p.t('benotungstool/c4positiv'), this.$p.t('benotungstool/c4negativ') ,...this.notenOptions.map(opt => opt.bezeichnung)] }
					},
					headerFilterFunc: this.notenFilterFunc,
					widthGrow: 1
				},
				{title: Vue.computed(() => this.$capitalize(this.$p.t('benotungstool/c4freigabe'))), field: 'freigegeben', widthGrow: 1, formatter: this.freigabeFormatter, variableHeight: true},
				{title: Vue.computed(() => this.$capitalize(this.$p.t('benotungstool/c4zeugnisnote'))),
					field: 'note',
					formatter: this.notenFormatter,
					topCalc: this.negativeNotenCalc,
					topCalcFormatter: this.negativeNotenCalcFormatter,
					headerFilter: 'list',
					headerFilterParams: () => {
						return { values: ["\u00A0", this.$p.t('benotungstool/c4noteEmpty'),this.$p.t('benotungstool/c4positiv'), this.$p.t('benotungstool/c4negativ') ,...this.notenOptions.map(opt => opt.bezeichnung)] }
					},
					headerFilterFunc: this.notenFilterFunc,
					widthGrow: 1
				}, 
				{title: Vue.computed(() => this.$capitalize(this.$p.t('benotungstool/c4kommPruef'))), 
					field: 'kommPruef', widthGrow: 1, 
					formatter: this.pruefungFormatter, 
					topCalc: this.terminCalcFunc,
					topCalcFormatter: this.terminCalcFormatter,
					hozAlign:"center", minWidth: 150, visible: false
				}
			],
				persistence: false,
			}	
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
			const rows = table.getRows();

			// custom select all logic
			const allowed = rows.filter(r => r.getData().selectable);
			const selected = allowed.every(r => r.isSelected());

			if(selected){
				allowed.forEach(r => r.deselect());
			} else {
				allowed.forEach(r => r.select());
			}

			// stop built-in handler
			e.stopPropagation();
			return false;
		},
		fixTabulatorSelectionFormatter(row) {
			// if a row is not selectable, remove the checkbox from the dom
			
			const data = row.getData()
			
			const notSelectable = data.pruefungen?.find(p => p.pruefungstyp_kurzbz == 'kommPruef') || data.hoechsterAntritt >= 3
			if(notSelectable) {
				row.getElement().children[0]?.children[0]?.remove()
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
		checkFreigabe(freigabedatum, benotungsdatum, uid) {
			if(!freigabedatum) {
				// check for change -> set freigabe to 'changed' on change
				return 'offen'
			} else if(benotungsdatum > freigabedatum) {
				return 'changed'
			} else {
				return 'ok'
			}
		},
		unselectableFormatter(row) {
			
		},
		notenFormatter(cell) {
			const value = cell.getValue()
			const field = cell.getField()
			let style = 'display: flex; justify-content: center; align-items: center; height: 100%;';
			// Wenn sich die Zeugnisnote von der von Ihnen freigegebenen Note unterscheidet,
			// wird erstere rot umrandet markiert.
			
			
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
			
			if(value === 'ok') {
				return '<div style="'+style+'">' +
				'<i class="fa fa-circle-check" style="color:green"></i></div>'
			} else if (value === 'offen') {
				return '<div style="'+style+'">' +
					'<i class="fa-regular fa-circle"></i></div>'
			} else if (value === 'changed') {
				return '<div style="'+style+'">' +
					'<i class="fa fa-circle-check"></i></div>'
			}
			
			return value
		},
		saveNote(e, cell) { // Notenvorschlag freigeben
			const row = cell.getRow()
			const data = row.getData()

			if(!data.note_vorschlag) return
			this.loading = true
			this.$api.call(ApiNoten.saveNotenvorschlag(this.lv_id, this.sem_kurzbz, data.uid, data.note_vorschlag, data.punkte))
				.then((res) => {
				if (res.meta.status === 'success') {
					const s = this.studenten.find(s => s.uid === data.uid)
					this.teilnoten[s.uid].note_lv = data.note_vorschlag
					s.freigabedatum = this.parseDate(res.data[0]['freigabedatum'])
					s.benotungsdatum = this.parseDate(res.data[0]['benotungsdatum'])

					s.freigegeben = this.checkFreigabe(s.freigabedatum, s.benotungsdatum, s.uid);
					
					row.update({ lv_note: data.note_vorschlag, freigegeben: 'changed' })
					// row.update({ freigegeben: 'changed' })
					row.reformat() // trigger reformat of arrow
					this.changedNotenCounter++;
				}
			}).finally(()=>this.loading = false)
			
			
		},
		punkteFormatter(cell) {
				
		},
		teilnotenFormatter(cell) {
			const val = cell.getValue()
			
			let style = 'white-space: pre-line;'
			
			return '<div style="">'+val+'</div>'
		},
		pruefungFormatter(cell) {
			const data = cell.getData()

			const noteDef = data.note ? this.notenOptions.find(n => n.note == data.note) : null
			if(!data.note || !noteDef?.lkt_ueberschreibbar) {
				return ''
			}
			
			const colDef = cell.getColumn().getDefinition()
			
			// column is just a date, student can have any of his antritte on this date, so we need to get
			// student.pruefungen and look for a pruefung with this cols title as date

			const field = cell.getColumn().getField()
			const studentPruefung = field != 'kommPruef' ? data.pruefungen.find(p => p.datum === field) : data['kommPruef']

			// is this column/cell allowed to have an add pruefung action 			
			const canAdd = field !== 'kommPruef' && data.hoechsterAntritt < 4 && !colDef.originalNote
			
			// TODO: check for some time limit maybe? old pruefungen can be changed/created
			// TODO: it also looks ugly and unprofessional, should at some peoplt disable/hide the change action
			
			// Create root row div
			const rowDiv = document.createElement('div');
			rowDiv.className = 'row flex-nowrap';
			rowDiv.style.display = 'flex';
			rowDiv.style.justifyContent = 'center';
			rowDiv.style.alignItems = 'center';
			rowDiv.style.height = '100%';
			
			if(studentPruefung) {
				let color = ''
				switch(studentPruefung.pruefungstyp_kurzbz) {
					case 'Termin1':
						color = 'green'
						break
					case 'Termin2':
						color = 'yellow'
						break
					case 'Termin3':
						color = 'orange'
						break
					case 'kommPruef':
						color = 'red'
						break
				}

				rowDiv.style.borderLeft = `4px solid ${color}`;
				rowDiv.style.marginLeft = "6px";     // small indent so text doesn't overlap
				rowDiv.style.boxSizing = "border-box";
			}
			
			function createCol(content, classParam) {
				const colDiv = document.createElement('div');
				colDiv.className = classParam ?? 'col-4';
				colDiv.style.justifyContent = 'center';
				colDiv.style.alignItems = 'center';
				colDiv.style.height = '100%';

				if (typeof content === 'string') {
					colDiv.textContent = content;
				} else if (content instanceof HTMLElement) {
					colDiv.appendChild(content);
				}

				return colDiv;
			}
			
			if(data[field]) {
				// showing date in 
				
				// const dateParts = data[field].datum.split('-')
				// const date = `${dateParts[2]}.${dateParts[1]}.${dateParts[0]}`
				//
				// // First column (date)
				// rowDiv.appendChild(createCol(date, 'col-4 d-flex justify-content-center align-items-center'));

				const noteDefEntry = data.note ? this.notenOptions.find(n => n.note == data[field].note) : null

				// Second column (note_bezeichnung)
				rowDiv.appendChild(createCol(noteDefEntry.bezeichnung || '', 'col-auto d-flex justify-content-center align-items-center'));
				
				// no actions on kommPruef allowed
				// no actions on termin1 aka pruefung 0 aka ursprüngliche note erlaubt
				if(field === 'kommPruef' || colDef.originalNote) { 
					// rowDiv.appendChild(createCol('', 'col-4 d-flex justify-content-center align-items-center')); // append empty col4 to have formatting similar
					return rowDiv
				} 
				
				// Third column (button)
				const button = document.createElement('button');
				button.className = 'btn btn-outline-secondary';
				button.textContent = this.$capitalize(this.$p.t('benotungstool/changePruefungButtonText'));
				button.addEventListener('click', () => {
					this.openPruefungModal(data, data[field], field);
				});

				rowDiv.appendChild(createCol(button, 'col-4 d-flex justify-content-center align-items-center'));

				return rowDiv;
				
			} else if (canAdd) { // return new btn action
				
				// dont render the add button in cells where a younger pruefung exists for the students
				const youngerPruefung = data.pruefungen.find(p => p.datum > field) 
				if(youngerPruefung) return rowDiv
				
				const button = document.createElement('button');
				button.className = 'btn btn-outline-secondary';
				button.textContent = this.$capitalize(this.$p.t('benotungstool/addPruefungButtonText'));
				button.addEventListener('click', () => {
					this.openPruefungModal(data, null, field)
				});

				rowDiv.appendChild(createCol(button), 'col-4 d-flex justify-content-center align-items-center');

				return rowDiv;
			} else return ''
		},
		openPruefungModal(student, pruefung = null, field) {
			this.pruefungStudent = student
			this.pruefung = pruefung
			const dateStr = this.pruefung?.datum ?? field
		
			const pruefungDateParts = dateStr.split('-')
			
			// does not work correctly

			// new date obj so datepicker picks ob the change by ref
			// const newDate = new Date()
			// newDate.setFullYear(+pruefungDateParts[0])
			// newDate.setMonth(+pruefungDateParts[1])
			// // newDate.setMonth(newDate.getMonth() - 1) // acount for js date month offset
			// newDate.setDate(+pruefungDateParts[2])
			
			// works correctly
			const newDate = new Date(Number(pruefungDateParts[0]), Number(pruefungDateParts[1]) - 1, Number(pruefungDateParts[2]))
			this.selectedPruefungDate = newDate
			
			
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
			if(def.originalNote) return this.$capitalize(this.$p.t('benotungstool/c4originalZnote'))
			return def.title;
		},
		arrowFormatter(cell) {
			const row = cell.getRow()
			const data = row.getData()
			
			let style = 'display: flex; justify-content: center; align-items: center; height: 100%;'
			
			if(!data.note_vorschlag || (data.note_vorschlag == data.lv_note)) { // uncolored arrow
				return '<div style="'+style+'">' +
					'<i class="fa fa-arrow-right"></i></div>'
			}
			
			// can save a notenvorschlag -> colored
			return '<div style="'+style+'">' +
				'<i class="fa fa-arrow-right fa-2xl" style="color:#00649C"></i></div>'
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
		insertSortedDate(arr, dateStr) {
			// Binary search to find insertion index
			let left = 0, right = arr.length;
			while (left < right) {
				const mid = (left + right) >> 1;
				if (arr[mid] < dateStr) left = mid + 1;
				else right = mid;
			}
			arr.splice(left, 0, dateStr); // insert at index
			return arr;
		},
		tableResolve(resolve) {
			this.tableBuiltResolve = resolve
		},
		notenOptionsResolve(resolve) {
			this.notenOptionsResolve = resolve
		},
		setupData(data){
			this.studenten = data[0] ?? []
			this.studenten.forEach(s => {
				s.pruefungen = []
				s.infoString = `${s.vorname} ${s.nachname}`// (${s.semester}${s.verband}${s.gruppe}) Mat.: ${s.matrikelnr}`// used for multiselect
			})
			this.pruefungen = data[1] ?? []
			this.domain = data[2]
			
			// contains notenvorschläge from moodle, lv_note 
			this.teilnoten = data[3] ?? []
			
			// let pruefungenRegularColCount = 0;
			this.distinctPruefungsDates = []
			const cols = [...this.notenTableOptions.columns.slice(0, -1)];
			let kommCol = null
			if(this.config?.CIS_GESAMTNOTE_PRUEFUNG_KOMMPRUEF) kommCol = this.notenTableOptions.columns[this.notenTableOptions.columns.length - 1];
			
			this.pruefungen?.forEach(p => {
				const dateParts = p.datum.split('-')
				p.dateObj = new Date(dateParts[0], +(dateParts[1]) - 1, dateParts[2])
				
				const student = this.studenten.find(s => s.uid === p.student_uid)
				
				if(!student) return
				
				// TODO: filter kommPruef here? or change kommProf ColDefinition
				
				if(p.pruefungstyp_kurzbz !== 'kommPruef' && !this.distinctPruefungsDates.includes(p.datum)) this.distinctPruefungsDates.push(p.datum)
				
				// seperate kommPruefungen from previous pruefungen counts since the column count variability always ends with this
				if(p.pruefungstyp_kurzbz == 'kommPruef') {
					student['kommPruef'] = p
				} else {
					student.pruefungen.push(p)
				}
				
				// if(student.pruefungen.length > pruefungenRegularColCount) pruefungenRegularColCount = student.pruefungen.length
			})

			this.studenten?.forEach(s => {
				// sort students regular pruefungen by datum
				s.pruefungen.sort((p1, p2) => {
					if(p1.datum > p2.datum) {
						return 1
					} else if (p1.datum < p2.datum) {
						return -1
					} else {
						return 0
					}
				})
				// set the sorted pruefungen to their respective column fields
				s.pruefungen.forEach((p, i) => {
					s[p.datum] = p
				})

				s.hoechsterAntritt = this.getAntrittCountStudent(s)
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
					const notenOption = this.notenOptions.find(n=>n.note == g.grade)
					if(notenOption.positiv) s.teilnote += ('<span>'+g.text +'</span>'+ '<br/>')
					else s.teilnote += ('<span style="color: red;">'+g.text +'</span>'+ '<br/>')
				})
				
				Object.defineProperty(s, 'selectable', {
					get() {
						const kP = s.pruefungen?.find(p => p.pruefungstyp_kurzbz == 'kommPruef')
						return !(kP || s.hoechsterAntritt >= 3)
					},
					set() {
						// empty setter so tabulator doesnt scream	
					},
					enumerable: true,
					configurable: true
				})
				
			})

			this.distinctPruefungsDates.sort((d1, d2) => {
				if(d1 > d2) {
					return 1
				} else if (d1 < d2) {
					return -1
				} else {
					return 0
				}
			})
			this.distinctPruefungsDates.forEach((date, index)=>{
				const dateparts = date.split('-')
				const titledate = `${dateparts[2]}.${dateparts[1]}.${dateparts[0]}`
				
				// TODO: should studenten without shadow pruefung Termin have their "ursprüngliche Zeugnisnote" 
				// col filled for consistency reasons?
			
				// TODO: test if this holds true
				const originalNote = index === 0
				cols.push({
					title: titledate,//this.$p.t('benotungstool/pruefungNr', [index+1]),
					field: date,
					formatter: this.pruefungFormatter,
					titleFormatter: this.pruefungTitleFormatter,
					topCalc: this.terminCalcFunc,
					topCalcFormatter: this.terminCalcFormatter,
					hozAlign:"center",
					widthGrow: 1,
					minWidth: 200,
					originalNote,
					visible: true
				})
			})

			if(this.config?.CIS_GESAMTNOTE_PRUEFUNG_KOMMPRUEF) cols.push(kommCol) // keep kommPruef Col as last

			this.loading = false
			
			this.$refs.notenTable.tabulator.clearSort()
			this.$refs.notenTable.tabulator.setColumns(cols)
			this.$refs.notenTable.tabulator.setData(this.studenten);
			this.$refs.notenTable.tabulator.redraw(true);
		},
		loadNoten(lv_id, sem_kurzbz) {
			this.loading = true
			this.$api.call(ApiNoten.getStudentenNoten(lv_id, sem_kurzbz))
				.then(res => {
					if(res?.data) this.setupData(res.data)
					if(res?.meta?.getExternalGradesError) this.$fhcAlert.alertError(res.meta.getExternalGradesError)
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
		async setupCreated() {
			this.loading = true
			
			this.debouncedFetchPunkteForPruefung = debounce(this.fetchNoteForPunktePruefung, 500)
			
			// fetch cis config regarding gesamtnoteneingabe, needs to be fetched before setup can finish
			const configPromise = this.$api.call(ApiNoten.getCisConfig()).then(res => {
				this.config = res.data
			})
			
			// fetch lva dropdown
			this.$api.call(ApiLehre.getZugewieseneLv(this.viewData?.uid, this.sem_kurzbz)).then(res => {
				this.lehrveranstaltungen = res.data
				
				// build dropdown option string
				this.lehrveranstaltungen.forEach(lva => {
					lva.fullString = `${lva.stg_kurzbz} - ${lva.lv_semester}: ${lva.lv_bezeichnung}`
				})
				
				this.selectedLehrveranstaltung = this.lehrveranstaltungen.find(lva => lva.lehrveranstaltung_id == this.lv_id)
			})
			
			LehreinheitenModule.setupContext(this.$.appContext.config.globalProperties)
			LehreinheitenModule.bindParams(Vue.ref(Vue.computed(() => this.LeDropdownParams)));
			
			// fetch sem_kurzbz dropdown
			this.$api.call(ApiStudiensemester.getStudiensemester()).then(res => {
				this.studiensemester = res.data[0]
				this.selectedSemester = this.studiensemester.find(sem => sem.studiensemester_kurzbz === this.sem_kurzbz)
			})
			
			// fetch noten dropdown
			this.$api.call(ApiNoten.getNoten()).then(async res => {
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
			
			this.loadNoten(this.lv_id, this.sem_kurzbz)
			this.calcMaxTableHeight()
			
		},
		lvChanged(e) {
			this.$router.push({
				name: "Benotungstool",
				params: {
					sem_kurzbz: this.sem_kurzbz,
					lv_id: e.value.lehrveranstaltung_id
				}
			})
			
			// reload data
			this.loadNoten(e.value.lehrveranstaltung_id, this.sem_kurzbz)
		},
		ssChanged(e) {
			// change url params & write history
			this.$router.push({
				name: "Benotungstool",
				params: {
					sem_kurzbz: e.value.studiensemester_kurzbz,
					lv_id: this.lv_id
				}
			})

			this.loading = true
			// diff lv_id -> reload zugewiesene lv
			this.$api.call(ApiLehre.getZugewieseneLv(this.viewData?.uid, e.value.studiensemester_kurzbz)).then(res => {
				this.lehrveranstaltungen = res.data

				// build dropdown option string
				this.lehrveranstaltungen.forEach(lva => {
					lva.fullString = `${lva.stg_kurzbz} - ${lva.lv_semester}: ${lva.lv_bezeichnung}`
				})

				this.selectedLehrveranstaltung = this.lehrveranstaltungen.find(lva => lva.lehrveranstaltung_id == this.lv_id)
			}).then(()=>{
				
				// reload data
				this.loadNoten(this.lv_id, e.value.studiensemester_kurzbz)
			}).finally( () => this.loading = false)

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
		getPruefungstypForStudentByAntritt(student) { 
			// when adding new pruefungen, determine the next pruefungstyp by using the antritt counter
			switch (student.hoechsterAntritt) {
				case 0:
					return "Termin2"
					break
				case 1: 
					return "Termin2"
					break
				case 2: 
					return "Termin3"
					break
				default:
					return ""
			}
		},
		savePruefungEingabe() {
			const year = this.selectedPruefungDate.getFullYear();
			const month = String(this.selectedPruefungDate.getMonth() + 1).padStart(2, '0'); // Months are 0-based
			const day = String(this.selectedPruefungDate.getDate()).padStart(2, '0');
			const dateStr = `${year}-${month}-${day}`;
			
			// first pruefung is always "Termin2" since normal note counts as Termin1
			// const pOffset = this.pruefung === null && this.pruefungStudent.pruefungen.length === 0 ? 2 : 1

			const typ = this.pruefung ? this.pruefung.pruefungstyp_kurzbz : this.getPruefungstypForStudentByAntritt(this.pruefungStudent)
			const note = this.selectedPruefungNote?.note ?? 9 // noch nicht eingetragen
			// TODO: check if this is supposed to work this way
			const punkte = this.selectedPruefungPunkte ?? 0
			this.$api.call(ApiNoten.saveStudentPruefung(
				this.pruefungStudent.uid,
				note,
				punkte,
				dateStr,
				this.lv_id,
				this.pruefungStudent.lehreinheit_id,
				this.sem_kurzbz,
				typ
			)).then(res => {
				if(res.meta.status === 'success') { //'Prüfung für Student ' + this.pruefungStudent.uid + ' bearbeitet oder angelegt'
					this.$fhcAlert.alertDefault(
						'success',
						'Info',
						this.$capitalize(this.$p.t('benotungstool/pruefungSaveForUid', [this.pruefungStudent.uid])),
						true
					)
					const s = this.studenten.find(s => s.uid === res.data[1]?.student_uid)
					
					s.freigabedatum = this.parseDate(res.data[1]?.['freigabedatum'])
					s.benotungsdatum = this.parseDate(res.data[1]?.['benotungsdatum'])
					
					s.freigegeben = this.checkFreigabe(s.freigabedatum, s.benotungsdatum, s.uid);
					
					s.lv_note = res.data[1]?.note
					
					// add new pruefung to row
					if(!this.pruefung) {			
						this.handleAddNewTermin(res.data, s)
					} else { // update existing
						const oldIndex = s.pruefungen.findIndex(p => p.pruefung_id == this.pruefung.pruefung_id)
						if(oldIndex !== -1) {
							s.pruefungen.splice(oldIndex, 1, res.data[0])
							s[res.data[0].datum] = res.data[0]
						}

						// antritte might have changed due to different benotung
						s.hoechsterAntritt = this.getAntrittCountStudent(s)
					}
					
					const oldScrollLeft = this.$refs.notenTable.tabulator.rowManager.scrollLeft
					const oldScrollTop = this.$refs.notenTable.tabulator.rowManager.scrollTop
					this.$refs.notenTable.tabulator.redraw(true)

					Vue.nextTick(()=> {
						const table = this.$refs.notenTable.tabulator.element.querySelector('.tabulator-tableholder')
						if(table) {
							table.scrollLeft = oldScrollLeft;
							table.oldScrollTop = oldScrollTop;
						}
					})

				}
			}).finally(()=> {
				this.pruefungStudent = null
				this.pruefung = null
			})
			
			this.$refs.modalContainerPruefung.hide()
		},
		handleAddNewTermin(data, student){
			const savedPruefung = data[0]
			const extra = data[2]
			
			// check for extra pruefung (termin1) to add before
			if(extra) {
				extra.datum = extra.datum.split(' ')[0]
				if(!this.distinctPruefungsDates.includes(extra.datum)) {
					this.insertSortedDate(this.distinctPruefungsDates, extra.datum)
				}
				
				student.pruefungen.push(extra)
				student[extra.datum] = extra
			}

			if(!this.distinctPruefungsDates.includes(savedPruefung.datum)) {
				this.insertSortedDate(this.distinctPruefungsDates, savedPruefung.datum)
			}
			
			// add pruefung to pruefungen array
			student.pruefungen.push(savedPruefung)
			
			// add pruefung to student via its datum as a field
			student[savedPruefung.datum] = savedPruefung

			// usually should be in order naturally, just to be save
			student.pruefungen.sort((p1, p2) => {
				if(p1.datum > p2.datum) {
					return 1
				} else if (p1.datum < p2.datum) {
					return -1
				} else {
					return 0
				}
			})
			
			// recalculate student antritte
			student.hoechsterAntritt = this.getAntrittCountStudent(student)
			
			// add col to table
			const cols = [...this.notenTableOptions.columns.slice(0, -1)];
			let kommCol = null
			if(this.config?.CIS_GESAMTNOTE_PRUEFUNG_KOMMPRUEF) kommCol = this.notenTableOptions.columns[this.notenTableOptions.columns.length - 1];


			// TODO: could reuse cols instead of recreating all from a variable maybe
			this.distinctPruefungsDates.forEach((date, index)=>{
				const dateparts = date.split('-')
				const titledate = `${dateparts[2]}.${dateparts[1]}.${dateparts[0]}`

				// TODO: should studenten without shadow pruefung Termin have their "ursprüngliche Zeugnisnote" 
				// col filled for consistency reasons?

				// TODO: test if this holds true, maybe in case where there are only kommPruef?
				const originalNote = index === 0
				cols.push({
					title: titledate,//this.$p.t('benotungstool/pruefungNr', [index+1]),
					field: date,
					formatter: this.pruefungFormatter,
					titleFormatter: this.pruefungTitleFormatter,
					hozAlign:"center",
					widthGrow: 1,
					minWidth: 200,
					originalNote,
					visible: true
				})
			})

			if(this.config?.CIS_GESAMTNOTE_PRUEFUNG_KOMMPRUEF) cols.push(kommCol) // keep kommPruef Col as last

			// set Cols
			this.$refs.notenTable.tabulator.clearSort()
			this.$refs.notenTable.tabulator.setColumns(cols)
			
			// redraw table outside this function
		},
		saveNoteneingabe() {
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
					s.freigabedatum = this.parseDate(d.freigabedatum)
					s.benotungsdatum = this.parseDate(d.benotungsdatum)
					s.freigegeben = this.checkFreigabe(s.freigabedatum, s.benotungsdatum, s.uid);
				})
				this.changedNotenCounter++;

				this.$refs.notenTable.tabulator.redraw(true)
			})
			
			this.$refs.modalContainerNotenSpeichern.hide()
		},
		openSaveModal() {
			this.$refs.modalContainerNotenSpeichern.show()
		},
		openNewPruefungsdatumModal() {
			this.$refs.modalContainerNeuesPruefungsdatum.show()
		},
		openNotenImportModal() {
			this.$refs.modalContainerNotenImport.show()
		},
		getOptionLabelNotePruefung(option) {
			return option.bezeichnung
		},
		leChanged(e) {
			this.selectedLehreinheit = e.value
		},
		addPruefung(){

			this.$refs.modalContainerNeuesPruefungsdatum.hide()
			
			// filter students that already have a pruefung on datum
			
			const year = this.selectedPruefungDate.getFullYear();
			const month = String(this.selectedPruefungDate.getMonth() + 1).padStart(2, '0'); // Months are 0-based
			const day = String(this.selectedPruefungDate.getDate()).padStart(2, '0');
			const dateStrDb = `${year}-${month}-${day}`;
			const dateStrFront = `${day}.${month}.${year}`;
			
			const uids = this.selectedUids.map(student => {
				return {
					uid: student.uid,
					lehreinheit_id: student.lehreinheit_id,
					typ: this.getPruefungstypForStudentByAntritt(student)//student.hoechsterAntritt 
				}
			})
			
			this.loading = true;
			this.$api.call(ApiNoten.createPruefungen(
				uids,
				dateStrDb, 
				this.lv_id,
				this.sem_kurzbz,
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
						if(student.savedPruefung || student.extraPruefung) {
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
			}).finally(()=>this.loading = false)
		},
		getAntrittCountStudent(student) {
			// checks for existence of a prüfung with a note that resolves to a 
			// "angetretene Prüfung" -> anything except "entschuldigt" & "noch nicht eingetragen"
			// and returns the next allowed pruefungstyp from the number of taken pruefungen
			
			// 1 -> reguläre note
			// 2 -> erste Nachprüfung / Termin2
			// 3 -> 2te Nachprüfung / Termin3
			// 4 -> kommPruef
			if(student['kommPruef']) return 4
			
			let pruefungsAntrittCount = 0
			const pLen = student.pruefungen.length
			for(let i = 0; i < pLen; i++) {
				const p = student.pruefungen[i]
				
				if(p.note != 9 && p.note != 17) pruefungsAntrittCount++
			}

			// when student never had to take an exam beyond the original benotung 
			// aka pruefungsantritt (even though it does not have to have pruefungscharacter)
			// it still counts as an antritt, except it is coming from a notenOption like "angerechnet" 
			// which indicates no participation at all
			if(pruefungsAntrittCount === 0 && student.note){
				const noteOption = this.notenOptions.find(note => note.note == student.note)

				if(noteOption.lehre) return 1
				else return 0
			}
			
			return pruefungsAntrittCount
		}
	},
	watch: {
		selectedUids(newVal, oldVal) {
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
			if(!this.$refs.notenTable) return
			this.$refs.notenTable.tabulator.clearFilter();
			if(newVal) this.$refs.notenTable.tabulator.setFilter("lehreinheit_id", "=", newVal.lehreinheit_id);
		},
		getKommPruefCount(newVal) {
			if(!this.config.CIS_GESAMTNOTE_PRUEFUNG_KOMMPRUEF) return 0
			if(this.$refs.notenTable?.tabulator && newVal > 0) {
				const kommPruefCol = this.$refs.notenTable?.tabulator.getColumn("kommPruef")
				kommPruefCol.show()
			} else if(this.$refs.notenTable?.tabulator && newVal == 0) {
				const kommPruefCol = this.$refs.notenTable?.tabulator.getColumn("kommPruef")
				kommPruefCol.hide()
			}
		}
	},
	computed: {
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
				lv_id: this.lv_id,
				sem_kurzbz: this.sem_kurzbz
			}	
		},	
		getStudentenOptions() {
			return this.studenten ? this.studenten.filter(s => s.selectable) : []
		},
		getKommPruefCount(){
			let counter = 0
			this.studenten?.forEach(s => {if(s['kommPruef']){counter++}})	
			return counter
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
		getNotenfreigabeHinweistext() {
			return this.$capitalize(this.$p.t('benotungstool/notenfreigabeHinweistextv3'))
		},
		getNotenimportHinweistext() {
			return this.$capitalize(this.$p.t('benotungstool/notenimportHinweistextv3'))
		}
	},
	created() {
		this.setupCreated()
	},
	mounted() {
		this.setupMounted()
	},
	template: `
		<bs-modal ref="modalContainerNotenImport" class="bootstrap-prompt" dialogClass="modal-lg">
			<template v-slot:title>{{$capitalize($p.t('benotungstool/c4notenImportieren'))}}</template>
			<template v-slot:default>
				<div class="row mt-4 justify-content-center">
					<div v-html="getNotenimportHinweistext"></div>
				</div>
				<div class="row mt-4 justify-content-center">
					<Textarea v-model="importString" rows="5"></Textarea>
				</div>
			</template>
			<template v-slot:footer>
				<button type="button" class="btn btn-primary" @click="importNoten">{{ $capitalize($p.t('benotungstool/c4import')) }}</button>
			</template>
		</bs-modal>

		<bs-modal ref="modalContainerNeuesPruefungsdatum" class="bootstrap-prompt" dialogClass="modal-lg"
			@hideBsModal="neuesPruefungsdatumModalVisible = false"
			@showBsModal="neuesPruefungsdatumModalVisible = true"
			>
			<template v-slot:title>{{$capitalize($p.t('benotungstool/c4addNewPruefung'))}}</template>
			<template v-slot:default>
				<div class="row justify-content-center">
					<div class="col-3 text-center">{{$capitalize($p.t('benotungstool/c4date'))}}:</div>
					<div class="col-6">
						<datepicker
							v-model="selectedPruefungDate"
							:clearable="false"
							format="dd.MM.yyyy"
							:enableTimePicker="false"
							:text-input="true"
							:auto-apply="true">
						</datepicker>
					</div>
				</div>
				
				<div class="row mt-4 justify-content-center">
					<div class="col-3 text-center">{{$capitalize($p.t('benotungstool/prueflingSelectionv2'))}}:</div>
					<div class="col-6">
						<Multiselect 
							v-model="selectedUids" 
							:options="getStudentenOptions" 
							optionLabel="infoString" 
							placeholder="Studenten auswählen"
							:maxSelectedLabels="3"
							showToggleAll
							class="w-100" />
					</div>
				</div>
				
			</template>
			<template v-slot:footer>
				<button type="button" class="btn btn-primary" @click="addPruefung">{{ $capitalize($p.t('benotungstool/c4addNewPruefung')) }}</button>
			</template>
		 </bs-modal>

		<bs-modal ref="modalContainerNotenSpeichern" class="bootstrap-prompt" dialogClass="modal-lg">
			<template v-slot:title>{{ $p.t('benotungstool/noteneingabeSpeichern') }}</template>
			<template v-slot:default>
				<div class="row mt-4 justify-content-center">
					 <div v-html="getNotenfreigabeHinweistext"> </div>
				</div>
				<div class="row mt-4 justify-content-center">
					<div class="col-auto">
						<Password v-model="password" :feedback="false" showIcon="fa fa-eye" :toggleMask="true" :promptLabel="$p.t('benotungstool/passwort')"></Password>
					</div>
				</div>
			</template>
			<template v-slot:footer>
				<button type="button" class="btn btn-primary" @click="saveNoteneingabe">{{ $p.t('benotungstool/noteneingabeBestätigen') }}</button>
			</template>
		</bs-modal>

		<bs-modal ref="modalContainerPruefung" class="bootstrap-prompt" dialogClass="modal-lg">
			<template v-slot:title>{{ pruefung ? $capitalize($p.t('benotungstool/editPruefungFor')) : $capitalize($p.t('benotungstool/createPruefungFor')) }} {{pruefungStudent?.vorname}} {{pruefungStudent?.nachname}}</template>
			<template v-slot:default>
				<div class="row justify-content-center">
					<div class="col-1 text-center">{{$capitalize($p.t('benotungstool/c4date'))}}:</div>
					<div class="col-6">
						<datepicker
							v-model="selectedPruefungDate"
							:clearable="false"
							:enableTimePicker="false"
							format="dd.MM.yyyy"
							:text-input="true"
							:auto-apply="true">
						</datepicker>
					</div>
				</div>
				<div v-if="config?.CIS_GESAMTNOTE_PUNKTE == true" class="row justify-content-center mt-4">
					<div class="col-1 text-center">{{$capitalize($p.t('benotungstool/c4punkte'))}}:</div>
					<div class="col-6">
						<InputNumber 
							v-model="selectedPruefungPunkte"
							@input="debouncedFetchPunkteForPruefung"
							inputId="selectedPruefungInput" :min="0" :max="100000">
						</InputNumber>
					</div>
				</div>
				<div class="row justify-content-center mt-4">
					<div class="col-1 text-center">{{$capitalize($p.t('lehre/note'))}}:</div>
					<div class="col-6">
						<Dropdown :placeholder="$capitalize($p.t('lehre/note'))" 
							:disabled="config?.CIS_GESAMTNOTE_PUNKTE == true"
							:style="{'width': '100%'}" :optionLabel="getOptionLabelNotePruefung" 
							v-model="selectedPruefungNote" :options="notenOptionsLehre" showClear>
							<template #optionsgroup="slotProps">
								<div> {{ option.bezeichnung }} </div>
							</template>
						</Dropdown>
					</div>
				</div>
			</template>
			<template v-slot:footer>
				<button type="button" class="btn btn-primary" @click="savePruefungEingabe">{{ $capitalize($p.t('global/speichern')) }}</button>
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

		<div class="row">
			<div class="col-4">
				<h2>{{$capitalize($p.t('benotungstool/benotungstoolTitle'))}}</h2>
				<h4>{{ lv?.bezeichnung }}</h4>
			</div>
			<div class="col-2">
				<div class="col-lg-auto">
					<Dropdown @change="lvChanged" :style="{'width': '100%'}" :optionLabel="getOptionLabelLv"
						:placeholder="$capitalize($p.t('lehre/lehrveranstaltung'))"
						v-model="selectedLehrveranstaltung" :options="lehrveranstaltungen" appendTo="self">
						<template #optionsgroup="slotProps">
							<div> {{ option.fullString }} </div>
						</template>
					</Dropdown>
				</div>
			</div>

			<div class="col-2">
				<div class="col-lg-auto">
					<Dropdown @change="leChanged" :style="{'width': '100%'}" v-bind="LehreinheitenModule"
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
			</div>
			
			<div class="col-2">
				<div class="col-lg-auto">
					<Dropdown @change="ssChanged" :style="{'width': '100%'}" :optionLabel="getOptionLabel" 
						v-model="selectedSemester" :options="studiensemester" appendTo="self">
						<template #optionsgroup="slotProps">
							<div> {{ option.studiensemester_kurzbz }} </div>
						</template>
					</Dropdown>
				</div>
			</div>
		</div>
		<hr>
		
		<div id="notentable" class="row" :style="'overflow-x: auto;'">
			<core-filter-cmpt
				v-if="tabulatorCanBeBuilt"
				@uuidDefined="handleUuidDefined"
				:title="''"
				ref="notenTable"
				:tabulator-options="notenTableOptions"
				:tabulator-events="notenTableEventHandlers"
				tableOnly
				:sideMenu="false"
			>
				 <template #actions>
					
					<button @click="openNewPruefungsdatumModal" role="button" :class="getNewBtnClass">
						{{$capitalize($p.t('benotungstool/c4addNewPruefung'))}} <i class="fa fa-plus"></i>
					</button>
					
					<Divider layout="vertical" style="transform: translateY(12px)"/>
					
					<button @click="openNotenImportModal" role="button" :class="getNotenImportBtnClass">
						{{$capitalize($p.t('benotungstool/c4notenImportieren'))}} <i class="fa fa-file-import"></i>
					</button>
					<button @click="openSaveModal" role="button" :class="getSaveBtnClass">
						{{$capitalize($p.t('benotungstool/approveGrades'))}} <i class="fa fa-save"></i>
					</button>
					
					<Divider layout="vertical" style="transform: translateY(12px)"/>
					
					<h4>{{ getFreigabeCounter > 0 ? $capitalize($p.t('benotungstool/freigabecounterPositiv', [getFreigabeCounter])) : '' }}</h4>
				 </template>
			</core-filter-cmpt>
		</div>
    `,
};

export default Benotungstool;