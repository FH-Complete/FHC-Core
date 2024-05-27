import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import PvAutoComplete from "../../../../../../../index.ci.php/public/js/components/primevue/autocomplete/autocomplete.esm.min.js";
import FhcFormValidation from '../../../../Form/Validation.js';
import BsModal from "../../../../Bootstrap/Modal.js";

export default{
	components: {
		CoreFilterCmpt,
		PvAutoComplete,
		FhcFormValidation,
		BsModal
	},
	props: {
		uid: Number
	},
	data() {
		return{
			tabulatorOptions: {
				ajaxURL: 'api/frontend/v1/stv/kontakt/getAdressen/' + this.uid,
				ajaxRequestFunc: this.$fhcApi.get,
				ajaxResponse: (url, params, response) => response.data,
				//autoColumns: true,
				columns:[
					{title:"Typ", field:"bezeichnung"},
					{title:"Strasse", field:"strasse"},
					{title:"Plz", field:"plz"},
					{title:"Ort", field:"ort"},
					{title:"Gemeinde", field:"gemeinde"},
					{title:"Nation", field:"nation"},
					{title:"Heimatadresse", field:"heimatadresse",
						formatter: (cell, formatterParams, onRendered) => {
							let output = cell.getValue() ? "ja" : "nein";
							return output;
						}
					},
					{title:"Abweich.Empf", field:"co_name"},
					{title:"Firma", field:"firmenname"},
					{title:"Firma_id", field:"firma_id", visible:false},
					{title:"Name", field:"name"},
					{title:"Adresse_id", field:"adresse_id", visible:false},
					{title:"Person_id", field:"person_id", visible:false},
					{title:"Name", field:"name", visible:false},
					{title:"letzte Änderung", field:"updateamum", visible:false},
					{title:"Rechnungsadresse", field:"rechnungsadresse", visible:false,
						formatter: (cell, formatterParams, onRendered) => {
							let output = cell.getValue() ? "ja" : "nein";
							return output;
						}
					},
					{title:"Anmerkung", field:"anmerkung", visible:false},
					{title: 'Aktionen', field: 'actions',
						minWidth: 150, // Ensures Action-buttons will be always fully displayed
						formatter: (cell, formatterParams, onRendered) => {
							let container = document.createElement('div');
							container.className = "d-flex gap-2";

							let button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-edit"></i>';
							button.addEventListener('click', (event) =>
								this.actionEditAdress(cell.getData().adresse_id)
							);
							container.append(button);

							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-xmark"></i>';
							button.addEventListener('click', () =>
								this.actionDeleteAdress(cell.getData().adresse_id)
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
				index: 'adresse_id',
			},
			tabulatorEvents: [
				{
					event: 'tableBuilt',
					handler: async () => {
						await this.$p.loadCategory(['notiz','global','person']);
						let cm = this.$refs.table.tabulator.columnManager;

						cm.getColumnByField('bezeichnung').component.updateDefinition({
							title: this.$p.t('global', 'typ')
						});
						cm.getColumnByField('strasse').component.updateDefinition({
							title: this.$p.t('person', 'strasse')
						});
						cm.getColumnByField('plz').component.updateDefinition({
							title: this.$p.t('person', 'plz')
						});
						cm.getColumnByField('ort').component.updateDefinition({
							title: this.$p.t('person', 'ort')
						});
						cm.getColumnByField('gemeinde').component.updateDefinition({
							title: this.$p.t('person', 'gemeinde')
						});
						cm.getColumnByField('nation').component.updateDefinition({
							title: this.$p.t('person', 'nation')
						});
						cm.getColumnByField('heimatadresse').component.updateDefinition({
							title: this.$p.t('person', 'heimatadresse')
						});
						cm.getColumnByField('co_name').component.updateDefinition({
							title: this.$p.t('person', 'co_name')
						});
						cm.getColumnByField('name').component.updateDefinition({
							title: this.$p.t('person', 'firma_zusatz')
						});
						cm.getColumnByField('firmenname').component.updateDefinition({
							title: this.$p.t('person', 'firma')
						});
						cm.getColumnByField('updateamum').component.updateDefinition({
							title: this.$p.t('notiz', 'letzte_aenderung')
						});
						cm.getColumnByField('rechnungsadresse').component.updateDefinition({
							title: this.$p.t('person', 'rechnungsadresse')
						});
						cm.getColumnByField('anmerkung').component.updateDefinition({
							title: this.$p.t('notiz', 'document')
						});
					}
				}
			],
			addressData: {
				zustelladresse: true,
				heimatadresse: true,
				rechnungsadresse: false,
				typ: 'h',
				nation: 'A'
			},
			initData: {
				zustelladresse: true,
				heimatadresse: true,
				rechnungsadresse: false,
				typ: 'h',
				nation: 'A'
			},
			places: [],
			suggestions: {},
			nations: [],
			adressentypen: [],
			firmen: [],
			filteredFirmen: [],
			abortController: {
				suggestions: null,
				places: null
			}
		}
	},
	computed:{
		orte() {
			return this.places.filter(ort => ort.name == this.addressData.gemeinde);
		},
		gemeinden() {
			return Object.values(this.places.reduce((res,place) => {
				res[place.name] = place;
				return res;
			}, {}));
		}
	},
	watch: {
		uid() {
			this.$refs.table.tabulator.setData('api/frontend/v1/stv/Kontakt/getAdressen/' + this.uid);
		}
	},
	methods:{
		actionNewAdress() {
			this.$refs.newAdressModal.show();
		},
		actionEditAdress(adress_id) {
			this.loadAdress(adress_id).then(() => {
				if(this.addressData.adresse_id)
					this.$refs.editAdressModal.show();
			});
		},
		actionDeleteAdress(adress_id) {
			this.loadAdress(adress_id).then(() => {
				if(this.addressData.adresse_id)
					if(this.addressData.heimatadresse)
						this.$fhcAlert.alertError(this.$p.t('person', 'error_deleteHomeAdress'));
					else
						this.$refs.deleteAdressModal.show();
			});
		},
		addNewAddress(addressData) {
			this.$fhcApi.post('api/frontend/v1/stv/kontakt/addNewAddress/' + this.uid,
				this.addressData
			).then(response => {
				this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.hideModal('newAdressModal');
					this.resetModal();
			}).catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
				window.scrollTo(0, 0);
				this.reload();
			});
		},
		reload() {
			this.$refs.table.reloadTable();
		},
		loadAdress(adress_id) {
			return this.$fhcApi.get('api/frontend/v1/stv/kontakt/loadAddress/' + adress_id)
				.then(result => {
						this.addressData = result.data;
						return result;
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		updateAddress(adress_id) {
			this.$fhcApi.post('api/frontend/v1/stv/kontakt/updateAddress/' + adress_id,
				this.addressData
			).then(response => {
				this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.hideModal('editAdressModal');
					this.resetModal();
			}).catch(this.$fhcAlert.handleSystemError)
			.finally(() => {
				window.scrollTo(0, 0);
				this.reload();
			});
		},
		deleteAddress(adress_id) {
			this.$fhcApi.post('api/frontend/v1/stv/kontakt/deleteAddress/' + adress_id)
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));
				}).catch(this.$fhcAlert.handleSystemError)
				.finally(()=> {
					window.scrollTo(0, 0);
					this.hideModal('deleteAdressModal');
					this.reload();
				});
		},
		loadPlaces() { //TODO(manu) Refactor API-controller
			if (this.abortController.places)
				this.abortController.places.abort();
			if (this.addressData.nation != 'A' || !this.addressData.plz)
				return;

			this.abortController.places = new AbortController();
			this.$fhcApi
				.get('api/frontend/v1/stv/address/getPlaces/' + this.addressData.plz, undefined, {
					signal: this.abortController.places.signal
				})
				.then(result => {
					this.places = result.data;
				});
/*				.catch(error => {
					if (error.code == 'ERR_BAD_REQUEST') {
						return this.$fhcAlert.handleFormValidation(error, this.$refs.form);
					}
					// NOTE(chris): repeat request
					if (error.code != "ERR_CANCELED")
						window.setTimeout(this.loadPlaces, 100);
				});*/
		},
		search(event) {
			return this.$fhcApi
				.get('api/frontend/v1/stv/kontakt/getFirmen/' + event.query)
				.then(result => {
					this.filteredFirmen = result.data.retval;
				});
		},
		hideModal(modalRef) {
			this.$refs[modalRef].hide();
		},
		resetModal() {
			this.addressData = {};
			this.addressData = this.initData;
		},
	},
	created() {
		this.$fhcApi
			.get('api/frontend/v1/stv/address/getNations')
			.then(result => {
				this.nations = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);
		this.$fhcApi
			.get('api/frontend/v1/stv/kontakt/getAdressentypen')
			.then(result => {
				this.adressentypen = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError)
	},
	// TODO(chris): use Form Component
	template: `
	<div class="stv-details-kontakt-address h-100 pt-3">
		
		<!--Modal: Add Address-->
		<bs-modal ref="newAdressModal">
			<template #title>{{$p.t('person', 'adresse_new')}}</template>
			<form class="row g-3" ref="addressData">
				<div class="row mb-3">
					<label for="adressentyp" class="form-label col-sm-4">{{$p.t('global', 'typ')}}</label>
					<div class="col-sm-6">
						<select id="adressentyp" class="form-select" v-model="addressData.typ">
							<option v-for="typ in adressentypen" :key="typ.adressentyp_kurzbz" :value="typ.adressentyp_kurzbz" >{{typ.bezeichnung}}</option>
						</select>
					</div>
				</div>
				<div class="row mb-3">
					<label for="strasse" class="form-label col-sm-4">{{$p.t('person', 'strasse')}}</label>
					<div class="col-sm-6">
						<input type="text" class="form-control" id="strasse" v-model="addressData['strasse']">
					</div>
				</div>
					
				<div class="row mb-3">
					<label for="nation" class="form-label col-sm-4">{{$p.t('person', 'nation')}}</label>
					<div class="col-sm-6">
						<select id="nation" class="form-select" v-model="addressData.nation">
							<option v-for="nation in nations" :key="nation.nation_code" :value="nation.nation_code" :disabled="nation.sperre">{{nation.kurztext}}</option>
						</select>
					</div>
				</div>
									
				<div class="row mb-3">
					<label for="plz" class="required form-label col-sm-4" >{{$p.t('person', 'plz')}}</label>
					 <div class="col-sm-6">
						<input type="text" class="form-control" required v-model="addressData['plz']" @input="loadPlaces" >
					</div>
				</div>
				
				<div class="row mb-3">
					<label for="gemeinde" class="form-label col-sm-4">{{$p.t('person', 'gemeinde')}}</label>
					<div class="col-sm-6">
						<select v-if="addressData['nation'] == 'A'" name="addressData[gemeinde]" class="form-select" v-model="addressData['gemeinde']">
							<option v-if="!gemeinden.length" disabled>Bitte gültige PLZ wählen</option>
							<option v-for="gemeinde in gemeinden" :key="gemeinde.name" :value="gemeinde.name">{{gemeinde.name}}</option>
						</select>
						<input v-else type="text" class="form-control" v-model="addressData['gemeinde']">
					</div>
				</div>
				
				<div class="row mb-3">
					<label for="Ort" class="form-label col-sm-4">{{$p.t('person', 'ort')}}</label>
					<div class="col-sm-6">
						<select v-if="addressData['nation'] == 'A'" name="address[ort]" class="form-select" v-model="addressData['ort']">
							<option v-if="!orte.length" disabled>Bitte gültige Gemeinde wählen</option>
							<option v-for="ort in orte" :key="ort.ortschaftsname" :value="ort.ortschaftsname">{{ort.ortschaftsname}}</option>
						</select>
						<input v-else type="text" name="ort" class="form-control" v-model="addressData['ort']">
					</div>
				</div>
									
				<div class="row mb-3">
					<label for="heimatadresse" class="form-label col-sm-4">{{$p.t('person', 'heimatadresse')}}</label>
					<div class="col-sm-3">
						<div class="form-check">
							<input id="heimatadresse" type="checkbox" class="form-check-input" value="1" v-model="addressData['heimatadresse']">
						</div>
					</div>
				</div>
					
				<div class="row mb-3">
					<label for="zustelladresse" class="form-label col-sm-4">{{$p.t('person', 'zustelladresse')}}</label>
					<div class="col-sm-3">
						<div class="form-check">
							<input id="zustelladresse" type="checkbox" class="form-check-input" value="1" v-model="addressData['zustelladresse']">
						</div>
					</div>
				</div>
				
				<div class="row mb-3">
					<label for="co_name" class="form-label col-sm-4">{{$p.t('person', 'co_name')}}</label>
					<div class="col-sm-6">
						<input type="text" id="co_name" class="form-control" v-model="addressData['co_name']">
					</div>
				</div>
				
				<div class="row mb-3">
					<label for="rechnungsadresse" class="form-label col-sm-4">{{$p.t('person', 'rechnungsadresse')}}</label>
					<div class="col-sm-3">
						<div class="form-check">
							<input id="rechnungsadresse" type="checkbox" class="form-check-input" v-model="addressData['rechnungsadresse']">
						</div>
					</div>
				</div>
				
				<div class="row mb-3">
					<label for="firma_name" class="form-label col-sm-4">{{$p.t('person', 'firma')}}</label>
					<div class="col-sm-3">
						<PvAutoComplete v-model="addressData['firma']"  optionLabel="name" :suggestions="filteredFirmen" @complete="search" :min-length="3"/>
					</div>
				</div>
				
				<div class="row mb-3">
					<label for="name" class="form-label col-sm-4">{{$p.t('person', 'firma_zusatz')}}</label>
					<div class="col-sm-6">
						<input type="text" class="form-control" id="name" v-model="addressData['name']">
					</div>
				</div>
				<div class="row mb-3">
					<label for="anmerkung" class="form-label col-sm-4">{{$p.t('global', 'anmerkung')}}</label>
					<div class="col-sm-6">
						<input type="text" class="form-control" id="anmerkung" v-model="addressData['anmerkung']">
					</div>
				</div>
			</form>
			<template #footer>
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{$p.t('ui', 'abbrechen')}}</button>
				<button type="button" class="btn btn-primary" @click="addNewAddress()">OK</button>
            </template>
		</bs-modal>
		
		<!--Modal: Edit Address-->
		<bs-modal ref="editAdressModal">
			<template #title>{{$p.t('person', 'adresse_edit')}}</template>
			<form class="row g-3" ref="addressData">
				<div class="row mb-3">
					<label for="adressentyp" class="form-label col-sm-4">{{$p.t('global', 'typ')}}</label>
					<div class="col-sm-6">
						<select id="adressentyp" class="form-control" v-model="addressData.typ">
							<option v-for="typ in adressentypen" :key="typ.adressentyp_kurzbz" :value="typ.adressentyp_kurzbz" >{{typ.bezeichnung}}</option>
						</select>
					</div>
				</div>
				
				<div class="row mb-3">
					<label for="strasse" class="form-label col-sm-4">{{$p.t('person', 'strasse')}}</label>
					<div class="col-sm-6">
						<input type="text" class="form-control" id="strasse" v-model="addressData.strasse">
					</div>
				</div>
					
				<div class="row mb-3">
					<label for="nation" class="form-label col-sm-4">{{$p.t('person', 'nation')}}</label>
					<div class="col-sm-6">
						<select id="nation" class="form-select" v-model="addressData.nation">
							<option v-for="nation in nations" :key="nation.nation_code" :value="nation.nation_code" :disabled="nation.sperre">{{nation.kurztext}}</option>
						</select>
					</div>
				</div>
				
				<div class="row mb-3">
					<label for="plz" class="required form-label col-sm-4" >{{$p.t('person', 'plz')}}</label>
					 <div class="col-sm-6">
						<input type="text" class="form-control" required v-model="addressData['plz']" @input="loadPlaces">
					</div>
				</div>
				<div class="row mb-3">
					<label for="gemeinde" class="form-label col-sm-4">{{$p.t('person', 'gemeinde')}}</label>
					<div v-if="addressData['gemeinde']" class="col-sm-6">
						<input type="text" class="form-control" v-model="addressData['gemeinde']">
					</div>
					<div v-else class="col-sm-6">
						<select v-if="addressData['nation'] == 'A'" name="addressData[gemeinde]" class="form-select" v-model="addressData['gemeinde']">
							<option v-if="!gemeinden.length" disabled>{{$p.t('person', 'plz_waehlen')}}</option>
							<option v-for="gemeinde in gemeinden" :key="gemeinde.name" :value="gemeinde.name">{{gemeinde.name}}</option>
						</select>
					</div>
				</div>
		
				<div class="row mb-3">
					<label for="Ort" class="form-label col-sm-4">{{$p.t('person', 'ort')}}</label>
					<div v-if="addressData['ort']" class="col-sm-6">
						<input type="text" name="ort" class="form-control" v-model="addressData['ort']">
					</div>
					<div v-else class="col-sm-6">
						<select v-if="addressData['nation'] == 'A'" name="address[ort]" class="form-select" v-model="addressData['ort']">
							<option v-if="!orte.length" disabled>{{$p.t('person', 'gemeinde_waehlen')}}</option>
							<option v-for="ort in orte" :key="ort.ortschaftsname" :value="ort.ortschaftsname">{{ort.ortschaftsname}}</option>
						</select>
					</div>
				</div>
								
				<div class="row mb-3">
					<label for="heimatadresse" class="form-label col-sm-4">{{$p.t('person', 'heimatadresse')}}</label>
					<div class="col-sm-3">
						<div class="form-check">
							<input id="heimatadresse" type="checkbox" class="form-check-input" value="1" v-model="addressData['heimatadresse']">
						</div>
					</div>
				</div>
					
				<div class="row mb-3">
					<label for="zustelladresse" class="form-label col-sm-4">{{$p.t('person', 'zustelladresse')}}</label>
					<div class="col-sm-3">
						<div class="form-check">
							<input id="zustelladresse" type="checkbox" class="form-check-input" value="1" v-model="addressData['zustelladresse']">
						</div>
					</div>
				</div>
				
				<div class="row mb-3">
					<label for="co_name" class="form-label col-sm-4">{{$p.t('person', 'co_name')}}</label>
					<div class="col-sm-6">
						<input type="text" id="co_name" class="form-control" v-model="addressData['co_name']">
					</div>
				</div>
				
				<div class="row mb-3">
					<label for="rechnungsadresse" class="form-label col-sm-4">{{$p.t('person', 'rechnungsadresse')}}</label>
					<div class="col-sm-3">
						<div class="form-check">
							<input id="rechnungsadresse" type="checkbox" class="form-check-input" v-model="addressData['rechnungsadresse']">
						</div>
					</div>
				</div>
				
				<div class="row mb-3">
					<label for="firma_name" class="form-label col-sm-4">{{$p.t('person', 'firma')}}</label>
					<div v-if="addressData.firmenname" class="col-sm-6">
						<input type="text" class="form-control" id="name" v-model="addressData.firmenname">
					</div>
					<div v-else class="col-sm-6">
						<PvAutoComplete v-model="addressData['firma']" optionLabel="name" :suggestions="filteredFirmen" @complete="search" :min-length="3"/>
					</div>
				</div>
				
				<div class="row mb-3">
					<input type="hidden" class="form-control" id="firma_id" v-model="addressData.firma_id">
				</div>
	
				<div class="row mb-3">
					<label for="name" class="form-label col-sm-4">{{$p.t('person', 'firma_zusatz')}}</label>
					<div class="col-sm-6">
						<input type="text" class="form-control" id="name" v-model="addressData['name']">
					</div>
				</div>
				
				<div class="row mb-3">
					<label for="anmerkung" class="form-label col-sm-4">{{$p.t('global', 'anmerkung')}}</label>
					<div class="col-sm-6">
						<input type="text" class="form-control" id="anmerkung" v-model="addressData['anmerkung']">
					</div>
				</div>
			
			</form>
			<template #footer>
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="resetModal">{{$p.t('ui', 'abbrechen')}}</button>
				<button ref="Close" type="button" class="btn btn-primary" @click="updateAddress(addressData.adresse_id)">OK</button>
            </template>
		</bs-modal>
		
		<!--Modal: deleteAdressModal-->
		<bs-modal ref="deleteAdressModal">
			<template #title>{{$p.t('person', 'adresse_delete')}}</template>
			<template #default>
				<p>{{$p.t('person', 'adresse_confirm_delete')}}</p>
			</template>
			<template #footer>
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="resetModal">{{$p.t('ui', 'abbrechen')}}</button>
				<button ref="Close" type="button" class="btn btn-primary" @click="deleteAddress(addressData.adresse_id)">OK</button>
			</template>
		</bs-modal>
			
		<core-filter-cmpt
			ref="table"
			:tabulator-options="tabulatorOptions"
			:tabulator-events="tabulatorEvents"
			table-only
			:side-menu="false"
			reload
			new-btn-show
			new-btn-label="Adresse"
			@click:new="actionNewAdress"
			>
		</core-filter-cmpt>
	</div>`
};

