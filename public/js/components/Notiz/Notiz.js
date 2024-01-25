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
			filteredMitarbeiter: [],
			zwischenvar: '',
			editorInitialized: false,
			editor: null
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
					this.filteredMitarbeiter = CoreRESTClient.getData(result.data);
				});
		},
		initTinyMCE() {

			const vm = this;
			tinymce.init({
				target: this.$refs.editor, //Important: not selector: to enable multiple import of component
				//height: 800,
				//plugins: ['lists'],
				//toolbar: " blocks | bold italic underline | alignleft aligncenter alignright alignjustify",
				toolbar: 'styleselect | bold italic underline | alignleft aligncenter alignright alignjustify',
				style_formats: [
					{ title: 'Blocks', block: 'div' },
					{ title: 'Paragraph', block: 'p' },
					{ title: 'Heading 1', block: 'h1' },
					{ title: 'Heading 2', block: 'h2' },
					{ title: 'Heading 3', block: 'h3' },
					{ title: 'Heading 4', block: 'h4' },
					{ title: 'Heading 5', block: 'h5' },
					{ title: 'Heading 6', block: 'h6' },
				],
				autoresize_bottom_margin: 16,

				setup: (editor) => {
					vm.editor = editor;

					editor.on('input', () => {
						const newContent = editor.getContent();
						vm.intText =  newContent;
					});
				},
			});
		},
	},
	mounted() {
		this.initTinyMCE();
	},
	watch: {
		intText: function(newVal) {
			const tinymcsVal = this.editor.getContent();

			if (tinymcsVal != newVal) {
				//Inhalt des Editors aktualisieren
				this.editor.setContent(newVal);
			}
		},
	},
	beforeDestroy() {
		this.editor.destroy();
	},
	template: `
	<div class="notiz-notiz">
	
		<form ref="form" @submit.prevent class="row">
			<div>
				<div class="row mb-3">
					<div class="col-sm-7">
						<span class="small">[{{this.typeId}}]</span>
					</div>
				</div>
				
				<div class="row mb-3">
					<div class="col-sm-7">
						<p v-if="statusNew" class="fw-bold"> {{$p.t('notiz','notiz_new')}}</p>
						<p v-else class="fw-bold">{{$p.t('notiz','notiz_edit')}}</p>
					</div>
				</div>
	
				<div class="row mb-3">
					<label for="titel" class="form-label col-sm-2">{{$p.t('global','titel')}}</label>
					<div class="col-sm-7">
						<input type="text" v-model="intTitel" class="form-control">
					</div>
				</div>
							
				<div class="row mb-3">
					<label for="text" class="form-label col-sm-2">{{$p.t('global','text')}}</label>
								
					<!-- TinyMce 5 -->
					<div class="col-sm-7">
						<textarea ref="editor" rows="5" cols="75" class="form-control"></textarea>
					</div>
				
				</div>
	
			<!-- show Documentupload-->
			<div v-if="showDocument">
				<div class="row mb-3">
				
					<label for="text" class="form-label col-sm-2">{{$p.t('notiz','document')}}</label>
					<div  class="col-sm-7 py-3">
						<!--Upload Component-->
						<FormUploadDms ref="upload" id="file" multiple v-model="intAnhang"></FormUploadDms>
					</div>
				
				</div>
			</div>
			
			<!-- show Details-->
			<div v-if="showErweitert">
			
				<div class="row mb-3">
					<label for="bis" class="form-label col-sm-2">{{$p.t('notiz','verfasser')}}</label>
					<div class="col-sm-3">
						<PvAutoComplete v-model="intVerfasser" optionLabel="mitarbeiter"  :suggestions="filteredMitarbeiter" @complete="search" minLength="3"/>
					</div>
					
					<label for="von" class="form-label col-sm-1">{{$p.t('global','gueltigVon')}}</label>
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
					<label for="bis" class="form-label col-sm-2">{{$p.t('notiz','bearbeiter')}}</label>
					<div class="col-sm-3">
						<PvAutoComplete v-model="intBearbeiter" optionLabel="mitarbeiter" :suggestions="filteredMitarbeiter" @complete="search" minlength="3"/>
					</div>
					
					
					<label for="bis" class="form-label col-sm-1">{{$p.t('global','gueltigBis')}}</label>
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
					<label for="bis" class="form-label col-sm-2">{{$p.t('notiz','erledigt')}}</label>
					<div class="col-sm-1">
						<input type="checkbox" v-model="intErledigt">
					</div>
				</div>
			</div>
			
			<div class="row mb-3">
				<label for="lastChange" class="form-label col-sm-2 small">{{$p.t('notiz','letzte_aenderung')}}</label>
				<div class="col-sm-7">
					<p class="small">{{this.lastChange}}</p>
				</div>
			</div>
					
		</form>
		
	</div>`
}

