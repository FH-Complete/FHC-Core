//import NotizList from "./Notizen/Notizen.js";
import {CoreRESTClient} from "../../../../RESTClient.js";
import {CoreFilterCmpt} from "../../../filter/Filter.js";
import Notiz from "../../../Notiz/Notiz.js";

var editIcon = function (cell, formatterParams) {
	return "<i class='fa fa-edit'></i>";
};
var deleteIcon = function (cell, formatterParams){
	return "<i class='fa fa-remove'></i>";
};

export default {
	components: {
		CoreRESTClient,
		CoreFilterCmpt,
		Notiz
	},
	props: {
		modelValue: Object
	},
	data() {
		return {
			tabulatorOptions: {
				ajaxURL: CoreRESTClient._generateRouterURI('components/stv/Notiz/getNotizen/' + this.modelValue.person_id),
				columns: [
					{title: "Titel", field: "titel"},
					{title: "Text", field: "text", width: 350},
					{title: "VerfasserIn", field: "verfasser_uid"},
					{title: "BearbeiterIn", field: "bearbeiter_uid", visible: false},
					{title: "Start", field: "start"},
					{title: "Ende", field: "ende"},
					{title: "Dokumente", field: "dms_id"},
					{title: "Erledigt", field: "erledigt"},
					{title: "Notiz_id", field: "notiz_id", visible: false},
					{title: "Notizzuordnung_id", field: "notizzuordnung_id", visible: false},
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
				selectable: true,
				index: 'notiz_id'
			},
			tabulatorEvents: [],
			notizen: [],
			formData: {
				titel: null,
				action: 'Neue Notiz',
				text: null,
				von: null,
				bis: null,
				dms_id: null,
				erledigt: false,
				verfasser: null,
				bearbeiter: null
			},
		}
	},
	methods:{
		actionEditNotiz(notiz_id){
			this.loadNotiz(notiz_id).then(() => {
				if(this.notizen.notiz_id) {
					this.formData.titel = this.notizen.titel;
					this.formData.action = 'Notiz bearbeiten';
					this.formData.text = this.notizen.text;
					this.formData.von = this.notizen.start;
					this.formData.bis = this.notizen.ende;
					this.formData.doc = this.notizen.dms_id;
					this.formData.erledigt = this.notizen.erledigt;
					this.formData.verfasser = this.notizen.verfasser_uid;
					this.formData.bearbeiter = this.notizen.bearbeiter_uid;
				}
			});
		},
		actionNewNotiz(){
			this.formData.titel = '';
			this.formData.action = 'Neue Notiz';
			this.formData.text = null;
			this.formData.von = null;
			this.formData.bis = null;
			this.formData.dms_id = null;
			this.formData.erledigt = false;
			this.formData.verfasser = null;
			this.formData.bearbeiter = null;
		},
		addNewNotiz(notizData) {
			CoreRESTClient.post('components/stv/Notiz/addNewNotiz/' + this.modelValue.person_id,
				this.formData
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
				this.$fhcAlert.alertError('Fehler bei Speicherroutine aufgetreten');
			}).finally(() => {
				window.scrollTo(0, 0);
			});
		},
		loadNotiz(notiz_id){
			return CoreRESTClient.get('components/stv/Notiz/loadNotiz/' + notiz_id)
				.then(
					result => {
						if(result.data.retval)
							this.notizen = result.data.retval;
						else
						{
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
			this.formData.titel = null;
			this.formData.text = null;
			this.formData.von = null;
			this.formData.bis = null;
			this.formData.dms_id = null;
			this.formData.erledigt = false;
			this.formData.verfasser = null;
			this.formData.bearbeiter = null;
		},
		updateNotiz(notiz_id){
			CoreRESTClient.post('components/stv/Notiz/updateNotiz/' + notiz_id,
				this.formData
			).then(response => {
				if (!response.data.error) {
					this.$fhcAlert.alertSuccess('Update erfolgreich');
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
				this.statusMsg = 'Error in Catch';
				this.$fhcAlert.alertError('Fehler bei Updateroutine aufgetreten');
			}).finally(() => {
				window.scrollTo(0, 0);
				//this.reload();
			});
		}
	},
	template: `
	<div class="stv-details-details h-100 pb-3">
	
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
			
		<Notiz 
			v-model:titel="formData.titel" 
			v-model:text="formData.text" 
			v-model:action="formData.action" 
			v-model:von="formData.von" 
			v-model:bis="formData.bis" 
			v-model:document="formData.dms_id"
			v-model:erledigt="formData.erledigt"
			v-model:verfasser="formData.verfasser"
			v-model:bearbeiter="formData.bearbeiter"
			></Notiz>
			
			
			<hr>
			Parent: 	{{titel}}  {{text}}| {{notizTitel}} {{notizText}}
			<br> {{modelValue}}
			<br> {{formData}}
			<hr>
		<button v-if="formData.action === 'Neue Notiz'"  type="button" class="btn btn-primary" @click="addNewNotiz()"> Neu anlegen </button>
		<button v-else type="button" class="btn btn-warning" @click="updateNotiz(notizen.notiz_id)"> Speichern </button>
	</div>
`
};