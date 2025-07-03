import {CoreFilterCmpt} from "../filter/Filter.js";

import BsModal from "../Bootstrap/Modal.js";
import CoreForm from '../Form/Form.js';
import FormInput from '../Form/Input.js';
import ListUnassigned from './List/Unassigned.js';
import ContractDetails from './List/Details.js';
import ContractStati from './List/Status.js';


export default {
	name:'CoreVertraege',
	components: {
		CoreFilterCmpt,
		BsModal,
		CoreForm,
		FormInput,
		ListUnassigned,
		ContractDetails,
		ContractStati
	},
	inject: {
/*		cisRoot: {
			from: 'cisRoot'
		},*/
		hasSchreibrechte: {
			from: 'hasSchreibrechte',
			default: false
		},
	},
	props: {
		endpoint: {
			type: Object,
			required: true
		},
		person_id: {
			type: [Number],
			required: true
		},
	},
	computed: {
		appRoot() {
			return FHC_JS_DATA_STORAGE_OBJECT.app_root;
		}
	},
	data() {
		return {
			tabulatorOptions: {
				ajaxURL: 'dummy',
				ajaxRequestFunc: () => this.$api.call(
					this.endpoint.getAllVertraege(this.person_id)
				),
				ajaxResponse: (url, params, response) => response.data,
				columns: [
					{title: "Bezeichnung", field: "bezeichnung", width: 150},
					{
						title: "Betrag", field: "betrag", width: 150,
						formatter: function (cell) {
							let value = cell.getValue();

							if (value == null) {
								return "0.00";
							}
							return parseFloat(value).toFixed(2);
						}
					},
					{title: "Vertragstyp", field: "vertragstyp_bezeichnung", width: 125},
					{title: "Status", field: "status"},
					{
						title: "Vertragsdatum",
						field: "vertragsdatum",
						width: 128,
						formatter: function (cell) {
							const dateStr = cell.getValue();
							const date = new Date(dateStr);
							return date.toLocaleString("de-DE", {
								day: "2-digit",
								month: "2-digit",
								year: "numeric",
							});
						}
					},
					{title: "VertragId", field: "vertrag_id", visible: false},
					{title: "Vertragsstunden", field: "vertragsstunden", visible: false},
					{title: "VertragsstundenStudiensemester", field: "vertragsstunden_studiensemester_kurzbz", visible: false},
					{title: "Anmerkung", field: "anmerkung", visible: false},
					{title: "isAbgerechnet", field: "isabgerechnet", visible: false},
					{
						title: 'Aktionen', field: 'actions',
						minWidth: 150,
						maxWidth: 150,
						formatter: (cell, formatterParams, onRendered) => {
							let container = document.createElement('div');
							container.className = "d-flex gap-2";

							let button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-edit"></i>';
							button.title = this.$p.t('vertrag', 'editVertrag');
							button.addEventListener(
								'click',
								(event) =>
									this.actionEditContract(cell.getData().vertrag_id)
							);
							container.append(button);

							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-xmark"></i>';
							button.title = this.$p.t('vertrag', 'deleteVertrag');

							if (!this.hasSchreibrechte) {
								button.disabled = true;
								button.classList.add('disabled');
							} else {
								button.addEventListener(
									'click',
									() =>
										this.actionDeleteContract(cell.getData().vertrag_id)
								);
							}
							container.append(button);

							return container;
						},
						frozen: true
					}],
				layout: 'fitColumns',
				layoutColumnsOnNewData: false,
				height: '250',
				selectableRangeMode: 'click',
				selectable: true,
				persistenceID: 'core-contracts'
			},
			tabulatorEvents: [
				{
					event: 'tableBuilt',
					handler: async() => {

						await this.$p.loadCategory(['ui', 'global', 'vertrag']);

						let cm = this.$refs.table.tabulator.columnManager;

						cm.getColumnByField('bezeichnung').component.updateDefinition({
							title: this.$p.t('ui', 'bezeichnung')
						});
						cm.getColumnByField('betrag').component.updateDefinition({
							title: this.$p.t('ui', 'betrag')
						});
						cm.getColumnByField('status').component.updateDefinition({
							title: this.$p.t('global', 'status')
						});
						cm.getColumnByField('vertragstyp_bezeichnung').component.updateDefinition({
							title: this.$p.t('vertrag', 'vertragstyp')
						});
						cm.getColumnByField('vertragsdatum').component.updateDefinition({
							title: this.$p.t('vertrag', 'vertragsdatum')
						});
						cm.getColumnByField('vertragsstunden').component.updateDefinition({
							title: this.$p.t('vertrag', 'vertragsstunden')
						});
						cm.getColumnByField('vertragsstunden_studiensemester_kurzbz').component.updateDefinition({
							title: this.$p.t('vertrag', 'vertragsstunden_studiensemester')
						});
						cm.getColumnByField('vertrag_id').component.updateDefinition({
							title: this.$p.t('ui', 'vertrag_id')
						});
						cm.getColumnByField('anmerkung').component.updateDefinition({
							title: this.$p.t('global', 'anmerkung')
						});
						cm.getColumnByField('isabgerechnet').component.updateDefinition({
							title: this.$p.t('vertrag', 'abgerechnet')
						});
/*						cm.getColumnByField('actions').component.updateDefinition({
							title: this.$p.t('global', 'aktionen')
						});*/
					}
				},
				{
					//is just enabled for ADDON Injection KU: MultiprintHonorarvertrag
					//(maybe enable also for ADDON FH Burgenland: MultiAccept later)
					event: 'rowClick',
					handler: (e, row) => {
						if (this.dataPrintHonorar != null && this.dataPrintHonorar.multiselect != null) {
							const selectedContract = row.getData().vertrag_id;
							const status = row.getData().status;
							const bezeichnung =	row.getData().bezeichnung;

							this.toggleRowClick(selectedContract, status, bezeichnung);
						}
					}
				},
			],
			statusNew: true,
			formData: {	},
			listContractsUnassigned: [],
			listContractTypes: [],
			contractSelected: [],
			listContractStati: [],
			contractFormData: {
				vertragsstatus_kurzbz: '',
				datum: new Date(),
			},
			dataPrintHonorar: [],
			triggeredData: [],
			childData: {},
			isFilterSet: false,
			ma_uid: null,
			clickedRows: [],
			arraySelectedContracts: [],
		}
	},
	watch: {
		person_id() {
			this.$refs.table.reloadTable();
			this.arraySelectedContracts = [];
			//this.$refs.table.tabulator.setData('api/frontend/v1/vertraege/vertraege/getAllVertraege/' + this.person_id);
		},
	},
	methods: {
		actionNewContract() {
			this.resetModal();
			this.$refs.unassignedLehrauftraege.reloadUnassigned();
			this.$refs.contractModal.show();
		},
		actionEditContract(vertrag_id) {
			this.resetModal();
			this.statusNew = false;
			//TODO(Manu) reload Assigned!!
			this.$refs.unassignedLehrauftraege.reloadUnassigned();
			this.loadContract(vertrag_id)
				.then(this.$refs.contractModal.show);
		},
		actionDeleteContract(vertrag_id) {
			this.$fhcAlert
				.confirmDelete()
				.then(result => result
					? vertrag_id
					: Promise.reject({handled: true}))
				.then(vertrag_id => this.$api.call(
					this.endpoint.deleteContract(vertrag_id))
				)
				.then(result => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));
					//window.scrollTo(0, 0);
					this.reload();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		addNewContract() {
			this.$refs.unassignedLehrauftraege.emitSaveEvent();

			const dataToSend = {
				person_id: this.person_id,
				formData: this.formData,
				clickedRows: this.childData, //all data needed, maybe smaller array?
			};
			return this.$refs.contractData
				.call(this.endpoint.addNewContract(dataToSend))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.$refs.contractModal.hide();
					this.resetModal();
					//this.$refs.contractdetails.reload();
					this.$refs.unassignedLehrauftraege.reloadUnassigned();
					this.reload();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		updateContract(vertrag_id) {
			this.$refs.unassignedLehrauftraege.emitSaveEvent();

			const dataToSend = {
				vertrag_id: vertrag_id,
				person_id: this.person_id,
				formData: this.formData,
				clickedRows: this.childData,
			};
			return this.$refs.contractData
				.call(this.endpoint.updateContract(dataToSend))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.$refs.contractModal.hide();
					this.resetModal();
					this.$refs.unassignedLehrauftraege.reloadUnassigned();
					this.$refs.contractdetails.reload();
					this.reload();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		loadContract(vertrag_id) {
			this.resetModal();
			this.statusNew = false;
			return this.$api
				.call(this.endpoint.loadContract(vertrag_id))
				.then(result => {
					this.formData = result.data;
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		//Methods Contract Stati
		addNewContractStatus({status, datum}) {
			const date = new Date();
			const formattedDate = datum != null ? datum.toLocaleDateString('en-CA',
				{
					year: 'numeric',
					month: '2-digit',
					day: '2-digit',
					hour: '2-digit',
					minute: '2-digit',
					hour12: false, // Use 24-hour format
				}
				) : null;
			let params = {
				vertrag_id : this.contractSelected.vertrag_id,
				vertragsstatus_kurzbz: status,
				datum: formattedDate
			};

			return this.$refs.contractstati.$refs.statusData
				.call(this.endpoint.insertContractStatus(params))
				.then(response => {
					//this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));

					this.$refs.contractstati.closeModal();
					this.$refs.contractstati.reload();
					this.reload();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		deleteContractStatus({status, vertrag_id}){
			let params = {
				vertrag_id : vertrag_id,
				vertragsstatus_kurzbz: status
			};
			return this.$api
				.call(this.endpoint.deleteContractStatus(params))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));

					this.$refs.contractstati.reload();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		updateContractStatus({datum, status}){
			const date = new Date();
			const formattedDate = datum != null ? datum.toLocaleDateString('en-CA',
				{
					year: 'numeric',
					month: '2-digit',
					day: '2-digit',
					hour: '2-digit',
					minute: '2-digit',
					hour12: false, // Use 24-hour format
				}
			) : null;
			let params = {
				vertrag_id : this.contractSelected.vertrag_id,
				datum : formattedDate,
				vertragsstatus_kurzbz: status
			};
			return this.$refs.contractstati.$refs.statusData
				.call(this.endpoint.updateContractStatus(params))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.$refs.contractstati.closeModal();
					this.$refs.contractstati.reload();
					this.reload();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		loadContractStatus({vertrag_id, status}){
			let params = {
				vertrag_id : vertrag_id,
				vertragsstatus_kurzbz: status
			};
			return this.$api
				.call(this.endpoint.loadContractStatus(params))
				.then(response => {
					this.contractFormData = response.data;
					this.$refs.contractstati.openModal();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		deleteLehrauftrag({lehreinheit_id, vertrag_id, mitarbeiter_uid}){

			let params = {
				vertrag_id : vertrag_id,
				lehreinheit_id: lehreinheit_id,
				mitarbeiter_uid: mitarbeiter_uid
			};
			return this.$api
				.call(this.endpoint.deleteLehrauftrag(params))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));
					this.resetModal();
					this.$refs.contractdetails.reload();
					this.$refs.unassignedLehrauftraege.reloadUnassigned();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		deleteBetreuung({person_id, vertrag_id, projektarbeit_id, betreuerart_kurzbz}){
			let params = {
				vertrag_id : vertrag_id,
				person_id: person_id,
				projektarbeit_id: projektarbeit_id,
				betreuerart_kurzbz: betreuerart_kurzbz
			};
			return this.$api
				.call(this.endpoint.deleteBetreuung(params))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));
					this.$refs.contractdetails.reload();
					this.$refs.unassignedLehrauftraege.reloadUnassigned();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},

		//Methods Unassigned List
		saveClickedRows(clickedRows) {
			this.childData = clickedRows;
		},
		reload() {
			this.$refs.table.reloadTable();
		},
		resetModal(){
			this.formData = {};
			this.formData.vertragsdatum  = new Date();
			this.formData.betrag = 0;
			this.formData.bezeichnung = this.getFormattedDate();
			this.formData.vertragstyp_kurzbz = null;
			this.statusNew = true;

			//this.childData = {};
			//always null??
			//TODO(Manu) check if this.childData = {},
		},
		updateBetrag(sumBetrag){
			this.formData.betrag = sumBetrag;
		},
		getFormattedDate() {
			const today = new Date();
			const year = today.getFullYear();
			const month = String(today.getMonth() + 1).padStart(2, "0");
			const day = String(today.getDate()).padStart(2, "0");

			return `${year}${month}${day}`; // Format: YYYYMMDD
		},
		onSwitchChange() {
			if (this.isFilterSet) {
				this.$refs.table.tabulator.setFilter("isabgerechnet", "!=", true);
			}
			else {
				this.$refs.table.tabulator.clearFilter("status");
			}
		},
		//methods for functionality ADDON KU
		printContract(){
			this.getMitarbeiterUid().then(()=> {
				//check if at least 2 contracts chosen
				if(this.arraySelectedContracts.length < 2) {
					this.$fhcAlert.alertError(this.$p.t('vertrag', 'alertMindestensZweiVertraege'));
					return;
				}

				//check if status=="Genehmigt"
				const statusNotGenehmigtExists = this.arraySelectedContracts.some(([_, status]) => status !== 'Genehmigt');
				if(statusNotGenehmigtExists) {
					this.$fhcAlert.alertError(this.$p.t('vertrag', 'alertOnlyApprovedContracts'));
					return;
				}

				//build String to Print PDF
				let vertragString = '';

				this.arraySelectedContracts.forEach(element => {
					vertragString += '&vertrag_id[]=' + element[0].toString();
				});

				let linkToPdf = this.dataPrintHonorar.link +
						'content/pdfExport.php?xml=' + this.dataPrintHonorar.xml + '&xsl=' + this.dataPrintHonorar.xsl + '&mitarbeiter_uid=' + this.ma_uid + vertragString + '&output=pdf&uid=' + this.ma_uid;
				window.open(linkToPdf, '_blank');
				});
		},
		getMitarbeiterUid(){
			return this.$api
				.call(this.endpoint.getMitarbeiterUid(this.person_id))
				.then(response => {
					this.ma_uid = response.data;
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		toggleRowClick(contractId, status, bezeichnung) {
			const index = this.arraySelectedContracts.findIndex(
				([id]) => id === contractId
			);
			if (index !== -1) {
				this.arraySelectedContracts.splice(index, 1);
			} else {
				this.arraySelectedContracts.push([contractId, status, bezeichnung]);
			}
		},
		clearSelection(){
			this.arraySelectedContracts = [];
		}
	},
	created() {
		Promise.all([
			this.$api.call(this.endpoint.getAllContractTypes()),
			this.$api.call(this.endpoint.getAllContractsNotAssigned(this.person_id)),
			this.$api.call(this.endpoint.getAllContractStati()),
			this.$api.call(this.endpoint.configPrintDocument())
		])
			.then(([result1, result2, result3, result4]) => {
				this.listContractTypes = result1.data;
				this.listContractsUnassigned = result2.data;
				this.listContractStati = result3.data;
				this.dataPrintHonorar = result4.data;
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	mounted() {
		//necessary for reloading components Status and Details
		this.$nextTick(() => {
			this.$refs.table.tabulator.on("rowClick", (e, row) => {
				this.contractSelected = row.getData();
			});
		});
		this.getFormattedDate();
	},
	template: `
	<div class="core-contracts h-100 d-flex flex-column">
	
		<!--	injected print functionality for KU Linz (printHonorarvertrag)  -->
	   <template v-if="arraySelectedContracts.length >= 2" class="container mt-2">
		 
		   <div v-for="item in arraySelectedContracts" :key="item[0]">
			  <input
				class="form-control"
				type="text"
				:value="item[2] + ' | ' + item[1] + ' (ID: ' + item[0] + ')'"
				aria-label="readonly input example"
				readonly
			  >
			</div>
		</template>
		
		<template v-if="arraySelectedContracts.length >= 2" class="d-flex">
			<div class="ms-auto mt-2">
				<button type="button" class="btn btn-secondary mx-1" @click="clearSelection()"><i class="fa fa-trash"></i></button>
				<button :disabled="!this.hasSchreibrechte" type="button" class="btn btn-primary" @click="printContract()">{{$p.t('vertrag', 'printHonorarvertrag')}}</button>
			</div>
		</template>

	<hr> 
		
<!--	filter: open means no status abgerechnet yet-->
		<div class="justify-content-end pb-3">
			<form-input
				container-class="form-switch"
				type="checkbox"
				:label="$p.t('vertrag/filter_offeneVertraege')"
				v-model="isFilterSet"
				@change="onSwitchChange"
				>
			</form-input>
		</div>
		
		<core-filter-cmpt
			ref="table"
			:tabulator-options="tabulatorOptions"
			:tabulator-events="tabulatorEvents"
			table-only
			:side-menu="false"
			reload
			new-btn-show
			:new-btn-label="this.$p.t('ui', 'vertrag')"
			@click:new="actionNewContract"
			>
		</core-filter-cmpt>
		
		<div class="row">
			<div class="col-sm-6">
				<!-- ContractDetails -->
				 <div class="md-4" v-if="contractSelected.vertrag_id !=null">
					<contract-details
						:person_id="person_id"
						:vertrag_id="contractSelected.vertrag_id"
						:endpoint="endpoint"
						@deleteLehrauftrag="deleteLehrauftrag"
						@deleteBetreuung="deleteBetreuung"
						ref="contractdetails"
					></contract-details>
				</div>
			</div>
			<div class="col-sm-6">
				<!-- ContractStati -->
				 <div class="md-4" v-if="contractSelected.vertrag_id !=null">      
					<contract-stati
						:person_id="person_id"
						:vertrag_id="contractSelected.vertrag_id"
						:listContractStati="listContractStati"
						:formDataParent="contractFormData"
						:endpoint="endpoint"
						@setContractStatus="addNewContractStatus"
						@deleteContractStatus="deleteContractStatus"
						@updateContractStatus="updateContractStatus"
						@loadContractStatus="loadContractStatus"
						ref="contractstati"
					></contract-stati>
				</div>
			</div>
		</div>
		
		<!--Modal: contractModal-->
			<bs-modal ref="contractModal" dialog-class="modal-xl">
				<template #title>
					<p v-if="statusNew" class="fw-bold mt-3">{{$p.t('vertrag', 'addVertrag')}}</p>
					<p v-else class="fw-bold mt-3">{{$p.t('vertrag', 'editVertrag')}}</p>
				</template>
				
					<list-unassigned
						:person_id="person_id"
						:endpoint="endpoint"
						ref="unassignedLehrauftraege"
						@saveClickedRows="saveClickedRows"
						@sum-updated="updateBetrag"
						></list-unassigned>
					<hr>
					<core-form ref="contractData">
						<div class="row mb-3">
							<form-input
								type="DatePicker"
								:label="$p.t('vertrag/datum_vertrag')"
								name="vertragsdatum"
								v-model="formData.vertragsdatum"
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
								type="text"
								:label="$p.t('ui/bezeichnung')"
								name="bezeichnung"
								v-model="formData.bezeichnung"
								>
							</form-input>
						</div>
						<div class="row mb-3">
							<form-input
								type="select"
								:label="$p.t('global/typ')"
								v-model="formData.vertragstyp_kurzbz"
								name="vertragstyp_kurzbz"
								>
								<option :value="null">-- {{$p.t('fehlermonitoring', 'keineAuswahl')}} --</option>
								<option
									v-for="entry in listContractTypes"
									:key="entry.vertragstyp_kurzbz"
									:value="entry.vertragstyp_kurzbz"
									>
									{{entry.bezeichnung}}
								</option>
							</form-input>
						</div>
						<div class="row mb-3">
							<form-input
								:label="$p.t('ui/betrag')"
								name="betrag"
								v-model="formData.betrag"
								>
							</form-input>
						</div>
						<div class="row mb-3" v-if="!statusNew">
							<form-input
								type="text"
								:label="$p.t('ui/stunden') + ' (' + $p.t('vertrag/vertrag_urfassung')+ ')'"
								name="vertragsstunden"
								v-model="formData.vertragsstunden"
								disabled
								>
							</form-input>
						</div>
						<div class="row mb-3" v-if="!statusNew">
							<form-input
								type="text"
								:label="$p.t('lehre/studiensemester') + ' (' + $p.t('vertrag/vertrag_urfassung')+ ')'"
								name="vertragsstunden_studiensemester_kurzbz"
								v-model="formData.vertragsstunden_studiensemester_kurzbz"
								disabled
								>
							</form-input>
						</div>
						<div class="row mb-3">
							<form-input
								type="textarea"
								:label="$p.t('global/anmerkung')"
								name="anmerkung"
								v-model="formData.anmerkung"
								>
							</form-input>
						</div>
				</core-form>
				
				<template #footer>
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="reload()">{{$p.t('ui', 'abbrechen')}}</button>
					<button type="button" class="btn btn-primary" :disabled="!this.hasSchreibrechte" @click="statusNew ? addNewContract() : updateContract(formData.vertrag_id)">{{$p.t('vertrag', 'vertragErstellen')}}</button>
				</template>
			</bs-modal>
		</core-form>
	</div>`
}

