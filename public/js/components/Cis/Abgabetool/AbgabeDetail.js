import Upload from '../../../components/Form/Upload/Dms.js';
import BsModal from '../../Bootstrap/Modal.js';

const today = new Date()
export const AbgabeDetail = {
	name: "AbgabeDetail",
	components: {
		Upload,
		BsModal,
		InputNumber: primevue.inputnumber,
		Checkbox: primevue.checkbox,
		Dropdown: primevue.dropdown,
		Textarea: primevue.textarea
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
			this.$fhcApi.factory.lehre.postStudentProjektarbeitEndupload(formData)
				.then(res => {
					this.handleUploadRes(res)
				})
			
			this.$refs.modalContainerEnduploadZusatzdaten.hide()
		},
		downloadAbgabe(termin) {
			this.$fhcApi.factory.lehre.getStudentProjektarbeitAbgabeFile(termin.paabgabe_id, this.projektarbeit.student_uid)
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
				this.$fhcApi.factory.lehre.postStudentProjektarbeitZwischenabgabe(formData)
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
			
			return 'font-color: ' + fontColor + '; background-color: ' + color
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
					<div style="width: 100px">{{$p.t('abgabetool/c4fixtermin')}}</div>
					<div class="col-1">{{$p.t('abgabetool/c4zieldatum')}}</div>
					<div class="col-1">{{$p.t('abgabetool/c4abgabetyp')}}</div>
					<div class="col-2">{{$p.t('abgabetool/c4abgabekurzbz')}}</div>
					<div class="col-1">{{$p.t('abgabetool/c4abgabedatum')}}</div>
					<div class="col">
						{{$p.t('abgabetool/c4fileupload')}}
					</div>
				</div>
				<div class="row" v-for="termin in projektarbeit.abgabetermine">
					<div style="width: 100px" class="d-flex justify-content-center align-items-center">
						<p class="fhc-bullet" :class="{ 'fhc-bullet-red': termin.fixtermin, 'fhc-bullet-green': !termin.fixtermin }"></p>
					</div>
					<div class="col-1 d-flex justify-content-center align-items-center">
						<div :style="getDateStyle(termin)">
							{{ termin.datum?.split("-").reverse().join(".") }}
						</div>				
					</div>
					<div class="col-1 d-flex justify-content-center align-items-center">{{ termin.bezeichnung }}</div>
					<div class="col-2 d-flex justify-content-center align-items-center">{{ termin.kurzbz }}</div>
					<div class="col-1 d-flex justify-content-center align-items-center">
						{{ termin.abgabedatum?.split("-").reverse().join(".") }}
						<a v-if="termin?.abgabedatum" @click="downloadAbgabe(termin)" style="cursor: pointer;">
							<i class="fa-solid fa-file-pdf"></i>
						</a>
					</div>
					<div class="col-6">
						<div class="row">
							<div class="col-6">
								<Upload v-if="termin && termin.allowedToUpload" accept=".pdf" v-model="termin.file"></Upload>
							</div>
							<div class="col-6">
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
							:optionLabel="getOptionLabel">
						</Dropdown>
					</div>
				</div>
				
<!--				 lektor fills these out-->
<!--				<div class="row mb-3 align-items-center">-->
<!--					<div class="row">Kontrollierte Schlagwörter</div>-->
<!--					<div class="row">-->
<!--						<Textarea v-model="form.kontrollschlagwoerter"></Textarea>-->
<!--					</div>-->
<!--					-->
<!--				-->
<!--				</div>-->
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

export default AbgabeDetail;
