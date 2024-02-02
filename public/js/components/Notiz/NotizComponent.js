import VueDatePicker from '../vueDatepicker.js.php';
import PvAutoComplete from "../../../../index.ci.php/public/js/components/primevue/autocomplete/autocomplete.esm.min.js";
import FormUploadDms from '../Form/Upload/Dms.js';
import {CoreRESTClient} from "../../RESTClient";
import {CoreFilterCmpt} from "../filter/Filter.js";
import BsModal from "../Bootstrap/Modal";


export default {
	components: {
		CoreFilterCmpt,
		VueDatePicker,
		PvAutoComplete,
		FormUploadDms,
		BsModal
	},
	props: [
		'typeId',
		'id',
		'showErweitert',
		'showDocument'
		],
	data(){
		return {
			tabulatorOptions: {
				ajaxURL: CoreRESTClient._generateRouterURI('components/stv/Notiz/getNotizen/' + this.id + '/' + this.typeId),
				columns: [
					{title: "Titel", field: "titel"},
					{title: "Text", field: "text_stripped", width: 250},
					{title:  "VerfasserIn", field: "verfasser_uid"},
					{title: "BearbeiterIn", field: "bearbeiter_uid", visible: false},
					{title: "Start", field: "start", visible: false},
					{title: "Ende", field: "ende", visible: false},
					{title: "Dokumente", field: "countdoc"},
					{title: "Erledigt", field: "erledigt", visible: false},
					{title: "Notiz_id", field: "notiz_id", visible: false},
					{title: "Notizzuordnung_id", field: "notizzuordnung_id", visible: false},
					{title: "letzte Änderung", field: "lastupdate", visible: false},
					{title: 'Aktionen', field: 'actions',
						minWidth: 150, // Ensures Action-buttons will be always fully displayed
						formatter: (cell, formatterParams, onRendered) => {
							let container = document.createElement('div');
							container.className = "d-flex gap-2";

							let button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-edit"></i>';
							button.addEventListener('click', (event) =>
								this.actionEditNotiz(cell.getData().notiz_id)
							);
							container.append(button);

							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-xmark"></i>';
							button.addEventListener('click', () =>
								this.actionDeleteNotiz(cell.getData().notiz_id)
							);
							container.append(button);

							return container;
						},
						frozen: true
					}
				],
				layout: 'fitDataFill',
				layoutColumnsOnNewData: false,
				height: '250',
				selectableRangeMode: 'click',
				selectable: true,
				index: 'notiz_id'
			},
			tabulatorEvents: [],
			notizen: [],
			multiupload: true,
			mitarbeiter: [],
			filteredMitarbeiter: [],
			zwischenvar: '',
			editorInitialized: false,
			editor: null,
			notizData: {
				typeId: this.typeId,
				titel: null,
				statusNew: true,
				text: null,
				lastChange: null,
				von: null,
				bis: null,
				document: null,
				erledigt: false,
				verfasser: null,
				bearbeiter: null,
				anhang: []
			},
		};
	},
	methods: {
		actionDeleteNotiz(notiz_id){
			this.loadNotiz(notiz_id).then(() => {
				if(this.notizen.notiz_id) {
					this.$refs.deleteNotizModal.show();
				}
			});
		},
		actionEditNotiz(notiz_id){
			this.loadNotiz(notiz_id).then(() => {
				console.log(this.notizen);
				if(this.notizen.notiz_id) {
					this.notizData.titel = this.notizen.titel;
					this.notizData.statusNew = false;
					this.notizData.text = this.notizen.text;
					this.notizData.lastChange = this.notizen.lastupdate;
					this.notizData.von = this.notizen.start;
					this.notizData.bis = this.notizen.ende;
					this.notizData.document = this.notizen.dms_id;
					this.notizData.erledigt = this.notizen.erledigt;
					this.notizData.verfasser = this.notizen.verfasser_uid; //todo(manu) better
					this.notizData.intVerfasser = this.notizen.verfasser_uid;
					this.notizData.intBearbeiter = this.notizen.bearbeiter_uid; //todo(manu) better
					this.notizData.bearbeiter = this.notizen.bearbeiter_uid;
				}
			})
				.then(() => {
					if(this.notizen.dms_id){
						console.log("loadEntries with " + this.notizen.notiz_id);
						this.loadDocEntries(this.notizen.notiz_id);
					}
				});
		},
		actionNewNotiz(){
			this.resetFormData();
		},
		addNewNotiz(notizData) {
			const formData = new FormData();

			formData.append('data', JSON.stringify(this.notizData));
			Object.entries(this.notizData.anhang).forEach(([k, v]) => formData.append(k, v));
			CoreRESTClient.post(
				'components/stv/Notiz/addNewNotiz/' + this.id,
				formData,
				{ Headers: { "Content-Type": "multipart/form-data" } }
			).then(response => {
				if (!response.data.error) {
					this.$fhcAlert.alertSuccess('Anlegen von neuer Notiz erfolgreich');
					this.resetFormData();
					this.reload();
				} else {
					const errorData = response.data.retval;
					Object.entries(errorData).forEach(entry => {
						const [key, value] = entry;
						this.$fhcAlert.alertError(value);
					});
				}
			}).catch(error => {
				if (error.response) {
					this.$fhcAlert.alertError(error.response.data);
				}
			}).finally(() => {
				window.scrollTo(0, 0);
			});
		},
		deleteNotiz(notiz_id){
			CoreRESTClient.post('components/stv/Notiz/deleteNotiz/' + notiz_id)
				.then(response => {
					if (!response.data.error) {
						this.$fhcAlert.alertSuccess('Löschen erfolgreich');
						this.$refs.deleteNotizModal.hide();
						this.reload();
					} else {
						this.$fhcAlert.alertError('Keine Notiz mit Id ' + notiz_id + ' gefunden');
					}
				}).catch(error => {
				this.$fhcAlert.alertError('Fehler bei Löschroutine aufgetreten');
			}).finally(()=> {
				window.scrollTo(0, 0);
			});
		},
		loadNotiz(notiz_id){
			return CoreRESTClient.get(
				'components/stv/Notiz/loadNotiz/' + notiz_id)
				.then(
					result => {
						if(result.data.retval) {
							this.notizen = result.data.retval;
							//console.log(this.notizen);
						}
						else {
							this.notizen = {};
							this.$fhcAlert.alertError('Keine Notiz mit Id ' + notiz_id + ' gefunden');
						}
						return result;
					}
				);
		},
		loadDocEntries(notiz_id){
			return CoreRESTClient.get('components/stv/Notiz/loadDokumente/' + notiz_id)
				.then(
					result => {
						if(result.data.retval) {
							this.notizData.anhang = result.data.retval;
							console.log(this.notizData.anhang);
						}
						else
						{
							this.notizData.anhang = {};
							this.$fhcAlert.alertError('Kein Dokumenteneintrag mit NotizId ' + notiz_id + ' gefunden');
						}
						return result;
					}
				);
		},
		updateNotiz(notiz_id){
			const formData = new FormData();
			formData.append('data', JSON.stringify(this.notizData));
			Object.entries(this.notizData.anhang).forEach(([k, v]) => formData.append(k, v));

			CoreRESTClient.post(
				'components/stv/Notiz/updateNotiz/' + notiz_id,
				formData,
				{ Headers: { "Content-Type": "multipart/form-data" } }
			).then(response => {
				if (!response.data.error) {
					this.$fhcAlert.alertSuccess('Update von Notiz erfolgreich');
					this.resetFormData();
					this.reload();
				} else {
					const errorData = response.data.retval;
					Object.entries(errorData).forEach(entry => {
						const [key, value] = entry;
						this.$fhcAlert.alertError(value);
					});
				}
			}).catch(error => {
				this.$fhcAlert.alertError('Fehler bei Updateroutine aufgetreten');
			}).finally(() => {
				window.scrollTo(0, 0);
			});
		},
		reload(){
			this.$refs.table.reloadTable();
		},
		resetFormData() {
			this.$refs.formc.reset();
			this.notizData = {
				typeId: this.typeId,
				titel: null,
				statusNew: true,
				text: null,
				lastChange: null,
				von: null,
				bis: null,
				document: null,
				erledigt: false,
				verfasser: this.uid,
				bearbeiter: null,
				anhang: []
			};
		},
		getUid(){
			CoreRESTClient
				.get('components/stv/Notiz/getUid')
				.then(result => {
					if(result.data.retval) {
						this.notizData.intVerfasser = result.data.retval;
					}
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		search(event) {
			return CoreRESTClient
				.get('components/stv/Notiz/getMitarbeiter/' + event.query)
				.then(result => {
					this.filteredMitarbeiter = CoreRESTClient.getData(result.data);
				});
		},
/*		initTinyMCE() {

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
		},*/
	},
	created(){
		this.getUid();
	},
	async mounted() {
		await this.$p.loadCategory(['notiz','global']);

		let cm = this.$refs.table.tabulator.columnManager;

		cm.getColumnByField('verfasser_uid').component.updateDefinition({
			title: this.$p.t('notiz', 'verfasser')
		});
		cm.getColumnByField('titel').component.updateDefinition({
			title: this.$p.t('global', 'titel')
		});
		cm.getColumnByField('text_stripped').component.updateDefinition({
			title: this.$p.t('global', 'text')
		});
		cm.getColumnByField('bearbeiter_uid').component.updateDefinition({
			title: this.$p.t('notiz', 'bearbeiter')
		});
		cm.getColumnByField('start').component.updateDefinition({
			title: this.$p.t('global', 'gueltigVon')
		});
		cm.getColumnByField('ende').component.updateDefinition({
			title: this.$p.t('global', 'gueltigBis')
		});
		cm.getColumnByField('countdoc').component.updateDefinition({
			title: this.$p.t('notiz', 'document')
		});
		cm.getColumnByField('erledigt').component.updateDefinition({
			title: this.$p.t('notiz', 'erledigt')
		});
		cm.getColumnByField('lastupdate').component.updateDefinition({
			title: this.$p.t('notiz', 'letzte_aenderung')
		});
	},
/*	mounted() {
		this.initTinyMCE();
	},*/
/*	watch: {
		intText: function(newVal) {
			const tinymcsVal = this.editor.getContent();

			if (tinymcsVal != newVal) {
				//Inhalt des Editors aktualisieren
				this.editor.setContent(newVal);
			}
		},
	},*/
	beforeDestroy() {
		this.editor.destroy();
	},
	watch: {
		//Watcher für autocomplete Bearbeiter und Verfasser
		'notizData.intBearbeiter': {
			handler(newVal) {
				if(typeof newVal === 'object') {
					this.notizData.bearbeiter = newVal.mitarbeiter_uid;
				}
			},
			deep: true
		},
		'notizData.intVerfasser': {
			handler(newVal) {
				if(typeof newVal === 'object') {
					this.notizData.verfasser = newVal.mitarbeiter_uid;
				}
			},
			deep: true
		}
	},
	template: `
	<div class="notiz-notiz">

		<!--Modal: deleteNotizModal-->
		<BsModal ref="deleteNotizModal">
			<template #title>Notiz löschen</template>
			<template #default>
				<p>Notiz wirklich löschen?</p>
			</template>
			<template #footer>
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="resetModal">Abbrechen</button>
				<button ref="Close" type="button" class="btn btn-primary" @click="deleteNotiz(notizen.notiz_id)">OK</button>
			</template>
		</BsModal>
		
		<core-filter-cmpt
			ref="table"
			:tabulator-options="tabulatorOptions"
			:tabulator-events="tabulatorEvents"
			table-only
			:side-menu="false"
			reload
			new-btn-show
			new-btn-label="Neu"
			@click:new="actionNewNotiz"
			>
		</core-filter-cmpt>
		<br><br>
	
		<form ref="formc" @submit.prevent class="row pt-3">
		<br><br>
			<div class="pt-2">
				<div class="row mb-3">
					<div class="col-sm-7">
						<span class="small">[{{notizData.typeId}}]</span>
					</div>
				</div>
				
				<div class="row mb-3">
					<div class="col-sm-7">
						<p v-if="notizData.statusNew" class="fw-bold"> {{$p.t('notiz','notiz_new')}}</p>
						<p v-else class="fw-bold">{{$p.t('notiz','notiz_edit')}}</p>
					</div>
				</div>
	
				<div class="row mb-3">
					<label for="titel" class="form-label col-sm-2">{{$p.t('global','titel')}}</label>
					<div class="col-sm-7">
						<input type="text" v-model="notizData.titel" class="form-control">
					</div>
				</div>
							
				<div class="row mb-3">
					<label for="text" class="form-label col-sm-2">{{$p.t('global','text')}}</label>
						
					<!--Todo(manu) make TINYMCE optional	-->	
					<!-- TinyMce 5 -->
<!--					<div class="col-sm-7">
						<textarea ref="editor" rows="5" cols="75" class="form-control"></textarea>
					</div>
					-->

					<div class="col-sm-7">
						<textarea rows="5" cols="75" v-model="notizData.text" class="form-control"></textarea>
					</div>
				
				</div>
	
			<!-- show Documentupload-->
			<div v-if="showDocument">
				<div class="row mb-3">
				
					<label for="text" class="form-label col-sm-2">{{$p.t('notiz','document')}}</label>
					<div  class="col-sm-7 py-3">
						<!--Upload Component-->
						<FormUploadDms ref="upload" id="file" multiple v-model="notizData.anhang"></FormUploadDms>
					</div>
				
				</div>
			</div>
			
			<!-- show Details-->
			<div v-if="showErweitert">
			
				<div class="row mb-3">
					<label for="bis" class="form-label col-sm-2">{{$p.t('notiz','verfasser')}}</label>
					<div class="col-sm-3">
						<PvAutoComplete v-model="notizData.intVerfasser" optionLabel="mitarbeiter"  :suggestions="filteredMitarbeiter" @complete="search" minLength="3"/>
					</div>
					
					<label for="von" class="form-label col-sm-1">{{$p.t('global','gueltigVon')}}</label>
					<div class="col-sm-3">
						<vue-date-picker
							id="von"
							v-model="notizData.Von"
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
						<PvAutoComplete v-model="notizData.intBearbeiter" optionLabel="mitarbeiter" :suggestions="filteredMitarbeiter" @complete="search" minlength="3"/>
					</div>
					
					
					<label for="bis" class="form-label col-sm-1">{{$p.t('global','gueltigBis')}}</label>
					<div class="col-sm-3">
						<vue-date-picker
							id="bis"
							v-model="notizData.Bis"
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
						<input type="checkbox" v-model="notizData.erledigt">
					</div>
				</div>
				
			</div>
			
			<div class="row mb-3">
				<label for="lastChange" class="form-label col-sm-2 small">{{$p.t('notiz','letzte_aenderung')}}</label>
				<div class="col-sm-7">
					<p class="small">{{notizData.lastChange}}</p>
				</div>
			</div>
			
			<button v-if="notizData.statusNew"  type="button" class="btn btn-primary" @click="addNewNotiz()"> {{$p.t('studierendenantrag', 'btn_new')}}</button>
			<button v-else type="button" class="btn btn-primary" @click="updateNotiz(notizen.notiz_id)"> {{$p.t('ui', 'speichern')}}</button>
					
		</form>
		
	</div>`
}

