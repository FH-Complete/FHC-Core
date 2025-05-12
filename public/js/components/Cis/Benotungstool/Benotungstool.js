import {CoreFilterCmpt} from "../../filter/Filter.js";
import ApiLehre from "../../../api/factory/lehre.js";
import ApiNoten from "../../../api/factory/noten.js";
import ApiStudiensemester from "../../../api/factory/studiensemester.js";
import BsModal from '../../Bootstrap/Modal.js';

export const Benotungstool = {
	name: "Benotungstool",
	components: {
		BsModal,
		CoreFilterCmpt,
		Dropdown: primevue.dropdown,
		Password: primevue.password
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
			password: '',
			tabulatorUuid: Vue.ref(0),
			domain: '',
			lv: null,
			studenten: null,
			pruefungen: null,
			studiensemester: null,
			selectedSemester: null,
			lehrveranstaltungen: null,
			selectedLehrveranstaltung: null,
			tableBuiltResolve: null,
			notenOptions: null,
			tableBuiltPromise: null,
			notenTableOptions: {
				height: 700,
				index: 'student_uid',
				layout: 'fitDataStretch',
				placeholder: this.$p.t('global/noDataAvailable'),
				columns: [
					{title: Vue.computed(() => this.$p.t('benotungstool/c4mail')), field: 'email', formatter: this.mailFormatter, tooltip: false, widthGrow: 1},
					{title: 'UID', field: 'uid', tooltip: false, widthGrow: 1},
					{title: Vue.computed(() => this.$p.t('benotungstool/c4vorname')), field: 'vorname',  tooltip: false, widthGrow: 1},
					{title: Vue.computed(() => this.$p.t('benotungstool/c4nachname')), field: 'nachname', widthGrow: 1},
					{title: Vue.computed(() => this.$p.t('benotungstool/c4teilnoten')), field: 'teilnote', widthGrow: 1},
					{title: Vue.computed(() => this.$p.t('benotungstool/c4note')), field: 'note_vorschlag',
						editor: 'list',
						editorParams: {
							values: Vue.computed(()=>this.notenOptions.map(opt => {
								return {
									label: opt.bezeichnung,
									value: opt.note
								}
							}))
						},
						formatter: (cell) => {
							const value = cell.getValue()
							const match = this.notenOptions.find(opt => opt.note === value)
							return match ? match.bezeichnung : value
						},
						widthGrow: 1},
					{title: '', width: 50, hozAlign: 'center', formatter: this.arrowFormatter, cellClick: this.saveNote},
					{title: Vue.computed(() => this.$p.t('benotungstool/c4lvnote')), field: 'lv_note', 
						formatter: this.notenFormatter,
						widthGrow: 1},
					{title: Vue.computed(() => this.$p.t('benotungstool/c4freigabe')), field: 'freigegeben', widthGrow: 1},
					{title: Vue.computed(() => this.$p.t('benotungstool/c4zeugnisnote')), field: 'note', formatter: this.notenFormatter, widthGrow: 1}
					// {title: Vue.computed(() => this.$p.t('benotungstool/c4termin1')), field: 'termin1', widthGrow: 1},
					// {title: Vue.computed(() => this.$p.t('benotungstool/c4termin2')), field: 'termin2', widthGrow: 1},
					// {title: Vue.computed(() => this.$p.t('benotungstool/c4termin3')), field: 'termin3', widthGrow: 1}
				],
				persistence: false,
			},
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
				}
			]};
	},
	methods: {
		notenFormatter(cell) {
			const value = cell.getValue()
			const match = this.notenOptions.find(opt => opt.note === value)
			return match ? match.bezeichnung : value
		},
		handlePasswordChanged(pw) {
			console.log('pw:', pw)	
		},
		saveNote(e, cell) {
			const row = cell.getRow()
			const data = row.getData()

			row.update({ note: data.note_vorschlag })
		},
		arrowFormatter(cell) {
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
		setupData(data){
			this.studenten = data[0]
			this.pruefungen = data[1]
			this.domain = data[2]
			this.teilnoten = data[3]
			
			this.pruefungen.forEach(p => {
				const student = this.studenten.find(s => s.uid === p.student_uid)
				
				if(!student) return
				// TODO: fetch typen and remove hardcoded strings
				// TODO: dynamic amount of termin columns!
				// if(p.pruefungstyp_kurzbz == "Termin1") {
				// 	student.termin1 = p
				// } else if (p.pruefungstyp_kurzbz == "Termin2") {
				// 	student.termin2 = p
				// } else if (p.pruefungstyp_kurzbz == "Termin3") {
				// 	student.termin3 = p
				// }
				
				// TODO: LE TEILNOTEN IN BACKEND LADEN, BERECHNEN UND VORSCHLAG PASTEN
				// if(p.negativ)
			})
			
			this.studenten.forEach(s => {
				s.email = this.buildMailToLink(s)

				const grades = this.teilnoten[s.uid].grades
				s.teilnote = ''
				grades.forEach(g => s.teilnote += g.text + ' ')
			})

			this.$refs.notenTable.tabulator.clearSort()
			this.$refs.notenTable.tabulator.setColumns(this.notenTableOptions.columns)
			this.$refs.notenTable.tabulator.setData(this.studenten);
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
		async setupCreated() {
			// fetch lva dropdown
			this.$api.call(ApiLehre.getZugewieseneLv(this.viewData?.uid, this.sem_kurzbz)).then(res => {
				console.log(res)
				this.lehrveranstaltungen = res.data
				
				// build dropdown option string
				this.lehrveranstaltungen.forEach(lva => {
					lva.fullString = `${lva.stg_kurzbz} - ${lva.lv_semester}: ${lva.lv_bezeichnung}`
				})
				
				this.selectedLehrveranstaltung = this.lehrveranstaltungen.find(lva => lva.lehrveranstaltung_id == this.lv_id)
			})
			
			//fetch sem_kurzbz dropdown
			this.$api.call(ApiStudiensemester.getStudiensemester()).then(res => {
				this.studiensemester = res.data[0]
				this.selectedSemester = this.studiensemester.find(sem => sem.studiensemester_kurzbz === this.sem_kurzbz)
			})
			
			// fetch noten dropdown
			await this.$api.call(ApiNoten.getNoten()).then(res => {
				this.notenOptions = res.data
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
			
			// reload data
			this.loadNoten(this.lv_id, e.value.studiensemester_kurzbz)

		},
		getOptionLabel(option) {
			return option.studiensemester_kurzbz
		},
		getOptionLabelLv(option) {
			return option.fullString
		},
		saveNoteneingabe() {
			
			this.$api.call(ApiNoten.saveStudentenNoten(this.password))
			
			this.$refs.modalContainerNotenSpeichern.hide()
		},
		openSaveModal() {
			this.$refs.modalContainerNotenSpeichern.show()
		}
	},
	watch: {
		
	},
	computed: {
		getSaveBtnClass() {
			return "btn btn-primary ml-2"
			// return !this.changedData.length ? "btn btn-secondary ml-2" : "btn btn-primary ml-2"
		}
	},
	created() {
		this.setupCreated()
	},
	mounted() {
		this.setupMounted()
	},
	template: `
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
				<button type="button" class="btn btn-primary" @click="saveNoteneingabe">{{ $p.t('global/noteneingabeBestätigen') }}</button>
			</template>
		 </bs-modal>

		<div class="row">
			<div class="col-8">
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
					<i class="fa fa-save"></i>
				</button>
			 </template>
		 </core-filter-cmpt>

    `,
};

export default Benotungstool;