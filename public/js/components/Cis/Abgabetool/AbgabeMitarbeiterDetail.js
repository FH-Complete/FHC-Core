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
		VueDatePicker
	},
	inject: ['abgabeTypeOptions', 'allowedNotenOptions', 'turnitin_link', 'old_abgabe_beurteilung_link', 'isMobile'],
	props: {
		projektarbeit: {
			type: Object,
			default: null
		}
	},
	data() {
		return {
			showAutomagicModalPhrase: false,
			sdModel: [],
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
			newTermin: null
		}
	},
	methods: {
		openZusatzdatenModal(termin) {
				
		},
		saveTermin(termin) {
			const paabgabe_id = termin.paabgabe_id
			termin.note_pk = termin.note?.note ?? null
			termin.betreuer_person_id = this.projektarbeit.betreuer_person_id
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
					const existingTerminRes = res.data[1]
					newTerminRes.bezeichnung = {
						bezeichnung: termin.bezeichnung?.bezeichnung,
						paabgabetyp_kurzbz: termin.bezeichnung?.paabgabetyp_kurzbz
					}
					
					// only insert new abgabe if we actually created a new one, not when saving/editing existing
					if(!existingTerminRes){
						this.projektarbeit.abgabetermine.push(newTerminRes)
						this.projektarbeit.abgabetermine.sort((a, b) =>new Date(a.datum) - new Date(b.datum))
					} else {
						const noteOptExisting = this.allowedNotenOptions.find(opt => opt.note == existingTerminRes.note)
						existingTerminRes.note = noteOptExisting
					}
					
					// negative abgabe -> automagically open new termin modal
					// really bad feature imo that will be annoying to deal with
					
					// termin is completely new and has negative note
					const savedNewWithNegative = !existingTerminRes && !newTerminRes.note?.positiv

					// termin existed previously + oldTermin had different note/positive note or no note at all
					const savedExistingNoteAsNegativeAndWasNotNegativeBefore = existingTerminRes && !newTerminRes.note?.positiv && (existingTerminRes.note?.positiv || existingTerminRes.note === undefined)

					const openModalDueToNegativeBeurteilung = savedNewWithNegative || savedExistingNoteAsNegativeAndWasNotNegativeBefore
					if(openModalDueToNegativeBeurteilung) {
						this.newTermin = {
							'paabgabe_id': -1,
							'projektarbeit_id': this.projektarbeit.projektarbeit_id,
							'fixtermin': false,
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
			this.$api.call(ApiAbgabe.getStudentProjektarbeitAbgabeFile(termin.paabgabe_id, this.projektarbeit.student_uid))
		},
		dateDiffInDays(datum, today){
			const oneDayMs = 1000 * 60 * 60 * 24
			return Math.round((new Date(datum) - new Date(today)) / oneDayMs)
		},
		getDateStyle(termin, mode) {
			const datum = new Date(termin.datum)
			const abgabedatum = new Date(termin.abgabedatum)

			// https://wiki.fhcomplete.info/doku.php?id=cis:abgabetool_fuer_studierende
			if (termin.abgabedatum === null) {
				if(datum < today) {
					return 'verpasst-header'
				} else if (datum > today && this.dateDiffInDays(datum, today) <= 12) {
					return 'abzugeben-header'
				} else {
					return 'standard-header'
				} 
			} else if(abgabedatum > datum) {
				return 'verspaetet-header'
			} else {
				return 'abgegeben-header'
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
			const path = this.projektarbeit?.betreuerart_kurzbz == 'Zweitbegutachter' ? 'ProjektarbeitsbeurteilungZweitbegutachter' : 'ProjektarbeitsbeurteilungErstbegutachter'
			const link = FHC_JS_DATA_STORAGE_OBJECT.app_root + 'index.ci.php/extensions/FHC-Core-Projektarbeitsbeurteilung/' + path
			window.open(link, '_blank')
		},
		formatDate(dateParam) {
			const date = new Date(dateParam)
			// handle missing leading 0
			const padZero = (num) => String(num).padStart(2, '0');

			const month = padZero(date.getMonth() + 1); // Months are zero-based
			const day = padZero(date.getDate());
			const year = date.getFullYear();

			return `${day}.${month}.${year}`;
		},
		getAccTabHeaderForTermin(termin) {
			let tabTitle = ''
			
			const datumFormatted = this.formatDate(termin.datum)
			tabTitle += termin.bezeichnung?.bezeichnung + ' ' + datumFormatted

			return tabTitle
		},
		openCreateNewAbgabeModal() {
			if(!this.newTermin) {
				this.newTermin = {
					'paabgabe_id': -1,
					'projektarbeit_id': this.projektarbeit.projektarbeit_id,
					'fixtermin': false,
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
		getPageWrapperStyle() {
			return 'position: relative; min-height: 100vh;'
		},
		getSpeedDialStyle() {
			return 'position: static !important;'
		}
	},
	watch: {
		'newTermin.bezeichnung'(newVal) {
			console.log('\'newTermin.bezeichnung\' watcher', newVal)

			if(newVal?.paabgabetyp_kurzbz === 'qualgate1' || newVal?.paabgabetyp_kurzbz === 'qualgate2') {
				this.newTermin.kurzbz = newVal.bezeichnung
			}
		}	
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
			dialogClass="bordered-modal modal-xl" 
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
<!--				minheight to avoid z-index magic for the datepicker inside the modal inside the modal...-->
				<div v-if="newTermin">
<!--				fixtermin is not an option for lektors-->
<!--					<div class="row">-->
<!--						<div class="col-4 col-md-3 fw-bold">{{$p.t('abgabetool/c4fixtermin')}}</div>-->
<!--						<div class="col-8 col-md-9">-->
<!--							<Checkbox -->
<!--								v-model="newTermin.fixtermin"-->
<!--								:binary="true" -->
<!--								:pt="{ root: { class: 'ml-auto' }}"-->
<!--							>-->
<!--							</Checkbox>-->
<!--						</div>-->
<!--					</div>-->
					<div class="row mt-2">
						<div class="col-4 col-md-3 fw-bold">{{$p.t('abgabetool/c4zieldatum')}}</div>
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
						<div class="col-4 col-md-3 fw-bold">{{$p.t('abgabetool/c4abgabetyp')}}</div>
						<div class="col-8 col-md-9">
							<Dropdown
								:style="{'width': '100%'}"
								v-model="newTermin.bezeichnung"
								:options="abgabeTypeOptions"
								:optionLabel="getOptionLabelAbgabetyp"
								scrollHeight="300px">
							</Dropdown>
						</div>
					</div>
					<div class="row mt-2" v-if="newTermin.bezeichnung?.paabgabetyp_kurzbz === 'qualgate1' || newTermin.bezeichnung?.paabgabetyp_kurzbz === 'qualgate2'">
						<div class="col-4 col-md-3 fw-bold">{{$p.t('abgabetool/c4upload_allowed')}}</div>
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
						<div class="col-4 col-md-3 fw-bold">{{$p.t('abgabetool/c4abgabekurzbz')}}</div>
						<div class="col-8 col-md-9">
							<Textarea style="margin-bottom: 4px;" v-model="newTermin.kurzbz" rows="1" :cols=" isMobile ? 30 : 90"></Textarea>
						</div>
					</div>	
				</div>
			</template>
			<template v-slot:footer>
				<button type="button" class="btn btn-primary" @click="handleSaveNewAbgabe(newTermin)">{{ $p.t('abgabetool/c4saveNewAbgabetermin') }}</button>
			</template>
		</bs-modal>

		<div v-if="projektarbeit">
			
			<div style="position: fixed; bottom: 24px; right: 24px; z-index: 9999;">
				<SpeedDial
					:style="getSpeedDialStyle"
					:model="speedDialItems" 
					direction="up"
					:radius="80"
					type="linear"
					buttonClass="p-button-rounded p-button-lg p-button-primary"
					:tooltipOptions="{ position: 'left' }"
				/>
			</div>
			
			<h5>{{$p.t('abgabetool/c4abgabeMitarbeiterbereich')}}</h5>

			<div class="row">
				<div class="col-6">
					<p> {{projektarbeit?.student}}</p>
					<p> {{projektarbeit?.titel}}</p>
					<p v-if="projektarbeit?.zweitbegutachter"> {{projektarbeit?.zweitbegutachter}}</p>
				</div>
			</div>
			
			<Accordion :multiple="true" :activeIndex="[0]">
				<template v-for="termin in this.projektarbeit?.abgabetermine">
					<AccordionTab :header="getAccTabHeaderForTermin(termin)" :headerClass="getDateStyle(termin)">
							
						<div class="row">
							<div class="col-4 col-md-3 fw-bold">{{$p.t('abgabetool/c4fixterminv2')}}</div>
							<div class="col-8 col-md-9">
<!--							always keep fixtermin checkbox disabled for mitarbeiter tool -->
								<Checkbox 
									v-model="termin.fixtermin"
									disabled
									:binary="true" 
									:pt="{ root: { class: 'ml-auto' }}"
								>
								</Checkbox>
							</div>
						</div>
						<div class="row mt-2">
							<div class="col-4 col-md-3 fw-bold">{{$p.t('abgabetool/c4zieldatum')}}</div>
							<div class="col-8 col-md-9">
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
							<div class="col-4 col-md-3 fw-bold">{{$p.t('abgabetool/c4abgabetyp')}}</div>
							<div class="col-8 col-md-9">
								<Dropdown
									:style="{'width': '100%'}"
									:disabled="!termin.allowedToSave"
									v-model="termin.bezeichnung"
									@change="handleChangeAbgabetyp(termin)"
									:options="abgabeTypeOptions"
									:optionLabel="getOptionLabelAbgabetyp">
								</Dropdown>
							</div>
						</div>
						<div class="row mt-2" v-if="termin.bezeichnung?.paabgabetyp_kurzbz === 'qualgate1' || termin.bezeichnung?.paabgabetyp_kurzbz === 'qualgate2'">
							<div class="col-4 col-md-3 fw-bold">{{$p.t('abgabetool/c4upload_allowed')}}</div>
							<div class="col-8 col-md-9">
								<Checkbox
									v-model="termin.upload_allowed"
									:binary="true"
									:pt="{ root: { class: 'ml-auto' }}"
								>
								</Checkbox>
							</div>
						</div>
						<div class="row mt-2" v-if="termin.bezeichnung?.paabgabetyp_kurzbz === 'qualgate1' || termin.bezeichnung?.paabgabetyp_kurzbz === 'qualgate2'">
							<div class="col-4 col-md-3 fw-bold">{{$p.t('abgabetool/c4note')}}</div>
							<div class="col-8 col-md-9">
								<Dropdown
									:style="{'width': '100%'}"
									v-model="termin.note"
									:options="allowedNotenOptions"
									:optionLabel="getNotenOptionLabel">
								</Dropdown>
							</div>
						</div>
						<div class="row mt-2" v-if="termin.bezeichnung?.paabgabetyp_kurzbz === 'qualgate1' || termin.bezeichnung?.paabgabetyp_kurzbz === 'qualgate2'">
							<div class="col-4 col-md-3 fw-bold">{{$p.t('abgabetool/c4notizQualGatev2')}}</div>
							<div class="col-8 col-md-9">
								<Textarea style="margin-bottom: 4px;" v-model="termin.beurteilungsnotiz" rows="1" :cols=" isMobile ? 30 : 90" :disabled="!termin.allowedToSave"></Textarea>
							</div>
						</div>
						
						<div class="row mt-2">
							<div class="col-4 col-md-3 fw-bold">{{$p.t('abgabetool/c4abgabekurzbz')}}</div>
							<div class="col-8 col-md-9">
								<Textarea style="margin-bottom: 4px;" v-model="termin.kurzbz" rows="1" :cols=" isMobile ? 30 : 90" :disabled="!termin.allowedToSave"></Textarea>
							</div>
						</div>
						<div class="row mt-2">
							<div class="col-4 col-md-3 fw-bold">{{$p.t('abgabetool/c4abgabedatum')}}</div>
							<div class="col-8 col-md-9">
								{{ termin.abgabedatum?.split("-").reverse().join(".") }}
								<button v-if="termin?.abgabedatum" @click="downloadAbgabe(termin)" class="btn btn-primary">
									<a> {{$p.t('abgabetool/c4downloadAbgabe')}} <i class="fa fa-file-pdf" style="margin-left:4px; cursor: pointer;"></i></a>
								</button>
							</div>
						</div>
						<div class="row mt-2">
							<div class="col-4 col-md-3 fw-bold">
<!--								 TODO: row description? -->
							</div>
							<div class="col-8 col-md-9">
								<div class="row">
									<div class="col-4">
										<button v-if="termin.allowedToSave" style="max-height: 40px;" class="btn btn-primary border-0" @click="saveTermin(termin)">
											{{ $p.t('abgabetool/c4save') }}
											<i class="fa-solid fa-floppy-disk"></i>
										</button>
									</div>
									<div class="col-4">
										<button v-if="termin.allowedToDelete && termin.paabgabe_id > 0" style="max-height: 40px;" class="btn btn-danger border-0" @click="handleDeleteTermin(termin)">
											{{ $p.t('abgabetool/c4delete') }}
											<i class="fa-solid fa-trash"></i>
										</button>
									</div>
								</div>
							</div>
						</div>
					</AccordionTab>
				</template>
			</Accordion>
		 </div>
`,
};

export default AbgabeMitarbeiterDetail;
