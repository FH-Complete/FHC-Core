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
			filteredMitarbeiter: []
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
			//const vm = this.intText;
			tinymce.init({
				selector: '#editor',
/*				selector: `#${this.$refs.editor.id}`,*/
				//height: 800,
				plugins: ['lists'],
/*				toolbar: "undo redo | blocks | bold italic | alignleft aligncenter alignright alignjustify | outdent indent",*/
				toolbar: "undo redo",

				autoresize_bottom_margin: 16,
				setup: (editor) => {

					// workaround for avoiding conflict id of frame: same Id as textarea
					// working just for new notes
					//solution for update notes: now working
					//todo(manu) but now: cursor position!!

					editor.on('input', () => {

						const newContent = editor.getContent();
/*						const contentLength = newContent.length;
						console.log(contentLength);

						editor.selection.setCursorLocation(editor.getBody(), contentLength);*/
						this.intText = newContent;

					});

/*					editor.on('change', () => {
						const newContent = editor.getContent();
						const contentLength = newContent.length;

						//editor.selection.setCursorLocation(editor.getBody(), contentLength);

						// Vue-Instanz-Referenz (vm), um auf die Daten zuzugreifen
						this.intText = newContent;
					});*/
				},
			});
		},
		updateContent(event) {
			// Manually update the content when the textarea is edited
			tinymce.get('editor').setContent(event.target.value);
			//console.log(event.target.value);
		},
	},
	mounted() {
		this.initTinyMCE();
	},
	watch: {
		intText: function(newVal, oldVal) {
			// Überprüfen, ob sich der Wert von intText geändert hat
			if (newVal !== oldVal) {

				const editor = tinymce.get('editor');

				// Aktualisieren Inhalt des Editors
				editor.setContent(newVal);
				//tinymce.get(this.$refs.editor.id).setContent(newVal);

			}
		},
	},
	beforeDestroy() {
		tinymce.get('editor').destroy();
/*		tinymce.get(this.$refs.editor.id).destroy();*/
	},
	template: `
	<div class="notiz-notiz">
	
<!--		<span v-for="(anhang,index) in intAnhang"> {{anhang.name}} | {{anhang.type}} | {{anhang.size}} {{index}}<br></span>-->
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
						<!--<textarea id="editor" rows="5" cols="75" @input="updateContent" class="form-control">{{ intText }}</textarea>-->
						<textarea id="editor" rows="5" cols="75" :value="intText" @input="updateContent" class="form-control"></textarea>

					</div>
					
					<!--	oldversion-->
<!--					<div class="col-sm-7">
						<textarea rows="5" cols="75" v-model="intText" class="form-control"></textarea>
					</div>-->
				</div>
	
			<!-- show Documentupload-->
			<div v-if="showDocument">
				<div class="row mb-3">
				
					<label for="text" class="form-label col-sm-2">Dokument</label>
					<div  class="col-sm-7 py-3">
						<!--Upload Component-->
						<FormUploadDms ref="upload" id="file" multiple v-model="intAnhang"></FormUploadDms>
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
		
		intText: {{intText}}


	</div>`
}

