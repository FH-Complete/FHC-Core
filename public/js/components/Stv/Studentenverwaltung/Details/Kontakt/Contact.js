import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import BsModal from "../../../../Bootstrap/Modal.js";
import PvAutoComplete from "../../../../../../../index.ci.php/public/js/components/primevue/autocomplete/autocomplete.esm.min.js";
import FormForm from '../../../../Form/Form.js';
import FormInput from '../../../../Form/Input.js';

export default{
	components: {
		CoreFilterCmpt,
		PvAutoComplete,
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
				ajaxURL: 'api/frontend/v1/stv/Kontakt/getKontakte/' + this.uid,
				ajaxRequestFunc: this.$fhcApi.get,
				ajaxResponse: (url, params, response) => response.data,
				columns:[
					{title:"Typ", field:"kontakttyp"},
					{title:"Kontakt", field:"kontakt"},
					{
						title:"Zustellung",
						field:"zustellung",
						formatter:"tickCross",
						hozAlign:"center",
						formatterParams: {
							tickElement: '<i class="fa fa-check text-success"></i>',
							crossElement: '<i class="fa fa-xmark text-danger"></i>'
						}
					},
					{title:"Anmerkung", field:"anmerkung"},
					{title:"Firma", field:"name", visible:false},
					{title:"Standort", field:"bezeichnung", visible:false},
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
				index: 'kontakt_id',
				persistenceID: 'stv-details-kontakt-contact'
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

						cm.getColumnByField('lastupdate').component.updateDefinition({
							title: this.$p.t('notiz', 'letzte_aenderung')
						});
						cm.getColumnByField('name').component.updateDefinition({
							title: this.$p.t('person', 'firma')
						});
						cm.getColumnByField('bezeichnung').component.updateDefinition({
							title: this.$p.t('person', 'standort')
						});
				}}
			],
			lastSelected: null,
			contactData: {
				zustellung: true,
				kontakttyp: 'email',
				firma_id: null
			},
			statusNew: true,
			kontakttypen: [],
			firmen: [],
			filteredFirmen: [],
			filteredOrte: null,
		}
	},
	watch: {
		uid() {
			this.$refs.table.tabulator.setData('api/frontend/v1/stv/Kontakt/getKontakte/' + this.uid);
		},
		contactData: {
			handler(newVal) {
				if (newVal.firma && newVal.firma.firma_id !== null && typeof newVal.firma.firma_id !== 'undefined') {
					this.loadStandorte(this.contactData.firma.firma_id);
				}
			},
			deep: true
		}
	},
	methods:{
		actionNewContact(){
			this.resetModal();
			this.$refs.contactModal.show();
		},
		actionEditContact(contact_id){
			this.statusNew = false;
			this.loadContact(contact_id);
			this.$refs.contactModal.show();
		},
		actionDeleteContact(contact_id){
			this.loadContact(contact_id);

			this.$fhcAlert
				.confirmDelete()
				.then(result => result
					? contact_id
					: Promise.reject({handled: true}))
				.then(this.deleteContact)
				.catch(this.$fhcAlert.handleSystemError);
		},
		addNewContact(formData) {
			this.$fhcApi.post('api/frontend/v1/stv/kontakt/addNewContact/' + this.uid,
				this.contactData)
				.then(response => {
						this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
						this.hideModal("contactModal");
						this.resetModal();
				}).catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
				window.scrollTo(0, 0);
				this.reload();
			});
		},
		loadContact(contact_id){
			this.statusNew = false;
			if(this.contactData.firma_id)
				this.loadStandorte(this.contactData.firma_id);
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
					this.resetModal();
					this.reload();
			});
		},
		updateContact(kontakt_id){
			this.$fhcApi.post('api/frontend/v1/stv/kontakt/updateContact/' + kontakt_id,
				this.contactData).
			then(response => {
				this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
				this.hideModal('contactModal');
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
		searchFirma(event) {
			return this.$fhcApi
				.get('api/frontend/v1/stv/kontakt/getFirmen/' + event.query)
				.then(result => {
					this.filteredFirmen = result.data.retval;
				});
		},
		loadStandorte(firmen_id) {
			return this.$fhcApi
				.get('api/frontend/v1/stv/kontakt/getStandorteByFirma/' + firmen_id)
				.then(result => {
					this.filteredOrte = result.data;
				});
		},
		resetModal(){
			this.contactData = {};
			this.contactData.zustellung = true;
			this.contactData.kontakttyp = 'email';
			this.contactData.kontakt = '';
			this.contactData.anmerkung = null;
			this.contactData.firma_id = null;
			this.contactData.name = null;
			this.contactData.standort_id = null;
			this.contactData.bezeichnung = null;

			this.statusNew = true;
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
		<div class="stv-details-kontakt-contact h-100 pt-3">

		<!--Modal: contactModal-->
		<BsModal ref="contactModal">
			<template #title>
				<p v-if="statusNew" class="fw-bold mt-3">{{$p.t('person', 'kontakt_new')}}</p>
				<p v-else class="fw-bold mt-3">{{$p.t('person', 'kontakt_edit')}}</p>
			</template>
				
			<form-form class="row g-3" ref="contactData">
			
				<div class="row my-3">
				
					<form-input 
						type="select" 
						name="typ"
						:label="$p.t('global/typ')"
						v-model="contactData.kontakttyp">
					>
						<option value="">keine Auswahl</option>
						<option v-for="typ in kontakttypen" :key="typ.kontakttyp_kurzbz" :value="typ.kontakttyp" >{{typ.kontakttyp}}</option>
					</form-input>
				</div>
				
				<div class="row mb-3">										   
					<form-input 
						type="text" 
						name="kontakt" 
						:label="$p.t('global/kontakt')+ ' *'"
						v-model="contactData.kontakt">
						required
						>
					</form-input>
				</div>
				
				<div class="row mb-3">									   
					<form-input 
						type="text" 
						name="anmerkung"
						:label="$p.t('global/anmerkung')"
						v-model="contactData.anmerkung">
						>
					</form-input>
				</div>
				
				<div class="row mb-3">
					<div class="col-sm-4">
						<form-input
							container-class="form-check"
							type="checkbox"
							name="zustellung"
							:label="$p.t('person/zustellung')"
							v-model="contactData.zustellung"
						>
						</form-input>
					</div>
				</div>
				
				<div v-if="statusNew" class="row mb-3">
					<form-input
						type="autocomplete"
						:label="$p.t('person/firma')"
						name="firma_name"
						v-model="contactData.firma"  
						optionLabel="name" 
						:suggestions="filteredFirmen" 
						@complete="searchFirma" 
						:min-length="3"
					>
					</form-input>
				</div>				
					
				<div v-else class="row mb-3">
					<form-input
						v-if="contactData.name" 
						type="text"
						name="name"
						:label="$p.t('person/firma')"
						v-model="contactData.name"
					>
					</form-input>
					<form-input
						v-else 
						type="autocomplete"
						:label="$p.t('person/firma')"
						name="firma_name"
						v-model="contactData.firma"  
						optionLabel="name" 
						:suggestions="filteredFirmen" 
						@complete="searchFirma" 
						:min-length="3"
					>
					</form-input>
				</div>
				
				<input type="hidden" class="form-control" id="firma_id" v-model="contactData.firma_id">
			
				<input type="hidden" class="form-control" id="standort_id" v-model="contactData.standort_id">
			
				<div class="row mb-3" v-if="contactData.standort_id || filteredOrte">
					<form-input
						v-if="contactData.name"
						type="text"
						name="name"
						:label="$p.t('person/firma') + ' / ' + $p.t('person/standort')" 
						v-model="contactData.bezeichnung"
					>
					</form-input>
					<form-input
						v-else
						type="select"
						name="ort"
						:label="$p.t('person/standort')"
						v-model="contactData.standort_id"
						>
						<option v-if="filteredOrte" disabled>{{$p.t('ui', 'bitteStandortWaehlen')}}</option>
						<option 
							v-for="ort in filteredOrte" 
							:key="ort.standort_id" 
							:value="ort.standort_id"
							>
							{{ort.bezeichnung}}
						</option>
					</form-input>
				</div>		
				
			</form-form>
				
			<template #footer>
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{$p.t('ui', 'abbrechen')}}</button>
				<button v-if="statusNew" type="button" class="btn btn-primary" @click="addNewContact()">OK</button>
				<button v-else type="button" class="btn btn-primary" @click="updateContact(contactData.kontakt_id)">OK</button>
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