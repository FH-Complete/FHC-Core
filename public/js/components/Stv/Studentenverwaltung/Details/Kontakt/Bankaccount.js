import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import {CoreRESTClient} from "../../../../../RESTClient";
import BsModal from "../../../../Bootstrap/Modal.js";

var editIcon = function (cell, formatterParams) {
	return "<i class='fa fa-edit'></i>";
};
var deleteIcon = function (cell, formatterParams)  {
	return "<i class='fa fa-remove'></i>";
};

export default{
	components: {
		CoreFilterCmpt,
		BsModal
	},
	props: {
		uid: String
	},
	data() {
		return{
			tabulatorOptions: {
				ajaxURL: CoreRESTClient._generateRouterURI('components/stv/Kontakt/getBankverbindung/' + this.uid),
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
					{title:"Verrechnung", field:"verrechnung", visible:false,
						formatter: (cell, formatterParams, onRendered) => {
							let output = cell.getValue() ? "ja" : "nein";
							return output;}
					},
					{title:"Person_id", field:"person_id", visible:false},
					{title:"Bankverbindung_id", field:"bankverbindung_id", visible:false},
					{formatter:editIcon, width:40, align:"center", cellClick: (e, cell) => {
							this.actionEditBankverbindung(cell.getData().bankverbindung_id);
						}, width:50, headerSort:false},
					{formatter:deleteIcon, width:40, align:"center", cellClick: (e, cell) => {
							this.actionDeleteBankverbindung(cell.getData().bankverbindung_id);
						}, width:50, headerSort:false },

				],
				layout: 'fitDataFill',
				layoutColumnsOnNewData:	false,
				height:	'auto',
				selectable:	true,
				index: 'bankverbindung_id',
			},
			tabulatorEvents: [],
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
			CoreRESTClient.post('components/stv/Kontakt/addNewBankverbindung/' + this.uid,
				this.bankverbindungData
			).then(response => {
				if (!response.data.error) {
					this.$fhcAlert.alertSuccess('Speichern erfolgreich');
/*					this.$refs.newBankverbindungModal.hide();*/
					this.hideModal('newBankverbindungModal');
					this.resetModal();
				} else {
					const errorData = response.data.retval;
					Object.entries(errorData).forEach(entry => {
						const [key, value] = entry;
						this.$fhcAlert.alertError('Das Feld ' + key + ' ist erforderlich');
					});
					this.statusCode = 0;
					this.statusMsg = response.data;
				}
			}).catch(error => {
				this.$fhcAlert.alertError('Fehler bei Speicherroutine aufgetreten');
			}).finally(() => {
				window.scrollTo(0, 0);
				this.reload();
			});
		},
		loadBankverbindung(bankverbindung_id){
			return CoreRESTClient.get('components/stv/Kontakt/loadBankverbindung/' + bankverbindung_id)
				.then(
				result => {
					if(!result.data.retval || result.data.retval.length < 1)
					{
						this.bankverbindungData = {};
						this.$fhcAlert.alertError('Keine Bankverbindung mit Id ' + bankverbindung_id + ' gefunden');
					}
					else
					{
						this.bankverbindungData = result.data.retval;
					}
					return result;
				}
			);
		},
		updateBankverbindung(bankverbindung_id){
			CoreRESTClient.post('components/stv/Kontakt/updateBankverbindung/' + bankverbindung_id,
				this.bankverbindungData)
				.then(response => {
					if (!response.data.error) {
						this.$fhcAlert.alertSuccess('Speichern erfolgreich');
						this.hideModal('editBankverbindungModal');
						this.resetModal();
					} else {
						const errorData = response.data.retval;
						Object.entries(errorData).forEach(entry => {
							const [key, value] = entry;
							this.$fhcAlert.alertError('Das Feld ' + key + ' ist erforderlich');
						});
					}
			}).catch(error => {
				this.$fhcAlert.alertError('Fehler bei Speicherroutine aufgetreten');
			}).finally(() => {
				window.scrollTo(0, 0);
				this.reload();
			});
		},
		deleteBankverbindung(bankverbindung_id){
			CoreRESTClient.post('components/stv/Kontakt/deleteBankverbindung/' + bankverbindung_id)
				.then(response => {
					if (!response.data.error || response.data === []) {
						this.$fhcAlert.alertSuccess('Löschen erfolgreich');
					} else {
						this.$fhcAlert.alertError('Keine Adresse mit Id ' + bankverbindung_id + ' gefunden');
					}
				}).catch(error => {
				this.$fhcAlert.alertError('Fehler bei Löschroutine aufgetreten');
			}).finally(()=> {
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
	async mounted() {
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
	},
	template: `	
		<div class="stv-list h-100 pt-3">
		
		<!--Modal: Add Bankverbindung-->
		<BsModal title="Bankverbindung anlegen" ref="newBankverbindungModal">
			<template #title>{{this.$p.t('person', 'bankvb_new')}}</template>
			<form class="row g-3" ref="bankverbindungData">
				<div class="row mb-3">
						<label for="name" class="form-label col-sm-4">Name</label>
						<div class="col-sm-6">
							<input type="text" :readonly="readonly" class="form-control" id="name" v-model="bankverbindungData['name']">
						</div>
					</div>
					<div class="row mb-3">										   
						<label for="anschrift" class="form-label col-sm-4">{{this.$p.t('person', 'anschrift')}}</label>
						<div class="col-sm-6">
							<input type="text" :readonly="readonly" class="form-control" id="anschrift" v-model="bankverbindungData['anschrift']">
						</div>
					</div>

					<div class="row mb-3">									   
						<label for="iban" class="form-label col-sm-4">IBAN</label>
						<div class="col-sm-6">
							<input type="text" :readonly="readonly" required class="form-control" id="iban" v-model="bankverbindungData['iban']">
						</div>
					</div>
					<div class="row mb-3">								   
						<label for="bic" class="form-label col-sm-4">BIC</label>
						<div class="col-sm-6">
							<input type="text" :readonly="readonly" class="form-control" id="bic" v-model="bankverbindungData['bic']">
						</div>
					</div>
					<div class="row mb-3">							   
						<label for="kontonr" class="form-label col-sm-4">{{this.$p.t('person', 'kontonr')}}</label>
						<div class="col-sm-6">
							<input type="text" :readonly="readonly" class="form-control" id="kontonr" v-model="bankverbindungData['kontonr']">
						</div>
					</div>
					<div class="row mb-3">										   
						<label for="blz" class="form-label col-sm-4">{{this.$p.t('person', 'blz')}}</label>
						<div class="col-sm-6">
							<input type="text" :readonly="readonly" class="form-control" id="blz" v-model="bankverbindungData['blz']">
						</div>
					</div>
					<div class="row mb-3">
						<label for="typ" class="form-label col-sm-4">{{this.$p.t('global', 'typ')}}</label>
						<div class="col-sm-6">
							<select  id="typ" class="form-select" required v-model="bankverbindungData['typ']">
								<option  value="p">{{this.$p.t('person', 'privatkonto')}}</option>
								<option  value="f">{{this.$p.t('person', 'firmenkonto')}}</option>
							</select>
						</div>
					</div>
					<div class="row mb-3">
						<label for="verrechnung" class="form-label col-sm-4">{{this.$p.t('person', 'verrechnung')}}</label>
						<div class="col-sm-3">
							<div class="form-check">
								<input id="verrechnung" type="checkbox" class="form-check-input" value="1" v-model="bankverbindungData['verrechnung']">
							</div>
						</div>
					</div>
			</form>
            <template #footer>
            		<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{this.$p.t('ui', 'abbrechen')}}</button>
					<button type="button" class="btn btn-primary" @click="addNewBankverbindung()">OK</button>
            </template>
		</BsModal>
				
		<!--Modal: Edit Bankverbindung-->
		<BsModal ref="editBankverbindungModal">
			<template #title>{{this.$p.t('person', 'bankvb_edit')}}</template>
				<form class="row g-3" ref="bankverbindungData">
				
					<div class="row mb-3">
						<label for="name" class="form-label col-sm-4">Name</label>
						<div class="col-sm-6">
							<input type="text" :readonly="readonly" class="form-control" id="name" v-model="bankverbindungData['name']">
						</div>
					</div>
					<div class="row mb-3">									   
						<label for="anschrift" class="form-label col-sm-4">{{this.$p.t('person', 'anschrift')}}</label>
						<div class="col-sm-6">
							<input type="text" :readonly="readonly" class="form-control" id="anschrift" v-model="bankverbindungData['anschrift']">
						</div>
					</div>
					<div class="row mb-3">
						<label for="iban" class="form-label col-sm-4">IBAN</label>
						<div class="col-sm-6">
							<input type="text" :readonly="readonly" required class="form-control" id="iban" v-model="bankverbindungData['iban']">
						</div>
					</div>
					<div class="row mb-3">									   
						<label for="bic" class="form-label col-sm-4">BIC</label>
						<div class="col-sm-6">
							<input type="text" :readonly="readonly" class="form-control" id="bic" v-model="bankverbindungData['bic']">
						</div>
					</div>
					<div class="row mb-3">									   
						<label for="kontonr" class="form-label col-sm-4">{{this.$p.t('person', 'kontonr')}}</label>
						<div class="col-sm-6">
							<input type="text" :readonly="readonly" class="form-control" id="kontonr" v-model="bankverbindungData['kontonr']">
						</div>
					</div>
					<div class="row mb-3">									   
						<label for="blz" class="form-label col-sm-4">{{this.$p.t('person', 'blz')}}</label>
						<div class="col-sm-6">
							<input type="text" :readonly="readonly" class="form-control" id="blz" v-model="bankverbindungData['blz']">
						</div>
					</div>
					<div class="row mb-3">
						<label for="typ" class="form-label col-sm-4">{{this.$p.t('global', 'typ')}}</label>
						<div class="col-sm-6">
							<select  id="typ" class="form-select" required v-model="bankverbindungData['typ']">
								<option  value="p">{{this.$p.t('person', 'privatkonto')}}</option>
								<option  value="f">{{this.$p.t('person', 'firmenkonto')}}</option>
							</select>
						</div>
					</div>
					<div class="row mb-3">
						<label for="verrechnung" class="form-label col-sm-4">{{this.$p.t('person', 'verrechnung')}}</label>
						<div class="col-sm-3">
							<div class="form-check">
								<input id="verrechnung" type="checkbox" class="form-check-input" value="1" v-model="bankverbindungData['verrechnung']">
							</div>
						</div>
					</div>															   
				</form>
				<template #footer>
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="resetModal">{{this.$p.t('ui', 'abbrechen')}}</button>
					<button ref="Close" type="button" class="btn btn-primary" @click="updateBankverbindung(bankverbindungData.bankverbindung_id)">OK</button>
            	</template>
		</BsModal>
		
		<!--Modal: Delete Bankverbindung-->
		<BsModal ref="deleteBankverbindungModal">
			<template #title>{{this.$p.t('person', 'bankvb_delete')}}</template>
			<template #default>
				<p>{{this.$p.t('person', 'bankvb_confirm_delete')}}</p>
			</template>									
			<template #footer>
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="resetModal">{{this.$p.t('ui', 'abbrechen')}}</button>
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
			new-btn-label="Neu"
			@click:new="actionNewBankverbindung"
		>
		</core-filter-cmpt>
		</div>`
};