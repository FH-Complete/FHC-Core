import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import BsModal from "../../../../Bootstrap/Modal.js";
import FormForm from '../../../../Form/Form.js';
import FormInput from '../../../../Form/Input.js';

export default{
	components: {
		CoreFilterCmpt,
		BsModal,
		FormForm,
		FormInput
	},
	props: {
		uid: Number
	},
	data() {
		return{
			tabulatorOptions: {
				ajaxURL: 'api/frontend/v1/stv/Kontakt/getBankverbindung/' + this.uid,
				ajaxRequestFunc: this.$fhcApi.get,
				ajaxResponse: (url, params, response) => response.data,
				columns:[
					{title:"Name", field:"name"},
					{title:"Anschrift", field:"anschrift", visible:false},
					{title:"BIC", field:"bic"},
					{title:"BLZ", field:"blz", visible:false},
					{title:"IBAN", field:"iban"},
					{title:"Kontonummer", field:"kontonr", visible:false},
					{title:"Typ", field:"typ", visible:false,
						formatter: (cell, formatterParams, onRendered) => {
							let output;
							switch(cell.getValue()){
								case "p":
									output = "Privatkonto";
									break;
								case "f":
									output = "Firmenkonto";
									break;
								default:
									output = cell.getValue();
							}
							return output;}
					},
					{
						title:"Verrechnung",
						field:"verrechnung",
						visible:false,
						formatter:"tickCross",
						hozAlign:"center",
						formatterParams: {
							tickElement: '<i class="fa fa-check text-success"></i>',
							crossElement: '<i class="fa fa-xmark text-danger"></i>'
						}
					},
					{title:"Person_id", field:"person_id", visible:false},
					{title:"Bankverbindung_id", field:"bankverbindung_id", visible:false},
					{title: 'Aktionen', field: 'actions',
						minWidth: 150, // Ensures Action-buttons will be always fully displayed
						formatter: (cell, formatterParams, onRendered) => {
							let container = document.createElement('div');
							container.className = "d-flex gap-2";

							let button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-edit"></i>';
							button.addEventListener('click', (event) =>
								this.actionEditBankverbindung(cell.getData().bankverbindung_id)
							);
							container.append(button);

							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-xmark"></i>';
							button.addEventListener('click', () =>
								this.actionDeleteBankverbindung(cell.getData().bankverbindung_id)
							);
							container.append(button);

							return container;
						},
						frozen: true
					},
				],
				layout: 'fitDataFill',
				layoutColumnsOnNewData:	false,
				height:	'auto',
				selectable:	true,
				index: 'bankverbindung_id',
				persistenceID: 'stv-details-kontakt-bankaccount'
			},
			tabulatorEvents: [
				{
					event: 'tableBuilt',
					handler: async() => {
						await this.$p.loadCategory(['global','person']);

						let cm = this.$refs.table.tabulator.columnManager;

						cm.getColumnByField('anschrift').component.updateDefinition({
							title: this.$p.t('person', 'anschrift')
						});
						cm.getColumnByField('kontonr').component.updateDefinition({
							title: this.$p.t('person', 'kontonr')
						});
						cm.getColumnByField('blz').component.updateDefinition({
							title: this.$p.t('person', 'blz')
						});
						cm.getColumnByField('typ').component.updateDefinition({
							title: this.$p.t('global', 'typ')
						});
						cm.getColumnByField('verrechnung').component.updateDefinition({
							title: this.$p.t('person', 'verrechnung')
						});
					}
				}
			],
			lastSelected: null,
			bankverbindungData: {
				verrechnung: true,
				typ: 'p'
			},
			statusNew: true
		}
	},
	watch: {
		uid(){
			this.$refs.table.tabulator.setData('api/frontend/v1/stv/Kontakt/getBankverbindung/' + this.uid);
		}
	},
	methods:{
		actionNewBankverbindung(){
			this.resetModal();
			this.$refs.bankverbindungModal.show();
		},
		actionEditBankverbindung(bankverbindung_id){
			this.statusNew = false;
			this.loadBankverbindung(bankverbindung_id).then(() => {
				if(this.bankverbindungData.bankverbindung_id)
					this.$refs.bankverbindungModal.show();
			});
		},
		actionDeleteBankverbindung(bankverbindung_id){
			this.loadBankverbindung(bankverbindung_id).then(() => {
				this.$fhcAlert
					.confirmDelete()
					.then(result => result
						? bankverbindung_id
						: Promise.reject({handled: true}))
					.then(this.deleteBankverbindung)
					.catch(this.$fhcAlert.handleSystemError);
			});
		},
		addNewBankverbindung(bankverbindungData) {
			this.$fhcApi.post('api/frontend/v1/stv/kontakt/addNewBankverbindung/' + this.uid,
				this.bankverbindungData
			).then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.hideModal('bankverbindungModal');
					this.resetModal();
			}).catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
				window.scrollTo(0, 0);
				this.reload();
			});
		},
		loadBankverbindung(bankverbindung_id){
			this.statusNew = false;
			return this.$fhcApi.get('api/frontend/v1/stv/kontakt/loadBankverbindung/' + bankverbindung_id)
				.then(
				result => {
					this.bankverbindungData = result.data;
					return result;
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		updateBankverbindung(bankverbindung_id){
			this.$fhcApi.post('api/frontend/v1/stv/kontakt/updateBankverbindung/' + bankverbindung_id,
				this.bankverbindungData)
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.hideModal('bankverbindungModal');
					this.resetModal();
			}).catch(this.$fhcAlert.handleSystemError)
			.finally(() => {
				window.scrollTo(0, 0);
				this.reload();
			});
		},
		deleteBankverbindung(bankverbindung_id){
			this.$fhcApi.post('api/frontend/v1/stv/kontakt/deleteBankverbindung/' + bankverbindung_id)
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));
				}).catch(this.$fhcAlert.handleSystemError)
			.finally(()=> {
				window.scrollTo(0, 0);
				this.resetModal();
				this.reload();
			});
		},
		hideModal(modalRef){
			this.$refs[modalRef].hide();
		},
		reload(){
			this.$refs.table.reloadTable();
		},
		resetModal(){
			this.bankverbindungData = {};
			this.bankverbindungData.name = "";
			this.bankverbindungData.anschrift = "";
			this.bankverbindungData.iban = "";
			this.bankverbindungData.bic = "";
			this.bankverbindungData.kontonr = "";
			this.bankverbindungData.blz = "";
			this.bankverbindungData.bic = "";
			this.bankverbindungData.verrechnung = true;
			this.bankverbindungData.typ = 'p';

			this.statusNew = true;
		},
	},
	template: `	
		<div class="stv-details-kontakt-bankaccount h-100 pt-3">
		
		<!--Modal: Bankverbindung-->
		<BsModal title="Bankverbindung anlegen" ref="bankverbindungModal">
			<template #title>
				<p v-if="statusNew" class="fw-bold mt-3">{{$p.t('person', 'bankvb_new')}}</p>
				<p v-else class="fw-bold mt-3">{{$p.t('person', 'bankvb_edit')}}</p>
			</template>

			<form-form class="row g-3" ref="bankverbindungData">
			
				<div class="row my-3">
					<form-input 
						type="text"
						name="name"
						label="Name"
						v-model="bankverbindungData.name"
					>
					</form-input>
				</div>
				
				<div class="row mb-3">										   
					<form-input 
						type="text"
						name="anschrift"
						:label="$p.t('person/anschrift')"
						v-model="bankverbindungData.anschrift"
					>
					</form-input>
				</div>

				<div class="row mb-3">							   
					<form-input 
						type="text"
						name="iban"
						label="IBAN *"
						v-model="bankverbindungData.iban"
						required
					>
					</form-input>
				</div>
				
				<div class="row mb-3">
					<form-input 
						type="text"
						name="bic"
						label="BIC"
						v-model="bankverbindungData.bic"
						>
					</form-input>
				</div>
				<div class="row mb-3">
					<form-input 
						type="text"
						name="kontonr"
						:label="$p.t('person/kontonr')"
						v-model="bankverbindungData.kontonr"
						>
					</form-input>
				</div>
				<div class="row mb-3">
					<form-input 
						type="text"
						name="blz"
						:label="$p.t('person/blz')"
						v-model="bankverbindungData.blz"
						>
					</form-input>
				</div>
				<div class="row mb-3">
					<form-input 
						type="select" 
						name="typ"
						:label="$p.t('global/typ')"
						v-model="bankverbindungData.typ"
						required
						>
						<option  value="p">{{$p.t('person', 'privatkonto')}}</option>
						<option  value="f">{{$p.t('person', 'firmenkonto')}}</option>
					</form-input>
				</div>
				<div class="row mb-3">
					<form-input
						container-class="form-check"
						type="checkbox"
						name="verrechnung"
						:label="$p.t('person/verrechnung')"
						v-model="bankverbindungData.verrechnung"
					>
					</form-input>
				</div>
			</form-form>
			
			<template #footer>
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{$p.t('ui', 'abbrechen')}}</button>
				<button v-if="statusNew" type="button" class="btn btn-primary" @click="addNewBankverbindung()">OK</button>
				<button v-else type="button" class="btn btn-primary" @click="updateBankverbindung(bankverbindungData.bankverbindung_id)">OK</button>
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
			new-btn-label="Bankverbindung"
			@click:new="actionNewBankverbindung"
		>
		</core-filter-cmpt>
		</div>`
};