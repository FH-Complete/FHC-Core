import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import {CoreRESTClient} from "../../../../../RESTClient";
import BsModal from "../../../../Bootstrap/Modal.js";
import FormForm from '../../../../Form/Form.js';
import FormInput from '../../../../Form/Input.js';

export default{
	components: {
		CoreFilterCmpt,
		BsModal,
		FormForm,
		FormInput,
	},
	inject: {
		defaultSemester: {
			from: 'defaultSemester',
		},
		hasPermissionToSkipStatusCheck: {
			from: 'hasPermissionToSkipStatusCheck',
			default: false
		},
		hasPrestudentPermission: {
			from: 'hasPrestudentPermission',
			default: false
		},
		hasAssistenzPermission: {
			from: 'hasAssistenzPermission',
			default: false
		},
		hasAdminPermission: {
			from: 'hasAdminPermission',
			default: false
		},
		hasAssistenzPermissionForStgs: {
			from: 'hasAssistenzPermissionForStgs',
			default: false
		},
		hasSchreibrechtAss: {
			from: 'hasSchreibrechtAss',
			default: false
		}
	},
	props: {
		prestudent_id: String
	},
	data() {
		return {
			tabulatorOptions: {
				ajaxURL: CoreRESTClient._generateRouterURI('components/stv/Status/getHistoryPrestudent/' + this.prestudent_id),
				//autoColumns: true,
				columns: [
					{title: "Kurzbz", field: "status_kurzbz", tooltip: true},
					{title: "StSem", field: "studiensemester_kurzbz"},
					{title: "Sem", field: "ausbildungssemester"},
					{title: "Lehrverband", field: "lehrverband"},
					{title: "Datum", field: "format_datum"},
					{title: "Studienplan", field: "bezeichnung"},
					{title: "BestätigtAm", field: "format_bestaetigtam"},
					{title: "AbgeschicktAm", field: "format_bewerbung_abgeschicktamum"},
					{title: "Statusgrund", field: "statusgrund_kurzbz", visible: false},
					{title: "Organisationsform", field: "orgform_kurzbz", visible: false},
					{title: "PrestudentInId", field: "prestudent_id", visible: false},
					{title: "StudienplanId", field: "studienplan_id", visible: false},
					{title: "Anmerkung", field: "anmerkung", visible: false},
					{title: "BestätigtVon", field: "bestaetigtvon", visible: false},
					{title: "InsertAmUm", field: "format_insertamum", visible: false},
					{title: "InsertVon", field: "insertvon", visible: false},
					{title: "UpdateAmUm", field: "format_updateamum", visible: false},
					{title: "UpdateVon", field: "updatevon", visible: false},
					{
						title: 'Aktionen', field: 'actions',
						minWidth: 150, // Ensures Action-buttons will be always fully displayed
						formatter: (cell, formatterParams, onRendered) => {


							//let disableButton = false;
							//const rowData = this.row.getData();


							let container = document.createElement('div');
							container.className = "d-flex gap-2";

							let button = document.createElement('button');
							if (this.dataMeldestichtag && this.dataMeldestichtag > cell.getData().datum)
								button.className = 'btn btn-outline-secondary btn-action disabled';
							else
								button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-forward"></i>';
							button.title = 'Status vorrücken';
							button.addEventListener('click', () =>
								this.actionAdvanceStatus(cell.getData().status_kurzbz, cell.getData().studiensemester_kurzbz, cell.getData().ausbildungssemester)
							);
							container.append(button);

							button = document.createElement('button');
							if (this.dataMeldestichtag && this.dataMeldestichtag > cell.getData().datum)
								button.className = 'btn btn-outline-secondary btn-action disabled';
							else
								button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-check"></i>';
							button.title = 'Status bestätigen';
							button.addEventListener('click', () =>
								this.actionConfirmStatus(cell.getData().status_kurzbz, cell.getData().studiensemester_kurzbz, cell.getData().ausbildungssemester)
							);
							container.append(button);

							button = document.createElement('button');
							if (this.dataMeldestichtag && this.dataMeldestichtag > cell.getData().datum)
								button.className = 'btn btn-outline-secondary btn-action disabled';
							else
								button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-edit"></i>';
							button.title = 'Status bearbeiten';
							button.addEventListener('click', (event) =>
								this.actionEditStatus(cell.getData().status_kurzbz, cell.getData().studiensemester_kurzbz, cell.getData().ausbildungssemester)
							);
							container.append(button);

							button = document.createElement('button');
							if (this.dataMeldestichtag && this.dataMeldestichtag > cell.getData().datum)
								button.className = 'btn btn-outline-secondary btn-action disabled';
							else
								button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-xmark"></i>';
							button.title = 'Status löschen';
							button.addEventListener('click', () =>
								this.actionDeleteStatus(cell.getData().status_kurzbz, cell.getData().studiensemester_kurzbz, cell.getData().ausbildungssemester)
							);
							container.append(button);

							return container;
						},
						frozen: true
					},
				],
				rowFormatter: (row) => {
					const rowData = row.getData();
					if (this.dataMeldestichtag && this.dataMeldestichtag > rowData.datum)
					{
						row.getElement().classList.add('disabled');

					}
				},
				layout: 'fitDataFill',
				layoutColumnsOnNewData: false,
				height: 'auto',
				selectable: false,
			},
			tabulatorEvents: [],
			statusData: {},
			listStudiensemester: [],
			maxSem:  Array.from({ length: 11 }, (_, index) => index),
			listStudienplaene: [],
			aufnahmestufen: {'': '-- keine Auswahl --', 1: 1, 2: 2, 3: 3},
			listStatusgruende: [],
			statusId: {},
			gruendeLength: {},
			dataMeldestichtag: null,
			stichtag: {}
		}
	},
	computed: {
		gruende() {
			return this.listStatusgruende.filter(grund => grund.status_kurzbz == this.statusData.status_kurzbz);
		},
	},
	watch: {
		data: {
			handler(n) {
				const start = this.status_kurzbz;
			},
			deep: true
		}
	},
	methods: {
		actionNewStatus() {
			console.log("Action: Neuen Status hinzufügen");
			this.statusData.status_kurzbz = 'Interessent';
			this.statusData.studiensemester_kurzbz = this.defaultSemester;
			this.statusData.ausbildungssemester = 1;
			this.statusData.datum = this.getDefaultDate();
			this.statusData.bestaetigtam = this.getDefaultDate();
			this.$refs.newStatusModal.show();
		},
		actionEditStatus(status, stdsem, ausbildungssemester){
			console.log("Action: Status bearbeiten: (" + status + ": " + stdsem + "/" + ausbildungssemester + ")")
			this.statusId = {
				'prestudent_id': this.prestudent_id,
				'status_kurzbz': status,
				'studiensemester_kurzbz': stdsem,
				'ausbildungssemester': ausbildungssemester
			};
			this.loadStatus(this.statusId).then(() => {
				if(this.statusData)
					this.$refs.editStatusModal.show();
			});
		},
		actionDeleteStatus(status, stdsem, ausbildungssemester){
			console.log("Action: Status löschen: (" + status + ": " + stdsem + "/" + ausbildungssemester + ")")
			//this.$refs.deleteStatusModal.show();
			this.statusId = {
				'prestudent_id': this.prestudent_id,
				'status_kurzbz': status,
				'studiensemester_kurzbz': stdsem,
				'ausbildungssemester': ausbildungssemester
			};

			this.loadStatus(this.statusId).then(() => {
				if(this.statusData)
					this.$refs.deleteStatusModal.show();
			});
		},
		actionAdvanceStatus(status, stdsem, ausbildungssemester){
			console.log("Action: Status vorrücken: (" + status + ": " + stdsem + "/" + ausbildungssemester + ")")
			//this.$refs.deleteStatusModal.show();
			this.statusId = {
				'prestudent_id': this.prestudent_id,
				'status_kurzbz': status,
				'studiensemester_kurzbz': stdsem,
				'ausbildungssemester': ausbildungssemester
			};
			this.loadStatus(this.statusId).then(() => {
				if(this.statusData)
					this.advanceStatus(this.statusId);
			});
		},
		actionConfirmStatus(status, stdsem, ausbildungssemester){
			console.log("Action: Status bestätigen: (" + status + ": " + stdsem + "/" + ausbildungssemester + ")")
			//this.$refs.deleteStatusModal.show();
			this.statusId = {
				'prestudent_id': this.prestudent_id,
				'status_kurzbz': status,
				'studiensemester_kurzbz': stdsem,
				'ausbildungssemester': ausbildungssemester
			};
			this.loadStatus(this.statusId).then(() => {
				if(this.statusData)
					this.confirmStatus(this.statusId);
			});
		},
		addNewStatus(){
			CoreRESTClient.post('components/stv/Status/addNewStatus/' + this.prestudent_id,
				this.statusData
			).then(response => {
				if (!response.data.error) {
					this.$fhcAlert.alertSuccess('Speichern erfolgreich');
					this.hideModal('newStatusModal');
					this.resetModal();
				} else {
					const errorData = response.data.retval;
					Object.entries(errorData).forEach(entry => {
						const [key, value] = entry;
						this.$fhcAlert.alertError(value);
					});
				}
			}).catch(error => {
				if (error.response) {
					console.log(error.response);
					this.$fhcAlert.alertError(error.response.data);
				}
			}).finally(() => {
				window.scrollTo(0, 0);
				this.reload();
			});
		},
		advanceStatus(statusId){
			return CoreRESTClient.post('components/stv/Status/advanceStatus/' +
				this.statusId.prestudent_id + '/' +
				this.statusId.status_kurzbz + '/' +
				this.statusId.studiensemester_kurzbz + '/' +
				this.statusId.ausbildungssemester)
				.then(
					result => {
						if(!result.data.error) {
							this.$fhcAlert.alertSuccess('Vorrückung Status erfolgreich');
						}
						else
						{
							const errorData = result.data.retval;
							this.$fhcAlert.alertError('Kein Status mit Id ' + status_id + ' gefunden');
						}
						/*						return result;*/
					}).catch(error => {
					if (error.response) {
						console.log(error.response);
						this.$fhcAlert.alertError(error.response.data);
					}
				}).finally(() => {
					window.scrollTo(0, 0);
					this.reload();
				});
		},
		confirmStatus(statusId){
			return CoreRESTClient.post('components/stv/Status/confirmStatus/' +
				this.statusId.prestudent_id + '/' +
				this.statusId.status_kurzbz + '/' +
				this.statusId.studiensemester_kurzbz + '/' +
				this.statusId.ausbildungssemester)
				.then(
					result => {
						if(!result.data.error) {
							this.$fhcAlert.alertSuccess('Bestätigung Status erfolgreich');
						}
						else
						{
							const errorData = result.data.retval;
							this.$fhcAlert.alertError('Kein Status mit Id ' + status_id + ' gefunden');
						}
						/*						return result;*/
					}).catch(error => {
					if (error.response) {
						console.log(error.response);
						this.$fhcAlert.alertError(error.response.data);
					}
				}).finally(() => {
					window.scrollTo(0, 0);
					this.reload();
				});
		},
		deleteStatus(status_id){
			return CoreRESTClient.post('components/stv/Status/deleteStatus/',
				status_id)
				.then(
					result => {
						if(!result.data.error) {
							this.$fhcAlert.alertSuccess('Löschen erfolgreich');
							this.hideModal('deleteStatusModal');
							this.resetModal();
						}
						else
						{
							const errorData = result.data.retval;
							this.$fhcAlert.alertError('Kein Status mit Id ' + status_id + ' gefunden');
						}
						/*						return result;*/
					}).catch(error => {
					if (error.response) {
						//console.log(error.response);
						this.$fhcAlert.alertError(error.response.data);
					}
				}).finally(() => {
					window.scrollTo(0, 0);
					this.reload();
				});
		},
		editStatus(){
			return CoreRESTClient.post('components/stv/Status/updateStatus/' +
				this.statusId.prestudent_id + '/' +
				this.statusId.status_kurzbz + '/' +
				this.statusId.studiensemester_kurzbz + '/' +
				this.statusId.ausbildungssemester,
				this.statusData)
				.then(
					result => {
						if(!result.data.error) {
							this.$fhcAlert.alertSuccess('Bearbeitung Status erfolgreich');
							this.hideModal('editStatusModal');
							this.resetModal();
						}
						else
						{
							const errorData = result.data.retval;
							this.$fhcAlert.alertError('Kein Status mit Id ' + status_id + ' gefunden');
						}
						/*						return result;*/
					}).catch(error => {
					if (error.response) {
						console.log(error.response);
						this.$fhcAlert.alertError(error.response.data);
					}
				}).finally(() => {
					window.scrollTo(0, 0);
					this.reload();
				});
		},
		loadStatus(status_id){
			return CoreRESTClient.post('components/stv/Status/loadStatus/',
				status_id)
				.then(
					result => {
						if(result.data.retval)
							this.statusData = result.data.retval;
						else
						{
							this.statusData = {};
							this.$fhcAlert.alertError('Kein Status mit Id ' + status_id + ' gefunden');
						}
						return result;
					}
				);
		},
		reload(){
			this.$refs.table.reloadTable();
		},
		hideModal(modalRef){
			this.$refs[modalRef].hide();
		},
		resetModal(){
			this.statusData = {};
			this.statusId = {};
		},
		getDefaultDate() {
			const today = new Date();
			return today;
		}
	},
	created(){
		CoreRESTClient
			.get('components/stv/Prestudent/getStudiensemester')
			.then(result => CoreRESTClient.getData(result.data) || [])
			.then(result => {
				this.listStudiensemester = result;
			})
			.catch(this.$fhcAlert.handleSystemError);
		CoreRESTClient
			.get('components/stv/Prestudent/getStudienplaene/' + this.prestudent_id)
			.then(result => CoreRESTClient.getData(result.data) || [])
			.then(result => {
				this.listStudienplaene = result;
			})
			.catch(this.$fhcAlert.handleSystemError);
		CoreRESTClient
			.get('components/stv/Status/getStatusgruende/')
			.then(result => CoreRESTClient.getData(result.data) || [])
			.then(result => {
				this.listStatusgruende = result;
			})
			.catch(this.$fhcAlert.handleSystemError);
		CoreRESTClient
			.get('components/stv/Status/getLastBismeldestichtag/')
			.then(result => CoreRESTClient.getData(result.data) || [])
			.then(result => {
				this.dataMeldestichtag = result[0].meldestichtag;
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: `
		<div class="stv-list h-100 pt-3">
		
		
		<p>TestData</p>

		Bismeldestichtag
		{{dataMeldestichtag }}
		
		

		
<!--		Berechtigungen:
			Skip Check: {{hasPermissionToSkipStatusCheck}} |
			Admin: {{hasAdminPermission}} |
			Studiengaenge: {{hasAssistenzPermissionForStgs}} |
			Schreibrecht ASS: {{hasSchreibrechtAss}}-->
		
		<hr>
		<p>{{statusId}}</p>	
		
			<!--Modal: Add New Status-->
			<BsModal ref="newStatusModal">
				<template #title>Neuen Status hinzufügen</template>
							
					<form-form class="row g-3" ref="statusData">
					
						<div class="row mb-3">
							<label for="status_kurzbz" class="form-label col-sm-4">Rolle</label>
							<div class="col-sm-6">
<!--								<form-input type="text" :readonly="readonly" class="form-control" id="status_kurzbz" v-model="statusData['status_kurzbz']">-->
								<form-input
									required 
									v-model="statusData['status_kurzbz']"
									name="status_kurzbz"
									type="select"
									>
									<option  value="Interessent">InteressentIn</option>
									<option  value="Bewerber">BewerberIn</option>
									<option  value="Aufgenommener">Aufgenommene/r</option>
									<option  value="Student">StudentIn</option>
									<option  value="Unterbrecher">UnterbrecherIn</option>
									<option  value="Diplomand">DiplomandIn</option>
									<option  value="Incoming">Incoming</option>
									<option  value="Absolvent">AbsolventIn</option>
									<option  value="Abbrecher">AbbrecherIn</option>
								</form-input>
							</div>
						</div>
						<div class="row mb-3">									   
							<label for="studiensemester_kurzbz" class="form-label col-sm-4">Studiensemester</label>
							<div class="col-sm-6">
								<form-input 
									:readonly="readonly" 
									type="select"
									name="studiensemester_kurzbz" 
									v-model="statusData['studiensemester_kurzbz']"
								>
									<option v-for="sem in listStudiensemester" :key="sem.studiensemester_kurzbz" :value="sem.studiensemester_kurzbz"  :selected="sem.studiensemester_kurzbz === defaultSemester">{{sem.studiensemester_kurzbz}}</option>
								</form-input>
							</div>
						</div>
						<!-- TODO(manu) if(defined('VORRUECKUNG_STATUS_MAX_SEMESTER') && VORRUECKUNG_STATUS_MAX_SEMESTER==false) 100 Semester-->
						<div class="row mb-3">
							<label for="ausbildungssemester" class="form-label col-sm-4">Ausbildungssemester</label>
							<div class="col-sm-6">
								<form-input 
									type="select" 
									:readonly="readonly" 
									name="ausbildungssemester" 
									v-model="statusData.ausbildungssemester"
								>
								 <option v-for="number in maxSem" :key="number" :value="number">{{ number }}</option>
								</form-input>
							</div>
						</div>
						
						<div class="row mb-3">
							<label for="datum" class="form-label col-sm-4">Datum</label>
							<div class="col-sm-6">
								<form-input 
									type="DatePicker" 
									:readonly="readonly" 
									name="datum" 
									v-model="statusData.datum"
									auto-apply
									:enable-time-picker="false"
									format="dd.MM.yyyy"
									preview-format="dd.MM.yyyy"
									:teleport="true"
								></form-input>
							</div>
						</div>
						<div class="row mb-3">									   
							<label for="bestaetigtam" class="form-label col-sm-4">Bestätigt am</label>
							<div class="col-sm-6">
								<form-input 
									type="DatePicker" 
									:readonly="readonly" 
									name="datum" 
									v-model="statusData.bestaetigtam"
									auto-apply
									:enable-time-picker="false"
									format="dd.MM.yyyy"
									preview-format="dd.MM.yyyy"
									:teleport="true"
								></form-input>
							</div>
						</div>
						<div class="row mb-3">
							<label for="bewerbung_abgeschicktamum" class="form-label col-sm-4">Bewerbung abgeschickt am</label>
							<div class="col-sm-6">
								<form-input 
									type="DatePicker" 
									:readonly="readonly" 
									name="datum" 
									v-model="statusData['bewerbung_abgeschicktamum']"
									auto-apply
									:enable-time-picker="false"
									format="dd.MM.yyyy"
									preview-format="dd.MM.yyyy"
									:teleport="true"
								></form-input>
							</div>
						</div>
						<div class="row mb-3">
							<label for="bezeichnung" class="form-label col-sm-4">Studienplan</label>
							<div class="col-sm-6">
								<form-input 
									:readonly="readonly" 
									type="select"
									name="studienplan" 
									v-model="statusData['studienplan_id']"
								>									
									<option v-for="sp in listStudienplaene" :key="sp.studienplan_id" :value="sp.studienplan_id">{{sp.bezeichnung}}</option>
								</form-input>
							</div>
						</div>
						<div class="row mb-3">
							<label for="anmerkung" class="form-label col-sm-4">Anmerkung</label>
							<div class="col-sm-6">
								<form-input 
									type="text"
									name="anmerkung" 
									v-model="statusData['anmerkung']"
								>									
								</form-input>
							</div>
						</div>
						
						<div class="row mb-3">									   
							<label for="aufnahmestufe" class="form-label col-sm-4">Aufnahmestufe</label>
							<div class="col-sm-6">
								<form-input 
								type="select" 
								:readonly="readonly" 
								name="aufnahmestufe" 
								v-model="statusData['rt_stufe']"
								>
								<option v-for="entry in aufnahmestufen" :key="entry" :value="entry">{{entry}}</option>
								</form-input>
							</div>
						</div>
										
						<div v-if="gruende.length > 0" class="row mb-3">
							<label for="grund" class="form-label col-sm-4">Grund</label>
							<div class="col-sm-6">
								<form-input 
									type="select" 
									:readonly="readonly" 
									name="statusgrund" 
									v-model="statusData['statusgrund_id']"
								>
								<option v-for="grund in gruende" :key="grund.statusgrund_id" :value="grund.statusgrund_id">{{grund.beschreibung[0]}}</option>
								</form-input>
							</div>
						</div>
					
					</form-form>
					
				<template #footer>
						<button type="button" class="btn btn-primary" @click="addNewStatus()">OK</button>
				</template>
								
			</BsModal>
			
			<!--Modal: Edit Status-->
			<BsModal ref="editStatusModal">
				<template #title>Status bearbeiten</template>
					<form-form class="row g-3" ref="statusData">
					
					<div v-if="statusData.datum < dataMeldestichtag ">
						<b>Meldestichtag erreicht - Bearbeiten nicht mehr möglich</b>
					</div>
					
					 <input type="hidden" id="statusId" name="statusId" value="statusData.statusId">
					
						<div class="row mb-3">
							<label for="status_kurzbz" class="form-label col-sm-4">Rolle</label>
							<div class="col-sm-6">
<!--								<form-input type="text" :readonly="readonly" class="form-control" id="status_kurzbz" v-model="statusData['status_kurzbz']">-->
								<form-input
									required 
									v-model="statusData['status_kurzbz']"
									name="status_kurzbz"
									type="select"
									disabled
									>
									<option  value="Interessent">InteressentIn</option>
									<option  value="Bewerber">BewerberIn</option>
									<option  value="Aufgenommener">Aufgenommene/r</option>
									<option  value="Student">StudentIn</option>
									<option  value="Unterbrecher">UnterbrecherIn</option>
									<option  value="Diplomand">DiplomandIn</option>
									<option  value="Incoming">Incoming</option>
									<option  value="Absolvent">AbsolventIn</option>
									<option  value="Abbrecher">AbbrecherIn</option>
								</form-input>
							</div>
						</div>
						<div class="row mb-3">									   
							<label for="studiensemester_kurzbz" class="form-label col-sm-4">Studiensemester</label>
							<div class="col-sm-6">
								<form-input 
									:readonly="readonly" 
									type="select"
									name="studiensemester_kurzbz" 
									v-model="statusData['studiensemester_kurzbz']"
								>
									<option v-for="sem in listStudiensemester" :key="sem.studiensemester_kurzbz" :value="sem.studiensemester_kurzbz">{{sem.studiensemester_kurzbz}}</option>
								</form-input>
							</div>
						</div>
						<!-- TODO(manu) if(defined('VORRUECKUNG_STATUS_MAX_SEMESTER') && VORRUECKUNG_STATUS_MAX_SEMESTER==false)-->
						<div class="row mb-3">
							<label for="ausbildungssemester" class="form-label col-sm-4">Ausbildungssemester</label>
							<div class="col-sm-6">
								<form-input 
									type="select" 
									:readonly="readonly" 
									name="ausbildungssemester" 
									v-model="statusData['ausbildungssemester']"
								>
								 <option v-for="number in maxSem" :key="number" :value="number">{{ number }}</option>
								</form-input>
							</div>
						</div>
						
						<div class="row mb-3">
							<label for="datum" class="form-label col-sm-4">Datum</label>
							<div class="col-sm-6">
								<form-input 
									type="DatePicker" 
									:readonly="readonly" 
									name="datum" 
									v-model="statusData['datum']"
									auto-apply
									:enable-time-picker="false"
									format="dd.MM.yyyy"
									preview-format="dd.MM.yyyy"
									:teleport="true"
								></form-input>
							</div>
						</div>
						<div class="row mb-3">									   
							<label for="bestaetigtam" class="form-label col-sm-4">Bestätigt am</label>
							<div class="col-sm-6">
								<form-input 
									type="DatePicker" 
									:readonly="readonly" 
									name="datum" 
									v-model="statusData['bestaetigtam']"
									auto-apply
									:enable-time-picker="false"
									format="dd.MM.yyyy"
									preview-format="dd.MM.yyyy"
									:teleport="true"
								></form-input>
							</div>
						</div>
						<div class="row mb-3">
							<label for="bewerbung_abgeschicktamum" class="form-label col-sm-4">B. abgeschickt am</label>
							<div class="col-sm-6">
								<form-input 
									type="DatePicker" 
									:readonly="readonly" 
									name="datum" 
									v-model="statusData['bewerbung_abgeschicktamum']"
									auto-apply
									:enable-time-picker="false"
									format="dd.MM.yyyy"
									preview-format="dd.MM.yyyy"
									:teleport="true"
								></form-input>
							</div>
						</div>
						<div class="row mb-3">
							<label for="bezeichnung" class="form-label col-sm-4">Studienplan</label>
							<div class="col-sm-6">
								<form-input 
									:readonly="readonly" 
									type="select"
									name="studienplan" 
									v-model="statusData['studienplan_id']"
								>									
									<option v-for="sp in listStudienplaene" :key="sp.studienplan_id" :value="sp.studienplan_id">{{sp.bezeichnung}}</option>
								</form-input>
							</div>
						</div>
						<div class="row mb-3">
							<label for="anmerkung" class="form-label col-sm-4">Anmerkung</label>
							<div class="col-sm-6">
								<form-input 
									type="text"
									name="anmerkung" 
									v-model="statusData['anmerkung']"
								>									
								</form-input>
							</div>
						</div>
						
						<div class="row mb-3">									   
							<label for="aufnahmestufe" class="form-label col-sm-4">Aufnahmestufe</label>
							<div class="col-sm-6">
								<form-input 
								type="select" 
								:readonly="readonly" 
								name="aufnahmestufe" 
								v-model="statusData['rt_stufe']"
								>
								<option v-for="entry in aufnahmestufen" :key="entry" :value="entry">{{entry}}</option>
								</form-input>
							</div>
						</div>
						
						<div v-if="gruende.length > 0" class="row mb-3">
							<label for="grund" class="form-label col-sm-4">Grund</label>
							<div class="col-sm-6">
								<form-input 
									type="select" 
									:readonly="readonly" 
									name="statusgrund" 
									v-model="statusData['statusgrund_id']"
								>
								<option v-for="grund in gruende" :key="grund.statusgrund_id" :value="grund.statusgrund_id">{{grund.beschreibung[0]}}</option>
								</form-input>
							</div>
						</div>
					
					</form-form>
					
				<template #footer>
						<button type="button" class="btn btn-primary" @click="editStatus()">OK</button>
				</template>
								
			</BsModal>
		
			<!--Modal: Delete Status-->
			<BsModal ref="deleteStatusModal">
				<template #title>Status löschen</template> 
				<template #default>
					<p>Prestudentstatus {{statusData.status_kurzbz}} (id: {{statusData.prestudent_id}}  
					/ {{statusData.studiensemester_kurzbz}}) im {{statusData.ausbildungssemester}}. Ausbildungssemester wirklich löschen?</p>					
				</template>	
				<template #footer>
					<button ref="Close" type="button" class="btn btn-primary" @click="deleteStatus(statusId)">OK</button>
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
				new-btn-label="Status"
				@click:new="actionNewStatus"
			>
		</core-filter-cmpt>
		
		</div>`
};