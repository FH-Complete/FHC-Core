import VueDatePicker from '../vueDatepicker.js.php';
import {CoreFilterCmpt} from "../filter/Filter.js";
import BsModal from "../Bootstrap/Modal";
import FormForm from '../Form/Form.js';
import FormInput from '../Form/Input.js';

export default {
	components: {
		CoreFilterCmpt,
		VueDatePicker,
		BsModal,
		FormForm,
		FormInput,
	},
	inject: {
		cisRoot: {
			from: 'cisRoot'
		},
	},
	props: [
		'person_id',
		'uid'
		],
	data(){
		return {
			tabulatorOptions: {
				ajaxURL: 'api/frontend/v1/stv/Betriebsmittel/getAllBetriebsmittel/' + this.uid + '/' + this.person_id,
				ajaxRequestFunc: this.$fhcApi.get,
				ajaxResponse: (url, params, response) => response.data,
				columns: [
					{title: "Nummer", field: "nummer"},
					{title:  "PersonId", field: "person_id"},
					{title: "Typ", field: "betriebsmitteltyp"},
				//	{title: "insertVon", field: "insertvon"}, //Test
				//	{title: "insertAmUm", field: "insertamum"}, //TESt
				//	{title: "Betriebsmittelperson_id", field: "betriebsmittelperson_id"},
					{title:  "Retourdatum", field: "retouram"},
					{title:  "Beschreibung", field: "beschreibung"},
					{title:  "Uid", field: "uid"},
					{title:  "Anmerkung", field: "anmerkung", visible: false},
					{title:  "Kaution", field: "kaution", visible: false},
					{title:  "Ausgabedatum", field: "ausgegebenam", visible: false},
					{title: "Betriebsmittel_id", field: "betriebsmittel_id", visible: false},
					{title: "Betriebsmittelperson_id", field: "betriebsmittelperson_id", visible: false},
					{
						title: 'Aktionen', field: 'actions',
						minWidth: 150, // Ensures Action-buttons will be always fully displayed
						formatter: (cell, formatterParams, onRendered) => {
							let container = document.createElement('div');
							container.className = "d-flex gap-2";

							let button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-print"></i>';
							button.title = 'Übernahmebestätigung drucken';
							let cellData = cell.getData();
							button.addEventListener(
								'click',
								(event) =>
								{
									let linkToPdf = this.cisRoot +
										'/content/pdfExport.php?xml=betriebsmittelperson.rdf.php&xsl=Uebernahme&id=' + cellData.betriebsmittelperson_id + '&output=pdf';

								window.open(linkToPdf, '_blank');
							});
							container.append(button);

							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-edit"></i>';
							button.title = 'Betriebsmittel bearbeiten';
							button.addEventListener(
								'click',
								(event) =>
									this.actionEditBetriebsmittel(cell.getData().betriebsmittelperson_id)
							);
							container.append(button);

							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-xmark"></i>';
							button.title = 'Betriebsmittel löschen';
							button.addEventListener(
								'click',
								() =>
									this.actionDeleteBetriebsmittel(cell.getData().betriebsmittelperson_id)
							);
							container.append(button);

							return container;
						},
						frozen: true
					}],
				layout: 'fitColumns',
				layoutColumnsOnNewData: false,
				height: '150',
				selectableRangeMode: 'click',
				selectable: true
			},
			//tableData: [],
			tabulatorEvents: [
				{
/*					//TODO Manu phrases
					}*/

					//TODO(manu)für editieren mit click: conflict with action buttons

/*             event: 'rowSelectionChanged',
			   handler: this.rowSelectionChanged*/
/*              handler: (e, row.getData().betriebsmittelperson_id) => {
// HandlerFunktion, die aufgerufen wird, wenn das Ereignis ausgelöst wird
					   //this.rowSelectionChanged(row.getData().betriebsmittelperson_id);
			   //      console.log("Selected Row Data:", row.getData());
					   //console.log(cell.getData().betriebsmittelperson_id);
*/
				},
			],
			betriebsmittelData: {},
			betriebsmittelperson_id : null,
			listBetriebsmitteltyp: [],
			formData: {
				ausgegebenam : this.getDefaultDate(),
			},
			statusNew: true,
			changesDetected: false
		};
	},
	computed: {
		deltaLength() {
			return Object.keys(this.formData).length;
		}
	},
	watch: {
		formData: {
			handler: function(newVal) {

				//console.log('Das Objekt wurde geändert:', newVal);
				this.changesDetected = true;
			},
			deep: true  // Hiermit wird auch die Veränderung von Eigenschaften des Objekts überwacht
		}
	},
	methods: {
		rowSelectionChanged(data) {
			//console.log("Selected Row Data:", data[0].betriebsmittelperson_id);
			this.param_id = {
				'betriebsmittelperson_id':  data[0].betriebsmittelperson_id};

			this.loadBetriebsmittel(this.param_id);
		},
		actionEditBetriebsmittel(betriebsmittelperson_id){
			console.log("action EditBM: id: " + betriebsmittelperson_id);
			this.statusNew = false;
			this.loadBetriebsmittel(betriebsmittelperson_id);
			this.changesDetected = false;
		},
		actionNewBetriebsmittel(){
			console.log("action newBM: person " + this.person_id);
			this.resetModal();
			this.statusNew = true;
			this.formData.ausgegebenam = this.getDefaultDate();
			//this.changesDetected = false;
			this.reload();
		},
		actionDeleteBetriebsmittel(betriebsmittelperson_id){
			console.log("Löschen von Datensatz mit id: " + betriebsmittelperson_id);

			this.loadBetriebsmittel(betriebsmittelperson_id).then(() => {
/*				if(this.formData) {
					this.$refs.deleteBetriebsmittelModal.show();
				}*/
				console.log("nach load" + betriebsmittelperson_id);
				this.$refs.deleteBetriebsmittelModal.show();
			});
		},
		addNewBetriebsmittel(){
			this.param = {
				'uid':  this.uid,
				'person_id': this.person_id,
				...this.formData
			};
			this.$fhcApi.post('api/frontend/v1/stv/betriebsmittel/addNewBetriebsmittel/',
				this.param
			).then(response => {
				console.log(response);
				this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
			//	this.hideModal('newBetriebsmittelModal');
				this.resetModal();
			}).catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					window.scrollTo(0, 0);
					this.reload();
				});
			//this.changesDetected = false; //not working
		},
		deleteBetriebsmittel(betriebsmittelperson_id){
			console.log("Delete mit id: " + betriebsmittelperson_id);
			this.param = {
				'betriebsmittelperson_id': betriebsmittelperson_id
			};
			return this.$fhcApi.post('api/frontend/v1/stv/betriebsmittel/deleteBetriebsmittel/',
				this.param)
				.then(
					result => {
						this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));
						this.hideModal('deleteBetriebsmittelModal');
						this.resetModal();
					})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					window.scrollTo(0, 0);
					this.reload();
				});
		},
		updateBetriebsmittel(){
			this.param = {
				'uid':  this.uid,
				'person_id': this.person_id,
				...this.formData
			};
			this.$fhcApi.post('api/frontend/v1/stv/betriebsmittel/updateBetriebsmittel/',
				this.param
			).then(response => {
				//console.log(response);
				this.$fhcAlert.alertSuccess(this.$p.t('ui', 'editSave'));
				//	this.hideModal('newBetriebsmittelModal');
				this.resetModal();
			}).catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					window.scrollTo(0, 0);
					this.reload();
				});
		},
		loadBetriebsmittel(betriebsmittelperson_id){
			console.log("loadBetriebsmittel id:" + betriebsmittelperson_id);
			this.resetModal();
			this.statusNew = false;
			this.param = {
				'betriebsmittelperson_id':  betriebsmittelperson_id
			};
			return this.$fhcApi.post('api/frontend/v1/stv/betriebsmittel/loadBetriebsmittel/',
				this.param)
				.then(result => result.data)
				.then(result => {
					this.formData = result;
				})
				.catch(this.$fhcAlert.handleSystemError);
		//	this.changesDetected = false;
		},
		reload(){
			this.$refs.table.reloadTable();
			//this.changesDetected = false;
		},
		hideModal(modalRef){
			this.$refs[modalRef].hide();
		},
		resetModal(){
			this.formData = {};
			this.formData.ausgegebenam = this.getDefaultDate();
			this.formData.listBetriebsmitteltyp = null;
			this.betriebsmittelperson_id = {};
			this.statusNew = true;
			//this.changesDetected = false;
		},
		getDefaultDate() {
			const today = new Date();
			return today;
		}
	},
	created(){
		this.$fhcApi
			.get('api/frontend/v1/stv/betriebsmittel/getTypenBetriebsmittel')
			.then(result => result.data)
			.then(result => {
				this.listBetriebsmitteltyp = result;
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	mounted(){

	},
/*	async mounted() {
		if(this.showTinyMCE){
			this.initTinyMCE();
		}

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
	},*/
	template: `
	<div class="betriebsmittel-betriebsmittel">
	
		<!--Modal: deleteBetriebsmittelModal-->
		<BsModal ref="deleteBetriebsmittelModal">
			<template #title>Betriebsmittel löschen</template>
			<template #default>
				<p>Betriebsmittel ({{formData.betriebsmitteltyp}} / {{formData.betriebsmittelperson_id}}) wirklich löschen?</p>
			</template>
			<template #footer>
<!--				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="resetModal">Abbrechen</button>-->
				<button ref="Close" type="button" class="btn btn-primary" @click="deleteBetriebsmittel(formData.betriebsmittelperson_id)">OK</button>
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
			new-btn-label="Betriebsmittel"
			@click:new="actionNewBetriebsmittel"
			>
		</core-filter-cmpt>
		<br>
		 {{cisRoot}}
		 {{formData}} {{changesDetected}}
				<hr>
			<form-form class="row g-3 col-6" ref="betriebsmittelData">
				<legend>Details</legend>
				
				<div class="row mb-3">
					<div class="col-sm-7">
						<p v-if="statusNew" class="fw-bold"> Betriebsmittel anlegen</p>
						<p v-else class="fw-bold">Betriebsmittel bearbeiten</p>
					</div>
				</div>
				
				<div class="row mb-3">								   
					<label for="typ" class="form-label col-sm-4">Typ</label>
					<div class="col-sm-6">
						<form-input
						type="select"
						name="typ"
						v-model="formData.betriebsmitteltyp"
						>
						<option v-for="entry in listBetriebsmitteltyp" :key="entry.betriebsmitteltyp" :value="entry.betriebsmitteltyp">{{entry.betriebsmitteltyp}}</option>
						</form-input>
					</div>
				</div>
				
				<div class="row mb-3">
					<label for="nummer" class="form-label col-sm-4">Nummer</label>
					<div class="col-sm-6">
						<form-input
							type="text"
							name="nummer"
							v-model="formData['nummer']"
						>						
						</form-input>
					</div>
				</div>
				
				<div class="row mb-3">
					<label for="nummer2" class="form-label col-sm-4">Nummer2</label>
					<div class="col-sm-6">
						<form-input
							type="text"
							name="nummer2"
							v-model="formData['nummer2']"
						>						
						</form-input>
					</div>
				</div>
			
				<div class="row mb-3">
					<label for="beschreibung" class="form-label col-sm-4">Beschreibung</label>
					<div class="col-sm-6">
						<form-input
							type="textarea"
							name="beschreibung"
							v-model="formData['beschreibung']"
						>						
						</form-input>
					</div>
				</div>
				
				<div class="row mb-3">
					<label for="kaution" class="form-label col-sm-4">Kaution</label>
					<div class="col-sm-6">
						<form-input
							type="text"
							name="kaution"
							v-model="formData['kaution']"
						>						
						</form-input>
					</div>
				</div>
				
				<div class="row mb-3">
					<label for="anmerkung" class="form-label col-sm-4">Anmerkung</label>
					<div class="col-sm-6">
						<form-input
							type="textarea"
							name="anmerkung"
							v-model="formData['anmerkung']"
						>						
						</form-input>
					</div>
				</div>
				
				<div class="row mb-3">
					<label for="ausgegebenam" class="form-label col-sm-4">Ausgegeben am</label>
					<div class="col-sm-6">
						<form-input
							type="DatePicker"
							:readonly="readonly"
							name="datum"
							v-model="formData['ausgegebenam']"
							auto-apply
							:enable-time-picker="false"
							format="dd.MM.yyyy"
							preview-format="dd.MM.yyyy"
							:teleport="true"
						></form-input>
					</div>
				</div>
				
				<div class="row mb-3">
					<label for="retouram" class="form-label col-sm-4">Retour am</label>
					<div class="col-sm-6">
						<form-input
							type="DatePicker"
							:readonly="readonly"
							name="datum"
							v-model="formData['retouram']"
							auto-apply
							:enable-time-picker="false"
							format="dd.MM.yyyy"
							preview-format="dd.MM.yyyy"
							:teleport="true"
						></form-input>
					</div>
<!--					<div class="position-sticky top-0 z-1">
						<button type="submit" class="btn btn-primary position-absolute top-0 end-0" :disabled="!deltaLength">Speichern2</button>
					</div>-->
				</div>
				

				<div class="row mb-3">
				<label class="form-label col-sm-8"></label>
					<div v-if="statusNew" class="col-sm-4">
						<button ref="Close" type="button" class="btn btn-primary" @click="addNewBetriebsmittel()" :disabled="!changesDetected">Speichern</button>
					</div>
					<div v-else class="col-sm-4">
						<button ref="Close" type="button" class="btn btn-warning" @click="updateBetriebsmittel()" :disabled="!changesDetected">Aktualisieren</button>
					</div>
					
				</div>
		</form-form>
		
	</div>`
}

