import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import {CoreRESTClient} from "../../../../../RESTClient";
import BsModal from "../../../../Bootstrap/Modal.js";
/*import PvToast from "../../../../../../../index.ci.php/public/js/components/primevue/toast/toast.esm.min.js";*/
import PvAutoComplete from "../../../../../../../index.ci.php/public/js/components/primevue/autocomplete/autocomplete.esm.min.js";

var editIcon = function (cell, formatterParams) {
	return "<i class='fa fa-edit'></i>";
};
var deleteIcon = function (cell, formatterParams){
	return "<i class='fa fa-remove'></i>";
};

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
				ajaxURL: CoreRESTClient._generateRouterURI('components/stv/Kontakt/getKontakte/' + this.uid),
				columns:[
					{title:"Typ", field:"kontakttyp"}, //TODO(manu) mix ok?
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
					{title:"letzte Änderung", field:"updateamum", visible:false},
						{formatter:editIcon, cellClick: (e, cell) => {
								this.actionEditContact(cell.getData().kontakt_id);
							}, width:50, headerSort:false, headerVisible:false},
						{formatter:deleteIcon, cellClick: (e, cell) => {
								this.actionDeleteContact(cell.getData().kontakt_id);

							}, width:50, headerSort:false, headerVisible:false},
				],
				layout: 'fitDataFill',
				layoutColumnsOnNewData:	false,
				height:	'auto',
				selectable:	true,
				index: 'kontakt_id'
			},
			tabulatorEvents: [],
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
			CoreRESTClient.post('components/stv/Kontakt/addNewContact/' + this.uid,
				this.contactData)
				.then(response => {
					if (!response.data.error) {
						this.$fhcAlert.alertSuccess('Speichern erfolgreich');
						this.hideModal("newContactModal");
						this.resetModal();
					} else {
						const errorData = response.data.retval;
						Object.entries(errorData).forEach(entry => {
							const [key, value] = entry;
							this.$fhcAlert.alertError(value);
						});
					}
			}).catch(error => {
				this.$fhcAlert.alertError('Fehler bei Speicherroutine aufgetreten');
			}).finally(() => {
				window.scrollTo(0, 0);
				this.reload();
			});
		},
		loadContact(contact_id){
			return CoreRESTClient.get('components/stv/Kontakt/loadContact/' + contact_id)
				.then(
					result => {
						if(result.data.retval)
							this.contactData = result.data.retval;
						else
						{
							this.contactData = {};
							this.$fhcAlert.alertError('Kein Kontakt mit Id ' + contact_id + ' gefunden');
						}
						return result;
					}
			);
		},
		deleteContact(kontakt_id){
			CoreRESTClient.post('components/stv/Kontakt/deleteContact/' + kontakt_id)
				.then(response => {
					if (!response.data.error) {
						this.$fhcAlert.alertSuccess('Löschen erfolgreich');
					} else {
						this.$fhcAlert.alertError('Keine Adresse mit Id ' + kontakt_id + ' gefunden');
					}
				})
				.catch(error => {
					this.$fhcAlert.alertError('Fehler bei Löschroutine aufgetreten');
			}).finally(()=> {
				window.scrollTo(0, 0);
				this.hideModal('deleteContactModal');
				this.resetModal();
				this.reload();
			});
		},
		updateContact(kontakt_id){
			CoreRESTClient.post('components/stv/Kontakt/updateContact/' + kontakt_id,
				this.contactData).
			then(response => {
				if (!response.data.error) {
					this.$fhcAlert.alertSuccess('Speichern erfolgreich');
					this.hideModal('editContactModal');
					this.resetModal();
					this.reload();
				} else {
					const errorData = response.data.retval;
					Object.entries(errorData).forEach(entry => {
						const [key, value] = entry;
						this.$fhcAlert.alertError(value);
					});
				}
			}).catch(error => {
				this.$fhcAlert.alertError('Fehler bei Speicherroutine aufgetreten');
			}).finally(() => {
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
			return CoreRESTClient
				.get('components/stv/Kontakt/getStandorte/' + event.query)
					.then(result => {
						this.filteredStandorte = CoreRESTClient.getData(result.data);
				});
		},
		resetModal(){
			this.contactData = {};
			this.contactData = this.initData;
		},
	},
	created(){
		CoreRESTClient
			.get('components/stv/Kontakt/getKontakttypen')
			.then(result => {
				this.kontakttypen = result.data;
			})
			.catch(err => {
				console.error(err.response.data || err.message);
			});
	},
	template: `	
		<div class="stv-list h-100 pt-3">
		
		<!--Modal: new Contact-->
		<BsModal ref="newContactModal">
			<template #title>Kontakt anlegen</template>
				<form class="row g-3" ref="contactData">
				
						<div class="row mb-3">
							<label for="kontakttyp" class="form-label col-sm-4">Typ</label>
							<div class="col-sm-6">
								<select id="kontakttyp" class="form-control" v-model="contactData.kontakttyp">
									<option value="">keine Auswahl</option>
									<option v-for="typ in kontakttypen" :key="typ.kontakttyp_kurzbz" :value="typ.kontakttyp" >{{typ.kontakttyp}}</option>
								</select>
							</div>
						</div>
						<div class="row mb-3">										   
							<label for="kontakt" class="form-label col-sm-4">Kontakt</label>
							<div class="col-sm-6">
								<input type="text" :readonly="readonly" class="form-control" id="kontakt" v-model="contactData['kontakt']">
							</div>
						</div>
						<div class="row mb-3">									   
							<label for="anmerkung" class="form-label col-sm-4">Anmerkung</label>
							<div class="col-sm-6">
								<input type="text" :readonly="readonly" class="form-control" id="anmerkung" v-model="contactData['anmerkung']">
							</div>
						</div>
						
						<div class="row mb-3">
							<label for="zustellung" class="form-label col-sm-4">Zustellung</label>
							<div class="col-sm-6">
								<div class="form-check">
									<input id="zustellung" type="checkbox" class="form-check-input" value="1" v-model="contactData['zustellung']">
								</div>
							</div>
						</div>
							
						<div class="row mb-3">
							<label for="firma_name" class="form-label col-sm-4">Firma / Standort</label>
								<div class="col-sm-6">
									<PvAutoComplete v-model="contactData['standort']" optionLabel="kurzbz" :suggestions="filteredStandorte" @complete="search" minLength="3"/>
								</div>
						</div>
				</form>
			    <template #footer>
            		<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
					<button type="button" class="btn btn-primary" @click="addNewContact()">OK</button>
            	</template>
		</BsModal>
						
		<!--Modal: Edit Contact-->
		<BsModal ref="editContactModal">
			<template #title>Kontakt bearbeiten</template>
			<form class="row g-3" ref="contactData">
				<div class="row mb-3">
					<label for="kontakttyp" class="form-label col-sm-4">Typ</label>
					<div class="col-sm-6">
						<select id="kontakttyp" class="form-control" v-model="contactData.kontakttyp">
							<option value="">-- keine Auswahl --</option>
							<option v-for="typ in kontakttypen" :key="typ.kontakttyp_kurzbz" :value="typ.kontakttyp" >{{typ.kontakttyp}}</option>
						</select>
					</div>
				</div>
				<div class="row mb-3">									   
					<label for="kontakt" class="form-label col-sm-4">Kontakt</label>
					<div class="col-sm-6">
						<input type="text" :readonly="readonly" class="form-control" id="kontakt" v-model="contactData['kontakt']">
					</div>
				</div>
				<div class="row mb-3">									   
					<label for="anmerkung" class="form-label col-sm-4">Anmerkung</label>
					<div class="col-sm-6">
						<input type="text" :readonly="readonly" class="form-control" id="anmerkung" v-model="contactData['anmerkung']">
					</div>
				</div>
				<div class="row mb-3">
					<label for="zustellung" class="form-label col-sm-4">Zustellung</label>
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
					<label for="standort" class="form-label col-sm-4">Firma / Standort</label>
						<div v-if="contactData.kurzbz" class="col-sm-3">
							<input type="text" :readonly="readonly" class="form-control" id="name" v-model="contactData.kurzbz">
						</div>	
						<div v-else class="col-sm-3">
							<PvAutoComplete v-model="contactData['standort']" optionLabel="kurzbz" :suggestions="filteredStandorte" @complete="search" minLength="3"/>
						</div>
				</div>
			</form>
			<template #footer>
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="resetModal">Abbrechen</button>
				<button type="button" class="btn btn-primary" @click="updateContact(contactData.kontakt_id)">OK</button>
			</template>
		</BsModal>
									
		<!--Modal: Delete Contact-->
		<BsModal ref="deleteContactModal">
			<template #title>Kontakt löschen</template>  
			<template #default>
				<p>Kontakt wirklich löschen?</p>
			</template>												
			<template #footer>
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="resetModal">Abbrechen</button>
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
			new-btn-label="Neu"
			@click:new="actionNewContact"
			>
		</core-filter-cmpt>
		</div>`
};