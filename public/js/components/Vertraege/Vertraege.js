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
			//TODO(Manu) check if filter bei Status: Abgerechnet wird zum Beispiel nicht angezeigt
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
					{
						title: 'Aktionen', field: 'actions',
						minWidth: 150, // Ensures Action-buttons will be always fully displayed
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
			formData: {},
			listContractsUnassigned: [],
			listContractTypes: [],
			contractSelected: []
		}
	},

	methods: {
		actionNewContract() {
			console.log("actionNewContract");
			this.resetModal();
			//this.loadContractsNotAssigned(this.person_id);
			this.$refs.contractModal.show();
		},
		actionEditContract(vertrag_id) {
			console.log("actionEditContract " + vertrag_id);
			this.statusNew = false;
			this
				.loadContract(vertrag_id)
				.then(this.$refs.contractModal.show);
		},
		actionDeleteContract(vertrag_id) {
			console.log("actionDeleteContract" + vertrag_id);
			this.$fhcAlert
				.confirmDelete()
				.then(result => result
					? vertrag_id
					: Promise.reject({handled: true}))
				.then(this.endpoint.deleteContract)
				.then(result => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));
					window.scrollTo(0, 0);
					this.reload();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		addNewContract() {
			return this.endpoint
				.addNewContract(this.person_id, this.formData)
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.$refs.contractModal.hide();
					this.resetModal();
					window.scrollTo(0, 0);
					this.reload();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		updateContract(vertrag_id) {
			return this.endpoint
				.updateContract(vertrag_id, this.formData)
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.$refs.contractModal.hide();
					this.resetModal();
					window.scrollTo(0, 0);
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
		reload() {
			this.$refs.table.reloadTable();
		},
		resetModal(){
			this.formData = {};
			this.formData.vertragsdatum  = new Date();
			this.formData.vertragstyp_kurzbz = null;
			this.statusNew = true;
		}
	},
	created() {
		Promise.all([
			this.endpoint.getAllContractTypes(),
			this.endpoint.getAllContractsNotAssigned2(this.person_id)
		])
			.then(([result1, result2]) => {
				this.listContractTypes = result1.data;
				this.listContractsUnassigned = result2.data;
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	mounted() {
		this.$nextTick(() => {
			this.$refs.table.tabulator.on("rowClick", (e, row) => {
				this.contractSelected = row.getData();
				console.log("vertrag_id: ", this.contractSelected.vertrag_id);
			});
		});
	},
	template: `

	<div class="core-vertraege h-100 d-flex flex-column">
	
	{{formData}}
	
	{{contractSelected}}
	
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
		Vertrag: {{contractSelected.vertrag_id}}
		
		<div class = "row">
		
			<div class="col-sm-6">
				<!-- ContractDetails -->
				 <div class="md-4" v-if="contractSelected.vertrag_id !=null">      
					<ContractDetails :person_id="person_id" :vertrag_id="contractSelected.vertrag_id"></ContractDetails>
				</div>
			</div>
			
			<div class="col-sm-6">
				<!-- ContractStati -->
				 <div class="md-4" v-if="contractSelected.vertrag_id !=null">      
					<ContractStati :vertrag_id="contractSelected.vertrag_id"></ContractStati>
				</div>
			</div>
				
		</div>
		
<!--		<hr>
		<div>
			&lt;!&ndash; ContractDetails &ndash;&gt;
			 <div class="row md-4" v-if="contractSelected.vertrag_id !=null">      
				<ContractDetails :person_id="person_id" :vertrag_id="contractSelected.vertrag_id"></ContractDetails>
			</div>
		</div>
		
		<div>
			&lt;!&ndash; ContractStati &ndash;&gt;
			 <div class="row md-4" v-if="contractSelected.vertrag_id !=null">      
				<ContractStati :vertrag_id="contractSelected.vertrag_id"></ContractStati>
			</div>
		</div>-->
				
		
		<!--Modal: contractModal-->
		<bs-modal ref="contractModal" dialog-class="modal-xl">
			<template #title>
				<p v-if="statusNew" class="fw-bold mt-3">{{$p.t('ui', 'add_contract')}}</p>
				<p v-else class="fw-bold mt-3">{{$p.t('ui', 'edit_contract')}}</p>
			</template>
			
			<core-form class="row g-3" ref="unassignedData">
		
				<ListUnassigned :endpoint="$fhcApi.factory.vertraege.person" :person_id="person_id"></ListUnassigned>
			
				<div class="row mb-3">
					<form-input
						type="DatePicker"
						:label="$p.t('ui/vertragsdatum')"
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
						:label="$p.t('ui/Stunden (Vertrags-Urfassung')"
						name="vertragsstunden"
						v-model="formData.vertragsstunden"
						disabled
						>
					</form-input>
				</div>
				
				<div class="row mb-3" v-if="!statusNew">		
					<form-input
						type="text"
						:label="$p.t('ui/Studiensemester (Vertrags-Urfassung')"
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

