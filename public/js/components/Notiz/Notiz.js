import VueDatePicker from '../vueDatepicker.js.php';
import PvAutoComplete from "../../../../index.ci.php/public/js/components/primevue/autocomplete/autocomplete.esm.min.js";
import File from '../Form/Upload/File.js';
import FormUploadDms from '../Form/Upload/Dms.js';
import {CoreRESTClient} from "../../RESTClient";

export default {
	components: {
		VueDatePicker,
		File,
		PvAutoComplete,
		FormUploadDms
	},
	props: [
		'typeId',
		'titel',
		'propText',
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
			zwischenvar: null
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
/*		intPropText: {
			get() {
				return this.propText;
			},
			set(value) {
				this.$emit('update:propText', value);
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
		},

		search(event) {
			return CoreRESTClient
				.get('components/stv/Notiz/getMitarbeiter/' + event.query)
				.then(result => {
					//console.log(result);
					this.filteredMitarbeiter = CoreRESTClient.getData(result.data);
				});
		},
		initTinyMCE() {
			tinymce.init({
				selector: '#editor',
				//height: 800,
				plugins: ['autoresize', 'lists'],
				toolbar: "undo redo | blocks | bold italic | alignleft aligncenter alignright alignjustify | outdent indent",

				autoresize_bottom_margin: 16,
				setup: (editor) => {

/*					editor.on('input', () => {
						this.$emit('update:propText', editor.getContent());
					});*/

					// workaround for avoiding conflict id of frame: same Id as textarea
					// working just for new notes
					//Todo(manu) find a solution for update notes
					editor.on('input', () => {
						this.intText = editor.getContent();
						//this.$emit('update:intText', editor.getContent());

					});
				},
			});
		},
		updateContent(event) {
			// Manually update the content when the textarea is edited
			tinymce.get('editor').setContent(event.target.value);
			console.log(event.target.value);
		},
	},
	mounted() {
		this.initTinyMCE();
	},
	beforeDestroy() {
		tinymce.get('editor').destroy();
	},
	template: `
	<div class="notiz-notiz">
<!--	<p>testausgaben child</p>
		<span v-for="(anhang,index) in intAnhang"> {{anhang.name}} {{index}}<br></span>-->
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
	
				<div class="row mb-3">
					<label for="titel" class="form-label col-sm-2">Titel</label>
					<div class="col-sm-7">
						<input type="text" v-model="intTitel" class="form-control">
					</div>
				</div>
							
				<div class="row mb-3">
					<label for="text" class="form-label col-sm-2">Text</label>
					
					<!--tinymce-->
					<div class="col-sm-7">
<!--						<textarea id="editor" rows="5" cols="75" @input="updateContent" class="form-control">{{ intText }}</textarea>-->
						<textarea id="editor" rows="5" cols="75" @input="updateContent" class="form-control">{{ intText }}</textarea>
					</div>
				</div>
	
			<!-- show Documentupload-->
			<div v-if="showDocument">
				<div class="row mb-3">
				
					<label for="text" class="form-label col-sm-2">Dokument</label>
					<div  class="col-sm-7 py-3">
						<!--Upload Component-->
						<FormUploadDms ref="upload" id="file" multiple v-model="intAnhang" ></FormUploadDms>
					</div>
				
				</div>
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
					<p class="small">{{this.lastChange}}</p>
				</div>
			</div>
					
		</form>
		
		intText: {{intText}} |<br>propText:	{{propText}} |<br> text: {{text}}  <br> {{intPropText}}


	</div>`
}

