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
	data() {
		return {
			//TODO(Manu) filter: alle vs offene Verträge
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
					{title: "Betrag", field: "betrag", width: 150},
					{title: "Vertragstyp", field: "vertragstyp_bezeichnung", width: 125},
					{title: "Status", field: "status"},
					{title: "Vertragsdatum", field: "format_vertragsdatum", width: 128},
					{title: "VertragstypKurzbz", field: "vertragstyp_kurzbz", visible: false},
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
							button.title = 'Vertrag bearbeiten';
							button.addEventListener(
								'click',
								(event) =>
									this.actionEditContract(cell.getData().vertrag_id)
							);
							container.append(button);

							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-xmark"></i>';
							button.title = 'Vertrag löschen';
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
/*					event: 'tableBuilt',
					handler: async() => {

						await this.$p.loadCategory(['wawi', 'global', 'infocenter']);

						let cm = this.$refs.table.tabulator.columnManager;

						cm.getColumnByField('nummer').component.updateDefinition({
							title: this.$p.t('wawi', 'nummer')
						});
						cm.getColumnByField('anmerkung').component.updateDefinition({
							title: this.$p.t('global', 'anmerkung')
						});
						cm.getColumnByField('format_retour').component.updateDefinition({
							title: this.$p.t('wawi', 'retourdatum')
						});
						cm.getColumnByField('kaution').component.updateDefinition({
							title: this.$p.t('infocenter', 'kaution')
						});
						cm.getColumnByField('format_ausgabe').component.updateDefinition({
							title: this.$p.t('wawi', 'ausgabedatum')
						});

					}*/
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
			isFilterSet: false
		}
	},
	watch: {
		person_id() {
			this.$refs.table.tabulator.setData('api/frontend/v1/vertraege/vertraege/getAllVertraege/' + this.person_id);
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
				.addNewContract(dataToSend)
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.$refs.contractModal.hide();
					this.resetModal();
					//this.$refs.contractdetails.reload(); //TOOD(Manu) check why error
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
				.updateContract(dataToSend)
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
			//TODO(Manu) refactor this
			const formattedDate = datum.toLocaleDateString('en-CA');
			let params = {
				vertrag_id : this.contractSelected.vertrag_id,
				status: {status},
				datum: formattedDate
			};

			return this.endpoint
				.insertContractStatus(params)
				.then(response => {
					//this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));

					this.$refs.contractstati.closeModal();
					//window.scrollTo(0, 0);
					this.$refs.contractstati.reload();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		deleteContractStatus({status, vertrag_id}){
			let params = {
				vertrag_id : {vertrag_id},
				status: {status}
			};
			return this.endpoint
				.deleteContractStatus(params)
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));

					//window.scrollTo(0, 0);
					this.$refs.contractstati.reload();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		deleteLehrauftrag({lehreinheit_id, vertrag_id, mitarbeiter_uid}){

			let params = {
				vertrag_id : {vertrag_id},
				lehreinheit_id: {lehreinheit_id},
				mitarbeiter_uid: {mitarbeiter_uid},
			};
			return this.endpoint
				.deleteLehrauftrag(params)
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));

					//window.scrollTo(0, 0);
					this.resetModal();
					this.$refs.contractdetails.reload();
					this.$refs.unassignedLehrauftraege.reloadUnassigned();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		deleteBetreuung({person_id, vertrag_id, projektarbeit_id, betreuerart_kurzbz}){
			let params = {
				vertrag_id : {vertrag_id},
				person_id: {person_id},
				projektarbeit_id: {projektarbeit_id},
				betreuerart_kurzbz: {betreuerart_kurzbz},
			};
			return this.endpoint
				.deleteBetreuung(params)
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));

					//window.scrollTo(0, 0);
					this.$refs.contractdetails.reload();
					this.$refs.unassignedLehrauftraege.reloadUnassigned();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		updateContractStatus({datum, status}){
			//TODO(Manu) refactor this
			const formattedDate = datum.toLocaleDateString('en-CA');
			let params = {
				vertrag_id : this.contractSelected.vertrag_id,
				datum : formattedDate,
				status: {status}
			};
			return this.endpoint
				.updateContractStatus(params)
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));

					//window.scrollTo(0, 0);
					this.$refs.contractstati.closeModal();
					this.$refs.contractstati.reload();
					this.reload();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		loadContractStatus({vertrag_id, status}){
			let params = {
				vertrag_id : {vertrag_id},
				status: {status}
			};
			return this.endpoint
				.loadContractStatus(params)
				.then(response => {
					this.contractFormData = response.data;
					this.$refs.contractstati.openModal();
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
			});
		});
		this.getFormattedDate();

	},
	template: `

	<div class="core-vertraege h-100 d-flex flex-column">
	
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
			new-btn-label="Vertrag"
			@click:new="actionNewContract"
			>
		</core-filter-cmpt>		
		
		<div class = "row">
		
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
			
			<core-form class="row g-3" ref="unassignedData">
		
<!--		{{formData}}-->
	
		<!--TODO(Manu) wenn einer gelöscht wird, wird er auch nicht sofort angezeigt
		Auswirkung von Vertragdetails of UnassignedList-->
				<list-unassigned 
					:endpoint="$fhcApi.factory.vertraege.person" 
					:person_id="person_id"
					ref="unassignedLehrauftraege"
					@saveClickedRows="saveClickedRows"
					@sum-updated="updateBetrag"
					></list-unassigned>

				<hr>
				
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
				<button type="button" class="btn btn-primary" @click="statusNew ? addNewContract() : updateContract(formData.vertrag_id)">{{$p.t('ui', 'speichern')}}</button>
			</template>
			
		</bs-modal>
	</div>`
}

