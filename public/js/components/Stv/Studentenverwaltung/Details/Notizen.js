import {CoreRESTClient} from "../../../../RESTClient.js";
import {CoreFilterCmpt} from "../../../filter/Filter.js";
import Notiz from "../../../Notiz/Notiz.js";
import BsModal from "../../../Bootstrap/Modal";

var editIcon = function (cell, formatterParams) {
	return "<i class='fa fa-edit'></i>";
};
var deleteIcon = function (cell, formatterParams) {
	return "<i class='fa fa-remove text-danger'></i>";
};

export default {
	components: {
		CoreRESTClient,
		CoreFilterCmpt,
		Notiz,
		BsModal
	},
	props: {
		modelValue: Object
	},
	data(){
		return {
			tabulatorOptions: {
				ajaxURL: CoreRESTClient._generateRouterURI('components/stv/Notiz/getNotizen/' + this.modelValue.person_id + '/person_id'),
				//ajaxURL: CoreRESTClient._generateRouterURI('components/stv/Notiz/getNotizen/' + this.modelValue.person_id + '/' + this.formData.typeId),
				columns: [
					{title: "Titel", field: "titel"},
					{title: "Text", field: "text_stripped", width: 350},
					{title: "VerfasserIn", field: "verfasser_uid"},
					{title: "BearbeiterIn", field: "bearbeiter_uid", visible: false},
					{title: "Start", field: "start", visible: false},
					{title: "Ende", field: "ende", visible: false},
					{title: "Dokumente", field: "countdoc"},
					{title: "Erledigt", field: "erledigt", visible: false},
					{title: "Notiz_id", field: "notiz_id", visible: false},
					{title: "Notizzuordnung_id", field: "notizzuordnung_id", visible: false},
					{title: "letzte Änderung", field: "lastupdate", visible: false},
					{
						formatter: editIcon, cellClick: (e, cell) => {
							this.actionEditNotiz(cell.getData().notiz_id);
							//console.log(cell.getRow().getIndex(), cell.getData(), this);
						}, width: 50, headerSort: false, headerVisible: false
					},
					{
						formatter: deleteIcon, cellClick: (e, cell) => {
							this.actionDeleteNotiz(cell.getData().notiz_id);

						}, width: 50, headerSort: false, headerVisible: false
					},
				],
				layout: 'fitDataFill',
				layoutColumnsOnNewData: false,
				height: '150',
				selectableRangeMode: 'click',
				selectable: true,
				index: 'notiz_id',
/*				rowClick: (e, row) => {
					const notizId = row.getData().notiz_id;
					console.log(notizId);
					this.actionEditNotiz(notizId);
				},*/
			},
			tabulatorEvents: [],
			notizen: [],
			uid: '',
			intVerfasser: '',
			filteredMitarbeiter: [],
			formData: {
				typeId: 'person_id',
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
			},
			showErweitert: true, //show details verfasser, bearbeiter, von, bis, erledigt
			showDocument: true //show upload documents

		};
	},
	methods:{
		actionDeleteNotiz(notiz_id){
			this.loadNotiz(notiz_id).then(() => {
				if(this.notizen.notiz_id) {
					this.$refs.deleteNotizModal.show();
				}
			});
		},
		actionEditNotiz(notiz_id){
			this.loadNotiz(notiz_id).then(() => {
				if(this.notizen.notiz_id) {
					this.formData.titel = this.notizen.titel;
					this.formData.statusNew = false;
					this.formData.text = this.notizen.text;
					this.formData.lastChange = this.notizen.lastupdate;
					this.formData.von = this.notizen.start;
					this.formData.bis = this.notizen.ende;
					this.formData.document = this.notizen.dms_id;
					this.formData.erledigt = this.notizen.erledigt;
					this.formData.verfasser = this.notizen.verfasser_uid;
					this.formData.bearbeiter = this.notizen.bearbeiter_uid;
				}
			})
				.then(() => {
					if(this.notizen.dms_id){
						console.log("loadEntries with " + this.notizen.notiz_id);
						this.loadDocEntries(this.notizen.notiz_id);
						//console.log(this.formData.anhang);
					}
				});
		},
		actionNewNotiz(){
			this.resetFormData();
			this.formData.typeId = 'person_id';
			this.formData.titel = '';
			this.formData.statusNew = true;
			this.formData.text = null;
			this.formData.lastChange = null;
			this.formData.von = null;
			this.formData.bis = null;
			this.formData.document = null;
			this.formData.erledigt = false;
			this.formData.verfasser = this.uid;
			this.formData.bearbeiter = null;
			this.formData.anhang = [];
		},
		addNewNotiz(notizData) {
			const formData = new FormData();

			formData.append('data', JSON.stringify(this.formData));
			Object.entries(this.formData.anhang).forEach(([k, v]) => formData.append(k, v));
			CoreRESTClient.post(
				'components/stv/Notiz/addNewNotiz/' + this.modelValue.person_id,
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
		loadDocEntries(notiz_id){
			return CoreRESTClient.get('components/stv/Notiz/loadDokumente/' + notiz_id)
				.then(
					result => {
						if(result.data.retval) {
							this.formData.anhang = result.data.retval;
							console.log(this.formData.anhang);
						}
						else
						{
							this.formData.anhang = {};
							this.$fhcAlert.alertError('Kein Dokumenteneintrag mit NotizId ' + notiz_id + ' gefunden');
						}
						return result;
					}
				);
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
		reload(){
			this.$refs.table.reloadTable();
		},
		resetFormData(){
			this.$refs.form.reset();
			this.formData = {
				typeId: 'person_id',
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
		updateNotiz(notiz_id){
			const formData = new FormData();

			formData.append('data', JSON.stringify(this.formData));
			Object.entries(this.formData.anhang).forEach(([k, v]) => formData.append(k, v));
			//console.log(this.formData);

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
	},
	created(){
		CoreRESTClient
			.get('components/stv/Notiz/getUid')
			.then(result => {
				if(result.data.retval) {
					this.formData.verfasser = result.data.retval;
				}
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	computed: {	},
	template: `
	<div class="stv-details-details h-100 pb-3">

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

	<br>
	<hr>
		<Notiz
			ref="form"
			:showErweitert="showErweitert"
			:showDocument="showDocument"
			v-model:typeId="formData.typeId"
			v-model:titel="formData.titel"
			v-model:text="formData.text"
			:lastChange="formData.lastChange"
			v-model:statusNew="formData.statusNew"
			v-model:von="formData.von"
			v-model:bis="formData.bis"
			v-model:document="formData.document"
			v-model:erledigt="formData.erledigt"
			v-model:verfasser="formData.verfasser"
			v-model:bearbeiter="formData.bearbeiter"
			v-model:anhang="formData.anhang"
		>
		</Notiz>
			
		<button v-if="formData.statusNew"  type="button" class="btn btn-primary" @click="addNewNotiz()"> Neu anlegen </button>
		<button v-else type="button" class="btn btn-primary" @click="updateNotiz(notizen.notiz_id)"> Speichern </button>

	</div>
	`
};
