import BsModal from '../../Bootstrap/Modal.js';
import VueDatePicker from '../../vueDatepicker.js.php';
import ApiAbgabe from '../../../api/factory/abgabe.js'

const today = new Date()
export const AbgabeMitarbeiterDetail = {
	name: "AbgabeMitarbeiterDetail",
	components: {
		BsModal,
		InputNumber: primevue.inputnumber,
		Checkbox: primevue.checkbox,
		Dropdown: primevue.dropdown,
		Textarea: primevue.textarea,
		SpeedDial: primevue.speeddial,
		Accordion: primevue.accordion,
		AccordionTab: primevue.accordiontab,
		Message: primevue.message,
		VueDatePicker
	},
	inject: [
		'abgabeTypeOptions',
		'abgabetypenBetreuer',
		'allowedNotenOptions',
		'turnitin_link',
		'old_abgabe_beurteilung_link',
		'isMobile'
	],
	props: {
		projektarbeit: {
			type: Object,
			default: null
		},
		isFullscreen: {
			type: Boolean,
			default: false
		},
		assistenzMode: {
			type: Boolean,
			default: false
		}
	},
	data() {
		return {
			activeIndexArray: null,
			showAutomagicModalPhrase: false,
			eidAkzeptiert: false,
			enduploadTermin: null,
			allActiveLanguages: FHC_JS_DATA_STORAGE_OBJECT.server_languages,
			speedDialItems: [{
				label: Vue.computed(() => this.$p.t('abgabetool/c4newAbgabetermin')),
				icon: "fa fa-plus",
				command: this.openCreateNewAbgabeModal
			},
			{
				label: Vue.computed(() => this.$p.t('abgabetool/c4benoten')),
				icon: "fa fa-user-check",
				command: this.openBenotung
			},
			{
				label: Vue.computed(() => this.$p.t('abgabetool/c4plagiatcheck_link')),
				icon: "fa fa-clipboard-check",
				command: this.openPlagiatcheck
			},
			{
				label: Vue.computed(() => this.$p.t('abgabetool/c4student_perspective')),
				icon: "fa fa-eye",
				command: this.openStudentPage
			}],
			newTermin: null,
			form: Vue.reactive({
				sprache: '',
				abstract: '',
				abstract_en: '',
				schlagwoerter: '',
				schlagwoerter_en: '',
				kontrollschlagwoerter: '',
				seitenanzahl: 1,
			})
		}
	},
	methods: {
		getActiveIndexTabArray(additional = []) {
			// here we try to assume which abgabetermine are the most relevant to the current user

			// lets try to take the termin with nearest date
			let closestIndex = -1;
			let minDiff = Infinity;
			const today = new Date();


			this.projektarbeit.abgabetermine.forEach((obj, i) => {
				const diff = Math.abs(new Date(obj.datum) - today);
				if (diff < minDiff) {
					minDiff = diff;
					closestIndex = i;
				}
			});

			return [closestIndex, ...additional]
		},
		getPlaceholderTermin(termin) {
			return termin?.bezeichnung?.bezeichnung ?? this.$p.t('abgabetool/abgabetypPlaceholder')	
		},
		saveTermin(termin) {
			const paabgabe_id = termin.paabgabe_id
			termin.note_pk = termin.note?.note ?? null
			termin.betreuer_person_id = this.projektarbeit.betreuer_person_id
			
			// phrasentext 'no late submission allowed' to 'late submission allowed' + boolean UI invert
			termin.fixtermin = !termin.invertedFixtermin
			return this.$api.call(ApiAbgabe.postProjektarbeitAbgabe(termin)).then( (res) => {
				if(res?.meta?.status == 'success') {
					this.$fhcAlert.alertSuccess(this.$p.t('ui/gespeichert'))
					
					const noteOpt = this.allowedNotenOptions.find(opt => opt.note == res.data[0].note)
					const newTerminRes = {
						'allowedToSave': true,
						'allowedToDelete': true,
						...res.data[0]
					}
					if(newTerminRes.note) newTerminRes.note = noteOpt
					newTerminRes.invertedFixtermin = !newTerminRes.fixtermin
					const existingTerminRes = res.data[1]
					
					const abgabeOpt = this.abgabeTypeOptions.find(opt => opt.paabgabetyp_kurzbz == newTerminRes.paabgabetyp_kurzbz)
					
					newTerminRes.bezeichnung = {
						bezeichnung: termin.bezeichnung?.bezeichnung,
						paabgabetyp_kurzbz: termin.bezeichnung?.paabgabetyp_kurzbz,
						benotbar: abgabeOpt.benotbar
					}
					
					
					
					// only insert new abgabe if we actually created a new one, not when saving/editing existing
					if(!existingTerminRes){
						this.projektarbeit.abgabetermine.push(newTerminRes)
					} else {
						const noteOptExisting = this.allowedNotenOptions.find(opt => opt.note == existingTerminRes.note)
						existingTerminRes.note = noteOptExisting
					}
					
					this.projektarbeit.abgabetermine.sort((a, b) =>new Date(a.datum) - new Date(b.datum))
					
					const index = this.projektarbeit.abgabetermine.findIndex(t => termin.paabgabe_id == t.paabgabe_id)
					this.activeIndexArray = this.getActiveIndexTabArray([index])
					
					// negative abgabe -> automagically open new termin modal
					// really bad feature imo that will be annoying to deal with
					
					// termin is completely new and has negative note
					const savedNewWithNegative = !existingTerminRes && !newTerminRes.note?.positiv && newTerminRes.note !== null

					// termin existed previously + oldTermin had different note/positive note or no note at all
					const savedExistingNoteAsNegativeAndWasNotNegativeBefore = existingTerminRes && !newTerminRes.note?.positiv && newTerminRes.note !== null && (existingTerminRes.note?.positiv || existingTerminRes.note === undefined)

					const openModalDueToNegativeBeurteilung = savedNewWithNegative || savedExistingNoteAsNegativeAndWasNotNegativeBefore
					if(openModalDueToNegativeBeurteilung) {
						this.newTermin = {
							'paabgabe_id': -1,
							'projektarbeit_id': this.projektarbeit.projektarbeit_id,
							'fixtermin': false,
							'invertedFixtermin': true,
							'kurzbz': '', // todo kurzbz textfield value vorschlag für qualgates
							'datum': new Date().toISOString().split('T')[0],
							'note': this.allowedNotenOptions.find(opt => opt.note == 9),
							'beurteilungsnotiz': '',
							'upload_allowed': false,
							'paabgabetyp_kurzbz': '',
							'bezeichnung': this.abgabeTypeOptions.find(opt => opt.paabgabetyp_kurzbz === newTerminRes.paabgabetyp_kurzbz),
							'abgabedatum': null,
							'insertvon': this.viewData?.uid ?? ''
						}
						
						this.showAutomagicModalPhrase = true

						this.$refs.modalContainerCreateNewAbgabe.show()
					} else {
						this.showAutomagicModalPhrase = false	
					}
				} else if(res?.meta?.status == 'error'){
					this.$fhcAlert.alertError()
				}
			})
		},
		openZusatzdatenModal() {
			this.$refs.modalContainerZusatzdaten.show()
		},
		async saveZusatzdaten(){
			if (!await this.validateZusatzdaten())
			{
				return false;
			}
			
			const pa = this.projektarbeit

			// post endabgabe
			const formData = new FormData();
			formData.append('projektarbeit_id', this.projektarbeit.projektarbeit_id);

			formData.append('sprache', this.form['sprache'].sprache)
			formData.append('abstract', this.form['abstract'])
			formData.append('abstract_en', this.form['abstract_en'])
			formData.append('schlagwoerter', this.form['schlagwoerter'])
			formData.append('schlagwoerter_en', this.form['schlagwoerter_en'])
			formData.append('seitenanzahl', this.form['seitenanzahl'])
			
			this.loading = true
			this.$api.call(ApiAbgabe.postStudentProjektarbeitZusatzdaten(formData))
				.then(res => {
					if(res.meta.status == 'success') {
						this.$fhcAlert.alertSuccess(this.$p.t('ui/gespeichert'))
						if(!data?.retval?.[0]) return
						const paRes = data.retval[0]
						pa.seitenanzahl = paRes.seitenanzahl ?? 1
						pa.kontrollschlagwoerter = paRes.kontrollschlagwoerter ?? ''
						pa.schlagwoerter = paRes.schlagwoerter ?? ''
						pa.sprache = paRes.sprache ?? ''
						pa.schlagwoerter_en = paRes.schlagwoerter_en ?? ''
						pa.abstract = paRes.abstract ?? ''
						pa.abstract_en = paRes.abstract_en ?? ''
					}
					
				}).finally(()=> {
				this.loading = false
			})

			this.$refs.modalContainerZusatzdaten.hide()
		},
		async validateZusatzdaten() {
			// just ask once
			if(await this.$fhcAlert.confirm({
				message: this.$p.t('abgabetool/confirmEnduploadSpeichern'),
				acceptLabel: this.$capitalize(this.$p.t('abgabetool/c4AcceptAndProceed')),
				acceptClass: 'btn btn-danger',
				rejectLabel: this.$capitalize(this.$p.t('abgabetool/c4Cancel')),
				rejectClass: 'btn btn-outline-secondary'
			}) === false) {
				return false
			}
			
			return true
		},
		async handleDeleteTermin(termin){
			if(await this.$fhcAlert.confirm({
				message: this.$p.t('abgabetool/c4confirm_delete'),
				acceptLabel: 'Löschen',
				acceptClass: 'btn btn-danger',
				rejectLabel: 'Zurück',
				rejectClass: 'btn btn-outline-secondary'
			}) === false) {
				return false
			} else {
				this.deleteTermin(termin)
			}
		},
		deleteTermin(termin) {
			this.$api.call(ApiAbgabe.deleteProjektarbeitAbgabe(termin.paabgabe_id)).then( (res) => {
				if(res?.meta?.status == 'success') {
					this.$fhcAlert.alertSuccess(this.$p.t('ui/genericDeleted', [this.$p.t('abgabetool/abgabe')]))
					// this.$p.t('global/tooltipLektorDeleteKontrolle', [this.$entryParams.permissions.kontrolleDeleteMaxReach ])
					const deletedTerminIndex = this.projektarbeit.abgabetermine.findIndex(t => t.paabgabe_id === termin.paabgabe_id)
					this.projektarbeit.abgabetermine.splice(deletedTerminIndex, 1)
				} else if(res?.meta?.status == 'error'){
					this.$fhcAlert.alertError()
				}
			})
		},
		validate: function(termin) {
			if(!termin.file.length) {
				this.$fhcAlert.alertWarning(this.$p.t('global/warningChooseFile'));
				return false
			}

			return true;
		},
		downloadAbgabe(termin) {
			const url = `/api/frontend/v1/Abgabe/getStudentProjektarbeitAbgabeFile?paabgabe_id=${termin.paabgabe_id}&student_uid=${this.projektarbeit.student_uid}`;

			window.open(FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + url)
			// this.$api.call(ApiAbgabe.getStudentProjektarbeitAbgabeFile(termin.paabgabe_id, this.projektarbeit.student_uid))
		},
		convertDateToIsoString(date) {
			// 1. Check if it is a Date object AND if the date value is valid (not 'Invalid Date')
			if (param instanceof Date && !isNaN(param.getTime())) {
				const year = param.getFullYear();
				// getMonth() is 0-indexed, so we add 1.
				const month = param.getMonth() + 1;
				const day = param.getDate();
		
				// Helper to pad single-digit numbers with a leading zero
				const pad = (num) => String(num).padStart(2, '0');
		
				// Return the formatted string: YYYY-MM-DD
				return `${year}-${pad(month)}-${pad(day)}`;
			}
		
			// If it's not a valid Date, return the original parameter
			return param;
		},
		dateDiffInDays(datumParam){
			let datum = datumParam
			if(datumParam instanceof Date && !isNaN(datum.getTime()))
			{
				const year = datumParam.getFullYear();
				const month = datumParam.getMonth() + 1;	// getMonth() is 0-indexed
				const day = datumParam.getDate();
				const pad = (num) => String(num).padStart(2, '0');
				datum = `${year}-${pad(month)}-${pad(day)}`	
			}
			
			const dateToday = luxon.DateTime.now().startOf('day');
			const dateDatum = luxon.DateTime.fromISO(datum).startOf('day');
			const duration = dateDatum.diff(dateToday, 'days');
			
			return duration.values.days;
		},
		getDateStyleClass(termin) {
			const datum = new Date(termin.datum)
			const abgabedatum = new Date(termin.abgabedatum)

			termin.diffindays = this.dateDiffInDays(termin.datum)
			
			if(today > datum && termin.benotbar && !termin.note) return 'beurteilungerforderlich'
			if (termin.abgabedatum === null && termin.upload_allowed) {
				if(datum < today) {
					return 'verpasst' // needs upload, missed it and has not submitted anything 
				} else if (datum > today && termin.diffindays <= 12) {
					return 'abzugeben' // needs to upload soon
				} else {
					return 'standard' // upload in distant future
				}
			}
			else if(abgabedatum > datum) {
				return 'verspaetet' // needs upload, missed it and has submitted smth late
			} else if(!termin.upload_allowed) {
				if(datum > today) return termin.diffinday <= 12 ? 'abzugeben' : 'standard'
				else if (today > datum) return 'abgegeben'
			} else {
				return 'abgegeben' // nothing else to do for that termin
			}
		},
		openBeurteilungLink(link) {
			window.open(link, '_blank')
		},
		getOptionLabelSprache(option) {
			return option.sprache
		},
		getOptionLabelAbgabetyp(option){
			return option.bezeichnung
		},
		getOptionDisabled(option) {
			return !option.aktiv
		},
		getNotenOptionLabel(option) {
			return option.bezeichnung
		},
		openStudentPage() {
			const link = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router
				+ '/Cis/Abgabetool/Student/' + this.projektarbeit?.student_uid
			window.open(link, '_blank')
		},
		openPlagiatcheck() {
			const link = this.turnitin_link
			window.open(link, '_blank')
		},
		openBenotung() {
			// old link check ?
			
			if(this.getSemesterBenotbar && this.projektarbeit?.abgabetermine.find(termin => termin.paabgabetyp_kurzbz == 'end' && termin.abgabedatum !== null)) {
				// TODO: shouldnt be hardcoded here, at least config in abgabetool -> ideally event sourced from projektarbeitsbeurteilung
				
				const path = this.projektarbeit?.betreuerart_kurzbz == 'Zweitbegutachter' ? 'ProjektarbeitsbeurteilungZweitbegutachter' : 'ProjektarbeitsbeurteilungErstbegutachter'
				const link = FHC_JS_DATA_STORAGE_OBJECT.app_root + 'index.ci.php/extensions/FHC-Core-Projektarbeitsbeurteilung/' + path
				window.open(link, '_blank')
			} else {
				window.open(this.old_abgabe_beurteilung_link, '_blank')
			}
		},
		formatDate(dateParam) {
			const date = new Date(dateParam)
			// handle missing leading 0
			const padZero = (num) => String(num).padStart(2, '0');

			const month = padZero(date.getMonth() + 1); // Months are zero-based
			const day = padZero(date.getDate());
			const year = date.getFullYear();
			
			return `${day}.${month}.${year}`
		},
		getAccTabHeaderForTermin(termin) {
			let tabTitle = ''
			
			const datumFormatted = this.formatDate(termin.datum)
			tabTitle += termin.bezeichnung?.bezeichnung + ' ' + datumFormatted

			return tabTitle
		},
		openCreateNewAbgabeModal() {
			if(!this.newTermin) {
				const typ = this.abgabeTypeOptions.find(opt => opt.paabgabetyp_kurzbz === 'zwischen')
				this.newTermin = {
					'paabgabe_id': -1,
					'projektarbeit_id': this.projektarbeit.projektarbeit_id,
					'fixtermin': false,
					'invertedFixtermin': true,
					'kurzbz': '',
					'datum': new Date().toISOString().split('T')[0],
					'note': this.allowedNotenOptions.find(opt => opt.note == 9),
					'beurteilungsnotiz': '',
					'upload_allowed': typ.upload_allowed_default,
					'paabgabetyp_kurzbz': '',
					'bezeichnung': typ,
					'abgabedatum': null,
					'insertvon': this.viewData?.uid ?? ''
				}
			}
			console.log(this.$refs.modalContainerCreateNewAbgabe)
			this.$refs.modalContainerCreateNewAbgabe.show()
		},
		validateTermin(termin) {
			// compare new termin to existing ones to block illegal termin constellations, if they exist
			
			return true
		},
		async handleSaveNewAbgabe(termin) {
			
			if(!this.validateTermin(termin)) {
				this.$fhcAlert.alertWarning('invalid termin')
				
				return false
			}
			
			await this.saveTermin(termin)
			
			this.$refs.modalContainerCreateNewAbgabe.hide()
			this.newTermin = {
				'paabgabe_id': -1,
				'projektarbeit_id': this.projektarbeit.projektarbeit_id,
				'fixtermin': false,
				'invertedFixtermin': true,
				'kurzbz': '',
				'datum': new Date().toISOString().split('T')[0],
				'note': this.allowedNotenOptions.find(opt => opt.note == 9),
				'beurteilungsnotiz': '',
				'upload_allowed': false,
				'paabgabetyp_kurzbz': '',
				'bezeichnung': this.abgabeTypeOptions.find(opt => opt.paabgabetyp_kurzbz === 'zwischen'),
				'abgabedatum': null,
				'insertvon': this.viewData?.uid ?? ''
			}
			
		},
		handleChangeAbgabetyp(termin) {
			// if paabgabetype qualgate is selected, fill out kurzbz textfield with bezeichnung of quality gate so users
			// are possibly less confused, which is a pursuit in vain
			if(termin.bezeichnung?.paabgabetyp_kurzbz === 'qualgate1' || termin.bezeichnung?.paabgabetyp_kurzbz === 'qualgate2') {
				termin.kurzbz = termin.bezeichnung.bezeichnung
			}
		}
			
	},
	computed: {
		allowedToSaveZusatzdaten() {
				return this.form.schlagwoerter.length > 0 && this.form.schlagwoerter_en.length > 0 && this.form.abstract.length > 0 && this.form.abstract_en.length > 0 && this.form.seitenanzahl > 0
		},
		getAllowedAbgabeTypeOptions() {
			if(this.assistenzMode) {
				return this.abgabeTypeOptions
			} else {
				return this.abgabeTypeOptions.filter(opt => this.abgabetypenBetreuer.includes(opt.bezeichnung))
			}
		},
		getMessagePtStyle() {
			// adjust outer spacing and internal padding to appear similar to doenload button in size
			return {
				root: {
					style: {
						margin: '0px'
					}
				},
				wrapper: {
					style: {
						padding: '6px'
					}
				}
			}	
		},
		getEid() {
			return this.$p.t('abgabetool/c4eidesstattlicheErklaerung')
		},
		getEnduploadErlaubt() {
			return !this.eidAkzeptiert
		},
		getSemesterBenotbar(){
			return this.projektarbeit?.isCurrent ?? false
		},
		endUploadVorhanden(){
			return this.projektarbeit?.abgabetermine.find(abgabe => abgabe.paabgabetyp_kurzbz === 'end' && abgabe.abgabedatum !== null)
		},
		qualityGateTerminAvailable() {
			let qgatefound = false
			this.projektarbeit?.abgabetermine.forEach(abgabe => {
				if(abgabe.bezeichnung?.paabgabetyp_kurzbz == 'qualgate1'
					|| abgabe?.bezeichnung?.paabgabetyp_kurzbz == 'qualgate2') {
					qgatefound = true
				}
			})
			return qgatefound
		},
		getSpeedDialStyle() {
			return 'position: static !important;'
		},
		getSpeedDialWrapperStyle() {
			// non fullscreen -> wrapper is positioned on right bottom corner of modal, wherever that is
			return 'position: absolute; z-index: 9999; left: 0px; bottom: 0.5rem;'
		},
		getTooltipVerspaetet() {
			return {
				value: this.$p.t('abgabetool/c4tooltipVerspaetet'),
				class: "custom-tooltip"
			}
		},
		getTooltipVerpasst() {
			return {
				value: this.$p.t('abgabetool/c4tooltipVerpasst'),
				class: "custom-tooltip"
			}
		},
		getTooltipAbzugeben() {
			return {
				value: this.$p.t('abgabetool/c4tooltipAbzugeben'),
				class: "custom-tooltip"
			}
		},
		getTooltipStandard() {
			return {
				value: this.$p.t('abgabetool/c4tooltipStandard'),
				class: "custom-tooltip"
			}
		},
		getTooltipBeurteilungerforderlich() {
			return {
				value: this.$p.t('abgabetool/c4tooltipBeurteilungerfolderlich'),
				class: "custom-tooltip"
			}
		},
		getTooltipAbgegeben() {
			return {
				value: this.$p.t('abgabetool/c4tooltipAbgegeben'),
				class: "custom-tooltip"
			}
		},
		getTooltipFixtermin() {
			return {
				value: this.$p.t('abgabetool/c4tooltipFixtermin'),
				class: "custom-tooltip"
			}
		},
		getTooltipAbgabeDetected() {
			return {
				value: this.$capitalize(this.$p.t('abgabetool/c4tooltipAbgabeDetected')),
				class: "custom-tooltip"
			}
		},
		getTooltipNotAllowedToSave() {
			return {
				value: this.$p.t('abgabetool/c4notAllowedToEditAbgabeTermin'),
				class: "custom-tooltip"
			}
		},
		getTooltipNotAllowedToDelete() {
			return {
				value: this.$p.t('abgabetool/c4notAllowedToDeleteAbgabeTermin'),
				class: "custom-tooltip"
			}
		},
		getProjektarbeitTitel() {
			if(this.projektarbeit?.titel) return this.projektarbeit.titel
			
			return ''
		},
		getProjektarbeitStudent(){

			if(this.projektarbeit?.student) return this.projektarbeit.student

			return ''
		}
	},
	watch: {
		'newTermin.bezeichnung'(newVal) {
			console.log('\'newTermin.bezeichnung\' watcher', newVal)

			if(newVal?.paabgabetyp_kurzbz === 'qualgate1' || newVal?.paabgabetyp_kurzbz === 'qualgate2') {
				this.newTermin.kurzbz = newVal.bezeichnung
			}
			
			this.newTermin.upload_allowed = newVal.upload_allowed_default
		},
		'projektarbeit'(newVal) {
			// set invertedFixtermin field for UI/UX purposes -> avoid double negation in text
			
			this.activeIndexArray = this.getActiveIndexTabArray()
			
			newVal?.abgabetermine?.forEach(termin => termin.invertedFixtermin = !termin.fixtermin)
			
			// default select german if projektarbeit sprache was null
			this.form.sprache = newVal.sprache ? this.allActiveLanguages.find(lang => lang.sprache == newVal.sprache) : this.allActiveLanguages.find(lang => lang.sprache == 'German')
			this.form.abstract = newVal.abstract ?? ''
			this.form.abstract_en = newVal.abstract_en ?? ''
			this.form.schlagwoerter = newVal.schlagwoerter ?? ''
			this.form.schlagwoerter_en = newVal.schlagwoerter_en ?? ''
			this.form.kontrollschlagwoerter = newVal.kontrollschlagwoerter ?? ''
			this.form.seitenanzahl = newVal.seitenanzahl ?? 1
			
		},
	},
	created() {

	},
	mounted() {

	},
	template: `
	<bs-modal
		id="innerModalNewAbgabe" 
		ref="modalContainerCreateNewAbgabe" 
		class="bootstrap-prompt" 
		dialogClass="bordered-modal modal-lg" 
		:backdrop="true"
		@hideBsModal="console.log('hideBsModal'); showAutomagicModalPhrase=false;"
	>
		<template v-slot:title>
			<div>
				{{ $p.t('abgabetool/c4newAbgabetermin') }}
			</div>
		</template>
		<template v-slot:default>
			<div v-if="showAutomagicModalPhrase" class="text-center"><p>{{$p.t('abgabetool/c4abgabeQualGateNegativAddNewAutomagisch')}}</p></div>
			<div v-if="newTermin">
				<div v-if="assistenzMode" class="row">
					<div class="col-4 col-md-3 fw-bold align-content-center">{{$p.t('abgabetool/c4fixterminv4')}}</div>
					<div class="col-8 col-md-9">
						<Checkbox 
							v-model="newTermin.invertedFixtermin"
							:binary="true" 
							:pt="{ root: { class: 'ml-auto' }}"
						>
						</Checkbox>
					</div>
				</div>
				<div class="row mt-2">
					<div class="col-4 col-md-3 fw-bold align-content-center">{{ $capitalize( $p.t('abgabetool/c4zieldatum') )}}</div>
					<div class="col-8 col-md-9">
						<VueDatePicker
							v-model="newTermin.datum"
							:clearable="false"
							:enable-time-picker="false"
							:format="formatDate"
							:text-input="true"
							auto-apply>
						</VueDatePicker>
					</div>
				</div>
				<div class="row mt-2">
					<div class="col-4 col-md-3 fw-bold align-content-center">{{ $capitalize( $p.t('abgabetool/c4abgabetyp') )}}</div>
					<div class="col-8 col-md-9">
						<Dropdown
							:style="{'width': '100%'}"
							v-model="newTermin.bezeichnung"
							:options="getAllowedAbgabeTypeOptions"
							:optionLabel="getOptionLabelAbgabetyp"
							scrollHeight="300px">
						</Dropdown>
					</div>
				</div>
				<div class="row mt-2">
					<div class="col-4 col-md-3 fw-bold align-content-center">{{ $capitalize( $p.t('abgabetool/c4upload_allowed') )}}</div>
					<div class="col-8 col-md-9">
						<Checkbox
							v-model="newTermin.upload_allowed"
							:binary="true"
							:pt="{ root: { class: 'ml-auto' }}"
						>
						</Checkbox>
					</div>
				</div>
				<div class="row mt-2">
					<div class="col-4 col-md-3 fw-bold align-content-center">{{ $capitalize( $p.t('abgabetool/c4abgabekurzbz') )}}</div>
					<div class="col-8 col-md-9">
						<Textarea style="margin-bottom: 4px;" v-model="newTermin.kurzbz" rows="1" class="w-100"></Textarea>
					</div>
				</div>	
			</div>
		</template>
		<template v-slot:footer>
			<button type="button" class="btn btn-primary" @click="handleSaveNewAbgabe(newTermin)">{{$capitalize( $p.t('abgabetool/c4saveNewAbgabetermin') )}}</button>
		</template>
	</bs-modal>

	<div v-if="projektarbeit">
		<h5>{{$capitalize( $p.t('abgabetool/c4abgabeMitarbeiterbereich') )}}</h5>

		<div class="row">
			<div class="col-6">
				<p> {{getProjektarbeitStudent}}</p>
				<p> {{getProjektarbeitTitel}}</p>
				<p v-if="projektarbeit?.zweitbegutachter"> {{projektarbeit?.zweitbegutachter}}</p>
			</div>
			<div class="col-6 d-flex justify-content-end align-items-start">
				<SpeedDial
					:model="speedDialItems" 
					direction="left"
					:radius="80"
					type="linear"
					buttonClass="p-button-rounded p-button-lg p-button-primary"
					:tooltipOptions="{ position: 'down' }"
				>
					<template #icon>
						<i class="fa-solid fa-bars"></i>
					</template>
				</SpeedDial>
			</div>
		</div>
		<div class="row" style="margin-bottom: 12px;">
			<div class="col-auto">
				<button type="button" class="btn btn-primary" @click="openCreateNewAbgabeModal">
					<i class="fa-solid fa-plus"></i>
					{{$capitalize( $p.t('abgabetool/c4newAbgabetermin') )}}
				</button>
				
			</div>
			<div class="col-auto">
				<button type="button" class="btn btn-primary ml-4" @click="openZusatzdatenModal">
					<i class="fa-solid fa-pen-to-square"></i>
					{{$capitalize( $p.t('abgabetool/c4ZusatzdatenBearbeiten') )}}
				</button>
			</div>
		</div>
		<Accordion :multiple="true" :activeIndex="activeIndexArray">
			<template v-for="termin in this.projektarbeit?.abgabetermine">
				<AccordionTab :headerClass="getDateStyleClass(termin) + '-header'">
					<template #header>
						<div class="d-flex row w-100">
							<div class="col-auto" style="transform: translateX(-62px)">
								<i v-if="getDateStyleClass(termin) == 'verspaetet'" v-tooltip.right="getTooltipVerspaetet" class="fa-solid fa-triangle-exclamation"></i>
								<i v-else-if="getDateStyleClass(termin) == 'verpasst'" v-tooltip.right="getTooltipVerpasst" class="fa-solid fa-calendar-xmark"></i>
								<i v-else-if="getDateStyleClass(termin) == 'abzugeben'" v-tooltip.right="getTooltipAbzugeben" class="fa-solid fa-hourglass-half"></i>
								<i v-else-if="getDateStyleClass(termin) == 'standard'" v-tooltip.right="getTooltipStandard" class="fa-solid fa-clock"></i>
								<i v-else-if="getDateStyleClass(termin) == 'abgegeben'" v-tooltip.right="getTooltipAbgegeben" class="fa-solid fa-check"></i>
								<i v-else-if="getDateStyleClass(termin) == 'beurteilungerforderlich'" v-tooltip.right="getTooltipBeurteilungerforderlich" class="fa-solid fa-list-check"></i>
							</div>
							<div class="col-auto text-start" style="min-width: max(150px, 20%); max-width: min(300px, 30%); transform: translateX(-30px)">
								<span>{{ termin?.bezeichnung?.bezeichnung }}</span>
							</div>
							<div class="col-auto text-start" style="min-width: 100px; transform: translateX(-30px)">
								<span>{{ formatDate(termin.datum) }}</span>
							</div>
							<div v-if="termin?.fixtermin" class="col-auto" style="transform: translateX(-30px)">
								<i  v-tooltip.right="getTooltipFixtermin" class="fa-solid fa-lock"></i>
							</div>
							<div v-if="termin?.abgabedatum" class="col-auto" style="transform: translateX(-30px)">
								<i v-tooltip.right="getTooltipAbgabeDetected" class="fa-solid fa-file"></i>
							</div>
						</div>				
					</template>
					<div class="row mt-2" v-if="assistenzMode">
						<div class="col-12 col-md-3 fw-bold align-content-center">{{$capitalize( $p.t('abgabetool/c4fixterminv4') )}}</div>
						<div class="col-12 col-md-9">
							<Checkbox 
								v-model="termin.invertedFixtermin"
								:binary="true" 
								:pt="{ root: { class: 'ml-auto' }}"
							>
							</Checkbox>
						</div>
					</div>
					<div class="row mt-2">
						<div class="col-12 col-md-3 align-content-center">
							<div class="row fw-bold" style="margin-left: 2px">{{$capitalize( $p.t('abgabetool/c4zieldatum') )}}</div>
							<div class="row fw-light" style="margin-left: 2px">{{$capitalize( $p.t('abgabetool/c4abgabeuntil2359') )}}</div>
						</div>
						<div class="col-12 col-md-9">
							<VueDatePicker
								v-model="termin.datum"
								:clearable="false"
								:disabled="!termin.allowedToSave"
								:enable-time-picker="false"
								:format="formatDate"
								:text-input="true"
								auto-apply>
							</VueDatePicker>
						</div>
					</div>
					<div class="row mt-2">
						<div class="col-12 col-md-3 fw-bold align-content-center">{{$capitalize( $p.t('abgabetool/c4abgabetyp') )}}</div>
						<div class="col-12 col-md-9">
							<Dropdown
								:style="{'width': '100%'}"
								:disabled="!termin.allowedToSave"
								:placeholder="getPlaceholderTermin(termin)"
								v-model="termin.bezeichnung"
								@change="handleChangeAbgabetyp(termin)"
								:options="getAllowedAbgabeTypeOptions"
								:optionLabel="getOptionLabelAbgabetyp"
								:optionDisabled="getOptionDisabled">
							</Dropdown>
						</div>
					</div>
					<div class="row mt-2" v-if="termin.bezeichnung?.paabgabetyp_kurzbz === 'qualgate1' || termin.bezeichnung?.paabgabetyp_kurzbz === 'qualgate2'">
						<div class="col-12 col-md-3 fw-bold align-content-center">{{$capitalize( $p.t('abgabetool/c4upload_allowed') )}}</div>
						<div class="col-12 col-md-9">
							<Checkbox
								:disabled="!termin.allowedToSave"
								v-model="termin.upload_allowed"
								:binary="true"
								:pt="{ root: { class: 'ml-auto' }}"
							>
							</Checkbox>
						</div>
					</div>
					<div class="row mt-2" v-if="termin.bezeichnung?.benotbar">
						<div class="col-12 col-md-3 fw-bold align-content-center">{{$capitalize( $p.t('abgabetool/c4note') )}}</div>
						<div class="col-12 col-md-9">
							<Dropdown
								:style="{'width': '100%'}"
								v-model="termin.note"
								:options="allowedNotenOptions"
								:optionLabel="getNotenOptionLabel">
							</Dropdown>
						</div>
					</div>
					<div class="row mt-2" v-if="termin.bezeichnung?.benotbar">
						<div class="col-12 col-md-3 fw-bold align-content-center">{{$capitalize( $p.t('abgabetool/c4notizQualGatev2') )}}</div>
						<div class="col-12 col-md-9">
							<Textarea style="margin-bottom: 4px;" v-model="termin.beurteilungsnotiz" rows="1" class="w-100" :disabled="!termin.allowedToSave"></Textarea>
						</div>
					</div>
					
					<div class="row mt-2">
						<div class="col-12 col-md-3 fw-bold align-content-center">{{$capitalize( $p.t('abgabetool/c4abgabekurzbz') )}}</div>
						<div class="col-12 col-md-9">
							<Textarea style="margin-bottom: 4px;" v-model="termin.kurzbz" class="w-100" rows="1" :disabled="!termin.allowedToSave"></Textarea>
						</div>
					</div>
					<div class="row mt-2">
						<div class="col-12 col-md-3 fw-bold align-content-center">{{$capitalize( $p.t('abgabetool/c4abgabedatum') )}}</div>
						<div class="col-12 col-md-9">
							<template v-if="termin?.abgabedatum">
								<div class="row">
									<div style="width:250px">
										<VueDatePicker
											v-model="termin.abgabedatum"
											:clearable="false"
											:disabled="true"
											:format="formatDate">
										</VueDatePicker>
									</div>

									<div class="col-auto">
										<button v-if="termin?.abgabedatum" @click="downloadAbgabe(termin)" class="btn btn-primary">
											<a> {{$capitalize( $p.t('abgabetool/c4downloadAbgabe') )}} <i class="fa fa-file-pdf" style="margin-left:4px; cursor: pointer;"></i></a>
										</button>	
									</div>
									
									<div v-if="termin?.signatur !== undefined && termin?.signatur !== null" class="col-auto">
										<Message v-if="termin?.signatur == true" severity="success" :closable="false" :pt="getMessagePtStyle"> {{ $capitalize($p.t('abgabetool/c4signaturGefunden')) }} </Message>
										<Message v-else-if="termin?.signatur == false" severity="error" :closable="false" :pt="getMessagePtStyle"> {{ $capitalize($p.t('abgabetool/c4keineSignatur')) }} </Message>
										<Message v-else-if="termin?.signatur == 'error'" severity="warn" :closable="false" :pt="getMessagePtStyle"> {{ $capitalize($p.t('abgabetool/c4signaturServerError')) }} </Message>
									</div>
									<div v-else class="col-auto">
										<Message severity="info" :closable="false" :pt="getMessagePtStyle"> {{ $p.t('abgabetool/c4noFileFound') }} </Message>
									</div>
									
								</div>						
							</template>
							<template v-else>
								{{ $capitalize( $p.t('abgabetool/c4nochNichtsAbgegeben') )}}
							</template>
						</div>
					</div>
					<div class="row mt-2">
						<div class="col-12 col-md-3 fw-bold align-content-center">
								 {{ $capitalize( $p.t('abgabetool/c4actions') )}}
						</div>
						<div class="col-12 col-md-9">
							<div class="row">
								<div class="col-auto">
									<button v-if="termin.allowedToSave" style="max-height: 40px;" class="btn btn-primary border-0" @click="saveTermin(termin)">
										{{ $capitalize( $p.t('abgabetool/c4save') )}}
										<i class="fa-solid fa-floppy-disk"></i>
									</button>
									<div v-else v-tooltip.right="getTooltipNotAllowedToSave">
										<button disabled style="max-height: 40px;" class="btn btn-primary border-0">
											{{$capitalize( $p.t('abgabetool/c4save') )}}
											<i class="fa-solid fa-floppy-disk"></i>
										</button>
									</div>
								</div>
								<div class="col-auto">
									<button v-if="termin.allowedToDelete && termin.paabgabe_id > 0" style="max-height: 40px;" class="btn btn-danger border-0" @click="handleDeleteTermin(termin)">
										{{$capitalize( $p.t('abgabetool/c4delete') )}}
										<i class="fa-solid fa-trash"></i>
									</button>
									<div v-else v-tooltip.right="getTooltipNotAllowedToDelete">
										<button disabled style="max-height: 40px;" class="btn btn-danger border-0">
											{{$capitalize( $p.t('abgabetool/c4delete') )}}
											<i class="fa-solid fa-trash"></i>
										</button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</AccordionTab>
			</template>
		</Accordion>
		
		<div v-if="projektarbeit?.abgabetermine.length == 0" style="display:flex; justify-content: center; align-content: center;">
			<h5>{{ $capitalize( $p.t('abgabetool/c4keineAbgabetermineGefunden') )}}</h5>
		</div>
		
		<bs-modal 
	 		ref="modalContainerZusatzdaten"
	 		class="bootstrap-prompt"
	 		dialogClass="bordered-modal modal-lg">
			<template v-slot:title>
				<div>
					{{$capitalize( $p.t('abgabetool/c4enduploadZusatzdaten') )}}
				</div>
				<div class="row mb-3 align-items-start">
					
					<p class="ml-4 mr-4">Student UID: {{ projektarbeit?.student_uid}}</p>
				
				</div>
				<div class="row mb-3 align-items-start">
					
					<p class="ml-4 mr-4">{{$capitalize( $p.t('abgabetool/c4titel') )}}: {{ projektarbeit?.titel }}</p>
				
				</div>
			</template>
			<template v-slot:default>
				<div class="row mb-3 align-items-start">
					<div class="row">{{$capitalize( $p.t('abgabetool/c4Sprache') )}}</div>
					<div class="row">
						<Dropdown 
							:style="{'width': '100%'}"
							v-model="form.sprache"
							:options="allActiveLanguages"
							:optionLabel="getOptionLabelSprache">
						</Dropdown>
					</div>
				</div>
<!--				 lektor fills these out-->
<!--				<div class="row mb-3 align-items-start">-->
<!--					<div class="row">Kontrollierte Schlagwörter</div>-->
<!--					<div class="row">-->
<!--						<Textarea v-model="form.kontrollschlagwoerter"></Textarea>-->
<!--					</div>-->
<!--					-->
<!--				-->
<!--				</div>-->
				<div class="row mb-3 align-items-start">
					<div class="row">{{$capitalize( $p.t('abgabetool/c4schlagwoerterGer') )}}</div>
					<div class="row">
						<Textarea v-model="form.schlagwoerter" class="w-100"></Textarea>
					</div>
				</div>
				
				<div class="row mb-3 align-items-start">
					<div class="row">{{$capitalize( $p.t('abgabetool/c4schlagwoerterEng') )}}</div>
					<div class="row">
						<Textarea v-model="form.schlagwoerter_en" class="w-100"></Textarea>
					</div>
				</div>
				
				<div class="row mb-3 align-items-start">
					<div class="row">{{$capitalize( $p.t('abgabetool/c4abstractGer') )}}</div>
					<div class="row">
						<Textarea v-model="form.abstract" rows="10" maxlength="5000" class="w-100"></Textarea>
						<p>{{ form.abstract?.length ? form.abstract.length : 0 }} / 5000 characters</p>
					</div>
				</div>

				<div class="row mb-3 align-items-start">
					<div class="row">{{$capitalize( $p.t('abgabetool/c4abstractEng') )}}</div>
					<div class="row">
						<Textarea v-model="form.abstract_en" rows="10" maxlength="5000" class="w-100"></Textarea>
						<p>{{ form.abstract_en?.length ? form.abstract_en.length : 0 }} / 5000 characters</p>
					</div>				
				</div>
				
				<div class="row mb-3 align-items-start">
					<div class="row">{{$capitalize( $p.t('abgabetool/c4seitenanzahl') )}}</div>
					<div class="row">
						<InputNumber 
							v-model="form.seitenanzahl"
							inputId="seitenanzahlInput" :min="1" :max="100000">
						</InputNumber>
					</div>		
				</div>
				
			</template>
			<template v-slot:footer>
				<div v-show="!allowedToSaveZusatzdaten">{{ $p.t('abgabetool/c4zusatzdatenausfuellen') }}</div>
				<button class="btn btn-primary" :disabled="!allowedToSaveZusatzdaten" @click="saveZusatzdaten">{{ $capitalize( $p.t('abgabetool/c4save') )}}</button>
			</template>
		</bs-modal>
		
	</div>
`,
};

export default AbgabeMitarbeiterDetail;
