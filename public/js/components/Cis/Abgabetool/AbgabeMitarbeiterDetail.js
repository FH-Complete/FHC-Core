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
					
					const newTerminRes = {
						...res.data
					}
					newTerminRes.bezeichnung = {
						bezeichnung: termin.bezeichnung?.bezeichnung,
						paabgabetyp_kurzbz: termin.bezeichnung?.paabgabetyp_kurzbz
					}
					
					this.projektarbeit.abgabetermine.push(newTerminRes)
					
					// if(paabgabe_id === -1) { // new abgabe has been inserted
					// 	termin.paabgabe_id = res?.data?.paabgabe_id
					// 	this.projektarbeit.abgabetermine.push({ // new abgatermin row
					// 		'paabgabe_id': -1,
					// 		'projektarbeit_id': this.projektarbeit.projektarbeit_id,
					// 		'fixtermin': false,
					// 		'kurzbz': '',
					// 		'datum': new Date().toISOString().split('T')[0],
					// 		'paabgabetyp_kurzbz': termin.paabgabetyp_kurzbz,
					// 		'note': this.allowedNotenOptions.find(opt => opt.note == termin.note?.note),
					// 		'upload_allowed': termin.upload_allowed,
					// 		'bezeichnung': this.abgabeTypeOptions.find(opt => opt.paabgabetyp_kurzbz === termin.paabgabetyp_kurzbz),
					// 		'abgabedatum': null,
					// 		'insertvon': this.viewData?.uid ?? '',
					// 		'allowedToSave': true,
					// 		'allowedToDelete': true
					// 	})
					// }
					
				} else if(res?.meta?.status == 'error'){
					this.$fhcAlert.alertError()
				}
			})
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

			// todo: rework styling but keep the color pattern logic
			// https://wiki.fhcomplete.info/doku.php?id=cis:abgabetool_fuer_studierende
			let color = 'white'
			let fontColor = 'black'
			let icon = '';
			if (termin.abgabedatum === null) {
				if(datum < today) {
					color = 'red'
					fontColor = 'white'
					icon = 'fa-triangle-exclamation'
				} else if (datum > today && this.dateDiffInDays(datum, today) <= 12) {
					color = 'yellow'
					icon = 'fa-circle-exclamation'
				}
			} else if(abgabedatum > datum) {
				color = 'pink' // aka "hellrot"
				fontColor = 'white'
				icon = 'fa-circle-question'
			} else {
				color = 'green'
				icon = 'fa-square-check'
			}

			//return `font-color: ${fontColor} ; background-color: ${color}; border-radius: 50%;`
			if(  typeof mode !== 'undefined' || mode === 'icon') {
				return icon;
			} else {
				return 'abgabe-zieldatum-border-' + color;
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
					'notiz': '',
					'upload_allowed': false,
					'paabgabetyp_kurzbz': '',
					'bezeichnung': '',
					'abgabedatum': null,
					'insertvon': this.viewData?.uid ?? ''
				}
			}
			console.log(this.$refs.modalContainerCreateNewAbgabe)
			this.$refs.modalContainerCreateNewAbgabe.show()
		},
		async handleSaveNewAbgabe(termin) {
			await this.saveTermin(termin)
			
			this.$refs.modalContainerCreateNewAbgabe.hide()
			this.newTermin = {
				'paabgabe_id': -1,
				'projektarbeit_id': this.projektarbeit.projektarbeit_id,
				'fixtermin': false,
				'kurzbz': '',
				'datum': new Date().toISOString().split('T')[0],
				'note': this.allowedNotenOptions.find(opt => opt.note == 9),
				'notiz': '',
				'upload_allowed': false,
				'paabgabetyp_kurzbz': '',
				'bezeichnung': '',
				'abgabedatum': null,
				'insertvon': this.viewData?.uid ?? ''
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
	created() {

	},
	mounted() {

	},
	template: `
		<bs-modal id="innerModalNewAbgabe" ref="modalContainerCreateNewAbgabe" class="bootstrap-prompt" dialogClass="bordered-modal modal-xl" :backdrop="true">
			<template v-slot:title>
				<div>
					{{ $p.t('abgabetool/c4newAbgabetermin') }}
				</div>
			</template>
			<template v-slot:default>
				<div v-if="newTermin">
					<div class="row">
						<div class="col-4 col-md-3 fw-bold">{{$p.t('abgabetool/c4fixtermin')}}</div>
						<div class="col-8 col-md-9">
							<Checkbox 
								v-model="newTermin.fixtermin"
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
								:optionLabel="getOptionLabelAbgabetyp">
							</Dropdown>
						</div>
					</div>
					<div class="row mt-2" v-if="newTermin.bezeichnung?.paabgabetyp_kurzbz === 'qualgate1' || newTermin.bezeichnung?.paabgabetyp_kurzbz === 'qualgate2'">
						<div class="col-4 col-md-3 fw-bold">{{$p.t('abgabetool/c4note')}}</div>
						<div class="col-8 col-md-9">
							<Dropdown
								:style="{'width': '100%'}"
								v-model="newTermin.note"
								:options="allowedNotenOptions"
								:optionLabel="getNotenOptionLabel">
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
					<div class="row mt-2" v-if="newTermin.bezeichnung?.paabgabetyp_kurzbz === 'qualgate1' || newTermin.bezeichnung?.paabgabetyp_kurzbz === 'qualgate2'">
						<div class="col-4 col-md-3 fw-bold">{{$p.t('abgabetool/c4notizQualGate')}}</div>
						<div class="col-8 col-md-9">
							<Textarea style="margin-bottom: 4px;" v-model="newTermin.notiz" :rows=" isMobile ? 2 : 4" :cols=" isMobile ? 30 : 90"></Textarea>
						</div>
					</div>
					<div class="row mt-2">
						<div class="col-4 col-md-3 fw-bold">{{$p.t('abgabetool/c4abgabekurzbz')}}</div>
						<div class="col-8 col-md-9">
							<Textarea style="margin-bottom: 4px;" v-model="newTermin.kurzbz" :rows=" isMobile ? 2 : 4" :cols=" isMobile ? 30 : 90"></Textarea>
						</div>
					</div>	
				</div>
			</template>
			<template v-slot:footer>
				<button type="button" class="btn btn-primary" @click="handleSaveNewAbgabe(newTermin)">{{ $p.t('global/c4saveNewAbgabetermin') }}</button>
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
				<div class="col-8">
					<p> {{projektarbeit?.student}}</p>
					<p> {{projektarbeit?.titel}}</p>
					<p v-if="projektarbeit?.zweitbegutachter"> {{projektarbeit?.zweitbegutachter}}</p>
				</div>
				<div class="col-4 d-flex">
					<div class="col">
						<div class="row">
							<button :disabled="!getSemesterBenotbar || !endUploadVorhanden" class="btn btn-secondary border-0" @click="openBenotung" style="width: 80%;">
								{{ $p.t('abgabetool/c4benoten') }}
								<i class="fa-solid fa-user-check"></i>
							</button>
						</div>
						<div class="row" style="width: 90%;">
							<span v-if="!getSemesterBenotbar" v-html="$p.t('abgabetool/c4aeltereParbeitBenoten', oldPaBeurteilungLink)"></span>
							<span v-else-if="!endUploadVorhanden">{{ $p.t('abgabetool/c4noEnduploadFound') }}</span>
						</div>
					</div>
					<div class="col">
						<div class="row">
							<button v-if="projektarbeit?.betreuerart_kurzbz !== 'Zweitbegutachter'" class="btn btn-secondary border-0" @click="openPlagiatcheck" style="width: 80%;">
								{{ $p.t('abgabetool/c4plagiatcheck_link')}}
								<i class="fa-solid fa-user-check"></i>
							</button>
						</div>
						
					</div>
					<div class="col">
						<div class="row">
							<button class="btn btn-secondary border-0" @click="openStudentPage" style="width: 80%;">
								{{ $p.t('abgabetool/c4student_perspective')}}
								<i class="fa-solid fa-eye"></i>
							</button>
						</div>
						
					</div>
				</div>
			</div>
			
			<Accordion :multiple="true" :activeIndex="[0]">
				<template v-for="termin in this.projektarbeit?.abgabetermine">
					<AccordionTab :header="getAccTabHeaderForTermin(termin)">
							
						<div class="row">
							<div class="col-4 col-md-3 fw-bold">{{$p.t('abgabetool/c4fixtermin')}}</div>
							<div class="col-8 col-md-9">
								<Checkbox 
									v-model="termin.fixtermin"
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
									:options="abgabeTypeOptions"
									:optionLabel="getOptionLabelAbgabetyp">
								</Dropdown>
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
							<div class="col-4 col-md-3 fw-bold">{{$p.t('abgabetool/c4notizQualGate')}}</div>
							<div class="col-8 col-md-9">
								<Textarea style="margin-bottom: 4px;" v-model="termin.notiz" :rows=" isMobile ? 2 : 4" :cols=" isMobile ? 30 : 90" :disabled="!termin.allowedToSave"></Textarea>
							</div>
						</div>
						<div class="row mt-2">
							<div class="col-4 col-md-3 fw-bold">{{$p.t('abgabetool/c4abgabekurzbz')}}</div>
							<div class="col-8 col-md-9">
								<Textarea style="margin-bottom: 4px;" v-model="termin.kurzbz" :rows=" isMobile ? 2 : 4" :cols=" isMobile ? 30 : 90" :disabled="!termin.allowedToSave"></Textarea>
							</div>
						</div>
						<div class="row mt-2">
							<div class="col-4 col-md-3 fw-bold">{{$p.t('abgabetool/c4abgabedatum')}}</div>
							<div class="col-8 col-md-9">
								{{ termin.abgabedatum?.split("-").reverse().join(".") }}
								<a v-if="termin?.abgabedatum" @click="downloadAbgabe(termin)" style="margin-left:4px; cursor: pointer;">
									<i class="fa-solid fa-2x fa-file-pdf"></i>
								</a>
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
										<button v-if="termin.allowedToDelete && termin.paabgabe_id > 0" style="max-height: 40px;" class="btn btn-primary border-0" @click="deleteTermin(termin)">
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
			
<!--			<div id="uploadWrapper">-->
<!--				<div class="row" style="margin-bottom: 12px;">-->
<!--					<div class="fw-bold" style="width: 100px">{{$p.t('abgabetool/c4fixtermin')}}</div>-->
<!--					<div class="row" style="max-width: calc(100% - 100px)">-->
<!--						<div class="col-2 fw-bold">{{$p.t('abgabetool/c4zieldatum')}}</div>-->
<!--						<div class="col-2 fw-bold">{{$p.t('abgabetool/c4abgabetyp')}}</div>-->
<!--						<div v-show="qualityGateTerminAvailable" class="col-2 fw-bold">{{$p.t('abgabetool/c4note')}}</div>-->
<!--						<div v-show="qualityGateTerminAvailable" class="col-1 fw-bold">{{$p.t('abgabetool/c4upload_allowed')}}</div>-->
<!--						<div class="col-2 fw-bold">{{$p.t('abgabetool/c4abgabekurzbz')}}</div>-->
<!--						<div class="col-1 fw-bold">{{$p.t('abgabetool/c4abgabedatum')}}</div>-->
<!--						<div class="col">-->
<!--							-->
<!--						</div>-->
<!--					</div>-->
<!--				</div>-->
<!--				<div v-if="!projektarbeit?.abgabetermine?.length">keine Termine gefunden!</div>-->
<!--				<div class="row" v-for="termin in projektarbeit.abgabetermine">-->
<!--					<div style="width: 100px" class="d-flex justify-content-center align-items-center">-->
<!--						<i v-if="termin.fixtermin" class="fa-solid fa-2x fa-circle-check fhc-bullet-red"></i>-->
<!--						<i v-else="" class="fa-solid fa-2x fa-circle-xmark fhc-bullet-green"></i>-->
<!--&lt;!&ndash;						<p class="fhc-bullet" :class="{ 'fhc-bullet-red': termin.fixtermin, 'fhc-bullet-green': !termin.fixtermin }"></p>&ndash;&gt;-->
<!--					</div>-->
<!--					<div class="row" style="max-width: calc(100% - 100px)">-->
<!--						<div class="col-2 d-flex justify-content-center align-items-center">-->
<!--							<div class="position-relative" :class="getDateStyle(termin)">-->
<!--								<VueDatePicker-->
<!--									style="width: 95%;"-->
<!--									v-model="termin.datum"-->
<!--									:clearable="false"-->
<!--									:disabled="!termin.allowedToSave"-->
<!--									:enable-time-picker="false"-->
<!--									:format="formatDate"-->
<!--									:text-input="true"-->
<!--									auto-apply>-->
<!--								</VueDatePicker>-->
<!--								<i class="position-absolute abgabe-zieldatum-overlay fa-solid fa-2x" :class="getDateStyle(termin, 'icon')"></i>-->
<!--							</div>				-->
<!--						</div>-->
<!--						<div class="col-2 d-flex justify-content-center align-items-center">-->
<!--							<Dropdown -->
<!--								:style="{'width': '100%'}"-->
<!--								:disabled="!termin.allowedToSave"-->
<!--								v-model="termin.bezeichnung"-->
<!--								:options="abgabeTypeOptions"-->
<!--								:optionLabel="getOptionLabelAbgabetyp">-->
<!--							</Dropdown>-->
<!--						</div>-->
<!--						<div v-if="qualityGateTerminAvailable || termin.bezeichnung?.paabgabetyp_kurzbz === 'qualgate1' || termin.bezeichnung?.paabgabetyp_kurzbz === 'qualgate2'" class="col-2 d-flex justify-content-center align-items-center">-->
<!--							<Dropdown -->
<!--								v-if="termin.bezeichnung?.paabgabetyp_kurzbz === 'qualgate1' || termin.bezeichnung?.paabgabetyp_kurzbz === 'qualgate2'"-->
<!--								:style="{'width': '100%'}"-->
<!--								v-model="termin.note"-->
<!--								:options="allowedNotenOptions"-->
<!--								:optionLabel="getNotenOptionLabel">-->
<!--							</Dropdown>-->
<!--						</div>-->
<!--						<div v-if="qualityGateTerminAvailable || termin.bezeichnung?.paabgabetyp_kurzbz === 'qualgate1' || termin.bezeichnung?.paabgabetyp_kurzbz === 'qualgate2'" class="col-1 d-flex justify-content-center align-items-center">-->
<!--							<Checkbox -->
<!--								v-if="termin.bezeichnung?.paabgabetyp_kurzbz === 'qualgate1' || termin.bezeichnung?.paabgabetyp_kurzbz === 'qualgate2'"-->
<!--								v-model="termin.upload_allowed"-->
<!--								:binary="true" -->
<!--								:pt="{ root: { class: 'ml-auto' }}"-->
<!--							>-->
<!--							</Checkbox>-->
<!--						</div>-->
<!--						<div class="col-2 d-flex justify-content-center align-items-center">-->
<!--							<Textarea style="margin-bottom: 4px;" v-model="termin.kurzbz" rows="1" cols="20" :disabled="!termin.allowedToSave"></Textarea>-->
<!--						</div>-->
<!--						<div class="col-1 d-flex justify-content-center align-items-center">-->
<!--							{{ termin.abgabedatum?.split("-").reverse().join(".") }}-->
<!--							<a v-if="termin?.abgabedatum" @click="downloadAbgabe(termin)" style="margin-left:4px; cursor: pointer;">-->
<!--								<i class="fa-solid fa-2x fa-file-pdf"></i>-->
<!--							</a>-->
<!--						</div>-->
<!--						<div class="col-2 align-content-center">-->
<!--							<button v-if="termin.allowedToSave" style="max-height: 40px;" class="btn btn-primary border-0" @click="saveTermin(termin)">-->
<!--								{{ $p.t('abgabetool/c4save') }}-->
<!--								<i class="fa-solid fa-floppy-disk"></i>-->
<!--							</button>-->
<!--							<button v-if="termin.allowedToDelete && termin.paabgabe_id > 0" style="max-height: 40px;" class="btn btn-primary border-0" @click="deleteTermin(termin)">-->
<!--								{{ $p.t('abgabetool/c4delete') }}-->
<!--								<i class="fa-solid fa-trash"></i>-->
<!--							</button>-->
<!--						</div>-->
<!--					</div>-->
<!--				</div>-->
<!--			</div>-->
		 </div>
`,
};

export default AbgabeMitarbeiterDetail;
