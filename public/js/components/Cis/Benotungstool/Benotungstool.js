import {CoreFilterCmpt} from "../../filter/Filter.js";
import ApiLehre from "../../../api/factory/lehre.js";
import ApiNoten from "../../../api/factory/noten.js";
import ApiStudiensemester from "../../../api/factory/studiensemester.js";
import BsModal from '../../Bootstrap/Modal.js';
import VueDatePicker from '../../vueDatepicker.js.php';

export const Benotungstool = {
	name: "Benotungstool",
	components: {
		BsModal,
		CoreFilterCmpt,
		Dropdown: primevue.dropdown,
		Password: primevue.password,
		Datepicker: VueDatePicker,
		Multiselect: primevue.multiselect
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
			default: () => ({name: '', uid: ''}),
			validator(value) {
				return value && value.name && value.uid
			}
		}
	},
	data() {
		return {
			selectedUids: [], // shared selection state
			selectedLehreinheit: null,
			lehreinheiten: null,
			tabulatorCanBeBuilt: false,
			selectedPruefungNote: null,
			selectedPruefungDate: new Date(), // v-model for pruefung edit datepicker
			pruefungStudent: null,
			pruefung: null,
			password: '',
			changedNotenCounter: 0,
			tabulatorUuid: Vue.ref(0),
			domain: '',
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
				event: "cellClick",
				handler: async (e, cell) => {
					
				}
			},
			{
				event: "rowSelectionChanged",
				handler: async (data, rows) => {
					console.log("Selected Data:", data);
					console.log("Selected Rows:", rows);
					this.selectedUids = data;
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
					}
				}
			}
			]};
	},
	methods: {
		getNotenTableOptions() {
			return {
				height: 700,
				index: 'uid',
				layout: 'fitDataStretch',
				placeholder: this.$p.t('global/noDataAvailable'),
				selectable: true,
				selectableRangeMode: "click", // shift+click
				selectablePersistence: false, // reset selection on table reload
				columns: [
				{
					formatter: "rowSelection",
					titleFormatter: "rowSelection", // Adds "select all" checkbox in header
					hozAlign: "center",
					headerSort: false,
					cellClick: function (e, cell) {
						cell.getRow().toggleSelect();
					},
					width: 50,
				},
				{title: Vue.computed(() => this.$p.t('benotungstool/c4mail')), field: 'email', formatter: this.mailFormatter, tooltip: false, widthGrow: 1},
				{title: 'UID', field: 'uid', tooltip: false, widthGrow: 1},
				{title: Vue.computed(() => this.$p.t('benotungstool/c4vorname')), field: 'vorname',  tooltip: false, widthGrow: 1},
				{title: Vue.computed(() => this.$p.t('benotungstool/c4nachname')), field: 'nachname', widthGrow: 1},
				{title: Vue.computed(() => this.$p.t('benotungstool/c4teilnoten')), field: 'teilnote', widthGrow: 1, formatter: this.teilnotenFormatter},
				{title: Vue.computed(() => this.$p.t('benotungstool/c4note')), field: 'note_vorschlag',
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
						const rowData = cell.getRow().getData();
						const noteOption = this.notenOptions.find(opt => opt.note == rowData.note)
						if(!noteOption) return true
						
						// also if student has any pruefungsnote disable noten selection
						if(this.pruefungen.find(p => p.student_uid == rowData.uid)) return false
						
						return noteOption.lkt_ueberschreibbar
					},
					formatter: (cell) => {
						const rowData = cell.getRow().getData();
						const value = cell.getValue()
						const match = this.notenOptions?.find(opt => opt.note == value)
						const val =  match ? match.bezeichnung : value
						const p = this.pruefungen.find(p => p.student_uid == rowData.uid)
						let style = ''
						
						if(val === undefined) return ''
						if(p || !match?.lkt_ueberschreibbar) style = 'color: gray;font-style: italic; background-color: #f0f0f0;pointer-events: none;opacity: 0.6;user-select: none;cursor: not-allowed;'
						return '<div style="'+style+'">' + val + '</div>'
					},
					widthGrow: 1},
				{title: '', width: 50, hozAlign: 'center', formatter: this.arrowFormatter, cellClick: this.saveNote},
				{title: Vue.computed(() => this.$p.t('benotungstool/c4lvnote')), field: 'lv_note',
					formatter: this.notenFormatter,
					widthGrow: 1},
				{title: Vue.computed(() => this.$p.t('benotungstool/c4freigabe')), field: 'freigegeben', widthGrow: 1, formatter: this.freigabeFormatter},
				{title: Vue.computed(() => this.$p.t('benotungstool/c4zeugnisnote')), field: 'note', formatter: this.notenFormatter, widthGrow: 1}, 
				{title: Vue.computed(() => this.$p.t('benotungstool/c4kommPruef')), field: 'kommPruef', widthGrow: 1, formatter: this.pruefungFormatter, hozAlign:"center", minWidth: 150}
			],
				persistence: false,
			}	
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
			
			if(value === 'ok') {
				return '<div style="display: flex; justify-content: center; align-items: center; height: 100%">' +
				'<i class="fa fa-circle-check" style="color:green"></i></div>'
			} else if (value === 'offen') {
				return '<div style="display: flex; justify-content: center; align-items: center; height: 100%">' +
					'<i class="fa-regular fa-circle"></i></div>'
			} else if (value === 'changed') {
				return '<div style="display: flex; justify-content: center; align-items: center; height: 100%">' +
					'<i class="fa fa-circle-check"></i></div>'
			}
			
			return value
		},
		handlePasswordChanged(pw) {
			// console.log('pw:', pw)	
		},
		saveNote(e, cell) { // Notenvorschlag freigeben
			const row = cell.getRow()
			const data = row.getData()

			if(!data.note_vorschlag) return
			this.$api.call(ApiNoten.saveNotenvorschlag(this.lv_id, this.sem_kurzbz, data.uid, data.note_vorschlag))
				.then((res) => {
				if (res.meta.status === 'success') {
					const s = this.studenten.find(s => s.uid === data.uid)
					this.teilnoten[s.uid].note_lv = data.note_vorschlag
					s.freigabedatum = this.parseDate(res.data[1]['freigabedatum'])
					s.benotungsdatum = this.parseDate(res.data[1]['benotungsdatum'])

					s.freigegeben = this.checkFreigabe(s.freigabedatum, s.benotungsdatum, s.uid);
					
					row.update({ lv_note: data.note_vorschlag })
					row.update({ freigegeben: 'changed' })
					row.reformat() // trigger reformat of arrow
					this.changedNotenCounter++;
				}
			})
			
			
		},
		teilnotenFormatter(cell) {
			const val = cell.getValue()
			return '<div style="white-space: pre-line;">'+val+'</div>'
		},
		pruefungFormatter(cell) {
			const data = cell.getData()
			
			const noteDef = data.note ? this.notenOptions.find(n => n.note == data.note) : null
			if(!data.note || !noteDef?.lkt_ueberschreibbar) {
				return ''
			}
			
			// TODO: check for some time limit maybe? old pruefungen can be changed/created
			
			// Create root row div
			const rowDiv = document.createElement('div');
			rowDiv.className = 'row';
			rowDiv.style.display = 'flex';
			rowDiv.style.justifyContent = 'center';
			rowDiv.style.alignItems = 'center';
			rowDiv.style.height = '100%';
			
			function createCol(content) {
				const colDiv = document.createElement('div');
				colDiv.className = 'col-4';
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
			
			const field = cell.getColumn().getField()
			if(data[field]) {
				const dateParts = data[field].datum.split('-')
				const date = `${dateParts[2]}.${dateParts[1]}.${dateParts[0]}`

				// First column (date)
				rowDiv.appendChild(createCol(date));

				// Second column (note_bezeichnung)
				rowDiv.appendChild(createCol(data[field].note_bezeichnung || ''));

				if(field === 'kommPruef' || field === 'pruefungNr0') {// no actions on kommPruef allowed
					rowDiv.appendChild(createCol('')); // append empty col4 to have formatting similar
					return rowDiv
				} 
				
				// Third column (button)
				const button = document.createElement('button');
				button.className = 'btn btn-outline-secondary';
				button.textContent = 'Change'; // TODO: phrase
				button.addEventListener('click', () => {
					this.openPruefungModal(data, data[field]);
				});

				rowDiv.appendChild(createCol(button));

				return rowDiv;
				
			} else if (field !== 'kommPruef' && field !== 'pruefungNr0') { // return new btn action
				const button = document.createElement('button');
				button.className = 'btn btn-outline-secondary';
				button.textContent = 'Add'; // TODO: phrase
				button.addEventListener('click', () => {
					this.openPruefungModal(data)
				});

				rowDiv.appendChild(createCol(button));

				return rowDiv;
			} else return ''
		},
		openPruefungModal(student, pruefung = null) {
			this.pruefungStudent = student
			this.pruefung = pruefung
			debugger
			if(this.pruefung?.datum) {
				const pruefungDateParts = this.pruefung?.datum?.split('-')

				// new date obj so datepicker picks ob the change by ref
				const newDate = new Date()
				newDate.setFullYear(pruefungDateParts[0])
				newDate.setMonth(pruefungDateParts[1])
				newDate.setDate(pruefungDateParts[2])
				this.selectedPruefungDate = newDate
			} else {
				const newDate = new Date()
				newDate.setTime(Date.now())
				this.selectedPruefungDate = newDate
			}
			
			if(this.pruefung?.note) {
				this.selectedPruefungNote = this.notenOptions.find(n => n.note == this.pruefung.note)
			} else {
				this.selectedPruefungNote = null
			}
			
			this.$refs.modalContainerPruefung.show()
		},
		pruefungTitleFormatter(cell) {
			const col = cell.getColumn()
			if(col.getField() === "pruefungNr0") return this.$p.t('benotungstool/c4originalZnote')
			return col.getDefinition().title;
		},
		arrowFormatter(cell) {
			const row = cell.getRow()
			const data = row.getData()
			
			if(!data.note_vorschlag || (data.note_vorschlag == data.lv_note)) { // uncolored arrow
				return '<div style="display: flex; justify-content: center; align-items: center; height: 100%">' +
					'<i class="fa fa-arrow-right"></i></div>'
			}
			
			// can save a notenvorschlag -> colored
			return '<div style="display: flex; justify-content: center; align-items: center; height: 100%">' +
				'<i class="fa fa-arrow-right" style="color:#00649C"></i></div>'
		},
		mailFormatter(cell) {
			const val = cell.getValue()
			return '<div style="display: flex; justify-content: center; align-items: center; height: 100%">' +
				'<a href='+val+'><i class="fa fa-envelope" style="color:#00649C"></i></a></div>'
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
			const distinctPruefungsDates = []
			const cols = [...this.notenTableOptions.columns.slice(0, -1)];
			const kommCol = this.notenTableOptions.columns[this.notenTableOptions.columns.length - 1];
			
			this.pruefungen?.forEach(p => {
				const student = this.studenten.find(s => s.uid === p.student_uid)
				
				if(!student) return
				
				// TODO: filter kommPruef here? or change kommProf ColDefinition
				
				if(!distinctPruefungsDates.includes(p.datum)) distinctPruefungsDates.push(p.datum)
				
				// seperate kommPruefungen from previous pruefungen counts since the column count variability always ends with this
				if(p.pruefungstyp_kurzbz == 'kommPruef') {
					student['kommPruef'] = p
				} else {
					student.pruefungen.push(p)
				}
				
				// if(student.pruefungen.length > pruefungenRegularColCount) pruefungenRegularColCount = student.pruefungen.length
			})

			// TODO: check if note is überschreibbar!
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
				
				s.email = this.buildMailToLink(s)
				s.lv_note = this.teilnoten[s.uid].note_lv
				s.freigabedatum = this.parseDate(this.teilnoten[s.uid]['freigabedatum'])
				s.benotungsdatum = this.parseDate(this.teilnoten[s.uid]['benotungsdatum'])
				s.freigegeben = this.checkFreigabe(s.freigabedatum, s.benotungsdatum, s.uid);
				
				const grades = this.teilnoten[s.uid].grades
				s.teilnote = ''
				
				grades.forEach(g => {
					const notenOption = this.notenOptions.find(n=>n.note == g.grade)
					if(notenOption.positiv) s.teilnote += ('<span>'+g.text +'</span>'+ '<br/>')
					else s.teilnote += ('<span style="color: red;">'+g.text +'</span>'+ '<br/>')
				})
				
			})
			
			distinctPruefungsDates.sort((d1, d2) => {
				if(d1 > d2) {
					return 1
				} else if (d1 < d2) {
					return -1
				} else {
					return 0
				}
			})
			distinctPruefungsDates.forEach((date, index)=>{
				// TODO date format dd.mm.yyyy
				
				// const title = 
				
				cols.push({
					title: date,//this.$p.t('benotungstool/pruefungNr', [index+1]),
					field: date,
					formatter: this.pruefungFormatter,
					titleFormatter: this.pruefungTitleFormatter,
					hozAlign:"center",
					widthGrow: 1,
					minWidth: 150
				})
			})
			console.log('distinctPruefungsDates', distinctPruefungsDates)
			console.log('cols', cols)

			cols.push(kommCol) // keep kommPruef Col as last

			this.$refs.notenTable.tabulator.clearSort()
			this.$refs.notenTable.tabulator.setColumns(cols)
			this.$refs.notenTable.tabulator.setData(this.studenten);
			this.$refs.notenTable.tabulator.redraw(true);
		},
		loadNoten(lv_id, sem_kurzbz) {
			this.$api.call(ApiNoten.getStudentenNoten(lv_id, sem_kurzbz))
				.then(res => {
					if(res?.data) this.setupData(res.data)
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

			this.notenTableOptions.height = window.visualViewport.height - rect.top
			this.$refs.notenTable.tabulator.setHeight(this.notenTableOptions.height)
		},
		setupCreated() {
			// fetch lva dropdown
			this.$api.call(ApiLehre.getZugewieseneLv(this.viewData?.uid, this.sem_kurzbz)).then(res => {
				this.lehrveranstaltungen = res.data
				
				// build dropdown option string
				this.lehrveranstaltungen.forEach(lva => {
					lva.fullString = `${lva.stg_kurzbz} - ${lva.lv_semester}: ${lva.lv_bezeichnung}`
				})
				
				this.selectedLehrveranstaltung = this.lehrveranstaltungen.find(lva => lva.lehrveranstaltung_id == this.lv_id)
			})
			
			this.$api.call(ApiLehre.getLeForLv(this.lv_id, this.sem_kurzbz)).then(res => {

				const data =  []
				// TODO: could be done on server in some shared function, copied from anw extension for now
				res.data?.retval?.forEach(entry => {

					const existing = data.find(e => e.lehreinheit_id === entry.lehreinheit_id)
					if (existing) {
						// supplement info
						existing.infoString += ', '
						if (entry.gruppe_kurzbz !== null) {
							existing.infoString += entry.gruppe_kurzbz
						} else {
							existing.infoString += entry.kurzbzlang + '-' + entry.semester
								+ (entry.verband ? entry.verband : '')
								+ (entry.gruppe ? entry.gruppe : '')
						}
					} else {
						// entries are supposed to be fetched ordered by non null gruppe_kurzbz first
						// so a new entry will always start with those groups, others are appended afterwards
						entry.infoString = entry.kurzbz + ' - ' + entry.lehrform_kurzbz + ' - '
						if (entry.gruppe_kurzbz !== null) {
							entry.infoString += entry.gruppe_kurzbz
						} else {
							entry.infoString += entry.kurzbzlang + '-' + entry.semester
								+ (entry.verband ? entry.verband : '')
								+ (entry.gruppe ? entry.gruppe : '')
						}

						data.push(entry)
					}
				})

				data.forEach(entry => {
					entry.infoString += ' | 👥' + entry.studentcount + ' | 📅' + entry.termincount
				})
				
				this.lehreinheiten = [...data]
				
			})
			
			// fetch sem_kurzbz dropdown
			this.$api.call(ApiStudiensemester.getStudiensemester()).then(res => {
				this.studiensemester = res.data[0]
				this.selectedSemester = this.studiensemester.find(sem => sem.studiensemester_kurzbz === this.sem_kurzbz)
			})
			
			// fetch noten dropdown
			this.$api.call(ApiNoten.getNoten()).then(res => {
				this.notenOptions = res.data
				this.notenOptionsLehre = res.data.filter(n => n.lehre === true)
				this.notenTableOptions = this.getNotenTableOptions()
				this.tabulatorCanBeBuilt = true // because promises would be more work and not much better here
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
			
			// diff lv_id -> reload zugewiesene lv
			this.$api.call(ApiLehre.getZugewieseneLv(this.viewData?.uid, this.sem_kurzbz)).then(res => {
				this.lehrveranstaltungen = res.data

				// build dropdown option string
				this.lehrveranstaltungen.forEach(lva => {
					lva.fullString = `${lva.stg_kurzbz} - ${lva.lv_semester}: ${lva.lv_bezeichnung}`
				})

				this.selectedLehrveranstaltung = this.lehrveranstaltungen.find(lva => lva.lehrveranstaltung_id == this.lv_id)
			}).then(()=>{
				// reload data
				this.loadNoten(this.lv_id, e.value.studiensemester_kurzbz)
			})

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
			const year = this.selectedPruefungDate.getFullYear();
			const month = String(this.selectedPruefungDate.getMonth() + 1).padStart(2, '0'); // Months are 0-based
			const day = String(this.selectedPruefungDate.getDate()).padStart(2, '0');
			const dateStr = `${year}-${month}-${day}`;
			
			// TODO: test this hypothesis
			// first pruefung is always "Termin2" since normal note counts as Termin1
			const pOffset = this.pruefung === null && this.pruefungStudent.pruefungen.length === 0 ? 2 : 1
			const typ = this.pruefung ? this.pruefung.pruefungstyp_kurzbz : ('Termin'+(this.pruefungStudent.pruefungen.length + pOffset)) 
			
			this.$api.call(ApiNoten.saveStudentPruefung(
				this.pruefungStudent.uid,
				this.selectedPruefungNote.note,
				this.pruefung?.punkte ?? '',
				dateStr,
				this.lv_id,
				this.pruefungStudent.lehreinheit_id,
				this.sem_kurzbz,
				typ
			)).then(res => {
				if(res.meta.status === 'success') {
					const s = this.studenten.find(s => s.uid === res.data[1]?.student_uid)
					console.log('old student freigabedatum', s.freigabedatum)
					console.log('old student benotungsdatum', s.benotungsdatum)
					
					s.freigabedatum = this.parseDate(res.data[1]?.['freigabedatum'])
					s.benotungsdatum = this.parseDate(res.data[1]?.['benotungsdatum'])

					console.log('new student freigabedatum', s.freigabedatum)
					console.log('new student benotungsdatum', s.benotungsdatum)
					s.freigegeben = this.checkFreigabe(s.freigabedatum, s.benotungsdatum, s.uid);
					
					// todo: update student subject grade/lv_note
					s.lv_note = res.data[1]?.note
					
					// find the exact student pruefung and update that
					
					// add new pruefung to row
					if(res.data[0]?.new) {			
						// TODO: if new pruefung was of typ "Termin2", "Termin1" shadow pruefung is not available
						this.handleAddNewTermin(res.data, s)
					} else { // update existing
						const oldIndex = s.pruefungen.findIndex(p => p.pruefung_id == res.data[0]?.pruefung_id)
						if(oldIndex !== -1) {
							s.pruefungen.splice(oldIndex, 1, res.data[0])
							s['pruefungNr'+oldIndex] = res.data[0]
						}
					}
					
					// // TODO: manual row update!
					// const row = this.$refs.notenTable.tabulator.getRow(s.uid)
					// row.update()

					this.$refs.notenTable.tabulator.redraw(true)
					
					this.$fhcAlert.alertSuccess('Prüfung gespeichert') //  TODO: phrase
				}
			})
			
			this.$refs.modalContainerPruefung.hide()
		},
		handleAddNewTermin(data, student){
			
		},
		saveNoteneingabe() {
			this.$api.call(ApiNoten.saveStudentenNoten(this.password, this.changedNoten, this.lv_id, this.sem_kurzbz))
				.then((res) => {
				if(res.meta.status === 'success') {
					this.$fhcAlert.alertSuccess('Noten gespeichert')
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
		handleChangePruefungDatum(e) {
			// console.log('handleChangePruefungDatum', e)
		},
		handleChangePruefungNote(e) {
			// console.log(e)
		},
		getOptionLabelNotePruefung(option) {
			return option.bezeichnung
		},
		leChanged(e) {
			this.selectedLehreinheit = e.value
		},
		addPruefung(){
			// TODO: save new pruefungs entry for all selected students on selected date with default note "noch nicht eingetragen" aka 9
		}
	},
	watch: {
		selectedUids(newVal, oldVal) {
			const table = this.$refs.notenTable?.tabulator

			if (!table) return;
			console.log('selectedUids watcher newVal',newVal)
			// Get all current rows
			const allRows = table.getRows();
			console.log('table allRows',allRows)
			// allRows.forEach(row => {
			// 	const rowData = row.getData();
			//
			// 	if (newVal.includes(rowData.uid)) {
			// 		row.select(); // ensure row is selected
			// 	} else {
			// 		row.deselect(); // ensure row is deselected
			// 	}
			// });
		},
		selectedLehreinheit(newVal) {
			if(!this.$refs.notenTable) return
			this.$refs.notenTable.tabulator.clearFilter();
			if(newVal) this.$refs.notenTable.tabulator.setFilter("lehreinheit_id", "=", newVal.lehreinheit_id);
		},
		getKommPruefCount(newVal) {
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
		getStudentenOptions() {
			return this.studenten ? this.studenten : []
		},
		getKommPruefCount(){
			let counter = 0
			this.studenten?.forEach(s => {if(s['kommPruef']){counter++}})	
			return counter
		},
		getSaveBtnClass() {
			// return "btn btn-primary ml-2"
			return !this.changedNoten?.length ? "btn btn-primary ml-2" : "btn btn-secondary ml-2"
		},
		getNewBtnClass() {
			return "btn btn-primary ml-2"
			// return !this.changedData.length ? "btn btn-secondary ml-2" : "btn btn-primary ml-2"
		},
		changedNoten() {
			const v = this.changedNotenCounter // hack to trigger computed
			const cs = this.studenten ? this.studenten.reduce((acc, cur) => {
				const teilnote = this.teilnoten[cur.uid]
				if(teilnote.note_lv && (cur.benotungsdatum > cur.freigabedatum)) {
					acc.push(cur)
				}
				return acc
			}, []) : []
			return cs
		}
	},
	created() {
		this.setupCreated()
	},
	mounted() {
		this.setupMounted()
	},
	template: `

		<bs-modal ref="modalContainerNeuesPruefungsdatum" class="bootstrap-prompt" dialogClass="modal-lg">
			<template v-slot:title>{{$p.t('benotungstool/c4addNewPruefung')}}</template>
			<template v-slot:default>
				<div class="row justify-content-center">
					<div class="col-auto">
						<div class="col-1 text-center">{{$p.t('benotungstool/c4date')}}:</div>
						<div class="col-6">
							<datepicker
								v-model="selectedPruefungDate"
								@update:model-value="handleChangePruefungDatum"
								:clearable="false"
								:time-picker="false"
								:text-input="true"
								:auto-apply="true">
							</datepicker>
						</div>
					</div>
				</div>
				
				<div class="row justify-content-center">
					<div class="col-auto">
						<div class="col-1 text-center">{{$p.t('benotungstool/prueflingSelection')}}:</div>
						<div class="col-6">
							<Multiselect 
								v-model="selectedUids" 
								:options="getStudentenOptions" 
								optionLabel="infoString" 
								placeholder="Studenten auswählen"
								:maxSelectedLabels="3"
								showToggleAll
								class="w-full md:w-20rem" />
						</div>
					</div>
				</div>
				
			</template>
			<template v-slot:footer>
				<button type="button" class="btn btn-primary" @click="addPruefung">{{ $p.t('benotungstool/c4addNewPruefung') }}</button>
			</template>
		 </bs-modal>

		 <bs-modal ref="modalContainerNotenSpeichern" class="bootstrap-prompt" dialogClass="modal-lg">
			<template v-slot:title>{{ $p.t('benotungstool/noteneingabeSpeichern') }}</template>
			<template v-slot:default>
				<div class="row justify-content-center">
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
			<template v-slot:title>{{ $p.t('benotungstool/createPruefungFor') }} {{pruefungStudent?.vorname}} {{pruefungStudent?.nachname}}</template>
			<template v-slot:default>
				<div class="row justify-content-center">
					<div class="col-1 text-center">{{$p.t('benotungstool/c4date')}}:</div>
					<div class="col-6">
						<datepicker
							v-model="selectedPruefungDate"
							@update:model-value="handleChangePruefungDatum"
							:clearable="false"
							:time-picker="false"
							:text-input="true"
							:auto-apply="true">
						</datepicker>
					</div>
				
				</div>
				<div class="row justify-content-center mt-4">
					<div class="col-1 text-center">{{$p.t('lehre/note')}}:</div>
					<div class="col-6">
						<Dropdown @change="handleChangePruefungNote" :placeholder="$p.t('lehre/note')" 
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
				<button type="button" class="btn btn-primary" @click="savePruefungEingabe">{{ $p.t('global/speichern') }}</button>
			</template>
		 </bs-modal>

		<div class="row">
			<div class="col-4">
				<h2>{{$p.t('benotungstool/benotungstoolTitle')}}</h2>
				<h4>{{ lv?.bezeichnung }}</h4>
			</div>
			<div class="col-2">
				<div class="col-lg-auto">
					<Dropdown @change="lvChanged" :style="{'width': '100%'}" :optionLabel="getOptionLabelLv" 
						v-model="selectedLehrveranstaltung" :options="lehrveranstaltungen">
						<template #optionsgroup="slotProps">
							<div> {{ option.fullString }} </div>
						</template>
					</Dropdown>
				</div>
			</div>
			
			<div class="col-2">
				<div class="col-lg-auto">
					<Dropdown @change="leChanged" :style="{'width': '100%'}" :optionLabel="getOptionLabelLe" 
						v-model="selectedLehreinheit" :options="lehreinheiten" showClear>
						<template #optionsgroup="slotProps">
							<div> {{ option.infoString }} </div>
						</template>
					</Dropdown>
				</div>
			</div>
			
			<div class="col-2">
				<div class="col-lg-auto">
					<Dropdown @change="ssChanged" :style="{'width': '100%'}" :optionLabel="getOptionLabel" 
						v-model="selectedSemester" :options="studiensemester">
						<template #optionsgroup="slotProps">
							<div> {{ option.studiensemester_kurzbz }} </div>
						</template>
					</Dropdown>
				</div>
			</div>
		</div>
		<hr>
		
			
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
				<button @click="openSaveModal" role="button" :class="getSaveBtnClass">
					{{$p.t('benotungstool/approveGrades')}} <i class="fa fa-save"></i>
				</button>
				<button @click="openNewPruefungsdatumModal" role="button" :class="getNewBtnClass">
					{{$p.t('benotungstool/c4addNewPruefung')}} <i class="fa fa-plus"></i>
				</button>
			 </template>
		 </core-filter-cmpt>

    `,
};

export default Benotungstool;