import {CoreFilterCmpt} from "../../../../filter/Filter.js";
/*import {CoreRESTClient} from "../../../../../RESTClient.js";*/
import BsModal from "../../../../Bootstrap/Modal.js";
/*import PvToast from "../../../../../../../index.ci.php/public/js/components/primevue/toast/toast.esm.min.js";*/
import PvAutoComplete from "../../../../../../../index.ci.php/public/js/components/primevue/autocomplete/autocomplete.esm.min.js";

export default{
	components: {
		CoreFilterCmpt,
		PvAutoComplete,
		BsModal
	},
	props: {
		uid: String
	},
	data() {
		return{
			tabulatorOptions: {
				ajaxURL: 'api/frontend/v1/stv/Kontakt/getKontakte/' + this.uid,
				ajaxRequestFunc: this.$fhcApi.get,
				ajaxResponse: (url, params, response) => response.data,
				columns:[
					{title:"Typ", field:"kontakttyp"},
					{title:"Kontakt", field:"kontakt"},
					{title:"Zustellung", field:"zustellung",
						formatter: (cell, formatterParams, onRendered) => {
							let output = cell.getValue() ? "ja" : "nein";
							return output;}
					},
					{title:"Anmerkung", field:"anmerkung"},
					{title:"Firma", field:"kurzbz", visible:false},
					{title:"Firma_id", field:"firma_id", visible:false},
					{title:"Person_id", field:"person_id", visible:false},
					{title:"Kontakt_id", field:"kontakt_id", visible:false},
					{title:"Standort_id", field:"standort_id", visible:false},
					{title:"letzte Änderung", field:"lastupdate", visible:false},
					{title: 'Aktionen', field: 'actions',
						minWidth: 150, // Ensures Action-buttons will be always fully displayed
						formatter: (cell, formatterParams, onRendered) => {
							let container = document.createElement('div');
							container.className = "d-flex gap-2";

							let button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-edit"></i>';
							button.addEventListener('click', (event) =>
								this.actionEditContact(cell.getData().kontakt_id)
							);
							container.append(button);

							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-xmark"></i>';
							button.addEventListener('click', () =>
								this.actionDeleteContact(cell.getData().kontakt_id)
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
				index: 'kontakt_id'
			},
			tabulatorEvents: [
				{
					event: 'tableBuilt',
					handler: async() => {
						await this.$p.loadCategory(['notiz','global','person']);

						let cm = this.$refs.table.tabulator.columnManager;

						cm.getColumnByField('kontakttyp').component.updateDefinition({
							title: this.$p.t('global', 'typ')
						});
						cm.getColumnByField('kontakt').component.updateDefinition({
							title: this.$p.t('global', 'kontakt')
						});
						cm.getColumnByField('zustellung').component.updateDefinition({
							title: this.$p.t('person', 'zustellung')
						});
						cm.getColumnByField('anmerkung').component.updateDefinition({
							title: this.$p.t('global', 'anmerkung')
						});
						cm.getColumnByField('kurzbz').component.updateDefinition({
							title: this.$p.t('person', 'firma')
						});
						cm.getColumnByField('lastupdate').component.updateDefinition({
							title: this.$p.t('notiz', 'letzte_aenderung')
						});
				}
			}
			],
			lastSelected: null,
			contactData: {
				zustellung: true,
				kontakttyp: 'email'
			},
			initData: {
				zustellung: true,
				kontakttyp: 'email'
			},
			kontakttypen: [],
			standorte: [],
			selectedStandort: null,
			filteredStandorte: null
		}
	},
	watch: {
		uid(){
			this.$refs.table.tabulator.setData('api/frontend/v1/stv/Kontakt/getKontakte/' + this.uid);
		}
	},
	methods:{
		actionNewContact(){
			this.$refs.newContactModal.show();
		},
		actionEditContact(contact_id){
			this.loadContact(contact_id);
			this.$refs.editContactModal.show();
		},
		actionDeleteContact(contact_id){
			this.loadContact(contact_id);
			this.$refs.deleteContactModal.show();
		},
		addNewContact(formData) {
			this.$fhcApi.post('api/frontend/v1/stv/kontakt/addNewContact/' + this.uid,
				this.contactData)
				.then(response => {
						this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
						this.hideModal("newContactModal");
						this.resetModal();
				}).catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
				window.scrollTo(0, 0);
				this.reload();
			});
		},
		loadContact(contact_id){
			return this.$fhcApi.get('api/frontend/v1/stv/kontakt/loadContact/' + contact_id)
				.then(
					result => {
						this.contactData = result.data;
						return result;
					})
				.catch(this.$fhcAlert.handleSystemError);
		},
		deleteContact(kontakt_id){
			this.$fhcApi.post('api/frontend/v1/stv/kontakt/deleteContact/' + kontakt_id)
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(()=> {
					window.scrollTo(0, 0);
					this.hideModal('deleteContactModal');
					this.resetModal();
					this.reload();
			});
		},
		updateContact(kontakt_id){
			this.$fhcApi.post('api/frontend/v1/stv/kontakt/updateContact/' + kontakt_id,
				this.contactData).
			then(response => {
				this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
				this.hideModal('editContactModal');
				this.resetModal();
				this.reload();
			}).catch(this.$fhcAlert.handleSystemError)
			.finally(()=> {
				window.scrollTo(0, 0);
				this.reload();
			});
		},
		hideModal(modalRef){
			this.$refs[modalRef].hide();
		},
		reload(){
			this.$refs.table.reloadTable();
		},
		search(event) {
			return this.$fhcApi
				.get('api/frontend/v1/stv/kontakt/getStandorte/' + event.query)
					.then(result => {
						this.filteredStandorte = result.data.retval;
				});
		},
		resetModal(){
			this.contactData = {};
			this.contactData = this.initData;
		},
	},
	created(){
		this.$fhcApi
			.get('api/frontend/v1/stv/kontakt/getKontakttypen')
			.then(result => {
				this.kontakttypen = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: `	
		<div class="stv-list h-100 pt-3">
		
		<!--Modal: new Contact-->
		<BsModal ref="newContactModal">
			<template #title>{{$p.t('person', 'kontakt_new')}}</template>
				<form class="row g-3" ref="contactData">
				
						<div class="row mb-3">
							<label for="kontakttyp" class="form-label col-sm-4">{{$p.t('global', 'typ')}}</label>
							<div class="col-sm-6">
								<select id="kontakttyp" class="form-control" v-model="contactData.kontakttyp">
									<option value="">keine Auswahl</option>
									<option v-for="typ in kontakttypen" :key="typ.kontakttyp_kurzbz" :value="typ.kontakttyp" >{{typ.kontakttyp}}</option>
								</select>
							</div>
						</div>
						<div class="row mb-3">										   
							<label for="kontakt" class="form-label col-sm-4">{{$p.t('global', 'kontakt')}}</label>
							<div class="col-sm-6">
								<input type="text" :readonly="readonly" class="form-control" id="kontakt" v-model="contactData['kontakt']">
							</div>
						</div>
						<div class="row mb-3">									   
							<label for="anmerkung" class="form-label col-sm-4">{{$p.t('global', 'anmerkung')}}</label>
							<div class="col-sm-6">
								<input type="text" :readonly="readonly" class="form-control" id="anmerkung" v-model="contactData['anmerkung']">
							</div>
						</div>
						
						<div class="row mb-3">
							<label for="zustellung" class="form-label col-sm-4">{{$p.t('person', 'zustellung')}}</label>
							<div class="col-sm-6">
								<div class="form-check">
									<input id="zustellung" type="checkbox" class="form-check-input" value="1" v-model="contactData['zustellung']">
								</div>
							</div>
						</div>
							
						<div class="row mb-3">
							<label for="firma_name" class="form-label col-sm-4">{{$p.t('person', 'firma')}} / {{$p.t('person', 'standort')}}</label>
								<div class="col-sm-6">
									<PvAutoComplete v-model="contactData['standort']" optionLabel="kurzbz" :suggestions="filteredStandorte" @complete="search" minLength="3"/>
								</div>
						</div>
				</form>
			    <template #footer>
            		<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{$p.t('ui', 'abbrechen')}}</button>
					<button type="button" class="btn btn-primary" @click="addNewContact()">OK</button>
            	</template>
		</BsModal>
						
		<!--Modal: Edit Contact-->
		<BsModal ref="editContactModal">
			<template #title>{{$p.t('person', 'kontakt_edit')}}</template>
			<form class="row g-3" ref="contactData">
				<div class="row mb-3">
					<label for="kontakttyp" class="form-label col-sm-4">{{$p.t('global', 'typ')}}</label>
					<div class="col-sm-6">
						<select id="kontakttyp" class="form-control" v-model="contactData.kontakttyp">
							<option value="">-- keine Auswahl --</option>
							<option v-for="typ in kontakttypen" :key="typ.kontakttyp_kurzbz" :value="typ.kontakttyp" >{{typ.kontakttyp}}</option>
						</select>
					</div>
				</div>
				<div class="row mb-3">									   
					<label for="kontakt" class="form-label col-sm-4">{{$p.t('global', 'kontakt')}}</label>
					<div class="col-sm-6">
						<input type="text" :readonly="readonly" class="form-control" id="kontakt" v-model="contactData['kontakt']">
					</div>
				</div>
				<div class="row mb-3">									   
					<label for="anmerkung" class="form-label col-sm-4">{{$p.t('global', 'anmerkung')}}</label>
					<div class="col-sm-6">
						<input type="text" :readonly="readonly" class="form-control" id="anmerkung" v-model="contactData['anmerkung']">
					</div>
				</div>
				<div class="row mb-3">
					<label for="zustellung" class="form-label col-sm-4">{{$p.t('person', 'zustellung')}}</label>
					<div class="col-sm-6">
						<div class="form-check">
							<input id="zustellung" type="checkbox" class="form-check-input" value="1" v-model="contactData['zustellung']">
						</div>
					</div>
				</div>
				<div class="row mb-3">			
					<input type="hidden" :readonly="readonly" class="form-control" id="standort_id" v-model="contactData.standort_id">
				</div>
				
				<div class="row mb-3">
					<label for="standort" class="form-label col-sm-4">{{$p.t('person', 'firma')}} / {{$p.t('person', 'standort')}}</label>
						<div v-if="contactData.kurzbz" class="col-sm-6">
							<input type="text" :readonly="readonly" class="form-control" id="name" v-model="contactData.kurzbz">
						</div>	
						<div v-else class="col-sm-3">
							<PvAutoComplete v-model="contactData['standort']" optionLabel="kurzbz" :suggestions="filteredStandorte" @complete="search" minLength="3"/>
						</div>
				</div>
			</form>
			<template #footer>
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="resetModal">{{$p.t('ui', 'abbrechen')}}</button>
				<button type="button" class="btn btn-primary" @click="updateContact(contactData.kontakt_id)">OK</button>
			</template>
		</BsModal>
									
		<!--Modal: Delete Contact-->
		<BsModal ref="deleteContactModal">
			<template #title>{{$p.t('person', 'kontakt_delete')}}</template>  
			<template #default>
				<p>{{$p.t('person', 'kontakt_confirm_delete')}}</p>
			</template>												
			<template #footer>
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="resetModal">{{$p.t('ui', 'abbrechen')}}</button>
				<button ref="Close" type="button" class="btn btn-primary" @click="deleteContact(contactData.kontakt_id)">OK</button>
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
			new-btn-label="Kontakt"
			@click:new="actionNewContact"
			>
		</core-filter-cmpt>
		</div>`
};