import {CoreFilterCmpt} from "../filter/Filter.js";

import BsModal from "../Bootstrap/Modal.js";
import CoreForm from '../Form/Form.js';
import FormInput from '../Form/Input.js';
import ListUnassigned from './List/Unassigned.js';
import ContractDetails from './List/Details.js';
import ContractStati from './List/Status.js';

export default {
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
		cisRoot: {
			from: 'cisRoot'
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
				ajaxRequestFunc: this.$fhcApi.factory.vertraege.person.getAllVertraege,
				ajaxParams: () => {
					return {
						person_id: this.person_id
					};
				},
				ajaxResponse: (url, params, response) => response.data,
				columns: [
					{title: "Bezeichnung", field: "bezeichnung", width: 150},
					{title: "lehreinheit_id", field: "lehreinheit_id", visible: true},
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
					{title: "Vertragsdatum", field: "format_vertragsdatum", width: 128},
					{title: "VertragId", field: "vertrag_id", visible: false},
					{title: "Vertragsdatum_iso", field: "vertragsdatum", visible: false},
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
							button.addEventListener(
								'click',
								() =>
									this.actionDeleteContract(cell.getData().vertrag_id)
							);
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
						cm.getColumnByField('lehreinheit_id').component.updateDefinition({
							title: this.$p.t('ui', 'lehreinheit_id')
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
						cm.getColumnByField('format_vertragsdatum').component.updateDefinition({
							title: this.$p.t('vertrag', 'vertragsdatum')
						});
						cm.getColumnByField('vertragsdatum').component.updateDefinition({
							title: this.$p.t('vertrag', 'vertragsdatum_iso')
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
						cm.getColumnByField('actions').component.updateDefinition({
							title: this.$p.t('global', 'aktionen')
						});
					}
				}
			],
			statusNew: true,
			formData: {	},
			listContractsUnassigned: [],
			listContractTypes: [],
			contractSelected: [],
			listContractStati: [],
			contractFormData: {
				vertragsstatus_kurzbz: 'test',
				datum: new Date(),
			},
			childData: {},
			isFilterSet: false,
		}
	},
	watch: {
		person_id() {
			this.$refs.table.reloadTable();
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
				.then(this.endpoint.deleteContract)
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
				clickedRows: this.childData, //do I need all Data, maybe smaller array?
			};

			return this.endpoint
				.addNewContract(this.$refs.contractData, dataToSend)
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
				clickedRows: this.childData, //do I need all Data, maybe smaller array?
			};

			return this.endpoint
				.updateContract(this.$refs.contractData, dataToSend)
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
			return this.endpoint
				.loadContract(vertrag_id)
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

			return this.endpoint
				.insertContractStatus(this.$refs.contractstati.$refs.statusData, params)
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
			return this.endpoint
				.deleteContractStatus(params)
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
			return this.endpoint
				.updateContractStatus(this.$refs.contractstati.$refs.statusData, params)
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
			return this.endpoint
				.loadContractStatus(params)
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
			return this.endpoint
				.deleteLehrauftrag(params)
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
			return this.endpoint
				.deleteBetreuung(params)
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
			//gefährlich, immer null??
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
	},
	created() {
		Promise.all([
			this.endpoint.getAllContractTypes(),
			this.endpoint.getAllContractsNotAssigned2(this.person_id),
			this.endpoint.getAllContractStati(),
		])
			.then(([result1, result2, result3]) => {
				this.listContractTypes = result1.data;
				this.listContractsUnassigned = result2.data;
				this.listContractStati = result3.data;
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	mounted() {
		this.$nextTick(() => {
			this.$refs.table.tabulator.on("rowClick", (e, row) => {
				this.contractSelected = row.getData();
				console.log("selected Row ", this.contractSelected);
			});
		});
		this.getFormattedDate();

	},
	template: `
	<div class="core-contracts h-100 d-flex flex-column">
	
	
	

<!--	<div 
		class="d-flex justify-content-start align-items-center w-100 pb-3 gap-3" 
		style="max-height: 8rem; overflow: hidden;">
	<img class="d-block h-100 rounded" alt="profilbild" :src="appRoot + 'cis/public/bild.php?src=person&person_id=' + person_id">
&lt;!&ndash;		<img class="d-block h-100 rounded" alt="profilbild" :src="appRoot + 'cis/public/bild.php?src=person&person_id=' + student.person_id">
			<h2 class="h4">{{students[0].titlepre}} {{students[0].vorname}} {{students[0].nachname}} {{students[0].titlepost}}</h2>&ndash;&gt;
	</div>-->
	
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
						:vertrag_id="contractSelected.vertrag_id"
						:listContractStati="listContractStati"
						:formDataParent="contractFormData"
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
						:endpoint="$fhcApi.factory.vertraege.person"
						:person_id="person_id"
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
					<button type="button" class="btn btn-primary" @click="statusNew ? addNewContract() : updateContract(formData.vertrag_id)">{{$p.t('vertrag', 'vertragErstellen')}}</button>
				</template>
			</bs-modal>
		</core-form>
	</div>`
}

