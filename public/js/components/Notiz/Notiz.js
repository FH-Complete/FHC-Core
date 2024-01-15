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
			filteredFirmen: []
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
				this.$emit('update:verfasser', value);
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
					<b>{{action}}</b>
					<div class="col-sm-7">
						<span class="small">[{{this.typeId}}]</span>
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
	
	<div v-if="showDocument">
			<slot name="document">
				<div class="row mb-3">
					<label for="text" class="form-label col-sm-2">Dokument</label>
					
				<!-- Component File-->
				<div  class="col-sm-7">		
					<File ref="upload" id="file" :multiupload="multiupload" v-model:dateien="intAnhang" ></File>
				</div>
			
				<hr>

				
			</slot>
	</div>	

		<div v-if="showErweitert">
<!--			<slot name="erweitert">	-->
				
				<div class="row mb-3">
					<label for="bis" class="form-label col-sm-2">VerfasserIn</label>
					<div class="col-sm-7">
						<input type="text" v-model="intVerfasser" class="form-control">	
						{{uid}}
					</div>
				</div>
				
				<div class="row mb-3">
					<label for="bis" class="form-label col-sm-2">BearbeiterIn</label>
					<div class="col-sm-7">
						<PvAutoComplete v-model="intBearbeiter" optionLabel="mitarbeiter"  :suggestions="filteredMitarbeiter" @complete="search" minLength="3"/>
<!--						<input type="text" v-model="intBearbeiter" class="form-control">	-->
					</div>
					
				</div>
									
				<div class="row mb-3">
					<label for="von" class="form-label col-sm-2">von</label>
					<div class="col-sm-2">
						<vue-date-picker 
							id="von" 
							v-model="intVon" 
							clearable="false" 
							auto-apply
							:enable-time-picker="false"
							format="dd.MM.yyyy" 
							preview-format="dd.MM.yyyy"></vue-date-picker>
					</div>
	
					<label for="bis" class="form-label col-sm-1">bis</label>
					<div class="col-sm-2">
						<vue-date-picker 
							id="bis" 
							v-model="intBis" 
							clearable="false" 
							auto-apply 
							:enable-time-picker="false"
							format="dd.MM.yyyy" 
							preview-format="dd.MM.yyyy"></vue-date-picker>
					</div>
	
					<label for="bis" class="form-label col-sm-1">erledigt</label>
					<div class="col-sm-1"> 
						<input type="checkbox" v-model="intErledigt">	
					</div>
				</div>
<!--			</slot>-->
		
	</div>
		
		
	</form>
	
</div>`
}

