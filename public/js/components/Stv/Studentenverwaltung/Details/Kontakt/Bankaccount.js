import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import BsModal from "../../../../Bootstrap/Modal.js";

export default{
	components: {
		CoreFilterCmpt,
		BsModal
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
			initData: {
				verrechnung: true,
				typ: 'p'
			}
		}
	},
	watch: {
		uid(){
			this.$refs.table.tabulator.setData('api/frontend/v1/stv/Kontakt/getBankverbindung/' + this.uid);
		}
	},
	methods:{
		actionNewBankverbindung(){
			this.$refs.newBankverbindungModal.show();
		},
		actionEditBankverbindung(bankverbindung_id){
			this.loadBankverbindung(bankverbindung_id).then(() => {
				if(this.bankverbindungData.bankverbindung_id)
					this.$refs.editBankverbindungModal.show();
			});
		},
		actionDeleteBankverbindung(bankverbindung_id){
			this.loadBankverbindung(bankverbindung_id).then(() => {
				if(this.bankverbindungData.bankverbindung_id)
					this.$refs.deleteBankverbindungModal.show();
			});
		},
		addNewBankverbindung(bankverbindungData) {
			this.$fhcApi.post('api/frontend/v1/stv/kontakt/addNewBankverbindung/' + this.uid,
				this.bankverbindungData
			).then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.hideModal('newBankverbindungModal');
					this.resetModal();
			}).catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
				window.scrollTo(0, 0);
				this.reload();
			});
		},
		loadBankverbindung(bankverbindung_id){
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
					this.hideModal('editBankverbindungModal');
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
				this.hideModal('deleteBankverbindungModal');
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
			this.bankverbindungData = this.initData;
		},
	},
	template: `	
		<div class="stv-list h-100 pt-3">
		
		<!--Modal: Add Bankverbindung-->
		<BsModal title="Bankverbindung anlegen" ref="newBankverbindungModal">
			<template #title>{{$p.t('person', 'bankvb_new')}}</template>
			<form class="row g-3" ref="bankverbindungData">
				<div class="row mb-3">
						<label for="name" class="form-label col-sm-4">Name</label>
						<div class="col-sm-6">
							<input type="text" class="form-control" id="name" v-model="bankverbindungData['name']">
						</div>
					</div>
					<div class="row mb-3">										   
						<label for="anschrift" class="form-label col-sm-4">{{$p.t('person', 'anschrift')}}</label>
						<div class="col-sm-6">
							<input type="text" class="form-control" id="anschrift" v-model="bankverbindungData['anschrift']">
						</div>
					</div>

					<div class="row mb-3">									   
						<label for="iban" class="form-label col-sm-4">IBAN<sup>*</sup></label>
						<div class="col-sm-6">
							<input type="text" required class="form-control" id="iban" v-model="bankverbindungData['iban']">
						</div>
					</div>
					<div class="row mb-3">								   
						<label for="bic" class="form-label col-sm-4">BIC</label>
						<div class="col-sm-6">
							<input type="text" class="form-control" id="bic" v-model="bankverbindungData['bic']">
						</div>
					</div>
					<div class="row mb-3">							   
						<label for="kontonr" class="form-label col-sm-4">{{$p.t('person', 'kontonr')}}</label>
						<div class="col-sm-6">
							<input type="text" class="form-control" id="kontonr" v-model="bankverbindungData['kontonr']">
						</div>
					</div>
					<div class="row mb-3">										   
						<label for="blz" class="form-label col-sm-4">{{$p.t('person', 'blz')}}</label>
						<div class="col-sm-6">
							<input type="text" class="form-control" id="blz" v-model="bankverbindungData['blz']">
						</div>
					</div>
					<div class="row mb-3">
						<label for="typ" class="form-label col-sm-4">{{$p.t('global', 'typ')}}<sup>*</sup></label>
						<div class="col-sm-6">
							<select  id="typ" class="form-select" required v-model="bankverbindungData['typ']">
								<option  value="p">{{$p.t('person', 'privatkonto')}}</option>
								<option  value="f">{{$p.t('person', 'firmenkonto')}}</option>
							</select>
						</div>
					</div>
					<div class="row mb-3">
						<label for="verrechnung" class="form-label col-sm-4">{{$p.t('person', 'verrechnung')}}</label>
						<div class="col-sm-3">
							<div class="form-check">
								<input id="verrechnung" type="checkbox" class="form-check-input" value="1" v-model="bankverbindungData['verrechnung']">
							</div>
						</div>
					</div>
			</form>
            <template #footer>
            		<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{$p.t('ui', 'abbrechen')}}</button>
					<button type="button" class="btn btn-primary" @click="addNewBankverbindung()">OK</button>
            </template>
		</BsModal>
				
		<!--Modal: Edit Bankverbindung-->
		<BsModal ref="editBankverbindungModal">
			<template #title>{{$p.t('person', 'bankvb_edit')}}</template>
				<form class="row g-3" ref="bankverbindungData">
				
					<div class="row mb-3">
						<label for="name" class="form-label col-sm-4">Name</label>
						<div class="col-sm-6">
							<input type="text" class="form-control" id="name" v-model="bankverbindungData['name']">
						</div>
					</div>
					<div class="row mb-3">									   
						<label for="anschrift" class="form-label col-sm-4">{{$p.t('person', 'anschrift')}}</label>
						<div class="col-sm-6">
							<input type="text" class="form-control" id="anschrift" v-model="bankverbindungData['anschrift']">
						</div>
					</div>
					<div class="row mb-3">
						<label for="iban" class="form-label col-sm-4">IBAN<sup>*</sup></label>
						<div class="col-sm-6">
							<input type="text" required class="form-control" id="iban" v-model="bankverbindungData['iban']">
						</div>
					</div>
					<div class="row mb-3">									   
						<label for="bic" class="form-label col-sm-4">BIC</label>
						<div class="col-sm-6">
							<input type="text" class="form-control" id="bic" v-model="bankverbindungData['bic']">
						</div>
					</div>
					<div class="row mb-3">									   
						<label for="kontonr" class="form-label col-sm-4">{{$p.t('person', 'kontonr')}}</label>
						<div class="col-sm-6">
							<input type="text" class="form-control" id="kontonr" v-model="bankverbindungData['kontonr']">
						</div>
					</div>
					<div class="row mb-3">									   
						<label for="blz" class="form-label col-sm-4">{{$p.t('person', 'blz')}}</label>
						<div class="col-sm-6">
							<input type="text" class="form-control" id="blz" v-model="bankverbindungData['blz']">
						</div>
					</div>
					<div class="row mb-3">
						<label for="typ" class="form-label col-sm-4">{{$p.t('global', 'typ')}}<sup>*</sup></label>
						<div class="col-sm-6">
							<select  id="typ" class="form-select" required v-model="bankverbindungData['typ']">
								<option  value="p">{{$p.t('person', 'privatkonto')}}</option>
								<option  value="f">{{$p.t('person', 'firmenkonto')}}</option>
							</select>
						</div>
					</div>
					<div class="row mb-3">
						<label for="verrechnung" class="form-label col-sm-4">{{$p.t('person', 'verrechnung')}}</label>
						<div class="col-sm-3">
							<div class="form-check">
								<input id="verrechnung" type="checkbox" class="form-check-input" value="1" v-model="bankverbindungData['verrechnung']">
							</div>
						</div>
					</div>															   
				</form>
				<template #footer>
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="resetModal">{{$p.t('ui', 'abbrechen')}}</button>
					<button ref="Close" type="button" class="btn btn-primary" @click="updateBankverbindung(bankverbindungData.bankverbindung_id)">OK</button>
            	</template>
		</BsModal>
		
		<!--Modal: Delete Bankverbindung-->
		<BsModal ref="deleteBankverbindungModal">
			<template #title>{{$p.t('person', 'bankvb_delete')}}</template>
			<template #default>
				<p>{{$p.t('person', 'bankvb_confirm_delete')}}</p>
			</template>									
			<template #footer>
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="resetModal">{{$p.t('ui', 'abbrechen')}}</button>
				<button ref="Close" type="button" class="btn btn-primary" @click="deleteBankverbindung(bankverbindungData.bankverbindung_id)">OK</button>
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