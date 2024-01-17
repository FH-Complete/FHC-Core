import VueDatePicker from '../vueDatepicker.js.php';
import PvAutoComplete from "../../../../index.ci.php/public/js/components/primevue/autocomplete/autocomplete.esm.min.js";
import File from '../Form/Upload/File.js';
import {CoreRESTClient} from "../../RESTClient";

export default {
	components: {
		VueDatePicker,
		File,
		PvAutoComplete
	},
	props: [
		'typeId',
		'titel',
		'text',
		'lastChange',
		'von',
		'bis',
		'statusNew',
		'document',
		'erledigt',
		'verfasser',
		'bearbeiter',
		'showErweitert',
		'showDocument',
		'anhang'
		],
	data(){
		return {
			multiupload: true,
			mitarbeiter: [],
			filteredMitarbeiter: [],
/*			filteredFirmen: []*/
		}
	},
	computed: {
		intTitel: {
			get() {
				return this.titel;
			},
			set(value) {
				this.$emit('update:titel', value);
			}
		},
		intText: {
			get() {
				return this.text;
			},
			set(value) {
				this.$emit('update:text', value);
			}
		},
/*		intLastChange: {
			get() {
				return this.lastChange;
			},
			set(value) {
				this.$emit('update:lastChange', value);
			}
		},*/
		intVon: {
			get() {
				return this.von;
			},
			set(value) {
				const tempVon = new Date(value).toISOString().split('T')[0];
				this.$emit('update:von', tempVon);
			}
		},
		intBis: {
			get() {
				return this.bis;
			},
			set(value) {
				const tempBis = new Date(value).toISOString().split('T')[0];
				this.$emit('update:bis', tempBis);
			}
		},
		intDocument: {
			get() {
				return this.document;
			},
			set(value) {
				this.$emit('update:document', value);
			}
		},
		intErledigt: {
			get() {
				return this.erledigt;
			},
			set(value) {
				this.$emit('update:erledigt', value);
			}
		},
		intVerfasser: {
			get() {
				return this.verfasser;
			},
			set(value) {
				//this.$emit('update:verfasser', value);
				this.$emit('update:verfasser', value.mitarbeiter_uid);
			}
		},
		intBearbeiter: {
			get() {
				return this.bearbeiter;
			},
			set(value) {
				if(value)
				{
					this.$emit('update:bearbeiter', value.mitarbeiter_uid);
				}
				else
					this.$emit('update:bearbeiter', value);
			}
		},
		intAnhang: {
			get() {
				return this.anhang;
			},
			set(value) {
				//console.log(value);
				this.$emit('update:anhang', value);
			}
		}
	},
	methods: {
		reset() {
			this.$refs.form.reset();
			this.intAnhang = null;
			//this.$emit('update:anhang', []);
		},

		search(event) {
			return CoreRESTClient
				.get('components/stv/Notiz/getMitarbeiter/' + event.query)
				.then(result => {
					//console.log(result);
					this.filteredMitarbeiter = CoreRESTClient.getData(result.data);
				});
		},
	},

	template: `
	<div class="notiz-notiz">
		<span v-for="(anhang,index) in intAnhang"> {{anhang.name}} {{index}}<br></span>
		<form ref="form" @submit.prevent class="row">
			<div>
				<div class="row mb-3">
					<div class="col-sm-7">
						<span class="small">[{{this.typeId}}]</span>
					</div>
				</div>
				
				<div class="row mb-3">
					<div class="col-sm-7">
						<p v-if="statusNew" class="fw-bold">Neue Notiz</p>
						<p v-else class="fw-bold">Notiz bearbeiten</p>
					</div>
				</div>
	
				<div class="notizTitle row mb-3">
					<label for="titel" class="form-label col-sm-2">Titel</label>
					<div class="col-sm-7">
						<input type="text" v-model="intTitel" class="form-control">
					</div>
				</div>
				
				<div class="row mb-3">
					<label for="text" class="form-label col-sm-2">Text</label>
					<div class="col-sm-7">
						<textarea rows="5" cols="75" v-model="intText" class="form-control"></textarea>
					</div>
				</div>
				
			</div>
	
			<!-- show Documentupload-->
			<div v-if="showDocument">
				<div class="row mb-3">
					<label for="text" class="form-label col-sm-2">Dokument</label>
					
				<!-- File component-->
				<div  class="col-sm-7">
					<File ref="upload" id="file" :multiupload="multiupload" v-model:dateien="intAnhang" ></File>
				</div>
				<hr>
			</div>
			
			<!-- show Details-->
			<div v-if="showErweitert">
			
				<div class="row mb-3">
					<label for="bis" class="form-label col-sm-2">VerfasserIn</label>
					<div class="col-sm-3">
						<PvAutoComplete v-model="intVerfasser" optionLabel="mitarbeiter"  :suggestions="filteredMitarbeiter" @complete="search" minLength="3"/>
					</div>
					
					<label for="von" class="form-label col-sm-1">gültig von</label>
					<div class="col-sm-3">
						<vue-date-picker
							id="von"
							v-model="intVon"
							clearable="false"
							auto-apply
							:enable-time-picker="false"
							format="dd.MM.yyyy"
							preview-format="dd.MM.yyyy"></vue-date-picker>
					</div>
				</div>
				
				<div class="row mb-3">
					<label for="bis" class="form-label col-sm-2">BearbeiterIn</label>
					<div class="col-sm-3">
						<PvAutoComplete v-model="intBearbeiter" optionLabel="mitarbeiter"  :suggestions="filteredMitarbeiter" @complete="search" minLength="3"/>
					</div>
					
					<label for="bis" class="form-label col-sm-1">gültig bis</label>
					<div class="col-sm-3">
						<vue-date-picker
							id="bis"
							v-model="intBis"
							clearable="false"
							auto-apply
							:enable-time-picker="false"
							format="dd.MM.yyyy"
							preview-format="dd.MM.yyyy"></vue-date-picker>
					</div>
					
				</div>
									
				<div class="row mb-3">
					<label for="bis" class="form-label col-sm-2">erledigt</label>
					<div class="col-sm-1">
						<input type="checkbox" v-model="intErledigt">
					</div>
				</div>
			</div>
			
			<div class="row mb-3">
				<label for="lastChange" class="form-label col-sm-2 small">letzte Änderung</label>
				<div class="col-sm-7">
<!--					<input v-model="lastChange" >-->
					<p class="small">{{this.lastChange}}</p>
				</div>
			</div>
			
		</form>

	</div>`
}

