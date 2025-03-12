import Upload from '../../../components/Form/Upload/Dms.js';
import BsModal from '../../Bootstrap/Modal.js';

const today = new Date()
export const AbgabeDetail = {
	name: "AbgabeDetail",
	components: {
		Upload,
		BsModal
	},
	props: {
		projektarbeit: {
			type: Object,
			default: null
		}
	},
	data() {
		return {
			file: []
		}
	},
	methods: {
		triggerUpload() {
			// todo: trigger the loadup

			this.$refs.modalContainerEnduploadZusatzdaten.hide()
		},
		upload(termin) {
			console.log(termin)
			// TODO load it up
			
			if(termin.bezeichnung === 'Endupload') {
				// open endupload form modal and await that it will be sent & checked

				this.$refs.modalContainerEnduploadZusatzdaten.show()
			}
		},
		dateDiffInDays(datum, today){
			const oneDayMs = 1000 * 60 * 60 * 24
			return Math.round((new Date(datum) - new Date(today)) / oneDayMs)
		},
		getDateStyle(termin) {
			const datum = new Date(termin.datum)
			const abgabedatum = new Date(termin.abgabedatum)
			
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
		}
	},
	watch: {
		'file'(newVal) {
			if(newVal == [] || newVal === null || newVal === undefined) return

			// check filetype on input change
			const file = newVal[0]
			if(!file) return

			if(file.type && file.type.includes('pdf')) {
				// all fine
			} else {
				// clear and alert for filetypes
				this.$fhcAlert.alertInfo(this.$p.t('abgabetool/c4allowedFileTypes'))
				this.entschuldigung.files = []
			}

		}
	},
	computed: {

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
					<div class="col">{{$p.t('abgabetool/c4fixtermin')}}</div>
					<div class="col">{{$p.t('abgabetool/c4zieldatum')}}</div>
					<div class="col">{{$p.t('abgabetool/c4abgabetyp')}}</div>
					<div class="col">{{$p.t('abgabetool/c4abgabekurzbz')}}</div>
					<div class="col">{{$p.t('abgabetool/c4abgabedatum')}}</div>
					<div class="col">
						{{$p.t('abgabetool/c4fileupload')}}
					</div>
					<div class="col">
					</div>
				</div>
				<div class="row" v-for="termin in projektarbeit.abgabetermine">
					<div class="col d-flex justify-content-center align-items-center">
						<p class="fhc-bullet" :class="{ 'fhc-bullet-red': termin.fixtermin, 'fhc-bullet-green': !termin.fixtermin }"></p>
					</div>
					<div class="col" :style="getDateStyle(termin)">{{ termin.datum?.split("-").reverse().join(".") }}</div>
					<div class="col">{{ termin.bezeichnung }}</div>
					<div class="col">{{ termin.kurzbz }}</div>
					<div class="col">{{ termin.abgabedatum?.split("-").reverse().join(".") }}</div>
					<div class="col">
<!--						<Upload accept=".pdf" v-model="file"></Upload>-->
					</div>
					<div class="col">
						<button class="btn btn-primary border-0" @click="upload(termin)">
							Upload
							<i style="margin-left: 8px" class="fa-solid fa-upload"></i>
						</button>
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
					
					student uid
				
				</div>
				<div class="row mb-3 align-items-center">
					
					{{ projektarbeit?.titel }}
				
				</div>
			</template>
			<template v-slot:default>
				<div class="row mb-3 align-items-center">
					
					Sprache der Arbeit
				
				</div>
				<div class="row mb-3 align-items-center">
					
					Kontrollierte Schlagwörter
				
				</div>
				<div class="row mb-3 align-items-center">
					
					Dt. Schlagwörter
				
				</div>
				<div class="row mb-3 align-items-center">
					
					Engl. Schlagwörter
				
				</div>
				<div class="row mb-3 align-items-center">
					
					Abstract max 5k characters
				
				</div>
				<div class="row mb-3 align-items-center">
					
					Abstract eng max 5k chars
				
				</div>
				<div class="row mb-3 align-items-center">
					
					Seitenanzahl
				
				</div>
			</template>
			<template v-slot:footer>
				<button class="btn btn-primary" @click="triggerUpload">{{$p.t('ui/hochladen')}}</button>
			</template>
		</bs-modal>
	 	
    `,
};

export default AbgabeDetail;
