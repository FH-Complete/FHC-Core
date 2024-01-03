import VueDatePicker from '../vueDatepicker.js.php';
//import SingleFile from '../Form/Upload/SingleFile.js';

export default {
	components: {
		VueDatePicker,
		//BsModal
		//SingleFile
	},
	props: [
		'titel',
		'text',
		'von',
		'bis',
		'action',
		'document',
		'erledigt',
		'verfasser',
		'bearbeiter',
		'showErweitert',
		'showDocument',
		'anhang'
		],
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
				this.$emit('update:von', value);
			}
		},
		intBis: {
			get() {
				return this.bis;
			},
			set(value) {
				this.$emit('update:bis', value);
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
				this.$emit('update:bearbeiter', value);
			}
		},
		intAnhang: {
			get() {
				return this.anhang;
			},
			set(value) {
				console.log(value);
				this.$emit('update:anhang', value);
			}
		}
	},
	methods: {
		handleFileChange(event) {
			//single
			this.intAnhang = event.target.files[0];

			//multiple
			//this.intAnhang = event.target.files;
		},
	},
	template: `
<div>

component: {{intTitel}} {{intVon}} || {{intAnhang.name}} {{intAnhang}}
	<form class="row">
		<div>
			<div class="row mb-3">
				<b>{{action}}</b>
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
					
					<!--Todo(Manu) Component SingleFile-->
					<!--<single-file id="file" v-model="intDocument"></single-file>-->

					<div  class="col-sm-7">					
						<span>
						  <input type="file" multiple ref="fileInput" @change="handleFileChange" v-model="intAnhang"/>
	<!--      							<button type="submit">Upload</button>-->
								<div v-if="intAnhang.name">
								  <p>Selected File: {{ intAnhang.name }}</p>
								  <button class="text-danger">X</button>
								</div>
						</span>
						
<!--						Anzeige outputs-->
						<span >					
							<ul>							
								<li v-for="anh in anhang">
									<button>{{anh.name}}</button><button class="text-danger">X</button>
								</li>
							</ul>
						</span>
					</div>
				</div>
				
			</slot>
	</div>	

		<div v-if="showErweitert">
			<slot name="erweitert">	
				<div class="row mb-3">
					<label for="bis" class="form-label col-sm-2">VerfasserIn</label>
					<div class="col-sm-7">
						<input type="text" v-model="intVerfasser" class="form-contrsol">	
						{{uid}}
					</div>
				</div>
				
				<div class="row mb-3">
					<label for="bis" class="form-label col-sm-2">BearbeiterIn</label>
					<div class="col-sm-7">
						<input type="text" v-model="intBearbeiter" class="form-control">	
					</div>
				</div>
							
				<div class="row mb-3">
					<label for="von" class="form-label col-sm-2">von</label>
					<div class="col-sm-2">
						<vue-date-picker id="von" v-model="intVon" clearable="false" auto-apply enable-time-picker="true" format="Y-m-d" preview-format="dd.MM.yyyy"></vue-date-picker>
					</div>
	
					<label for="bis" class="form-label col-sm-1">bis</label>
					<div class="col-sm-2">
						<vue-date-picker id="bis" v-model="intBis" clearable="false" auto-apply enable-time-picker="true" format="dd.MM.yyyy" preview-format="dd.MM.yyyy"></vue-date-picker>
					</div>
	
					<label for="bis" class="form-label col-sm-1">erledigt</label>
					<div class="col-sm-1"> 
						<input type="checkbox" v-model="intErledigt">	
					</div>
				</div>
			</slot>
		
	</div>
		
		
	</form>
</div>`
}

