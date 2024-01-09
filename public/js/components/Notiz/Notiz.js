import VueDatePicker from '../vueDatepicker.js.php';
import File from '../Form/Upload/File.js';

export default {
	components: {
		VueDatePicker,
		File
	},
	props: [
		'typeId',
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
	data(){
		return {
			multiupload: true
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
/*	mounted(){
		showZuordnung(){
			this.idTyp = 'Person';
		}
	},*/
	methods: {
/*		handleFileChange(event) {
			this.intAnhang = event.target.files;
		},*/
		reset() {
			this.$refs.form.reset();
			//this.$emit('update:anhang', dt.files);
		},
/*		deleteFile(id){
			//console.log("Delete file with id " + id );

			//console.log(this.intAnhang[id]);

			const dt = new DataTransfer();
			const files = this.$refs.upload.files;

			for (let i = 0; i < files.length; i++) {
				const file = files[i];
				if (id !== i)
					dt.items.add(file); // here you exclude the file. thus removing it.
			}

			this.$refs.upload.files = dt.files; // Assign the updates list
			this.$emit('update:anhang', dt.files);
		}*/
	},

	template: `
<div>
	<form ref="form" @submit.prevent class="row">
		<div>
			<div class="row mb-3">
				<b>{{action}}
				<div class="col-sm-7">
					</b><span class="small">[{{this.typeId}}]</span>
				</div>	
			</div>
<!--			<div>
				<p>Zuordnung {{this.typeId}}</p>
			</div>-->
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

<!--					<div  class="col-sm-7">					
						<span>
						
						  <input ref="upload" type="file" multiple ref="fileInput" @change="handleFileChange" v-model="intAnhang"/>
													
						</span>
						
						<span >					
							<ul>							
								<li v-for="(anh,index) in intAnhang">
									<button>{{anh.name}}</button><button class="text-danger" @click="deleteFile(index)">X {{index}}</button>
								</li>
							</ul>
						</span>
					</div>
				</div>-->
				
				<hr>

				
			</slot>
	</div>	

		<div v-if="showErweitert">
			<slot name="erweitert">	
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
						<input type="text" v-model="intBearbeiter" class="form-control">	
					</div>
				</div>
							
				<div class="row mb-3">
					<label for="von" class="form-label col-sm-2">von</label>
					<div class="col-sm-2">
						<vue-date-picker id="von" v-model="intVon" clearable="false" auto-apply enable-time-picker="true" format="dd.MM.yyyy" preview-format="dd.MM.yyyy"></vue-date-picker>
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

