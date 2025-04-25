import BsModal from '../../Bootstrap/Modal.js';

const today = new Date()
export const AbgabeMitarbeiterDetail = {
	name: "AbgabeMitarbeiterDetail",
	components: {
		BsModal,
		InputNumber: primevue.inputnumber,
		Checkbox: primevue.checkbox,
		Dropdown: primevue.dropdown,
		Textarea: primevue.textarea,
		VueDatePicker
	},
	props: {
		projektarbeit: {
			type: Object,
			default: null
		}
	},
	data() {
		return {
			eidAkzeptiert: false,
			enduploadTermin: null,
			allActiveLanguages: FHC_JS_DATA_STORAGE_OBJECT.server_languages,
			// TODO: fetch types
			allAbgabeTypes: [
				{
					paabgabetyp_kurzbz: 'abstract',
					bezeichnung: 'Entwurf'
				},
				{
					paabgabetyp_kurzbz: 'zwischen',
					bezeichnung: 'Zwischenabgabe'
				},
				{
					paabgabetyp_kurzbz: 'note',
					bezeichnung: 'Benotung'
				},
				{
					paabgabetyp_kurzbz: 'end',
					bezeichnung: 'Endupload'
				},
				{
					paabgabetyp_kurzbz: 'enda',
					bezeichnung: 'Endabgabe im Sekretariat'
				}
			],
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
		openZusatzdatenModal(termin) {
				
		},
		saveTermin(termin) {
			const paabgabe_id = termin.paabgabe_id
			this.$fhcApi.factory.lehre.postProjektarbeitAbgabe(termin).then( (res) => {
				if(res?.meta?.status == 'success') {
					this.$fhcAlert.alertSuccess(this.$p.t('ui/gespeichert'))

					if(paabgabe_id === -1) { // new abgabe has been inserted
						termin.paabgabe_id = res?.data?.retval

						this.projektarbeit.abgabetermine.push({ // new abgatermin row

							'paabgabe_id': -1,
							'projektarbeit_id': this.projektarbeit.projektarbeit_id,
							'fixtermin': false,
							'kurzbz': '',
							'datum': new Date().toISOString().split('T')[0],
							'paabgabetyp_kurzbz': '',
							'bezeichnung': '',
							'abgabedatum': null,
							'insertvon': this.viewData?.uid ?? '',
							'allowedToSave': true,
							'allowedToDelete': true
						})
					}
					
					
				} else if(res?.meta?.status == 'error'){
					this.$fhcAlert.alertError()
				}
				
			})
		},
		deleteTermin(termin) {
			this.$fhcApi.factory.lehre.deleteProjektarbeitAbgabe(termin.paabgabe_id).then( (res) => {
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
			this.$fhcApi.factory.lehre.getStudentProjektarbeitAbgabeFile(termin.paabgabe_id, this.projektarbeit.student_uid)
		},
		dateDiffInDays(datum, today){
			const oneDayMs = 1000 * 60 * 60 * 24
			return Math.round((new Date(datum) - new Date(today)) / oneDayMs)
		},
		getDateStyle(termin) {
			const datum = new Date(termin.datum)
			const abgabedatum = new Date(termin.abgabedatum)

			// todo: rework styling but keep the color pattern logic
			// https://wiki.fhcomplete.info/doku.php?id=cis:abgabetool_fuer_studierende
			let color = 'white'
			let fontColor = 'black'
			if (termin.abgabedatum === null) {
				if(datum < today) {
					color = 'red'
					fontColor = 'white'
				} else if (datum > today && this.dateDiffInDays(datum, today) <= 12) {
					color = 'yellow'
				}
			} else if(abgabedatum > datum) {
				color = 'pink' // aka "hellrot"
				fontColor = 'white'
			} else {
				color = 'green'
			}

			return `font-color: ${fontColor} ; background-color: ${color}; border-radius: 50%;`
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
		formatDate(dateParam) {
			const date = new Date(dateParam)
			// handle missing leading 0
			const padZero = (num) => String(num).padStart(2, '0');

			const month = padZero(date.getMonth() + 1); // Months are zero-based
			const day = padZero(date.getDate());
			const year = date.getFullYear();

			return `${day}.${month}.${year}`;
		},
		openStudentPage() {
			const link = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router
				+ '/Cis/Abgabetool/Student/' + this.projektarbeit?.student_uid
			window.open(link, '_blank')
		},
		openPlagiatcheck() {
			// todo: hardcoded turnitin link pfui?
			const link = "https://technikum-wien.turnitin.com/sso/sp/redwood/saml/5IyfmBr2OcSIaWQTKlFCGj/start"
			window.open(link, '_blank')
		}
	},
	watch: {
		projektarbeit(newVal) {
			// default select german if projektarbeit sprache was null
			this.form.sprache = newVal.sprache ? this.allActiveLanguages.find(lang => lang.sprache == newVal.sprache) : this.allActiveLanguages.find(lang => lang.sprache == 'German')
			this.form.abstract = newVal.abstract
			this.form.abstract_en = newVal.abstract_en
			this.form.schlagwoerter = newVal.schlagwoerter
			this.form.schlagwoerter_en = newVal.schlagwoerter_en
			this.form.kontrollschlagwoerter = newVal.kontrollschlagwoerter
			this.form.seitenanzahl = newVal.seitenanzahl
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
		}
		
	},
	created() {

	},
	mounted() {

	},
	template: `
		<div v-if="projektarbeit">
		
			<h5>{{$p.t('abgabetool/c4abgabeMitarbeiterbereich')}}</h5>
			<div class="row">
				<div class="col-8">
					<p> {{projektarbeit?.student}}</p>
					<p> {{projektarbeit?.titel}}</p>
					<p v-if="projektarbeit?.zweitbegutachter"> {{projektarbeit?.zweitbegutachter}}</p>
				</div>
				<div class="col-4 d-flex justify-content-end">
					<div>
						<button :disabled="!getSemesterBenotbar && endUploadVorhanden" class="btn btn-secondary border-0" @click="openPlagiatcheck" style="margin-right: 4px;">
							benoten
							<i style="margin-left: 8px" class="fa-solid fa-user-check"></i>
						</button>
					</div>
					<button v-if="projektarbeit?.betreuerart_kurzbz !== 'Zweitbegutachter'" class="btn btn-secondary border-0" @click="openPlagiatcheck" style="margin-right: 4px;">
						zur Plagiatsprüfung
						<i style="margin-left: 8px" class="fa-solid fa-user-check"></i>
					</button>
					<button class="btn btn-secondary border-0" @click="openStudentPage">
						Studentenansicht
						<i style="margin-left: 8px" class="fa-solid fa-eye"></i>
					</button>
				</div>
			</div>
			<div id="uploadWrapper">
				<div class="row" style="margin-bottom: 12px;">
					<div style="width: 100px">{{$p.t('abgabetool/c4fixtermin')}}</div>
					<div class="col-2">{{$p.t('abgabetool/c4zieldatum')}}</div>
					<div class="col-2">{{$p.t('abgabetool/c4abgabetyp')}}</div>
					<div class="col-4">{{$p.t('abgabetool/c4abgabekurzbz')}}</div>
					<div class="col-1">{{$p.t('abgabetool/c4abgabedatum')}}</div>
					<div class="col">
						
					</div>
				</div>
				<div v-if="!projektarbeit?.abgabetermine?.length">keine Termine gefunden!</div>
				<div class="row" v-for="termin in projektarbeit.abgabetermine">
					<div style="width: 100px" class="d-flex justify-content-center align-items-center">
						<p class="fhc-bullet" :class="{ 'fhc-bullet-red': termin.fixtermin, 'fhc-bullet-green': !termin.fixtermin }"></p>
					</div>
					<div class="col-2 d-flex justify-content-center align-items-center">
						<div :style="getDateStyle(termin)">
							<VueDatePicker
								style="width: 95%;"
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
					<div class="col-2 d-flex justify-content-center align-items-center">
						<Dropdown 
							:style="{'width': '100%'}"
							:disabled="!termin.allowedToSave"
							v-model="termin.bezeichnung"
							:options="allAbgabeTypes"
							:optionLabel="getOptionLabelAbgabetyp">
						</Dropdown>
					</div>
					<div class="col-4 d-flex justify-content-center align-items-center">
						<Textarea style="margin-bottom: 4px;" v-model="termin.kurzbz" rows="3" cols="60" :disabled="!termin.allowedToSave"></Textarea>
					</div>
					<div class="col-1 d-flex justify-content-center align-items-center">
						{{ termin.abgabedatum?.split("-").reverse().join(".") }}
						<a v-if="termin?.abgabedatum" @click="downloadAbgabe(termin)" style="margin-left:4px; cursor: pointer;">
							<i class="fa-solid fa-file-pdf"></i>
						</a>
					</div>
					<div class="col-2 align-content-center">
						<div class="row">
							<div class="col-6">
								<button v-if="termin.allowedToSave" class="btn btn-primary border-0" @click="saveTermin(termin)">
									Speichern
									<i style="margin-left: 8px" class="fa-solid fa-floppy-disk"></i>
								</button>
							</div>
							<div class="col-6">
								<button v-if="termin.allowedToDelete && termin.paabgabe_id > 0" class="btn btn-primary border-0" @click="deleteTermin(termin)">
									Löschen
									<i style="margin-left: 8px" class="fa-solid fa-trash"></i>
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		 </div>
		 <bs-modal ref="modalContainerEnduploadZusatzdaten" class="bootstrap-prompt" dialogClass="modal-lg">
			<template v-slot:title>
				<div>
					{{$p.t('abgabetool/c4enduploadZusatzdaten')}}
				</div>
				<div class="row mb-3 align-items-center">
					
					<p class="ml-4 mr-4">Student UID: {{ projektarbeit?.student_uid}}</p>
				
				</div>
				<div class="row mb-3 align-items-center">
					
					<p class="ml-4 mr-4">Titel: {{ projektarbeit?.titel }}</p>
				
				</div>
			</template>
			<template v-slot:default>
				<div class="row mb-3 align-items-center">
					<div class="row">{{$p.t('abgabetool/c4Sprache')}}</div>
					<div class="row">
						<Dropdown 
							:style="{'width': '100%'}"
							v-model="form.sprache"
							:options="allActiveLanguages"
							:optionLabel="getOptionLabelSprache">
						</Dropdown>
					</div>
				</div>
				
				<div class="row mb-3 align-items-center">
					<div class="row">Kontrollierte Schlagwörter</div>
					<div class="row">
						<Textarea v-model="form.kontrollschlagwoerter"></Textarea>
					</div>
				</div>
				<div class="row mb-3 align-items-center">
					<div class="row">{{$p.t('abgabetool/c4schlagwoerterGer')}}</div>
					<div class="row">
						<Textarea v-model="form.schlagwoerter"></Textarea>
					</div>
				</div>
				
				<div class="row mb-3 align-items-center">
					<div class="row">{{$p.t('abgabetool/c4schlagwoerterEng')}}</div>
					<div class="row">
						<Textarea v-model="form.schlagwoerter_en"></Textarea>
					</div>
				</div>
				
				<div class="row mb-3 align-items-center">
					<div class="row">{{$p.t('abgabetool/c4abstractGer')}}</div>
					<div class="row">
						<Textarea v-model="form.abstract" rows="10"></Textarea>
					</div>
				</div>

				<div class="row mb-3 align-items-center">
					<div class="row">{{$p.t('abgabetool/c4abstractEng')}}</div>
					<div class="row">
						<Textarea v-model="form.abstract_en" rows="10"></Textarea>
					</div>				
				</div>
				
				<div class="row mb-3 align-items-center">
					<div class="row">{{$p.t('abgabetool/c4seitenanzahl')}}</div>
					<div class="row">
						<InputNumber 
							v-model="form.seitenanzahl"
							inputId="seitenanzahlInput" :min="1" :max="100000">
						</InputNumber>
					</div>		
				</div>
				
				<div v-if="projektarbeit">
					<div v-html="getEid"></div>
					<div class="row">
						<div class="col-9"></div>
						<div class="col-2"><p>{{ $p.t('abgabetool/c4gelesenUndAkzeptiert') }}</p></div>
						<div class="col-1">
							
							<Checkbox 
								v-model="eidAkzeptiert" 
								:binary="true" 
								:pt="{ root: { class: 'ml-auto' }}"
							>
							</Checkbox>
						</div>
					</div>
				</div>
				
			</template>
			<template v-slot:footer>
				<button class="btn btn-primary" :disabled="getEnduploadErlaubt" @click="triggerEndupload">{{$p.t('ui/hochladen')}}</button>
			</template>
		</bs-modal>

`,
};

export default AbgabeMitarbeiterDetail;
