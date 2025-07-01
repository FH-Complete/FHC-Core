import VueDatePicker from '../vueDatepicker.js.php';
import PvAutoComplete from "../../../../index.ci.php/public/js/components/primevue/autocomplete/autocomplete.esm.min.js";
import FormUploadDms from '../Form/Upload/Dms.js';
import {CoreFilterCmpt} from "../filter/Filter.js";
import BsModal from "../Bootstrap/Modal.js";
import FormForm from '../Form/Form.js';
import FormInput from '../Form/Input.js';


export default {
	components: {
		CoreFilterCmpt,
		VueDatePicker,
		PvAutoComplete,
		FormUploadDms,
		FormForm,
		FormInput,
		BsModal
	},
	props: {
		endpoint: {
			type: Object,
			required: true
		},
		typeId: String,
		id: {
			type: [Number, String],
			required: true
		},
		notizLayout: {
			type: String,
			default: 'popupModal',
			validator(value) {
				return [
					'classicFas',
					'twoColumnsFormRight',
					'twoColumnsFormLeft',
					'popupModal'
					].includes(value)
			}
		},
		showErweitert: Boolean,
		showDocument: Boolean,
		showTinyMce: Boolean,
		visibleColumns: Array
	},
	data() {
		return {
			tabulatorOptions: {
				ajaxURL: 'dummy',
				ajaxRequestFunc: () => this.$api.call(this.endpoint.getNotizen(this.id, this.typeId)),
				ajaxParams: () => {
					return {
						id: this.id,
						type: this.typeId
					};
				},
				ajaxResponse: (url, params, response) => response.data,
				columns: [
					{
						title: "Titel",
						field: "titel",
						width: 100,
						tooltip:function(e, cell, onRendered){
							var el = document.createElement("div");
							el.style.backgroundColor = "white";
							el.style.color = "black";
							el.style.fontWeight = "bold";
							el.style.padding = "5px";
							el.style.border = "1px solid black";
							el.style.borderRadius = "5px";

							el.innerText = cell.getValue();

							el.innerText = cell.getColumn().getField() + " - " + cell.getValue();

							return el;
						},
					},
					{
						title: "Text",
						field: "text_stripped",
						width: 250,
						clipContents: true,
						tooltip:function(e, cell, onRendered){
							var el = document.createElement("div");
							el.style.backgroundColor = "white";
							el.style.color = "black";
							el.style.fontWeight = "bold";
							el.style.padding = "5px";
							el.style.border = "1px solid black";
							el.style.borderRadius = "5px";

							el.innerText = cell.getValue();

							return el;
						},
					},
					{title: "VerfasserIn", field: "verfasser", width: 124},
					{title: "BearbeiterIn", field: "bearbeiter", width: 126},
					{title: "Verfasser UID", field: "verfasser_uid", width: 124, visible: false},
					{title: "Bearbeiter UID", field: "bearbeiter_uid", width: 126, visible: false},
					{title: "Start", field: "start_format", width: 86, visible: false},
					{title: "Ende", field: "ende_format", width: 86, visible: false},
					{title: "Dokumente", field: "countdoc", width: 100, visible: false},
					{
						title: "Erledigt",
						field: "erledigt",
						width: 97,
						visible: false,
						formatter:"tickCross",
						hozAlign:"center",
						formatterParams: {
							tickElement: '<i class="fa fa-check text-success"></i>',
							crossElement: '<i class="fa fa-xmark text-danger"></i>'
						}
					},
					{title: "Notiz_id", field: "notiz_id", width: 92, visible: false},
					{title: "Notizzuordnung_id", field: "notizzuordnung_id", width: 164, visible: false},
					{title: "type_id", field: "type_id", width: 164, visible: false},
					{title: "extension_id", field: "id", width: 135, visible: false},
					{
						title: "letzte Änderung", 
						field: "lastupdate",
						width: 146,
						visible: false,
						formatter: function (cell) {
							const dateStr = cell.getValue();
							if (!dateStr) return "";

							const date = new Date(dateStr);
							return date.toLocaleString("de-DE", {
								day: "2-digit",
								month: "2-digit",
								year: "numeric",
								hour: "2-digit",
								minute: "2-digit",
								second: "2-digit",
								hour12: false
							});
						}
					},
					{
						title: 'Aktionen', field: 'actions',
						width: 100,
						formatter: (cell, formatterParams, onRendered) => {
							let container = document.createElement('div');
							container.className = "d-flex gap-2";

							let button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.title = this.$p.t('ui', 'notiz_edit');
							button.innerHTML = '<i class="fa fa-edit"></i>';
							button.addEventListener(
								'click',
								(event) =>
									this.actionEditNotiz(cell.getData().notiz_id)
							);
							container.append(button);

							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.title = this.$p.t('notiz', 'notiz_delete');
							button.innerHTML = '<i class="fa fa-xmark"></i>';
							button.addEventListener(
								'click',
								() =>
									this.actionDeleteNotiz(cell.getData().notiz_id)
							);
							container.append(button);

							return container;
						},
						frozen: true
					}],
				layout: 'fitColumns',
				layoutColumnsOnNewData: false,
				responsiveLayout: "collapse",
				height: '250',
				selectableRangeMode: 'click',
				selectable: true,
				index: 'notiz_id',
				persistenceID: 'core-notiz'
			},
			tabulatorEvents: [
				{
					event: 'tableBuilt',
					handler: async () => {

						await this.$p.loadCategory(['notiz', 'global', 'ui']);

						let cm = this.$refs.table.tabulator.columnManager;

						cm.getColumnByField('verfasser').component.updateDefinition({
							title: this.$p.t('notiz', 'verfasser'),
							visible: this.showVariables.showVerfasser
						});
						cm.getColumnByField('verfasser_uid').component.updateDefinition({
							title: this.$p.t('ui', 'verfasser_uid'),
						});
						cm.getColumnByField('titel').component.updateDefinition({
							title: this.$p.t('global', 'titel'),
							//visible: this.showVariables.showTitel
						});
						cm.getColumnByField('bearbeiter').component.updateDefinition({
							title: this.$p.t('notiz', 'bearbeiter'),
							visible: this.showVariables.showBearbeiter
						});
						cm.getColumnByField('bearbeiter_uid').component.updateDefinition({
							title: this.$p.t('ui', 'bearbeiter_uid'),
						});
						cm.getColumnByField('start_format').component.updateDefinition({
							title: this.$p.t('global', 'gueltigVon'),
							visible: this.showVariables.showVon
						});
						cm.getColumnByField('ende_format').component.updateDefinition({
							title: this.$p.t('global', 'gueltigBis'),
							visible: this.showVariables.showBis
						});
						cm.getColumnByField('countdoc').component.updateDefinition({
							title: this.$p.t('notiz', 'document'),
							visible: this.showVariables.showDokumente
						});
						cm.getColumnByField('erledigt').component.updateDefinition({
							title: this.$p.t('notiz', 'erledigt'),
							visible: this.showVariables.showErledigt
						});
						cm.getColumnByField('lastupdate').component.updateDefinition({
							title: this.$p.t('notiz', 'letzte_aenderung'),
							visible: this.showVariables.showLastupdate
						});
						cm.getColumnByField('notiz_id').component.updateDefinition({
							visible: this.showVariables.showNotiz_id,
							title: this.$p.t('ui', 'notiz_id')
						});
						cm.getColumnByField('notizzuordnung_id').component.updateDefinition({
							visible: this.showVariables.showNotizzuordnung_id,
							title: this.$p.t('ui', 'notizzuordnung_id')
						});
						cm.getColumnByField('type_id').component.updateDefinition({
							visible: this.showVariables.showType_id,
							title: this.$p.t('ui', 'type_id')
						});
						cm.getColumnByField('id').component.updateDefinition({
							visible: this.showVariables.showId,
							title: this.$p.t('ui', 'extension_id')
						});
						cm.getColumnByField('actions').component.updateDefinition({
							title: this.$p.t('global', 'aktionen')
						});

						cm.getColumnByField('text_stripped').component.updateDefinition({
							title: this.$p.t('global', 'text'),
							width: 250,
							tooltip: true,
							clipContents: true,
						});

						// Force layout recalculation for handling overflow text
						this.$refs.table.tabulator.redraw(true);
					}
				}
			],
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
				text: '',
				lastUpdate: null,
				von: null,
				bis: null,
				document: null,
				erledigt: false,
				verfasser: null,
				bearbeiter: null,
				anhang: []
			},
			showVariables: {
				showTitel: false,
				showText: false,
				showVerfasser: false,
				showBearbeiter: false,
				showVon: false,
				showBis: false,
				showDokumente: false,
				showErledigt: false,
				showNotiz_id: false,
				showNotizzuordnung_id: false,
				showType_id: false,
				showId: false,
				showLastupdate: false
			},
		}
	},
	methods: {
		actionDeleteNotiz(notiz_id) {
			this.loadNotiz(notiz_id);

			this.$fhcAlert
				.confirmDelete()
				.then(result => result
					? notiz_id
					: Promise.reject({handled: true}))
				.then(this.deleteNotiz)
				.catch(this.$fhcAlert.handleSystemError);
		},
		actionEditNotiz(notiz_id) {
			this.loadNotiz(notiz_id).then(() => {
				if (this.notizen.notiz_id) {
					this.notizData.typeId = this.typeId;
					this.notizData.titel = this.notizen.titel;
					this.notizData.statusNew = false;
					this.notizData.text = this.notizen.text;
					this.notizData.intText = this.notizen.text || '';
					this.notizData.lastupdate = this.notizen.lastupdate;
					this.notizData.von = this.notizen.start;
					this.notizData.bis = this.notizen.ende;
					this.notizData.document = this.notizen.dms_id;
					this.notizData.erledigt = this.notizen.erledigt;
					this.notizData.verfasser = this.notizen.verfasser_uid;
					this.notizData.intVerfasser = this.notizen.verfasser_uid;
					this.notizData.intBearbeiter = this.notizen.bearbeiter_uid;
					this.notizData.bearbeiter = this.notizen.bearbeiter_uid;
				}
			})
				.then(() => {
					if(this.notizLayout == 'popupModal') {
						this.$refs.NotizModal.show();
					}
					if (this.notizData.dms_id) {
						this.loadDocEntries(this.notizData.notiz_id);
					} else
						this.notizData.anhang = [];
				});
		},
		actionNewNotiz() {
			this.resetFormData();
			if(this.notizLayout == 'popupModal') {
				this.$refs.NotizModal.show();
			}
		},
		addNewNotiz() {
			const formData = new FormData();

			formData.append('data', JSON.stringify(this.notizData));
			Object.entries(this.notizData.anhang).forEach(([k, v]) => formData.append(k, v));

			this.$refs.formNotiz.clearValidation();

			return this.$refs.formNotiz
				.call(this.endpoint.addNewNotiz(this.id, formData))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.resetFormData();
					if(this.notizLayout == 'popupModal') {
						this.$refs.NotizModal.hide();
					}
					this.reload();
					this.$emit('reload');
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					window.scrollTo(0, 0);
				});
		},
		deleteNotiz(notiz_id) {
			return this.$api
				.call(this.endpoint.deleteNotiz(notiz_id, this.typeId, this.id))
				.then(result => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));
					this.reload();
					this.$emit('reload');
					this.resetFormData();
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					window.scrollTo(0, 0);
				});
		},
		loadNotiz(notiz_id) {
			return this.$api
				.call(this.endpoint.loadNotiz(notiz_id))
				.then(result => {
					this.notizData = result.data;
					this.notizData.typeId = this.typeId;
					this.notizData.anhang = [];
					return result;
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		loadDocEntries(notiz_id) {
			return this.$api
				.call(this.endpoint.loadDokumente(notiz_id))
				.then(
					result => {
						this.notizData.anhang = result.data;
						return result;
					})
				.catch(this.$fhcAlert.handleSystemError);
		},
		updateNotiz(notiz_id) {
			const formData = new FormData();
			formData.append('data', JSON.stringify(this.notizData));
			Object.entries(this.notizData.anhang).forEach(([k, v]) => formData.append(k, v));

			this.$refs.formNotiz.clearValidation();
			return this.$refs.formNotiz
				.call(this.endpoint.updateNotiz(notiz_id, formData))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.resetFormData();
					if(this.notizLayout == 'popupModal') {
						this.$refs.NotizModal.hide();
					}
					this.reload();
					this.$emit('reload');
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					window.scrollTo(0, 0);
				});
		},
		reload() {
			this.$refs.table.reloadTable();
		},
		resetFormData() {
			//TODO(Manu) check ref, needed here?
			//this.$refs.formNotiz.reset();
			//this.$refs.formc.reset();
			this.notizData = {
				typeId: this.typeId,
				titel: null,
				statusNew: true,
				text: '',
				lastUpdate: null,
				von: null,
				bis: null,
				document: null,
				erledigt: false,
				verfasser: this.uid,
				bearbeiter: null,
				anhang: []
			};
		},
		getUid() {
			return this.$api
				.call(this.endpoint.getUid())
				.then(result => {
					this.notizData.intVerfasser = result.data;
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		search(event) {
			return this.$api
				.call(this.endpoint.getMitarbeiter(event.query))
				.then(result => {
					this.filteredMitarbeiter = result.data.retval;
				});
		},
		initTinyMCE() {

			const vm = this;
			tinymce.init({
				target: this.$refs.editor.$refs.input, //Important: not selector: to enable multiple import of component
				//height: 800,
				//plugins: ['lists'],
				//toolbar: " blocks | bold italic underline | alignleft aligncenter alignright alignjustify",
				toolbar: 'styleselect | bold italic underline | alignleft aligncenter alignright alignjustify',
				style_formats: [
					{title: 'Blocks', block: 'div'},
					{title: 'Paragraph', block: 'p'},
					{title: 'Heading 1', block: 'h1'},
					{title: 'Heading 2', block: 'h2'},
					{title: 'Heading 3', block: 'h3'},
					{title: 'Heading 4', block: 'h4'},
					{title: 'Heading 5', block: 'h5'},
					{title: 'Heading 6', block: 'h6'},
				],
				autoresize_bottom_margin: 16,

				setup: (editor) => {
					vm.editor = editor;

					editor.on('input', () => {
						const newContent = editor.getContent();
						vm.notizData.text = newContent;
					});
				},
			});
		},
		updateText(value) {
			this.notizData.text = value;
		},
		initializeShowVariables() {
			this.visibleColumns.forEach(column => {
				const columnToShow = "show" + column.charAt(0).toUpperCase() + column.slice(1);
				this.showVariables[columnToShow] = true;
			});
		},
	},
	created() {
		this.initializeShowVariables();
		this.getUid();
	},
	async mounted() {
		if(this.showTinyMce){
			this.initTinyMCE();
		}
	},
	watch: {
		//watcher für Tinymce-Textfeld
		'notizData.text': {
			handler(newVal) {
				if (this.showTinyMce) {
					const tinymcsVal = this.editor.getContent();

					if (tinymcsVal != newVal) {
						//Inhalt des Editors aktualisieren
						this.editor.setContent(newVal);
					}
				}
			}
		},
		//Watcher für autocomplete Bearbeiter und Verfasser
		'notizData.intBearbeiter': {
			handler(newVal) {
				if (typeof newVal === 'object') {
					this.notizData.bearbeiter = newVal.mitarbeiter_uid;
				}
			},
			deep: true
		},
		'notizData.intVerfasser': {
			handler(newVal) {
				if (typeof newVal === 'object') {
					this.notizData.verfasser = newVal.mitarbeiter_uid;
				}
			},
			deep: true
		},
		id() {
			this.reload();
		}
	},
	beforeDestroy() {
		if(this.showTinyMce) {
			this.editor.destroy();
		}
	},
	template: `
	<div class="core-notiz">
	
		<div v-if="notizLayout=='classicFas'">
		
			<core-filter-cmpt
				ref="table"
				:tabulator-options="tabulatorOptions"
				:tabulator-events="tabulatorEvents"
				table-only
				:side-menu="false"
				reload
				new-btn-show
				:new-btn-label="this.$p.t('global', 'notiz')"
				@click:new="actionNewNotiz"
				>
			</core-filter-cmpt>
			
			<br>
		
			<form-form ref="formNotiz" @submit.prevent class="row pt-3">
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
							<form-input
								container-class="col-12"
								:label="$p.t('global','titel')  + ' *'"
								type="text"
								v-model="notizData.titel"
								name="titel"
								>
							</form-input>
					</div>
								
					<div class="row mb-3">	
						<!-- TinyMce 5 -->
						<div v-if="showTinyMce" class="col-sm-12">
								<form-input
									ref="editor"
									:label="$p.t('global','text')  + ' *'"
									type="textarea"
									v-model="notizData.text"
									name="text"
									rows="5"
									cols="75"
									>
								</form-input>
							</div>
						<div v-else class="col-sm-12">
							<form-input
								ref="editor"
								:label="$p.t('global','text')  + ' *'"
								type="textarea"
								v-model="notizData.text"
								name="text"
								rows="5"
								cols="75"
								>
							</form-input>
						</div>
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
											
						<div v-if="notizData.verfasser_uid" class="col-sm-3">
							<input type="text" :readonly="readonly" class="form-control" id="name" v-model="notizData.verfasser_uid">
						</div>
						<div v-else class="col-sm-3">
							<PvAutoComplete v-model="notizData.intVerfasser" optionLabel="mitarbeiter"  :suggestions="filteredMitarbeiter" @complete="search" minLength="3"/>
						</div>
						
						<label for="von" class="form-label col-sm-1">{{$p.t('global','gueltigVon')}}</label>
						<div class="col-sm-3">
							<vue-date-picker
								id="von"
								v-model="notizData.start"
								clearable="false"
								auto-apply
								:enable-time-picker="false"
								format="dd.MM.yyyy"
								:teleport="true"
								preview-format="dd.MM.yyyy"></vue-date-picker>
						</div>
					</div>
					
					<div class="row mb-3">
						<label for="bis" class="form-label col-sm-2">{{$p.t('notiz','bearbeiter')}}</label>
						
						<div v-if="notizData.bearbeiter_uid" class="col-sm-3">
							<input type="text" :readonly="readonly" class="form-control" id="name" v-model="notizData.bearbeiter_uid">
						</div>
						
						<div v-else class="col-sm-3">
							<PvAutoComplete v-model="notizData.intBearbeiter" optionLabel="mitarbeiter" :suggestions="filteredMitarbeiter" @complete="search" minlength="3"/>
						</div>
						
						
						<label for="bis" class="form-label col-sm-1">{{$p.t('global','gueltigBis')}}</label>
						<div class="col-sm-3">
							<vue-date-picker
								id="bis"
								v-model="notizData.ende"
								clearable="false"
								auto-apply
								:enable-time-picker="false"
								format="dd.MM.yyyy"
								:teleport="true"
								preview-format="dd.MM.yyyy">
							</vue-date-picker>
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
					<label for="lastUpdate" class="form-label col-sm-2 small">{{$p.t('notiz','letzte_aenderung')}}</label>
					<div class="col-sm-7">
						<p class="small">{{notizData.lastupdate}}</p>
					</div>
				</div>
				
				<button v-if="notizData.statusNew"  type="button" class="btn btn-primary" @click="addNewNotiz()"> {{$p.t('studierendenantrag', 'btn_new')}}</button>
				<button v-else type="button" class="btn btn-primary" @click="updateNotiz(notizData.notiz_id)"> {{$p.t('ui', 'speichern')}}</button>
			</form-form>
		</div>

		<div v-else-if="notizLayout=='twoColumnsFormRight'" class="notiz-notiz">
			
			<div class="row">
				<div class="col-sm-6 pt-6">
					<br>
					<core-filter-cmpt
						ref="table"
						:tabulator-options="tabulatorOptions"
						:tabulator-events="tabulatorEvents"
						table-only
						:side-menu="false"
						reload
						new-btn-show
						:new-btn-label="this.$p.t('global', 'notiz')"
						@click:new="actionNewNotiz"
						>
					</core-filter-cmpt>	
				</div>
			
				<div class="col-sm-6">
					<form-form ref="formNotiz" @submit.prevent class="row pt-3">
						<div class="col pt-3">
							<p v-if="notizData.statusNew" class="fw-bold">{{$p.t('notiz','notiz_new')}} <span> [{{notizData.typeId}}]</span></p>
							<p v-else class="fw-bold">{{$p.t('notiz','notiz_edit')}} <span> [{{notizData.typeId}}]</span></p>
						</div>
											
						<div class="position-sticky top-0 z-1 pt-3">
							<button v-if="notizData.statusNew" class="btn btn-primary position-absolute top-0 end-0" @click="addNewNotiz()"> {{$p.t('studierendenantrag', 'btn_new')}}</button>
							<button v-else class="btn btn-primary position-absolute top-0 end-0" @click="updateNotiz(notizData.notiz_id)"> {{$p.t('ui', 'speichern')}}</button>
						</div>
		
						<div class="row mb-3">
							<form-input
								container-class="col-12"
								:label="$p.t('global','titel')  + ' *'"
								type="text"
								v-model="notizData.titel"
								name="titel"
								>
							</form-input>
						</div>
									
						<div class="row mb-3">
							<!-- TinyMce 5 -->
							<div v-if="showTinyMce" class="col-sm-12">
								<form-input
									ref="editor"
									:label="$p.t('global','text')  + ' *'"
									type="textarea"
									v-model="notizData.text"
									name="text"
									rows="5"
									cols="75"
									>
								</form-input>
							</div>
							
							<div v-else class="col-sm-12">
								<form-input
									ref="editor"
									:label="$p.t('global','text')  + ' *'"
									type="textarea"
									v-model="notizData.text"
									name="text"
									rows="5"
									cols="75"
									>
								</form-input>
							</div>
						</div>
				
						<!-- show Documentupload-->
						<div v-if="showDocument">
							<div class="row mb-3">		
								<div  class="col-sm-12 py-3">
								<label for="text" class="form-label col-sm-2">{{$p.t('notiz','document')}}</label>
									<!--Upload Component-->
									<FormUploadDms ref="upload" id="file" multiple v-model="notizData.anhang"></FormUploadDms>
								</div>
							</div>
						</div>
						
						<!-- show Details-->
						<div v-if="showErweitert">
							<div class="row mb-3">
								<form-input
									container-class="col-6"
									:label="$p.t('global', 'gueltigVon')"
									type="DatePicker"
									v-model="notizData['start']"
									name="von"
									auto-apply
									:enable-time-picker="false"
									format="dd.MM.yyyy"
									preview-format="dd.MM.yyyy"
									:teleport="true"
									>
								</form-input>
								
								<form-input
									container-class="col-6"
									:label="$p.t('global', 'gueltigBis')"
									type="DatePicker"
									v-model="notizData.ende"
									name="bis"
									auto-apply
									:enable-time-picker="false"
									format="dd.MM.yyyy"
									preview-format="dd.MM.yyyy"
									:teleport="true"
									>
								</form-input>
							</div>
							
							<div class="row mb-3">
								<form-input 
									v-if="notizData.verfasser_uid"
									container-class="col-6"
									:label="$p.t('notiz', 'verfasser')"
									type="text"
									v-model="notizData.verfasser_uid"
									name="titel"
									>
								</form-input>
						
								<form-input
									v-else
									container-class="col-6"
									:label="$p.t('notiz', 'verfasser')"
									type="autocomplete"
									v-model="notizData.intVerfasser"
									:suggestions="filteredMitarbeiter" 
									@complete="search" 
									optionLabel="mitarbeiter"
									minLength="3"
									>
								</form-input>
								
								<form-input
									v-if="notizData.bearbeiter_uid"
									container-class="col-6"
									:label="$p.t('notiz', 'bearbeiter')"
									v-model="notizData.bearbeiter_uid"
									minlength="3"
									>
								</form-input>
								
								<form-input
									v-else
									container-class="col-6"
									:label="$p.t('notiz', 'bearbeiter')"
									type="autocomplete"
									v-model="notizData.intBearbeiter"
									:suggestions="filteredMitarbeiter" 
									@complete="search" 
									optionLabel="mitarbeiter"
									minlength="3"
									>
								</form-input>	
							</div>
															
							<div class="row mb-3">
								<div class="col-2 pt-4 d-flex align-items-center">
									<form-input
										container-class="form-check"
										:label="$p.t('notiz', 'erledigt')"
										type="checkbox"
										v-model="notizData.erledigt"
										name="erledigt"
										>
									</form-input>
								</div>
							</div>
						</div>
						
						<div class="row mb-3">
							<label for="lastUpdate" class="form-label col-sm-3 small">{{$p.t('notiz','letzte_aenderung')}}</label>
							<div class="col-sm-5">
								<p class="small">{{notizData.lastupdate}}</p>
							</div>
						</div>
					</form-form>
				</div>
			</div> 
		</div>
		
		<div v-else-if="notizLayout=='twoColumnsFormLeft'" class="notiz-notiz">
			
			<div class="row">
				<div class="col-sm-6">
				  	<form-form ref="formNotiz" @submit.prevent class="row pt-3">
						<div class="col pt-3">
							<p v-if="notizData.statusNew" class="fw-bold">{{$p.t('notiz','notiz_new')}} <span> [{{notizData.typeId}}]</span></p>
							<p v-else class="fw-bold">{{$p.t('notiz','notiz_edit')}} <span> [{{notizData.typeId}}]</span></p>
						</div>
												
						<div class="row mb-3">
							<form-input
								container-class="col-12"
								:label="$p.t('global','titel')  + ' *'"
								type="text"
								v-model="notizData.titel"
								name="titel"
								>
							</form-input>
						</div>
									
						<div class="row mb-3">
							<!-- TinyMce 5 -->
							<div v-if="showTinyMce" class="col-sm-12">
								<form-input
									ref="editor"
									:label="$p.t('global', 'text') + ' *'"
									type="textarea"
									v-model="notizData.text"
									name="text"
									rows="5"
									cols="75"
									>
								</form-input>
							</div>
							
							<div v-else class="col-sm-12">
								<form-input
									ref="editor"
									:label="$p.t('global','text')  + ' *'"
									type="textarea"
									v-model="notizData.text"
									name="text"
									rows="5"
									cols="75"
									>
								</form-input>
							</div>
						</div>
				
						<!-- show Documentupload-->
						<div v-if="showDocument">
							<div class="row mb-3">		
								<div  class="col-sm-12 py-3">
									<label for="text" class="form-label col-sm-2">{{$p.t('notiz','document')}}</label>
									<!--Upload Component-->
									<FormUploadDms ref="upload" id="file" multiple v-model="notizData.anhang"></FormUploadDms>
								</div>
							</div>
						</div>
						
						<!-- show Details-->
						<div v-if="showErweitert">
							<div class="row mb-3">
								<form-input
									container-class="col-6"
									:label="$p.t('global', 'gueltigVon')"
									type="DatePicker"
									v-model="notizData['start']"
									name="von"
									auto-apply
									:enable-time-picker="false"
									format="dd.MM.yyyy"
									preview-format="dd.MM.yyyy"
									:teleport="true"
									>
								</form-input>
								
								<form-input
									container-class="col-6"
									:label="$p.t('global', 'gueltigBis')"
									type="DatePicker"
									v-model="notizData.ende"
									name="bis"
									auto-apply
									:enable-time-picker="false"
									format="dd.MM.yyyy"
									preview-format="dd.MM.yyyy"
									:teleport="true"
									>
								</form-input>
							</div>
							
					
							<div class="row mb-3">
								<form-input 
									v-if="notizData.verfasser_uid"
									container-class="col-6"
									:label="$p.t('notiz', 'verfasser')"
									type="text"
									v-model="notizData.verfasser_uid"
									name="titel"
									>
								</form-input>
						
								<form-input
									v-else
									container-class="col-6"
									:label="$p.t('notiz', 'verfasser')"
									type="autocomplete"
									v-model="notizData.intVerfasser"
									:suggestions="filteredMitarbeiter" 
									@complete="search" 
									optionLabel="mitarbeiter"
									minLength="3"
									>
								</form-input>
								
								<form-input
									v-if="notizData.bearbeiter_uid"
									container-class="col-6"
									:label="$p.t('notiz', 'bearbeiter')"
									v-model="notizData.bearbeiter_uid"
									minlength="3"
									>
								</form-input>
								
								<form-input
									v-else
									container-class="col-6"
									:label="$p.t('notiz', 'bearbeiter')"
									type="autocomplete"
									v-model="notizData.intBearbeiter"
									:suggestions="filteredMitarbeiter" 
									@complete="search" 
									optionLabel="mitarbeiter"
									minlength="3"
									>
								</form-input>	
							</div>
															
							<div class="row mb-3">
								<div class="col-2 pt-4 d-flex align-items-center">
									<form-input
										container-class="form-check"
										:label="$p.t('notiz', 'erledigt')"
										type="checkbox"
										v-model="notizData.erledigt"
										name="erledigt"
										>
									</form-input>
								</div>
							</div>
						</div>
						
						<div class="row mb-3">
							<label for="lastUpdate" class="form-label col-sm-3 small">{{$p.t('notiz','letzte_aenderung')}}</label>
							<div class="col-sm-5">
								<p class="small">{{notizData.lastupdate}}</p>
							</div>
						</div>
						
						<div>
							<button v-if="notizData.statusNew" class="btn btn-primary" @click="addNewNotiz()"> {{$p.t('studierendenantrag', 'btn_new')}}</button>
							<button v-else class="btn btn-primary" @click="updateNotiz(notizData.notiz_id)"> {{$p.t('ui', 'speichern')}}</button>
						</div>
					</form-form>
				</div>
				
				<div class="col-sm-6 pt-6">
					<br>
					<core-filter-cmpt
						ref="table"
						:tabulator-options="tabulatorOptions"
						:tabulator-events="tabulatorEvents"
						table-only
						:side-menu="false"
						reload
						new-btn-show
						:new-btn-label="this.$p.t('global', 'notiz')"
						@click:new="actionNewNotiz"
						>
					</core-filter-cmpt>	
				</div>
			</div> 
		</div>
		
		<div v-else-if="notizLayout=='popupModal'" class="notiz-notiz">
			
			<BsModal ref="NotizModal">
				<template #title>
					<p v-if="notizData.statusNew" class="fw-bold">{{$p.t('notiz','notiz_new')}} <span> [{{notizData.typeId}}]</span></p>
					<p v-else class="fw-bold">{{$p.t('notiz','notiz_edit')}} <span> [{{notizData.typeId}}]</span></p>
				</template>
				<template #default>
					<div>
				  	<form-form ref="formNotiz" @submit.prevent class="row">
						<div class="row mb-3">
							<form-input
								container-class="col-12"
								:label="$p.t('global','titel')  + ' *'"
								type="text"
								v-model="notizData.titel"
								name="titel"
								>
							</form-input>
						</div>
									
						<div class="row mb-3">
							<!-- TinyMce 5 -->
							<div v-if="showTinyMce" class="col-sm-12">
								<form-input
									ref="editor"
									:label="$p.t('global','text')  + ' *'"
									type="textarea"
									v-model="notizData.text"
									name="text"
									rows="5"
									cols="75"
									>
								</form-input>
							</div>
							
							<div v-else class="col-sm-12">
								<form-input
									ref="editor"
									:label="$p.t('global','text')  + ' *'"
									type="textarea"
									v-model="notizData.text"
									name="text"
									rows="5"
									cols="75"
									>
								</form-input>
							</div>
						</div>
				
						<!-- show Documentupload-->
						<div v-if="showDocument">
							<div class="row mb-3">		
								<div  class="col-sm-12 py-3">
									<label for="text" class="form-label col-sm-2">{{$p.t('notiz','document')}}</label>
									<!--Upload Component-->
									<FormUploadDms ref="upload" id="file" multiple v-model="notizData.anhang"></FormUploadDms>
								</div>
							</div>
						</div>
						
						<!-- show Details-->
						<div v-if="showErweitert">
							<div class="row mb-3">
								<form-input
									container-class="col-6"
									:label="$p.t('global', 'gueltigVon')"
									type="DatePicker"
									v-model="notizData['start']"
									name="von"
									auto-apply
									:enable-time-picker="false"
									format="dd.MM.yyyy"
									preview-format="dd.MM.yyyy"
									:teleport="true"
									>
								</form-input>
								
								<form-input
									container-class="col-6"
									:label="$p.t('global', 'gueltigBis')"
									type="DatePicker"
									v-model="notizData.ende"
									name="bis"
									auto-apply
									:enable-time-picker="false"
									format="dd.MM.yyyy"
									preview-format="dd.MM.yyyy"
									:teleport="true"
									>
								</form-input>
							</div>
							
					
							<div class="row mb-3">
								<form-input 
									v-if="notizData.verfasser_uid"
									container-class="col-6"
									:label="$p.t('notiz', 'verfasser')"
									type="text"
									v-model="notizData.verfasser_uid"
									name="titel"
									>
								</form-input>
						
								<form-input
									v-else
									container-class="col-6"
									:label="$p.t('notiz', 'verfasser')"
									type="autocomplete"
									v-model="notizData.intVerfasser"
									:suggestions="filteredMitarbeiter" 
									@complete="search" 
									optionLabel="mitarbeiter"
									minLength="3"
									>
								</form-input>
								
								<form-input
									v-if="notizData.bearbeiter_uid"
									container-class="col-6"
									:label="$p.t('notiz', 'bearbeiter')"
									v-model="notizData.bearbeiter_uid"
									minlength="3"
									>
								</form-input>
								
								<form-input
									v-else
									container-class="col-6"
									:label="$p.t('notiz', 'bearbeiter')"
									type="autocomplete"
									v-model="notizData.intBearbeiter"
									:suggestions="filteredMitarbeiter" 
									@complete="search" 
									optionLabel="mitarbeiter"
									minlength="3"
									>
								</form-input>	
							</div>
															
							<div class="row mb-3">
								<div class="col-2 pt-4 d-flex align-items-center">
									<form-input
										container-class="form-check"
										:label="$p.t('notiz', 'erledigt')"
										type="checkbox"
										v-model="notizData.erledigt"
										name="erledigt"
										>
									</form-input>
								</div>
							</div>
						</div>
						
						<div class="row mb-3">
							<label for="lastUpdate" class="form-label col-sm-3 small">{{$p.t('notiz','letzte_aenderung')}}</label>
							<div class="col-sm-5">
								<p class="small">{{notizData.lastupdate}}</p>
							</div>
						</div>
						
						<div>
							<button v-if="notizData.statusNew" class="btn btn-primary" @click="addNewNotiz()"> {{$p.t('studierendenantrag', 'btn_new')}}</button>
							<button v-else class="btn btn-primary" @click="updateNotiz(notizData.notiz_id)"> {{$p.t('ui', 'speichern')}}</button>
						</div>
					</form-form>
				</div>
				</template>
			</BsModal>
			
			<div class="row">
				<core-filter-cmpt
					ref="table"
					:tabulator-options="tabulatorOptions"
					:tabulator-events="tabulatorEvents"
					table-only
					:side-menu="false"
					reload
					new-btn-show
					:new-btn-label="this.$p.t('global', 'notiz')"
					@click:new="actionNewNotiz"
					>
				</core-filter-cmpt>	
			</div> 
		</div>
		
		<div v-else>
			<p v-if="notizLayout">Falsches Layout übergeben: {{notizLayout}}</p>
			<p v-else>Kein Layout übergeben</p>
		</div>
	</div>`,
}