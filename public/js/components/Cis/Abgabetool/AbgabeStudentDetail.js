import Upload from '../../../components/Form/Upload/Dms.js';
import BsModal from '../../Bootstrap/Modal.js';
import VueDatePicker from '../../vueDatepicker.js.php';

const today = new Date()
export const AbgabeStudentDetail = {
	name: "AbgabeStudentDetail",
	components: {
		Upload,
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
		},
		viewMode: {
			type: Boolean,
			default: false
		}
	},
	data() {
		return {
			eidAkzeptiert: false,
			enduploadTermin: null,
			allActiveLanguages: FHC_JS_DATA_STORAGE_OBJECT.server_languages,
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
		validate: function(termin) {
			if(!termin.file.length) {
				this.$fhcAlert.alertWarning(this.$p.t('global/warningChooseFile'));
				return false
			}

			return true;
		},
		triggerEndupload() {
			if (!this.validate(this.enduploadTermin))
			{
				return false;
			}
			
			// post endabgabe
			const formData = new FormData();
			formData.append('paabgabetyp_kurzbz', this.enduploadTermin.paabgabetyp_kurzbz)
			formData.append('projektarbeit_id', this.enduploadTermin.projektarbeit_id);
			formData.append('paabgabe_id', this.enduploadTermin.paabgabe_id)
			formData.append('student_uid', this.projektarbeit.student_uid)
			formData.append('bperson_id', this.projektarbeit.bperson_id)
			
			// TODO: validate/check for null etc.
			formData.append('sprache', this.form['sprache'].sprache)
			formData.append('abstract', this.form['abstract'])
			formData.append('abstract_en', this.form['abstract_en'])
			formData.append('schlagwoerter', this.form['schlagwoerter'])
			formData.append('schlagwoerter_en', this.form['schlagwoerter_en'])
			formData.append('seitenanzahl', this.form['seitenanzahl'])
			
			for (let i = 0; i < this.enduploadTermin.file.length; i++) {
				formData.append('file', this.enduploadTermin.file[i]);
			}
			this.$api.call(ApiAbgabe.postStudentProjektarbeitEndupload(formData))
				.then(res => {
					this.handleUploadRes(res)
				})
			
			this.$refs.modalContainerEnduploadZusatzdaten.hide()
		},
		downloadAbgabe(termin) {
			// TODO: test
			this.$api.call(ApiAbgabe.getStudentProjektarbeitAbgabeFile(termin.paabgabe_id, this.projektarbeit.student_uid))
			// this.$fhcApi.factory.lehre.getStudentProjektarbeitAbgabeFile(termin.paabgabe_id, this.projektarbeit.student_uid)
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
		upload(termin) {

			if (!this.validate(termin))
			{
				return false;
			}
			
			if(termin.bezeichnung === 'Endupload') {
				// open endupload form modal for further inputs
				this.enduploadTermin = termin
				this.$refs.modalContainerEnduploadZusatzdaten.show()
			} else {
				const formData = new FormData();
				formData.append('paabgabetyp_kurzbz', termin.paabgabetyp_kurzbz)
				formData.append('projektarbeit_id', this.projektarbeit.projektarbeit_id)
				formData.append('paabgabe_id', termin.paabgabe_id)
				formData.append('student_uid', this.projektarbeit.student_uid)
				formData.append('bperson_id', this.projektarbeit.bperson_id)
				
				for (let i = 0; i < termin.file.length; i++) {
					formData.append('file', termin.file[i]);
				}

				this.$api.call(ApiAbgabe.postStudentProjektarbeitZwischenabgabe(formData))
					.then(res => {
						this.handleUploadRes(res)
					})
			}
		},
		handleUploadRes(res) {
			if(res.meta.status == "success") {
				this.$fhcAlert.alertSuccess('File erfolgreich hochgeladen')
			} else {
				this.$fhcAlert.alertError('File upload error')
			}
			
			if(res.meta.signaturInfo) {
				this.$fhcAlert.alertInfo(res.meta.signaturInfo)
			}
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
		getOptionLabel(option) {
			return option.sprache
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
		}
	},
	created() {

	},
	mounted() {

	},
	template: `
		<div v-if="projektarbeit">
		
			<h5>{{$p.t('abgabetool/c4abgabeStudentenbereich')}}</h5>
			<div class="row">
				<p> {{projektarbeit?.betreuer}}</p>
				<p> {{projektarbeit?.titel}}</p>
			</div>
			<div id="uploadWrapper">
				<div class="row" style="margin-bottom: 12px;">
					<div class="col-1 fw-bold text-center">{{$p.t('abgabetool/c4fixtermin')}}</div>
					<div class="col-2 fw-bold">{{$p.t('abgabetool/c4zieldatum')}}</div>
					<div class="col-2 fw-bold">{{$p.t('abgabetool/c4abgabetyp')}}</div>
					<div class="col-3 fw-bold">{{$p.t('abgabetool/c4abgabekurzbz')}}</div>
					<div class="col-1 fw-bold text-center">{{$p.t('abgabetool/c4abgabedatum')}}</div>
					<div class="col-3 fw-bold">
						{{$p.t('abgabetool/c4fileupload')}}
					</div>
				</div>
				<div class="row" v-for="termin in projektarbeit.abgabetermine">
					<div class="col-1 d-flex justify-content-center align-items-start">
						<i v-if="termin.fixtermin" class="fa-solid fa-2x fa-circle-check fhc-bullet-red"></i>
						<i v-else="" class="fa-solid fa-2x fa-circle-xmark fhc-bullet-green"></i>
<!--
						<p class="fhc-bullet" :class="{ 'fhc-bullet-red': termin.fixtermin, 'fhc-bullet-green': !termin.fixtermin }"></p>
-->
					</div>
					<div class="col-2 d-flex justify-content-start align-items-start">
						<div class="position-relative" :class="getDateStyle(termin)">
							<VueDatePicker
								v-model="termin.datum"
								:clearable="false"
								:disabled="true"
								:enable-time-picker="false"
								:format="formatDate"
								:text-input="true"
								auto-apply>
							</VueDatePicker>
							<i class="position-absolute abgabe-zieldatum-overlay fa-solid fa-2x" :class="getDateStyle(termin, 'icon')"></i>
						</div>
					</div>
					<div class="col-2 d-flex justify-content-start align-items-start">{{ termin.bezeichnung }}</div>
					<div class="col-3 d-flex justify-content-start align-items-start">
						<Textarea style="margin-bottom: 4px;" v-model="termin.kurzbz" rows="3" cols="45" :disabled="true"></Textarea>
					</div>
					<div class="col-1 d-flex flex-column justify-content-start align-items-center">
						{{ termin.abgabedatum?.split("-").reverse().join(".") }}
						<a v-if="termin?.abgabedatum" @click="downloadAbgabe(termin)" style="margin-left:4px; cursor: pointer;">
							<i class="fa-solid fa-3x fa-file-pdf"></i>
						</a>
					</div>
					<div class="col-3" v-if="!viewMode">
						<div class="row">
							<div class="col-8">
								<Upload v-if="termin && termin.allowedToUpload" accept=".pdf" v-model="termin.file"></Upload>
							</div>
							<div class="col-4">
								<button class="btn btn-primary border-0" @click="upload(termin)" :disabled="!termin.allowedToUpload">
									Upload
									<i style="margin-left: 8px" class="fa-solid fa-upload"></i>
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
				<div class="row mb-3 align-items-start">
					
					<p class="ml-4 mr-4">Student UID: {{ projektarbeit?.student_uid}}</p>
				
				</div>
				<div class="row mb-3 align-items-start">
					
					<p class="ml-4 mr-4">Titel: {{ projektarbeit?.titel }}</p>
				
				</div>
			</template>
			<template v-slot:default>
				<div class="row mb-3 align-items-start">
					<div class="row">{{$p.t('abgabetool/c4Sprache')}}</div>
					<div class="row">
						<Dropdown 
							:style="{'width': '100%'}"
							v-model="form.sprache"
							:options="allActiveLanguages"
							:optionLabel="getOptionLabel">
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
					<div class="row">{{$p.t('abgabetool/c4schlagwoerterGer')}}</div>
					<div class="row">
						<Textarea v-model="form.schlagwoerter"></Textarea>
					</div>
				</div>
				
				<div class="row mb-3 align-items-start">
					<div class="row">{{$p.t('abgabetool/c4schlagwoerterEng')}}</div>
					<div class="row">
						<Textarea v-model="form.schlagwoerter_en"></Textarea>
					</div>
				</div>
				
				<div class="row mb-3 align-items-start">
					<div class="row">{{$p.t('abgabetool/c4abstractGer')}}</div>
					<div class="row">
						<Textarea v-model="form.abstract" rows="10"></Textarea>
					</div>
				</div>

				<div class="row mb-3 align-items-start">
					<div class="row">{{$p.t('abgabetool/c4abstractEng')}}</div>
					<div class="row">
						<Textarea v-model="form.abstract_en" rows="10"></Textarea>
					</div>				
				</div>
				
				<div class="row mb-3 align-items-start">
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

export default AbgabeStudentDetail;
